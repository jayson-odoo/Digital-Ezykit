<?php
include '../config.php'; // include the config
// User must be logged in to access this page
session_start();
// if (!isset($_SESSION['auth']) || $_SESSION['auth'] != 1) {
//     header('Location: login.php');
//     exit();
// }

// Read ezkit data from database
include "../db.php";
GetMyConnection();
// For serial number
$sql = 'select * from tblitem_master_ezkit_serialnumber ';	
$r = mysql_query($sql);
$nr   = mysql_num_rows($r); // Get the number of rows
if($nr > 0){
    $arrayserialnumber = array(); // array of serial number
    while ($row = mysql_fetch_assoc($r)) {
        $master_serialnumber = $row['master_serialnumber'];
        $ezkit_id = $row['ezkit_id'];
        $arrayserialnumber[$master_serialnumber] = $ezkit_id; // add the serial number into the array
    }
}

// For module/description/price
$active = "Y"; // only select active modules
$sql_ezkit = 'select * from tblitem_master_ezkit where master_active = "'.$active.'"';	
$r_ezkit = mysql_query($sql_ezkit);
$nr_ezkit = mysql_num_rows($r_ezkit); // Get the number of rows
if($nr_ezkit > 0){
    $arraymodule = array(); // array of modules
    $arraydescription = array(); // array of descriptions
    $arrayprice = array(); // array of prices
    $arrayepprices = array(); // array of ep prices
    $arrayinstallationprice = array(); // array of installaion prices
    while ($row = mysql_fetch_assoc($r_ezkit)) {
        $id = $row['id'];
        $master_module = $row['master_module'];
        $master_description = $row['master_description'];
        $master_price = $row['master_price'];
        $master_ep = $row['master_ep'];
        $master_installation = $row['master_installation'];
        $arraymodule[$id] = $master_module; // add the module into the array
        $arraydescription[$id] = $master_description; // add the description into the array
        $arrayprice[$id] = $master_price; // add the price into the array
        $arrayepprices[$id] = $master_ep; // add the price into the array
        $arrayinstallationprice[$id] = $master_installation; // add the price into the array
    }
}

// $data_string = json_encode($arrayserialnumber);
// print $data_string;
$_SESSION['ezikit'] = "";
CleanUpDB();

