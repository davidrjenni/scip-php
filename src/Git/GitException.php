<?php

declare(strict_types=1);

namespace ScipPhp\Git;

use Exception;

final class GitException extends Exception
{
    /** @param  non-empty-string  $cmd */
    public function __construct(string $cmd, string $error)
    {
        parent::__construct("Error while executing {$cmd}: {$error}");
    }
}
