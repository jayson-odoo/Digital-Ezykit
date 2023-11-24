var quotation_price = 0;
var column_counter = 0;
/* 
  Name: calculateQuotation
  Description: Reformat data into kjl format
  Input:
      1. flag: available values - ['1','2','3','4']
  Output:
      grandTotal - total price of all shape selected

*/
function calculateQuotation(flag) {
  if (document.getElementById("uidInput")) {
    var uidInput = document.getElementById("uidInput").value;
    var uidArray = uidInput.split("\n");
  }
  if (document.getElementById("quotationTable")) {
    var table = document.getElementById("quotationTable");
  }
  moduleCounts = [];
  errorUids = []; // Reset errorUids
  var grandTotal = 0;
  moduletotal = 0;
  totalinstallationprice = 0;
  total_surcharge = 0;
  worktopdescription_full = "";
  description_surcharge_full = "";
  transportationDistance = 0;

  if (flag != 4) {
    // Check for worktop category and type
    var worktopcategorySelect = document.getElementById("worktopcategory");
    selectedworktopcategory = worktopcategorySelect.options[worktopcategorySelect.selectedIndex].value;
    var worktoptypeSelect = document.getElementById("worktoptype");
    selectedworktoptype = worktoptypeSelect.options[worktoptypeSelect.selectedIndex].value;
  } else {
    selectedworktopcategory = document.getElementById("worktopcategory").value;
    selectedworktoptype = document.getElementById("worktoptype").value;
    if (selectedworktoptype !== undefined)
      worktoptypecheck = 0;
  }

  // worktop description to be inserted in quotation
  var worktopdescription = "Worktop Charges";
  worktopdescription_full = worktopdescription.concat(" (", selectedworktopcategory, " ", selectedworktoptype, ")");

  // Calculate worktop charges
  worktopUnitMeasurement = parseFloat(document.getElementById("worktopUnitMeasurement").value);
  worktopUnitPrice = parseFloat(document.getElementById("worktopUnitPrice").value);
  var worktopUnitPricetext = document.getElementById("worktopUnitPrice"); // to update the text displayed for unit price

  // Update unit price according to worktop type 
  if (worktoptypecheck == 1) { // if user select worktop category/type
    if (worktopUnitMeasurement < 1.82 && worktopUnitMeasurement > 0) {
      total_surcharge = selectedworktoptype == "40mm S series" || selectedworktoptype == "40mm P series" ? 150 : 350;
      grandTotal = grandTotal + total_surcharge;
    }
    if (selectedworktoptype == "40mm S series") {
      worktopUnitPrice = 1146;
      worktopUnitPricetext.value = 1146;
      if ($('#surchargerow').length == 1) { // if surcharge row exist update surcharge price
        document.getElementById('surchargeCharges').innerHTML = '<strong>RM' + total_surcharge.toFixed(2) + '</strong>';
        description_surcharge_full = "Quartz Worktop Less than 1.82 m2 Charges";
        document.getElementById('surchargeDescription').innerHTML = description_surcharge_full;
      }
    } else if (selectedworktoptype == "40mm P series") {
      worktopUnitPrice = 1385;
      worktopUnitPricetext.value = 1385;
      if ($('#surchargerow').length == 1) { // if surcharge row exist update surcharge price
        document.getElementById('surchargeCharges').innerHTML = '<strong>RM' + total_surcharge.toFixed(2) + '</strong>';
        description_surcharge_full = "Quartz Worktop Less than 1.82 m2 Charges";
        document.getElementById('surchargeDescription').innerHTML = description_surcharge_full;
      }
    } else if (selectedworktoptype == "12mm thickness") {
      worktopUnitPrice = 890;
      worktopUnitPricetext.value = 890;
      if ($('#surchargerow').length == 1) { // if surcharge row exist update surcharge price
        document.getElementById('surchargeCharges').innerHTML = '<strong>RM' + total_surcharge.toFixed(2) + '</strong>';
        description_surcharge_full = "Compact Worktop Less than 1.50 m2 Charges";
        document.getElementById('surchargeDescription').innerHTML = description_surcharge_full;
      }
    } else if (selectedworktoptype == "38mm thickness") {
      worktopUnitPrice = 1426;
      worktopUnitPricetext.value = 1426;
      if ($('#surchargerow').length == 1) { // if surcharge row exist update surcharge price
        document.getElementById('surchargeCharges').innerHTML = '<strong>RM' + total_surcharge.toFixed(2) + '</strong>';
        description_surcharge_full = "Compact Worktop Less than 1.50 m2 Charges";
        document.getElementById('surchargeDescription').innerHTML = description_surcharge_full;
      }
    }
  } else { // if user manual change the unit price/measurement
    if (selectedworktoptype == "40mm S series" || selectedworktoptype == "40mm P series") {
      // Add another new row if M2 less than 1.82
      if (worktopUnitMeasurement < 1.82 && worktopUnitMeasurement > 0) {
        total_surcharge = selectedworktoptype == "40mm S series" || selectedworktoptype == "40mm P series" ? 150 : 350;
        grandTotal = grandTotal + total_surcharge;
        if ($('#surchargerow').length == 0) {// must surcharge row not existed only insert new row
          if (typeof table != "undefined") {
            var row = table.insertRow(table.rows.length - 3);
            row.id = "surchargerow";
            column_counter = 0;
            var noCell = row.insertCell(column_counter);
            var moduleCell = row.insertCell(++column_counter);
            var descriptionCell = row.insertCell(++column_counter);
            var uomCell = row.insertCell(++column_counter);
            var unitPriceCell = row.insertCell(++column_counter);
            var numModulesCell = row.insertCell(++column_counter);
            var totalCell = row.insertCell(++column_counter);
            totalCell.id = "surchargeCharges";
            descriptionCell.id = "surchargeDescription";

            var module_surcharge = "Surcharge";
            description_surcharge_full = "Quartz Worktop Less than 1.82 m2 Charges";

            noCell.innerHTML = table.rows.length - 5;
            moduleCell.innerHTML = module_surcharge;
            descriptionCell.innerHTML = description_surcharge_full;
            uomCell.innerHTML = 'Unit';
            unitPriceCell.innerHTML = parseFloat(total_surcharge).toFixed(2);
            numModulesCell.innerHTML = 1;
            totalCell.innerHTML = "<strong>RM" + total_surcharge.toFixed(2) + "</strong>";
          }
          globalsurcharge = total_surcharge;
        }
        else { // just add the total up 
          description_surcharge_full = "Quartz Worktop Less than 1.82 m2 Charges";
        }
      } else { // remove the surcharge row and deduct grandtotal
        if ($('#surchargerow').length == 1) {// must surcharge row existed only remove row
          if (worktopUnitMeasurement > 0) {
            grandTotal = grandTotal - globalsurcharge;
          }
          globalsurcharge = 0; // reset globalsurcharge to 0
          total_surcharge = 0; // reset totalsurcharge to 0
          var row = document.getElementById("surchargerow");
          row.parentNode.removeChild(row);
        }
      }
    } else if (selectedworktoptype == "12mm thickness" || selectedworktoptype == "38mm thickness") {
      // Add another new row if M2 less than 1.5
      if (worktopUnitMeasurement < 1.5 && worktopUnitMeasurement > 0) {
        if ($('#surchargerow').length == 0) {// must surcharge row not existed only insert new row
          var row = table.insertRow(table.rows.length - 3);
          row.id = "surchargerow";
          column_counter = 0
          var noCell = row.insertCell(column_counter);
          var moduleCell = row.insertCell(++column_counter);
          var descriptionCell = row.insertCell(++column_counter);
          var uomCell = row.insertCell(++column_counter);
          var unitPriceCell = row.insertCell(++column_counter);
          var numModulesCell = row.insertCell(++column_counter);
          var totalCell = row.insertCell(++column_counter);
          totalCell.id = "surchargeCharges";
          descriptionCell.id = "surchargeDescription";

          var module_surcharge = "Surcharge";
          description_surcharge_full = "Compact Worktop Less than 1.50 m2 Charges";

          var price_surcharge = 350;
          total_surcharge = 350;

          noCell.innerHTML = table.rows.length - 5;
          moduleCell.innerHTML = module_surcharge;
          descriptionCell.innerHTML = description_surcharge_full;
          uomCell.innerHTML = "Pcs";
          unitPriceCell.innerHTML = parseFloat(worktopUnitPrice).toFixed(2);
          numModulesCell.innerHTML = 1;
          totalCell.innerHTML = "<strong>RM" + total_surcharge.toFixed(2) + "</strong>";
          globalsurcharge = total_surcharge;
          grandTotal = grandTotal + total_surcharge;
        }
        else { // just add the total up
          total_surcharge = 350;
          grandTotal = grandTotal + total_surcharge;
          description_surcharge_full = "Compact Worktop Less than 1.50 m2 Charges";
        }
      } else { // remove the surcharge row and deduct grandtotal
        if ($('#surchargerow').length == 1) {// must surcharge row existed only remove row
          if (worktopUnitMeasurement > 0) {
            grandTotal = grandTotal - globalsurcharge;
          }
          globalsurcharge = 0; // reset globalsurcharge to 0
          total_surcharge = 0; // reset totalsurcharge to 0
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
  if (transportationDistance > 0) { // if got distance, only got transportation charges
    transportationUnitPrice = 2.5;
    transportationCharges = ((transportationDistance * transportationUnitPrice) + 100) / 0.85;
    transportationCharges = Math.ceil(transportationCharges); // round up the transportation charges
  } else { // no transportation charges
    transportationUnitPrice = 0;
    transportationCharges = transportationDistance * transportationUnitPrice;
  }

  // Check if worktop/transportation charges is 0
  if (isNaN(worktopCharges)) { // no price no need change status
    worktop_check = false;
    worktopCharges = 0;
  } else {
    worktop_check = true;
  }
  if (isNaN(transportationCharges)) { // no price no need change status
    transportation_check = false;
    transportationCharges = 0;
  } else {
    transportation_check = true;
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
    ;
    historicaluniqueid.push(item_id);
  })

  var rowIndex = 4;
  var checkifexist = 0; // default 0
  if (historicaluniqueid.includes(uidInput)) {
    checkifexist = 1; // exist change to 1
  }
  var test = 0;
  for (var uid_loop in moduleCounts) {
    test++;
    var count = moduleCounts[uid_loop];
    if (moduleCounts.hasOwnProperty(uid_loop) && count > 0 && checkifexist == 0) {
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

      if (flag != 4) {
        if ($('#surchargerow').length == 1) {
          var row = table.insertRow(table.rows.length - 5);
        } else {
          var row = table.insertRow(table.rows.length - 4);
        }
        column_counter = 0
        var noCell = row.insertCell(column_counter);
        var moduleCell = row.insertCell(++column_counter);
        var descriptionCell = row.insertCell(++column_counter);
        var uomCell = row.insertCell(++column_counter);
        var unitPriceCell = row.insertCell(++column_counter);
        var numModulesCell = row.insertCell(++column_counter);
        var totalCell = row.insertCell(++column_counter);

        noCell.innerHTML = table.rows.length - 5;
        
        moduleCell.innerHTML = module;
        descriptionCell.innerHTML = description;
        uomCell.innerHTML = "Unit";
        unitPriceCell.innerHTML = parseFloat(price).toFixed(2);
        numModulesCell.innerHTML = count;
        totalCell.innerHTML = "<strong>RM" + total.toFixed(2) + "</strong>";
      }

      arrayuniqueid.push(uid_loop);
      moduletotal += total;
    }
  }
  if (isNaN(moduletotal)) { // no price no need to add
    grandTotal = grandTotal;
  } else {
    grandTotal = grandTotal + moduletotal + totalinstallationprice;
  }
  if (flag != 4) {
    if (uidArray[0].length == 20 && flag == 0) { // to remove module
      if (historicaluniqueid.includes(uidInput)) {
        var index = historicaluniqueid.indexOf(uidInput);
        historicaluniqueid.splice(index, 1);
        document.getElementById("quotationTable").deleteRow(index + 1);
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
      } else {
        historicaluniqueid.push(uidInput);
      }
      document.getElementById("uidInput").value = "";
    }
  }

  // infill
  // calculate infill
  if (typeof objinfill != "undefined" && flag != 4) {
  Object.keys(objinfill).forEach((infill_type) => {
    const infill = objinfill[infill_type]
    if (infill.qty > 0) {
      if ($('#surchargerow').length == 1) {
        var row = table.insertRow(table.rows.length - 5);
      } else {
        var row = table.insertRow(table.rows.length - 4);
      }
      column_counter = 0;
      var noCell = row.insertCell(column_counter);
      var moduleCell = row.insertCell(++column_counter);
      var descriptionCell = row.insertCell(++column_counter);
      var uomCell = row.insertCell(++column_counter);
      var unitPriceCell = row.insertCell(++column_counter);
      var numModulesCell = row.insertCell(++column_counter);
      var totalCell = row.insertCell(++column_counter);
      var infill_total_string = Math.ceil(parseFloat(infill.unit_price)*infill.qty).toFixed(2);
      var infill_total = parseFloat(infill_total_string)
      noCell.innerHTML = table.rows.length - 5;
      moduleCell.innerHTML = infill.name;
      descriptionCell.innerHTML = infill.description;
      uomCell.innerHTML = "Pcs";
      unitPriceCell.innerHTML = parseFloat(infill.unit_price).toFixed(2);
      numModulesCell.innerHTML = infill.qty;
      
      totalCell.innerHTML = "<strong>RM" + infill_total_string + "</strong>";
      grandTotal = grandTotal + infill_total;
    }
    })
  }

  // plinth
  // calculate plinth
  if (typeof objplinth != "undefined" && flag != 4) {
    Object.keys(objplinth).forEach((plinth_type) => {
      const plinth = objplinth[plinth_type]
      if (plinth.length > 0) {
        if ($('#surchargerow').length == 1) {
          var row = table.insertRow(table.rows.length - 5);
        } else {
          var row = table.insertRow(table.rows.length - 4);
        }
        column_counter = 0;
        var noCell = row.insertCell(column_counter);
        var moduleCell = row.insertCell(++column_counter);
        var descriptionCell = row.insertCell(++column_counter);
        var uomCell = row.insertCell(++column_counter);
        var unitPriceCell = row.insertCell(++column_counter);
        var numModulesCell = row.insertCell(++column_counter);
        var totalCell = row.insertCell(++column_counter);
        var plinth_total_string = Math.ceil(parseFloat(plinth.unit_price)*plinth.length).toFixed(2);
        var plinth_total = parseFloat(plinth_total_string)
        noCell.innerHTML = table.rows.length - 5;
        moduleCell.innerHTML = plinth.name;
        uomCell.innerHTML = plinth.uom == "Meter Run" ? "MR" : "Pcs";
        unitPriceCell.innerHTML = parseFloat(plinth.unit_price).toFixed(2);
        descriptionCell.innerHTML = plinth.description;
        numModulesCell.innerHTML = plinth.length;
        
        totalCell.innerHTML = "<strong>RM" + plinth_total_string + "</strong>";
        grandTotal = grandTotal + plinth_total;
      }
      })
    }

  // Update the cell values with the new total prices
  if (document.getElementById('installationCharges')) {
    document.getElementById('installationCharges').innerHTML = '<strong>RM' + totalinstallationprice.toFixed(2) + '</strong>';
  }

  // Update the cell values with the new total prices
  if (document.getElementById('worktopCharges')) {
    document.getElementById('worktopCharges').innerHTML = '<strong>RM' + worktopCharges.toFixed(2) + '</strong>';
  }
  if (document.getElementById('transportationCharges')) {
    document.getElementById('transportationCharges').innerHTML = '<strong>RM' + transportationCharges.toFixed(2) + '</strong>';
  }
  // Calculate grand total including worktop and transportation charges
  if (isNaN(worktopCharges)) { // no price no need to add
    grandTotal = grandTotal;
  } else {
    grandTotal = grandTotal + worktopCharges;
  }
  if (isNaN(transportationCharges)) { // no price no need to add
    grandTotal = grandTotal;
  } else {
    grandTotal = grandTotal + transportationCharges;
  }

  // Calculate discount charges according to percentage
  discountCharges = 0;
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
  // Recalculate grand total
  if (isNaN(discountCharges)) { // no price no need to add
    grandTotal = grandTotal;
  } else {
    grandTotal = grandTotal - discountCharges;
  }

  // Update grand total
  quotation_price = grandTotal;
  var grandTotalCell = document.getElementById("grandTotal");
  if (grandTotalCell) {
    grandTotalCell.innerHTML = "<strong>Grand Total: RM" + grandTotal.toFixed(2) + "</strong>";
  }
  updateParentTotalPrice(parseFloat(quotation_price, 2));
  // Update error message and clear button
  var errorCell = document.getElementById("errorCell");
  var clearInvalidButton = document.getElementById("clearInvalidButton");
  var generatequotationbutton = document.getElementById("generatequotationbutton");

  // if got more than 1 module only show generate quotation button
  if (generatequotationbutton) {
    if (historicaluniqueid.length > 0 || digitalezarr.length > 0) {
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
  Name: sendData
  Description: Post data to kubiq_quotation_process.php
  Input:
      None
  Output:
      None
*/
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

/* 
  Name: getModule
  Description: Get module name of uid entered
  Input:
      1. uid: available values - ['1','2','3', ...]
  Output:
      ['QV4567','QLS9067','QL9067', ...]
*/
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

/* 
  Name: getDescription
  Description: Get module description of uid entered
  Input:
      1. uid: available values - ['1','2','3', ...]
  Output:
      ['450 x 700 x 560mm','900 x 700 x 560mm (SINK)','900 x 700 x 560mm', ...]
*/
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
/* 
  Name: getPrice
  Description: Get module price of uid entered
  Input:
      1. uid: available values - ['1','2','3', ...]
  Output:
      ['337','475','535', ...]
*/
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
/* 
  Name: getEpPrice
  Description: Get module ep price of uid entered
  Input:
      1. uid: available values - ['1','2','3', ...]
  Output:
      ['337','475','535', ...]
*/
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

/* 
  Name: getInstallationPrice
  Description: Get module installation price of uid entered
  Input:
      1. uid: available values - ['1','2','3', ...]
  Output:
      ['337','475','535', ...]
*/
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
/* 
  Name: renameSerialNumber
  Description: Get uid for serial number scanned
  Input:
      1. serialNumber: available values - ['04:6f:cf:aa:db:36:80','04:06:fc:9a:06:73:81','04:6f:f6:aa:db:36:80', ...]
  Output:
      ['1','2','3', ...]
*/
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
  if (serialNumber[0].length == 20) {
    return renamedSerialNumbers[serialNumber] || "";
  } else {
    return "";
  }
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