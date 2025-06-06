<?php namespace App\Controllers;

use App\Models\AutorModel;
use CodeIgniter\API\ResponseTrait;

class Autor extends BaseController
{
    use ResponseTrait;

    protected $autorModel;

    public function __construct()
    {
        $this->autorModel = new AutorModel();
    }

    // Listar todos los autores
    public function index()
    {
        $autores = $this->autorModel->findAll();
        return $this->respond($autores);
    }

       // Obtener un autor específico
    public function show($id = null)
    {
        $autor = $this->autorModel->find($id);
        
        if (!$autor) {
            return $this->failNotFound('Autor no encontrado');
        }
        
        return $this->respond($autor);
    }

    // Crear un nuevo autor
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

        $autorId = $this->autorModel->insert($data);
        $autor = $this->autorModel->find($autorId);
        
        return $this->respondCreated($autor);
    }

    // Actualizar un autor
    public function update($id = null)
    {
        $autor = $this->autorModel->find($id);
        
        if (!$autor) {
            return $this->failNotFound('Autor no encontrado');
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

        $this->autorModel->update($id, $data);
        $autor = $this->autorModel->find($id);
        
        return $this->respond($autor);
    }

    // Eliminar un autor
    public function delete($id = null)
    {
        $autor = $this->autorModel->find($id);
        
        if (!$autor) {
            return $this->failNotFound('Autor no encontrado');
        }

        // Verificar si el autor tiene libros asociados
        $librosAsociados = $this->autorModel->libros($id);
        if (!empty($librosAsociados)) {
            return $this->fail('No se puede eliminar el autor porque tiene libros asociados', 409);
        }

        $this->autorModel->delete($id);
        return $this->respondDeleted(['message' => 'Autor eliminado correctamente']);
    }

    // Obtener libros de un autor
    public function libros($autorId)
    {
        $autor = $this->autorModel->find($autorId);
        
        if (!$autor) {
            return $this->failNotFound('Autor no encontrado');
        }
        
        $libros = $this->autorModel->libros($autorId);
        return $this->respond($libros);
    }


     public function index()
    {
        $data = $this->CosasModel->findAll();
        return $this->respond($data);
    }

    public function show($id = null)
    {
        if ($id === null) {
            return $this->failNotFound('No se proporcionó ID');
        }

        $data = $this->CosasModel->find($id);
        if ($data === null) {
            return $this->failNotFound('No existe el registro con id: ' . $id);
        }

        return $this->respond($data);
    }

    public function create()
    {
        $data = $this->request->getJSON();

        if(!$this->CosasModel->save($data)){
            return $this->fail($this->CosasModel->errors());
        }

        return $this->respondCreated(['message' => 'Registro creado boen']);
    }

    public function update($id = null)
    {
        if ($id === null){
            return $this->failNotFound('No se proporciono un id');
        }

        $json = $this->request->getJSON();
        if(!$this->CosasModel->find($id)){
            return $this->failNotFound('no existe' .$id);
        }

        if(!$this->CosasModel->update($id, $json)){
            return $this->fail($this->CosasModel->errors());
        }
        return $this->respond(['message' => 'Registro actualizado correctamente']);
    }
    
    public function delete($id = null)
    {
        if ($id === null){
            return $this->failNotFound('No se proporcionó un id');
        }

        if(!$this->CosasModel->find($id))
        {
            return !$this->failNotFound('No existe el registro' .$id);
        }

        if (!$this->CosasModel->delete($id)){
            return $this->fail($this->CosasModel->errors());
        }

        return $this->respondDeleted(['message' => 'Registro eliminado existosamente']);
    }






}