<?php

declare(strict_types=1);

namespace TestData;

final class ClassC
{
    use TraitE;

    public string $c1;

    public ClassB|ClassD $c2;

    public function c1(): ClassB|ClassD
    {
        return $this->c2;
    }
}
