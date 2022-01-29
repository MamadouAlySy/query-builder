<?php

declare(strict_types=1);

namespace MamadouAlySy\QueryBuilder\Manupulation\Traits;

trait LimitTrait
{
    protected ?int $limit = null;

    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    public function hasLimit(): bool
    {
        return !is_null($this->limit);
    }

    public function renderLimit(): string
    {
        return $this->hasLimit() ? ' LIMIT ' . $this->limit : '';
    }
}