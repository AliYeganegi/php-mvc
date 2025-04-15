<?php
// src/Core/Model.php
namespace App\Core;

use App\Core\Database\QueryBuilder;

abstract class Model
{
    protected $table;

    protected $primaryKey = 'id';
    
    protected $timestamps = true;
    
    protected $attributes = [];

    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }
    
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
    
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (empty($this->fillable) || in_array($key, $this->fillable)) {
                $this->setAttribute($key, $value);
            }
        }
        
        return $this;
    }

    public function setAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    public function __set(string $key, $value): void
    {
        $this->setAttribute($key, $value);
    }

    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    public function newQuery(): QueryBuilder
    {
        return new QueryBuilder($this->getTable());
    }

    public static function find($id): ?self
    {
        $instance = new static();
        $result = $instance->newQuery()->whereEqual($instance->primaryKey, $id)->first();
        
        return $result ? (new static())->fill($result) : null;
    }

    public static function all(): array
    {
        $instance = new static();
        $results = $instance->newQuery()->get();
        
        return array_map(fn($attributes) => (new static())->fill($attributes), $results);
    }

    public static function create(array $attributes): self
    {
        $instance = new static($attributes);
        $instance->save();
        
        return $instance;
    }

    public function save(): bool
    {
        $attributes = $this->getAttributes();
        
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

    public static function where(string $column, string $operator, $value): QueryBuilder
    {
        $instance = new static();
        return $instance->newQuery()->where($column, $operator, $value);
    }
}