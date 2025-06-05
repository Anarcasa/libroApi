<?php namespace App\Models;

use CodeIgniter\Model;

class AutorModel extends Model
{
    protected $table = 'autor';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Obtener o crear el autor por defecto
    public function getDefaultAutor()
    {
        $autor = $this->where('nombre', 'No especificado')->first();
        
        if (!$autor) {
            $id = $this->insert([
                'nombre' => 'No especificado'
            ]);
            return $id;
        }
        
        return $autor['id'];
    }
}