if(isset($_GET['ezkit']) && $_GET['ezkit'] == 'true'){
  $digitalezkitarr = $_GET['items'] ?:'';
  $worktop = $_GET['worktop'] ?: 0 ;
  $unitprice = $_GET['unitprice'] ?: 1146 ;
  $discount = $_GET['discount'] ?: 0 ;
  $transportation = $_GET['transportation'] ?: 0 ;
  $digitalezkitarr = json_decode($digitalezkitarr,1);
  $masteruid_arr = [];

  foreach($digitalezkitarr as $key => $arr){
    $count = 1;
    $sql_ezkit = 'SELECT * FROM tblitem_master_ezkit WHERE master_kjl_model_id = "'.$arr['productId'].'";';	
    $r_ezkit = executeQuery($sql_ezkit);
    while ($row = mysqli_fetch_assoc($r_ezkit)) {
      if (isset($masteruid_arr[$row['master_uid']]) && $masteruid_arr[$row['master_uid']] >= 1 ){
        $masteruid_arr[$row['master_uid']] = $masteruid_arr[$row['master_uid']] + 1;
      } else {
        $masteruid_arr[$row['master_uid']] = $count;
      }
      $count++;
    }
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <!-- <title>EziKit 2.0 Quotation</title> -->
  <script src="digital_ezykit/scripts/ezykit_share.js"></script>
  <script>
    var errorUids = []; // Declare errorUids globally
    var nfcReader;
    var moduleCounts = [];
    var historicaluniqueid = []; // to store tag number (always 20 digit)
    var worktop_check = false; // default false
    var transportation_check = false; // default false
    var checkfocus = 0; // to check for focus
    var moduletotal = 0;
    var arrayuniqueid = []; // to store converted tag number (between 1-2 digit)
    var worktoptypecheck = 0; // for worktop type checking
    var globalsurcharge = 0; // for worktop surcharge
    var totalinstallationprice = 0; // for installation charge
    var worktopdescription_full = "";
    var description_surcharge_full = "";
    var worktopCharges = 0;
    var total_surcharge = 0;
    var transportationDistance = 0;
    var transportationCharges = 0;
    var discountCharges = 0;
    var item_id = "";
    var qty = 0;

    // Assign JS variable to PHP variable
    var arrayserialnumber = '<?php echo json_encode($arrayserialnumber);?>';
    var arraymodule = '<?php echo json_encode($arraymodule);?>';
    var arraydescription = '<?php echo json_encode($arraydescription);?>';
    var arrayprice = '<?php echo json_encode($arrayprice);?>';
    var arrayepprices = '<?php echo json_encode($arrayepprices);?>';
    var arrayinstallationprice = '<?php echo json_encode($arrayinstallationprice);?>';
    var digitalezkitarr = '<?php echo json_encode($masteruid_arr);?>';
    var kjl_data_kjl = '<?php echo json_encode($digitalezkitarr);?>';
    
    const objarrayserialnumber = JSON.parse(arrayserialnumber); // convert to javascript object
    const objarraymodule = JSON.parse(arraymodule); // convert to javascript object
    const objarraydescription = JSON.parse(arraydescription); // convert to javascript object
    const objarrayprice = JSON.parse(arrayprice); // convert to javascript object
    const objarrayepprice = JSON.parse(arrayepprices); // convert to javascript object
    const objarrayinstallationprice = JSON.parse(arrayinstallationprice); // convert to javascript object
    const objarraydigitalezkit = JSON.parse(digitalezkitarr);
    var objarraykjl_data_kjl = JSON.parse(kjl_data_kjl);
    objarraykjl_data_kjl = {"items": objarraykjl_data_kjl};

    var digitalezarr = Object.keys(objarraydigitalezkit);
    if(digitalezarr.length > 0) {
      for (var key in objarraydigitalezkit) {
        if (objarraydigitalezkit.hasOwnProperty(key)){
          item_id = key;
          qty = objarraydigitalezkit[key];
          document.getElementById("worktopUnitMeasurement").value = <?php echo $worktop;  ?>;
          document.getElementById("worktopUnitPrice").value = <?php echo $unitprice; ?>;
          document.getElementById("transportationDistance").value = <?php echo $transportation; ?>;
          document.getElementById("discountpercentage").value = <?php echo $discount; ?>;
          calculateQuotation(3);
        }
      }
    }

    function generatequotation() {
      let worktopUnitMeasurement = parseFloat(document.getElementById("worktopUnitMeasurement").value);
      let worktopUnitPrice = parseFloat(document.getElementById("worktopUnitPrice").value);
      let transportationDistance = parseFloat(document.getElementById("transportationDistance").value);
      let discountpercentage = parseFloat(document.getElementById("discountpercentage").value);
      if(historicaluniqueid.length === 0){ // if empty array show a alert message above
        alert("Please add in at least 1 module!");
      }else if(worktopUnitMeasurement < 0){
        alert("Worktop Unit Measurement cannot be negative!");
      } else if(worktopUnitPrice < 0){
        alert("Worktop Unit Price cannot be negative!");
      } else if(transportationDistance < 0){
        alert("Transportation distance cannot be negative!");
      } else if(discountpercentage < 0){
        alert("Discount percentage cannot be negative!");
      } else if(discountpercentage > 100){
        alert("Discount percentage cannot exceed 100!");
      }else { // proceed to generate the quotation
        const jsonString = JSON.stringify(objarraykjl_data_kjl);
        const encodedString = encodeURIComponent(jsonString);
        // Create a new URLSearchParams object
        const searchParams = new URLSearchParams();
        // Iterate through the object's properties and append them to the URLSearchParams object
        for (const key in objarraykjl_data_kjl) {
          if (key === 'items') {
            // If the key is 'items', stringify the array of objects and append it as a single query parameter
            searchParams.append(key, JSON.stringify(objarraykjl_data_kjl[key]));
          } else if (Array.isArray(objarraykjl_data_kjl[key])) {
            // If the value is an array, append it as multiple values for the same key
            objarraykjl_data_kjl[key].forEach(value => {
              searchParams.append(key, value);
            });
          } else {
            // If the value is not an array, append it as a single key-value pair
            searchParams.append(key, query_arr[key]);
          }
        }

        // Get the final query string
        const queryString = searchParams.toString();
        
        sendData();
        // open_KJL();
        window.open(window.location.origin + "/index.php?module=leads_cc_create_kubiq&"+queryString);
        // location.reload();
      }
    }

    function deleteAllCookies() {
      console.log("test");
      const cookies = document.cookie.split(";");

      for (let i = 0; i < cookies.length; i++) {
          const cookie = cookies[i];
          const eqPos = cookie.indexOf("=");
          const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
          document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
      }
    }

    function getModule(uid) {
      // var modules = {
      //   1: "QV4567",
      //   2: "QLS9067",
      //   3: "QL9067",
      //   4: "QV3072",
      //   5: "QV4572",
      //   6: "QV9072",
      //   7: "QL3067",
      //   8: "QL3AD4567",
      //   9: "QL3AD6067",
      //   10: "QLT60200",
      //   11: "QLC10567-L",
      //   12: "QLC1567-R",
      //   13: "QV4567-2",
      //   14: "QLS9067-2"
      // };
      var modules = objarraymodule;

      return modules[uid] || "";
    }

    function getDescription(uid) {
      // var descriptions = {
      //   1: "450 x 700 x 560mm",
      //   2: "900 x 700 x 560mm (SINK)",
      //   3: "900 x 700 x 560mm",
      //   4: "300 x 700 x 300mm",
      //   5: "450 x 700 x 300mm",
      //   6: "900 x 700 x 300mm",
      //   7: "300 x 700 x 560mm",
      //   8: "450 x 700 x 560mm",
      //   9: "600 x 700 x 560mm",
      //   10: "600 x 2000 x 560mm",
      //   11: "1050 x 700 x 560mm",
      //   12: "1050 x 700 x 560mm",
      //   13: "450 x 700 x 560mm",
      //   14: "900 x 700 x 560mm (SINK)"
      // };
      var descriptions = objarraydescription;

      return descriptions[uid] || "";
    }

    function getPrice(uid) {
      // var prices = {
      //   1: 337,
      //   2: 475,
      //   3: 535,
      //   4: 208,
      //   5: 261,
      //   6: 427,
      //   7: 280,
      //   8: 752,
      //   9: 843,
      //   10: 993,
      //   11: 560,
      //   12: 560,
      //   13: 888,
      //   14: 999
      // };
      var prices = objarrayprice;

      return prices[uid] || 0;
    }

    function getEpPrice(uid) {
      // var prices = {
      //   1: 337,
      //   2: 475,
      //   3: 535,
      //   4: 208,
      //   5: 261,
      //   6: 427,
      //   7: 280,
      //   8: 752,
      //   9: 843,
      //   10: 993,
      //   11: 560,
      //   12: 560,
      //   13: 888,
      //   14: 999
      // };
      var epprices = objarrayepprice;

      return epprices[uid] || 0;
    }

    function getInstallationPrice(uid) {
      // var prices = {
      //   1: 337,
      //   2: 475,
      //   3: 535,
      //   4: 208,
      //   5: 261,
      //   6: 427,
      //   7: 280,
      //   8: 752,
      //   9: 843,
      //   10: 993,
      //   11: 560,
      //   12: 560,
      //   13: 888,
      //   14: 999
      // };
      var installationprices = objarrayinstallationprice;

      return installationprices[uid] || 0;
    }

    function clearList() {
      document.getElementById("uidInput").value = "";
      var table = document.getElementById("quotationTable");
      var grandtotalCell = document.getElementById("grandTotal").innerHTML;
      // Using match with regEx
      var matches = grandtotalCell.match(/(\d+)/);
      if (matches) {
        // console.log(matches[0]);
        var grandtotal = matches[0];
      }
      // console.log(grandtotalCell);
      for (let index = 0; index < historicaluniqueid.length; index++){
        var row = table.rows[1];
        var priceCell = row.cells[4];
         // Input string
        let str = priceCell.innerHTML;
        // console.log(str);
        matches = str.match(/(\d+)/);
        // Display output if number extracted
        if (matches) {
            // console.log(matches[0]);
            var price = matches[0];
        }
        grandtotal = grandtotal - price; // deduct the grandtotal from each item
        document.getElementById("quotationTable").deleteRow(1);
      }

      // deduct installation charges
      var totalinstallationCell = document.getElementById("installationCharges").innerHTML;
      // Using match with regEx
      var totalinstallationmatches = totalinstallationCell.match(/(\d+)/);
      if (totalinstallationmatches) {
        // console.log(matches[0]);
        var totalinstallationcharges = totalinstallationmatches[0];
      }
      grandtotal = grandtotal - totalinstallationcharges; // deduct the total installation charges

      moduleCounts = [];
      historicaluniqueid = [];
      arrayuniqueid = [];
      moduletotal = 0;
      totalinstallationprice = 0;

      // Reupdate discount charges according to grand total, add back old discount value and deduct transportation charges
      var discountChargesCell = document.getElementById("discountCharges").innerHTML;
      // Using match with regEx
      var discountChargesmatches = discountChargesCell.match(/(\d+)/);
      if (discountChargesmatches) {
        // console.log(matches[0]);
        var discountChargesvalue = discountChargesmatches[0];
      }
      grandtotal = parseFloat(grandtotal);
      discountChargesvalue = parseFloat(discountChargesvalue);
      grandtotal = grandtotal + discountChargesvalue;

      var discountCharges = 0;
      var transportationChargesCell = document.getElementById("transportationCharges").innerHTML;
      // Using match with regEx
      var transportationChargesmatches = transportationChargesCell.match(/(\d+)/);
      if (transportationChargesmatches) {
        // console.log(matches[0]);
        var transportationCharges = transportationChargesmatches[0];
      }
      var grandTotalexcludediscount = grandtotal - transportationCharges; // Exclude transportation charges to check for discount charges
      // if(grandTotalexcludediscount > 6000){ // more than 6000, 6% discount
      //   discountCharges = grandTotalexcludediscount * 6 / 100;
      //   discountCharges = Math.ceil(discountCharges); // round up the discount charges
      // }else if(grandTotalexcludediscount > 3000){ // more than 3000, 3% discount
      //   discountCharges = grandTotalexcludediscount * 3 / 100;
      //   discountCharges = Math.ceil(discountCharges); // round up the discount charges
      // }else{ // less than 3000, no discount
      //   discountCharges = 0;
      // }

      // Update the discount cell values with the new total prices
      document.getElementById('discountCharges').innerHTML = '<strong>-RM' + discountCharges.toFixed(2) + '</strong>';

      // Update the installation cell values with the new total prices
      document.getElementById('installationCharges').innerHTML = '<strong>-RM' + totalinstallationprice.toFixed(2) + '</strong>';

      // Recalculate grand total
      if(isNaN(discountCharges)){ // no price no need to add
        grandtotal = grandtotal;
      }else{
        grandtotal = grandtotal  - discountCharges;
      }

      // Update row numbers
      var table = document.getElementById("quotationTable");
      const rows = table.querySelectorAll('tr:not(:first-child)');
          rows.forEach((row, index) => {
            row.cells[0].textContent = index + 1;
          });
      // calculateQuotation();

      // Update grand total
      var grandTotalUpdate = document.getElementById("grandTotal");
      grandTotalUpdate.innerHTML = "<strong>Grand Total: RM" + grandtotal.toFixed(2) + "</strong>";
    }

    function startScan() {
      checkfocus = 1;
      document.getElementById("uidInput").focus();
      const overlapbutton = document.getElementById("overlapbutton");
      // overlapbutton.style.backgroundColor = 'green';
      // overlapbutton.classList.toggle('btn btn-success');
      overlapbutton.className = "btn btn-success";
      var stopScanMessage = document.getElementById("stopScanMessage");
      stopScanMessage.style.display = "block";
      // console.log("startscan");
      // if(checkfocus == 0){
      //   document.getElementById("uidInput").focus();
      //   checkfocus = 1;
      //   const overlapbutton = document.getElementById("overlapbutton");
      //   overlapbutton.style.backgroundColor = 'green';
      // }else{
      //   document.getElementById("uidInput").blur();
      //   checkfocus = 0;
      // }
    }

    function clearInvalidUids() {
      var uidInput = document.getElementById("uidInput");
      var uidArray = uidInput.value.split("\n");
      var validUids = [];

      for (var i = 0; i < uidArray.length; i++) {
        var uid = uidArray[i].trim();
        if (uid !== "" && !errorUids.includes(uid)) {
          validUids.push(uid);
        }
      }

      uidInput.value = validUids.join("\n");
      calculateQuotation();
    }

    function playSound(soundUrl) {
      var audio = new Audio(soundUrl);
      audio.play();
    }

    // to toggle worktop category and type
    function toggleworktopselection(){
      var worktopcategorySelect = document.getElementById("worktopcategory");
      var worktoptypeSelect = document.getElementById("worktoptype");

      // When user select the category, auto change the type and call calculatequotation()
      worktopcategorySelect.addEventListener("change", function() {
        var selectedworktopcategory = worktopcategorySelect.options[worktopcategorySelect.selectedIndex].value;
        $('#worktoptype').find('option').remove();
        if (selectedworktopcategory === "Quartz") {
          // worktoptypeSelect = "40mm_s_series";
          // worktoptypeSelect.options[0].disabled = false;
          // worktoptypeSelect.options[1].disabled = false;
          // worktoptypeSelect.options[2].disabled = true;
          // worktoptypeSelect.options[3].disabled = true;
          $('#worktoptype').append(`<option value="40mm S series">40mm S series</option>`); 
          $('#worktoptype').append(`<option value="40mm P series">40mm P series</option>`); 
        } else if (selectedworktopcategory === "Compact") {
          // worktoptypeSelect = "12mm_thickness";
          // worktoptypeSelect.options[0].disabled = true;
          // worktoptypeSelect.options[1].disabled = true;
          // worktoptypeSelect.options[2].disabled = false;
          // worktoptypeSelect.options[3].disabled = false;
          $('#worktoptype').append(`<option value="12mm thickness">12mm thickness</option>`); 
          $('#worktoptype').append(`<option value="38mm thickness">38mm thickness</option>`);  
        }
        worktoptypecheck = 1;
        calculateQuotation();
      });

      //When user select the type, call calculatequotation()
      worktoptypeSelect.addEventListener("change", function() {
        worktoptypecheck = 1;
        calculateQuotation();
      });
    }

    function sleep(miliseconds) {
        var currentTime = new Date().getTime();
        while (currentTime + miliseconds >= new Date().getTime()) {
        }
    }

    document.addEventListener("DOMContentLoaded", function(event) {
      startNfcReader(); // Automatically start the NFC reader
    });

    window.onload = toggleworktopselection;
    // window.addEventListener("load",function(){
    //   deleteAllCookies();
    // },false);

  </script>
  <style>
    html, body {
      height: 100%;
      margin: 0;
      overflow: hidden;
    }

    #header {
      display: flex;
      align-items: center;
      padding: 16px;
      background-color: #f5f5f5;
    }

    #logo {
      height: 40px;
      margin-right: 16px;
    }

    #title {
      font-size: 24px;
      font-weight: bold;
    }

    #container {
      display: flex;
      align-items: flex-start;
      height: calc(100% - 80px);
      padding: 16px;
    }

    #uidInput {
      width: 80px;
      height: 8em;
      padding: 8px;
      resize: none;
      margin-right: 16px;
    }

    #uidTitle {
      font-weight: bold;
    }

    #quotation {
      flex-grow: 1;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 8px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    #grandTotal {
      margin-top: 16px;
      font-weight: bold;
    }

    #disclaimer {
      font-size: 11px;
    }

    #errorCell {
      color: red;
      margin-top: 8px;
      margin-bottom: 16px;
    }

    #clearInvalidButton {
      display: none;
    }

    #generatequotationbutton {
      display: none;
    }

    button {
      padding: 10px 24px;
      font-weight: bold;
    }

    #fullscreenButton {
      position: fixed;
      bottom: 16px;
      right: 16px;
      z-index: 999;
    }

    #nfcStatus {
      margin-top: 8px;
    }

    #worktopUnitMeasurement {
      width: 50px; /* Adjust the width as per your preference */
    }

    #worktopUnitPrice {
      width: 60px; /* Adjust the width as per your preference */
    }

    #transportationDistance {
      width: 50px; /* Adjust the width as per your preference */
    }

    #discountpercentage {
      width: 50px; /* Adjust the width as per your preference */
    }

    .ClearListButton {
      width: 110px;
    }

    .uidInputTextArea {
      width: 80px !important;
      height: 5px !important;
    }

    #overlapbutton {
      height: 40px !important;
      width: 80px !important;
    }

  </style>
