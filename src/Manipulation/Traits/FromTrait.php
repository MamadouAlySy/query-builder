<?php

declare(strict_types=1);

namespace MamadouAlySy\QueryBuilder\Manipulation\Traits;

trait FromTrait
{
    protected ?string $table = null;

    public function from(string $table): static
    {
        $this->table = $table;

        return $this;
    }

    protected function getFromStatement(): string
    {
        return $this->hasFromStatement() ? " FROM `{$this->table}`" : "";
    }

    protected function hasFromStatement(): bool
    {
        return $this->table !== null;
    }
}
