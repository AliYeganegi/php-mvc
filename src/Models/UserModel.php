<?php
// src/Models/UserModel.php
namespace App\Models;

use App\Core\Model;

class UserModel extends Model
{
    // Optional: Specify table name if different from conventional naming
    protected $table = 'users';
    
    // Specify which fields can be mass-assigned
    protected $fillable = ['name', 'email', 'password'];
    
    /**
     * Find a user by email
     * @param string $email
     * @return static|null
     */
    public static function findByEmail(string $email): ?self
    {
        $instance = new static();
        $result = $instance->newQuery()->whereEqual('email', $email)->first();
        
        return $result ? (new static())->fill($result) : null;
    }
    
    /**
     * Check if the provided password matches the stored hash
     * @param string $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }
    
    /**
     * Set the password (with automatic hashing)
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->setAttribute('password', password_hash($password, PASSWORD_DEFAULT));
    }
}