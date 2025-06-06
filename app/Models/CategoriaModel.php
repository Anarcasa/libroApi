<?php namespace App\Models;

use CodeIgniter\Model;

class CategoriaModel extends Model
{
    protected $table = 'categoria';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Obtener o crear la categoría por defecto
    public function getDefaultCategoria()
    {
        $categoria = $this->where('nombre', 'Sin clasificar')->first();
        
        if (!$categoria) {
            $id = $this->insert([
                'nombre' => 'Sin clasificar'
            ]);
            return $id;
        }
        
        return $categoria['id'];
    }

    // Relación muchos a muchos con Libros
    public function libros($categoriaId)
    {
        return $this->db->table('libro_categoria')
            ->select('libro.*')
            ->join('libro', 'libro.id = libro_categoria.libro_id')
            ->where('libro_categoria.categoria_id', $categoriaId)
            ->get()
            ->getResultArray();
    }
}