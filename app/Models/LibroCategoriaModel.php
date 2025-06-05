<?php namespace App\Models;

use CodeIgniter\Model;

class LibroCategoriaModel extends Model
{
    protected $table = 'libro_categoria';
    protected $primaryKey = ['libro_id', 'categoria_id'];
    protected $allowedFields = ['libro_id', 'categoria_id', 'created_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = ''; // No usamos updated_at en esta tabla
}