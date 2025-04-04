<?php
// src/Core/Database/QueryBuilder.php
namespace App\Core\Database;

class QueryBuilder
{
    /**
     * The table to query
     * @var string
     */
    protected $table;
    
    /**
     * Selected columns
     * @var array
     */
    protected $select = ['*'];
    
    /**
     * Where conditions
     * @var array
     */
    protected $where = [];
    
    /**
     * Order by clauses
     * @var array
     */
    protected $orderBy = [];
    
    /**
     * Limit clause
     * @var int|null
     */
    protected $limit = null;
    
    /**
     * Offset clause
     * @var int|null
     */
    protected $offset = null;
    
    /**
     * Join clauses
     * @var array
     */
    protected $joins = [];
    
    /**
     * Parameter bindings
     * @var array
     */
    protected $bindings = [];
    
    /**
     * Constructor
     * @param string $table The table name
     */
    public function __construct(string $table)
    {
        $this->table = $table;
    }
    
    /**
     * Static factory method
     * @param string $table
     * @return static
     */
    public static function table(string $table): self
    {
        return new static($table);
    }
    
    /**
     * Select specific columns
     * @param array $columns
     * @return $this
     */
    public function select(array $columns): self
    {
        $this->select = $columns;
        return $this;
    }
    
    /**
     * Add a where condition
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return $this
     */
    public function where(string $column, string $operator, $value): self
    {
        $this->where[] = compact('column', 'operator', 'value');
        return $this;
    }
    
    /**
     * Add a where equals condition
     * @param string $column
     * @param mixed $value
     * @return $this
     */
    public function whereEqual(string $column, $value): self
    {
        return $this->where($column, '=', $value);
    }
    
    /**
     * Add an order by clause
     * @param string $column
     * @param string $direction
     * @return $this
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }
        
        $this->orderBy[] = compact('column', 'direction');
        return $this;
    }
    
    /**
     * Add a limit clause
     * @param int $limit
     * @return $this
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }
    
    /**
     * Add an offset clause
     * @param int $offset
     * @return $this
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }
    
    /**
     * Add a join clause
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @param string $type
     * @return $this
     */
    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = compact('table', 'first', 'operator', 'second', 'type');
        return $this;
    }
    
    /**
     * Build the SQL query
     * @return string
     */
    protected function buildQuery(): string
    {
        $query = "SELECT " . implode(', ', $this->select) . " FROM {$this->table}";
        
        // Add joins
        foreach ($this->joins as $join) {
            $query .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }
        
        // Add where conditions
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
        
        // Add order by
        if (!empty($this->orderBy)) {
            $query .= " ORDER BY ";
            $orders = [];
            
            foreach ($this->orderBy as $order) {
                $orders[] = "{$order['column']} {$order['direction']}";
            }
            
            $query .= implode(', ', $orders);
        }
        
        // Add limit and offset
        if ($this->limit !== null) {
            $query .= " LIMIT {$this->limit}";
            
            if ($this->offset !== null) {
                $query .= " OFFSET {$this->offset}";
            }
        }
        
        return $query;
    }
    
    /**
     * Execute the query and get all results
     * @return array
     */
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
    
    /**
     * Execute the query and get the first result
     * @return array|null
     */
    public function first(): ?array
    {
        $this->limit(1);
        $result = $this->get();
        
        return !empty($result) ? $result[0] : null;
    }
    
    /**
     * Insert a new record
     * @param array $data
     * @return int The last insert ID
     */
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
    
    /**
     * Update records
     * @param array $data
     * @return int The number of rows affected
     */
    public function update(array $data): int
    {
        $columns = array_keys($data);
        $sets = array_map(fn($col) => "{$col} = :set_{$col}", $columns);
        
        $query = "UPDATE {$this->table} SET " . implode(', ', $sets);
        
        // Add where conditions
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
        
        // Bind the SET values
        foreach ($data as $column => $value) {
            $statement->bindValue(":set_{$column}", $value);
        }
        
        // Bind the WHERE values
        foreach ($this->bindings as $key => $value) {
            $statement->bindValue(":{$key}", $value);
        }
        
        $statement->execute();
        return $statement->rowCount();
    }
    
    /**
     * Delete records
     * @return int The number of rows affected
     */
    public function delete(): int
    {
        $query = "DELETE FROM {$this->table}";
        
        // Add where conditions
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
    
    /**
     * Execute a raw query
     * @param string $query
     * @param array $bindings
     * @return array
     */
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