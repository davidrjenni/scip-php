<?php

namespace Test\Dep;

class ClassI {

    public const I1 = 23;

    public bool $i1;

    public static function i1($value): bool
    {
        $this->i1 = $value % 2 === 0;
        return $this->i1;
    }
}
