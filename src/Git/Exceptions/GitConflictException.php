<?php

namespace JoelSeneque\StatamicStage\Git\Exceptions;

class GitConflictException extends GitOperationException
{
    public function __construct(
        string $message,
        protected string $conflictingFiles = ''
    ) {
        parent::__construct($message);
    }

    public function getConflictingFiles(): string
    {
        return $this->conflictingFiles;
    }
}
