<?php

declare(strict_types=1);

namespace Test\Dep;

use Test\Dep\ClassI;

function fun4(): ClassI
{
    return new ClassI();
}
