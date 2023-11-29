<?php
GetMyConnection();
$txtid = "";
$txtmastermodule = "";
$txtmasterdescription = "";
$txtmasterkjlmodelid = "";
$txtmasterprice = "";
$txtmasteritemcode = "";
$txtmasteractive = "";
$txtbranch = "";
$txtmasterendpanel = "";
$txtmasterinfill = "";
$txtmasterfop = "";
$txtmasterplinth = "";
$txtmasterinstallation = "";
$txtmasterep = "";
$txtmastertype = "";
$txtmastermaterial = "";
$txtmasteruom = "";
$txtmasterspec = "";
$txtmasterplinth_selection = "";

if (isset($_GET['txtid'])) {
	// edit per tab query diff table
	$view_id = htmlentities($_GET['txtid']);
	$table = $_GET['table'] ?: trim($_GET['table']);

	// get employee name
	$nquery = mysql_query('SELECT * FROM ' . $table . ' WHERE id = "' . $view_id . '"');
	$nnum_rows = mysql_num_rows($nquery); // Get the number of rows
	if ($nnum_rows = 1) { // If record found.
		$nfetch = mysql_fetch_array($nquery);
		$txtid = $nfetch['id'];
		$txtmastermodule = isset($nfetch['master_module']) ? $nfetch['master_module'] : (isset($nfetch['name']) ? $nfetch['name'] : '');
		$txtmasterdescription = isset($nfetch['master_description']) ? $nfetch['master_description'] : (isset($nfetch['description']) ? $nfetch['description'] : '');
		$txtmasterkjlmodelid = isset($nfetch['master_kjl_model_id']) ? $nfetch['master_kjl_model_id'] : '';
		$txtmasterwidth = isset($nfetch['master_width']) ? $nfetch['master_width'] : (isset($nfetch['width']) ? $nfetch['width'] : '');
		$txtmasterheight = isset($nfetch['master_height']) ? $nfetch['master_height'] : (isset($nfetch['length']) ? $nfetch['length'] : '');
		$txtmasterdepth = isset($nfetch['master_depth']) ? $nfetch['master_depth'] : (isset($nfetch['depth']) ? $nfetch['depth'] : '');
		$txtmasterinstallation = isset($nfetch['master_installation']) ? $nfetch['master_installation'] : '';
		$txtmasterprice = isset($nfetch['master_price']) ? $nfetch['master_price'] : (isset($nfetch['price']) ? $nfetch['price'] : '');
		$txtmasteritemcode = isset($nfetch['master_item_code']) ? $nfetch['master_item_code'] : (isset($nfetch['item_code']) ? $nfetch['item_code'] : '');
		$txtmasteractive = isset($nfetch['master_active']) ? $nfetch['master_active'] : '';
		$txtmasterep = isset($nfetch['master_ep']) ? $nfetch['master_ep'] : '';
		$txtmastertype = isset($nfetch['master_type']) ? $nfetch['master_type'] : '';
		$txtmasterspec = isset($nfetch['spec']) ? $nfetch['spec'] : '';
		$txtmastermaterial = isset($nfetch['material']) ? $nfetch['material'] : '';
		$txtmasteruom = isset($nfetch['uom']) ? $nfetch['uom'] : '';
		$txtmasterplinth_selection = isset($nfetch['kitchen_wardrobe']) ? $nfetch['kitchen_wardrobe'] : '';
	}
	$tab = table_map_tab($table);
}
function table_map_tab($table){
	switch($table){
		case 'tblitem_master_ezkit':
			$tab = 'load_ezkit_module';
			break;
		case 'tblitem_master_ezkit_worktop':
			$tab = 'load_ezkit_worktop';
			break;
		case 'tblitem_master_ezkit_infill':
			$tab = 'load_ezkit_infill';
			break;
		case 'tblitem_master_ezkit_plinth':
			$tab = 'load_ezkit_plinth';
			break;
		case 'tblitem_master_ezkit_handle':
			$tab = 'load_ezkit_handle';
			break;
		case 'tblitem_master_ezkit_door_color':
			$tab = 'load_ezkit_doorcolor';
			break;
		default:
			$tab = '';
			break;
	}
	return $tab;
}

