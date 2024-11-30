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
            --background-color: #f8f9fa;
            --text-color: #333;
            --header-color: #ffffff;
        }

        body {
            background-color: var(--background-color);
            font-family: Arial, sans-serif;
            color: var(--text-color);
        }

        .navbar {
            background-color: var(--primary-color);
        }

        .navbar .navbar-brand,
        .navbar .btn {
            color: var(--header-color);
        }

        .navbar .btn:hover {
            background-color: var(--secondary-color);
        }

        .content-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 30px;
        }

        .card {
            flex: 1;
            min-width: 280px;
            background-color: var(--header-color);
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .card-header {
            font-size: 1.2rem;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            margin-bottom: 15px;
        }

        .form-control {
            border-radius: 8px;
        }

        .order-table {
            border-collapse: collapse;
            width: 100%;
        }

        .order-table th,
        .order-table td {
            text-align: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .order-table th {
            background-color: var(--primary-color);
            color: var(--header-color);
        }

        .btn-primary,
        .btn-danger {
            width: 100%;
            border-radius: 25px;
            padding: 10px;
            margin: 10px 0;
        }

        .btn-primary {
            background-color: var(--primary-color);
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

        .input-group {
            margin-bottom: 20px;
        }

        .order-summary {
            margin-top: 30px;
            text-align: right;
            font-weight: bold;
            font-size: 1.25rem;
        }

        @media (max-width: 768px) {
            .order-summary {
                text-align: center;
            }

            .content-wrapper {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Distribuidora - Panel Vendedor</a>
            <button class="btn btn-outline-light" id="logoutButton">Cerrar Sesión</button>
        </div>
    </nav>
    <div class="container my-4">
        <h1 class="text-center mb-4">Panel de Gestión de Pedidos</h1>

        <div class="content-wrapper">
            <!-- Gestor de Pedidos -->
            <div class="card">
                <div class="card-header">Gestor de Pedidos</div>

                <!-- Formulario para Cliente y Producto -->
                <div class="input-group">
                    <label for="clienteInput" class="form-label">Cliente</label>
                    <input type="text" class="form-control" id="clienteInput" placeholder="Buscar cliente (2 letras mínimo)">
                </div>

                <div class="input-group">
                    <label for="tipoPedido" class="form-label">Tipo de Pedido</label>
                    <select class="form-control" id="tipoPedido">
                        <option value="Caja">Caja</option>
                        <option value="Reparto">Reparto</option>
                    </select>
                </div>

                <div class="input-group">
                    <label for="productoInput" class="form-label">Producto</label>
                    <input type="text" class="form-control" id="productoInput" placeholder="Buscar producto (3 letras mínimo)">
                    <div id="resultadosBusqueda" class="list-group mt-2"></div>
                </div>

                <!-- Tabla Pedido Actual -->
                <table class="order-table mt-3">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="pedidoActual">
                        <tr>
                            <td colspan="6">No hay productos en el pedido.</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Botones de Acciones -->
                <button class="btn btn-primary" id="confirmarPedido">Confirmar Pedido</button>
                <button class="btn btn-danger" id="cancelarPedido">Cancelar Pedido</button>
                <div class="order-summary" id="totalPedido">Total: $0</div>
            </div>

                <!-- Historial de Pedidos -->
            <div class="card">
                <div class="card-header">Historial de Pedidos</div>
                <button class="btn btn-outline-primary w-100 mb-3" data-bs-toggle="modal" data-bs-target="#modalHistorialPedidos">
                    <span class="material-icons">history</span> Ver Historial de Pedidos
                </button>
            </div>
        </div>
        <!-- Modal de Mensaje -->
        <div class="modal fade" id="modalMensaje" tabindex="-1" aria-labelledby="modalMensajeLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalMensajeLabel">Mensaje</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="modalMensajeCuerpo">
                        <!-- Aquí se mostrará el mensaje -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery y Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <!-- Incluye jsPDF-AutoTable -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            let productosEnPedido = [];

            // Function to show messages in a modal
            function mostrarMensajeModal(mensaje) {
                $('#modalMensajeCuerpo').text(mensaje);
                $('#modalMensaje').modal('show');
            }

            // Search for products with AJAX
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
                                        `<button class="list-group-item list-group-item-action" onclick="agregarProducto(${producto.id}, '${producto.nombre}', '${producto.descripcion}', ${producto.precio}, ${producto.stock})">
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

            // Add product to the current order
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

            // Update the current order table
            function actualizarTablaPedido() {
                let tbody = $('#pedidoActual');
                tbody.empty();
                let totalPedido = 0;
                productosEnPedido.forEach(function (producto) {
                    let totalProducto = producto.precio * producto.cantidad;
                    totalPedido += totalProducto;
                    tbody.append(`
                        <tr>
                            <td>${producto.nombre}</td>
                            <td>${producto.descripcion}</td>
                            <td><input type="number" class="form-control text-center cantidadProducto" data-id="${producto.id}" value="${producto.cantidad}" min="1" max="${producto.stock}" onchange="actualizarCantidad(${producto.id}, this.value)"></td>
                            <td>${producto.precio}</td>
                            <td class="totalProducto">${totalProducto}</td>
                            <td><button class="btn btn-danger btn-sm" onclick="eliminarProducto(${producto.id})">Eliminar</button></td>
                        </tr>
                    `);
                });
                if (productosEnPedido.length === 0) {
                    tbody.append('<tr><td colspan="6">No hay productos en el pedido.</td></tr>');
                }
                $('#totalPedido').text(`Total: $${totalPedido}`);
            }

            // Función para generar PDF con jsPDF
            function generarPDF() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();

                // Definir una función auxiliar para generar la misma información en dos secciones de la página
                function generarContenido(startY) {
                    // Encabezado de la Distribuidora (Compacto y Organizado)
                    doc.setFontSize(14);
                    doc.setFont("helvetica", "bold");
                    doc.setTextColor(0, 0, 0);
                    doc.text("LA DOCE", 10, startY);

                    doc.setFontSize(10);
                    doc.setFont("helvetica", "normal");
                    doc.text("Necochea 1350 (CABA), LA BOCA", 10, startY + 6);
                    doc.text("Tel: 1559092429 - WhatsApp: 1557713277", 10, startY + 12);
                    doc.text("ladocedistribuidora@hotmail.com", 10, startY + 18);
                    doc.text("Documento no válido como factura", 10, startY + 24);

                    // Detalles del Remito
                    doc.setFontSize(14);
                    doc.setFont("helvetica", "bold");
                    doc.text("REMITO FICHA", 140, startY);
                    doc.setFontSize(10);
                    doc.setFont("helvetica", "normal");
                    doc.text(`Fecha: ${new Date().toLocaleDateString()}`, 140, startY + 6);

                    // Información del Cliente
                    doc.setFontSize(12);
                    doc.setFont("helvetica", "bold");
                    doc.text(`Sres.: ${$('#clienteInput').val()}`, 10, startY + 35);

                    // Productos del Pedido (en Columnas)
                    let yPosition = startY + 45;
                    doc.setFontSize(10);
                    doc.setFont("helvetica", "bold");
                    doc.text("Productos:", 10, yPosition);
                    yPosition += 8;

                    // Configuración de columnas
                    const col1X = 10; // Primera columna
                    const col2X = 110; // Segunda columna
                    let col = 0; // Control de columna (0 para izquierda, 1 para derecha)
                    let columnYPos = yPosition;

                    productosEnPedido.forEach((producto, index) => {
                        // Preparar el texto del producto
                        const productoTexto = `${index + 1}. ${producto.nombre} - ${producto.cantidad} ${producto.unidades || ''} - $${producto.precio.toFixed(2)} - Total: $${(producto.precio * producto.cantidad).toFixed(2)}`;

                        // Colocar el texto en la columna correspondiente
                        if (col === 0) {
                            // Primera columna (izquierda)
                            doc.setFontSize(10);
                            doc.setFont("helvetica", "normal");
                            doc.text(productoTexto, col1X, columnYPos);
                            col = 1; // Cambiar a la siguiente columna
                        } else {
                            // Segunda columna (derecha)
                            doc.setFontSize(10);
                            doc.setFont("helvetica", "normal");
                            doc.text(productoTexto, col2X, columnYPos);
                            col = 0; // Volver a la columna izquierda
                            columnYPos += 8; // Incrementar la posición Y solo cuando se termina una fila completa
                        }

                        // Si el producto está en la última columna pero no hay más productos para emparejar
                        if (index === productosEnPedido.length - 1 && col === 1) {
                            columnYPos += 8; // Incrementar para evitar superposición
                        }

                        // Si se acerca al borde inferior de la página, pasa a la siguiente sección
                        if (columnYPos > 260) {
                            columnYPos = startY + 10; // Reiniciar en la mitad inferior de la página para la segunda copia
                            col = 0; // Reiniciar a la primera columna
                        }
                    });

                    // Total del Pedido
                    columnYPos += 10;
                    doc.setFontSize(12);
                    doc.setFont("helvetica", "bold");
                    doc.text(`Total: $${productosEnPedido.reduce((sum, producto) => sum + (producto.precio * producto.cantidad), 0).toFixed(2)}`, 10, columnYPos);

                    // Nota Final y Recibi de Conformidad
                    columnYPos += 15;
                    doc.setFontSize(10);
                    doc.setFont("helvetica", "normal");
                    doc.text("Una vez recibida la mercadería, no se aceptan devoluciones.", 10, columnYPos);
                    doc.text("Recibi de conformidad:", 140, columnYPos);
                }

                // Generar el contenido dos veces, para la parte superior e inferior de la hoja
                generarContenido(10); // Primera copia en la parte superior
                generarContenido(150); // Segunda copia en la parte inferior

                // Guardar el PDF con un nombre específico
                doc.save("pedido.pdf");
            }

            // Confirm order
            $('#confirmarPedido').on('click', function () {
                if (productosEnPedido.length === 0) {
                    mostrarMensajeModal("No hay productos en el pedido para confirmar.");
                    return;
                }

                let tipoPedido = $('#tipoPedido').val();
                let cliente = $('#clienteInput').val().trim();

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
                        tipoPedido: tipoPedido,
                        productos: productosEnPedido
                    }),
                    success: function () {
                        mostrarMensajeModal("Pedido confirmado correctamente");
                        generarPDF();
                        productosEnPedido = [];
                        actualizarTablaPedido();
                    },
                    error: function () {
                        mostrarMensajeModal("Error al confirmar el pedido.");
                    }
                });
            });
            // Cancel order
            $('#cancelarPedido').on('click', function () {
                if (productosEnPedido.length === 0) {
                    mostrarMensajeModal("No hay productos para cancelar.");
                    return;
                }

                // Vaciar la lista de productos
                productosEnPedido = [];

                // Actualizar la tabla para reflejar que el pedido ha sido cancelado
                actualizarTablaPedido();

                // Limpiar los inputs
                $('#clienteInput').val('');
                $('#tipoPedido').val('Caja'); // Restablecer al valor por defecto
                $('#productoInput').val('');
                $('#resultadosBusqueda').empty(); // Limpiar los resultados de búsqueda del producto

                mostrarMensajeModal("Pedido cancelado correctamente.");
            });

        });
    </script>
</body>

</html>
