<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribuidora - Panel Vendedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    <!-- Incluye jsPDF-AutoTable -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
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
                    if (tipoPedido === 'Reparto') {
                        generarPDF();
                    }
                    productosEnPedido = [];
                    actualizarTablaPedido();
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

        function generarPDF() {
          const { jsPDF } = window.jspdf;
          const doc = new jsPDF();

          // Función para generar el contenido de cada mitad
          function generarContenido(doc, offsetY) {
              // Encabezado de la empresa
              doc.setFontSize(18);
              doc.setFont("helvetica", "bold");
              doc.setTextColor(0, 0, 0);
              doc.text("LA DOCE", 10, offsetY + 15);
              doc.setFontSize(10);
              doc.setFont("helvetica", "normal");
              doc.text("Necochea 1350 (CABA) la boca", 10, offsetY + 20);
              doc.text("1559092429 / Whatsapp 1557713277", 10, offsetY + 25);
              doc.text("ladocedistribuidora@hotmail.com", 10, offsetY + 30);

              // Información del remito
              doc.setFontSize(12);
              doc.setFont("helvetica", "bold");
              doc.text("Remito", 150, offsetY + 15);
              doc.setFont("helvetica", "normal");
              doc.text(`Fecha: ${new Date().toLocaleDateString()}`, 150, offsetY + 20);
              doc.text(`Ficha: ${Math.floor(Math.random() * 10000)}`, 150, offsetY + 25); // Ficha con un número aleatorio
              doc.text(`N° Remito: ${Math.floor(Math.random() * 100000)}`, 150, offsetY + 30);

              // Línea divisoria
              doc.setLineWidth(0.5);
              doc.line(10, offsetY + 35, 200, offsetY + 35);

              // Información del cliente
              doc.setFontSize(12);
              doc.setFont("helvetica", "bold");
              doc.text("Sres:", 10, offsetY + 45);
              doc.setFont("helvetica", "normal");
              doc.text(`${document.getElementById('clienteInput').value}`, 25, offsetY + 45);

              // Encabezado de la tabla de productos
              doc.setFontSize(10);
              doc.setFont("helvetica", "bold");
              doc.text("Código", 10, offsetY + 55);
              doc.text("Unidades", 30, offsetY + 55);
              doc.text("Bultos", 55, offsetY + 55);
              doc.text("Descripción", 80, offsetY + 55);
              doc.text("P. Unitario", 150, offsetY + 55);
              doc.text("Total", 180, offsetY + 55);

              // Línea debajo del encabezado de la tabla
              doc.line(10, offsetY + 58, 200, offsetY + 58);

              // Cuerpo de la tabla de productos
              let startY = offsetY + 65;
              doc.setFont("helvetica", "normal");
              let totalPedido = 0;

              productosEnPedido.forEach((producto, index) => {
                  const totalProducto = producto.precio * producto.cantidad;
                  totalPedido += totalProducto;

                  doc.text(producto.codigo || `#${index + 1}`, 10, startY);
                  doc.text(`${producto.cantidad}`, 30, startY);
                  doc.text(`${producto.bultos || '-'}`, 55, startY); // Si hay bultos o dejar vacío
                  doc.text(`${producto.nombre}`, 80, startY);
                  doc.text(`$${producto.precio.toFixed(2)}`, 150, startY, null, null, "right");
                  doc.text(`$${totalProducto.toFixed(2)}`, 180, startY, null, null, "right");
                  startY += 10;
              });

              // Línea divisoria antes del total
              doc.line(10, startY, 200, startY);
              startY += 5;

              // Total del pedido
              doc.setFontSize(12);
              doc.setFont("helvetica", "bold");
              doc.text(`TOTAL: $${totalPedido.toFixed(2)}`, 180, startY, null, null, "right");
              startY += 10;

              // Aclaraciones al pie del remito
              doc.setFontSize(10);
              doc.setFont("helvetica", "italic");
              doc.setTextColor(0, 0, 0);
              doc.text("Una vez recibida la mercadería no se aceptan devoluciones.", 10, startY);
              startY += 10;

              // Pie de página con hora y máquina
              doc.setFontSize(10);
              doc.setFont("helvetica", "normal");
              doc.text(`Máquina: 1`, 10, startY);
              doc.text(`Hora: ${new Date().toLocaleTimeString()}`, 50, startY);
              doc.text("HOJA 1/1", 150, startY);
          }

          // Generar la primera mitad
          generarContenido(doc, 0);

          // Línea divisoria horizontal para separar las dos mitades
          doc.line(10, 145, 200, 145);

          // Generar la segunda mitad desplazada verticalmente
          generarContenido(doc, 150);

          // Guardar el PDF con un nombre específico
          doc.save("remito.pdf");
        }

      });
    </script>
  </body>
</html>
