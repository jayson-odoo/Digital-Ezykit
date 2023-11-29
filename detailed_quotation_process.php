<?php
session_start();
include '../db.php'; // include the library for database connection
include '../config.php'; // include the config
GetMyConnection();

$worktop = json_decode($_POST['worktop']);
$proposal_id = $_POST['proposal_id'];
$sql = 'select id from tblquotation_summary_kubiq where summary_proposalid = ' .$proposal_id;
$query = mysql_query($sql);
while ($row = mysql_fetch_array($query)) {
	$quotation_summary_id = $row{'id'};        
}

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
		. mysql_real_escape_string($worktop[$x]->item_code) 
		. '");';
	$query = mysql_query($sql);
}

CleanUpDB();
?>