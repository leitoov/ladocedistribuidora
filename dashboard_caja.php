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
            grid-template-columns: 1fr;
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
        p#totalAPagar {
            font-size: 21px;
            font-weight: 500;
            margin: 0px;
            padding: 0px;
        }
        p#montoTotalFinal {
            padding: 0px;
            font-size: 30px;
            font-weight: 600;
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

        .text-green {
            color: green;
        }

        .text-red {
            color: red;
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

    <div class="modal fade" id="modalCobrarPedido" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cobrar Pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalCobrarCuerpo">
                    <form id="formCobrarPedido">
                        <div class="mb-3">
                            <label class="form-label">Total a Pagar</label>
                            <p id="totalAPagar" class="form-control-plaintext"></p>
                        </div>
                        <div class="mb-3">
                            <label for="medioPago" class="form-label">Medio de Pago</label>
                            <select id="medioPago" class="form-select">
                                <option value="">Seleccionar</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="mixto">Mixto</option>
                            </select>
                        </div>
                        <div class="mb-3" id="campoEfectivo">
                            <label for="montoEfectivo" class="form-label">Monto en Efectivo</label>
                            <input type="number" class="form-control" id="montoEfectivo" placeholder="0.00">
                        </div>
                        <div class="mb-3" id="campoTransferencia">
                            <label for="montoTransferencia" class="form-label">Monto en Transferencia</label>
                            <input type="number" class="form-control" id="montoTransferencia" placeholder="0.00">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descuento Aplicado (Efectivo)</label>
                            <p id="descuentoAplicado" class="form-control-plaintext text-red"></p>
                            <!-- Botones para aplicar y quitar descuento -->
                            <button type="button" id="botonAplicarDescuento" class="btn btn-warning btn-sm mt-2">Aplicar Descuento</button>
                            <button type="button" id="botonQuitarDescuento" class="btn btn-secondary btn-sm mt-2">Quitar Descuento</button>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Recargo Aplicado (Transferencia)</label>
                            <p id="recargoAplicado" class="form-control-plaintext text-green"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: bold; font-size: 1.2rem; color: blue;">Monto Total Final</label>
                            <p id="montoTotalFinal" class="form-control-plaintext" style="font-weight: bold; font-size: 1.2rem; color: blue;"></p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="confirmarCobro">Confirmar Cobro</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
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
            let pedidosAgrupados = {};

            // Cargar los pedidos para la caja
            function cargarPedidosCaja() {
                $.ajax({
                    url: 'api/orders.php',
                    type: 'POST',
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        token: '<?php echo $_SESSION['token']; ?>',
                    }),
                    success: function (data) {
                        let tbody = $('#pedidosCaja');
                        tbody.empty();
                        // Group orders by pedido_id
                        data.forEach(function(item) {
                            if (!pedidosAgrupados[item.pedido_id]) {
                                pedidosAgrupados[item.pedido_id] = {
                                    pedido_id: item.pedido_id,
                                    fecha: item.fecha,
                                    total: parseFloat(item.total),
                                    estado: item.estado,
                                    tipo_pedido: item.tipo_pedido,
                                    nombre_cliente: item.nombre_cliente,
                                    productos: []
                                };
                            }
                            pedidosAgrupados[item.pedido_id].productos.push({
                                id_producto: item.id_producto,
                                producto_nombre: item.producto_nombre,
                                cantidad: item.cantidad,
                                precio_producto: parseFloat(item.precio_producto)
                            });
                        });

                        // Show orders
                        if (Object.keys(pedidosAgrupados).length > 0) {
                            Object.values(pedidosAgrupados).forEach(function (pedido) {
                                tbody.append(`
                                    <tr>
                                        <td>${pedido.pedido_id}</td>
                                        <td>${pedido.nombre_cliente}</td>
                                        <td>${pedido.tipo_pedido}</td>
                                        <td>$${pedido.total.toFixed(2)}</td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <button class="btn btn-primary" onclick="cobrarPedido(${pedido.pedido_id})">
                                                    Cobrar
                                                </button>
                                                <button class="btn btn-warning" onclick="editarPedido(${pedido.pedido_id})">
                                                    Editar
                                                </button>
                                                <button class="btn btn-danger" onclick="anularPedido(${pedido.pedido_id})">
                                                    Anular
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                `);
                            });
                        } else {
                            tbody.append('<tr><td colspan="5" class="text-center text-muted">No hay pedidos disponibles</td></tr>');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Error en la solicitud:", status, error);
                        console.log("Respuesta del servidor:", xhr.responseText);
                        mostrarMensajeModal("Error al cargar los pedidos de la caja: " + error);
                    }
                });
            }

            // Function to show messages in a modal
            function mostrarMensajeModal(mensaje) {
                $('#modalMensajeCuerpo').text(mensaje);
                $('#modalMensaje').modal('show');
            }

            // Editar Pedido
            window.editarPedido = function (pedidoId) {
                window.location.href = `editar_pedido.php?pedidoId=${pedidoId}`;
            }

            // Cobrar pedido - Mostrar Modal
            window.cobrarPedido = function (pedidoId) {
                let pedido = pedidosAgrupados[pedidoId];
                if (!pedido) {
                    mostrarMensajeModal("Pedido no encontrado");
                    return;
                }

                // Resetear el estado del modal
                resetModal();

                // Llenar datos iniciales del pedido
                $('#totalAPagar').text(formatCurrency(pedido.total));
                $('#montoTotalFinal').text(formatCurrency(pedido.total)); // Mostrar el total inicial
                $('#modalCobrarPedido').modal('show');

                // Asignar eventos para el selector de medios de pago
                $('#medioPago').off('change').on('change', function () {
                    const medioPago = $(this).val();
                    handleMedioPagoChange(medioPago, pedido);
                });

                // Escuchar cambios en los inputs de monto y checkbox de recargo
                $('#montoEfectivo').off('input').on('input', function () {
                    recalcularMixto(pedido);
                });

                $('#recargoEnEfectivo').off('change').on('change', function () {
                    recalcularMixto(pedido);
                });

                // Botón para aplicar descuento en efectivo
                $('#botonAplicarDescuento').off('click').on('click', function () {
                    aplicarDescuento(pedido);
                });

                $('#botonQuitarDescuento').off('click').on('click', function () {
                    quitarDescuento(pedido);
                });

                // Resetear el modal al cerrarlo
                $('#modalCobrarPedido').on('hidden.bs.modal', function () {
                    resetModal();
                });
            };

            // Manejar cambios en el medio de pago
            function handleMedioPagoChange(medioPago, pedido) {
                resetModal(); // Reiniciar antes de manejar el nuevo medio de pago

                if (medioPago === 'efectivo') {
                    $('#descuentoAplicado').parent().show();
                    $('#recargoAplicado').parent().hide();
                    $('#botonAplicarDescuento').show();
                    $('#montoTotalFinal').text(formatCurrency(pedido.total)); // Mostrar el total inicial
                } else if (medioPago === 'transferencia') {
                    const recargo = pedido.total * 0.05;
                    const totalConRecargo = pedido.total + recargo;

                    $('#recargoAplicado').text(formatCurrency(recargo)).addClass('text-green');
                    $('#descuentoAplicado').parent().hide();
                    $('#recargoAplicado').parent().show();
                    $('#botonAplicarDescuento').hide();
                    $('#botonQuitarDescuento').hide();
                    $('#montoTotalFinal').text(formatCurrency(totalConRecargo)); // Mostrar total con recargo
                } else if (medioPago === 'mixto') {
                    $('#campoEfectivo').show();
                    $('#campoTransferencia').show();
                    $('#montoTransferencia').prop('readonly', true); // Hacer el input de transferencia no editable
                    $('#recargoAplicado').parent().show();
                    $('#descuentoAplicado').parent().hide();
                    $('#recargoEnEfectivoContainer').show(); // Mostrar checkbox para recargo en efectivo
                    $('#montoTotalFinal').text(formatCurrency(pedido.total)); // Mostrar el total inicial
                }
            }

            // Aplicar descuento manualmente en efectivo
            function aplicarDescuento(pedido) {
                const totalPedido = pedido.total;
                const descuento = totalPedido * 0.05;
                const totalConDescuento = totalPedido - descuento;

                // Actualizar valores en el modal
                $('#descuentoAplicado').text(formatCurrency(descuento)).addClass('text-red');
                $('#montoTotalFinal').text(formatCurrency(totalConDescuento));
                $('#botonAplicarDescuento').hide();
                $('#botonQuitarDescuento').show();
            }

            // Quitar descuento manualmente
            function quitarDescuento(pedido) {
                const totalPedido = pedido.total;

                // Actualizar valores en el modal
                $('#descuentoAplicado').text(formatCurrency(0)).removeClass('text-red');
                $('#montoTotalFinal').text(formatCurrency(totalPedido));
                $('#botonAplicarDescuento').show();
                $('#botonQuitarDescuento').hide();
            }

            // Recalcular valores para el pago mixto
            function recalcularMixto(pedido) {
                const montoEfectivo = parseFloat($('#montoEfectivo').val()) || 0;
                const totalPedido = pedido.total;
                const recargoPorcentaje = 0.05;

                // Calcular restante en transferencia
                let restanteTransferencia = totalPedido - montoEfectivo;

                // Calcular recargo
                let recargo = restanteTransferencia * recargoPorcentaje;

                // Determinar si el recargo se abona en efectivo
                if ($('#recargoEnEfectivo').is(':checked')) {
                    recargo = 0; // El recargo ya está incluido en efectivo
                    restanteTransferencia = totalPedido - montoEfectivo;
                }

                // Calcular total final
                const totalFinal = montoEfectivo + restanteTransferencia + recargo;

                // Actualizar campos
                $('#montoTransferencia').val(restanteTransferencia > 0 ? restanteTransferencia.toFixed(2) : '');
                $('#recargoAplicado').text(formatCurrency(recargo)).addClass('text-green');
                $('#montoTotalFinal').text(formatCurrency(totalFinal)).css('color', 'blue');
            }

            // Resetear el modal
            function resetModal() {
                $('#medioPago').val('');
                $('#montoEfectivo, #montoTransferencia').val('');
                $('#montoTransferencia').prop('readonly', false); // Hacer editable por defecto
                $('#descuentoAplicado, #recargoAplicado, #montoTotalFinal').text('');
                $('#campoEfectivo, #campoTransferencia, #recargoEnEfectivoContainer').hide();
                $('#descuentoAplicado').parent().hide();
                $('#recargoAplicado').parent().hide();
            }

            // Formatear moneda en formato argentino
            function formatCurrency(value) {
                return new Intl.NumberFormat('es-AR', {
                    style: 'currency',
                    currency: 'ARS',
                }).format(value);
            }





            // Anular pedido
            window.anularPedido = function (pedidoId) {
                if (confirm(`¿Estás seguro de que quieres anular el pedido ${pedidoId}?`)) {
                    mostrarMensajeModal(`Pedido ${pedidoId} anulado.`);
                    cargarPedidosCaja();
                }
            }

            // Cargar pedidos al cargar la página
            cargarPedidosCaja();

            // Cerrar sesión
            $('#logoutButton').on('click', function () {
                window.location.href = 'logout.php';
            });

            // Función para imprimir el ticket
            function imprimirTicket(pedido) {
                let ventana = window.open('', 'PRINT', 'height=600,width=400');
                ventana.document.write('<html><head><title>Ticket de Venta</title>');
                ventana.document.write('</head><body>');
                ventana.document.write('<div class="ticket-container">');
                ventana.document.write(`<div class="header">
                                            <p><strong>Distribuidora XYZ</strong><br>
                                            Fecha: ${pedido.fecha}<br>
                                            Cliente: ${pedido.nombre_cliente}<br>
                                            Nº de Ticket: ${pedido.pedido_id}</p>
                                        </div>`);
                pedido.productos.forEach(producto => {
                    ventana.document.write(`<div class="item">
                                                <span>${producto.producto_nombre} (${producto.precio_producto} x ${producto.cantidad})</span>
                                                <span>${(producto.precio_producto * producto.cantidad).toFixed(2)}</span>
                                            </div>`);
                });
                ventana.document.write(`<div class="total"><p>Total: ${pedido.total.toFixed(2)}</p></div>`);
                ventana.document.write('</div></body></html>');
                ventana.document.close();
                ventana.print();
            }
        });
    </script>

    </body>
</html>
