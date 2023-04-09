<?php

declare(strict_types=1);

namespace TestData;

use Exception;

final class ClassH extends Exception
{

    public function __construct()
    {
        parent::__construct();
    }

    public function h1(): int
    {
        return $this->getCode();
    }
}