</head>
<body>
<div class="row">
	<div class="col-md-12">
    <div class="panel panel-default">			
			<div class="panel-body">
     <!-- content -->
  <!-- <div id="content" class="app-content" role="main"> -->
    <!-- <div class="box"> -->
      <!-- Content Navbar -->
      <!-- <div class="navbar md-whiteframe-z1 no-radius blue"> -->
        <!-- Open side - Naviation on mobile -->
        <!-- <a md-ink-ripple  data-toggle="modal" data-target="#aside" class="navbar-item pull-left visible-xs visible-sm"><i class="mdi-navigation-menu i-24"></i></a> -->
        <!-- / -->
        <!-- Page title - Bind to $state's title -->
        <!-- <div class="navbar-item pull-left h4">Kubiq Ezikit</div> -->
        <!-- / -->
		
      <!-- </div> -->
       <!-- Content -->
       <!-- <div class="box-row">
        <div class="box-cell">
          <div class="box-inner padding"> -->
  <!-- <div id="header">
    <img id="logo" src="https://kubiq.com.my/wp-content/uploads/2023/03/kubiq-logo.png" alt="Kubiq Logo">
    <h1 id="title">EziKit 2.0 Quotation</h1>
  </div> -->
  <div id="container">
    <div id="quotation">
      <table id="quotationTable">
        <tr>
          <th>No.</th>
          <th>Module</th>
          <th>Description</th>
          <th>Quantity</th>
          <th>Total (RM)</th>
        </tr>
        <tr>
          <td id="worktoprunningno">1</td>
          <td>Worktop</td>
          <td>M<sup>2</sup>:
          <input type="number" id="worktopUnitMeasurement" name="worktopUnitMeasurement" value="0" oninput="calculateQuotation()">  * Unit Price:
            <input type="number" id="worktopUnitPrice" name="worktopUnitPrice" value="1146" oninput="calculateQuotation()">
            <label for="worktopcategory">Type:</label>
            <select id="worktopcategory">
              <option value="Quartz">Quartz</option>
              <option value="Compact">Compact</option>
            </select>
            <label for="worktoptype">Spec:</label>
            <select id="worktoptype">
              <option value="40mm S series">40mm S series</option>
              <option value="40mm P series">40mm P series</option>
            </select>
          </td>
          <td>1</td>
          <td id="worktopCharges"><strong>RM0.00</strong></td>
        </tr>
        <tr>
          <td id="transportrunningno">2</td>
          <td>Transportation</td>
          <td>Distance:<input type="number" id="transportationDistance" name="transportationDistance" value="0" oninput="calculateQuotation()"> km</td>
          <td>1</td>
          <td id="transportationCharges"><strong>RM0.00</strong></td>
        </tr>
        <tr>
          <td id="discountrunningno">3</td>
          <td>Discount</td>
          <td>Discount:<input type="number" id="discountpercentage" name="discountpercentage" value="0" oninput="calculateQuotation()" min="0" max="100"> %</td>
          <td>1</td>
          <td id="discountCharges"><strong>-RM0.00</strong></td>
        </tr>
        <tr>
          <td id="installationrunningno">4</td>
          <td>Installation</td>
          <td>Installation</td>
          <td>1</td>
          <td id="installationCharges"><strong>RM0.00</strong></td>
        </tr>
      </table>
      <!-- <div>
        <label for="worktopUnitMeasurement">Worktop Unit Measurement:</label>
        <input type="number" id="worktopUnitMeasurement" name="worktopUnitMeasurement" value="0" oninput="calculateQuotation()">
        <label for="worktopUnitPrice">Worktop Unit Price:</label>
        <input type="number" id="worktopUnitPrice" name="worktopUnitPrice" value="0" oninput="calculateQuotation()">
      </div>
      <div>
        <label for="transportationDistance">Transportation Distance:</label>
        <input type="number" id="transportationDistance" name="transportationDistance" value="0" oninput="calculateQuotation()">
        <label for="transportationUnitPrice">Transportation Unit Price:</label>
        <input type="number" id="transportationUnitPrice" name="transportationUnitPrice" value="0" oninput="calculateQuotation()">
      </div> -->
      <div id="grandTotal">
        <strong>Grand Total: RM0.00</strong>
      </div>
      <div id="disclaimer"> 
        Disclaimer: The quotation provided here is preliminary and not a finalise pricing. The actual cost is subject to change based on an on-site measurement and assessment.
      </div>
      <br>
      <span id="uidTitle">Start Scanning:</span>
      <textarea id="uidInput" class="uidInputTextArea" placeholder="Enter UID" oninput="calculateQuotation(0)" style="z-index: -2; position: absolute;"></textarea>
    <!-- <textarea id="uidInput" class="uidInputTextArea" placeholder="Enter UID" oninput="calculateQuotation(0)"></textarea>  -->
      <button id="overlapbutton" onclick="startScan()" class="btn btn-danger">Start</button>
      <div id="stopScanMessage" hidden>
        <strong>Click anywhere else to stop scanning</strong>
      </div>
      <div id="errorCell"></div>
      <button id="clearInvalidButton" onclick="clearInvalidUids()">Clear invalid UID</button>
      <br>
      <div id="generatequotation">
        <button onclick="clearList()" class="ClearListButton">Clear list</button>
        <button id="generatequotationbutton" onclick="generatequotation()">Generate Quotation</button>
      </div>
    </div>
  </div>
  <!-- </div>
  </div>
  </div> -->
  <div id="fullscreenButton">
    <button onclick="toggleFullscreen()">Toggle Fullscreen</button>
  </div>
  <!-- </div> -->
