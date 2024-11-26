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
$userRole = $tokenData->rol; // 'vendedor', 'caja', 'admin'
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
    <h1 class="text-center mb-4">Panel Principal - <?php echo ucfirst($userRole); ?></h1>

    <?php if ($userRole === 'vendedor'): ?>
      <!-- Vista para Vendedor -->
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
            <p>Consulta los pedidos realizados.</p>
            <button class="btn btn-primary" id="orderHistory">Ver Historial</button>
          </div>
        </div>
      </div>

    <?php elseif ($userRole === 'caja'): ?>
      <!-- Vista para Caja -->
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
            <h5>Pedidos Procesados</h5>
            <p id="processedOrders" class="fs-4 fw-bold">0</p>
          </div>
        </div>
      </div>
      <h2 class="mt-4">Pedidos en Espera</h2>
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

    <?php elseif ($userRole === 'admin'): ?>
      <!-- Vista para Administrador -->
      <div class="row text-center mb-4">
        <div class="col-md-4">
          <div class="card p-3">
            <h5>Estadísticas</h5>
            <p>Visualiza métricas de ventas.</p>
            <button class="btn btn-primary" id="viewStats">Ver Estadísticas</button>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card p-3">
            <h5>Gestión de Inventario</h5>
            <p>Administra productos y cantidades.</p>
            <button class="btn btn-primary" id="manageInventory">Gestionar Inventario</button>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card p-3">
            <h5>Informes</h5>
            <p>Genera informes detallados.</p>
            <button class="btn btn-primary" id="generateReports">Generar Informe</button>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(document).ready(function() {
      const token = '<?php echo $_SESSION['token']; ?>';

      // Cargar pedidos (solo Caja)
      function loadOrders() {
        $.ajax({
          url: 'api/orders.php',
          method: 'POST',
          contentType: 'application/json',
          data: JSON.stringify({ token: token }),
          success: function(response) {
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

      // Actualizar pedidos cada minuto (solo para Caja)
      <?php if ($userRole === 'caja'): ?>
      setInterval(loadOrders, 60000);
      loadOrders();
      <?php endif; ?>

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
