<?php

declare(strict_types=1);

namespace TestData
{
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
}

// https://www.php.net/manual/en/language.namespaces.definitionmultiple.php

namespace TestData2
{
    final class ClassJ
    {
        public const J2 = 42;
    }
}

namespace
{
    final class ClassJ
    {
        public const J3 = 42;
    }
}