$type_query = mysql_query('SELECT * FROM tblitem_master_ezkit_module_type;');
$type_rows = mysql_num_rows($type_query); // Get the number of rows
if ($type_rows > 0) { // If record found.
	while ($row = mysql_fetch_array($type_query)) {
		$type_arr[$row['id']] = $row['type'];
	}
}
CleanUpDB();
?>
<!-- content -->
<div id="content" class="app-content" role="main">
	<div class="box">
		<!-- Content Navbar -->
		<div class="navbar md-whiteframe-z1 no-radius blue">
			<!-- Open side - Naviation on mobile -->
			<a md-ink-ripple data-toggle="modal" data-target="#aside"
				class="navbar-item pull-left visible-xs visible-sm"><i class="mdi-navigation-menu i-24"></i></a>
			<!-- / -->
			<!-- Page title - Bind to $state's title -->
			<div class="navbar-item pull-left h4">Admin</div>
			<!-- / -->
		</div>
		<!-- Content -->
		<div class="box-row">
			<div class="box-cell">
				<div class="box-inner padding">
					<div class="row">
						<div class="col-md-12">
							<div class="panel-heading bg-black">
								<div class="row row-sm">
									<div class="col-xs-12 font-bold header">
										<div class="navbar-brand">
											<!-- <img src="digital_ezykit/images/kubiq_logo.png" alt="Kubiq Logo" height="50"> -->
										</div>
										<ul class="nav nav-tabs" role="tablist">
											<?php
											if (isset($type_arr) && (!isset($_GET['action']) || $_GET['action'] != 'edit')) {
												$count = 0;
												foreach ($type_arr as $key => $value) {
													echo '<li class="nav-item" role="presentation">
																	<a class="nav-link ' . (($count == 0) ? 'active' : '') . '" data-target="#test_' . $count . '" id="load_ezkit_' . str_replace(' ', '', strtolower($value)) . '"
																		aria-controls="test_' . $count . '" role="tab" data-toggle="tab"
																		data-src="">' . $value . '</a>
																</li>';
													$count++;
												}
											}
											?>
										</ul>
									</div>
								</div>
							</div>
						</div>
						<form action="?module=ezkit_create" method="post" id="frmuser" name="frmuser">
							<input type="hidden" id="txtid" name="txtid" value="<?php echo $txtid ?>">

							<div class="panel panel-default">
								<div class="panel-heading bg-white">
									<div class="block font-bold">Ezikit Details</div>
								</div>
								<div class="panel-body">
									<div class="row row-sm">
										<div class="col-xs-3">
											<div id="name">Module *</div>
											<input type="text" name="txtmastermodule" id="txtmastermodule"
												value="<?php echo $txtmastermodule; ?>" placeholder="QV1234"
												class="form-control" required>
										</div>
										<div class="col-xs-3" id="description">Description *
											<input type="text" name="txtmasterdescription" id="txtmasterdescription"
												value="<?php echo $txtmasterdescription; ?>"
												placeholder="100 x 100 x 100mm" class="form-control">
										</div>
										<div class="col-xs-2" id="kjl_model">KuJiaLe Model *
											<input type="text" name="txtmasterkjlmodelid" id="txtmasterkjlmodelid"
												value="<?php echo $txtmasterkjlmodelid; ?>" placeholder="3QOWQER"
												class="form-control">
										</div>
										<div class="col-xs-2" id="price">
											<div id="price_display">Module Price *</div>
											<input type="text" name="txtmasterprice" id="txtmasterprice"
												value="<?php echo $txtmasterprice; ?>" placeholder="100.00"
												class="form-control">
										</div>
										<div class="col-xs-2" id="status">Status
											<select id="txtmasteractive" name="txtmasteractive" class="form-control">
												<option value="Y" <?php if ($txtmasteractive == "Y")
													echo " selected"; ?>>
													Active</option>
												<option value="N" <?php if ($txtmasteractive == "N")
													echo " selected"; ?>>
													Inactive</option>
											</select>
										</div>
									</div>
									<div class="row row-sm" id="measurement">
										<br>
										<div class="col-xs-3" id="width">Width *
											<input type="text" name="txtmasterwidth" id="txtmasterwidth"
												value="<?php echo !empty($txtmasterwidth) ? $txtmasterwidth : ''; ?>"
												placeholder="100.00" class="form-control">
										</div>
										<div class="col-xs-3" id="height">
											<div id="height_display">Height *</div>
											<input type="text" name="txtmasterheight" id="txtmasterheight"
												value="<?php echo !empty($txtmasterheight) ? $txtmasterheight : ''; ?>"
												placeholder="100.00" class="form-control">
										</div>
										<div class="col-xs-3" id="depth">Depth *
											<input type="text" name="txtmasterdepth" id="txtmasterdepth"
												value="<?php echo !empty($txtmasterdepth) ? $txtmasterdepth : ''; ?>"
												placeholder="100.00" class="form-control">
										</div>

										<div class="col-xs-3" id="spec">Spec *
											<input type="text" name="txtmasterspec" id="txtmasterspec"
												value="<?php echo !empty($txtmasterspec) ? $txtmasterspec : ''; ?>"
												placeholder="Spec placeholder" class="form-control">
										</div>
										<div class="col-xs-3" id="item_code">Item Code *
											<input type="text" name="txtmasteritemcode" id="txtmasteritemcode"
												value="<?php echo !empty($txtmasteritemcode) ? $txtmasteritemcode : ''; ?>"
												placeholder="Item code..." class="form-control">
										</div>

										<div class="col-xs-3" id="type">Type
											<select id="txtmastertype" name="txtmastertype" class="form-control"
												onblur="getInstall()">
												<!-- getInstall() not used -->
												<option value="">-Please Select-</option>
												<option value="Wall" <?php if ($txtmastertype == "Wall")
													echo " selected"; ?>>Wall</option>
												<option value="Base" <?php if ($txtmastertype == "Base")
													echo " selected"; ?>>Base</option>
												<option value="Tall" <?php if ($txtmastertype == "Tall")
													echo " selected"; ?>>Tall</option>
											</select>
										</div>

										<div class="col-xs-3" id="material">Material
											<select id="txtmastermaterial" name="txtmastermaterial"
												class="form-control">
												<option value="">-Please Select-</option>
												<option value="Quartz" <?php if ($txtmastermaterial == "Quartz")
													echo " selected"; ?>>Quartz</option>
												<option value="Compact" <?php if ($txtmastermaterial == "Compact")
													echo " selected"; ?>>Compact</option>
											</select>
										</div>

										<div class="col-xs-3" id="uom">UOM
											<select id="txtmasteruom" name="txtmasteruom" class="form-control">
												<option value="">-Please Select-</option>
												<option value="By Piece" <?php if ($txtmasteruom == "By Piece")
													echo " selected"; ?>>By Piece</option>
												<option value="Meter Run" <?php if ($txtmasteruom == "Meter Run")
													echo " selected"; ?>>Meter Run</option>
											</select>
										</div>

										<div class="col-xs-3" id="masterplinth_selection">Kitchen / Wardrobe
											<select id="txtmasterplinth_selection" name="txtmasterplinth_selection"
												class="form-control">
												<option value="">-Please Select-</option>
												<option value="Kitchen" <?php if ($txtmasterplinth_selection == "Kitchen")
													echo " selected"; ?>>Kitchen</option>
												<option value="Wardrobe" <?php if ($txtmasterplinth_selection == "Wardrobe")
													echo " selected"; ?>>
													Wardrobe</option>
											</select>
										</div>

									</div>
									<div class="row row-sm" id="pricing">
										<br>
										<div class="col-xs-3" id="installation">Installation *
											<input type="text" name="txtmasterinstallation" id="txtmasterinstallation"
												value="<?php echo $txtmasterinstallation; ?>" placeholder="100.00"
												class="form-control">
										</div>

										<div class="col-xs-3" id="average_ep">Average EP *
											<input type="text" name="txtmasterep" id="txtmasterep"
												value="<?php echo $txtmasterep; ?>" placeholder="100.00"
												class="form-control">
										</div>

									</div>
									<br>
									<button type="submit" class="btn btn-primary m-b" id="btnAction1" name="btnAction1"
										value="Save">Save</button>
									<a href="?module=digital_ezykit/ezkit_list" class="btn btn-default m-b">Cancel</a>
								</div>
							</div>
						</form>
					</div>

				</div>
			</div>

		</div>
	</div>

	<!-- / -->
