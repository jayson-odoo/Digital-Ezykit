<?php
include '../config.php'; // include the config
include "../db.php";
// User must be logged in to access this page
session_start();

// Read ezkit data from database
GetMyConnection();
// For serial number
$sql = 'select * from tblitem_master_ezkit_serialnumber ';
$r = mysql_query($sql);
$nr = mysql_num_rows($r); // Get the number of rows
if ($nr > 0) {
  $arrayserialnumber = array(); // array of serial number
  while ($row = mysql_fetch_assoc($r)) {
    $master_serialnumber = $row['master_serialnumber'];
    $ezkit_id = $row['ezkit_id'];
    $arrayserialnumber[$master_serialnumber] = $ezkit_id; // add the serial number into the array
  }
}

// For module/description/price
$active = "Y"; // only select active modules
$sql_ezkit = 'select * from tblitem_master_ezkit where master_active = "' . $active . '"';
$r_ezkit = mysql_query($sql_ezkit);
$nr_ezkit = mysql_num_rows($r_ezkit); // Get the number of rows
if ($nr_ezkit > 0) {
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
    $arraymodule['Kitchen'][$row['master_type']][$id] = $master_module; // add the module into the array
    $arraydescription['Kitchen'][$row['master_type']][$id] = $master_description; // add the description into the array
    $arrayprice['Kitchen'][$row['master_type']][$id] = $master_price; // add the price into the array
    $arrayepprices['Kitchen'][$row['master_type']][$id] = $master_ep; // add the price into the array
    $arrayinstallationprice['Kitchen'][$row['master_type']][$id] = $master_installation; // add the price into the array
  }
}
// for worktop 
$sql = 'select * from tblitem_master_ezkit_worktop;';
$r = mysql_query($sql);
$nr = mysql_num_rows($r); // Get the number of rows
if ($nr > 0) {
  while ($row = mysql_fetch_assoc($r)) {
    $arraymodule['Kitchen']['Worktop'][$row['id']] = $row['name'];
    $arraydescription['Kitchen']['Worktop'][$row['id']] = $row['description'].' L: '.(int) $row['length'].' mm';
    $arrayprice['Kitchen']['Worktop'][$row['id']] = $row['price'];
    $arrayepprices['Kitchen']['Worktop'][$row['id']] = 0;
    $arrayinstallationprice['Kitchen']['Worktop'][$row['id']] = 0;
  }
}

// for transport 
$sql = 'select * from tblitem_master_ezkit_transportation;';
$r = mysql_query($sql);
$nr = mysql_num_rows($r); // Get the number of rows
if ($nr > 0) {
  while ($row = mysql_fetch_assoc($r)) {
    $transport[] = $row;
  }
}

// for worktop labour 
$sql = 'select * from tblitem_master_ezkit_worktop_labour;';
$r = mysql_query($sql);
$nr = mysql_num_rows($r); // Get the number of rows
if ($nr > 0) {
  while ($row = mysql_fetch_assoc($r)) {
    $worktop_labour[] = $row;
  }
}

// for cap
$sql = 'select * from tblitem_master_ezkit_cap;';
$r = mysql_query($sql);
$nr = mysql_num_rows($r); // Get the number of rows
if ($nr > 0) {
  while ($row = mysql_fetch_assoc($r)) {
    $cap[] = $row;
  }
}

// for door color
$sql = 'select * from tblitem_master_ezkit_door_color;';
$r = mysql_query($sql);
$nr = mysql_num_rows($r); // Get the number of rows
if ($nr > 0) {
  while ($row = mysql_fetch_assoc($r)) {
    $door_color[] = $row;
  }
}

$_SESSION['ezikit'] = "";
CleanUpDB();

