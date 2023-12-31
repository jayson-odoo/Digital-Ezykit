<?php
session_start();
include '../db.php'; // include the library for database connection
include '../config.php'; // include the config
GetMyConnection();

$worktop = json_decode($_POST['worktop']);
$modules = json_decode($_POST['modules']);
$panels = json_decode($_POST['panels']);
$colors = json_decode($_POST['color']);
$discount = json_decode($_POST['discount'])[0]->discount;

$caps = json_decode($_POST['caps']);
$proposal_id = $_POST['proposal_id'];
$sql = 'select id from tblquotation_summary_kubiq where summary_proposalid = ' .$proposal_id;
$query = mysql_query($sql);
while ($row = mysql_fetch_array($query)) {
	$quotation_summary_id = $row{'id'};        
}
$sql = 'select * from tblproposal_items_dc_kubiq where summary_drawing_id = ' .$quotation_summary_id;
$query = mysql_query($sql);
$row = mysql_fetch_array($query);

$summary_subtotalab = 0;

if ($row == 0) {
	for ($x = 0; $x < count($worktop); $x++) {
		$sql = 'select master_active from tblitem_master_kubiq where master_active = "Active" and master_code = "' .$worktop[$x]->item_code. '"';
		echo $sql;
		$query = mysql_query($sql);
		$nrs = mysql_num_rows($query);
		if($nrs > 0){
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
				item_discount,
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
				. mysql_real_escape_string(isset($worktop[$x]->subtype) ? $worktop[$x]->discount : $discount) . '","' 
				. mysql_real_escape_string($worktop[$x]->item_code) 
				. '");';
			$query = mysql_query($sql);
		}
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
			item_discount,
			item_installation,
			item_model
			)';
		$sql .= ' value("' 
			. mysql_real_escape_string($proposal_id) . '","' 
			. mysql_real_escape_string($quotation_summary_id) . '","' 
			. mysql_real_escape_string($modules[$x]->description) . '","' 
			. mysql_real_escape_string(1) . '","' 
			. mysql_real_escape_string('Unit') . '","' 
			. mysql_real_escape_string('Q') . '","' 
			. mysql_real_escape_string($modules[$x]->price) . '","' 
			. mysql_real_escape_string($modules[$x]->price) . '","' 
			. mysql_real_escape_string(0.6) . '","' 
			. mysql_real_escape_string($discount) . '","' 
			. mysql_real_escape_string($modules[$x]->installation) . '","' 
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
			item_discount,
			item_model,
			item_non_std,
			item_w,
			item_h,
			item_d,
			item_kjl_parentid
			)';
		$sql .= ' value("' 
			. mysql_real_escape_string($proposal_id) . '","' 
			. mysql_real_escape_string($quotation_summary_id) . '","' 
			. mysql_real_escape_string($panels[$x]->name) . '","' 
			. mysql_real_escape_string($panels[$x]->qty) . '","' 
			. mysql_real_escape_string($panels[$x]->uom) . '","' 
			. mysql_real_escape_string($panels[$x]->item_type) . '","' 
			. mysql_real_escape_string($panels[$x]->price) . '","' 
			. mysql_real_escape_string($panels[$x]->price) . '","' 
			. mysql_real_escape_string(0.6) . '","' 
			. mysql_real_escape_string($panels[$x]->discount) . '","' 
			. mysql_real_escape_string($panels[$x]->item_code) . '","' 
			. mysql_real_escape_string($panels[$x]->non_std) . '","' 
			. mysql_real_escape_string($panels[$x]->width) . '","' 
			. mysql_real_escape_string($panels[$x]->length) . '","' 
			. mysql_real_escape_string($panels[$x]->depth) . '","' 
			. mysql_real_escape_string('') 
			. '");';
		$query = mysql_query($sql);
		$summary_subtotalab = $summary_subtotalab + $panels[$x]->price;
	}

	for ($x = 0; $x < count($colors); $x++) {
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
			. mysql_real_escape_string($colors[$x]->description) . '","' 
			. mysql_real_escape_string($colors[$x]->qty) . '","' 
			. mysql_real_escape_string($colors[$x]->uom) . '","' 
			. mysql_real_escape_string($colors[$x]->item_type) . '","' 
			. mysql_real_escape_string($colors[$x]->price) . '","' 
			. mysql_real_escape_string($colors[$x]->price) . '","' 
			. mysql_real_escape_string(0.6) . '","' 
			. mysql_real_escape_string($colors[$x]->item_code) . '","' 
			. mysql_real_escape_string($colors[$x]->non_std) . '","' 
			. mysql_real_escape_string('') 
			. '");';
		$query = mysql_query($sql);
	}
	
	for ($x = 0; $x < count($caps); $x++) {
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
			item_discount,
			item_model,
			item_non_std,
			item_kjl_parentid
			)';
		$sql .= ' value("' 
			. mysql_real_escape_string($proposal_id) . '","' 
			. mysql_real_escape_string($quotation_summary_id) . '","' 
			. mysql_real_escape_string($caps[$x]->description) . '","' 
			. mysql_real_escape_string($caps[$x]->qty) . '","' 
			. mysql_real_escape_string($caps[$x]->uom) . '","' 
			. mysql_real_escape_string($caps[$x]->item_type) . '","' 
			. mysql_real_escape_string($caps[$x]->price) . '","' 
			. mysql_real_escape_string($caps[$x]->price) . '","' 
			. mysql_real_escape_string(0.6) . '","' 
			. mysql_real_escape_string($caps[$x]->discount) . '","' 
			. mysql_real_escape_string($caps[$x]->item_code) . '","' 
			. mysql_real_escape_string($caps[$x]->non_std) . '","' 
			. mysql_real_escape_string('') 
			. '");';
		$query = mysql_query($sql);
	}

	if ($summary_subtotalab > 0) {
		$sql = 'update tblquotation_summary_kubiq
		set summary_subtotalab = ' .$summary_subtotalab.
		' where id = ' .$quotation_summary_id.
		' and summary_proposalid = ' .$proposal_id;
		$query = mysql_query($sql);
	}
}

CleanUpDB();
?>