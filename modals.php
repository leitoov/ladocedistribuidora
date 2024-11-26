<!-- Modal Buscar Producto -->
<div class="modal fade" id="modalBuscarProducto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Buscar Producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="text" class="form-control mb-3" id="buscarProductoInput" placeholder="Ingrese nombre o código del producto">
        <div id="resultadoBusqueda" class="mt-3"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="buscarProductoBtn">Buscar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Añadir Producto -->
<div class="modal fade" id="modalAñadirProducto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Añadir Producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="text" class="form-control mb-3" placeholder="Nombre del producto" id="nuevoProductoNombre">
        <input type="number" class="form-control mb-3" placeholder="Cantidad" id="nuevoProductoCantidad">
        <input type="number" class="form-control mb-3" placeholder="Precio" id="nuevoProductoPrecio">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="añadirProductoBtn">Añadir</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Modificar Producto -->
<div class="modal fade" id="modalModificarProducto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modificar Producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="text" class="form-control mb-3" id="modificarProductoCodigo" placeholder="Código del producto">
        <input type="text" class="form-control mb-3" id="modificarProductoNombre" placeholder="Nuevo nombre">
        <input type="number" class="form-control mb-3" id="modificarProductoCantidad" placeholder="Nueva cantidad">
        <input type="number" class="form-control mb-3" id="modificarProductoPrecio" placeholder="Nuevo precio">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="modificarProductoBtn">Modificar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Armar Pedido -->
<div class="modal fade" id="modalArmarPedido" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Armar Pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="text" class="form-control mb-3" id="clientePedido" placeholder="Nombre del cliente">
        <div id="productosPedido" class="mt-3">
          <!-- Lista dinámica de productos -->
        </div>
        <button class="btn btn-success w-100" id="añadirProductoPedidoBtn">Añadir Producto</button>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="finalizarPedidoBtn">Finalizar Pedido</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Historial de Pedidos -->
<div class="modal fade" id="modalHistorialPedidos" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Historial de Pedidos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>ID</th>
              <th>Cliente</th>
              <th>Fecha</th>
              <th>Total</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody id="tablaHistorialPedidos">
            <!-- Rellenar dinámicamente con datos -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Devoluciones / Cancelaciones -->
<div class="modal fade" id="modalDevoluciones" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Devoluciones / Cancelaciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>ID Pedido</th>
              <th>Cliente</th>
              <th>Razón</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="tablaDevoluciones">
            <!-- Rellenar dinámicamente con datos -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Consultar Inventario -->
<div class="modal fade" id="modalConsultarInventario" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Consultar Inventario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Código</th>
              <th>Producto</th>
              <th>Cantidad</th>
              <th>Precio</th>
            </tr>
          </thead>
          <tbody id="tablaInventario">
            <!-- Rellenar dinámicamente con datos -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