if (isset($_GET['ezkit']) && $_GET['ezkit'] == 'true') {
  $discount = $_GET['discount'] ?: 0; // default value
  $transportation = $_GET['transportation'] ?: -1; // default value
  $worktop_labour_sink = $_GET['worktop_labour_sink'] ?: 0; // default value
  $worktop_labour_opening = $_GET['worktop_labour_opening'] ?: 0; // default value
  $infill = $_GET['infill'] ?: 0; // default value
  $plinth = $_GET['plinth'] ?: 0; // default value
  $door_color_get = $_GET['door_color'] ?: 0; // default value
}
?>
<!DOCTYPE html>
<html>

<head>
  <script src="digital_ezykit/scripts/ezykit_share.js"></script>
  <script>
    var errorUids = []; // Declare errorUids globally
    var nfcReader;
    var moduleCounts = [];
    var historicaluniqueid = []; // to store tag number (always 20 digit)
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
    var worktopTransportationCharges = 0;
    var transportationCharges = 0;
    var worktopLabourSinkCharges = 0;
    var worktopLabourOpeningCharges = 0;
    var selectedworktoptype;
    var selectedworktopcategory;
    var worktopUnitPrice;
    var worktopUnitMeasurement;
    var discountpercentage;
    var discountCharges = 0;
    var item_id = "";
    var qty = 0;

    // Assign JS variable to PHP variable
    var arrayserialnumber = '<?php echo json_encode($arrayserialnumber); ?>';
    var arraymodule = '<?php echo json_encode($arraymodule); ?>';
    var arraydescription = '<?php echo json_encode($arraydescription); ?>';
    var arrayprice = '<?php echo json_encode($arrayprice); ?>';
    var arrayepprices = '<?php echo json_encode($arrayepprices); ?>';
    var arrayinstallationprice = '<?php echo json_encode($arrayinstallationprice); ?>';
    var array_cap_list = '<?php echo json_encode($cap); ?>';

    var objarraydigitalezkit = {};
    var objarraykjl_data_kjl = { "items": JSON.parse(localStorage.getItem("items")) }; //get item list from local storage
    objarraykjl_data_kjl.items.forEach((item) => {
      if (objarraydigitalezkit[item.id]) {
        objarraydigitalezkit[item.id]++
      } else {
        objarraydigitalezkit[item.id] = 1;
      }
    })

    const objarrayserialnumber = JSON.parse(arrayserialnumber); // convert to javascript object
    var objarraymodule = JSON.parse(arraymodule); // convert to javascript object
    var objarraydescription = JSON.parse(arraydescription); // convert to javascript object
    var objarrayprice = JSON.parse(arrayprice); // convert to javascript object
    var objarrayepprice = JSON.parse(arrayepprices); // convert to javascript object
    var objarrayinstallationprice = JSON.parse(arrayinstallationprice); // convert to javascript object
    var objinfill = '<?php echo ($infill); ?>';
    objinfill = JSON.parse(objinfill)
    var objplinth = '<?php echo ($plinth); ?>';
    objplinth = JSON.parse(objplinth)
    var digitalezarr = Object.keys(objarraydigitalezkit);
    var objcap_list = JSON.parse(array_cap_list);

    //Based on shape selection in design page calculate price
    var transport_local = '<?php echo $transportation; ?>'
    if (transport_local != -1) {
      $("#transportationDistance").val('<?php echo number_format($transportation,2); ?>');
      if ( <?php echo number_format($transportation,2); ?> > 0 ){
        getprice('<?php echo number_format($transportation,2); ?>', 0);
      }
      document.getElementById("transportationDistance").value = <?php echo $transportation; ?>;
    }

    $("#worktopLabourSinkSelection").val('<?php echo number_format($worktop_labour_sink,2); ?>');
    if ( <?php echo number_format($worktop_labour_sink,2); ?> > 0 ){
      getprice('<?php echo number_format($worktop_labour_sink,2); ?>', 2);
    }

    $("#worktopLabourOpeningSelection").val('<?php echo number_format($worktop_labour_opening,2); ?>');
    if ( <?php echo number_format($worktop_labour_opening,2); ?> > 0 ){
      getprice('<?php echo number_format($worktop_labour_opening,2); ?>', 3);
    }
    document.getElementById("discountpercentage").value = <?php echo $discount; ?>;
    
    $("#doorColorSelection").val('<?php echo $door_color_get; ?>');
    if (digitalezarr.length > 0) {
      for (var key in objarraydigitalezkit) {
        if (objarraydigitalezkit.hasOwnProperty(key)) {
          item_id = key;
          qty = objarraydigitalezkit[key];
        }
      }
      calculateQuotation(3);
    }
    /* 
        Name: generatequotation
        Description: Necessary value checking and generate data parse to lead create kubiq
        Input:
            None
        Output:
            None
    */
    function generatequotation() {
      let transportationDistance = parseFloat(document.getElementById("transportationDistance").value);
      let selectedWorktopLabourSink = document.getElementById("worktopLabourSinkSelection").selectedOptions[0];
      let selectedWorktopLabourOpening = document.getElementById("worktopLabourOpeningSelection").selectedOptions[0];
      let selectedDoorColor = document.getElementById("doorColorSelection").selectedOptions[0];
      let discountpercentage = parseFloat(document.getElementById("discountpercentage").value);
      let l_end_cap = document.getElementById("l_end_cap_qty");
      let c_end_cap = document.getElementById("c_end_cap_qty");
      let corner_cap = document.getElementById("corner_cap_qty");
      console.log(selectedDoorColor)
      if (isNaN(transportationDistance)) {
        alert("Please key in transportation distance");
      } else if (transportationDistance < 0) {
        alert("Please key in positive transportation distance");
      } else if (discountpercentage < 0) {
        alert("Please key in positive discount percentage");
      } else if (discountpercentage > 100) {
        alert("Please key in discount percentage less than 100");
      } else if (selectedDoorColor.value == 0) {
        alert("Please select a door");
      } else { // proceed to generate the quotation
        sendData(); //set data into session
        // prepare parameter to pass to quotation page
        var query_arr = [];
        query_arr['from_digital_ezykit'] = 1;

        // Create a new URLSearchParams object
        const searchParams = new URLSearchParams();

        // Iterate through the object's properties and append them to the URLSearchParams object
        for (const key in query_arr) {
          // If the value is not an array, append it as a single key-value pair
          searchParams.append(key, query_arr[key]);
        }
        // Get the final query string
        const queryString = searchParams.toString();
        // Staging
        // window.open(window.location.origin + "/skcrm/index.php?module=leads_cc_create_kubiq&from_digital_ezykit=1");
        // Live
        window.open(window.location.origin + "/html/index.php?module=leads_cc_create_kubiq&" + queryString);
      }
    }

  </script>
  <style>
    html,
    body {
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

    th,
    td {
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
      width: 50px;
      /* Adjust the width as per your preference */
    }

    #worktopUnitPrice {
      width: 60px;
      /* Adjust the width as per your preference */
    }

    #transportationDistance {
      width: 50px;
      /* Adjust the width as per your preference */
    }

    #infillqty_short,
    #infillqty_long {
      width: 50px;
      /* Adjust the width as per your preference */
    }

    #l_end_cap_qty {
      width: 50px;
      /* Adjust the width as per your preference */
    }

    #c_end_cap_qty {
      width: 50px;
      /* Adjust the width as per your preference */
    }

    #corner_cap_qty {
      width: 50px;
      /* Adjust the width as per your preference */
    }

    #discountpercentage {
      width: 50px;
      /* Adjust the width as per your preference */
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
          <div id="container">
            <div id="quotation">
              <table id="quotationTable">
                <tr>
                  <th>No.</th>
                  <th>Module</th>
                  <th>Description</th>
                  <th>UoM</th>
                  <th>Unit Price (RM)</th>
                  <th>Quantity</th>
                  <th>Total (RM)</th>
                </tr>
                <tr>
                  <td id="worktop_labour_sink">1</td>
                  <td>Worktop Sink Labour</td>
                  <td><select id="worktopLabourSinkSelection" name="worktopLabourSinkSelection" class="form-control" onchange="getprice(this.value, 2);">
                      <option value="0">--Please select an option--</option>
                      <?php
                      foreach ($worktop_labour as $key => $value) {
                        if (strpos($value['description'], "Sink") !== false ) {
                          echo '<option wldescription="' .$value['description']. '" wlitemcode="' .$value['item_code']. '" value="' . $value['price'] . '">' . $value['description'] . '</option>';
                        }
                      }
                      ?>
                    </select></td>
                  <td>Unit</td>
                  <td>-</td>
                  <td>1</td>
                  <td id="worktopLabourSinkCharges"><strong>RM0.00</strong></td>
                </tr>
                <tr>
                  <td id="worktop_labour_opening">2</td>
                  <td>Worktop Opening Labour</td>
                  <td><select id="worktopLabourOpeningSelection" name="worktopLabourOpeningSelection" class="form-control" onchange="getprice(this.value, 3);">
                      <option value="0">--Please select an option--</option>
                      <?php
                      foreach ($worktop_labour as $key => $value) {
                        if (strpos($value['description'], "Opening") !== false ) {
                          echo '<option wldescription="' .$value['description']. '" wlitemcode="' .$value['item_code']. '" value="' . $value['price'] . '">' . $value['description'] . '</option>';
                        }
                      }
                      ?>
                    </select></td>
                  <td>Unit</td>
                  <td>-</td>
                  <td>1</td>
                  <td id="worktopLabourOpeningCharges"><strong>RM0.00</strong></td>
                </tr>
                <tr>
                  <td id="doorcolorrunningno">3</td>
                  <td>Door Color</td>
                  <td><select id="doorColorSelection" name="doorColorSelection" class="form-control">
                      <option value="0">--Please select an option--</option>
                      <?php
                      foreach ($door_color as $key => $value) {
                        echo '<option dcitemcode=' .urlencode($value['item_code']). '" value="' . $value['name'] . '">' . $value['name'] . '</option>';
                      }
                      ?>
                    </select></td>
                  <td>-</td>
                  <td>-</td>
                  <td>-</td>
                  <td id="doorColorCharges"><strong>RM0.00</strong></td>
                </tr>
                <tr>
                  <td id="transportrunningno">4</td>
                  <td>Transportation</td>
                  <td>Transportation</td>
                  <td>km</td>
                  <td>-</td>
                  <td><input type="number" min="0" id="transportationDistance" name="transportationDistance" value=""
                      oninput="calculateQuotation(4)"></td>
                  <td id="transportationCharges"><strong>RM0.00</strong></td>
                </tr>
                
                <tr>
                  <td id="discountrunningno">5</td>
                  <td>Discount</td>
                  <td>Discount</td>
                  <td>%</td>
                  <td>-</td>
                  <td><input type="number" min="0" id="discountpercentage" name="discountpercentage" value="0"
                      oninput="calculateQuotation(4)" min="0" max="100"></td>
                  <td id="discountCharges"><strong>-RM0.00</strong></td>
                </tr>
                <tr>
                  <td id="installationrunningno">6</td>
                  <td>Installation</td>
                  <td>Installation</td>
                  <td>-</td>
                  <td>-</td>
                  <td>1</td>
                  <td id="installationCharges"><strong>RM0.00</strong></td>
                </tr>
              </table>
              <div id="grandTotal">
                <strong>Grand Total: RM0.00</strong>
              </div>
              <div id="disclaimer">
                Disclaimer: Discount applied excluding transportation & installation charges
              </div>
              <br>
              <textarea id="uidInput" class="uidInputTextArea" placeholder="Enter UID" oninput="calculateQuotation(0)"
                style="z-index: -2; position: absolute;"></textarea>
              <div id="generatequotation">
                <button id="generatequotationbutton" onclick="generatequotation()">Submit</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    /* 
        Name: mouseOut
        Description: Stop scanning when mouse have other action
        Input:
            None
        Output:
            None
    */
    function mouseOut() {
      if (document.getElementById("overlapbutton").classList.contains('btn btn-success')) {
        document.getElementById("uidInput").focus();
      }
      checkfocus = 0;
      const uidInputlistener = document.getElementById("uidInput");
      uidInputlistener.addEventListener("focusout", (event) => {
        if (checkfocus == 0) {
          const overlapbutton = document.getElementById("overlapbutton");
          overlapbutton.className = "btn btn-danger";
          var stopScanMessage = document.getElementById("stopScanMessage");
          stopScanMessage.style.display = "none";
        }
      });
    }

    /* 
        Name: startNfcReader
        Description: Start NFC Reader
        Input:
            None
        Output:
            None
    */
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

    /* 
        Name: handleNfcReading
        Description: On scan, action for scanning return sound list
        Input:
            1. event : ScanEvent
        Output:
            None
    */
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

    /* 
        Name: isUidInList
        Description: Check if nfc read is correct
        Input:
            1. uid : available values - ['1','2','3', ...]
        Output:
            None
    */
    function isUidInList(uid) {
      var uidInput = document.getElementById("uidInput");
      var uidArray = uidInput.value.split("\n");

      return uidArray.includes(uid);
    }
    /* 
        Name: removeUidFromList
        Description: Remove uid from UID input list
        Input:
            1. uid : available values - ['1','2','3', ...]
        Output:
            None
    */
    function removeUidFromList(uid) {
      var uidInput = document.getElementById("uidInput");
      var uidArray = uidInput.value.split("\n");
      var index = uidArray.indexOf(uid);

      if (index !== -1) {
        uidArray.splice(index, 1);
        uidInput.value = uidArray.join("\n");
        calculateQuotation(4);
      }
    }

    /* 
        Name: addSerialNumber
        Description: Add serial number into UID list and calculate price
        Input:
            1. serialNumber : available values - ['04:6f:cf:aa:db:36:80','04:06:fc:9a:06:73:81','04:6f:f6:aa:db:36:80', ...]
        Output:
            None
    */
    function addSerialNumber(serialNumber) {
      var uidInput = document.getElementById("uidInput");
      uidInput.value += serialNumber + '\n';
      calculateQuotation(4);
    }

    /* 
        Name: addSerialNumber
        Description: Add serial number into UID list and calculate price
        Input:
            1. soundUrl : available values - ['https://signaturegroup.com.my/unscan.mp3']
        Output:
            None
    */
    function playSound(soundUrl) {
      var audio = new Audio(soundUrl);
      audio.play();
    }

    /* 
        Name: enterFullscreen
        Description: Allow page to become full screen
        Input:
            None
        Output:
            None
    */
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

    /* 
        Name: exitFullscreen
        Description: Exit from full screen
        Input:
            None
        Output:
            None
    */
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

    /* 
        Name: toggleFullscreen
        Description: Action to exit or enter full screen
        Input:
            None
        Output:
            None
    */
    function toggleFullscreen() {
      if (isFullscreen()) {
        exitFullscreen();
      } else {
        enterFullscreen();
      }
    }

    /* 
        Name: isFullscreen
        Description: Check if current status is full screen
        Input:
            None
        Output:
            true || false
    */
    function isFullscreen() {
      return (
        document.fullscreenElement ||
        document.mozFullScreenElement ||
        document.webkitFullscreenElement ||
        document.msFullscreenElement
      );
    }

    document.addEventListener("DOMContentLoaded", function (event) {
      startNfcReader(); // Automatically start the NFC reader
    });

    function getprice(val, charges) {
      if (charges == 0) {
        $("#transportationCharges").html("<strong>RM" + val + "</strong>");
      } else if (charges == 2) {
        $("#worktopLabourSinkCharges").html("<strong>RM"+val+"</strong>");
      } else if (charges == 3) {
        $("#worktopLabourOpeningCharges").html("<strong>RM"+val+"</strong>");
      }

      calculateQuotation(4);
    }
  </script>
</body>

</html>
<?php //include('footer.php'); ?>