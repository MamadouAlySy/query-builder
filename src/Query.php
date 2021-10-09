<?php

declare(strict_types=1);

namespace MamadouAlySy;

class Query
{
    protected string $sql;
    protected array $parameters;

    public function __construct(string $sql, array $parameters = [])
    {
        $this->sql = $sql;
        $this->parameters = $parameters;
    }

    /**
     * Get the value of parameters
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Set the value of parameters
     *
     * @param array $parameters
     *
     * @return self
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Get the value of sql
     *
     * @return string
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * Set the value of sql
     *
     * @param string $sql
     *
     * @return self
     */
    public function setSql(string $sql): self
    {
        $this->sql = $sql;

        return $this;
    }
}
