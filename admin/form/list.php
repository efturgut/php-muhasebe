<?php include('../../main.php'); ?>
<?php include_content_page('list', false, 'form'); ?>
<?php get_header(); ?>
<?php
add_page_info( 'title', 'Formlar' );
add_page_info( 'nav', array('name'=>'Form Yönetimi', 'url'=>get_site_url('admin/form/') ) );

if(isset($_GET['status_id'])) {
	if($form_status = get_form_status($_GET['status_id'])) {

		add_page_info( 'title', 'Formlar - '.$form_status->name );
		add_page_info( 'nav', array('name'=>'Tüm Formlar', 'url'=>get_site_url('admin/form/list.php')) );
		add_page_info( 'nav', array('name'=>$form_status->name) );
	}
} else {
	add_page_info( 'nav', array('name'=>'Tüm Formlar') );
}
?>


<?php  
// ilk liste acilisinde sıralama yapilmasi icin
if(!isset($_GET['orderby_name'])) {
	$_GET['orderby_name'] = 'id';
	$_GET['orderby_type'] = 'DESC'; 
}

$forms = get_forms(array('_GET'=>true)); ?>








<?php if(extra_is_mobile()): ?>

	<?php
		// panel arama
		$arr_s = array();
		$arr_s['s_name'] = 'forms';
		$arr_s['db-s-where'][] = array('name'=>'Hesap kartı', 'val'=>'account_name');
		$arr_s['db-s-where'][] = array('name'=>'Telefon', 'val'=>'account_gsm');
		$arr_s['db-s-where'][] = array('name'=>'Adet', 'val'=>'item_quantity');
		search_form_for_panel($arr_s); 
	?>

	<div class="panel panel-default panel-table">
		<?php if($forms): ?>
			<table class="table table-hover table-bordered table-condensed table-striped">
				<thead>
					<tr>
						<th>ID <?php echo get_table_order_by('id', 'ASC'); ?></th>
						<th class="hidden-xs-portrait" width="40"></th>
						<th>Tarih <?php echo get_table_order_by('date', 'ASC'); ?></th>
						<th>Hesap Kartı <?php echo get_table_order_by('account_name', 'ASC'); ?></th>
						<th class="hidden-xs-portrait">Ürünler <?php echo get_table_order_by('item_quantity', 'ASC'); ?></th>
						<th width="80">Toplam <?php echo get_table_order_by('total', 'ASC'); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($forms->list as $form): ?>
					<?php $form_status = get_form_status($form->status_id); ?>
					<tr>
						<td><a href="detail.php?id=<?php echo $form->id; ?>">#<?php echo $form->id; ?></a></td>
						<td class="hidden-xs-portrait">
							<span class="label label-primary" data-toggle="tooltip" title="<?php echo $form_status->name; ?>" style="background-color:<?php echo $form_status->bg_color; ?>; color:<?php echo $form_status->color; ?>;"><?php echo extra_get_abbreviation($form_status->name); ?></span>
						</td>
						<td class="fs-11 text-muted"><?php echo extra_get_date($form->date, 'd M H:i'); ?></td>
						<td class="fs-10"><?php if($form->account_id): ?><a href="../account/detail.php?id=<?php echo $form->account_id; ?>" target="_blank"><i class="fa fa-external-link" aria-hidden="true"></i> <?php echo $form->account_name; ?></a> <?php else: ?><?php echo $form->account_name; ?><?php endif; ?></td>
						<td class="text-center hidden-xs-portrait"><span class="text-muted"  data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php echo $form->item_count; ?> farklı ürün"><?php echo $form->item_quantity; ?> adet</span></td>
						<td class="text-right"><?php echo get_set_money($form->total, true); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			
			<?php pagination($forms->num_rows); ?>

		<?php else: ?>
			<div class="not-found">
				<?php print_alert(); ?>
			</div>
		<?php endif; ?>

	</div>
