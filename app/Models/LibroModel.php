<?php namespace App\Models;

use CodeIgniter\Model;

class LibroModel extends Model
{
    protected $table = 'libro';
    protected $primaryKey = 'id';
    protected $allowedFields = ['titulo', 'descripcion', 'anio_publicacion', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $returnType = 'array';

    // Relación muchos a muchos con Autores
    public function autores($libroId)
    {
        return $this->db->table('libro_autor')
            ->select('autor.*')
            ->join('autor', 'autor.id = libro_autor.autor_id')
            ->where('libro_autor.libro_id', $libroId)
            ->get()
            ->getResultArray();
    }

    // Relación muchos a muchos con Categorías
    public function categorias($libroId)
    {
        return $this->db->table('libro_categoria')
            ->select('categoria.*')
            ->join('categoria', 'categoria.id = libro_categoria.categoria_id')
            ->where('libro_categoria.libro_id', $libroId)
            ->get()
            ->getResultArray();
    }

    // Añadir autores a un libro
    public function addAutores($libroId, $autores)
    {
        $data = [];
        foreach ($autores as $autorId) {
            $data[] = [
                'libro_id' => $libroId,
                'autor_id' => $autorId,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        return $this->db->table('libro_autor')->insertBatch($data);
    }

    // Añadir categorías a un libro
    public function addCategorias($libroId, $categorias)
    {
        $data = [];
        foreach ($categorias as $categoriaId) {
            $data[] = [
                'libro_id' => $libroId,
                'categoria_id' => $categoriaId,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        return $this->db->table('libro_categoria')->insertBatch($data);
    }

    // Eliminar relaciones con autores
    public function removeAutores($libroId)
    {
        return $this->db->table('libro_autor')->where('libro_id', $libroId)->delete();
    }

    // Eliminar relaciones con categorías
    public function removeCategorias($libroId)
    {
        return $this->db->table('libro_categoria')->where('libro_id', $libroId)->delete();
    }

    // Eliminar un autor específico de un libro
    public function removeAutor($libroId, $autorId)
    {
        return $this->db->table('libro_autor')
            ->where('libro_id', $libroId)
            ->where('autor_id', $autorId)
            ->delete();
    }

    // Eliminar una categoría específica de un libro
    public function removeCategoria($libroId, $categoriaId)
    {
        return $this->db->table('libro_categoria')
            ->where('libro_id', $libroId)
            ->where('categoria_id', $categoriaId)
            ->delete();
    }

    // Obtener todos los libros con sus autores y categorías
    public function getAllLibros()
    {
        $libros = $this->findAll();
        
        foreach ($libros as &$libro) {
            $libro['autores'] = $this->autores($libro['id']);
            $libro['categorias'] = $this->categorias($libro['id']);
        }
        
        return $libros;
    }
}