var quotation_price = 0;
var column_counter = 0;
var grandTotal = 0;
var ROW_OFFSET = 7;
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
  moduleCounts = {};
  errorUids = []; // Reset errorUids
  grandTotal = 0;
  moduletotal = 0;
  totalinstallationprice = 0;
  total_surcharge = 0;
  worktopdescription_full = "";
  description_surcharge_full = "";
  transportationDistance = 0;

  // Calculate transportation charges
  worktopLabourSinkCharges = parseFloat(document.getElementById("worktopLabourSinkSelection").value);
  worktopLabourOpeningCharges = parseFloat(document.getElementById("worktopLabourOpeningSelection").value);
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

  if (isNaN(transportationCharges)) { // no price no need change status
    transportationCharges = 0;
  }

  if (isNaN(worktopLabourSinkCharges)) { // no price no need change status
    worktopLabourSinkCharges = 0;
  }

  if (isNaN(worktopLabourOpeningCharges)) { // no price no need change status
    worktopLabourOpeningCharges = 0;
  }

  var local_shapes;
  if (typeof shapes != "undefined") {
    local_shapes = shapes;
  } else {
    local_shapes = JSON.parse(localStorage.getItem("items")).filter((item) => typeof item.id != "undefined");
  }
  local_shapes.forEach((shape) => {
    var numericUid = parseInt(shape.id);
    var type = shape.type;
    var sequence = {
      'Base': 0,
      'Wall': 1,
      'Tall': 2,
      'Worktop': 3
    }
    var kitchen_wardrobe = shape.kitchen_wardrobe
    if (!moduleCounts[kitchen_wardrobe]) {
      moduleCounts[kitchen_wardrobe] = {};
    }
    if (!moduleCounts[kitchen_wardrobe][type]) {
      moduleCounts[kitchen_wardrobe][type] = { 'sequence': sequence[type] };
    }

    if (moduleCounts[kitchen_wardrobe][type][numericUid]) {
      moduleCounts[kitchen_wardrobe][type][numericUid] += 1;
    } else {
      moduleCounts[kitchen_wardrobe][type][numericUid] = 1;
    }
    ;
  })
  var rowIndex = 4;
  var module_only_total = 0;
  var module_only_qty = 0;
  var worktop_total = 0;
  var worktop_qty = 0;
  for (var kitchen_wardrobe in moduleCounts) {
    Object.keys(moduleCounts[kitchen_wardrobe]).sort(function (a, b) {
      var keyA = moduleCounts[kitchen_wardrobe][a].sequence, keyB = moduleCounts[kitchen_wardrobe][a].sequence;
      // Compare the 2 dates
      if (keyA < keyB) return -1;
      if (keyA > keyB) return 1;
      return 0;
    });
    createDropdownRow('Module', flag, table);
    for (var type in moduleCounts[kitchen_wardrobe]) {
      for (var uid_loop in moduleCounts[kitchen_wardrobe][type]) {
        var count = moduleCounts[kitchen_wardrobe][type][uid_loop];
        if (uid_loop == "sequence") {
          continue;
        }
        if (moduleCounts[kitchen_wardrobe][type].hasOwnProperty(uid_loop) && count > 0) {
          var module = getModule('Kitchen', type, uid_loop);
          var description = getDescription('Kitchen', type, uid_loop);
          var moduleprice = getPrice('Kitchen', type, uid_loop);
          moduleprice = parseFloat(moduleprice);
          var epprice = getEpPrice('Kitchen', type, uid_loop);
          epprice = parseFloat(epprice);
          var price = moduleprice + epprice;
          price = Math.ceil(price);
          var installationprice;
          if (type == "Worktop") {
            createDropdownRow('Worktop', flag, table);
            installationprice = 0;
          } else {
            installationprice = getInstallationPrice('Kitchen', type, uid_loop);
          }

          installationprice = parseFloat(installationprice);
          totalinstallationprice += installationprice * count;

          var total = count * price;
          if (flag != 4) {
            if ($('#surchargerow').length == 1) {
              var row = table.insertRow(table.rows.length - ROW_OFFSET);
            } else {
              var row = table.insertRow(table.rows.length - ROW_OFFSET + 1);
            }
            // Set Bootstrap attributes for the entire row
            if (type == "Worktop") {
              row.setAttribute("name", "WorktopCollapse");
              row.setAttribute("class", "collapse");
            } else {
              row.setAttribute("name", "ModuleCollapse");
              row.setAttribute("class", "collapse");
            }
            column_counter = 0;
            var noCell = row.insertCell(column_counter);
            var moduleCell = row.insertCell(++column_counter);
            var descriptionCell = row.insertCell(++column_counter);
            var uomCell = row.insertCell(++column_counter);
            var unitPriceCell = row.insertCell(++column_counter);
            var numModulesCell = row.insertCell(++column_counter);
            var totalCell = row.insertCell(++column_counter);

            noCell.innerHTML = table.rows.length - ROW_OFFSET;

            moduleCell.innerHTML = module;
            descriptionCell.innerHTML = description;
            uomCell.innerHTML = "Unit";
            unitPriceCell.innerHTML = parseFloat(price).toFixed(2);
            numModulesCell.innerHTML = count;
            totalCell.innerHTML = "<strong>RM" + total.toFixed(2) + "</strong>";
          }
          arrayuniqueid.push(uid_loop);
          moduletotal += total;
          if (type == "Worktop") {
            worktop_total += total;
            worktop_qty += count;
            $('#WorktopTotal').html("<strong>RM" + parseFloat(worktop_total).toFixed(2) + "</strong>");
            $('#WorktopQuantity').html("<strong>" + parseInt(worktop_qty) + "</strong>");
          } else {
            module_only_total += total;
            module_only_qty += count;
            $('#ModuleTotal').html("<strong>RM" + parseFloat(module_only_total).toFixed(2) + "</strong>");
            $('#ModuleQuantity').html("<strong>" + parseInt(module_only_qty) + "</strong>");
          }
        }

      }
    }
  }
  totalinstallationprice = Math.ceil(totalinstallationprice);
  // infill
  // calculate infill
  var local_objinfill = typeof objinfill != "undefined" ? objinfill : infillIdentification()
  // plinth
  // calculate plinth
  var local_objplinth = typeof objplinth != "undefined" ? objplinth : plinthLengthCalculation(local_objinfill.open_end_plinth, local_objinfill.open_end_plinth_cap)
  if (document.getElementById('infillqty_long')) {
    local_objinfill.long.qty = document.getElementById('infillqty_long').value;
  }

  if (document.getElementById('infillqty_short')) {
    local_objinfill.short.qty = document.getElementById('infillqty_short').value;
  }
  var plinth_total = 0;
  var alu_cap_total = 0;
  var plinth_cap_only = {};
  if (typeof local_objplinth != "undefined") {
    createDropdownRow('Panel', flag, table);
    Object.keys(local_objplinth).forEach((plinth_type) => {
      const plinth = local_objplinth[plinth_type]
      if (plinth.length > 0) {
        var plinth_total_string = Math.ceil(parseFloat(plinth.unit_price) * plinth.length).toFixed(2);
        plinth_total = parseFloat(plinth_total_string)
        if (flag != 4) {
          if ($('#surchargerow').length == 1) {
            var row = table.insertRow(table.rows.length - ROW_OFFSET);
          } else {
            var row = table.insertRow(table.rows.length - ROW_OFFSET + 1);
          }
          // Set Bootstrap attributes for the entire row
          row.setAttribute("name", "PanelCollapse");
          row.setAttribute("class", "collapse");

          column_counter = 0;
          var noCell = row.insertCell(column_counter);
          var moduleCell = row.insertCell(++column_counter);
          var descriptionCell = row.insertCell(++column_counter);
          var uomCell = row.insertCell(++column_counter);
          var unitPriceCell = row.insertCell(++column_counter);
          var numModulesCell = row.insertCell(++column_counter);
          // numModulesCell.setAttribute('contenteditable', true);

          var totalCell = row.insertCell(++column_counter);

          noCell.innerHTML = table.rows.length - ROW_OFFSET;
          moduleCell.innerHTML = plinth.name;
          uomCell.innerHTML = plinth.uom == "Meter Run" ? "MR" : "Pcs";
          unitPriceCell.innerHTML = parseFloat(plinth.unit_price).toFixed(2);
          descriptionCell.innerHTML = plinth.description;
          numModulesCell.innerHTML = plinth.length;

          totalCell.innerHTML = "<strong>RM" + plinth_total_string + "</strong>";
        }
        grandTotal = grandTotal + plinth_total;
      }
      plinth_cap_only[plinth_type] = {
        'plinth_cap': plinth.plinth_cap
      };
    })
  }

  var infill_total = 0;
  var cap_total = 0;
  if (typeof local_objinfill != "undefined") {
    Object.keys(local_objinfill).sort((a, b) => {
      // Move properties with objects as values to the front
      const aValueIsObject = typeof local_objinfill[a] === 'object';
      const bValueIsObject = typeof local_objinfill[b] === 'object';
    
      if (aValueIsObject && !bValueIsObject) {
        return -1;
      } else if (!aValueIsObject && bValueIsObject) {
        return 1;
      }
    
      // For other properties or when both have objects as values, use default sorting
      return a.localeCompare(b);
    }).forEach((infill_type) => {
      const infill = local_objinfill[infill_type]

      if (infill_type == 'lnc_end_cap' && local_objinfill[infill_type] > 0) {
        if (typeof objcap_list != "undefined") {
          createDropdownRow('Accessories', flag, table);
          objcap_list.forEach(cap => {
            if (cap.name == 'L End Cap' || cap.name == 'C End Cap') {
              var cap_total_string = Math.ceil(parseFloat(cap.price) * local_objinfill[infill_type]).toFixed(2);
              cap_total = parseFloat(cap_total_string)
              if (flag != 4) {
                if ($('#surchargerow').length == 1) {
                  var row = table.insertRow(table.rows.length - ROW_OFFSET);
                } else {
                  var row = table.insertRow(table.rows.length - ROW_OFFSET + 1);
                }
                // Set Bootstrap attributes for the entire row
                row.setAttribute("name", "AccessoriesCollapse");
                row.setAttribute("class", "collapse");
                column_counter = 0;
                var noCell = row.insertCell(column_counter);
                var moduleCell = row.insertCell(++column_counter);
                var descriptionCell = row.insertCell(++column_counter);
                var uomCell = row.insertCell(++column_counter);
                var unitPriceCell = row.insertCell(++column_counter);
                var numModulesCell = row.insertCell(++column_counter);
                // numModulesCell.setAttribute('contenteditable', true);
                var totalCell = row.insertCell(++column_counter);

                noCell.innerHTML = table.rows.length - ROW_OFFSET;
                var fieldname = cap.name.replace(/ /g, '_').toLowerCase() + '_qty';

                moduleCell.innerHTML = cap.name;
                descriptionCell.innerHTML = cap.description;
                uomCell.innerHTML = "Pcs";
                unitPriceCell.innerHTML = parseFloat(cap.price).toFixed(2);
                numModulesCell.innerHTML = '<input type="number" min="0" price="' + cap.price + '" description="' + cap.description + '" id="' + fieldname + '" name="' + fieldname + '" value="' + local_objinfill[infill_type] + '"></td>';

                totalCell.innerHTML = "<strong>RM" + cap_total_string + "</strong>";
              }
              grandTotal = grandTotal + cap_total;
            }
          });
        }
      } else {
        if (infill.qty > 0) {
          var infill_total_string = Math.ceil(parseFloat(infill.unit_price) * infill.qty).toFixed(2);
          infill_total = parseFloat(infill_total_string)
          if (flag != 4) {
            if ($('#surchargerow').length == 1) {
              var row = table.insertRow(table.rows.length - ROW_OFFSET);
            } else {
              var row = table.insertRow(table.rows.length - ROW_OFFSET + 1);
            }
            // Set Bootstrap attributes for the entire row
            row.setAttribute("name", "PanelCollapse");
            row.setAttribute("class", "collapse");
            column_counter = 0;
            var noCell = row.insertCell(column_counter);
            var moduleCell = row.insertCell(++column_counter);
            var descriptionCell = row.insertCell(++column_counter);
            var uomCell = row.insertCell(++column_counter);
            var unitPriceCell = row.insertCell(++column_counter);
            var numModulesCell = row.insertCell(++column_counter);
            // numModulesCell.setAttribute('contenteditable', true);

            var totalCell = row.insertCell(++column_counter);

            noCell.innerHTML = table.rows.length - ROW_OFFSET;
            moduleCell.innerHTML = infill.name;
            descriptionCell.innerHTML = infill.description;
            uomCell.innerHTML = "Pcs";
            unitPriceCell.innerHTML = parseFloat(infill.unit_price).toFixed(2);
            numModulesCell.innerHTML = '<input type="number" min="0" price="' + parseFloat(infill.unit_price).toFixed(2) + '" id="infillqty_' + infill_type + '" name="infillqty_' + infill_type + '" value="' + infill.qty + '" onchange="update_infill_price(this)">';

            totalCell.innerHTML = "<strong id='infill_display_price'>RM" + infill_total_string + "</strong>";
          }
          grandTotal = grandTotal + infill_total;
        }
      }
    })
  }
  Object.keys(plinth_cap_only).forEach((plinth_type) => {
    var plinth = plinth_cap_only[plinth_type]
    if (plinth.plinth_cap > 0) {
        if (local_objinfill['lnc_end_cap'] == 0) {
          createDropdownRow('Accessories', flag, table);
        }
        if (typeof objcap_list != "undefined") {
          objcap_list.forEach(cap => {
            if (cap.name == 'Alu Plinth Corner Cap') {
              var alu_cap_total_string = Math.ceil(parseFloat(cap.price) * plinth.plinth_cap).toFixed(2);
              alu_cap_total = parseFloat(alu_cap_total_string)
              if (flag != 4) {
                if ($('#surchargerow').length == 1) {
                  var row = table.insertRow(table.rows.length - ROW_OFFSET);
                } else {
                  var row = table.insertRow(table.rows.length - ROW_OFFSET + 1);
                }
                // Set Bootstrap attributes for the entire row
                row.setAttribute("name", "AccessoriesCollapse");
                row.setAttribute("class", "collapse");
                column_counter = 0;
                var noCell = row.insertCell(column_counter);
                var moduleCell = row.insertCell(++column_counter);
                var descriptionCell = row.insertCell(++column_counter);
                var uomCell = row.insertCell(++column_counter);
                var unitPriceCell = row.insertCell(++column_counter);
                var numModulesCell = row.insertCell(++column_counter);
                // numModulesCell.setAttribute('contenteditable', true);

                var totalCell = row.insertCell(++column_counter);

                noCell.innerHTML = table.rows.length - ROW_OFFSET;
                moduleCell.innerHTML = cap.name;
                descriptionCell.innerHTML = cap.description;
                uomCell.innerHTML = "Pcs";
                unitPriceCell.innerHTML = parseFloat(cap.price).toFixed(2);
                numModulesCell.innerHTML = plinth.plinth_cap;
                numModulesCell.innerHTML = '<input type="number" min="0" id="corner_cap_qty" price="' + cap.price + '" description="' + cap.description + '" name="corner_cap_qty" value="' + plinth.plinth_cap + '"></td>';

                totalCell.innerHTML = "<strong>RM" + alu_cap_total_string + "</strong>";
              }
              grandTotal = grandTotal + alu_cap_total;
            }
          });
        }
      }
  })
  var other_charges_total = 0;
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
  if (document.getElementById('worktopLabourSinkCharges')) {
    document.getElementById('worktopLabourSinkCharges').innerHTML = '<strong>RM' + worktopLabourSinkCharges.toFixed(2) + '</strong>';
  }
  if (document.getElementById('worktopLabourOpeningCharges')) {
    document.getElementById('worktopLabourOpeningCharges').innerHTML = '<strong>RM' + worktopLabourOpeningCharges.toFixed(2) + '</strong>';
  }
  // Calculate grand total including worktop and transportation charges
  // if (isNaN(worktopCharges)) { // no price no need to add
  //   grandTotal = grandTotal;
  // } else {
  //   grandTotal = grandTotal + worktopCharges;
  // }
  if (isNaN(moduletotal)) { // no price no need to add
    grandTotal = grandTotal;
  } else {
    other_charges_total = other_charges_total + totalinstallationprice;
    grandTotal = grandTotal + moduletotal + totalinstallationprice;
  }

  if (isNaN(transportationCharges)) { // no price no need to add
    grandTotal = grandTotal;
  } else {
    other_charges_total = other_charges_total + transportationCharges;
    grandTotal = grandTotal + transportationCharges;
  }

  if (isNaN(worktopLabourSinkCharges)) { // no price no need to add
    grandTotal = grandTotal;
  } else {
    other_charges_total = other_charges_total + worktopLabourSinkCharges;
    grandTotal = grandTotal + worktopLabourSinkCharges;
  }

  if (isNaN(worktopLabourOpeningCharges)) { // no price no need to add
    grandTotal = grandTotal;
  } else {
    other_charges_total = other_charges_total + worktopLabourOpeningCharges;
    grandTotal = grandTotal + worktopLabourOpeningCharges;
  }

  // Calculate discount charges according to percentage
  discountCharges = 0;
  discountpercentage = parseFloat(document.getElementById("discountpercentage").value);
  var discountCharges_worktop = 0;
  var discountCharges_module_only = 0;
  if (discountpercentage > 0) { // if got percentage, only got discount value
    var grandtotalfordiscount = grandTotal; // copy the existing grand total
    grandtotalfordiscount = worktop_total; // Discount exclude transportation & installation
    discountCharges_worktop = grandtotalfordiscount * discountpercentage / 100; // calculate the discount value according to discount percentage
    discountCharges_worktop = Math.floor(discountCharges_worktop); // round up the discount charges

    grandtotalfordiscount = module_only_total + infill_total + plinth_total; // Discount exclude transportation & installation
    discountCharges_module_only = grandtotalfordiscount * discountpercentage / 100; // calculate the discount value according to discount percentage
    discountCharges_module_only = Math.floor(discountCharges_module_only); // round up the discount charges

    discountCharges = discountCharges_worktop + discountCharges_module_only;
  }

  if (document.getElementById('discountCharges')) {
    document.getElementById('discountCharges').innerHTML = '<strong>-RM' + discountCharges.toFixed(2) + '</strong>';
  }
  // Recalculate grand total
  if (isNaN(discountCharges)) { // no price no need to add
    grandTotal = grandTotal;
  } else {
    other_charges_total = other_charges_total - discountCharges;
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
    if (digitalezarr.length > 0) {
      generatequotationbutton.style.display = "inline-block"; // show the generate quotation button
    } else {
      generatequotationbutton.style.display = "none"; // hide the generate quotation button
    }
  }

  createDropdownRow('Others', flag, table); //Add for the last of the setting

  //Assign to table for total in dropdown
  var panel_total = plinth_total + infill_total;
  var accessories_total = alu_cap_total + cap_total;
  $('#AccessoriesTotal').html("<strong>RM" + parseFloat(accessories_total).toFixed(2) + "</strong>");
  $('#PanelTotal').html("<strong>RM" + parseFloat(panel_total).toFixed(2) + "</strong>");
  $('#OthersTotal').html("<strong>RM" + parseFloat(other_charges_total).toFixed(2) + "</strong>");

  // Update row numbers
  if (table) {
    const rows = table.querySelectorAll("tr:not(:first-child):not([name*='Control'])");
    rows.forEach((row, index) => {
      row.cells[0].textContent = index + 1;
    });
  }

  return grandTotal;
}
function toggleChevron(type) {
  var classList = document.getElementsByName(type + "ControlCollapse")[0].cells[0].childNodes[0].classList;
  if (classList.contains('fa-chevron-down')) {
    classList.remove('fa-chevron-down');
    classList.add('fa-chevron-up')
  } else {
    classList.remove('fa-chevron-up');
    classList.add('fa-chevron-down')
  }
}
function createDropdownRow(type, flag, table) {
  if (flag != 4) {
    if ($('#surchargerow').length == 1) {
      var row = table.insertRow(table.rows.length - ROW_OFFSET);
    } else {
      var row = table.insertRow(table.rows.length - ROW_OFFSET + 1);
    }
    // Set Bootstrap attributes for the entire row
    row.setAttribute("onclick", "toggleChevron('" + type + "')");
    row.setAttribute("data-toggle", "collapse");
    row.setAttribute("data-target", "[name='" + type + "Collapse']");
    row.setAttribute("aria-expanded", "false");
    row.setAttribute("aria-controls", "[name='" + type + "Collapse']");
    row.setAttribute("name", "" + type + "ControlCollapse");
    row.setAttribute("style", "background-color:#f5f5f5; cursor: pointer");
    // Add cells to the new row
    for (var i = 0; i < 4; i++) {
      var cell = row.insertCell(i);
      switch (i) {
        case 0:
          cell.innerHTML = '<i class="fa fa-chevron-down"></i>';
          break;
        case 1:
          cell.innerHTML = '<strong>' + type + '</strong>';
          cell.setAttribute("colspan", "4");
          break;
        case 2:
          cell.setAttribute("id", type + "Quantity");
          break;
        case 3:
          cell.setAttribute("id", type + "Total");
          break;
        default:
          break;
      }
    }
  }
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
    worktopLabourSinkCharges: worktopLabourSinkCharges,
    worktopLabourOpeningCharges: worktopLabourOpeningCharges,
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
function getModule(kitchen_wardrobe, type, uid) {
  var modules = objarraymodule;
  return modules[kitchen_wardrobe][type][uid] || "";
}

/* 
  Name: getDescription
  Description: Get module description of uid entered
  Input:
      1. uid: available values - ['1','2','3', ...]
  Output:
      ['450 x 700 x 560mm','900 x 700 x 560mm (SINK)','900 x 700 x 560mm', ...]
*/
function getDescription(kitchen_wardrobe, type, uid) {
  var descriptions = objarraydescription;
  return descriptions[kitchen_wardrobe][type][uid] || "";
}
/* 
  Name: getPrice
  Description: Get module price of uid entered
  Input:
      1. uid: available values - ['1','2','3', ...]
  Output:
      ['337','475','535', ...]
*/
function getPrice(kitchen_wardrobe, type, uid) {
  var prices = objarrayprice;
  return prices[kitchen_wardrobe][type][uid] || 0;
}
/* 
  Name: getEpPrice
  Description: Get module ep price of uid entered
  Input:
      1. uid: available values - ['1','2','3', ...]
  Output:
      ['337','475','535', ...]
*/
function getEpPrice(kitchen_wardrobe, type, uid) {
  var epprices = objarrayepprice;

  return epprices[kitchen_wardrobe][type][uid] || 0;
}

/* 
  Name: getInstallationPrice
  Description: Get module installation price of uid entered
  Input:
      1. uid: available values - ['1','2','3', ...]
  Output:
      ['337','475','535', ...]
*/
function getInstallationPrice(kitchen_wardrobe, type, uid) {
  var installationprices = objarrayinstallationprice;

  return installationprices[kitchen_wardrobe][type][uid] || 0;
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

function update_infill_price(infill_input) {
  var price = infill_input.attributes.price.nodeValue * infill_input.value;
  $("#infill_display_price").html("RM" + price.toFixed(2));
  var grandTotal = calculateQuotation(4);
  updateParentTotalPrice(grandTotal);
}