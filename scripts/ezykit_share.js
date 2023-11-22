import { shapes, objarraymodule, objarraydescription, objarrayprice, objarrayepprice, objarrayinstallationprice } from './kubiq_ezykit_design.js'
var grandTotal = 0;
/* 
  Name: calculateQuotation
  Description: Reformat data into kjl format
  Input:
      1. flag: available values - ['1','2','3','4']
  Output:
      grandTotal - total price of all shape selected

*/
export function calculateQuotation(flag) {
  grandTotal = 0;
  
  var moduleCounts = [];
  var moduletotal = 0;
  var totalinstallationprice = 0;
  var total_surcharge = 0;
  var worktopdescription_full = "";
  var description_surcharge_full = "Quartz Worktop Less than " +  surcharge_measurement_max + " m2 Charges";
  var transportationDistance = 0;
  var worktoptypecheck = 0;
  var table = document.getElementById("quotationTable");

  if (flag == 3) {
    // Get worktop details from selection
    var worktopcategorySelect = document.getElementById("worktopcategory");
    var selectedworktopcategory = worktopcategorySelect.options[worktopcategorySelect.selectedIndex].value;
    var worktoptypeSelect = document.getElementById("worktoptype");
    var selectedworktoptype = worktoptypeSelect.options[worktoptypeSelect.selectedIndex].value;
  } else {
    // Get worktop details from element
    var selectedworktopcategory = document.getElementById("worktopcategory").value;
    var selectedworktoptype = document.getElementById("worktoptype").value;
  }
  var surcharge_measurement_max = selectedworktopcategory == "Quartz" ? 1.82 : 1.5
  // worktop description to be inserted in quotation
  var worktopdescription = "Worktop Charges";
  worktopdescription_full = worktopdescription.concat(" (", selectedworktopcategory, " ", selectedworktoptype, ")");

  // Calculate worktop charges
  var worktopUnitMeasurement = parseFloat(document.getElementById("worktopUnitMeasurement").value);
  var worktopUnitPricetext = document.getElementById("worktopUnitPrice"); // to update the text displayed for unit price

  // Update unit price according to worktop type
  
  if (worktopUnitMeasurement < surcharge_measurement_max && worktopUnitMeasurement > 0) {
    total_surcharge = selectedworktopcategory == "Quartz" ? 150 : 350;
    grandTotal = grandTotal + total_surcharge;

    // update surcharge ui
    if ($('#surchargerow').length == 0) {// must surcharge row not existed only insert new row
      if (table) {
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
        noCell.innerHTML = table.rows.length - 5;
        moduleCell.innerHTML = module_surcharge;
        descriptionCell.innerHTML = description_surcharge_full;
        numModulesCell.innerHTML = 1;
        totalCell.innerHTML = "<strong>RM" + total_surcharge.toFixed(2) + "</strong>";
      }
    }
  } else { // remove the surcharge row
    if ($('#surchargerow').length == 1) {// must surcharge row existed only remove row
      var row = document.getElementById("surchargerow");
      row.parentNode.removeChild(row);
    }
  }

  // only hard set unit price when user did not set
  if (flag != 2) {
    if (selectedworktoptype == "40mm S series") {
        worktopUnitPrice = 1146;
    } else if (selectedworktoptype == "40mm P series") {
      worktopUnitPrice = 1385;
    }  else if (selectedworktoptype == "12mm thickness") {
      worktopUnitPrice = 890;
    }  else if (selectedworktoptype == "38mm thickness") {
      worktopUnitPrice = 1426;
    }
    worktopUnitPricetext.value = worktopUnitPrice;  
  } else {
    worktopUnitPrice = parseFloat(worktopUnitPricetext.value);
  }
  var worktopCharges = worktopUnitMeasurement * worktopUnitPrice;
  worktopCharges = Math.ceil(worktopCharges); // round up the worktop charges
  
  // Calculate transportation charges
  transportationDistance = parseFloat(document.getElementById("transportationDistance").value);
  var transportationUnitPrice = 0; // initialize as 0
  var transportationCharges = 0; // initialize as 0
  if (transportationDistance > 0) { // if got distance, only got transportation charges
    transportationUnitPrice = 2.5;
    transportationCharges = ((transportationDistance * transportationUnitPrice) + 100) / 0.85;
    transportationCharges = Math.ceil(transportationCharges); // round up the transportation charges
  } else { // no transportation charges
    transportationUnitPrice = 0;
    transportationCharges = transportationDistance * transportationUnitPrice;
  }

  var local_shapes;
  if (typeof shapes != "undefined") {
    local_shapes = shapes;
  } else {
    local_shapes = JSON.parse(localStorage.getItem("items"))
  }
  local_shapes.forEach((shape) => {
    var numericUid = parseInt(shape.id);
    if (moduleCounts[numericUid]) {
      moduleCounts[numericUid] += 1;
    } else {
      moduleCounts[numericUid] = 1;
    }
  })

  
  for (var uid_loop in moduleCounts) {
    var count = moduleCounts[uid_loop];
    if (moduleCounts.hasOwnProperty(uid_loop) && count > 0) {
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

      totalinstallationprice = totalinstallationprice * count;
      var total = count * price;
      if (flag == 3) {
        if ($('#surchargerow').length == 1) {
          var row = table.insertRow(table.rows.length - 5);
        } else {
          var row = table.insertRow(table.rows.length - 4);
        }
        var noCell = row.insertCell(0);
        var moduleCell = row.insertCell(1);
        var descriptionCell = row.insertCell(2);
        var numModulesCell = row.insertCell(3);
        var totalCell = row.insertCell(4);
  
        noCell.innerHTML = table.rows.length - 5;
        moduleCell.innerHTML = module;
        descriptionCell.innerHTML = description;
        numModulesCell.innerHTML = count;
        totalCell.innerHTML = "<strong>RM" + total.toFixed(2) + "</strong>";
  
      }
      moduletotal += total;
    }
  }

  // Update frontend
  if (document.getElementById('installationCharges')) {
    document.getElementById('installationCharges').innerHTML = '<strong>RM' + totalinstallationprice.toFixed(2) + '</strong>';
  }

  if (document.getElementById('worktopCharges')) {
    document.getElementById('worktopCharges').innerHTML = '<strong>RM' + worktopCharges.toFixed(2) + '</strong>';
  }
  if (document.getElementById('transportationCharges')) {
    document.getElementById('transportationCharges').innerHTML = '<strong>RM' + transportationCharges.toFixed(2) + '</strong>';
  }

  // Recalculate grand total
  if (isNaN(moduletotal)) { // no price no need to add
    grandTotal = grandTotal;
  } else {
    grandTotal += moduletotal + totalinstallationprice;
  }

  if (isNaN(worktopCharges)) { // no price no need to add
    grandTotal = grandTotal;
  } else {
    grandTotal += worktopCharges;
  }
  
  if (isNaN(transportationCharges)) { // no price no need to add
    grandTotal = grandTotal;
  } else {
    grandTotal += transportationCharges;
  }

  // Calculate discount charges according to percentage
  var discountCharges = 0;
  discountpercentage = parseFloat(document.getElementById("discountpercentage").value);

  if (discountpercentage > 0) { // if got percentage, only got discount value
    var grandtotalfordiscount = grandTotal; // copy the existing grand total
    grandtotalfordiscount = grandtotalfordiscount - transportationCharges - totalinstallationprice; // Discount exclude transportation & installation
    discountCharges = grandtotalfordiscount * discountpercentage / 100; // calculate the discount value according to discount percentage
    discountCharges = Math.ceil(discountCharges); // round up the discount charges
  }

  if (document.getElementById('discountCharges')) {
    document.getElementById('discountCharges').innerHTML = '<strong>-RM' + discountCharges.toFixed(2) + '</strong>';
  }

  if (isNaN(discountCharges)) { // no price no need to add
    grandTotal = grandTotal;
  } else {
    grandTotal -= discountCharges;
  }

  // Update grand total
  var quotation_price = grandTotal;
  var grandTotalCell = document.getElementById("grandTotal");
  if (grandTotalCell) {
    grandTotalCell.innerHTML = "<strong>Grand Total: RM" + grandTotal.toFixed(2) + "</strong>";
  }
  updateParentTotalPrice(parseFloat(grandTotal, 2));
  // Update error message and clear button
  var generatequotationbutton = document.getElementById("generatequotationbutton");

  // if got more than 1 module only show generate quotation button
  if (generatequotationbutton) {
    if (digitalezarr.length > 0) {
      generatequotationbutton.style.display = "inline-block"; // show the generate quotation button
    } else {
      generatequotationbutton.style.display = "none"; // hide the generate quotation button
    }
  }

  // Update row numbers
  if (table) {
    const rows = table.querySelectorAll('tr:not(:first-child)');
    rows.forEach((row, index) => {
      row.cells[0].textContent = index + 1;
    });
  }
  
  return grandTotal;
}

