<?php namespace App\Controllers;

use App\Models\CategoriaModel;
use CodeIgniter\API\ResponseTrait;

class Categoria extends BaseController
{
    use ResponseTrait;

    protected $categoriaModel;

    public function __construct()
    {
        $this->categoriaModel = new CategoriaModel();
    }

    // Listar todas las categorías
    public function index()
    {
        $categorias = $this->categoriaModel->findAll();
        return $this->respond($categorias);
    }

    // Crear una nueva categoría
    public function create()
    {
        $rules = [
            'nombre' => 'required|min_length[3]|max_length[255]'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = [
            'nombre' => $this->request->getVar('nombre')
        ];

        $categoriaId = $this->categoriaModel->insert($data);
        $categoria = $this->categoriaModel->find($categoriaId);
        
        return $this->respondCreated($categoria);
    }

    // Obtener una categoría específica
    public function show($id = null)
    {
        $categoria = $this->categoriaModel->find($id);
        
        if (!$categoria) {
            return $this->failNotFound('Categoría no encontrada');
        }
        
        return $this->respond($categoria);
    }

    // Actualizar una categoría
    public function update($id = null)
    {
        $categoria = $this->categoriaModel->find($id);
        
        if (!$categoria) {
            return $this->failNotFound('Categoría no encontrada');
        }

        $rules = [
            'nombre' => 'required|min_length[3]|max_length[255]'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = [
            'nombre' => $this->request->getVar('nombre')
        ];

        $this->categoriaModel->update($id, $data);
        $categoria = $this->categoriaModel->find($id);
        
        return $this->respond($categoria);
    }

    // Eliminar una categoría
    public function delete($id = null)
    {
        $categoria = $this->categoriaModel->find($id);
        
        if (!$categoria) {
            return $this->failNotFound('Categoría no encontrada');
        }

        // Verificar si la categoría tiene libros asociados
        $librosAsociados = $this->categoriaModel->libros($id);
        if (!empty($librosAsociados)) {
            return $this->fail('No se puede eliminar la categoría porque tiene libros asociados', 409);
        }

        $this->categoriaModel->delete($id);
        return $this->respondDeleted(['message' => 'Categoría eliminada correctamente']);
    }

    // Obtener libros de una categoría
    public function libros($categoriaId)
    {
        $categoria = $this->categoriaModel->find($categoriaId);
        
        if (!$categoria) {
            return $this->failNotFound('Categoría no encontrada');
        }
        
        $libros = $this->categoriaModel->libros($categoriaId);
        return $this->respond($libros);
    }
}