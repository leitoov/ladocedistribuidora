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
            max-width: 90%;
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
        p{
            margin-bottom: 0px;
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 0fr));
            gap: 20px;
            margin-top: 20px;
            margin-bottom: 4%;
        }

        .product-card {
            background: var(--card-background);
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-align: left;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .product-card-title {
            font-size: 1rem;
            font-weight: bold;
            color: #007acc;
            /*margin-bottom: 10px;*/
        }

        .product-card-details {
            margin-bottom: 15px;
            font-size: 0.9rem;
            color: #333;
        }

        .product-card-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .product-card-actions input {
            width: 60%;
            text-align: center;
        }

        .product-card-actions .btn {
            padding: 5px 10px;
            font-size: 0.9rem;
        }

        .product-search-results {
            max-height: 200px;
            overflow-y: auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 0fr));
            text-align: left;
        }

        .product-search-results .list-group-item {
            cursor: pointer;
            padding: 15px;
            border-bottom: 1px solid #bfbfbf40;
        }

        .order-summary {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: var(--card-background);
            border-top: 1px solid var(--hover-color);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
        }

        @media (max-width: 768px) {
            .product-columns {
                grid-template-columns: 1fr;
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
            <div class="row g-3 pt-2">
                <div class="col-md-6">
                    <div class="input-group">
                        <label for="clienteInput" class="form-label w-100 text-start">Cliente</label>
                        <input type="text" class="form-control" id="clienteInput" placeholder="Buscar cliente (2 letras mínimo)">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <label for="tipoPedido" class="form-label w-100 text-start">Tipo de Pedido</label>
                        <select class="form-control" id="tipoPedido">
                            <option value="Caja">Caja</option>
                            <option value="Reparto">Reparto</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Product Search -->
            <div class="mt-4">
                <div class="input-group">
                    <label for="productoInput" class="form-label w-100 text-start">Producto</label>
                    <input type="text" class="form-control" id="productoInput" placeholder="Buscar producto (3 letras mínimo)">
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
                                        ${producto.nombre} ${producto.descripcion} - $${producto.precio}
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
            // Vaciar el buscador
            $('#productoInput').val('');
            $('#resultadosBusqueda').empty();
        };

        // Actualizar la tabla del pedido
        function actualizarTablaPedido() {
            const container = $('#pedidoActual');
            container.empty(); // Limpiar contenido previo
            let totalPedido = 0;

            if (productosEnPedido.length > 0) {
                productosEnPedido.forEach((producto) => {
                    const totalProducto = producto.precio * producto.cantidad;
                    totalPedido += totalProducto;

                    // Crear tarjeta para el producto
                    const cardHtml = `
                        <div class="product-card">
                            <div class="product-card-title">${producto.nombre} ${producto.descripcion}</div>
                            <div class="product-card-details">
                                <p><strong>Precio:</strong> $${producto.precio}</p>
                                <p><strong>Total:</strong> $${totalProducto}</p>
                            </div>
                            <div class="product-card-actions">
                                <input type="number" 
                                    class="form-control cantidadProducto" 
                                    data-id="${producto.id}" 
                                    value="${producto.cantidad}" 
                                    min="0" 
                                    max="${producto.stock}" 
                                    onchange="actualizarCantidad(${producto.id}, this.value)">
                                <button class="btn btn-danger btn-sm" onclick="eliminarProducto(${producto.id})">Eliminar</button>
                            </div>
                        </div>
                    `;
                    container.append(cardHtml);
                });
            } else {
                container.append('<div class="text-center text-muted">No hay productos en el pedido.</div>');
            }

            // Actualizar el total del pedido
            $('#totalPedido').text(totalPedido.toFixed(2));
        }

        // Actualizar cantidad de un producto
        window.actualizarCantidad = function (id, nuevaCantidad) {
            let producto = productosEnPedido.find(p => p.id === id);
            if (producto) {
                nuevaCantidad = parseInt(nuevaCantidad);
                if (nuevaCantidad === 0) {
                    eliminarProducto(id);
                } else if (nuevaCantidad >= 1 && nuevaCantidad <= producto.stock) {
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
