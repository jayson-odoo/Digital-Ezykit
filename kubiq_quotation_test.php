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
          calculateQuotation(3);
        }
      }
    }

    function calculateQuotation(flag) {
      // console.log(flag);
      var uidInput = document.getElementById("uidInput").value;
      var uidArray = uidInput.split("\n");
      var table = document.getElementById("quotationTable");
      moduleCounts = [];
      errorUids = []; // Reset errorUids
      var grandTotal = 0;
      total_surcharge = 0;
      worktopdescription_full = "";
      description_surcharge_full = "";
      transportationDistance = 0;

      // Check for worktop category and type
      var worktopcategorySelect = document.getElementById("worktopcategory");
      var selectedworktopcategory = worktopcategorySelect.options[worktopcategorySelect.selectedIndex].value;
      // console.log(selectedworktopcategory);
      var worktoptypeSelect = document.getElementById("worktoptype");
      var selectedworktoptype = worktoptypeSelect.options[worktoptypeSelect.selectedIndex].value;
      // console.log(selectedworktoptype);

      // worktop description to be inserted in quotation
      var worktopdescription = "Worktop Charges";
      worktopdescription_full = worktopdescription.concat(" (", selectedworktopcategory , " " , selectedworktoptype, ")"); 
      // console.log(worktopdescription_full);

      // Calculate worktop charges
      var worktopUnitMeasurement = parseFloat(document.getElementById("worktopUnitMeasurement").value);
      var worktopUnitPrice = parseFloat(document.getElementById("worktopUnitPrice").value);
      var worktopUnitPricetext = document.getElementById("worktopUnitPrice"); // to update the text displayed for unit price

      // Update unit price according to worktop type 
      if(worktoptypecheck == 1){ // if user select worktop category/type
        if(selectedworktoptype=="40mm S series"){
          worktopUnitPrice = 1146;
          worktopUnitPricetext.value = 1146;
          if($('#surchargerow').length == 1){ // if surcharge row exist update surcharge price
            total_surcharge = 150;
            grandTotal = grandTotal + total_surcharge;
            document.getElementById('surchargeCharges').innerHTML = '<strong>RM' + total_surcharge.toFixed(2) + '</strong>';
            // let description_surcharge = "Surcharge for worktop";
            // let description_surcharge_select = "Quartz";
            // let description_surcharge_type = selectedworktoptype;
            // description_surcharge_full = description_surcharge.concat(" (", description_surcharge_select, " ",description_surcharge_type, ")");
            description_surcharge_full = "Quartz Worktop Less than 1.82 m2 Charges";
            document.getElementById('surchargeDescription').innerHTML = description_surcharge_full;
          }
        }else if(selectedworktoptype=="40mm P series"){
          worktopUnitPrice = 1385;
          worktopUnitPricetext.value = 1385;
          if($('#surchargerow').length == 1){ // if surcharge row exist update surcharge price
            total_surcharge = 150;
            grandTotal = grandTotal + total_surcharge;
            document.getElementById('surchargeCharges').innerHTML = '<strong>RM' + total_surcharge.toFixed(2) + '</strong>';
            // let description_surcharge = "Surcharge for worktop";
            // let description_surcharge_select = "Quartz";
            // let description_surcharge_type = selectedworktoptype;
            // description_surcharge_full = description_surcharge.concat(" (", description_surcharge_select, " ",description_surcharge_type, ")");
            description_surcharge_full = "Quartz Worktop Less than 1.82 m2 Charges";
            document.getElementById('surchargeDescription').innerHTML = description_surcharge_full;
          }
        }else if(selectedworktoptype=="12mm thickness"){
          worktopUnitPrice = 890;
          worktopUnitPricetext.value = 890;
          if($('#surchargerow').length == 1){ // if surcharge row exist update surcharge price
            total_surcharge = 350;
            grandTotal = grandTotal + total_surcharge;
            document.getElementById('surchargeCharges').innerHTML = '<strong>RM' + total_surcharge.toFixed(2) + '</strong>';
            // let description_surcharge = "Surcharge for worktop";
            // let description_surcharge_select = "Compact";
            // let description_surcharge_type = selectedworktoptype;
            // description_surcharge_full = description_surcharge.concat(" (", description_surcharge_select, " ",description_surcharge_type, ")");
            description_surcharge_full = "Compact Worktop Less than 1.50 m2 Charges";
            document.getElementById('surchargeDescription').innerHTML = description_surcharge_full;
          }
        }else if(selectedworktoptype=="38mm thickness"){
          worktopUnitPrice = 1426;
          worktopUnitPricetext.value = 1426;
          if($('#surchargerow').length == 1){ // if surcharge row exist update surcharge price
            total_surcharge = 350;
            grandTotal = grandTotal + total_surcharge;
            document.getElementById('surchargeCharges').innerHTML = '<strong>RM' + total_surcharge.toFixed(2) + '</strong>';
            // let description_surcharge = "Surcharge for worktop";
            // let description_surcharge_select = "Compact";
            // let description_surcharge_type = selectedworktoptype;
            // description_surcharge_full = description_surcharge.concat(" (", description_surcharge_select, " ",description_surcharge_type, ")");
            description_surcharge_full = "Compact Worktop Less than 1.50 m2 Charges";
            document.getElementById('surchargeDescription').innerHTML = description_surcharge_full;
          }
        }
      }else{ // if user manual change the unit price/measurement
        if(selectedworktoptype=="40mm S series" || selectedworktoptype=="40mm P series"){
          // Add another new row if M2 less than 1.82
          if (worktopUnitMeasurement < 1.82 && worktopUnitMeasurement > 0) { 
            if($('#surchargerow').length == 0){// must surcharge row not existed only insert new row
              var row = table.insertRow(table.rows.length - 3);
              row.id = "surchargerow";
              var noCell = row.insertCell(0);
              var moduleCell = row.insertCell(1);
              var descriptionCell = row.insertCell(2);
              var numModulesCell = row.insertCell(3);
              var totalCell = row.insertCell(4);
              totalCell.id = "surchargeCharges";
              descriptionCell.id = "surchargeDescription";

              var module_surcharge = "Surcharge";
              // var description_surcharge = "Surcharge for worktop";
              // var description_surcharge_select = "Quartz";
              // var description_surcharge_type = selectedworktoptype;
              // description_surcharge_full = description_surcharge.concat(" (", description_surcharge_select, " ",description_surcharge_type, ")");
              description_surcharge_full = "Quartz Worktop Less than 1.82 m2 Charges";

              var price_surcharge = 150;
              total_surcharge = 150;

              noCell.innerHTML = table.rows.length - 5;
              moduleCell.innerHTML = module_surcharge;
              descriptionCell.innerHTML = description_surcharge_full;
              numModulesCell.innerHTML = 1;
              totalCell.innerHTML = "<strong>RM" + total_surcharge.toFixed(2) + "</strong>";
              globalsurcharge = total_surcharge;
              grandTotal = grandTotal + total_surcharge;
              }
            else{ // just add the total up 
              // console.log("quartz");
              total_surcharge = 150;
              grandTotal = grandTotal + total_surcharge;
              // let description_surcharge = "Surcharge for worktop";
              // let description_surcharge_select = "Quartz";
              // let description_surcharge_type = selectedworktoptype;
              // description_surcharge_full = description_surcharge.concat(" (", description_surcharge_select, " ",description_surcharge_type, ")");
              description_surcharge_full = "Quartz Worktop Less than 1.82 m2 Charges";
            }
          } else { // remove the surcharge row and deduct grandtotal
            if($('#surchargerow').length == 1){// must surcharge row existed only remove row
              if(worktopUnitMeasurement > 0){
                grandTotal = grandTotal - globalsurcharge;
              }
              globalsurcharge = 0; // reset globalsurcharge to 0
              total_surcharge = 0; // reset totalsurcharge to 0
              // document.getElementById("quotationTable").deleteRow(1);
              var row = document.getElementById("surchargerow");
              row.parentNode.removeChild(row);
            }
          }
        }else if(selectedworktoptype=="12mm thickness" || selectedworktoptype=="38mm thickness"){
            // Add another new row if M2 less than 1.5
            if (worktopUnitMeasurement < 1.5 && worktopUnitMeasurement > 0) { 
              if($('#surchargerow').length == 0){// must surcharge row not existed only insert new row
                var row = table.insertRow(table.rows.length - 3);
                row.id = "surchargerow";
                var noCell = row.insertCell(0);
                var moduleCell = row.insertCell(1);
                var descriptionCell = row.insertCell(2);
                var numModulesCell = row.insertCell(3);
                var totalCell = row.insertCell(4);
                totalCell.id = "surchargeCharges";
                descriptionCell.id = "surchargeDescription";

                var module_surcharge = "Surcharge";
                // var description_surcharge = "Surcharge for worktop";
                // var description_surcharge_select = "Compact";
                // var description_surcharge_type = selectedworktoptype;
                // description_surcharge_full = description_surcharge.concat(" (", description_surcharge_select, " ",description_surcharge_type, ")");
                description_surcharge_full = "Compact Worktop Less than 1.50 m2 Charges";

                var price_surcharge = 350;
                total_surcharge = 350;

                noCell.innerHTML = table.rows.length - 5;
                moduleCell.innerHTML = module_surcharge;
                descriptionCell.innerHTML = description_surcharge_full;
                numModulesCell.innerHTML = 1;
                totalCell.innerHTML = "<strong>RM" + total_surcharge.toFixed(2) + "</strong>";
                globalsurcharge = total_surcharge;
                grandTotal = grandTotal + total_surcharge;
                }
                else { // just add the total up
                  // console.log("Compact");
                  total_surcharge = 350;
                  grandTotal = grandTotal + total_surcharge;
                  // let description_surcharge = "Surcharge for worktop";
                  // let description_surcharge_select = "Compact";
                  // let description_surcharge_type = selectedworktoptype;
                  // description_surcharge_full = description_surcharge.concat(" (", description_surcharge_select, " ",description_surcharge_type, ")");
                  description_surcharge_full = "Compact Worktop Less than 1.50 m2 Charges";
                }
            } else { // remove the surcharge row and deduct grandtotal
              if($('#surchargerow').length == 1){// must surcharge row existed only remove row
                if(worktopUnitMeasurement > 0){
                  grandTotal = grandTotal - globalsurcharge;
                }
                globalsurcharge = 0; // reset globalsurcharge to 0
                total_surcharge = 0; // reset totalsurcharge to 0
                // document.getElementById("quotationTable").deleteRow(1);
                var row = document.getElementById("surchargerow");
                row.parentNode.removeChild(row);
              }
          }
        }
      }
      worktoptypecheck = 0;

      worktopCharges = worktopUnitMeasurement * worktopUnitPrice;
      worktopCharges = Math.ceil(worktopCharges); // round up the worktop charges

      // Calculate transportation charges
      transportationDistance = parseFloat(document.getElementById("transportationDistance").value);
      var transportationUnitPrice = 0; // initialize as 0
      transportationCharges = 0; // initialize as 0
      if(transportationDistance > 0){ // if got distance, only got transportation charges
        transportationUnitPrice = 2.5;
        transportationCharges = ((transportationDistance * transportationUnitPrice) + 100)/0.85;
        transportationCharges = Math.ceil(transportationCharges); // round up the transportation charges
      }else{ // no transportation charges
        transportationUnitPrice = 0;
        transportationCharges = transportationDistance * transportationUnitPrice;
      }

      // Check if worktop/transportation charges is 0
      if(isNaN(worktopCharges)){ // no price no need change status
        worktop_check = false;
        worktopCharges = 0;
      }else{
        worktop_check = true;
      }
      if(isNaN(transportationCharges)){ // no price no need change status
        transportation_check = false;
        transportationCharges = 0;
      }else{
        transportation_check = true;
      }

      // Clear existing table rows
      // while (table.rows.length > 4) {
      //   table.deleteRow(4);
      // }
      if (flag != 3){
        for (var i = 0; i < uidArray.length; i++) {
          var uid = renameSerialNumber(uidArray);
          // var uid = uidArray[i].trim();

          // console.log(uid);
          if (isValidUid(uid)) {
            var numericUid = parseInt(uid);
            if (flag == 0){
              if (moduleCounts[numericUid]) {
                moduleCounts[numericUid]++;
              } else {
                moduleCounts[numericUid] = 1;
              }
            }
            playSound("https://signaturegroup.com.my/scan.mp3"); // Play sound for valid UID
          } else if (uid !== "") {
            if (!errorUids.includes(uid)) {
              errorUids.push(uid);
            }
            playSound("https://signaturegroup.com.my/invalid.mp3"); // Play sound for invalid UID
          }
        }
      } else {
        var numericUid = parseInt(item_id);
        if (moduleCounts[numericUid]) {
          moduleCounts[numericUid]++;
        } else {
          moduleCounts[numericUid] = 1;
        }
        historicaluniqueid.push(item_id);
      }
      // console.log(table.rows.length);
      // var rowIndex = 1;
      var rowIndex = 4;
      var checkifexist = 0; // default 0
      if(historicaluniqueid.includes(uidInput)){
        checkifexist = 1; // exist change to 1
      }

      for (var uid_loop in moduleCounts) {
        var count = moduleCounts[uid_loop];

        if (moduleCounts.hasOwnProperty(uid_loop) && count > 0 && checkifexist == 0) {
          if($('#surchargerow').length == 1){
            var row = table.insertRow(table.rows.length - 5);
          }else{
            var row = table.insertRow(table.rows.length - 4);
          }
          var noCell = row.insertCell(0);
          var moduleCell = row.insertCell(1);
          var descriptionCell = row.insertCell(2);
          var numModulesCell = row.insertCell(3);
          var totalCell = row.insertCell(4);
          
          var module = getModule(uid_loop);
          var description = getDescription(uid_loop);
          var moduleprice = getPrice(uid_loop);
          moduleprice = parseFloat(moduleprice);
          var epprice = getEpPrice(uid_loop);
          epprice = parseFloat(epprice);
          var price = moduleprice + epprice;
          price = Math.ceil(price);
          var installationprice = getInstallationPrice(uid_loop);
          installationprice = parseFloat(installationprice);
          totalinstallationprice += installationprice;
          totalinstallationprice = Math.ceil(totalinstallationprice);
          var total = count * price;

          noCell.innerHTML = table.rows.length - 5;
          moduleCell.innerHTML = module;
          descriptionCell.innerHTML = description;
          numModulesCell.innerHTML = count;
          totalCell.innerHTML = "<strong>RM" + total.toFixed(2) + "</strong>";

          arrayuniqueid.push(uid_loop);
          moduletotal += total;
        }
      }
      
      if(isNaN(moduletotal)){ // no price no need to add
        grandTotal = grandTotal;
      }else{
        grandTotal = grandTotal  + moduletotal + totalinstallationprice;
      }

      if(uidArray[0].length==20 && flag == 0){ // to remove module
        if(historicaluniqueid.includes(uidInput)){
          var index = historicaluniqueid.indexOf(uidInput);
          historicaluniqueid.splice(index,1);
          document.getElementById("quotationTable").deleteRow(index+1);
          uid = renameSerialNumber(uidArray);
          moduleprice = getPrice(uid);
          moduleprice = parseFloat(moduleprice);
          epprice = getEpPrice(uid);
          epprice = parseFloat(epprice);
          price = moduleprice + epprice;
          price = Math.ceil(price);
          installationprice = getInstallationPrice(uid);
          installationprice = parseFloat(installationprice);
          installationprice = Math.ceil(installationprice);
          grandTotal = grandTotal - price;
          moduletotal = moduletotal - price;
          grandTotal = grandTotal - installationprice;
          totalinstallationprice = totalinstallationprice - installationprice;

          // Remove element from arrayuniqueid
          const element = arrayuniqueid.indexOf(uid);
          if (element > -1) { // only splice array when item is found
            arrayuniqueid.splice(element, 1); // 2nd parameter means remove one item only
          }

          // console.log(uid);
          // moduleCounts[numericUid] = 0;
          // console.log(index);
          // calculateQuotation(1);
        }else{
          historicaluniqueid.push(uidInput);
        }
        // console.log(moduleCounts);
        // console.log(historicaluniqueid);
        document.getElementById("uidInput").value = "";
      }

      // if(worktopCharges > 0 && worktop_check == true){
      //       var worktopChargesRow = table.insertRow(table.rows.length++);
      //       var worktopNoCell = worktopChargesRow.insertCell(0);
      //       var worktopModuleCell = worktopChargesRow.insertCell(1);
      //       var worktopDescriptionCell = worktopChargesRow.insertCell(2);
      //       var worktopNumModulesCell = worktopChargesRow.insertCell(3);
      //       var worktopTotalCell = worktopChargesRow.insertCell(4);

      //       worktopNoCell.innerHTML = table.rows.length - 1;
      //       worktopModuleCell.innerHTML = "Worktop";
      //       worktopDescriptionCell.innerHTML = "Worktop Charges"; 
      //       worktopNumModulesCell.innerHTML = 1;
      //       worktopTotalCell.innerHTML = "<strong>RM" + worktopCharges.toFixed(2) + "</strong>";
      //   }

      //   if(transportationCharges > 0 && transportation_check == true){
      //       var transportationChargesRow = table.insertRow(table.rows.length++);
      //       var transportationNoCell = transportationChargesRow.insertCell(0);
      //       var transportationModuleCell = transportationChargesRow.insertCell(1);
      //       var transportationDescriptionCell = transportationChargesRow.insertCell(2);
      //       var transportationNumModulesCell = transportationChargesRow.insertCell(3);
      //       var transportationTotalCell = transportationChargesRow.insertCell(4);

      //       transportationNoCell.innerHTML = table.rows.length - 1;
      //       transportationModuleCell.innerHTML = "Transportation";
      //       transportationDescriptionCell.innerHTML = "Transportation Charges"; 
      //       transportationNumModulesCell.innerHTML = 1;
      //       transportationTotalCell.innerHTML = "<strong>RM" + transportationCharges.toFixed(2) + "</strong>";
      //   }

      // Update the cell values with the new total prices
      // console.log(totalinstallationprice);
      document.getElementById('installationCharges').innerHTML = '<strong>RM' + totalinstallationprice.toFixed(2) + '</strong>';
      // var installationCharges33 = document.getElementById('installationCharges');
      // console.log(installationCharges33);

      // Update the cell values with the new total prices
      document.getElementById('worktopCharges').innerHTML = '<strong>RM' + worktopCharges.toFixed(2) + '</strong>';
      document.getElementById('transportationCharges').innerHTML = '<strong>RM' + transportationCharges.toFixed(2)+ '</strong>';

      // Calculate grand total including worktop and transportation charges
      if(isNaN(worktopCharges)){ // no price no need to add
        grandTotal = grandTotal;
      }else{
        grandTotal = grandTotal  + worktopCharges;
      }
      if(isNaN(transportationCharges)){ // no price no need to add
        grandTotal = grandTotal;
      }else{
        grandTotal = grandTotal  + transportationCharges;
      }

      // Calculate discount charges according to percentage
      discountCharges = 0;
      var discountpercentage = parseFloat(document.getElementById("discountpercentage").value);
      if(discountpercentage > 0){ // if got percentage, only got discount value
        var grandtotalfordiscount= grandTotal; // copy the existing grand total
        grandtotalfordiscount = grandtotalfordiscount - transportationCharges - totalinstallationprice; // Discount exclude transportation & installation
        discountCharges = grandtotalfordiscount * discountpercentage / 100; // calculate the discount value according to discount percentage
        discountCharges = Math.ceil(discountCharges); // round up the discount charges
      }
      // var grandTotalexcludediscount = grandTotal - transportationCharges; // Exclude transportation charges to check for discount charges
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

      // Recalculate grand total
      if(isNaN(discountCharges)){ // no price no need to add
        grandTotal = grandTotal;
      }else{
        grandTotal = grandTotal  - discountCharges;
      }

      // store the arrayuniqueid into a cookie to save into php variable
      // document.cookie = "arrayuniqueid = " + arrayuniqueid; // array of all modules
      // document.cookie = "worktopDescription = " + worktopdescription_full; // description of worktop
      // document.cookie = "worktopCharges = " + worktopCharges; // value of worktop charges
      // // if($('#surchargerow').length == 1){
      //   // console.log(description_surcharge_full);
      //   document.cookie = "surchargeDescription = " + description_surcharge_full; // description of worktop surcharge
      //   document.cookie = "surchargeCharges = " + total_surcharge;// value of worktop surcharge
      // // }
      // document.cookie = "transportationDistance = " + transportationDistance; // value of transportation distance
      // document.cookie = "transportationCharges = " + transportationCharges; // value of transportation charges
      // document.cookie = "discountCharges = " + discountCharges; // value of discount charges
      // document.cookie = "totalinstallationprice = " + totalinstallationprice; // value of installation charges

      // Create an AJAX request
      // var xhr = new XMLHttpRequest();
      // xhr.open("POST", "/kubiq_quotation_process.php");
      // xhr.setRequestHeader("Content-Type", "application/json");
      
      // Update grand total
      var grandTotalCell = document.getElementById("grandTotal");
      grandTotalCell.innerHTML = "<strong>Grand Total: RM" + grandTotal.toFixed(2) + "</strong>";

      // Update error message and clear button
      var errorCell = document.getElementById("errorCell");
      var clearInvalidButton = document.getElementById("clearInvalidButton");
      var generatequotationbutton = document.getElementById("generatequotationbutton");

      // if got more than 1 module only show generate quotation button
      if (historicaluniqueid.length > 0 || digitalezarr.length > 0){
        generatequotationbutton.style.display = "inline-block"; // show the generate quotation button
      }else{
        generatequotationbutton.style.display = "none"; // hide the generate quotation button
      }


      // if (errorUids.length > 0) { // contains error
      //   var errorMessage = "Invalid UID " + errorUids.join(", ") + ", please remove.";
      //   errorCell.innerHTML = '<span style="color: red;">' + errorMessage + '</span>';
      //   clearInvalidButton.style.display = "inline-block"; // show the clear invalid uid button
      //   generatequotationbutton.style.display = "none"; // hide the generate quotation button
      // } else { // no error
      //   errorCell.innerHTML = "";
      //   clearInvalidButton.style.display = "none"; // hide the clear invalid uid button
      //   generatequotationbutton.style.display = "inline-block"; // show the generate quotation button
      // }


      // update the running no
      // document.getElementById('worktoprunningno').innerHTML = table.rows.length - 3;
      // document.getElementById('transportrunningno').innerHTML = table.rows.length - 2;
      // document.getElementById('discountrunningno').innerHTML = table.rows.length - 1;
      // Update row numbers
      const rows = table.querySelectorAll('tr:not(:first-child)');
          rows.forEach((row, index) => {
            row.cells[0].textContent = index + 1;
          });
    }

    function isValidUid(uid) {
      return getModule(uid) !== ""; // Check if UID is a number and exists in the CSV
    }

    function sendData() {
      // to send data to session variable, refer to kubiq_quotation_process.php
      var data = {
          arrayuniqueid: arrayuniqueid,
          worktopDescription: worktopdescription_full,
          worktopCharges: worktopCharges,
          surchargeDescription: description_surcharge_full,
          surchargeCharges: total_surcharge,
          transportationDistance: transportationDistance,
          transportationCharges: transportationCharges,
          discountCharges: discountCharges,
          totalinstallationprice: totalinstallationprice
      };
      console.log(data);
      var xhr = new XMLHttpRequest();

      //👇 set the PHP page you want to send data to
      xhr.open("POST", "kubiq_quotation_process.php", true);
      xhr.setRequestHeader("Content-Type", "application/json");

      //👇 what to do when you receive a response
      xhr.onreadystatechange = function () {
          if (xhr.readyState == XMLHttpRequest.DONE) {
              //alert(xhr.responseText);
          }
      };

      //👇 send the data
      xhr.send(JSON.stringify(data));
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
        // Create element with <a> tag
        const link = document.createElement("a");

        // Create a blog object with the file content which you want to add to the file
        const file = new Blob([JSON.stringify(objarraykjl_data_kjl)], { type: 'application/json' });

        // Add file content in the object URL
        link.href = URL.createObjectURL(file);

        // Add file name
        link.download = "KJL_3D.json";

        // Add click event to <a> tag to save file.
        link.click();
        URL.revokeObjectURL(link.href);
        sendData();
        open_KJL();
        window.open("http://skcrm.com.my/html/index.php?module=leads_cc_create_kubiq");
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

    function open_KJL() {
        $.ajax({ 
            type : 'POST',
            url  : 'digital_ezykit/kubiq_ezykit_process_kjl.php',
            success: function(responseText){
                sleep(500);
                window.open("https://yun.kujiale.com/cloud/tool/h5/bim?designid="+responseText+"&launchMiniapp=3FO4K4VMKV3T&__rd=y&_gr_ds=true", "_openKJL");
            }
        }
        )
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
