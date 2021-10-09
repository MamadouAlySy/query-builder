<?php

declare (strict_types = 1);

namespace MamadouAlySy;

class QueryBuilder
{
    protected string $type;
    protected string $table;
    protected array $data             = [];
    protected array $fields           = [];
    protected array $conditions       = [];
    protected  ? string $currentField = null;
    protected  ? int $limit           = null;
    protected  ? int $offset          = null;

    public function select(...$args) : self
    {
        $this->type   = 'select';
        $this->fields = func_get_args();

        return $this;
    }

    public function insert(array $data) : self
    {
        $this->type   = 'insert';
        $this->fields = array_keys($data);
        $this->data   = $data;

        return $this;

    }

    public function update(array $data) : self
    {
        $this->type   = 'update';
        $this->fields = array_keys($data);
        $this->data   = $data;

        return $this;

    }

    public function delete(): self
    {
        $this->type = 'delete';

        return $this;

    }

    public function from(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    public function into(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    public function where(string $field, string $type = 'and'): self
    {
        $type                                       = strtoupper($type);
        $this->conditions[$field]['condition-type'] = $type;
        $this->currentField                         = $field;

        return $this;
    }

    public function orWhere(string $field): self
    {
        return $this->where($field, 'or');
    }

    private function operation(string $operator, mixed $value): self
    {
        $field                                = $this->currentField;
        $this->conditions[$field]['operator'] = $operator;
        $this->conditions[$field]['value']    = $value;
        $this->data["c$field"]                = $value;

        return $this;
    }

    public function equal(mixed $value): self
    {
        return $this->operation('=', $value);
    }

    public function different(mixed $value): self
    {
        return $this->operation('!=', $value);
    }

    public function greaterThan(mixed $value): self
    {
        return $this->operation('>', $value);
    }

    public function lowerThan(mixed $value): self
    {
        return $this->operation('<', $value);
    }

    public function greaterThanAndEqualTo(mixed $value): self
    {
        return $this->operation('>=', $value);
    }

    public function lowerThanAndEqualTo(mixed $value): self
    {
        return $this->operation('<=', $value);
    }

    public function limit(int $value): self
    {
        $this->limit = $value;

        return $this;
    }

    public function offset(int $value): self
    {
        $this->offset = $value;

        return $this;
    }

    public function getQuery(): Query
    {
        return new Query(
            $this->getSql(),
            $this->getData()
        );
    }

    public function getSql()
    {
        $sql = match(true) {
            $this->type === 'insert' => $this->getInsertSqlQuery(),
            $this->type === 'select' => $this->getSelectSqlQuery(),
            $this->type === 'update' => $this->getUpdateSqlQuery(),
            $this->type === 'delete' => $this->getDeleteSqlQuery(),
        default=> ''
        };

        return trim($sql) . ';';
    }

    public function getData()
    {
        return $this->data;
    }

    protected function getInsertSqlQuery(): string
    {
        $table  = $this->table;
        $fields = implode('`, `', $this->fields);
        $values = ':' . implode(', :', $this->fields);

        return "INSERT INTO {$table}(`{$fields}`) VALUES($values)";
    }

    protected function getSelectSqlQuery(): string
    {
        $table      = $this->table;
        $fields     = empty($this->fields) ? '*' : implode('`, `', $this->fields);
        $conditions = $this->buildConditions();

        return "SELECT {$fields} FROM {$table} {$conditions}";
    }

    protected function getUpdateSqlQuery(): string
    {
        $table   = $this->table;
        $updates = '';

        foreach ($this->fields as $key) {
            $updates .= "{$key} = :{$key}, ";
        }

        $updates    = rtrim($updates, ', ');
        $conditions = $this->buildConditions();

        return "UPDATE {$table} SET {$updates} {$conditions}";
    }

    protected function getDeleteSqlQuery(): string
    {
        $table      = $this->table;
        $conditions = $this->buildConditions();

        return "DELETE FROM {$table} {$conditions}";
    }

    protected function buildConditions(): string
    {
        $sql = '';
        // WHERE
        if (!empty($this->conditions)) {
            $conditions  = $this->conditions;
            $queryString = '';
            foreach ($conditions as $field => $params) {
                $type     = $params['condition-type'];
                $operator = $params['operator'];

                $queryString .= " {$type} {$field} {$operator} :c{$field}";
            }
            $queryString = trim($queryString, " AND ");
            $queryString = trim($queryString, " OR ");

            $sql .= " WHERE {$queryString}";
        }

        // GROUPING
        // ORDER
        // LIMIT
        if ($this->limit != null) {
            $sql .= ' LIMIT ' . $this->limit;
        }
        // OFFSET
        if ($this->offset != null) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        return trim($sql);
    }

    public function reset()
    {
        $this->data         = [];
        $this->fields       = [];
        $this->conditions   = [];
        $this->currentField = null;
        $this->limit        = null;
        $this->offset       = null;
    }
}
