<?php
// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Verificar si el usuario tiene un token en la sesión
if (!isset($_SESSION['token'])) {
    header('Location: index.html');
    exit();
}

// Incluir función para verificar el token
require 'verify_token.php';
$jwt_secret = 'Adeleteamo1988@';

try {
    // Verificar y decodificar el token
    $tokenData = verifyJWT($_SESSION['token'], $jwt_secret);
    if (!$tokenData) {
        throw new Exception('Token inválido o expirado.');
    }
} catch (Exception $e) {
    // Redirigir al login si el token no es válido
    session_destroy();
    header('Location: index.html');
    exit();
}

// Extraer información del token
$userId = $tokenData->user_id;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Distribuidora - Panel Vendedor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <style>
    .card {
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      transition: transform 0.2s ease;
    }
    .card:hover {
      transform: scale(1.05);
    }
    .navbar {
      background-color: #00bfff;
    }
    .navbar .navbar-brand, .navbar .btn {
      color: #fff;
    }
    .navbar .btn:hover {
      background-color: #007acc;
    }
    table thead {
      background-color: #007bff;
      color: #fff;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Distribuidora</a>
      <button class="btn btn-outline-light" id="logoutButton">Cerrar Sesión</button>
    </div>
  </nav>
  <div class="container my-4">
    <h1 class="text-center mb-4">Panel Vendedor</h1>
    <div class="row text-center">
      <div class="col-md-4">
        <div class="card p-3">
          <h5>Buscar Productos</h5>
          <p>Consulta rápida de productos en el inventario.</p>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalBuscarProducto">Buscar Productos</button>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-3">
          <h5>Añadir Producto</h5>
          <p>Agrega nuevos productos al inventario.</p>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAñadirProducto">Añadir Producto</button>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-3">
          <h5>Modificar Producto</h5>
          <p>Actualiza los detalles de productos existentes.</p>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalModificarProducto">Modificar Producto</button>
        </div>
      </div>
    </div>
    <div class="row text-center mt-4">
      <div class="col-md-6">
        <div class="card p-3">
          <h5>Armar Pedido</h5>
          <p>Gestión rápida de productos para pedidos.</p>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalArmarPedido">Nuevo Pedido</button>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card p-3">
          <h5>Historial de Pedidos</h5>
          <p>Consulta los pedidos realizados anteriormente.</p>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalHistorialPedidos">Ver Historial</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modals -->
  <!-- Modal Buscar Producto -->
  <div class="modal fade" id="modalBuscarProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Buscar Producto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="text" class="form-control mb-3" id="buscarProductoInput" placeholder="Ingrese nombre o código del producto">
          <div id="resultadoBusqueda" class="mt-3"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="buscarProductoBtn">Buscar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Añadir Producto -->
  <div class="modal fade" id="modalAñadirProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Añadir Producto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="text" class="form-control mb-3" placeholder="Nombre del producto" id="nuevoProductoNombre">
          <input type="number" class="form-control mb-3" placeholder="Cantidad" id="nuevoProductoCantidad">
          <input type="number" class="form-control mb-3" placeholder="Precio" id="nuevoProductoPrecio">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="añadirProductoBtn">Añadir</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Modificar Producto -->
  <div class="modal fade" id="modalModificarProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Modificar Producto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="text" class="form-control mb-3" id="modificarProductoCodigo" placeholder="Código del producto">
          <input type="text" class="form-control mb-3" id="modificarProductoNombre" placeholder="Nuevo nombre">
          <input type="number" class="form-control mb-3" id="modificarProductoCantidad" placeholder="Nueva cantidad">
          <input type="number" class="form-control mb-3" id="modificarProductoPrecio" placeholder="Nuevo precio">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="modificarProductoBtn">Modificar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Aquí puedes añadir los scripts necesarios para la funcionalidad de los modales
  </script>
</body>
</html>
