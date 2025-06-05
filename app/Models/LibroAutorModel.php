<?php namespace App\Models;

use CodeIgniter\Model;

class LibroAutorModel extends Model
{
    protected $table = 'libro_autor';
    protected $primaryKey = ['libro_id', 'autor_id'];
    protected $allowedFields = ['libro_id', 'autor_id', 'created_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = ''; // No usamos updated_at en esta tabla
}