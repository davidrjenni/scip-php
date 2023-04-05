<?php

declare(strict_types=1);

namespace ScipPhp\Git;

use function exec;
use function implode;
use function strlen;
use function substr;

final class Git
{
    /**
     * Returns the version of the Git repository in the current directory.
     *
     * @return non-empty-string
     */
    public static function version(): string
    {
        $version = self::git('tag', '-l', '--points-at', 'HEAD');
        if ($version !== '') {
            return $version;
        }
        $sha = self::git('rev-parse', 'HEAD');
        if (strlen($sha) < 12) {
            throw new GitException('git rev-parse HEAD', 'got no output');
        }
        return substr($sha, 0, 12);
    }

    private static function git(string ...$args): string
    {
        $cmd = 'git ' . implode(' ', $args);
        $output = [];
        $result = exec($cmd, $output);
        if ($result === false) {
            throw new GitException($cmd, $output[0] ?? '');
        }
        return $result;
    }
}
