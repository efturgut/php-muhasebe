<?php include('../../main.php'); ?>
<?php
if($form = get_form(@$_GET['id'])) {

} else {
	$form = new StdClass;
}

// eger payment->type yok ise ve GET metodu ile type degeri gelmis ise
if(!isset($form->template)) {
	if(isset($_GET['template'])) {
		$form->template = $_GET['template'];
	}
}
include_content_page('detail', @$form->template, 'form', array('form'=>$form));
?>
<?php get_header(); ?>
<?php
// eger _GET ile ID degeri geliyorsa ve form bulunamiyorsa betik akisini duruduru ve hata mesajini ekrana bas
if(isset($_GET['id'])) {
	if(empty($form->id)) {
		add_page_info('title', 'Form Bulunamadı');
		echo get_alert(_b($_GET['id']).' ID numarali form bulunamadı.', 'warning', false);
		get_footer(); exit;
	}	
}


if(empty($form->id)) {
	$form->id = 0;
	$form->date = date('Y-m-d H:i:s');

	if(isset($_GET['in'])) { $form->in_out = '0'; } 
	elseif(isset($_GET['out'])) { $form->in_out = '1'; }
	if(!$form_status = get_form_status(array('val_5'=>'default', 'val_enum'=>$form->in_out))) {
		echo get_alert("<b>".get_in_out_label($form->in_out)."</b> form durumu bulunamadı. En az 1 tane form durumu olması gerekiyor. <b>".get_in_out_label($form->in_out)."</b> form durumunu <a href='".get_site_url('admin/system/form_status/form_status.php?taxonomy=extra_fs_form')."' class='strong'>buradan</a> oluşturabilirsiniz.", "warning");
		return false;
	}
} else{

	if(isset($_GET['update_status_id'])) {
		$args['where']['id'] = $_GET['status_id'];
		$args['where']['in_out'] = $form->in_out;

		if(change_form_status($form->id, $args )) { $form = get_form($form->id); }
	}
	
	// aktif form_durumunu kotnrol edelim, eger aktif form_durumu yok ise guncel "default" olan form_durumunu bu forma ekleyelim
	if(!$form_status = get_form_status($form->status_id)) { if($form_status = get_form_status(array('val_5'=>'default', 'val_enum'=>$form->in_out))) { update_form($form->id, array('status_id'=>$form_status->id)); $form = get_form($form->id); } }
	$form_status_all 	= get_form_status_all($form->in_out);
}


// in_out control
if($form->in_out != '0' and $form->in_out != '1') {
	echo get_alert('Giriş çıkış türü yanlış.', 'warning');
	exit; 
}


// page info
if($form->id) {
	add_page_info( 'title', '#'.$form->id.' - '.get_in_out_label($form->in_out).' Formu' );
} else {
	add_page_info( 'title', 'Yeni '.get_in_out_label($form->in_out).' Formu' );
}

add_page_info( 'nav', array('name'=>'Form Yönetimi', 'url'=>get_site_url('admin/form/') ) );
add_page_info( 'nav', array('name'=>'Tüm Formlar', 'url'=>get_site_url('admin/form/list.php') ) );
add_page_info( 'nav', array('name'=>extra()->page['title']) );


/* form ekleme, guncelleme ve yeni form hareketi ekleme */
if(isset($_POST['form'])) {
	if($form_id = set_form($form->id, $_POST)) {
		// eger $form degeri bos ise empty($form->id) demek ki bu form ilk defa oluşuyor,
		// set_form ile olusan "$form_id" degiskenindeki ID parametresine yonlendirelim.
		if(empty($form->id)) { header("Location: ?id=".$form_id); }
	}

	/* form hareketi ekleme */
	if($form->id) {
		$form_item['in_out'] 		= $form->in_out;
		$form_item['uniquetime']	= $_POST['item_uniquetime'];
		$form_item['form_id'] 		= $form->id;
		$form_item['account_id']	= $form->account_id;
		$form_item['item_code'] 	= $_POST['item_code'];
		$form_item['item_name']		= $_POST['item_name'];
		$form_item['quantity']	= $_POST['quantity'];
		$form_item['price']		= $_POST['price'];
		$form_item['vat']		= $_POST['vat'];

		if(!empty($_POST['item_code']) or !empty($_POST['item_name'])) {
			add_form_item($form->id, $form_item); 
		}
	} //.$form->id

} //.isset($_POST['form'])





