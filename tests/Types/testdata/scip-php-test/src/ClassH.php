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
            $x = \TestData4\fun2()->f2()->a2();
            return $this->getCode() + fun2()->a2() * $x;
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
    use TestData\ClassA;
    use TestData\ClassB;

    final class ClassJ
    {
        public const J3 = 42;

        /** @return ($p1 is positive-int ? ClassA : ClassB) */
        public function j1(int $p1)
        {
            if ($p1 > 0) {
                return new ClassA();
            }
            return new ClassB();
        }

        /**
         * @template T of int|string
         * @param T $p1
         * @return (T is int ? ClassA : ClassB)
         */
        public function j2($p1)
        {
            if (is_int($p1)) {
                return new ClassA();
            }
            return new ClassB();
        }

        public function j3(): void
        {
            $this->j1(1)->a1();
            $this->j1(-1)->b2();
            $this->j2(1)->a1();
            $this->j2('foo')->b2();
        }
    }
}
