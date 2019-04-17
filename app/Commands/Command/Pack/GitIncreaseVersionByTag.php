<?php

namespace Commands\Command\Pack;

class GitIncreaseVersionByTag extends GitCreateTag
{
    /**
     * @return string|null
     * @throws \Git\GitException
     */
    protected function getNewTag() : ?string
    {
        $lastTag = $this->getLastTag();
        return null !== $lastTag ? $this->getNextVersion($lastTag) : null;
    }
}