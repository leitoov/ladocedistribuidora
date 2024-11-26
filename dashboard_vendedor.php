<?php
session_start();

// Verificar si el usuario tiene un token en la sesión
if (!isset($_SESSION['token'])) {
    header('Location: ../index.html');
    exit();
}

// Incluir función para verificar el token
require '../verify_token.php';
$jwt_secret = 'Adeleteamo1988@';

try {
    // Verificar y decodificar el token
    $tokenData = verifyJWT($_SESSION['token'], $jwt_secret);
    if (!$tokenData || $tokenData->rol !== 'vendedor') {
        throw new Exception('Token inválido o expirado.');
    }
} catch (Exception $e) {
    session_destroy();
    header('Location: ../index.html');
    exit();
}

// Extraer información del token
$userId = $tokenData->user_id;
$userRole = $tokenData->rol;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Vendedor</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../styles.css">
  <style>
    .card {
      border: 1px solid #dee2e6;
      transition: transform 0.2s ease;
      border-radius: 10px;
    }
    .card:hover {
      transform: scale(1.02);
    }
    .material-icons {
      font-size: 48px;
      color: #007bff;
    }
    .btn-primary {
      background-color: #007bff;
      border-color: #007bff;
    }
    .btn-primary:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Distribuidora</a>
      <button class="btn btn-outline-primary" id="logoutButton">Cerrar Sesión</button>
    </div>
  </nav>
  <div class="container my-4">
    <h1 class="text-center mb-4">Panel Vendedor</h1>

    <!-- Sección Productos -->
    <div class="mb-5">
      <h2 class="text-center mb-3">Productos</h2>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card text-center p-3">
            <i class="material-icons">search</i>
            <h5 class="card-title mt-2">Buscar Productos</h5>
            <p class="card-text">Consulta rápida de productos en el inventario.</p>
            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalBuscarProducto">Buscar</button>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card text-center p-3">
            <i class="material-icons">add_circle</i>
            <h5 class="card-title mt-2">Añadir Producto</h5>
            <p class="card-text">Agrega nuevos productos al inventario.</p>
            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalAñadirProducto">Añadir</button>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card text-center p-3">
            <i class="material-icons">edit</i>
            <h5 class="card-title mt-2">Modificar Producto</h5>
            <p class="card-text">Actualiza los detalles de productos existentes.</p>
            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalModificarProducto">Modificar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Sección Pedidos -->
    <div class="mb-5">
      <h2 class="text-center mb-3">Pedidos</h2>
      <div class="row g-4">
        <div class="col-md-6">
          <div class="card text-center p-3">
            <i class="material-icons">shopping_cart</i>
            <h5 class="card-title mt-2">Armar Pedido</h5>
            <p class="card-text">Gestión rápida de productos para pedidos.</p>
            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalArmarPedido">Nuevo Pedido</button>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card text-center p-3">
            <i class="material-icons">history</i>
            <h5 class="card-title mt-2">Historial de Pedidos</h5>
            <p class="card-text">Consulta los pedidos realizados anteriormente.</p>
            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalHistorialPedidos">Ver Historial</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Sección Gestión -->
    <div>
      <h2 class="text-center mb-3">Gestión</h2>
      <div class="row g-4">
        <div class="col-md-6">
          <div class="card text-center p-3">
            <i class="material-icons">undo</i>
            <h5 class="card-title mt-2">Devoluciones/Cancelaciones</h5>
            <p class="card-text">Confirma o rechaza devoluciones.</p>
            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalDevoluciones">Revisar</button>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card text-center p-3">
            <i class="material-icons">inventory</i>
            <h5 class="card-title mt-2">Consultar Inventario</h5>
            <p class="card-text">Revisa el estado actual del stock.</p>
            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalConsultarInventario">Ver Inventario</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modals -->
  <?php include 'modals.php'; ?>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
