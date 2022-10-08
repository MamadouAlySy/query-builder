<?php

declare(strict_types=1);

namespace MamadouAlySy\QueryBuilder\Manipulation\Traits;

trait WhereTrait
{
    protected ?array $conditions = null;
    protected array $conditionValues = [];

    public function where(string $field, string $operator, mixed $value, string $type = 'AND'): static
    {
        $this->conditions[] = [$type, $field, $operator, '?'];
        $this->conditionValues[] = $value;

        return $this;
    }

    public function orWhere(string $field, string $operator, mixed $value): static
    {
        return $this->where($field, $operator, $value, 'OR');
    }

    protected function getWhereStatement(): string
    {
        if ($this->hasWhereStatement()) {
            $sql = "";

            foreach ($this->conditions as $condition) {
                $sql .= implode(' ', $condition) . " ";
            }

            $sql = trim($sql, 'AND ');
            $sql = trim($sql, 'OR ');
            $sql = trim($sql);

            return " WHERE " . $sql;
        }
        return "";
    }

    protected function hasWhereStatement(): bool
    {
        return $this->conditions !== null && !empty($this->conditions);
    }

    protected function getConditionValues(): array
    {
        return $this->conditionValues;
    }
}
