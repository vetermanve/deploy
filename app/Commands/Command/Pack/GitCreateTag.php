<?php

namespace Commands\Command\Pack;

use Admin\App;
use Commands\Command\CommandProto;
use Commands\CommandConfig;
use Commands\CommandContext;
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
            $this->runtime->log('Create new tag failed: this repository does not have tags for increment minor version or tag is incorrect');
            return;
        }

        $releaseNote = sprintf(
            '[%s] RELEASE NOTE: %s',
            (new \DateTime)->format(self::DATE_FORMAT),
            implode(', ', (array) $this->getContext()->getPack()->getBranches())
        );
        $sshPrivateKey = App::i()->auth->getUser()->getSSH();
        if (null === $sshPrivateKey) {
            $this->runtime->log('specific ssh private key "' . $sshPrivateKey . '" not found. Used default.', 'git config');
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
        $dotParts = explode('.', $lastVersion);
        $lastItem = array_pop($dotParts);
        if (is_numeric($lastItem)) {
            $lastItem++;
            $dotParts[] = $lastItem;
        } else {
            // if receive a "release_tag" without version, insert it: "release_tag-1.0.0"
            $dotParts[] = '_' . self::FIRST_VERSION;
        }

        return implode('.', $dotParts);
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
                // path in slot = regex for tag or empty
                $slot = $this->getContext()->getSlot();
                return $repo->getLastTag($slot instanceof TagYmlSlot ? $slot->getTag() : null);
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
}