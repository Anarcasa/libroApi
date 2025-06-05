<?php namespace App\Controllers;

use App\Models\LibroModel;
use App\Models\AutorModel;
use App\Models\CategoriaModel;
use CodeIgniter\API\ResponseTrait;

class Libro extends BaseController
{
    use ResponseTrait;

    protected $libroModel;
    protected $autorModel;
    protected $categoriaModel;

    public function __construct()
    {
        $this->libroModel = new LibroModel();
        $this->autorModel = new AutorModel();
        $this->categoriaModel = new CategoriaModel();
    }

    // Listar todos los libros
    public function index()
    {
        $libros = $this->libroModel->findAll();
        
        foreach ($libros as &$libro) {
            $libro['autores'] = $this->libroModel->autores($libro['id']);
            $libro['categorias'] = $this->libroModel->categorias($libro['id']);
        }
        
        return $this->respond($libros);
    }

    // Crear un nuevo libro
    public function create()
    {
        $rules = [
            'titulo' => 'required|min_length[3]|max_length[255]',
            'descripcion' => 'permit_empty|string',
            'anio_publicacion' => 'permit_empty|numeric|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = [
            'titulo' => $this->request->getVar('titulo'),
            'descripcion' => $this->request->getVar('descripcion'),
            'anio_publicacion' => $this->request->getVar('anio_publicacion')
        ];

        try {
            // Crear el libro
            $libroId = $this->libroModel->insert($data);
            
            // Obtener o crear el autor por defecto
            $defaultAutorId = $this->autorModel->getDefaultAutor();
            
            // Obtener o crear la categoría por defecto
            $defaultCategoriaId = $this->categoriaModel->getDefaultCategoria();
            
            // Asignar autor y categoría por defecto
            $this->libroModel->addAutores($libroId, [$defaultAutorId]);
            $this->libroModel->addCategorias($libroId, [$defaultCategoriaId]);
            
            // Obtener el libro con sus relaciones
            $libro = $this->libroModel->find($libroId);
            $libro['autores'] = $this->libroModel->autores($libroId);
            $libro['categorias'] = $this->libroModel->categorias($libroId);
            
            return $this->respondCreated($libro);
        } catch (\Exception $e) {
            return $this->failServerError('Error al crear el libro: ' . $e->getMessage());
        }
    }

