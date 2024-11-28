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
        .navbar {
            background-color: #00bfff;
        }

        .navbar .navbar-brand,
        .navbar .btn {
            color: #fff;
        }

        .navbar .btn:hover {
            background-color: #007acc;
        }

        .section-header {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .split {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .split>div {
            flex: 1;
            min-width: 45%;
        }

        .order-table th,
        .order-table td {
            text-align: center;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Distribuidora</a>
            <button class="btn btn-outline-light" id="logoutButton">Cerrar Sesión</button>
        </div>
    </nav>
    <div class="container my-4">
        <h1 class="text-center mb-4">Panel Vendedor</h1>

        <div class="split">
            <!-- Gestión de Pedidos -->
            <div class="card p-4">
                <h2 class="section-header">Gestión de Pedidos</h2>

                <!-- Cliente -->
                <div class="mb-3">
                    <label for="clienteInput" class="form-label">Cliente</label>
                    <input type="text" class="form-control" id="clienteInput" placeholder="Buscar cliente (2 letras mínimo)">
                </div>

                <!-- Tipo de Pedido -->
                <div class="mb-3">
                    <label for="tipoPedido" class="form-label">Tipo de Pedido</label>
                    <select class="form-control" id="tipoPedido">
                        <option value="Caja">Caja</option>
                        <option value="Reparto">Reparto</option>
                    </select>
                </div>

                <!-- Productos -->
                <div class="mb-3">
                    <label for="productoInput" class="form-label">Producto</label>
                    <input type="text" class="form-control" id="productoInput" placeholder="Buscar producto (3 letras mínimo)">
                    <div id="resultadosBusqueda" class="list-group mt-2"></div>
                </div>

                <!-- Pedido Actual -->
                <table class="table table-striped order-table mt-3">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="pedidoActual">
                        <tr>
                            <td colspan="5">No hay productos en el pedido.</td>
                        </tr>
                    </tbody>
                </table>

                <button class="btn btn-primary w-100 mt-3" id="confirmarPedido">Confirmar Pedido</button>
                <button class="btn btn-danger w-100 mt-3" id="cancelarPedido">Cancelar Pedido</button>
                <h4 class="text-end mt-3" id="totalPedido">Total: $0</h4>
            </div>

            <!-- Funciones Complementarias -->
            <div class="card p-4">
                <h2 class="section-header">Funciones Complementarias</h2>

                <!-- Historial de Pedidos -->
                <button class="btn btn-outline-primary w-100 mb-3" data-bs-toggle="modal" data-bs-target="#modalHistorialPedidos">
                    <span class="material-icons">history</span> Historial de Pedidos
                </button>

                <!-- Añadir Producto -->
                <a href="añadir_producto.php" class="btn btn-outline-success w-100 mb-3">
                    <span class="material-icons">add_circle</span> Añadir Producto
                </a>

                <!-- Modificar Producto -->
                <a href="modificar_producto.php" class="btn btn-outline-warning w-100 mb-3">
                    <span class="material-icons">edit</span> Modificar Producto
                </a>
            </div>
        </div>
    </div>

    <!-- Modal para mostrar errores y mensajes -->
    <div class="modal fade" id="modalMensaje" tabindex="-1" aria-labelledby="modalMensajeLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMensajeLabel">Mensaje</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalMensajeCuerpo">
                    <!-- Mensaje de error o información -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>

    <script>
    $(document).ready(function() {
        let productosEnPedido = [];

        // Function to show messages in a modal
        function mostrarMensajeModal(mensaje) {
            $('#modalMensajeCuerpo').text(mensaje);
            $('#modalMensaje').modal('show');
        }

        // Search for products with AJAX
        $('#productoInput').on('keyup', function() {
            let termino = $(this).val();
            if (termino.length >= 3) {
                $.ajax({
                    url: 'api/products.php',
                    type: 'GET',
                    data: { termino: termino },
                    success: function(respuesta) {
                        $('#resultadosBusqueda').empty();
                        if (respuesta.length > 0) {
                            respuesta.forEach(function(producto) {
                                $('#resultadosBusqueda').append(
                                    `<button class="list-group-item list-group-item-action" onclick="agregarProducto(${producto.id}, '${producto.nombre}', ${producto.precio}, ${producto.stock})">
                                        ${producto.nombre} - $${producto.precio}
                                    </button>`
                                );
                            });
                        } else {
                            $('#resultadosBusqueda').append('<div class="list-group-item">No se encontraron productos.</div>');
                        }
                    },
                    error: function() {
                        mostrarMensajeModal("Error al realizar la búsqueda.");
                    }
                });
            } else {
                $('#resultadosBusqueda').empty();
            }
        });

        // Add product to the current order
        window.agregarProducto = function(id, nombre, precio, stock) {
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
            productosEnPedido.forEach(function(producto) {
                let totalProducto = producto.precio * producto.cantidad;
                totalPedido += totalProducto;
                tbody.append(`
                    <tr>
                        <td>${producto.nombre}</td>
                        <td><input type="number" class="form-control text-center cantidadProducto" data-id="${producto.id}" value="${producto.cantidad}" min="1" max="${producto.stock}" onchange="actualizarCantidad(${producto.id}, this.value)"></td>
                        <td>${producto.precio}</td>
                        <td class="totalProducto">${totalProducto}</td>
                        <td><button class="btn btn-danger btn-sm" onclick="eliminarProducto(${producto.id})">Eliminar</button></td>
                    </tr>
                `);
            });
            if (productosEnPedido.length === 0) {
                tbody.append('<tr><td colspan="5">No hay productos en el pedido.</td></tr>');
            }
            $('#totalPedido').text(`Total: $${totalPedido}`);
        }

        // Update the quantity of a product in the order
        window.actualizarCantidad = function(id, nuevaCantidad) {
            let producto = productosEnPedido.find(p => p.id === id);
            if (producto) {
                nuevaCantidad = parseInt(nuevaCantidad);
                if (nuevaCantidad > producto.stock) {
                    mostrarMensajeModal("No hay suficiente stock disponible.");
                    // Find the specific input for this product and set its value to 1
                    $(`input.cantidadProducto[data-id="${id}"]`).val(1);
                    producto.cantidad = 1; // Also update the product's quantity in the array
                    actualizarTablaPedido(); // Refresh the table to reflect the change
                    return;
                }
                producto.cantidad = nuevaCantidad;
                if (producto.cantidad <= 0) {
                    eliminarProducto(id);
                } else {
                    actualizarTablaPedido();
                }
            }
        };

        // Remove product from the order
        window.eliminarProducto = function(id) {
            let producto = productosEnPedido.find(p => p.id === id);
            if (producto) {
                productosEnPedido = productosEnPedido.filter(p => p.id !== id);
                actualizarTablaPedido();
            }
        };

        // Confirm order
        $('#confirmarPedido').on('click', function() {
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
                success: function(respuesta) {
                    mostrarMensajeModal("Pedido confirmado correctamente");
                    productosEnPedido = [];
                    actualizarTablaPedido();
                    if (tipoPedido === 'Reparto') {
                        generarPDF();
                    }
                },
                error: function(jqXHR) {
                    let errorMsg = jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : "Error al confirmar el pedido";
                    if (errorMsg.includes("stock insuficiente")) {
                        productosEnPedido.forEach(function(producto) {
                            producto.cantidad = 1;
                        });
                        actualizarTablaPedido();
                    }
                    mostrarMensajeModal(errorMsg);
                }
            });
        });

        // Cancel order
        $('#cancelarPedido').on('click', function() {
            if (confirm("¿Está seguro de que desea cancelar el pedido? Todos los productos se eliminarán.")) {
                productosEnPedido = [];
                actualizarTablaPedido();
            }
        });

        // Función para generar PDF utilizando jsPDF
        function generarPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Encabezado del PDF
            doc.setFontSize(16);
            doc.text("Distribuidora - Pedido", 10, 10);
            doc.setFontSize(12);
            doc.text(`Cliente: ${document.getElementById('clienteInput').value}`, 10, 20);
            doc.text(`Tipo de Pedido: ${document.getElementById('tipoPedido').value}`, 10, 30);
            doc.text(`Fecha: ${new Date().toLocaleString()}`, 10, 40);

            // Agregar una línea para dividir el encabezado del contenido
            doc.line(10, 45, 200, 45);

            // Encabezado de la tabla de productos
            doc.text("Productos:", 10, 55);
            let y = 65;

            // Añadir cada producto al PDF
            productosEnPedido.forEach((producto, index) => {
                doc.text(`${index + 1}. Producto: ${producto.nombre}`, 10, y);
                doc.text(`Cantidad: ${producto.cantidad} - Precio: $${producto.precio} - Total: $${(producto.precio * producto.cantidad).toFixed(2)}`, 10, y + 10);
                y += 20;
            });

            // Mostrar el total del pedido al final
            const totalPedido = productosEnPedido.reduce((acc, producto) => acc + (producto.precio * producto.cantidad), 0);
            doc.setFontSize(14);
            doc.text(`Total del Pedido: $${totalPedido.toFixed(2)}`, 10, y + 10);

            // Guardar el PDF con un nombre específico
            doc.save("pedido.pdf");
        }
    });
    </script>

  </body>
</html>
