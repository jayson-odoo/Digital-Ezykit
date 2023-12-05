<?php
session_start();
include '../db.php'; // include the library for database connection
include '../config.php'; // include the config
GetMyConnection();

$worktop = json_decode($_POST['worktop']);
$modules = json_decode($_POST['modules']);
$panels = json_decode($_POST['panels']);
$proposal_id = $_POST['proposal_id'];
$sql = 'select id from tblquotation_summary_kubiq where summary_proposalid = ' .$proposal_id;
$query = mysql_query($sql);
while ($row = mysql_fetch_array($query)) {
	$quotation_summary_id = $row{'id'};        
}
$sql = 'select * from tblproposal_items_dc_kubiq where summary_drawing_id = ' .$quotation_summary_id;
$query = mysql_query($sql);
$row = mysql_fetch_array($query);

if ($row == 0) {
	for ($x = 0; $x < count($worktop); $x++) {
		$sql = 'insert into tblproposal_items_dc_kubiq(
			proposal_id,
			summary_drawing_id,
			item_name,
			item_qty,
			item_uom,
			item_part,
			item_amount,
			item_rrp,
			item_dealer_rate,
			item_model
			)';
		$sql .= ' value("' 
			. mysql_real_escape_string($proposal_id) . '","' 
			. mysql_real_escape_string($quotation_summary_id) . '","' 
			. mysql_real_escape_string($worktop[$x]->description) . '","' 
			. mysql_real_escape_string(1) . '","' 
			. mysql_real_escape_string('Pcs') . '","' 
			. mysql_real_escape_string('C') . '","' 
			. mysql_real_escape_string($worktop[$x]->price) . '","' 
			. mysql_real_escape_string($worktop[$x]->price) . '","' 
			. mysql_real_escape_string(0.6) . '","' 
			. mysql_real_escape_string($worktop[$x]->item_code) 
			. '");';
		$query = mysql_query($sql);
	}

	for ($x = 0; $x < count($modules); $x++) {
		$sql = 'insert into tblproposal_items_dc_kubiq(
			proposal_id,
			summary_drawing_id,
			item_name,
			item_qty,
			item_uom,
			item_part,
			item_amount,
			item_rrp,
			item_dealer_rate,
			item_model
			)';
		$sql .= ' value("' 
			. mysql_real_escape_string($proposal_id) . '","' 
			. mysql_real_escape_string($quotation_summary_id) . '","' 
			. mysql_real_escape_string($modules[$x]->description) . '","' 
			. mysql_real_escape_string(1) . '","' 
			. mysql_real_escape_string('Pcs') . '","' 
			. mysql_real_escape_string('Q') . '","' 
			. mysql_real_escape_string($modules[$x]->price) . '","' 
			. mysql_real_escape_string($modules[$x]->price) . '","' 
			. mysql_real_escape_string(0.6) . '","' 
			. mysql_real_escape_string($modules[$x]->name) 
			. '");';
		$query = mysql_query($sql);
	}

	for ($x = 0; $x < count($panels); $x++) {
		$sql = 'insert into tblproposal_items_dc_kubiq(
			proposal_id,
			summary_drawing_id,
			item_name,
			item_qty,
			item_uom,
			item_part,
			item_amount,
			item_rrp,
			item_dealer_rate,
			item_model,
			item_non_std,
			item_kjl_parentid
			)';
		$sql .= ' value("' 
			. mysql_real_escape_string($proposal_id) . '","' 
			. mysql_real_escape_string($quotation_summary_id) . '","' 
			. mysql_real_escape_string($panels[$x]->description) . '","' 
			. mysql_real_escape_string($panels[$x]->qty) . '","' 
			. mysql_real_escape_string($panels[$x]->uom) . '","' 
			. mysql_real_escape_string($panels[$x]->item_type) . '","' 
			. mysql_real_escape_string($panels[$x]->price) . '","' 
			. mysql_real_escape_string($panels[$x]->price) . '","' 
			. mysql_real_escape_string(0.6) . '","' 
			. mysql_real_escape_string($panels[$x]->item_code) . '","' 
			. mysql_real_escape_string($panels[$x]->non_std) . '","' 
			. mysql_real_escape_string('') 
			. '");';
		$query = mysql_query($sql);
	}
}

CleanUpDB();
?>