</div>

</div>
<!-- / content -->

<?php 
include('footer.php');
if (isset($_GET['action']) && $_GET['action'] == 'edit') {
	echo "<script>
	var tab_id = '".$tab."';
	$(document).ready(function () {
		tab_switch(tab_id);
	});
	</script>";
}
?>

<script>
	$('#txtjoindate1').datepicker({
		daysOfWeekDisabled: "0",
		//startDate: "<?php echo date("d-m-Y"); ?>"
	});
	$('#txtjoindate').datepicker({
		daysOfWeekDisabled: "0",
		//startDate: "<?php echo date("d-m-Y"); ?>"
	});

</script>

<script>
	$('#txtmastertype').on('change', function () {
		var width = $('#txtmasterwidth').val();
		var type = $('#txtmastertype').val();

		if (this.value == 'Wall' || this.value == 'Base') {
			$('#txtmasterinstallation').val(width / 1000 * 102.5);
		}
		else {
			$('#txtmasterinstallation').val(width / 1000 * 164);
		}


	});
</script>

<script type="text/javascript">
	// tab switch
	$('a[data-toggle="tab"]').on('show.bs.tab', function (e) {

		tab_id = $(this).attr("id"); // the remote url for content
		var target = $(this).data("target"); // the target pane
		var data_src = $(this).data("src");
		var tab = $(this); // this tab

		tab_switch(tab_id);
	});

	function tab_switch(tab_id){
		if (tab_id == 'load_ezkit_module') {
			var show_arr = {
				'show': ['description', 'kjl_model', 'price', 'status', 'width', 'height', 'depth', 'type', 'installation', 'average_ep'],
				'hide': ['material', 'uom', 'spec', 'masterplinth_selection', 'item_code']
			};
			show_item(show_arr);
			var required_arr = {
				'true': ['txtmasteractive', 'txtmastertype', 'txtmasterdescription', 'txtmasterkjlmodelid', 'txtmasterprice', 'txtmasterwidth', 'txtmasterheight', 'txtmasterdepth', 'txtmasterinstallation', 'txtmasterep'],
				'false': ['txtmasterspec', 'txtmastermaterial', 'txtmasteruom', 'txtmasterplinth_selection', 'txtmasteritemcode']
			};
			required_item(required_arr);

			$('#txtmasterdescription').attr("placeholder", "100 x 100 x 100mm");
		} else if (tab_id == 'load_ezkit_worktop') {
			var show_arr = {
				'show': ['description', 'price', 'width', 'height', 'depth', 'material', 'spec', 'item_code'],
				'hide': ['uom', 'status', 'masterplinth_selection', 'kjl_model', 'type', 'installation', 'average_ep']
			};
			show_item(show_arr);
			var required_arr = {
				'true': ['txtmasterdescription', 'txtmasterprice', 'txtmasterwidth', 'txtmasterheight', 'txtmasterdepth', 'txtmasterspec', 'txtmastermaterial', 'txtmasteritemcode'],
				'false': ['txtmasteractive', 'txtmastertype', 'txtmasteruom', 'txtmasterinstallation', 'txtmasterep', 'txtmasterplinth_selection', 'txtmasterkjlmodelid']
			};

			required_item(required_arr);

			$('#txtmasterdescription').attr("placeholder", "Worktop Description");
		} else if (tab_id == 'load_ezkit_doorcolor') {
			var show_arr = {
				'hide': ['uom', 'status', 'masterplinth_selection', 'kjl_model', 'type', 'installation', 'average_ep', 'description', 'price', 'width', 'height', 'depth', 'material', 'spec', 'item_code']
			};
			show_item(show_arr);
		} else if (tab_id == 'load_ezkit_infill') {
			$('#txtmasterdescription').attr("placeholder", "Infill Description");
			var show_arr = {
				'show': ['description', 'price', 'width', 'height', 'depth'],
				'hide': ['uom', 'status', 'masterplinth_selection', 'kjl_model', 'type', 'installation', 'average_ep', 'material', 'spec', 'item_code']
			};
			show_item(show_arr);
			var required_arr = {
				'true': ['txtmasterdescription', 'txtmasterprice', 'txtmasterwidth', 'txtmasterheight', 'txtmasterdepth'],
				'false': ['txtmasteractive', 'txtmastertype', 'txtmasteruom', 'txtmasterinstallation', 'txtmasterep', 'txtmasterplinth_selection', 'txtmasterkjlmodelid', 'txtmasterspec', 'txtmastermaterial', 'txtmasteritemcode']
			};

			required_item(required_arr);
		} else if (tab_id == 'load_ezkit_plinth') {
			$('#txtmasterdescription').attr("placeholder", "Plinth Description");
			var show_arr = {
				'show': ['description', 'price', 'width', 'height', 'depth', 'uom', 'masterplinth_selection'],
				'hide': ['status', 'kjl_model', 'type', 'installation', 'average_ep', 'material', 'spec', 'item_code']
			};
			show_item(show_arr);
			var required_arr = {
				'true': ['txtmasterdescription', 'txtmasterprice', 'txtmasterwidth', 'txtmasterheight', 'txtmasterdepth', 'txtmasteruom', 'txtmasterplinth_selection'],
				'false': ['txtmasteractive', 'txtmastertype', 'txtmasterinstallation', 'txtmasterep', 'txtmasterkjlmodelid', 'txtmasterspec', 'txtmastermaterial', 'txtmasteritemcode']
			};

			required_item(required_arr);

		} else if (tab_id == 'load_ezkit_handle') {
			$('#txtmasterdescription').attr("placeholder", "Handle Description");
			var show_arr = {
				'show': ['description', 'price'],
				'hide': ['status', 'kjl_model', 'type', 'installation', 'average_ep', 'material', 'spec', 'width', 'height', 'depth', 'uom', 'masterplinth_selection', 'item_code']
			};

			show_item(show_arr);
			var required_arr = {
				'true': ['txtmasterdescription', 'txtmasterprice'],
				'false': ['txtmasteractive', 'txtmastertype', 'txtmasterinstallation', 'txtmasterep', 'txtmasterkjlmodelid', 'txtmasterspec', 'txtmastermaterial', 'txtmasterwidth', 'txtmasterheight', 'txtmasterdepth', 'txtmasteruom', 'txtmasterplinth_selection', 'txtmasteritemcode']
			};
			required_item(required_arr);
		}

		if (tab_id != 'load_ezkit_module') {
			$('#name').html('Name *');
			$('#height_display').html('Length *');
			$('#price_display').html('Price *');
			$('#txtmastermodule').attr("placeholder", "Name of Item");
		} else {
			$('#name').html('Module *');
			$('#height_display').html('Height *');
			$('#txtmastermodule').attr("placeholder", "QV1234");
		}
	}

	function show_item(show_arr) {
		// Loop through the keys in show_arr (assuming 'show' and 'hide' are the only keys)
		Object.keys(show_arr).forEach(function (key) {
			// Loop through the array for each key
			show_arr[key].forEach(function (id) {
				// Perform actions based on the key ('show' or 'hide')
				if (key === 'show') {
					$('#' + id).show();
				} else if (key === 'hide') {
					$('#' + id).hide();
				}
			});
		});
	}

	function required_item(required_arr) {
		// Loop through the keys in required_arr (assuming 'true' and 'false' are the only keys)
		Object.keys(required_arr).forEach(function (key) {
			// Loop through the array for each key
			required_arr[key].forEach(function (id) {
				// Perform actions based on the key ('required is true' or 'required is false')
				if (key === 'true') {
					$('#' + id).prop("required", true);
				} else if (key === 'false') {
					$('#' + id).prop("required", false);
				}
			});
		});
	}
	$(document).ready(function () {
		// hide unuse input for module
		$("#material").hide();
		$("#uom").hide();
		$("#masterplinth_selection").hide();
		$("#spec").hide();

		$('#frmuser').submit(function (event) {
			var txtid = $('#txtid');
			var txtmastermodule = $('#txtmastermodule');
			var txtmasterdescription = $('#txtmasterdescription');
			var txtmasterkjlmodelid = $('#txtmasterkjlmodelid');
			var txtmasterwidth = $('#txtmasterwidth');
			var txtmasterheight = $('#txtmasterheight');
			var txtmasterdepth = $('#txtmasterdepth');
			var txtmasterinstallation = $('#txtmasterinstallation');
			var txtmasterprice = $('#txtmasterprice');
			var txtmasteritemcode = $('#txtmasteritemcode');
			console.log(txtmasteritemcode)
			var txtmasteractive = $('#txtmasteractive');
			var txtbranch = $('#txtbranch');
			var txtmasterep = $('#txtmasterep');
			var txtmastertype = $('#txtmastertype');
			var txtmasterspec = $('#txtmasterspec');
			var txtmastermaterial = $('#txtmastermaterial');
			var txtmasteruom = $('#txtmasteruom');
			var txtmasterplinth_selection = $('#txtmasterplinth_selection');
			//var	txtbranchsrilanka = $('#txtbranchsrilanka');		
			// imgInp = imgInp.substr(imgInp.lastIndexOf('\\') + 1);

			//alert(imgInp);

			var save_result = $('.save_result'); // Get the result div

			if (txtmastermodule.val() != '') { // Check the values is not empty and make the ajax request

				<?php if (isset($_GET['action']) && $_GET['action'] == 'edit') { ?>
					if (!tab_id){
						tab_id = 'load_ezkit_module';
					}
					var UrlToPass = 'action=edit&tab='+ tab_id +'&txtid=' + encodeURIComponent(txtid.val()) + '&txtmastermodule=' + encodeURIComponent(txtmastermodule.val()) + '&txtmasterdescription=' + encodeURIComponent(txtmasterdescription.val()) + '&txtmasterkjlmodelid=' + encodeURIComponent(txtmasterkjlmodelid.val()) + '&txtmasterwidth=' + encodeURIComponent(txtmasterwidth.val()) + '&txtmasterheight=' + encodeURIComponent(txtmasterheight.val()) + '&txtmasterdepth=' + encodeURIComponent(txtmasterdepth.val()) + '&txtmasterinstallation=' + encodeURIComponent(txtmasterinstallation.val()) + '&txtmasterprice=' + encodeURIComponent(txtmasterprice.val()) + '&txtmasteritemcode=' + encodeURIComponent(txtmasteritemcode.val()) + '&txtmasteractive=' + encodeURIComponent(txtmasteractive.val()) + '&txtmasterep=' + encodeURIComponent(txtmasterep.val()) + '&txtmastertype=' + encodeURIComponent(txtmastertype.val()) + '&txtmasterspec=' + encodeURIComponent(txtmasterspec.val()) + '&txtmastermaterial=' + encodeURIComponent(txtmastermaterial.val()) + '&txtmasteruom=' + encodeURIComponent(txtmasteruom.val()) + '&txtmasterplinth_selection=' + encodeURIComponent(txtmasterplinth_selection.val()) + '&tab=' + encodeURIComponent(tab_id);
				<?php } else { ?>
					var UrlToPass = 'action=add&txtid=' + encodeURIComponent(txtid.val()) + '&txtmastermodule=' + encodeURIComponent(txtmastermodule.val()) + '&txtmasterdescription=' + encodeURIComponent(txtmasterdescription.val()) + '&txtmasterkjlmodelid=' + encodeURIComponent(txtmasterkjlmodelid.val()) + '&txtmasterwidth=' + encodeURIComponent(txtmasterwidth.val()) + '&txtmasterheight=' + encodeURIComponent(txtmasterheight.val()) + '&txtmasterdepth=' + encodeURIComponent(txtmasterdepth.val()) + '&txtmasterinstallation=' + encodeURIComponent(txtmasterinstallation.val()) + '&txtmasterprice=' + encodeURIComponent(txtmasterprice.val()) + '&txtmasteritemcode=' + encodeURIComponent(txtmasteritemcode.val()) + '&txtmasteractive=' + encodeURIComponent(txtmasteractive.val()) + '&txtmasterep=' + encodeURIComponent(txtmasterep.val()) + '&txtmastertype=' + encodeURIComponent(txtmastertype.val()) + '&txtmasterspec=' + encodeURIComponent(txtmasterspec.val()) + '&txtmastermaterial=' + encodeURIComponent(txtmastermaterial.val()) + '&txtmasteruom=' + encodeURIComponent(txtmasteruom.val()) + '&txtmasterplinth_selection=' + encodeURIComponent(txtmasterplinth_selection.val()) + '&tab=' + encodeURIComponent(tab_id);
				<?php } ?>
				// alert(UrlToPass);

				$.ajax({ // Send the credential values to another process_department.php using Ajax in POST menthod
					type: 'POST',
					data: UrlToPass,
					url: 'digital_ezykit/ezkit_process.php',
					success: function (responseText) { // Get the result and asign to each cases
						//alert(responseText);

						if (responseText == 0) {
							save_result.html('<div class="form-group has-error"><label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i> Error. Please check with IT</label></div>');
						}
						else if (responseText != 0) {
							// window.location = '?module=digital_ezykit/ezkit_list';
						}
						else {
							alert('<div class="form-group has-error"><label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i> SQL Code error</label></div>');
						}
					}
				});
			}
			return false;
		});
	});
</script>

<script src="../libs/jquery/validator/jquery.form-validator.min.js"></script>
<script> $.validate(); </script>

<script type="text/javascript">
	function readURL(input) {

		if (input.files && input.files[0]) {
			var reader = new FileReader();

			reader.onload = function (e) {
				$('#photoimg').attr('src', e.target.result);
				var txtphoto = e.target.result;
			}

			reader.readAsDataURL(input.files[0]);
		}
	}

	$("#imgInp").change(function () {
		readURL(this);
		$('input[type=hidden].txtphoto').val(txtphoto);
	});
</script>