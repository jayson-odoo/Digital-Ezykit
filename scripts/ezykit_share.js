
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
    worktopUnitMeasurement = parseFloat(document.getElementById("worktopUnitMeasurement").value);
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
      moduleCounts[numericUid] = qty;
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