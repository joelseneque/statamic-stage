<?php

namespace JoelSeneque\StatamicStage\Git\Exceptions;

use Exception;

class GitOperationException extends Exception
{
    public function __construct(
        string $message,
        protected string $command = '',
        protected string $output = ''
    ) {
        parent::__construct($message);
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getOutput(): string
    {
        return $this->output;
    }
}
