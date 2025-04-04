<?php

declare(strict_types=1);

namespace ScipPhp\Types\Internal;

use Override;

use function array_keys;

final readonly class CompositeType implements Type
{
    /** @var list<non-empty-string> */
    private array $types;

    public function __construct(?Type ...$types)
    {
        $flattened = [];
        foreach ($types as $type) {
            if ($type === null) {
                continue;
            }
            foreach ($type->flatten() as $t) {
                $flattened[$t] = true;
            }
        }
        $this->types = array_keys($flattened);
    }

    /** @inheritDoc */
    #[Override]
    public function flatten(): array
    {
        return $this->types;
    }
}
