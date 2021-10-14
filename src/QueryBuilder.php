<?php

declare (strict_types = 1);

namespace MamadouAlySy;

use MamadouAlySy\Exceptions\QueryBuilderException;
use MamadouAlySy\Interfaces\ConnectionInterface;
use PDO;
use PhpParser\Node\Expr\Cast\Double;
use stdClass;

class QueryBuilder
{
    protected string $type;
    protected string $table;
    protected array $data = [];
    protected array $fields = [];
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected string $dropType = '';
    protected array $conditions = [];
    protected ?string $currentField = null;
    protected ?ConnectionInterface $connection;

    public function __construct(?ConnectionInterface $connection = null)
    {
        $this->connection = $connection;
    }

    public function setConnection(?ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function create(): self
    {
        $this->type = 'create';

        return $this;
    }

    public function drop(): self
    {
        $this->type = 'drop';

        return $this;
    }

    protected function type(string $type, ?int $limit = null): self
    {
        $type = strtoupper($type);
        $type .= $limit ? "($limit)" : '';

        $this->fields[$this->currentField] = [
            'type' => $type,
            'behavior' => [],
        ];

        return $this;
    }

    protected function behavior(string $behavior): self
    {
        $this->fields[$this->currentField]['behavior'][]= $behavior;

        return $this;
    }

    public function field(string $name, string $type = 'varchar', ?int $length = null, string $behavior = ''): self
    {
        $this->currentField = $name;
        $this->type($type, $length)->behavior($behavior);

        return $this;
    }

    public function int(int $length = 11): self
    {
        return $this->type('int', $length);
    }

    public function double(int $length = 11): self
    {
        return $this->type('double', $length);
    }

    public function string(int $length = 255): self
    {
        return $this->type('varchar', $length);
    }

    public function text(): self
    {
        return $this->type('text');
    }

    public function date(): self
    {
        return $this->type('date');
    }

    public function datetime(): self
    {
        return $this->type('datetime');
    }

    public function timestamp(): self
    {
        return $this->type('timestamp');
    }

    public function primaryKey(): self
    {
        return $this->behavior("PRIMARY KEY");
    }

    public function notNull(): self
    {
        return $this->behavior('NOT NULL');
    }

    public function default(string|int|double $default): self
    {
        $this->data[$this->currentField] = $default;
        return $this->behavior('DEFAULT :' . $this->currentField);
    }

    public function autoIncrement(): self
    {
        return $this->behavior('AUTO_INCREMENT');
    }

    public function select(...$args) : self
    {
        $this->type   = 'select';
        $this->fields = func_get_args();

        return $this;
    }

    public function everything(): self
    {
        $this->fields = [];

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
        $this->table = "`$table`";

        return $this;
    }

    public function into(string $table): self
    {
        return $this->from($table);
    }

    public function table(string $table): self
    {
        $this->from($table);
        $this->dropType = 'table';

        return $this;
    }

    public function database(string $table): self
    {
        $this->from($table);
        $this->dropType = 'database';

        return $this;
    }

    public function where(string $field, string $type = 'and'): self
    {
        $type = strtoupper($type);
        $this->currentField = $field;
        $this->conditions[$field]['condition-type'] = $type;

        return $this;
    }

    public function orWhere(string $field): self
    {
        return $this->where($field, 'or');
    }

    private function operation(string $operator, mixed $value): self
    {
        $field = $this->currentField;
        $this->data["c$field"] = $value;
        $this->conditions[$field]['value'] = $value;
        $this->conditions[$field]['operator'] = $operator;

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

    public function limit(?int $value): self
    {
        $this->limit = $value;

        return $this;
    }

    public function offset(?int $value): self
    {
        $this->offset = $value;

        return $this;
    }

    /**
     * @throws QueryBuilderException
     */
    public function commit(): bool
    {
        $this->verifyConnection();
        $query = $this->connection->open()->prepare($this->getSql());
        return $query->execute($this->getData());
    }

    /**
     * @throws QueryBuilderException
     */
    public function get(string $class = stdClass::class, bool $one = false): array|object
    {
        $this->verifyConnection();
        $query = $this->connection->open()->prepare($this->getSql());
        $this->reset();

        if ($query == false) {
            throw new QueryBuilderException("Unable to prepare the sql query");
        }
        
        if ($query->execute($this->getData())) {
            $query->setFetchMode(PDO::FETCH_CLASS, $class);
            return $one ? $query->fetch() : $query->fetchAll();
        }

        return [];
    }

    /**
     * @throws QueryBuilderException
     */
    public function first(string $class = stdClass::class): object
    {
        return $this->get($class, true);
    }


    public function getSql(): string
    {
        $sql = match (true) {
            $this->type === 'create' => $this->getCreateSqlQuery(),
            $this->type === 'insert' => $this->getInsertSqlQuery(),
            $this->type === 'select' => $this->getSelectSqlQuery(),
            $this->type === 'update' => $this->getUpdateSqlQuery(),
            $this->type === 'delete' => $this->getDeleteSqlQuery(),
            $this->type === 'drop' => $this->getDropSqlQuery(),
            default => ''
        };

        return trim($sql) . ';';
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getCreateSqlQuery(): string
    {
        $table  = $this->table;
        $sql = "CREATE TABLE $table(";
        
        foreach ($this->fields as $name => $params) {
            $type = $params['type'];
            $sql .= "$name $type " . implode(' ', $params['behavior']) . ", ";
        }

        return trim($sql, ', ') . ')';
    }

    public function getDropSqlQuery(): string
    {
        $table  = $this->table;
        $type = strtoupper($this->dropType);
        return "DROP $type IF EXISTS $table";
    }

    protected function getInsertSqlQuery(): string
    {
        $table  = $this->table;
        $fields = implode('`, `', $this->fields);
        $values = ':' . implode(', :', $this->fields);

        return "INSERT INTO $table(`$fields`) VALUES($values)";
    }

    protected function getSelectSqlQuery(): string
    {
        $table      = $this->table;
        $fields     = empty($this->fields) ? '*' : implode('`, `', $this->fields);
        $conditions = $this->buildConditions();

        return "SELECT $fields FROM $table $conditions";
    }

    protected function getUpdateSqlQuery(): string
    {
        $table   = $this->table;
        $updates = '';

        foreach ($this->fields as $key) {
            $updates .= "$key = :$key, ";
        }

        $updates    = rtrim($updates, ', ');
        $conditions = $this->buildConditions();

        return "UPDATE $table SET $updates $conditions";
    }

    protected function getDeleteSqlQuery(): string
    {
        $table      = $this->table;
        $conditions = $this->buildConditions();

        return "DELETE FROM $table $conditions";
    }

    protected function buildConditions(): string
    {
        $sql = '';

        if (!empty($this->conditions)) {
            $queryString = '';
            $conditions  = $this->conditions;
            foreach ($conditions as $field => $params) {
                $type     = $params['condition-type'];
                $operator = $params['operator'];

                $queryString .= " $type $field $operator :c$field";
            }
            $queryString = trim($queryString, " AND ");
            $queryString = trim($queryString, " OR ");

            $sql .= " WHERE $queryString";
        }

        // TODO: GROUPING
        // TODO: ORDER
        // TODO: LIMIT

        $sql .= $this->limit ? ' LIMIT ' . $this->limit : '';
        $sql .= $this->offset ? ' OFFSET ' . $this->offset : '';

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
        $this->dropType     = '';
    }

    public function getQuery(): Query
    {
        return new Query(
            $this->getSql(),
            $this->getData()
        );
    }

    /**
     * @throws QueryBuilderException
     */
    public function verifyConnection(): void
    {
        if ($this->connection === null) {
            throw new QueryBuilderException("Connection required!");
        }
    }
}
