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
$userRole = $tokenData->rol;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Distribuidora - Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <style>
    body {
      background-color: #f4f4f4;
      font-family: Arial, sans-serif;
    }
    .navbar {
      background-color: #00bfff;
    }
    .navbar .navbar-brand, .navbar .btn {
      color: #fff;
    }
    .navbar .btn:hover {
      background-color: #0099cc;
      color: #fff;
    }
    .card {
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .card h5 {
      color: #007bff;
    }
    .btn-refresh {
      background-color: #00bfff;
      color: #fff;
      border: none;
    }
    .btn-refresh:hover {
      background-color: #0099cc;
    }
    table thead {
      background-color: #007bff;
      color: #fff;
    }
    table tbody tr:nth-child(even) {
      background-color: #f2f2f2;
    }
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
    <h1 class="text-center mb-4">Panel Principal</h1>
    <div class="row text-center mb-4">
      <div class="col-md-4">
        <div class="card p-3">
          <h5>Pedidos Totales</h5>
          <p id="totalOrders" class="fs-4 fw-bold">0</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-3">
          <h5>Pedidos Pendientes</h5>
          <p id="pendingOrders" class="fs-4 fw-bold">0</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-3">
          <h5>Pedidos Completados</h5>
          <p id="completedOrders" class="fs-4 fw-bold">0</p>
        </div>
      </div>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Pedidos Recientes</h2>
      <button class="btn btn-refresh" id="refreshButton">Actualizar Pedidos</button>
    </div>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Cliente</th>
          <th>Total</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="ordersTable">
        <tr>
          <td colspan="5" class="text-center">Cargando pedidos...</td>
        </tr>
      </tbody>
    </table>
  </div>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(document).ready(function() {
      const token = '<?php echo $_SESSION['token']; ?>';

      // Función para cargar pedidos
      function loadOrders() {
        $.ajax({
          url: 'api/orders.php',
          method: 'POST',
          contentType: 'application/json',
          data: JSON.stringify({ token: token }),
          success: function(response) {
            $('#totalOrders').text(response.length || 0);

            const table = $('#ordersTable');
            table.empty();

            if (response.length > 0) {
              response.forEach(order => {
                table.append(`
                  <tr>
                    <td>${order.pedido_id}</td>
                    <td>${order.producto_nombre || 'N/A'}</td>
                    <td>${order.total}</td>
                    <td>${order.estado}</td>
                    <td><button class="btn btn-sm btn-primary">Ver</button></td>
                  </tr>
                `);
              });
            } else {
              table.append('<tr><td colspan="5" class="text-center">No hay pedidos recientes.</td></tr>');
            }
          },
          error: function(xhr) {
            console.error(xhr.responseText);
            alert('Error al cargar los pedidos.');
          }
        });
      }

      // Llamar la función cada minuto
      setInterval(loadOrders, 60000);

      // Botón para actualizar manualmente
      $('#refreshButton').on('click', loadOrders);

      // Cargar pedidos al inicio
      loadOrders();

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
