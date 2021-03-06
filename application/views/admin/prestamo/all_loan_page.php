<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title">Herramientas</h3> <hr/>
    	<a href="<?php echo base_url('admin/prestamo/nuevo'); ?>" class="btn btn-success" data-toggle="tooltip" data-original-title="Nuevo prestamo"><i class="fa fa-plus-circle"></i> Nuevo</a>
  </div>
<!-- /.box-footer-->
</div>
<div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Todos los prestamos registrados</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <?php if ($prestamos): ?>
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>Prestamista</th>
                  <th>Cliente</th>
                  <th >Monto</th>
                  <th >Cuotas</th>
                  <th>Total</th>
                  <th>Fecha</th>
                </tr>
                </thead>
                <tbody>
                 <?php foreach ($prestamos as $key => $prestamo): ?>
                   <tr>
                     <td><a href="<?php echo base_url('admin/user/view/'.$prestamo['id_prestamista']) ?>"><?php echo ucwords($prestamo['username']) ?></a></td>
                     <td><a href="<?php echo base_url('admin/prestamo/cliente/'.$prestamo['id_cliente']) ?>"><?php echo ucwords($prestamo['nombre'].' '.$prestamo['apellido']) ?></a></td>
                     <td><?php echo number_format ($prestamo['monto'], 2, ',', '.') ?> $</td>
                     <td ><?php echo $prestamo['cant_cuotas'] ?>&nbsp;<a title='Ver Cuotas' href="<?php echo base_url('admin/prestamo/cuotas/'.$prestamo['id']); ?>"><i class="fa fa-fw fa-search-plus"></i></a></td>
                     <td ><?php echo number_format ($prestamo['monto_total'], 2, ',', '.') ?> $</td>
                     <td ><?php echo $prestamo['registerdate'] ?> $</td>
                   </tr>
                 <?php endforeach ?>
                </tbody>
                <tfoot>
                <tr>
                  <th>Prestamista</th>
                  <th>Cliente</th>
                  <th>Prestamo</th>
                  <th>Cuotas</th>
                  <th>Total</th>
                  <th>Fecha</th>
                </tr>
                </tfoot>
              </table>
            <?php else: ?>
            No hay prestamos registrados
               <?php endif ?>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>