    // Crear un libro con autor y categoría
    public function createWithRelations()
    {
        $rules = [
            'titulo' => 'required|min_length[3]|max_length[255]',
            'descripcion' => 'permit_empty|string',
            'anio_publicacion' => 'permit_empty|numeric|greater_than[0]',
            'autor_id' => 'required|numeric|greater_than[0]', // autor_id siempre requerido
            'categoria_id' => 'permit_empty|numeric|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Verificar si el autor y la categoría existen
        $autorId = $this->request->getVar('autor_id');
        $categoriaId = $this->request->getVar('categoria_id');

        if (!$this->autorModel->find($autorId)) {
            return $this->failNotFound('Autor no encontrado');
        }

        if (!$this->categoriaModel->find($categoriaId)) {
            return $this->failNotFound('Categoría no encontrada');
        }

        // Crear el libro
        $data = [
            'titulo' => $this->request->getVar('titulo'),
            'descripcion' => $this->request->getVar('descripcion'),
            'anio_publicacion' => $this->request->getVar('anio_publicacion')
        ];

        $libroId = $this->libroModel->insert($data);
        
        // Agregar autor y categoría
        $this->libroModel->addAutores($libroId, [$autorId]);
        $this->libroModel->addCategorias($libroId, [$categoriaId]);
        
        // Obtener el libro con sus relaciones
        $libro = $this->libroModel->find($libroId);
        $libro['autores'] = $this->libroModel->autores($libroId);
        $libro['categorias'] = $this->libroModel->categorias($libroId);
        
        return $this->respondCreated($libro);
    }

    // Obtener un libro específico
    public function show($id = null)
    {
        $libro = $this->libroModel->find($id);
        
        if (!$libro) {
            return $this->failNotFound('Libro no encontrado');
        }
        
        $libro['autores'] = $this->libroModel->autores($id);
        $libro['categorias'] = $this->libroModel->categorias($id);
        
        return $this->respond($libro);
    }

    // Actualizar un libro
    public function update($id = null)
    {
        $libro = $this->libroModel->find($id);
        
        if (!$libro) {
            return $this->failNotFound('Libro no encontrado');
        }

        $rules = [
            'titulo' => 'permit_empty|min_length[3]|max_length[255]',
            'descripcion' => 'permit_empty|string',
            'anio_publicacion' => 'permit_empty|numeric|greater_than[0]',
            'autores' => 'permit_empty|is_array',
            'categorias' => 'permit_empty|is_array'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = [
            'titulo' => $this->request->getVar('titulo') ?? $libro['titulo'],
            'descripcion' => $this->request->getVar('descripcion') ?? $libro['descripcion'],
            'anio_publicacion' => $this->request->getVar('anio_publicacion') ?? $libro['anio_publicacion']
        ];

        $this->libroModel->update($id, $data);
        
        // Actualizar relaciones si se proporcionaron
        $autores = $this->request->getVar('autores');
        if ($autores !== null) {
            $this->libroModel->removeAutores($id);
            if (!empty($autores)) {
                $this->libroModel->addAutores($id, $autores);
            }
        }
        
        $categorias = $this->request->getVar('categorias');
        if ($categorias !== null) {
            $this->libroModel->removeCategorias($id);
            if (!empty($categorias)) {
                $this->libroModel->addCategorias($id, $categorias);
            }
        }
        
        // Obtener el libro actualizado con sus relaciones
        $libro = $this->libroModel->find($id);
        $libro['autores'] = $this->libroModel->autores($id);
        $libro['categorias'] = $this->libroModel->categorias($id);
        
        return $this->respond($libro);
    }

    // Eliminar un libro
    public function delete($id = null)
    {
        $libro = $this->libroModel->find($id);
        
        if (!$libro) {
            return $this->failNotFound('Libro no encontrado');
        }
        
        $this->libroModel->delete($id);
        return $this->respondDeleted(['message' => 'Libro eliminado correctamente']);
    }

    // Obtener autores de un libro
    public function autores($libroId)
    {
        $libro = $this->libroModel->find($libroId);
        
        if (!$libro) {
            return $this->failNotFound('Libro no encontrado');
        }
        
        $autores = $this->libroModel->autores($libroId);
        return $this->respond($autores);
    }

    // Obtener categorías de un libro
    public function categorias($libroId)
    {
        $libro = $this->libroModel->find($libroId);
        
        if (!$libro) {
            return $this->failNotFound('Libro no encontrado');
        }
        
        $categorias = $this->libroModel->categorias($libroId);
        return $this->respond($categorias);
    }

    // Agregar un autor a un libro
    public function addAutor($libroId)
    {
        $libro = $this->libroModel->find($libroId);
        
        if (!$libro) {
            return $this->failNotFound('Libro no encontrado');
        }

        $rules = [
            'autor_id' => 'required|numeric|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $autorId = $this->request->getVar('autor_id');
        
        // Verificar si el autor existe
        if (!$this->autorModel->find($autorId)) {
            return $this->failNotFound('Autor no encontrado');
        }

        // Agregar el autor al libro
        $this->libroModel->addAutores($libroId, [$autorId]);
        
        // Devolver el libro actualizado con sus relaciones
        $libro = $this->libroModel->find($libroId);
        $libro['autores'] = $this->libroModel->autores($libroId);
        $libro['categorias'] = $this->libroModel->categorias($libroId);
        
        return $this->respond($libro);
    }

    // Agregar una categoría a un libro
    public function addCategoria($libroId)
    {
        $libro = $this->libroModel->find($libroId);
        
        if (!$libro) {
            return $this->failNotFound('Libro no encontrado');
        }

        $rules = [
            'categoria_id' => 'required|numeric|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $categoriaId = $this->request->getVar('categoria_id');
        
        // Verificar si la categoría existe
        if (!$this->categoriaModel->find($categoriaId)) {
            return $this->failNotFound('Categoría no encontrada');
        }

        // Agregar la categoría al libro
        $this->libroModel->addCategorias($libroId, [$categoriaId]);
        
        // Devolver el libro actualizado con sus relaciones
        $libro = $this->libroModel->find($libroId);
        $libro['autores'] = $this->libroModel->autores($libroId);
        $libro['categorias'] = $this->libroModel->categorias($libroId);
        
        return $this->respond($libro);
    }

    // Eliminar un autor de un libro
    public function removeAutor($libroId, $autorId)
    {
        $libro = $this->libroModel->find($libroId);
        if (!$libro) {
            return $this->failNotFound('Libro no encontrado');
        }

        if (!$this->autorModel->find($autorId)) {
            return $this->failNotFound('Autor no encontrado');
        }

        $this->libroModel->removeAutor($libroId, $autorId);
        
        // Devolver el libro actualizado
        $libro = $this->libroModel->find($libroId);
        $libro['autores'] = $this->libroModel->autores($libroId);
        $libro['categorias'] = $this->libroModel->categorias($libroId);
        
        return $this->respond($libro);
    }

    // Eliminar una categoría de un libro
    public function removeCategoria($libroId, $categoriaId)
    {
        $libro = $this->libroModel->find($libroId);
        if (!$libro) {
            return $this->failNotFound('Libro no encontrado');
        }

        if (!$this->categoriaModel->find($categoriaId)) {
            return $this->failNotFound('Categoría no encontrada');
        }

        $this->libroModel->removeCategoria($libroId, $categoriaId);
        
        // Devolver el libro actualizado
        $libro = $this->libroModel->find($libroId);
        $libro['autores'] = $this->libroModel->autores($libroId);
        $libro['categorias'] = $this->libroModel->categorias($libroId);
        
        return $this->respond($libro);
    }
}