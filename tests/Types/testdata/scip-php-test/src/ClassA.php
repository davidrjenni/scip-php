<?php

declare(strict_types=1);

namespace TestData;

final class ClassA
{
    private ClassB $a1;

    protected int $a2;

    public function a1(): ?int
    {
        $v1 = $this->a1::$b1->c2->b2;
        $v2 = $this->a1->b1?->e1;
        $v3 = $this->a1->b1?->c2->d1->f1;
        return $v1 + $v2 + $v3 + $this->a1::$b1?->c2->a2;
    }

    public function a2(): int
    {
        $v1 = $this->a1();
        if ($v1 === null || $this->a1->b1?->e2->i1($v1) || $this->a1->b1?->e2->i1) {
            return $this->a1->b1() * $this->a1->b1?->e2::I1;
        }
        $v2 = ($v3 = $this->a1->b1)?->c1()->d1->f1();
        $v4 = ClassB::B1;
        return $v1 + $v2 + $v4 + $this->a1::B1;
    }

    public function a3(): void
    {
        $v1 = ($v3 = $this->a1->b1)?->c1()->d1->f2::G1;
        $v2 = EnumG::G2;
    }

    public function a4(): int
    {
        $v1 = $this->a1();
        $v2 = ($v1 !== null ?: $this->a1->b1)?->c1;
        $v3 = ($v1 !== null ? $this->a1->b1 : $this->a1->b1?->c2->d1)?->c1();
        $v3 = ClassF::f2()->a1();
        $v3 = (new ClassF())::f2()->a1();
        $v4 = (new class($v3) {
            public int $z1;
            public function __construct(?int $p) { $this->z1 = $p; }
        })->z1;
        $v5 = (new class($v3) extends ClassF {})->f1;
        $v5 = (clone (new class($v3) extends ClassF {}))->f1;
        $v6 = ($v1 ?? $this->a1)?->b1;
        $v6 = ($this->a1 ?? $v1)?->b1;
        $v6 = ($this->a1 ?? $this->a1?->b1)?->c1;
        $v7 = (match($v1) {
            1 => $this->a1,
            2 => $this->a1?->b1,
        })->c1;
        $v7 = (match($v1) {
            1 => $this->a1,
            PHP_MAJOR_VERSION => $this->a1?->b1,
        })->b2;
        $v8 = ($this->a1->b1 ?: $this->a1)?->c1;
    }
}
