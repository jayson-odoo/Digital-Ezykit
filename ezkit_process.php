<?php
session_start();
include '../db.php'; // include the library for database connection
include '../config.php'; // include the config
GetMyConnection();

if (isset($_POST['action']) && $_POST['action'] == 'add') {
	$txtid = $_POST['txtid'] ?: trim($_POST['txtid']);
	$txtmastermodulename = $_POST['txtmastermodule'] ?: trim($_POST['txtmastermodule']);
	$txtmasterdescription = $_POST['txtmasterdescription'] ?: trim($_POST['txtmasterdescription']);
	$txtmasterkjlmodelid = $_POST['txtmasterkjlmodelid'] ?: trim($_POST['txtmasterkjlmodelid']);
	$txtmasterwidth = $_POST['txtmasterwidth'] ?: trim($_POST['txtmasterwidth']);
	$txtmasterheight = $_POST['txtmasterheight'] ?: trim($_POST['txtmasterheight']);
	$txtmasterdepth = $_POST['txtmasterdepth'] ?: trim($_POST['txtmasterdepth']);
	$txtmasterprice = $_POST['txtmasterprice'] ?: trim($_POST['txtmasterprice']);
	$txtmasteractive = $_POST['txtmasteractive'] ?: trim($_POST['txtmasteractive']);
	$txtmasterinstallation = $_POST['txtmasterinstallation'] ?: trim($_POST['txtmasterinstallation']);
	$txtmasterep = $_POST['txtmasterep'] ?: trim($_POST['txtmasterep']);
	$txtmastertype = $_POST['txtmastertype'] ?: trim($_POST['txtmastertype']);
	$txtmasterspec = $_POST['txtmasterspec'] ?: trim($_POST['txtmasterspec']);
	$txtmastermaterial = $_POST['txtmastermaterial'] ?: trim($_POST['txtmastermaterial']);
	$txtmasteruom = $_POST['txtmasteruom'] ?: trim($_POST['txtmasteruom']);
	$txtmasterplinth_selection = $_POST['txtmasterplinth_selection'] ?: trim($_POST['txtmasterplinth_selection']);
	$tab = $_POST['tab'] ?: trim($_POST['tab']);

	if (!empty($tab)) {
		switch ($tab) {
			case 'load_ezkit_worktop':
				// insert into tblitem_master_ezkit_worktop
				$sql = 'insert into tblitem_master_ezkit_worktop(name, description, width, length, depth, material, spec, price)';
				$sql .= ' value("' . mysql_real_escape_string($txtmastermodulename) . '","' . mysql_real_escape_string($txtmasterdescription) . '","' . mysql_real_escape_string($txtmasterwidth) . '","' . mysql_real_escape_string($txtmasterheight) . '","' . mysql_real_escape_string($txtmasterdepth) . '","' . mysql_real_escape_string($txtmastermaterial) . '","' . mysql_real_escape_string($txtmasterspec) . '","' . mysql_real_escape_string($txtmasterprice) . '")';
				break;
			case 'load_ezkit_doorcolor':
				// insert into tblitem_master_ezkit_door_color
				$sql = 'insert into tblitem_master_ezkit_door_color(name)';
				$sql .= ' value("' . mysql_real_escape_string($txtmastermodulename) . '")';
				break;
			case 'load_ezkit_infill':
				// insert into tblitem_master_ezkit_infill
				$sql = 'insert into tblitem_master_ezkit_infill(name, description, width, length, depth, price)';
				$sql .= ' value("' . mysql_real_escape_string($txtmastermodulename) . '","' . mysql_real_escape_string($txtmasterdescription) . '","' . mysql_real_escape_string($txtmasterwidth) . '","' . mysql_real_escape_string($txtmasterheight) . '","' . mysql_real_escape_string($txtmasterdepth) . '","' . mysql_real_escape_string($txtmasterprice) . '")';
				break;
			case 'load_ezkit_plinth':
				print_r("Here");
				// insert into tblitem_master_ezkit_plinth
				$sql = 'insert into tblitem_master_ezkit_plinth(name, description, width, length, depth, kitchen_wardrobe, uom, price)';
				$sql .= ' value("' . mysql_real_escape_string($txtmastermodulename) . '","' . mysql_real_escape_string($txtmasterdescription) . '","' . mysql_real_escape_string($txtmasterwidth) . '","' . mysql_real_escape_string($txtmasterheight) . '","' . mysql_real_escape_string($txtmasterdepth) . '","' . mysql_real_escape_string($txtmasterplinth_selection) . '","' . mysql_real_escape_string($txtmasteruom) . '","' . mysql_real_escape_string($txtmasterprice) . '")';
				break;
			case 'load_ezkit_handle':
				// insert into tblitem_master_ezkit_handle
				$sql = 'insert into tblitem_master_ezkit_handle(name, description, price)';
				$sql .= ' value("' . mysql_real_escape_string($txtmastermodulename) . '","' . mysql_real_escape_string($txtmasterdescription) . '","' . mysql_real_escape_string($txtmasterprice) . '")';
				break;
			default:
				// insert into tblitem_master_ezkit
				$sql = 'insert into tblitem_master_ezkit(master_module, master_description, master_kjl_model_id, master_width, master_height, master_depth, master_installation, master_price, master_ep, master_active, master_type)';
				$sql .= ' value("' . mysql_real_escape_string($txtmastermodulename) . '", "' . mysql_real_escape_string($txtmasterdescription) . '","' . mysql_real_escape_string($txtmasterkjlmodelid) . '", "' . mysql_real_escape_string($txtmasterwidth) . '", "' . mysql_real_escape_string($txtmasterheight) . '", "' . mysql_real_escape_string($txtmasterdepth) . '", "' . mysql_real_escape_string($txtmasterinstallation) . '", "' . mysql_real_escape_string($txtmasterprice) . '", "' . mysql_real_escape_string($txtmasterep) . '", "' . mysql_real_escape_string($txtmasteractive) . '", "' . mysql_real_escape_string($txtmastertype) . '")';
				break;
		}
	}
	$query = mysql_query($sql);
	echo $sql;
	echo 1;
}