/* form pasiflestirme veya silme */
if(isset($_GET['status'])) {
	if($_GET['status'] != $form->status) {
		if($_GET['status'] == '0' OR $_GET['status'] == '1') {
			if( update_form($form->id, array('update'=> array('status'=>$_GET['status']), 'add_alert'=>false)) ) {}	
		}
	}
} //.isset($_GET['status'])




/* form hareketi silmek */
if(isset($_GET['delete_form_item'])) {
	delete_form_item($form->id, $_GET['delete_form_item']);
}


/* form guncellemeleri */
if($form->id) {
	calc_form($form->id); // form toplamlarini hesaplayalim
	calc_account($form->account_id); // eger hesap karti var ise bakiyesini hesaplayalim

	$form = get_form($form->id); // formu tekrar cagiralim
	$form_meta = get_form_meta($form->id); // form elemanlarını cagiralim
}
?>


	

	<?php if(@$form->status == '0' AND $form->id): ?>
		<?php echo get_alert('<i class="fa fa-trash-o"></i> <b>Dikkat!</b> form pasif durumda.', 'warning', false); ?>
		<div class="h-20 visible-xs"></div>
	<?php else: ?>
		<?php create_modal(array('id'=>'status_item', 
			'title'=>'Form <u>pasifleştirme</u>', 
			'content'=>_b($form->id).' ID numarali formu pasifleştirmek istiyor musun? <br /> <small>Form veritabanından <u>silinmez</u>. Fakat arama ve listelemelerde bulunamaz.</small>', 
			'btn'=>'<a href="?id='.$form->id.'&status=0" class="btn btn-danger">Evet, onaylıyorum</a>')); ?>
	<?php endif; ?>
	

	<ul class="nav nav-tabs extra-nav-page" role="tablist"> 
		<li role="presentation" class="active"><a href="#home" id="home-tab" role="tab" data-toggle="tab" aria-controls="home" aria-expanded="true"><i class="fa fa-file-text-o"></i><span class="hidden-xs"> Form</span></a></li> 
		<?php if($form->id): ?>
			<li role="presentation" class=""><a href="#logs" role="tab" id="logs-tab" data-toggle="tab" aria-controls="logs" aria-expanded="false"><i class="fa fa-database"></i><span class="hidden-xs"> Geçmiş</span></a></li> 
			
			<li role="presentation" class="dropdown pull-right extra-menu-right"> <a href="#" class="dropdown-toggle" id="myTabDrop1" data-toggle="dropdown" aria-controls="myTabDrop1-contents" aria-expanded="false"><i class="fa fa-cogs"></i><span class="hidden-xs"> Seçenekler</span> <span class="caret"></span></a> 
				<ul class="dropdown-menu" aria-labelledby="myTabDrop1" id="myTabDrop1-contents"> 
					<!--<li><a href="<?php site_url('admin/user/task/add.php?attachment&form_id='.$form->id); ?>" target="_blank"><i class="fa fa-tasks fa-fw"></i> Görev Ekine Ekle</a></li> -->
					<!--<li><a href="<?php site_url('admin/user/message/add.php?attachment&form_id='.$form->id); ?>" target="_blank"><i class="fa fa-envelope-o fa-fw"></i> Mesaj Ekine Ekle</a></li>  -->
					<!--<li role="separator" class="divider"></li> -->
					<?php if($form->status == '1'): ?>
						<li><a href="#"  data-toggle="modal" data-target="#status_item"><i class="fa fa-trash-o fa-fw text-danger"></i> Sil</a></li>
					<?php else: ?>
						<li><a href="?id=<?php echo $form->id; ?>&status=1"><i class="fa fa-undo fa-fw text-success"></i> Aktifleştir</a></li>
					<?php endif; ?>
				</ul> 
			</li>

			<li role="presentation" class="dropdown pull-right extra-menu-right"> <a href="#" class="dropdown-toggle" id="myTabDrop1" data-toggle="dropdown" aria-controls="myTabDrop1-contents" aria-expanded="false"><i class="fa fa-print"></i><span class="hidden-xs"> Yazdır</span> <span class="caret"></span></a> 
				<ul class="dropdown-menu" aria-labelledby="myTabDrop1" id="myTabDrop1-contents"> 
					<li><a href="print.php?id=<?php echo $form->id; ?>&print" target="_blank"><i class="fa fa-fw fa-file-text-o"></i> Form Yazdır</a></li> 
					<li><a href="print.php?id=<?php echo $form->id; ?>&print&showBarcode" target="_blank"><i class="fa fa-fw fa-file-text-o"></i> Barkodlu Form Yazdır</a></li> 
					<li><a href="print-invoice.php?id=<?php echo $form->id; ?>&print" target="_blank"><i class="fa fa-fw fa-file-archive-o"></i> Fatura Yazdır</a></li> 
				</ul> 
			</li>
			
			<li role="presentation" class="dropdown pull-right extra-menu-right"> <a href="#" class="dropdown-toggle" id="myTabDrop1" data-toggle="dropdown" aria-controls="myTabDrop1-contents" aria-expanded="false"><i class="fa fa-square" style="color:<?php echo $form_status->bg_color; ?>"></i> 
				<span class="hidden-xs"><?php echo $form_status->name; ?></span> 
				<span class="visible-xs-inline hidden-xs-portrait"><?php echo $form_status->name; ?></span> 
				<span class="visible-xs-inline hidden-xs-landscape"><?php echo extra_get_abbreviation($form_status->name); ?></span> <span class="caret"></span></a> 
				<ul class="dropdown-menu" aria-labelledby="myTabDrop1" id="myTabDrop1-contents"> 
					<?php if($form_status_all): ?>
						<?php foreach($form_status_all as $status): ?>
							<li><a href="?id=<?php echo $form->id; ?>&status_id=<?php echo $status->id; ?>&update_status_id&uniquetime=<?php uniquetime(); ?>"><i class="fa fa-square" style="color:<?php echo $status->bg_color; ?>"></i> &nbsp; <?php echo $status->name; ?></a></li> 
						<?php endforeach; ?> 
					<?php endif; ?>
				</ul> 
			</li>
		<?php endif; ?>
	</ul>




	


	<div class="tab-content"> 
		<div class="tab-pane fade active in" role="tabpanel" id="home" aria-labelledby="home-tab"> 
					<?php 
						print_alert('set_form'); 
						if(isset($_GET['update_status_id'])) { print_alert('change_form_status'); }
					?>
					<form name="form_add_accout" id="form_add_account" action="<?php if($form->id): ?>?id=<?php echo $form->id; ?><?php else: ?><?php endif; ?>" method="POST" class="validate">

						<div class="row">
							<div class="col-xs-6 col-md-3">
								<div class="form-group">
									<label for="date"><i class="fa fa-calendar"></i> Tarih </label>
									<input type="text" name="date" id="date" value="<?php echo @substr($form->date,0,16); ?>" class="form-control input-sm datetime">
								</div>
							</div>
							<div class="col-xs-6 col-md-5">
								<div class="form-group">
									<label for="note"><i class="fa fa-pencil"></i> Not <sup class="text-muted hidden-xs">*bu form ile ilgili hatırlatıcı veya önemli bir not yazabilirsiniz</sup> </label>
									<input type="text" name="note" id="note" value="<?php echo @$form_meta->note; ?>" class="form-control input-sm" minlength="1" maxlength="128">
								</div>
							</div>
							<div class="col-xs-6 col-md-2">
								<div class="form-group">
									<label for="account_tax_home">Vergi Dairesi </label>
									<input type="text" name="account_tax_home" id="account_tax_home" value="<?php echo @$form->account_tax_home; ?>" class="form-control input-sm" minlength="3" maxlength="32">
								</div>
							</div>
							<div class="col-xs-6 col-md-2">
								<div class="form-group">
									<label for="account_tax_no">Vergi/T.C. No</label>
									<input type="tel" name="account_tax_no" id="account_tax_no" value="<?php echo @$form->account_tax_no; ?>" class="form-control input-sm" minlength="3" maxlength="32">
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-3">
								<div class="form-group">
									<label for="account_code"><i class="fa fa-barcode"></i> Hesap Kodu </label>
									<input type="text" name="account_code" id="account_code" value="<?php echo @$form->account_code; ?>" class="form-control input-sm" minlength="3" maxlength="32">
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label for="account_name">Hesap Adı <?php if(@$form->account_id):?><a href="../account/detail.php?id=<?php echo $form->account_id; ?>" target="_blank"><i class="fa fa-external-link"></i></a><?php endif; ?> </label>
										<label id="new_account" class="pull-right" data-toggle="tooltip" data-placement="top" title="" data-original-title="Yeni bir hesap kartı oluşturulsun mu?"><input type="checkbox" name="new_account" id="new_account" value="true" data-toggle="switch" switch-size="xs" on-text="Evet" off-text="Hayır"></label>
									<input type="text" name="account_name" id="account_name" value="<?php echo @$form->account_name; ?>" class="form-control input-sm" minlength="3" maxlength="32">
								</div>
							</div>
							<div class="col-xs-6 col-md-2">
								<div class="form-group">
									<label for="account_gsm">Cep Telefonu </label>
									<input type="tel" name="account_gsm" id="account_gsm" value="<?php echo @$form->account_gsm; ?>" class="form-control input-sm" minlength="3" maxlength="32">
								</div>
							</div>
							<div class="col-xs-6 col-md-2">
								<div class="form-group">
									<label for="account_phone">Sabit Telefonu </label>
									<input type="tel" name="account_phone" id="account_phone" value="<?php echo @$form->account_phone; ?>" class="form-control input-sm" minlength="3" maxlength="32">
								</div>
							</div>
							<div class="col-xs-12 col-md-2">
								<div class="form-group">
									<label for="account_email">E-Posta </label>
									<input type="email" name="account_email" id="account_email" value="<?php echo @$form->account_email; ?>" class="form-control input-sm email">
								</div>
							</div>

							<div class="clearfix"></div>

							<div class="col-xs-12 col-md-6">
								<div class="form-group">
									<label for="account_address">Adres </label>
									<input type="text" name="account_address" id="account_address" value="<?php echo @$form_meta->address; ?>" class="form-control input-sm">
								</div>
							</div>	
						</div>


						<div class="text-right">
							<input type="hidden" name="in_out" value="<?php echo $form->in_out; ?>">
							<input type="hidden" name="account_id" id="account_id" value="<?php echo @$form->account_id; ?>">
							<input type="hidden" name="form">
							<input type="hidden" name="uniquetime" value="<?php echo microtime(true); ?>">
						</div>

						<script>
						$(document).ready(function() {

							var json_url = '<?php site_url("admin/account/getJSON.php"); ?>';
							var item = { 
								name:'<span class="account_name">[TEXT]</span>', 
								gsm:'<span class="pull-right text-muted">[TEXT]</span>',
								"br":"<br />", 
								"":"bos deger", 
								city:'<span class="account_city pull-right">[TEXT]</span>'}

							input_getJSON_account('#account_code', 'account', item, json_url, 'code');	
							input_getJSON_account('#account_name', 'account', item, json_url, 'name');

							var item = { "name":'<span class="account_name">[TEXT]</span>', "br":"<br /> &nbsp;", "gsm":'<span class="pull-right text-muted">[TEXT]</span>'}	
							input_getJSON_account('#account_gsm', 'account', item, json_url, 'gsm');	

							var item = { "name":'<span class="account_name">[TEXT]</span>', "br":"<br /> &nbsp;", "phone":'<span class="pull-right text-muted">[TEXT]</span>'}	
							input_getJSON_account('#account_phone', 'account', item, json_url, 'phone');	

							var item = { "name":'<span class="account_name">[TEXT]</span>', "br":"<br /> &nbsp;", "email":'<span class="pull-right text-muted">[TEXT]</span>'}	
							input_getJSON_account('#account_email', 'account', item, json_url, 'email');	

							var item = { "name":'<span class="account_name">[TEXT]</span>', "br":"<br /> &nbsp;", "tax_no":'<span class="pull-right text-muted">[TEXT]</span>'}	
							input_getJSON_account('#account_tax_no', 'account', item, json_url, 'tax_no');	

							
							// eger hesap kodu degisiyor ise gizli inputta kalan account_id sıfırlayalim
							$('#account_code').change(function() {
								$('#account_id').val('');
								account_id_change($('#account_id').val());
							});

							// account_id degistiginde
							$('#account_id').change(function() {
								account_id_change($(this).val());
							});
							
							<?php if(@$form->account_id): ?>
								account_id_change('x'); // eger form kayit edilmis ve form account_id var ise yeni hesap olusturma butonuna gizle
							<?php endif; ?>

						});

						<?php if(@$form_meta->country): ?>
							$('#account_country').val("<?php echo $form_meta->country; ?>");
						<?php endif; ?>

						function account_getJSON_click(param) {
							var id 		= $(param).attr('data-id');
							var code 	= $(param).attr('data-code');
							var name 	= $(param).attr('data-name');
							var gsm 	= $(param).attr('data-gsm');
							var phone 	= $(param).attr('data-phone');
							var email 	= $(param).attr('data-email');
							var tax_home 	= $(param).attr('data-tax_home');
							var tax_no 		= $(param).attr('data-tax_no');
							var address 	= $(param).attr('data-address');
							var district 	= $(param).attr('data-district');
							var city 		= $(param).attr('data-city');
							var country 	= $(param).attr('data-country');
							
							$('#account_id').val(id);
							$('#account_code').val(code);
							$('#account_name').val(name);
							$('#account_gsm').val(gsm);
							$('#account_phone').val(phone);
							$('#account_email').val(email);
							$('#account_tax_home').val(tax_home);
							$('#account_tax_no').val(tax_no);
							$('#account_address').val(address);
							$('#account_district').val(district);
							$('#account_city').val(city);
							$('#account_gsm').val(gsm);
							$('#account_country').val(country);

							// country
							$('.select-account').val(country);
							$('.select-account').change();

							account_id_change($('#account_id').val());

							$('#item_code').focus();


						}

						function account_id_change(val) {
							if(val == '') {
								$('#new_account').show();
							} else {
								$('#new_account').hide();
							}
						}


						</script>


						<div class="clearfix"></div>
						<div class="h-20"></div>


						<div class="panel panel-default panel-table mobile-full" id="form_items">
							<div class="panel-heading">
								<div class="row">
									<div class="col-md-6">
										<h3 class="panel-title"><i class="fa fa-file-text-o"></i> Form Hareketleri</h3>
									</div>
									<div class="col-md-6">
										<div class="pull-right">

										</div>
									</div>
								</div>
							</div>

							<?php $form_items = get_form_items($form->id); ?>
							<?php if( is_alert('add_form_item') or is_alert('delete_form_item') ): ?><div class="not-found"><?php print_alert('add_form_item'); ?><?php print_alert('delete_form_item'); ?></div> <?php endif; ?>
							<style>table thead tr.border-none td { border:0px !important; }</style>
							<table class="table table-condensed table-striped table-hover table-bordered">
								<thead>
									<tr class="border-none">
										<td>
											<label>&nbsp;</label>
											<input type="hidden" name="item_id" id="item_id" value="">
											<input type="hidden" name="item_uniquetime" id="item_uniquetime" value="<?php echo microtime(true); ?>">
											<button class="btn btn-default "  id="submit_control"><i class="fa fa-plus"></i></button>
										</td>
										<td width="20%" class="hidden-xs-portrait">
											<div class="form-group">
												<label for="item_code"><i class="fa fa-barcode hidden-xs"></i><span class="visible-xs">Kodu</span> <span class="hidden-xs">Ürün/Barkod Kodu</span></label>
												<input type="text" name="item_code" required  id="item_code" class="form-control input-sm">
											</div>
										</td>
										<td width="30%">
											<div class="form-group">
												<label for="item_name"><span class="visible-xs">Adı</span><span class="hidden-xs">Ürün/Hizmet Adı</span></label>
												<input type="text" required  name="item_name" id="item_name" class="form-control input-sm">
											</div>
										</td>
										<td>
											<div class="form-group">
												<label for="quantity">Adet</label>
												<input type="tel"   name="quantity" id="quantity" class="form-control input-sm calc_item" value="1" maxlength="10">
											</div>
										</td>
										<td>
											<div class="form-group">
												<label for="price">B.Fiyatı</label>
												<input type="tel" name="price" id="price" class="form-control input-sm money calc_item" value="0" maxlength="15">
											</div>
										</td>
										<td class="hidden-xs">
											<div class="form-group">
												<label for="total">Tutar</label>
												<input type="tel" name="total" id="total" class="form-control input-sm money calc_item" maxlength="15">
											</div>
										</td>
										<td width="5%" class="hidden-xs">
											<div class="form-group">
												<label for="item_vat">KDV</label>
												<input type="tel" name="vat" id="vat" class="form-control input-sm calc_item">
											</div>
										</td>
										<td width="10%" class="hidden-xs">
											<div class="form-group">
												<label for="vat_total"><span class="hidden-xs">KDV Tutarı</span><span class="visible-xs fs-11">KDV Tutarı</span></label>
												<input type="tel" name="vat_total" id="vat_total" class="form-control input-sm money calc_item">
											</div>
										</td>
										<td>
											<div class="form-group">
												<label for="col_total"><span class="hidden-xs">Toplamı</span><span class="visible-xs">Toplam</span></label>
												<input type="tel" name="col_total" id="col_total" class="form-control input-sm money">
											</div>
										</td>
									</tr>
								</thead>
								<tbody>
									<?php if($form_items): ?>
										<?php
										$total_vat = 0;
										?>
										<?php foreach($form_items->list as $item): ?>
											<tr class="<?php echo @$_GET['flashItemID'] == $item->item_id ? 'flashItemID' : ''; ?>">
												<td class="text-center"><a href="?id=<?php echo $form->id; ?>&delete_form_item=<?php echo $item->id; ?>#form_items" title="Sil"><i class="fa fa-trash"></i></a></td>
												<td class="hidden-xs-portrait"><?php echo $item->item_code; ?></td>
												<td><?php echo $item->item_name; ?></td>
												<td class="text-center"><?php echo $item->quantity; ?></td>
												<td class="text-right"><?php echo get_set_money($item->price, true); ?></td>
												<td class="text-right hidden-xs"><?php echo get_set_money($item->total, true); ?></td>
												<td class="text-center text-muted hidden-xs"><?php echo $item->vat; ?></td>
												<td class="text-right text-muted hidden-xs"><?php echo get_set_money($item->vat_total, true); ?></td>
												<td class="text-right"><?php echo get_set_money($item->total, true); ?></td>
											</tr>
											<?php $total_vat = $total_vat + $item->vat_total; ?>
										<?php endforeach; ?>
									<?php endif; ?>
								</tbody>
								<tfoot>
									<?php if(extra_is_mobile()): ?>
										<tr class="fs-12">
											<th class="text-center text-muted"><?php echo @$form->item_count; ?></th>
											<th colspan="2"></th>
											<th class="text-center"><?php echo @$form->item_quantity; ?></th>
											<th class="text-right text-muted hidden-xs"><?php echo get_set_money(@$total_vat, true); ?></th>
											<th colspan="2" class="text-right fs-12"><?php echo get_set_money(@$form->total, true); ?></th>
										</tr>
									<?php else: ?>
										<tr>
											<th class="text-center text-muted"><?php echo @$form->item_count; ?></th>
											<th colspan="2"></th>
											<th class="text-center"><?php echo @$form->item_quantity; ?></th>
											<th colspan="3"></th>
											<th class="text-right text-muted hidden-xs"><?php echo get_set_money(@$total_vat, true); ?></th>
											<th class="text-right"><?php echo get_set_money(@$form->total, true); ?></th>
										</tr>
									<?php endif; ?>
								</tfoot>
							</table>
						</div>
							
							<style>
							.flashItemID td {
								animation: alertloginError 300ms infinite;
				             	 animation-iteration-count: 4;
							}


				            @keyframes alertloginError{
				              0%{ background-color: transparent; }
				              100%{ background-color: #ddd; }
				            }
							</style>


						<div class="text-right">
							<div class="h-20 visible-xs"></div>
							<!--<button class="btn btn-primary btn-xs-block btn-insert btn-save"><i class="fa fa-floppy-o"></i> Kaydet</button>-->
						</div>

<script>/*
$(document).ready(function () {

$('#form_add_account').validate({
    rules: {
        item_code: {
            required: true
        },
        item_name: {
            required: true
        },
        quantity: {
            required: true
        },
		price: {
            required: true
        },
		total: {
            required: true
        },
		vat: {
            required: true
        },
		vat_total: {
            required: true
        },
		col_total: {
            required: true
        }
    },
    success: function (element) {
		
		document.getElementById("submit_control").disabled = false;
    }
});
});*/
</script>

						<script>
						$(document).ready(function() {

							var json_url = '<?php site_url("admin/item/getJSON.php"); ?>';
							var item = { 
								name:'<span class="item_name">[TEXT]</span>', 
								"":"<br />", 
								quantity:'<span class="text-muted item_quantity">[TEXT]</span>', 
								p_sale:'<span class="item_price pull-right">[TEXT]</span>'
							}

							input_getJSON_account('#item_code', 'item', item, json_url, 'code');
							input_getJSON_account('#item_name', 'item', item, json_url, 'name');

							$('.calc_item').keyup(function() {
								calc_item();
							});

							// eger urun kodu degisiyor ise gizli inputta kalan item_id sıfırlayalim
							$('#item_code').change(function() {
								$('#item_id').val('');
							});

						});

						function item_getJSON_click(param) {
							var id 		= $(param).attr('data-id');
							var code 	= $(param).attr('data-code');
							var name 	= $(param).attr('data-name');
							var p_purc	= $(param).attr('data-p_purc');
							var p_sale	= $(param).attr('data-p_sale');
							var p_purch_out_vat 	= $(param).attr('data-p_purch_out_vat');
							var p_sale_out_vat 		= $(param).attr('data-p_sale_out_vat');
							var vat 		= $(param).attr('data-vat');

							
							$('#item_id').val(id);
							$('#item_code').val(code);
							$('#item_name').val(name);
							<?php if($form->in_out == 0): ?>
								$('#price').val(p_purc);
							<?php else: ?>
								$('#price').val(p_sale);
							<?php endif; ?>
							$('#vat').val(vat);

							calc_item();

							$('#price').focus();
						}

						function calc_item() {
							var quantity 	= $('#quantity').val();
							var price		= $('#price').val();
							var vat 		= math_vat_rate($('#vat').val());

							// degeler numeric mi? degil mi?
							if(!$.isNumeric(quantity))	{ quantity = 1; 	}
							if(!$.isNumeric(price))		{ price = 0.00; 	}
							if(!$.isNumeric(vat))		{ vat = 0; 	}


							var total 		= quantity * parseFloat(price);
							var vat_total 	= total - ( parseFloat(total) / vat );
							var col_total	= parseFloat(total);

							$('#total').val(total);
							$('#vat_total').val(vat_total);
							$('#col_total').val(col_total);

						}
						</script>
					</form>

		</div> 
		<div class="tab-pane fade" role="tabpanel" id="profile" aria-labelledby="profile-tab"> 
			<p>Food <a href="#" onclick="getJSON_click();">truck</a> fixie locavore, accusamus mcsweeney's marfa nulla single-origin coffee squid. Exercitation +1 labore velit, blog sartorial PBR leggings next level wes anderson artisan four loko farm-to-table craft beer twee. Qui photo booth letterpress, commodo enim craft beer mlkshk aliquip jean shorts ullamco ad vinyl cillum PBR. Homo nostrud organic, assumenda labore aesthetic magna delectus mollit. Keytar helvetica VHS salvia yr, vero magna velit sapiente labore stumptown. Vegan fanny pack odio cillum wes anderson 8-bit, sustainable jean shorts beard ut DIY ethical culpa terry richardson biodiesel. Art party scenester stumptown, tumblr butcher vero sint qui sapiente accusamus tattooed echo park.</p> 
		</div> 
		<div class="tab-pane fade" role="tabpanel" id="logs" aria-labelledby="logs-tab"> 
			<?php theme_get_logs(" table_id='forms:".$form->id."' "); ?>
		</div> 
		<div class="tab-pane fade" role="tabpanel" id="dropdown1" aria-labelledby="dropdown1-tab"> 
			<p>Etsy mixtape wayfarers, ethical wes anderson tofu before they sold out mcsweeney's organic lomo retro fanny pack lo-fi farm-to-table readymade. Messenger bag gentrify pitchfork tattooed craft beer, iphone skateboard locavore carles etsy salvia banksy hoodie helvetica. DIY synth PBR banksy irony. Leggings gentrify squid 8-bit cred pitchfork. Williamsburg banh mi whatever gluten-free, carles pitchfork biodiesel fixie etsy retro mlkshk vice blog. Scenester cred you probably haven't heard of them, vinyl craft beer blog stumptown. Pitchfork sustainable tofu synth chambray yr.</p> 
		</div> 
		<div class="tab-pane fade" role="tabpanel" id="dropdown2" aria-labelledby="dropdown2-tab"> 
			<p>Trust fund seitan letterpress, keytar raw denim keffiyeh etsy art party before they sold out master cleanse gluten-free squid scenester freegan cosby sweater. Fanny pack portland seitan DIY, art party locavore wolf cliche high life echo park Austin. Cred vinyl keffiyeh DIY salvia PBR, banh mi before they sold out farm-to-table VHS viral locavore cosby sweater. Lomo wolf viral, mustache readymade thundercats keffiyeh craft beer marfa ethical. Wolf salvia freegan, sartorial keffiyeh echo park vegan.</p> 
		</div> 
	</div>


<?php get_footer(); ?>