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
    $tokenData = verifyJWT($_SESSION['token'], $jwt_secret);
    if (!$tokenData) {
        throw new Exception('Token inválido o expirado.');
    }
} catch (Exception $e) {
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
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    .navbar {
      background-color: #00bfff;
    }
    .navbar .navbar-brand, .navbar .btn {
      color: #fff;
    }
    .navbar .btn:hover {
      background-color: #007acc;
    }
    .section-header {
      font-size: 20px;
      font-weight: bold;
      margin-bottom: 15px;
    }
    .split {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
    }
    .split > div {
      flex: 1;
      min-width: 45%;
    }
    .order-table th, .order-table td {
      text-align: center;
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

    <div class="split">
      <!-- Gestión de Pedidos -->
      <div class="card p-4">
        <h2 class="section-header">Gestión de Pedidos</h2>

        <!-- Cliente -->
        <div class="mb-3">
          <label for="clienteInput" class="form-label">Cliente</label>
          <input type="text" class="form-control" id="clienteInput" placeholder="Buscar cliente (2 letras mínimo)">
        </div>

        <!-- Productos -->
        <div class="mb-3">
          <label for="productoInput" class="form-label">Producto</label>
          <input type="text" class="form-control" id="productoInput" placeholder="Buscar producto (3 letras mínimo)">
        </div>

        <!-- Pedido Actual -->
        <table class="table table-striped order-table mt-3">
          <thead>
            <tr>
              <th>Producto</th>
              <th>Cantidad</th>
              <th>Precio Unitario</th>
              <th>Total</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="pedidoActual">
            <tr>
              <td colspan="5">No hay productos en el pedido.</td>
            </tr>
          </tbody>
        </table>

        <button class="btn btn-primary w-100 mt-3" id="guardarPedido">Guardar Pedido</button>
      </div>

      <!-- Funciones Complementarias -->
      <div class="card p-4">
        <h2 class="section-header">Funciones Complementarias</h2>

        <!-- Historial de Pedidos -->
        <button class="btn btn-outline-primary w-100 mb-3" data-bs-toggle="modal" data-bs-target="#modalHistorialPedidos">
          <span class="material-icons">history</span> Historial de Pedidos
        </button>

        <!-- Añadir Producto -->
        <button class="btn btn-outline-success w-100 mb-3" data-bs-toggle="modal" data-bs-target="#modalAñadirProducto">
          <span class="material-icons">add_circle</span> Añadir Producto
        </button>

        <!-- Modificar Producto -->
        <button class="btn btn-outline-warning w-100 mb-3" data-bs-toggle="modal" data-bs-target="#modalModificarProducto">
          <span class="material-icons">edit</span> Modificar Producto
        </button>
      </div>
    </div>
  </div>

  <!-- Modals -->
  <!-- Historial de Pedidos -->
  <div class="modal fade" id="modalHistorialPedidos" tabindex="-1" aria-labelledby="modalHistorialPedidosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalHistorialPedidosLabel">Historial de Pedidos</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Fecha</th>
              </tr>
            </thead>
            <tbody id="historialPedidosBody">
              <tr>
                <td colspan="5" class="text-center">Cargando...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Añadir Producto -->
  <div class="modal fade" id="modalAñadirProducto" tabindex="-1" aria-labelledby="modalAñadirProductoLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalAñadirProductoLabel">Añadir Producto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="text" class="form-control mb-3" placeholder="Nombre del producto" id="nuevoProductoNombre">
          <textarea class="form-control mb-3" placeholder="Descripción" id="nuevoProductoDescripcion"></textarea>
          <input type="number" class="form-control mb-3" placeholder="Precio" id="nuevoProductoPrecio">
          <input type="number" class="form-control mb-3" placeholder="Cantidad" id="nuevoProductoStock">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="guardarProducto">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modificar Producto -->
  <div class="modal fade" id="modalModificarProducto" tabindex="-1" aria-labelledby="modalModificarProductoLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalModificarProductoLabel">Modificar Producto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="text" class="form-control mb-3" placeholder="Código o nombre del producto">
          <input type="text" class="form-control mb-3" placeholder="Nuevo nombre">
          <input type="number" class="form-control mb-3" placeholder="Nueva cantidad">
          <input type="number" class="form-control mb-3" placeholder="Nuevo precio">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-warning">Modificar</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Aquí puedes añadir la lógica de las funcionalidades
  </script>
</body>
</html>