/* 
  Name: isValidUid
  Description: Check validity of uid entered
  Input:
      1. uid: available values - ['1','2','3', ...]
  Output:
      true || false
*/
function isValidUid(uid) {
  return getModule(uid) !== ""; // Check if UID is a number and exists in the CSV
}

/* 
  Name: getModule
  Description: Get module name of uid entered
  Input:
      1. uid: available values - ['1','2','3', ...]
  Output:
      ['QV4567','QLS9067','QL9067', ...]
*/
function getModule(uid) {
  var modules = objarraymodule;

  return modules[uid] || "";
}

/* 
  Name: getDescription
  Description: Get module description of uid entered
  Input:
      1. uid: available values - ['1','2','3', ...]
  Output:
      ['450 x 700 x 560mm','900 x 700 x 560mm (SINK)','900 x 700 x 560mm', ...]
*/
function getDescription(uid) {
  var descriptions = objarraydescription;

  return descriptions[uid] || "";
}
/* 
  Name: getPrice
  Description: Get module price of uid entered
  Input:
      1. uid: available values - ['1','2','3', ...]
  Output:
      ['337','475','535', ...]
*/
function getPrice(uid) {
  var prices = objarrayprice;

  return prices[uid] || 0;
}
/* 
  Name: getEpPrice
  Description: Get module ep price of uid entered
  Input:
      1. uid: available values - ['1','2','3', ...]
  Output:
      ['337','475','535', ...]
*/
function getEpPrice(uid) {
  var epprices = objarrayepprice;

  return epprices[uid] || 0;
}

/* 
  Name: getInstallationPrice
  Description: Get module installation price of uid entered
  Input:
      1. uid: available values - ['1','2','3', ...]
  Output:
      ['337','475','535', ...]
*/
function getInstallationPrice(uid) {
  var installationprices = objarrayinstallationprice;

  return installationprices[uid] || 0;
}

/* 
  Name: updateParentTotalPrice
  Description: Update Total Price to the main price of the file
  Input:
      1. price: available values - ['price']
  Output:
      None
*/
function updateParentTotalPrice(price) {
  // Access the parent document from within the iframe
  var parentDocument = parent.document;

  // Access an element in the parent document
  var parentElement = parentDocument.getElementById("total_price");

  // Manipulate the element in the parent document
  parentElement.value = price;
}

/* 
  Name: getTotalPriceFromParent
  Description: Get Total Price to the main price of the file
  Input:
      None
  Output:
      parentInputValue
*/
function getTotalPriceFromParent() {
  // Access the parent document from within the iframe
  var parentDocument = parent.document;

  // Access the value of the input element in the parent document
  var parentInputValue = parentDocument.getElementById("total_price").value;

  // Log or use the value as needed
  return parentInputValue;
}

export { updateParentTotalPrice }