<?php else: ?>
	<div class="panel panel-default panel-table">
		<div class="panel-heading">
			<div class="row">
				<div class="col-md-6">
					<h3 class="panel-title">Formlar</h3>
				</div>
				<div class="col-md-6">
					<div class="pull-right">
						
						<?php
							// panel arama
							$arr_s = array();
							$arr_s['s_name'] = 'forms';
							$arr_s['db-s-where'][] = array('name'=>'Hesap kartı', 'val'=>'account_name');
							$arr_s['db-s-where'][] = array('name'=>'Cep Telefonu', 'val'=>'account_gsm');
							$arr_s['db-s-where'][] = array('name'=>'Adet', 'val'=>'item_quantity');
							search_form_for_panel($arr_s); 
						?>
					

						<!-- Single button -->
						<div class="btn-group btn-icon" data-toggle="tooltip" data-placement="top" title="Pdf">
						  <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						    <i class="fa fa-file-pdf-o"></i>
						  </button>
						  <ul class="dropdown-menu dropdown-menu-right">
						  	<li class="dropdown-header"><i class="fa fa-download"></i> PDF AKTAR</li>
						    <li><a href="<?php echo str_replace('list.php', 'export.php', get_set_url_parameters(array('add'=> array('export'=>'pdf')))); ?>">Aktif Listeyi Aktar</a></li>
						    <li><a href="<?php echo str_replace('list.php', 'export.php', get_set_url_parameters(array('add'=> array('export'=>'pdf', 'limit'=>'false')))); ?>">Hepsini Aktar <sup class="text-muted">(<?php echo $forms->num_rows; ?>)</sup></a></li>
						  </ul>
						</div>


						<!-- Single button -->
						<div class="btn-group btn-icon" data-toggle="tooltip" data-placement="top" title="Excel">
						  <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						    <i class="fa fa-file-excel-o"></i>
						  </button>
						  <ul class="dropdown-menu dropdown-menu-right">
						  	<li class="dropdown-header"><i class="fa fa-download"></i> EXCEL AKTAR</li>
						    <li><a href="<?php echo str_replace('list.php', 'export.php', get_set_url_parameters(array('add'=> array('export'=>'excel')))); ?>">Aktif Listeyi Aktar</a></li>
						    <li><a href="<?php echo str_replace('list.php', 'export.php', get_set_url_parameters(array('add'=> array('export'=>'excel', 'limit'=>'false')))); ?>">Hepsini Aktar <sup class="text-muted">(<?php echo $forms->num_rows; ?>)</sup></a></li>
						  </ul>
						</div>


						<!-- Single button -->
						<div class="btn-group btn-icon" data-toggle="tooltip" data-placement="top" title="Yazdır">
						  <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						    <i class="fa fa-print"></i>
						  </button>
						  <ul class="dropdown-menu dropdown-menu-right">
						  	<li class="dropdown-header"><i class="fa fa-file-o"></i> YAZDIR</li>
						    <li><a href="<?php echo str_replace('list.php', 'export.php', get_set_url_parameters(array('add'=> array('export'=>'print')))); ?>" target="_blank">Aktif Listeyi Yazdır</a></li>
						    <li><a href="<?php echo str_replace('list.php', 'export.php', get_set_url_parameters(array('add'=> array('export'=>'print', 'limit'=>'false')))); ?>" target="_blank">Hepsini Yazdır <sup class="text-muted">(<?php echo $forms->num_rows; ?>)</sup></a></li>
						    <li class="divider"></li>
						    <li><a href="<?php echo str_replace('list.php', 'export.php', get_set_url_parameters(array('add'=> array('export'=>'print', 'addBarcode'=>true)))); ?>" target="_blank">Barkodlu Aktif Listeyi Yazır</a></li>
						    <li><a href="<?php echo str_replace('list.php', 'export.php', get_set_url_parameters(array('add'=> array('export'=>'print', 'limit'=>'false', 'addBarcode'=>true)))); ?>" target="_blank">Barkodlu Hepsini Yazdır <sup class="text-muted">(<?php echo $forms->num_rows; ?>)</sup></a></li>
						  </ul>
						</div>

					</div>
				</div> 
			</div>
		</div>

		<?php if($forms): ?>
			<table class="table table-hover table-bordered table-condensed table-striped">
				<thead>
					<tr>
						<th width="100">Form ID <?php echo get_table_order_by('id', 'ASC'); ?></th>
						<th width="200">Form Durumu</th>
						<th width="120">Tarih <?php echo get_table_order_by('date', 'ASC'); ?></th>
						<th>Hesap Kartı <?php echo get_table_order_by('account_name', 'ASC'); ?></th>
						<th width="100">Telefon <?php echo get_table_order_by('account_gsm', 'ASC'); ?></th>
						<th width="100">Şehir  <?php echo get_table_order_by('account_city', 'ASC'); ?></th>
						<th width="80">Ürünler <?php echo get_table_order_by('item_quantity', 'ASC'); ?></th>
						<th width="100">Toplam <?php echo get_table_order_by('total', 'ASC'); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($forms->list as $form): ?>
					<?php $form_status = get_form_status($form->status_id); ?>
					<tr>
						<td><a href="detail.php?id=<?php echo $form->id; ?>" style="text-decoration:none;">#<?php echo $form->id; ?></a></td>
						<td><a href="detail.php?id=<?php echo $form->id; ?>" style="text-decoration:none;"><span class="label label-primary" style="background-color:<?php echo $form_status->bg_color; ?>; color:<?php echo $form_status->color; ?>;"><?php echo $form_status->name; ?></span></a></td>
						<td><a href="detail.php?id=<?php echo $form->id; ?>" style="text-decoration:none;"><?php echo substr($form->date,0,16); ?></td>
						<td><?php if($form->account_id): ?><a href="../account/detail.php?id=<?php echo $form->account_id; ?>" target="_blank" style="text-decoration:none;"><i class="fa fa-external-link" aria-hidden="true"></i> <?php echo $form->account_name; ?></a> <?php else: ?><?php echo $form->account_name; ?><?php endif; ?></td>
						<td><a href="detail.php?id=<?php echo $form->id; ?>" style="text-decoration:none;"><?php echo $form->account_gsm; ?></a></td>
						<td><a href="detail.php?id=<?php echo $form->id; ?>" style="text-decoration:none;"><?php echo $form->account_city; ?></a></td>
						<td class="text-center"><a href="detail.php?id=<?php echo $form->id; ?>" style="text-decoration:none;"><span class="text-muted"  data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php echo $form->item_count; ?> farklı ürün"><?php echo $form->item_quantity; ?> adet</span></a></td>
						<td class="text-right"><a href="detail.php?id=<?php echo $form->id; ?>" style="text-decoration:none;"><?php echo get_set_money($form->total, true); ?></a></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			
			<?php pagination($forms->num_rows); ?>

		<?php else: ?>
			<div class="not-found">
				<?php print_alert(); ?>
			</div>
		<?php endif; ?>

	</div>

<?php endif; ?>

<?php get_footer(); ?>