<?php

namespace Commands\Command\Pack;

use Admin\App;
use Commands\Command\CommandProto;
use Commands\CommandConfig;
use Commands\CommandContext;

class GitCreateTag extends CommandProto
{
    public const QUESTION_TAG = 'tag';

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
        $checkpoint = $this->context->getCheckpoint()->getName();
        $tag = $this->getContext()->get(CommandContext::USER_CONTEXT)[self::QUESTION_TAG] ?? null;
        if (!$tag) {
            $this->runtime->log(sprintf('tag `%s` is invalid', $tag));
            return;
        }

        $sshPrivateKey = getcwd().'/ssh_keys/'.App::i()->auth->getUserLogin();
        if (!file_exists($sshPrivateKey)) {
            $this->runtime->log('specific ssh private key "'.$sshPrivateKey.'" not found. Used default.', 'git config');
            $sshPrivateKey = null;
        }

        foreach ($this->context->getPack()->getRepos() as $id => $repo) {
            $repo->setSshKeyPath($sshPrivateKey);

            $repo->fetch();
            $repo->checkout($checkpoint);
            $repo->createTag($tag);
            $repo->push('--tags');

            $repo->setSshKeyPath(null);

            $this->runtime[$repo->getPath()] = $repo->getLastOutput();
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
            'Введи желаемый тег (текущий: ' . trim($lastTag) . ')',
            $this->getNextVersion($lastTag)
        );
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

    private function getNextVersion(string $lastVersion)
    {
        $dotParts = explode('.', $lastVersion);
        $lastItem = array_pop($dotParts);
        if (is_numeric($lastItem)) {
            $lastItem++;
            $dotParts[] = $lastItem;
        }

        return implode('.', $dotParts);
    }

    /**
     * @return string|null
     */
    private function getLastTag() : ?string
    {
        $sshPrivateKey = getcwd() . '/ssh_keys/' . App::i()->auth->getUserLogin();
        foreach ($this->context->getPack()->getRepos() as $id => $repo) {
            try {
                $repo->setSshKeyPath($sshPrivateKey);
                return $repo->getLastTag();
            } finally {
                $repo->setSshKeyPath(null);
                $this->runtime[$repo->getPath()] = $repo->getLastOutput();
            }
        }

        return null;
    }
}