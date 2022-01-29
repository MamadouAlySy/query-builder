<?php

declare(strict_types=1);

namespace MamadouAlySy\QueryBuilder\Manupulation\Traits;

trait WhereTrait
{
    protected array $wereConditions = [];

    public function where(string $field, string $operator, mixed $value, string $type = 'AND'): static
    {
        array_push($this->wereConditions, [$type, $field, $operator, $value]);

        return $this;
    }

    public function orWhere(string $field, string $operator, mixed $value, string $type = 'AND'): static
    {
        return $this->where($field, $operator, $value, 'OR');
    }

    protected function hasWhere(): bool
    {
        return !empty($this->wereConditions);
    }

    protected function renderWhere(): string
    {
        if ($this->hasWhere()) {

            $conditionSql = '';

            foreach ($this->wereConditions as $condition) {
                $conditionSql .= implode(' ', $condition) . ' ';
            }

            $conditionSql = ltrim($conditionSql, 'AND');
            $conditionSql = ltrim($conditionSql, 'OR');
            $conditionSql = rtrim($conditionSql, ' ');

            return ' WHERE' . $conditionSql;
        }

        return '';
    }
}