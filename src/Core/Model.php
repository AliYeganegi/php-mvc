<?php
// src/Core/Model.php
namespace App\Core;

use App\Core\Database\QueryBuilder;

abstract class Model
{
    /**
     * The table associated with the model
     * @var string
     */
    protected $table;
    
    /**
     * The primary key
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * Indicates if the model has timestamps
     * @var bool
     */
    protected $timestamps = true;
    
    /**
     * The model's attributes
     * @var array
     */
    protected $attributes = [];
    
    /**
     * The attributes that are mass assignable
     * @var array
     */
    protected $fillable = [];
    
    /**
     * Constructor
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }
    
    /**
     * Get the table name
     * @return string
     */
    public function getTable(): string
    {
        if ($this->table) {
            return $this->table;
        }
        
        // Default table name based on class name (plural)
        // e.g., UserModel => users
        $className = (new \ReflectionClass($this))->getShortName();
        $tableName = strtolower(preg_replace('/Model$/', '', $className));
        return $tableName . 's';
    }
    
    /**
     * Fill the model with attributes
     * @param array $attributes
     * @return $this
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (empty($this->fillable) || in_array($key, $this->fillable)) {
                $this->setAttribute($key, $value);
            }
        }
        
        return $this;
    }
    
    /**
     * Set an attribute
     * @param string $key
     * @param mixed $value
     */
    public function setAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }
    
    /**
     * Get an attribute
     * @param string $key
     * @return mixed|null
     */
    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }
    
    /**
     * Get all attributes
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    
    /**
     * Magic getter
     * @param string $key
     * @return mixed|null
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }
    
    /**
     * Magic setter
     * @param string $key
     * @param mixed $value
     */
    public function __set(string $key, $value): void
    {
        $this->setAttribute($key, $value);
    }
    
    /**
     * Check if an attribute exists
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }
    
    /**
     * Create a new query builder instance for the model
     * @return QueryBuilder
     */
    public function newQuery(): QueryBuilder
    {
        return new QueryBuilder($this->getTable());
    }
    
    /**
     * Find a model by its primary key
     * @param int|string $id
     * @return static|null
     */
    public static function find($id): ?self
    {
        $instance = new static();
        $result = $instance->newQuery()->whereEqual($instance->primaryKey, $id)->first();
        
        return $result ? (new static())->fill($result) : null;
    }
    
    /**
     * Get all models
     * @return array
     */
    public static function all(): array
    {
        $instance = new static();
        $results = $instance->newQuery()->get();
        
        return array_map(fn($attributes) => (new static())->fill($attributes), $results);
    }
    
    /**
     * Create a new model with attributes
     * @param array $attributes
     * @return static
     */
    public static function create(array $attributes): self
    {
        $instance = new static($attributes);
        $instance->save();
        
        return $instance;
    }
    
    /**
     * Save the model
     * @return bool
     */
    public function save(): bool
    {
        $attributes = $this->getAttributes();
        
        // Add timestamps if needed
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            
            if (empty($this->attributes[$this->primaryKey])) {
                $attributes['created_at'] = $now;
            }
            
            $attributes['updated_at'] = $now;
        }
        
        // If we have an ID, update the record
        if (!empty($this->attributes[$this->primaryKey])) {
            $id = $this->attributes[$this->primaryKey];
            unset($attributes[$this->primaryKey]); // Don't update the ID
            
            $affected = $this->newQuery()
                ->whereEqual($this->primaryKey, $id)
                ->update($attributes);
                
            return $affected > 0;
        }
        
        // Otherwise, insert a new record
        $id = $this->newQuery()->insert($attributes);
        
        if ($id) {
            $this->setAttribute($this->primaryKey, $id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete the model
     * @return bool
     */
    public function delete(): bool
    {
        if (empty($this->attributes[$this->primaryKey])) {
            return false;
        }
        
        $affected = $this->newQuery()
            ->whereEqual($this->primaryKey, $this->attributes[$this->primaryKey])
            ->delete();
            
        return $affected > 0;
    }
    
    /**
     * Start a where query
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return QueryBuilder
     */
    public static function where(string $column, string $operator, $value): QueryBuilder
    {
        $instance = new static();
        return $instance->newQuery()->where($column, $operator, $value);
    }
}