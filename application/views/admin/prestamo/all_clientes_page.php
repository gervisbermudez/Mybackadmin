<div class="row">
	<div class="col-xs-12">
		<div class="box">
				<div class="box-header with-border">
					<h3 class="box-title">Herramientas</h3>
					<hr />
					<a href="<?php echo base_url('admin/prestamo/clientes/nuevo'); ?>" class="btn btn-success" data-toggle="tooltip"
					 data-original-title="Nuevo prestamo"><i class="fa fa-plus-circle"></i> Nuevo</a>
				</div>
				<!-- /.box-footer-->
			</div>
		<div class="box">
			<div class="box-header">
				<h3 class="box-title">Todos los clientes registrados</h3>
			</div>
			
			<div class="box-body">
				<?php if ($clientes): ?>
				<table id="example1" class="table table-bordered table-striped">
					<thead>
						<tr>
							<?php if ($this->session->userdata('user')['level'] < 2): ?>
							<th class="hidden-xs">Registrado por</th>
							<?php endif ?>
							<th>Nombre</th>
							<th>Dirección</th>
							<th>Telefono</th>
							<th class="hidden-xs">Identificación</th>
							<th class="hidden-xs">Registro</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($clientes as $key => $cliente): ?>
						<tr>
							<?php if ($this->session->userdata('user')['level'] < 2): ?>
							<td class="hidden-xs"><a href="<?php echo base_url('admin/user/view/'.$cliente['id_user_register']); ?>">
									<?php echo ucwords($cliente['username']) ?></a></td>
							<?php endif ?>
							<td><a href="<?php echo base_url('admin/prestamo/cliente/'.$cliente['id']); ?>">
									<?php echo ucwords($cliente['nombre'].' '.$cliente['apellido']) ?></a></td>
							<td>
							<a href="https://www.google.com.ar/maps/search/<?php echo ucwords($cliente['direccion']) ?>" target="_blank"><?php echo ucwords($cliente['direccion']) ?></a>
							</td>
							<td>
								<?php echo $cliente['telefono'] ?>
							</td>
							<td class="hidden-xs">
								<?php echo $cliente['identificacion'] ?>
							</td>
							<td class="hidden-xs">
								<?php echo $cliente['registerdate'] ?>
							</td>
						</tr>
						<?php endforeach ?>
					</tbody>
				</table>
				<?php else: ?>
				No tienes clientes registrados, <a href="<?php echo base_url('admin/prestamo/clientes/nuevo') ?>">Registrar nuevo</a>
				<?php endif ?>
			</div>
		</div>
	</div>
</div>
