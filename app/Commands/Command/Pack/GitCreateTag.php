<?php

namespace Commands\Command\Pack;

use Admin\App;
use Commands\Command\CommandProto;
use Commands\CommandConfig;
use Commands\CommandContext;
use Composer\Semver\Semver;
use Exception\BuilderException;
use PHLAK\SemVer\Exceptions\InvalidVersionException;
use PHLAK\SemVer\Version;
use Service\Event\EventConfig;
use Service\Slot\TagYmlSlot;

class GitCreateTag extends CommandProto
{
    public const QUESTION_TAG  = 'tag';
    public const DATE_FORMAT   = 'Y-m-d H:i:sP';
    public const FIRST_VERSION = '1.0.0';

    /**
     * @return string
     */
    public function getId()
    {
        return CommandConfig::CHECKPOINT_CREATE_TAG;
    }

    /**
     * @return string
     */
    public function getHumanName()
    {
        return 'Тегнуть';
    }

    public function run()
    {
        if (!$this->getContext()->getCheckpoint()) {
            $this->runtime[] = 'nothing to tag';
            return;
        }

        $checkpoint = $this->getContext()->getCheckpoint()->getName();
        $tag = $this->getNewTag();
        if (!$tag) {
            throw new BuilderException(
                'Create new tag failed: this repository does not have tags for increment minor version or tag is incorrect. ' .
                'Create a first tag for your project like a `release-1.0.0` (and set `/release-.*/` pattern to builder.yml at your repo)'
            );
        }

        $releaseNote = sprintf(
            '[%s] RELEASE NOTE: %s',
            (new \DateTime)->format(self::DATE_FORMAT),
            implode(', ', (array) $this->getContext()->getPack()->getBranches())
        );
        $sshPrivateKey = App::i()->auth->getUser()->getSSH();
        if (null === $sshPrivateKey) {
            $this->runtime[] = 'specific ssh private key "' . $sshPrivateKey . '" not found. Used default.';
            $sshPrivateKey = null;
        }

        foreach ($this->getContext()->getPack()->getRepos() as $id => $repo) {
            try {
                $repo->setSshKeyPath($sshPrivateKey);
                // remote tag - master data
                $repo->removeLocalTags();
                $repo->fetch();
                $repo->checkout($checkpoint);
                $repo->createTag($tag, $releaseNote);
                $repo->push('--tags');
                $this->notifyVersionChange($tag);
            } finally {
                $repo->setSshKeyPath(null);
                $this->runtime[$repo->getPath()] = $repo->getLastOutput();
            }
        }
    }

    /**
     * @return array
     * @throws \Git\GitException
     */
    public function isQuestion() : array
    {
        $lastTag = $this->getLastTag();
        return $this->createQuestion(
            self::QUESTION_TAG,
            'Введи желаемый тег (последний: ' . trim($lastTag) . ')',
            !empty($lastTag) ? $this->getNextVersion($lastTag) : self::FIRST_VERSION
        );
    }

    /**
     * @return bool
     */
    public function isConfirmRequired()
    {
        $slot = $this->getContext()->getSlot();
        return $slot instanceof TagYmlSlot ? (bool) $slot->getConfirm() : false;
    }

    /**
     * @return string|null
     */
    protected function getNewTag() : ?string
    {
        return $this->getContext()->get(CommandContext::USER_CONTEXT)[self::QUESTION_TAG] ?? null;
    }

    /**
     * @param string $lastVersion
     * @return string
     */
    protected function getNextVersion(string $lastVersion)
    {
        $nextVersion = $lastVersion;
        // improve this regular expression
        $pattern = preg_replace('/(\d+\.)(\d+\.)(\d+)/', '', $lastVersion);
        try {
            $version = new Version(str_replace($pattern, '', $lastVersion));
        } catch (\Throwable $e) {
            $this->runtime[] = 'Can not parse version in `' . $lastVersion . '`';
            return $nextVersion;
        }

        $slot = $this->getContext()->getSlot();
        $releaseType = null;
        if ($slot instanceof TagYmlSlot) {
            $releaseType = $slot->release;
        }
        switch ($releaseType) {
            case TagYmlSlot::RELEASE_MAJOR:
                $nextVersion = $version->incrementMajor();
                break;
            case TagYmlSlot::RELEASE_MINOR:
                $nextVersion = $version->incrementMinor();
                break;
            default:
                $nextVersion = $version->incrementPatch();
                break;
        }

        return $pattern . $nextVersion;
    }

    /**
     * @return string|null
     * @throws \Git\GitException
     */
    protected function getLastTag() : ?string
    {
        $sshPrivateKey = App::i()->auth->getUser()->getSSH();
        foreach ($this->getContext()->getPack()->getRepos() as $id => $repo) {
            try {
                $repo->setSshKeyPath($sshPrivateKey);
                $repo->removeLocalTags();
                $repo->fetch();
                // tag in TagYmlSlot = regex for tag or empty
                $slot = $this->getContext()->getSlot();
                return $repo->getLastTag($slot instanceof TagYmlSlot ? $slot->tag : null);
            } catch (\Throwable $e) {
                $this->runtime[$repo->getPath()] = $repo->getLastOutput();
            } finally {
                $repo->setSshKeyPath(null);
            }
        }

        return null;
    }

    /**
     * Выглядит, как отдельная фабрика. Перенеси при переиспользовании в отдельный объект
     * @param string $field
     * @param string $question
     * @param string $placeholder
     * @return array
     */
    private function createQuestion(string $field, string $question, string $placeholder) : array
    {
        return [
            'field'       => $field,
            'question'    => $question,
            'placeholder' => $placeholder,
        ];
    }

    /**
     * @param string $version
     */
    private function notifyVersionChange(string $version = '') : void
    {
        $this->runtime->getEventProcessor()->add(
            'Текущая версия окружения: ' . $version,
            EventConfig::EVENT_TYPE_VERSION_CHANGE, [
            EventConfig::DATA_CALLBACK => $this->context->getSlot()->getCallback(),
            EventConfig::DATA_SLACK    => $this->context->getSlot()->getSlack(),
        ]);
    }
}