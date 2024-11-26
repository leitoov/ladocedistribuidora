<?php
session_start();
require 'verify_token.php';

// Verificar si el token es válido
if (!isset($_SESSION['token'])) {
    echo 'vendedor';
    //header('Location: index.html');
    exit();
}

// Extraer datos del token
$jwt_secret = 'Adeleteamo1988@';
$tokenData = verifyJWT($_SESSION['token'], $jwt_secret);
if (!$tokenData || $tokenData->rol !== 'Vendedor') {
    session_destroy();
    echo 'vendedor';
    //header('Location: index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Distribuidora - Vendedor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <style>
    .card { border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
    .navbar { background-color: #00bfff; }
    .navbar .navbar-brand, .navbar .btn { color: #fff; }
    .navbar .btn:hover { background-color: #0099cc; color: #fff; }
    table thead { background-color: #007bff; color: #fff; }
    table tbody tr:nth-child(even) { background-color: #f2f2f2; }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Distribuidora</a>
      <button class="btn" id="logoutButton">Cerrar Sesión</button>
    </div>
  </nav>
  <div class="container my-4">
    <h1 class="text-center mb-4">Panel Vendedor</h1>

    <!-- Sección Compartida: Gestión de Productos -->
    <div class="row text-center mb-4">
      <div class="col-md-4">
        <div class="card p-3">
          <h5>Buscar Productos</h5>
          <p>Consulta rápida de productos en el inventario.</p>
          <button class="btn btn-primary" id="searchProduct">Buscar Productos</button>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-3">
          <h5>Añadir Producto</h5>
          <p>Agrega nuevos productos al inventario.</p>
          <button class="btn btn-primary" id="addProduct">Añadir Producto</button>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-3">
          <h5>Modificar Producto</h5>
          <p>Actualiza los detalles de productos existentes.</p>
          <button class="btn btn-primary" id="editProduct">Modificar Producto</button>
        </div>
      </div>
    </div>

    <!-- Sección Específica del Vendedor -->
    <div class="row text-center mb-4">
      <div class="col-md-6">
        <div class="card p-3">
          <h5>Armar Pedido</h5>
          <p>Gestión rápida de productos para pedidos.</p>
          <button class="btn btn-primary" id="newOrder">Nuevo Pedido</button>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card p-3">
          <h5>Historial de Pedidos</h5>
          <p>Consulta los pedidos realizados anteriormente.</p>
          <button class="btn btn-primary" id="orderHistory">Ver Historial</button>
        </div>
      </div>
    </div>

    <div class="row text-center mb-4">
      <div class="col-md-6">
        <div class="card p-3">
          <h5>Devoluciones / Cancelaciones</h5>
          <p>Confirma o rechaza devoluciones.</p>
          <button class="btn btn-primary" id="returnRequests">Revisar Solicitudes</button>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card p-3">
          <h5>Pedidos de Reparto</h5>
          <p>Genera pedidos gestionados por el repartidor.</p>
          <button class="btn btn-primary" id="repartoOrders">Nuevo Pedido de Reparto</button>
        </div>
      </div>
    </div>

    <div class="row text-center">
      <div class="col-md-12">
        <div class="card p-3">
          <h5>Consultar Inventario</h5>
          <p>Revisa el estado actual del stock.</p>
          <button class="btn btn-primary" id="inventoryCheck">Ver Inventario</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(document).ready(function() {
      // Funciones Compartidas
      $('#searchProduct').on('click', function() {
        console.log('Buscar productos - Implementar lógica');
      });

      $('#addProduct').on('click', function() {
        console.log('Añadir producto - Implementar lógica');
      });

      $('#editProduct').on('click', function() {
        console.log('Modificar producto - Implementar lógica');
      });

      // Funciones Específicas del Vendedor
      $('#newOrder').on('click', function() {
        console.log('Crear un nuevo pedido - Implementar lógica');
      });

      $('#orderHistory').on('click', function() {
        console.log('Historial de pedidos - Implementar lógica');
      });

      $('#returnRequests').on('click', function() {
        console.log('Revisar solicitudes de devoluciones - Implementar lógica');
      });

      $('#repartoOrders').on('click', function() {
        console.log('Crear un pedido de reparto - Implementar lógica');
      });

      $('#inventoryCheck').on('click', function() {
        console.log('Consultar inventario - Implementar lógica');
      });

      // Cerrar sesión
      $('#logoutButton').on('click', function() {
        $.ajax({
          url: 'logout.php',
          success: function() {
            window.location.href = 'index.html';
          }
        });
      });
    });
  </script>
</body>
</html>
