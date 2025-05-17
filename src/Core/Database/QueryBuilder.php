<?php

namespace App\Core\Database;

class QueryBuilder
{
    protected $table;
    
    protected $select = ['*'];

    protected $where = [];

    protected $orderBy = [];

    protected $limit = null;

    protected $offset = null;

    protected $joins = [];

    protected $bindings = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }
    
    public static function table(string $table): self
    {
        return new static($table);
    }

    public function select(array $columns): self
    {
        $this->select = $columns;
        return $this;
    }
    
    public function where(string $column, string $operator, $value): self
    {
        $this->where[] = compact('column', 'operator', 'value');
        return $this;
    }

    public function whereEqual(string $column, $value): self
    {
        return $this->where($column, '=', $value);
    }
    
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }
        
        $this->orderBy[] = compact('column', 'direction');
        return $this;
    }
    
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }
    
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }
    
    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = compact('table', 'first', 'operator', 'second', 'type');
        return $this;
    }

    protected function buildQuery(): string
    {
        $query = "SELECT " . implode(', ', $this->select) . " FROM {$this->table}";
        
        foreach ($this->joins as $join) {
            $query .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }
        
        if (!empty($this->where)) {
            $query .= " WHERE ";
            $conditions = [];
            
            foreach ($this->where as $index => $condition) {
                $paramName = "where_{$index}";
                $conditions[] = "{$condition['column']} {$condition['operator']} :{$paramName}";
                $this->bindings[$paramName] = $condition['value'];
            }
            
            $query .= implode(' AND ', $conditions);
        }
        
        if (!empty($this->orderBy)) {
            $query .= " ORDER BY ";
            $orders = [];
            
            foreach ($this->orderBy as $order) {
                $orders[] = "{$order['column']} {$order['direction']}";
            }
            
            $query .= implode(', ', $orders);
        }
        
        if ($this->limit !== null) {
            $query .= " LIMIT {$this->limit}";
            
            if ($this->offset !== null) {
                $query .= " OFFSET {$this->offset}";
            }
        }
        
        return $query;
    }

    public function get(): array
    {
        $query = $this->buildQuery();
        $statement = Connection::getPDO()->prepare($query);
        
        foreach ($this->bindings as $key => $value) {
            $statement->bindValue(":{$key}", $value);
        }
        
        $statement->execute();
        return $statement->fetchAll();
    }
    
    public function first(): ?array
    {
        $this->limit(1);
        $result = $this->get();
        
        return !empty($result) ? $result[0] : null;
    }

    public function insert(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);
        
        $query = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $statement = Connection::getPDO()->prepare($query);
        
        foreach ($data as $column => $value) {
            $statement->bindValue(":{$column}", $value);
        }
        
        $statement->execute();
        return (int) Connection::getPDO()->lastInsertId();
    }
    
    public function update(array $data): int
    {
        $columns = array_keys($data);
        $sets = array_map(fn($col) => "{$col} = :set_{$col}", $columns);
        
        $query = "UPDATE {$this->table} SET " . implode(', ', $sets);
        
        if (!empty($this->where)) {
            $query .= " WHERE ";
            $conditions = [];
            
            foreach ($this->where as $index => $condition) {
                $paramName = "where_{$index}";
                $conditions[] = "{$condition['column']} {$condition['operator']} :{$paramName}";
                $this->bindings[$paramName] = $condition['value'];
            }
            
            $query .= implode(' AND ', $conditions);
        }
        
        $statement = Connection::getPDO()->prepare($query);
        
        foreach ($data as $column => $value) {
            $statement->bindValue(":set_{$column}", $value);
        }
        
        foreach ($this->bindings as $key => $value) {
            $statement->bindValue(":{$key}", $value);
        }
        
        $statement->execute();
        return $statement->rowCount();
    }

    public function delete(): int
    {
        $query = "DELETE FROM {$this->table}";
        
        if (!empty($this->where)) {
            $query .= " WHERE ";
            $conditions = [];
            
            foreach ($this->where as $index => $condition) {
                $paramName = "where_{$index}";
                $conditions[] = "{$condition['column']} {$condition['operator']} :{$paramName}";
                $this->bindings[$paramName] = $condition['value'];
            }
            
            $query .= implode(' AND ', $conditions);
        }
        
        $statement = Connection::getPDO()->prepare($query);
        
        foreach ($this->bindings as $key => $value) {
            $statement->bindValue(":{$key}", $value);
        }
        
        $statement->execute();
        return $statement->rowCount();
    }

    public static function raw(string $query, array $bindings = []): array
    {
        $statement = Connection::getPDO()->prepare($query);
        
        foreach ($bindings as $key => $value) {
            $statement->bindValue(is_int($key) ? $key + 1 : ":{$key}", $value);
        }
        
        $statement->execute();
        return $statement->fetchAll();
    }
}