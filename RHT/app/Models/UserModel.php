<?php 

namespace App\Models;  
use CodeIgniter\Model;

  
class UserModel extends Model{

    protected $table = 'users';
    
    protected $allowedFields = [
        'name',
        'email',
        'password',
        'employee_id',
        'role_id',
        'created_at'
    ];

}