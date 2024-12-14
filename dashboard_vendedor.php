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
            font-size: 0.75rem;
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
        .totalsummary{
            font-size: 25px;
            font-weight: 500;
        }
        .h-38{
            height: 38px;
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
                        <input type="text" class="form-control" id="clienteInput" placeholder="Escribe para buscar cliente...">
                        <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalBusquedaClientes">
                            Buscar
                        </button>
                    </div>
                    <!--div class="input-group">
                        <label for="clienteInput" class="form-label w-100 text-start">Cliente</label>
                        <input type="text" class="form-control" id="clienteInput" placeholder="Buscar cliente (2 letras mínimo)">
                    </div -->
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
        <div class="totalsummary">Total: $<span id="totalPedido">0</span></div>
        <div>
            <button class="btn btn-primary me-2" id="confirmarPedido">
                <i class="bi bi-check-circle"></i> Confirmar Pedido
            </button>
            <button class="btn btn-danger" id="cancelarPedido">
                <i class="bi bi-x-circle"></i> Cancelar Pedido
            </button>
        </div>
    </div>
    <!-- Modal para búsqueda de clientes -->
    <div class="modal fade" id="modalBusquedaClientes" tabindex="-1" aria-labelledby="modalBusquedaClientesLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalBusquedaClientesLabel">Buscar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="resultadosClientes" class="list-group">
                        <!-- Los resultados dinámicos se agregarán aquí -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="btnUsarTextoCliente" type="button" class="btn btn-primary" data-bs-dismiss="modal">Usar lo escrito</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
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
    <!-- Modal para mostrar mensajes -->
    <div class="modal fade" id="modalMensaje" tabindex="-1" aria-labelledby="modalMensajeLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMensajeLabel">Mensaje</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalMensajeCuerpo">
                    <!-- El mensaje se llenará dinámicamente -->
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    $(document).ready(function () {
        let productosEnPedido = [];


        $('#modalBusquedaClientes').on('shown.bs.modal', function () {
            const termino = $('#clienteInput').val();
            if (termino.length >= 3) {
                buscarClientes(termino);
            } else {
                $('#resultadosClientes').html('<div class="list-group-item">Escribe al menos 3 letras para buscar.</div>');
            }
        });
        function buscarClientes(termino) {
            $.ajax({
                url: 'api/clientes.php',
                type: 'GET',
                data: { termino: termino },
                success: function (respuesta) {
                    $('#resultadosClientes').empty();
                    if (Array.isArray(respuesta) && respuesta.length > 0) {
                        respuesta.forEach(cliente => {
                            $('#resultadosClientes').append(`
                                <button class="list-group-item list-group-item-action" onclick="seleccionarCliente('${cliente.nombre}')">
                                    ${cliente.nombre} - ${cliente.direccion || ''} - ${cliente.telefono || ''} - ${cliente.email || ''}
                                </button>
                            `);
                        });
                    } else {
                        $('#resultadosClientes').html('<div class="list-group-item">No se encontraron clientes.</div>');
                    }
                },
                error: function () {
                    $('#resultadosClientes').html('<div class="list-group-item text-danger">Error al buscar clientes.</div>');
                }
            });
        }

        // Seleccionar cliente de la lista
        window.seleccionarCliente = function (nombre) {
            $('#clienteInput').val(nombre); // Rellenar el campo con el cliente seleccionado
            $('#modalBusquedaClientes').modal('hide'); // Cerrar el modal
        };

        // Usar el texto escrito si no se selecciona un cliente
        $('#btnUsarTextoCliente').on('click', function () {
            const textoEscrito = $('#clienteInput').val();
            if (textoEscrito.length > 0) {
                $('#clienteInput').val(textoEscrito); // Usar lo que escribió el usuario
            }
        });

        // Cuando el usuario escribe en el campo de cliente
        $('#clienteInput').on('input', function () {
            const texto = $(this).val();
            if (texto.length >= 3) {
                buscarClientes(texto);
            }
        });
        function formatearNumero(numero) {
            return new Intl.NumberFormat('es-AR', {
                style: 'decimal',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(numero);
        }

        // Función para mostrar productos después de consultar la API
        function mostrarProductos(respuesta) {
            $('#resultadosBusqueda').empty();

            if (respuesta.productos && respuesta.productos.length > 0) {
                respuesta.productos.forEach(function (producto) {
                    let tieneUnidad = producto.stock_unidad > 0 && producto.precio_unitario > 0;
                    let tienePack = producto.stock_pack > 0 && producto.precio_pack > 0;

                    if (tieneUnidad || tienePack) {
                        // Mostrar con select si ambos están disponibles
                        if (tieneUnidad && tienePack) {
                            $('#resultadosBusqueda').append(`
                                <div class="list-group-item">
                                    <strong>${producto.nombre}</strong> ${producto.descripcion}
                                    <div class="form-group mt-2">
                                        <select class="form-select form-select-sm" onchange="seleccionarTipoProducto(${producto.id}, this.value)">
                                            <option value="unidad">Unidad - $${formatearNumero(producto.precio_unitario)} (Stock: ${producto.stock_unidad})</option>
                                            <option value="pack">Pack - $${formatearNumero(producto.precio_pack)} (Stock: ${producto.stock_pack})</option>
                                        </select>
                                        <button class="btn btn-sm btn-primary mt-2" onclick="agregarProducto(${producto.id}, '${producto.nombre}', '${producto.descripcion}', ${producto.precio_unitario}, ${producto.precio_pack}, ${producto.stock_unidad}, ${producto.stock_pack}, 'unidad')">
                                            Agregar al pedido
                                        </button>
                                    </div>
                                </div>
                            `);
                        } 
                        // Mostrar solo unidad si solo tiene unidad
                        else if (tieneUnidad) {
                            $('#resultadosBusqueda').append(`
                                <div class="list-group-item">
                                    <strong>${producto.nombre}</strong> ${producto.descripcion}
                                    - Unidad: $${formatearNumero(producto.precio_unitario)} (Stock: ${producto.stock_unidad})
                                    <button class="btn btn-sm btn-primary mt-2" onclick="agregarProducto(${producto.id}, '${producto.nombre}', '${producto.descripcion}', ${producto.precio_unitario}, 0, ${producto.stock_unidad}, 0, 'unidad')">
                                        Agregar Unidad
                                    </button>
                                </div>
                            `);
                        } 
                        // Mostrar solo pack si solo tiene pack
                        else if (tienePack) {
                            $('#resultadosBusqueda').append(`
                                <div class="list-group-item">
                                    <strong>${producto.nombre}</strong> ${producto.descripcion}
                                    - Pack: $${formatearNumero(producto.precio_pack)} (Stock: ${producto.stock_pack})
                                    <button class="btn btn-sm btn-primary mt-2" onclick="agregarProducto(${producto.id}, '${producto.nombre}', '${producto.descripcion}', 0, ${producto.precio_pack}, 0, ${producto.stock_pack}, 'pack')">
                                        Agregar Pack
                                    </button>
                                </div>
                            `);
                        }
                    }
                });
            } else {
                $('#resultadosBusqueda').append('<div class="list-group-item">No se encontraron productos que cumplan las condiciones.</div>');
            }
        }
        // Buscar productos con AJAX
        $('#productoInput').on('keyup', function () {
            let termino = $(this).val();
            let filtroTipo = $('#filtroTipo').val(); // Captura el valor del filtro (pack/unidad/todos)
            if (termino.length >= 3) {
                $.ajax({
                    url: 'api/products.php',
                    type: 'GET',
                    data: { termino: termino },
                    success: function (respuesta) {
                        $('#resultadosBusqueda').empty();
                        if (respuesta.productos.length > 0) {
                            respuesta.productos.forEach(function (producto) {
                                $('#resultadosBusqueda').append(
                                    `<button class="list-group-item list-group-item-action" 
                                        onclick="agregarProducto(${producto.id}, '${producto.nombre}', 
                                        '${producto.descripcion}', ${producto.precio_unitario || 0}, 
                                        ${producto.precio_pack || 0}, ${producto.stock_unidad}, ${producto.stock_pack})">
                                        ${producto.nombre} ${producto.descripcion} 
                                        - ${producto.precio_unitario > 0 ? `Unidad: $${formatearNumero(producto.precio_unitario)}` : ''} 
                                        ${producto.precio_pack > 0 ? `Pack: $${formatearNumero(producto.precio_pack)}` : ''}
                                    </button>`
                                );
                            });
                        } else {
                            $('#resultadosBusqueda').append('<div class="list-group-item">No se encontraron productos.</div>');
                        }
                    },
                    error: function () {
                        $('#resultadosBusqueda').html('<div class="list-group-item text-danger">Error al buscar productos.</div>');
                    }
                });
            } else {
                $('#resultadosBusqueda').empty();
            }
        });

        // Agregar producto al pedido
        window.agregarProducto = function (id, nombre, descripcion, precio_unitario, precio_pack, stock_unidad, stock_pack) {
            let productoExistente = productosEnPedido.find(p => p.id === id);
            // Lógica para determinar qué tipo (pack o unidad) agregar inicialmente
            let tipoSeleccionado = '';
            let precioSeleccionado = 0;
            let stockSeleccionado = 0;

            // Si hay stock y precio en pack, selecciona pack por defecto
            if (precio_pack > 0 && stock_pack > 0) {
                tipoSeleccionado = 'pack';
                precioSeleccionado = precio_pack;
                stockSeleccionado = stock_pack;
            }
            // Si no hay stock en pack pero sí en unidad, selecciona unidad
            else if (precio_unitario > 0 && stock_unidad > 0) {
                tipoSeleccionado = 'unidad';
                precioSeleccionado = precio_unitario;
                stockSeleccionado = stock_unidad;
            } else {
                // Si no hay stock disponible, muestra un mensaje y no agrega el producto
                mostrarMensajeModal('Este producto no tiene stock disponible.');
                return;
            }

            // Si el producto ya existe en el pedido, incrementa la cantidad según el tipo seleccionado
            if (!productoExistente) {
                productosEnPedido.push({
                    id: id,
                    nombre: nombre,
                    descripcion: descripcion,
                    precio_unitario: precio_unitario || 0,
                    precio_pack: precio_pack || 0,
                    cantidad: 1,
                    stock_unidad: stock_unidad || 0,
                    stock_pack: stock_pack || 0,
                    tipo: tipoSeleccionado // Establece el tipo según la lógica anterior
                });
            } else {
                if (productoExistente.tipo === 'unidad' && productoExistente.cantidad < stock_unidad) {
                    productoExistente.cantidad++;
                } else if (productoExistente.tipo === 'pack' && productoExistente.cantidad < stock_pack) {
                    productoExistente.cantidad++;
                } else {
                    mostrarMensajeModal('No hay suficiente stock disponible para este producto.');
                    return;
                }
            }

            // Actualiza la tabla del pedido
            actualizarTablaPedido();

            // Limpia la entrada de búsqueda y los resultados
            $('#productoInput').val('');
            $('#resultadosBusqueda').empty();
        };

        // Actualizar la tabla del pedido
        function actualizarTablaPedido() {
            const container = $('#pedidoActual');
            container.empty(); // Limpiar contenido previo
            let totalPedido = 0;

            if (productosEnPedido.length > 0) {
                productosEnPedido.forEach(producto => {
                    const precioSeleccionado = producto.tipo === 'unidad' ? producto.precio_unitario : producto.precio_pack;
                    const totalProducto = precioSeleccionado * producto.cantidad;
                    totalPedido += totalProducto;

                    container.append(`
                        <div class="product-card">
                            <div class="product-card-title">${producto.nombre} ${producto.descripcion}</div>
                            <div class="product-card-details">
                                <p><strong>Precio:</strong> $${formatearNumero(precioSeleccionado)}</p>
                                <p><strong>Total:</strong> $${formatearNumero(totalProducto)}</p>
                            </div>
                            <div class="product-card-actions">
                                <select class="form-select form-select-sm h-38 w-50 mx-3" onchange="cambiarTipoProducto(${producto.id}, this.value)">
                                    <option value="unidad" ${producto.tipo === 'unidad' ? 'selected' : ''} ${producto.stock_unidad > 0 ? '' : 'disabled'}>
                                        Unidad
                                    </option>
                                    <option value="pack" ${producto.tipo === 'pack' ? 'selected' : ''} ${producto.stock_pack > 0 ? '' : 'disabled'}>
                                        Pack
                                    </option>
                                </select>
                                <input type="number" 
                                    class="form-control cantidadProducto" 
                                    data-id="${producto.id}" 
                                    value="${producto.cantidad}" 
                                    min="1" 
                                    max="${producto.tipo === 'unidad' ? producto.stock_unidad : producto.stock_pack}" 
                                    onchange="actualizarCantidad(${producto.id}, this.value)">
                            </div>
                            <button class="btn btn-danger btn-sm mt-2" onclick="eliminarProducto(${producto.id})">Eliminar</button>
                        </div>
                    `);
                });
            } else {
                container.append('<div class="text-center text-muted">No hay productos en el pedido.</div>');
            }

            $('#totalPedido').text(formatearNumero(totalPedido));
        }

        // Cambiar tipo de precio del producto (unidad/pack)
        window.cambiarTipoProducto = function (id, nuevoTipo) {
            let producto = productosEnPedido.find(p => p.id === id);
            if (producto) {
                producto.tipo = nuevoTipo;
                // Restablecer la cantidad a 1
                producto.cantidad = 1;
                // Actualizar la tabla del pedido para reflejar los cambios
                actualizarTablaPedido();
            }
        };

        // Actualizar cantidad de un producto
        window.actualizarCantidad = function (id, nuevaCantidad) {
            let producto = productosEnPedido.find(p => p.id === id);
            if (producto) {
                nuevaCantidad = parseInt(nuevaCantidad);
                if (nuevaCantidad >= 1 && nuevaCantidad <= (producto.tipo === 'unidad' ? producto.stock_unidad : producto.stock_pack)) {
                    producto.cantidad = nuevaCantidad;
                    actualizarTablaPedido();
                } else if (nuevaCantidad < 1) {
                    eliminarProducto(id); // Eliminar si la cantidad es menor a 1
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

        // Mostrar mensaje en un modal
        function mostrarMensajeModal(mensaje) {
            $('#modalMensajeCuerpo').text(mensaje);
            $('#modalMensaje').modal('show');
        }

        // Confirmar pedido
        $('#confirmarPedido').on('click', function () {
            if (productosEnPedido.length === 0) {
                mostrarMensajeModal("No hay productos en el pedido para confirmar.");
                return;
            }

            const cliente = $('#clienteInput').val().trim();

            // Validar que el cliente tenga al menos 3 letras
            if (!cliente || cliente.length < 3) {
                mostrarMensajeModal("El campo 'Cliente' debe tener al menos 3 letras.");
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
                success: function (response) {
                    if (response.estado === "Confirmado") {
                        $.ajax({
                            url: 'api/clientes.php', // Ruta para obtener datos del cliente
                            type: 'GET',
                            data: { termino: cliente },
                            success: function (clientes) {
                                const clienteData = clientes.length > 0 ? clientes[0] : null; // Toma el primer cliente encontrado
                                generarPDF(response, clienteData); // Genera el PDF con los datos del pedido y del cliente
                                mostrarMensajeModal(response.message); // Mostrar mensaje de éxito
                                limpiarDatos(); // Limpiar los campos
                            },
                            error: function () {
                                generarPDF(response, null); // Generar PDF sin datos del cliente si hay error
                                mostrarMensajeModal("Pedido confirmado correctamente, pero no se pudo obtener datos del cliente.");
                                limpiarDatos();
                            }
                        });
                    }
                },
                error: function () {
                    mostrarMensajeModal("Error al confirmar el pedido. Por favor, inténtalo de nuevo.");
                }
            });
        });

        // Generar PDF duplicando la información para partir la hoja
        function generarPDF(pedidoData, clienteData) {
            const doc = new window.jspdf.jsPDF();

            const generarSeccion = (inicioY) => {
                doc.text("Distribuidora", 10, inicioY);
                doc.text(`Pedido ID: ${pedidoData.pedido_id}`, 10, inicioY + 10);
                doc.text(`Estado: ${pedidoData.estado}`, 10, inicioY + 20);
                doc.text(`Cliente: ${clienteData ? clienteData.nombre : 'No especificado'}`, 10, inicioY + 30);
                if (clienteData) {
                    doc.text(`Dirección: ${clienteData.direccion}`, 10, inicioY + 40);
                    doc.text(`Teléfono: ${clienteData.telefono}`, 10, inicioY + 50);
                }

                doc.text("Productos:", 10, inicioY + 60);

                let inicioProductosY = inicioY + 70;
                productosEnPedido.forEach((producto, index) => {
                    const tipoProducto = producto.tipo === "unidad" ? "Unitario" : "Pack";
                    doc.text(
                        `${index + 1}. ${producto.nombre} (${tipoProducto}) - Cantidad: ${producto.cantidad} - Precio: $${formatearNumero(
                            producto.tipo === "unidad" ? producto.precio_unitario : producto.precio_pack
                        )} - Total: $${formatearNumero(
                            producto.tipo === "unidad"
                                ? producto.cantidad * producto.precio_unitario
                                : producto.cantidad * producto.precio_pack
                        )}`,
                        10,
                        inicioProductosY
                    );
                    inicioProductosY += 10;
                });

                doc.text(`Total del Pedido: $${formatearNumero(pedidoData.total)}`, 10, inicioProductosY + 10);
            };

            // Duplicar los datos en la hoja
            generarSeccion(10); // Primera mitad
            generarSeccion(150); // Segunda mitad

            // Guardar PDF
            doc.save(`pedido_${pedidoData.pedido_id}.pdf`);
        }


        // Cancelar pedido
        $('#cancelarPedido').on('click', function () {
            limpiarDatos(); // Limpiar todos los datos
            mostrarMensajeModal("Pedido cancelado correctamente.");
        });

        // Función para limpiar todos los datos y restablecer el formulario
        function limpiarDatos() {
            productosEnPedido = [];
            actualizarTablaPedido();
            $('#clienteInput').val('');
            $('#tipoPedido').val('Caja');
            $('#productoInput').val('');
            $('#resultadosBusqueda').empty();
        }

        // Generar PDF del pedido
        function generarPDF() {
            const doc = new window.jspdf.jsPDF();

            doc.text("Distribuidora", 10, 10);
            doc.text(`Cliente: ${$('#clienteInput').val()}`, 10, 20);
            doc.text(`Tipo de Pedido: ${$('#tipoPedido').val()}`, 10, 30);

            let inicioY = 40;
            doc.text("Productos:", 10, inicioY);

            productosEnPedido.forEach((producto, index) => {
                inicioY += 10;
                doc.text(
                    `${index + 1}. ${producto.nombre} - Cantidad: ${producto.cantidad} - Total: $${(producto.precio_unitario * producto.cantidad).toFixed(2)}`,
                    10,
                    inicioY
                );
            });

            inicioY += 10;
            doc.text(`Total del Pedido: $${productosEnPedido.reduce((acc, p) => acc + (p.precio_unitario * p.cantidad), 0).toFixed(2)}`, 10, inicioY);

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
