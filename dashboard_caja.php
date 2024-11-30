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
    <title>Distribuidora - Panel Caja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        :root {
            --primary-color: #00bfff;
            --secondary-color: #007acc;
            --background-color: #f0f4f8;
            --text-color: #333;
            --card-background: #ffffff;
            --hover-color: #e6f2ff;
        }

        * {
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .app-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: bold;
            color: white !important;
        }

        .order-management {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .order-management {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background-color: var(--card-background);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-header i {
            margin-right: 10px;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .form-control {
            border-radius: 8px;
            padding: 10px;
        }

        .order-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .order-table thead {
            background-color: var(--primary-color);
            color: white;
        }

        .order-table th,
        .order-table td {
            padding: 12px;
            text-align: center;
        }

        .order-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .btn-primary,
        .btn-danger {
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .modal-content {
            border-radius: 12px;
        }

        .form-label {
            width: 100%;
        }

        /* Responsive Adjustments */
        @media (max-width: 576px) {
            .order-management {
                grid-template-columns: 1fr;
            }

            .order-actions {
                grid-template-columns: 1fr;
            }

            .order-table {
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-cart-check me-2"></i> Distribuidora - Caja
            </a>
            <button class="btn btn-outline-light" id="logoutButton">
                <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
            </button>
        </div>
    </nav>

    <div class="container app-container">
        <h1 class="text-center my-4">Panel de Gestión de Caja</h1>

        <div class="order-management">
            <!-- Order Manager Card -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <i class="bi bi-pencil-square"></i> Pedidos para Caja
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="order-table mt-3">
                        <thead>
                            <tr>
                                <th>ID Pedido</th>
                                <th>Cliente</th>
                                <th>Tipo de Pedido</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="pedidosCaja">
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    No hay pedidos disponibles
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Product Management Card -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <i class="bi bi-box-seam"></i> Gestión de Productos
                    </div>
                </div>
                <button class="btn btn-primary w-100 mb-3" onclick="window.location.href='anadir_producto.php'">
                    <i class="bi bi-plus-circle me-2"></i> Añadir Producto
                </button>
                <button class="btn btn-warning w-100" onclick="window.location.href='modificar_producto.php'">
                    <i class="bi bi-pencil me-2"></i> Editar Producto
                </button>
            </div>

            <!-- Order History Card -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <i class="bi bi-clock-history"></i> Historial de Pedidos
                    </div>
                </div>
                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalHistorialPedidos">
                    <i class="bi bi-list-ul me-2"></i> Ver Historial
                </button>
            </div>
        </div>
    </div>

    <!-- Message Modal -->
    <div class="modal fade" id="modalMensaje" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mensaje</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalMensajeCuerpo">
                    <!-- Message content -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            // Cargar los pedidos para la caja
            function cargarPedidosCaja() {
                $.ajax({
                    url: 'api/pedidos.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        let tbody = $('#pedidosCaja');
                        tbody.empty();
                        if (data.length > 0) {
                            data.forEach(function (pedido) {
                                tbody.append(`
                                    <tr>
                                        <td>${pedido.id}</td>
                                        <td>${pedido.cliente}</td>
                                        <td>${pedido.tipo_pedido}</td>
                                        <td>$${pedido.total}</td>
                                        <td>
                                            <button class="btn btn-primary btn-sm" onclick="procesarPedido(${pedido.id})">Procesar</button>
                                            <button class="btn btn-warning btn-sm" onclick="editarPedido(${pedido.id})">Editar</button>
                                        </td>
                                    </tr>
                                `);
                            });
                        } else {
                            tbody.append('<tr><td colspan="5" class="text-center text-muted">No hay pedidos disponibles</td></tr>');
                        }
                    },
                    error: function () {
                        mostrarMensajeModal("Error al cargar los pedidos de la caja.");
                    }
                });
            }

            // Function to show messages in a modal
            function mostrarMensajeModal(mensaje) {
                $('#modalMensajeCuerpo').text(mensaje);
                $('#modalMensaje').modal('show');
            }

            // Procesar Pedido
            window.procesarPedido = function (pedidoId) {
                // Lógica para procesar un pedido, por ejemplo cambiar el estado del pedido o confirmar su pago
                mostrarMensajeModal(`Pedido ${pedidoId} procesado correctamente.`);
                cargarPedidosCaja();
            }

            // Editar Pedido
            window.editarPedido = function (pedidoId) {
                // Redirigir a una página donde se pueda editar el pedido
                window.location.href = `editar_pedido.php?pedidoId=${pedidoId}`;
            }

            // Cargar pedidos al cargar la página
            cargarPedidosCaja();

            //Cerrar sesión
            $('#logoutButton').on('click', function () {
                window.location.href = 'logout.php';
            });
        });
    </script>
</body>

</html>