if (isset($_POST['action']) && $_POST['action'] == 'edit') {
	$txtid = $_POST['txtid'] ?: trim($_POST['txtid']);
	$txtmastermodulename = $_POST['txtmastermodule'] ?: trim($_POST['txtmastermodule']);
	$txtmasterdescription = $_POST['txtmasterdescription'] ?: trim($_POST['txtmasterdescription']);
	$txtmasterkjlmodelid = $_POST['txtmasterkjlmodelid'] ?: trim($_POST['txtmasterkjlmodelid']);
	$txtmasterwidth = $_POST['txtmasterwidth'] ?: trim($_POST['txtmasterwidth']);
	$txtmasterheight = $_POST['txtmasterheight'] ?: trim($_POST['txtmasterheight']);
	$txtmasterdepth = $_POST['txtmasterdepth'] ?: trim($_POST['txtmasterdepth']);
	$txtmasterprice = $_POST['txtmasterprice'] ?: trim($_POST['txtmasterprice']);
	$txtmasteractive = $_POST['txtmasteractive'] ?: trim($_POST['txtmasteractive']);
	$txtmasterinstallation = $_POST['txtmasterinstallation'] ?: trim($_POST['txtmasterinstallation']);
	$txtmasterep = $_POST['txtmasterep'] ?: trim($_POST['txtmasterep']);
	$txtmastertype = $_POST['txtmastertype'] ?: trim($_POST['txtmastertype']);
	$txtmasterspec = $_POST['txtmasterspec'] ?: trim($_POST['txtmasterspec']);
	$txtmastermaterial = $_POST['txtmastermaterial'] ?: trim($_POST['txtmastermaterial']);
	$txtmasteruom = $_POST['txtmasteruom'] ?: trim($_POST['txtmasteruom']);
	$txtmasterplinth_selection = $_POST['txtmasterplinth_selection'] ?: trim($_POST['txtmasterplinth_selection']);
	$tab = $_POST['tab'] ?: trim($_POST['tab']);

	if (!empty($tab)) {
		switch ($tab) {
			case 'load_ezkit_worktop':
				$sql = 'update tblitem_master_ezkit_worktop set ';
				$sql .= 'name = "' . mysql_real_escape_string($txtmastermodulename) . '", ';
				$sql .= 'description = "' . mysql_real_escape_string($txtmasterdescription) . '", ';
				$sql .= 'width = "' . mysql_real_escape_string($txtmasterwidth) . '", ';
				$sql .= 'length = "' . mysql_real_escape_string($txtmasterheight) . '", ';
				$sql .= 'depth = "' . mysql_real_escape_string($txtmasterdepth) . '", ';
				$sql .= 'material = "' . mysql_real_escape_string($txtmastermaterial) . '", ';
				$sql .= 'spec = "' . mysql_real_escape_string($txtmasterspec) . '", ';
				$sql .= 'price = "' . mysql_real_escape_string($txtmasterprice) . '"';
				$sql .= 'where id = ' . $txtid;
				break;
			case 'load_ezkit_doorcolor':
				$sql = 'update tblitem_master_ezkit_door_color set ';
				$sql .= 'name = "' . mysql_real_escape_string($txtmastermodulename) . '"';
				$sql .= 'where id = ' . $txtid;
				break;
			case 'load_ezkit_infill':
				$sql = 'update tblitem_master_ezkit_infill set ';
				$sql .= 'name = "' . mysql_real_escape_string($txtmastermodule) . '", ';
				$sql .= 'description = "' . mysql_real_escape_string($txtmasterdescription) . '", ';
				$sql .= 'width = "' . mysql_real_escape_string($txtmasterwidth) . '", ';
				$sql .= 'length = "' . mysql_real_escape_string($txtmasterheight) . '", ';
				$sql .= 'depth = "' . mysql_real_escape_string($txtmasterdepth) . '", ';
				$sql .= 'price = "' . mysql_real_escape_string($txtmasterprice) . '"';
				$sql .= 'where id = ' . $txtid;
				break;
			case 'load_ezkit_plinth':
				$sql = 'update tblitem_master_ezkit_plinth set ';
				$sql .= 'name = "' . mysql_real_escape_string($txtmastermodule) . '", ';
				$sql .= 'description = "' . mysql_real_escape_string($txtmasterdescription) . '", ';
				$sql .= 'width = "' . mysql_real_escape_string($txtmasterwidth) . '", ';
				$sql .= 'length = "' . mysql_real_escape_string($txtmasterheight) . '", ';
				$sql .= 'depth = "' . mysql_real_escape_string($txtmasterdepth) . '", ';
				$sql .= 'kitchen_wardrobe = "' . mysql_real_escape_string($txtmasterplinth_selection) . '", ';
				$sql .= 'uom = "' . mysql_real_escape_string($txtmasteruom) . '", ';
				$sql .= 'price = "' . mysql_real_escape_string($txtmasterprice) . '" ';
				$sql .= 'where id = ' . $txtid;
				break;
			case 'load_ezkit_handle':
				$sql = 'update tblitem_master_ezkit_handle set ';
				$sql .= 'name = "' . mysql_real_escape_string($txtmastermodule) . '", ';
				$sql .= 'description = "' . mysql_real_escape_string($txtmasterdescription) . '", ';
				$sql .= 'price = "' . mysql_real_escape_string($txtmasterprice) . '" ';
				$sql .= 'where id = ' . $txtid;
				break;
			default:
				$sql = 'update tblitem_master_ezkit set ';
				$sql .= 'master_module = "' . mysql_real_escape_string($txtmastermodule) . '", ';
				$sql .= 'master_description = "' . mysql_real_escape_string($txtmasterdescription) . '", ';
				$sql .= 'master_kjl_model_id = "' . mysql_real_escape_string($txtmasterkjlmodelid) . '", ';
				$sql .= 'master_width = "' . mysql_real_escape_string($txtmasterwidth) . '", ';
				$sql .= 'master_height = "' . mysql_real_escape_string($txtmasterheight) . '", ';
				$sql .= 'master_depth = "' . mysql_real_escape_string($txtmasterdepth) . '", ';
				$sql .= 'master_installation = "' . mysql_real_escape_string($txtmasterinstallation) . '", ';
				$sql .= 'master_price = "' . mysql_real_escape_string($txtmasterprice) . '", ';
				$sql .= 'master_ep = "' . mysql_real_escape_string($txtmasterep) . '", ';
				$sql .= 'master_active = "' . mysql_real_escape_string($txtmasteractive) . '", ';
				$sql .= 'master_type = "' . mysql_real_escape_string($txtmastertype) . '" ';
				$sql .= 'where id = ' . $txtid;
				break;
		}
	}

	$query = mysql_query($sql);
	echo $sql;

	echo 1;
}

if (isset($_POST['action']) && $_POST['action'] == 'delete') {
	$id = $_POST['id'] ?: trim($_POST['id']);
	$table = $_POST['table'] ?: trim($_POST['table']);
  
	if(!empty($table)){
		$query = mysql_query('DELETE FROM '.$table.' WHERE id = "' . $id . '" ');
	}
	echo 1;
}

CleanUpDB();
?>