<!-- </div> -->
  </div>
  </div>
  </div>
  </div>
  <script>
    document.getElementById("overlapbutton").addEventListener("mouseover", (event) => {
        checkfocus = 1;
      });
    document.getElementById("overlapbutton").addEventListener("mouseout", mouseOut);    

    function mouseOut(){
      if (document.getElementById("overlapbutton").classList.contains('btn btn-success')) {
        document.getElementById("uidInput").focus();
      }
      checkfocus = 0;
      const uidInputlistener = document.getElementById("uidInput");
      // console.log("mouseout "+checkfocus);
      
      uidInputlistener.addEventListener("focusout", (event) => {
        if(checkfocus == 0){
          const overlapbutton = document.getElementById("overlapbutton");
          // overlapbutton.style.backgroundColor = 'red';
          // overlapbutton.classList.toggle('btn btn-danger');
          overlapbutton.className = "btn btn-danger";
          var stopScanMessage = document.getElementById("stopScanMessage");
          stopScanMessage.style.display = "none";
          // document.getElementById("uidInput").blur();
          // checkfocus = 0;
          // console.log("focusout "+checkfocus);
        }
      });
    }

    function startNfcReader() {
      if ('NDEFReader' in window && 'TextDecoder' in window) {
        try {
          nfcReader = new NDEFReader();
          nfcReader.scan().then(() => {
            nfcReader.addEventListener('reading', handleNfcReading);
            document.getElementById("nfcStatus").innerText = "NFC Status: On";
          }).catch((error) => {
            console.error('Error starting NFC reader:', error);
          });
        } catch (error) {
          console.error('Error initializing NFC reader:', error);
        }
      } else {
        console.error('Web NFC API is not supported in this browser.');
      }
    }

    function handleNfcReading(event) {
      var serialNumber = event.serialNumber;
      var renamedUid = renameSerialNumber(serialNumber);

      if (isUidInList(renamedUid)) {
        removeUidFromList(renamedUid);
        playSound("https://signaturegroup.com.my/unscan.mp3"); // Play sound for UID removal
      } else {
        addSerialNumber(renamedUid);
      }
    }

    function renameSerialNumber(serialNumber) {
      // var renamedSerialNumbers = {
      //   "04:6f:cf:aa:db:36:80": "1",
      //   "04:06:fc:9a:06:73:81": "2",
      //   "04:6f:f6:aa:db:36:80": "4",
      //   "04:6f:d9:aa:db:36:80": "5",
      //   "04:fb:fd:9a:06:73:80": "6",
      //   "04:6e:ce:aa:db:36:80": "8",
      //   "04:02:fc:9a:06:73:81": "9",
      //   "04:0a:fc:9a:06:73:81": "10",
      //   "04:f8:fc:9a:06:73:80": "12",
      //   "67:6e:12:72": "13",
      //   "84:1e:90:a3": "14"
      // };
      const objarrayserialnumber = JSON.parse(arrayserialnumber);
      var renamedSerialNumbers = objarrayserialnumber;
      // console.log(serialNumber[0].length);
      if(serialNumber[0].length==20){
        return renamedSerialNumbers[serialNumber] || "";
      }else{
        return "";
      }
    }

    function isUidInList(uid) {
      var uidInput = document.getElementById("uidInput");
      var uidArray = uidInput.value.split("\n");

      return uidArray.includes(uid);
    }

    function removeUidFromList(uid) {
      var uidInput = document.getElementById("uidInput");
      var uidArray = uidInput.value.split("\n");
      var index = uidArray.indexOf(uid);

      if (index !== -1) {
        uidArray.splice(index, 1);
        uidInput.value = uidArray.join("\n");
        calculateQuotation();
      }
    }

    function addSerialNumber(serialNumber) {
      var uidInput = document.getElementById("uidInput");
      uidInput.value += serialNumber + '\n';
      calculateQuotation();
    }

    function playSound(soundUrl) {
      var audio = new Audio(soundUrl);
      audio.play();
    }

    function enterFullscreen() {
      var appContainer = document.documentElement;

      if (appContainer.requestFullscreen) {
        appContainer.requestFullscreen();
      } else if (appContainer.mozRequestFullScreen) {
        appContainer.mozRequestFullScreen();
      } else if (appContainer.webkitRequestFullscreen) {
        appContainer.webkitRequestFullscreen();
      } else if (appContainer.msRequestFullscreen) {
        appContainer.msRequestFullscreen();
      }
    }

    function exitFullscreen() {
      if (document.exitFullscreen) {
        document.exitFullscreen();
      } else if (document.mozCancelFullScreen) {
        document.mozCancelFullScreen();
      } else if (document.webkitExitFullscreen) {
        document.webkitExitFullscreen();
      } else if (document.msExitFullscreen) {
        document.msExitFullscreen();
      }
    }

    function toggleFullscreen() {
      if (isFullscreen()) {
        exitFullscreen();
      } else {
        enterFullscreen();
      }
    }

    function isFullscreen() {
      return (
        document.fullscreenElement ||
        document.mozFullScreenElement ||
        document.webkitFullscreenElement ||
        document.msFullscreenElement
      );
    }

    document.addEventListener("DOMContentLoaded", function(event) {
      startNfcReader(); // Automatically start the NFC reader
    });
  </script>
</body>
</html>
<?php //include('footer.php'); ?>
