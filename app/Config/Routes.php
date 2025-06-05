<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');



// Rutas para Libros
$routes->group('api', function($routes) {
    $routes->resource('libros', ['controller' => 'Libro']);
    $routes->get('libros/(:num)/autores', 'Libro::autores/$1');
    $routes->get('libros/(:num)/categorias', 'Libro::categorias/$1');
    $routes->post('libros/(:num)/autor', 'Libro::addAutor/$1');
    $routes->post('libros/(:num)/categoria', 'Libro::addCategoria/$1');
    $routes->post('libros/create-with-relations', 'Libro::createWithRelations');
    
    // Rutas para gestionar relaciones
    $routes->delete('libros/(:num)/autor/(:num)', 'Libro::removeAutor/$1/$2');
    $routes->delete('libros/(:num)/categoria/(:num)', 'Libro::removeCategoria/$1/$2');
    
    // Rutas para Autores
    $routes->resource('autores', ['controller' => 'Autor']);
    $routes->get('autores/(:num)/libros', 'Autor::libros/$1');
    
    // Rutas para Categorías
    $routes->resource('categorias', ['controller' => 'Categoria']);
    $routes->get('categorias/(:num)/libros', 'Categoria::libros/$1');
});