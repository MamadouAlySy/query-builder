<?php

declare(strict_types=1);

namespace MamadouAlySy\QueryBuilder\Manupulation\Traits;

trait FromTrait
{
    protected ?string $from = null;

    public function from(string $table): static
    {
        $this->from = $table;

        return $this;
    }

    protected function renderFrom(): string
    {
        return $this->hasFrom() ? " FROM `{$this->from}`" : '';
    }

    protected function hasFrom(): bool
    {
        return !is_null($this->from) && !empty($this->from);
    }
}