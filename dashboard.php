<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['token'])) {
    header('Location: index.html'); // Redirigir a login si no está logueado
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Distribuidora - Dashboard</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- CSS Global -->
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Distribuidora</a>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="logout.php" id="logoutButton">Cerrar Sesión</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Contenido Principal -->
  <div class="container my-4">
    <h1 class="section-title text-center">Panel Principal</h1>
    
    <!-- Estadísticas Principales -->
    <div class="row">
      <div class="col-md-4">
        <div class="stat-card">
          <h3 id="totalOrders">0</h3>
          <p>Pedidos Totales</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card">
          <h3 id="pendingOrders">0</h3>
          <p>Pedidos Pendientes</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card">
          <h3 id="completedOrders">0</h3>
          <p>Pedidos Completados</p>
        </div>
      </div>
    </div>

    <!-- Pedidos Recientes -->
    <h2 class="section-title">Pedidos Recientes</h2>
    <div class="card">
      <div class="card-header">Lista de Pedidos</div>
      <div class="card-body">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>ID Pedido</th>
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
    </div>
  </div>

  <!-- jQuery y Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(document).ready(function() {
      const token = '<?php echo $_SESSION["token"] ?? ""; ?>';

      if (!token) {
        alert('Por favor, inicie sesión.');
        window.location.href = 'index.html';
        return;
      }

      // Función para cargar estadísticas y pedidos
      function loadDashboard() {
        $.ajax({
          url: 'api/orders.php',
          method: 'GET',
          headers: { Authorization: 'Bearer ' + token },
          success: function(response) {
            // Actualizar estadísticas
            $('#totalOrders').text(response.total || 0);
            $('#pendingOrders').text(response.pending || 0);
            $('#completedOrders').text(response.completed || 0);

            // Actualizar tabla de pedidos
            const ordersTable = $('#ordersTable');
            ordersTable.empty();

            if (response.orders && response.orders.length > 0) {
              response.orders.forEach(order => {
                ordersTable.append(`
                  <tr>
                    <td>${order.id}</td>
                    <td>${order.cliente}</td>
                    <td>$${order.total}</td>
                    <td>${order.estado}</td>
                    <td>
                      <button class="btn btn-primary btn-sm">Ver</button>
                    </td>
                  </tr>
                `);
              });
            } else {
              ordersTable.append('<tr><td colspan="5" class="text-center">No hay pedidos recientes.</td></tr>');
            }
          },
          error: function() {
            alert('Error al cargar el dashboard.');
          }
        });
      }

      // Cargar el dashboard
      loadDashboard();
    });
  </script>
</body>
</html>
