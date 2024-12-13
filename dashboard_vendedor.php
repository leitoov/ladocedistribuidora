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
        :root {
            --primary-color: #00bfff;
            --secondary-color: #007acc;
            --background-color: #f0f4f8;
            --text-color: #333;
            --card-background: #ffffff;
            --hover-color: #e6f2ff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
            text-align: center;
        }

        .container {
            max-width: 1200px;
            margin: auto;
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

        .navbar-nav {
            margin-left: auto;
        }

        .navbar-nav .nav-item {
            margin-left: 15px;
        }

        .card {
            background-color: var(--card-background);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .card-header {
            font-weight: bold;
            background-color: var(--primary-color);
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-transform: uppercase;
        }

        .product-columns {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .product-item {
            background: var(--card-background);
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-align: left;
        }

        .product-item div {
            margin-bottom: 10px;
        }

        .product-item strong {
            font-size: 1.1rem;
            color: var(--primary-color);
        }

        .product-item button {
            align-self: flex-end;
            font-size: 0.9rem;
            padding: 8px 15px;
            border-radius: 5px;
            background-color: #dc3545;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .product-item button:hover {
            background-color: #c82333;
        }

        .order-summary {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: var(--card-background);
            border-top: 1px solid var(--hover-color);
            padding: 15px 20px;
            box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
        }

        .order-summary div {
            font-weight: bold;
            font-size: 1.2rem;
        }

        .order-summary button {
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 1rem;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: #6c757d;
        }

        .product-search-results {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .product-search-results .list-group-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .product-search-results .list-group-item:hover {
            background-color: var(--hover-color);
        }

        .table th,
        .table td {
            text-align: center;
            vertical-align: middle;
        }

        .form-control, .form-select {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 12px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 8px rgba(0, 191, 255, 0.4);
        }

        small.text-danger {
            font-size: 0.8rem;
            display: block;
            margin-top: 5px;
        }

        small.text-danger.d-none {
            display: none;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .product-columns {
                grid-template-columns: 1fr;
            }

            .order-summary {
                flex-direction: column;
                gap: 10px;
            }

            .order-summary button {
                width: 100%;
            }
        }


    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-cart-check me-2"></i> Distribuidora
            </a>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#modalHistorialPedidos">
                        <i class="bi bi-clock-history me-2"></i> Historial de Pedidos
                    </a>
                </li>
                <li class="nav-item">
                    <button class="btn btn-outline-light" id="logoutButton">
                        <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                    </button>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container app-container">
        <h1 class="text-center my-4">Panel de Gestión de Pedidos</h1>

        <div class="card">
            <div class="card-header text-center">
                <i class="bi bi-cart-plus"></i> Gestor de Pedidos
            </div>

            <!-- Inputs for Client and Order Type -->
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control cliente-input" id="clienteInput" placeholder="Cliente">
                        <label for="clienteInput">Buscar cliente (2 letras mínimo)</label>
                        <small id="clienteError" class="text-danger d-none">El nombre del cliente es obligatorio.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <select class="form-select tipo-pedido-input" id="tipoPedido">
                            <option value="Caja">Caja</option>
                            <option value="Reparto">Reparto</option>
                        </select>
                        <label for="tipoPedido">Tipo de Pedido</label>
                    </div>
                </div>
            </div>

            <!-- Product Search -->
            <div class="mt-4">
                <div class="form-floating">
                    <input type="text" class="form-control producto-input" id="productoInput" placeholder="Producto">
                    <label for="productoInput">Buscar producto (3 letras mínimo)</label>
                    <small id="productoError" class="text-danger d-none">Por favor, ingrese al menos 3 letras para buscar productos.</small>
                </div>
                <div id="resultadosBusqueda" class="product-search-results mt-3"></div>
            </div>

            <!-- Current Order -->
            <div class="mt-4">
                <div id="pedidoActual" class="product-columns">
                    <!-- Products will be dynamically loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Order Summary -->
    <div class="order-summary">
        <div>Total: $<span id="totalPedido">0</span></div>
        <div>
            <button class="btn btn-primary me-2" id="confirmarPedido">
                <i class="bi bi-check-circle"></i> Confirmar Pedido
            </button>
            <button class="btn btn-danger" id="cancelarPedido">
                <i class="bi bi-x-circle"></i> Cancelar Pedido
            </button>
        </div>
    </div>

    <!-- Modal for Order History -->
    <div class="modal fade" id="modalHistorialPedidos" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Historial de Pedidos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Contenido del historial de pedidos...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
        let productosEnPedido = [];

        // Mostrar mensaje en un modal
        function mostrarMensajeModal(mensaje) {
            $('#modalMensajeCuerpo').text(mensaje);
            $('#modalMensaje').modal('show');
        }

        // Búsqueda de productos con AJAX
        $('#productoInput').on('keyup', function () {
            let termino = $(this).val();
            if (termino.length >= 3) {
                $.ajax({
                    url: 'api/products.php',
                    type: 'GET',
                    data: { termino: termino },
                    success: function (respuesta) {
                        $('#resultadosBusqueda').empty();
                        if (respuesta.length > 0) {
                            respuesta.forEach(function (producto) {
                                $('#resultadosBusqueda').append(
                                    `<button class="list-group-item list-group-item-action" 
                                        onclick="agregarProducto(${producto.id}, '${producto.nombre}', 
                                        '${producto.descripcion}', ${producto.precio}, ${producto.stock})">
                                        ${producto.nombre} - $${producto.precio}
                                    </button>`
                                );
                            });
                        } else {
                            $('#resultadosBusqueda').append('<div class="list-group-item">No se encontraron productos.</div>');
                        }
                    },
                    error: function () {
                        mostrarMensajeModal("Error al realizar la búsqueda.");
                    }
                });
            } else {
                $('#resultadosBusqueda').empty();
            }
        });

        // Agregar producto al pedido
        window.agregarProducto = function (id, nombre, descripcion, precio, stock) {
            let productoExistente = productosEnPedido.find(p => p.id === id);
            if (productoExistente) {
                if (productoExistente.cantidad < stock) {
                    productoExistente.cantidad++;
                    actualizarTablaPedido();
                } else {
                    mostrarMensajeModal("No hay suficiente stock disponible.");
                }
            } else {
                if (stock > 0) {
                    let nuevoProducto = {
                        id: id,
                        nombre: nombre,
                        descripcion: descripcion,
                        precio: precio,
                        cantidad: 1,
                        stock: stock
                    };
                    productosEnPedido.push(nuevoProducto);
                    actualizarTablaPedido();
                } else {
                    mostrarMensajeModal("No hay suficiente stock disponible.");
                }
            }
        };

        // Actualizar la tabla del pedido
        function actualizarTablaPedido() {
            let container = $('#pedidoActual');
            container.empty();
            let totalPedido = 0;

            if (productosEnPedido.length > 0) {
                let html = '<div class="product-columns">';
                productosEnPedido.forEach(function (producto) {
                    let totalProducto = producto.precio * producto.cantidad;
                    totalPedido += totalProducto;

                    html += `
                        <div class="product-item">
                            <div><strong>${producto.nombre}</strong></div>
                            <div>${producto.descripcion}</div>
                            <div>Cantidad: 
                                <input type="number" class="form-control text-center cantidadProducto" 
                                    data-id="${producto.id}" 
                                    value="${producto.cantidad}" 
                                    min="1" max="${producto.stock}" 
                                    onchange="actualizarCantidad(${producto.id}, this.value)">
                            </div>
                            <div>Precio: $${producto.precio}</div>
                            <div>Total: $${totalProducto}</div>
                            <button class="btn btn-danger btn-sm" onclick="eliminarProducto(${producto.id})">Eliminar</button>
                        </div>
                    `;
                });
                html += '</div>';
                container.append(html);
            } else {
                container.append('<div class="text-center text-muted">No hay productos en el pedido.</div>');
            }

            $('#totalPedido').text(totalPedido.toFixed(2));
        }

        // Actualizar cantidad de un producto
        window.actualizarCantidad = function (id, nuevaCantidad) {
            let producto = productosEnPedido.find(p => p.id === id);
            if (producto) {
                nuevaCantidad = parseInt(nuevaCantidad);
                if (nuevaCantidad >= 1 && nuevaCantidad <= producto.stock) {
                    producto.cantidad = nuevaCantidad;
                    actualizarTablaPedido();
                } else {
                    mostrarMensajeModal("Cantidad inválida o fuera de stock.");
                }
            }
        };

        // Eliminar un producto del pedido
        window.eliminarProducto = function (id) {
            productosEnPedido = productosEnPedido.filter(p => p.id !== id);
            actualizarTablaPedido();
        };

        // Confirmar pedido
        $('#confirmarPedido').on('click', function () {
            if (productosEnPedido.length === 0) {
                mostrarMensajeModal("No hay productos en el pedido para confirmar.");
                return;
            }

            const cliente = $('#clienteInput').val().trim();
            if (!cliente) {
                mostrarMensajeModal("Debe ingresar un cliente para confirmar el pedido.");
                return;
            }

            $.ajax({
                url: 'api/pedido.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    cliente: cliente,
                    tipoPedido: $('#tipoPedido').val(),
                    productos: productosEnPedido
                }),
                success: function () {
                    mostrarMensajeModal("Pedido confirmado correctamente.");
                    generarPDF();
                    productosEnPedido = [];
                    actualizarTablaPedido();
                },
                error: function () {
                    mostrarMensajeModal("Error al confirmar el pedido.");
                }
            });
        });

        // Cancelar pedido
        $('#cancelarPedido').on('click', function () {
            if (productosEnPedido.length === 0) {
                mostrarMensajeModal("No hay productos para cancelar.");
                return;
            }

            productosEnPedido = [];
            actualizarTablaPedido();
            $('#clienteInput').val('');
            $('#tipoPedido').val('Caja');
            $('#productoInput').val('');
        });

        // Generar PDF del pedido
        function generarPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            doc.text("Distribuidora", 10, 10);
            doc.text(`Cliente: ${$('#clienteInput').val()}`, 10, 20);
            doc.text(`Tipo de Pedido: ${$('#tipoPedido').val()}`, 10, 30);

            let inicioY = 40;
            doc.text("Productos:", 10, inicioY);

            productosEnPedido.forEach((producto, index) => {
                inicioY += 10;
                doc.text(
                    `${index + 1}. ${producto.nombre} - Cantidad: ${producto.cantidad} - Total: $${(producto.precio * producto.cantidad).toFixed(2)}`,
                    10,
                    inicioY
                );
            });

            inicioY += 10;
            doc.text(`Total del Pedido: $${productosEnPedido.reduce((acc, p) => acc + (p.precio * p.cantidad), 0).toFixed(2)}`, 10, inicioY);

            doc.save("pedido.pdf");
        }

        // Cerrar sesión
        $('#logoutButton').on('click', function () {
            window.location.href = 'logout.php';
        });
    });

    </script>
</body>
</html>
