<?php
include '../config.php'; // include the config
include "../db.php";
// User must be logged in to access this page
session_start();
if (!isset($_SESSION['auth']) || $_SESSION['auth'] != 1) {
    header('Location: ../login.php');
    exit();
}
// Read ezkit data from database

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
?>
<!DOCTYPE html>
<html>
<head>
    <link href='https://fonts.googleapis.com/css?family=Inter' rel='stylesheet'/>
    <meta charset="utf-8" />
    <title>Kubiq Digital Ezykit</title>
    <meta name="description" content="app, web app, responsive, responsive layout, admin, admin panel, admin dashboard, flat, flat ui, ui kit, AngularJS, ui route, charts, widgets, components" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles/kubiq_ezykit_design.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.css">
    <style>
        #modules {
            padding: 20px;
            background: #eee;
            margin-bottom: 20px;
            z-index: 1;
            border-radius: 10px;
        }

        #dropzone {
            padding: 20px;
            background: #eee;
            min-height: 100px;
            margin-bottom: 20px;
            z-index: 0;
            border-radius: 10px;
        }

        #dropzone.active {
            outline: 1px solid blue;
        }

        #dropzone.hover {
            outline: 1px solid blue;
        }

        .drop-item {
            cursor: pointer;
            margin-bottom: 10px;
            background-color: rgb(255, 255, 255);
            padding: 5px 10px;
            border-radisu: 3px;
            border: 1px solid rgb(204, 204, 204);
            position: relative;
        }

        .drop-item .remove {
            position: absolute;
            top: 4px;
            right: 4px;
        }
    </style>
</head>
<body>
  <script src="scripts/kubiq_ezykit_design.js"></script>
  <header class="navbar navbar-expand-lg navbar-light bg-light">
      <a href="https://kubiq.com.my" class="navbar-brand">
          <img src="images/kubiq_logo.png" alt="Kubiq Logo" height="50"/>
      </a>
      <ul class="nav nav-tabs">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="#">Design</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../kubiq_quotation.php">Quotation</a>
        </li>
      </ul>
      <button class="btn btn-primary ml-5" type="button" onclick="newDesign()">New Design</button>
      <form class="form-inline ml-auto">
          <div class="form-group">
              <label for="total_price">Total (RM):</label>
              <input type="text" class="form-control ml-1" id="total_price" placeholder="Total..." readonly>
          </div>
          <button class="btn btn-primary ml-1" type="button">Continue</button>
      </form>
  </header>
<div class="wrapper d-flex align-items-stretch">
    <nav id="sidebar">
        <div class="custom-menu">
            <button type="button" id="sidebarCollapse" class="btn btn-primary">
                <i class="fa fa-bars"></i>
                <span class="sr-only">Toggle Menu</span>
            </button>
        </div>
        <div class="p-4">
          <div class="input-group">
            <div class="form-outline">
              <!-- Search form -->
              <div class="md-form mt-0">
                <input class="form-control" type="text" placeholder="Search modules..." aria-label="Search">
              </div>
            </div>
          </div>
          <hr>
          <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item drag">
              <button class="btn btn-light btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                <i class="fas fa-chevron-down"></i>
                Base
              </button>
              <div class="collapse" id="collapseExample">
                <ul class="list-group">
                  <li class="list-group-item btn btn-light drag btn btn-default" onclick="addShape(45,70)">
                    <div class="container">
                      <div class="row">
                        <div class="col align-middle">
                          <img src="images/kubiq_logo.png" alt="Kubiq Logo" height="25"/>
                        </div>
                        <div class="col">
                          <div class="text-wrap">
                            <small>QB4570</small>
                          </div>
                          <div class="text-wrap">
                            <small>Base Unit 1 Door</small>
                          </div>
                          <div class="text-wrap">
                            <small>45x60x100</small>
                          </div>
                        </div>
                      </div>
                    </div>
                  </li>
                  <li class="list-group-item">QB6070</li>
                  <li class="list-group-item">QB8070</li>
                </ul>
              </div>
            </li>
          </ul>
          <hr>
        </div>
    </nav>
    <!-- <div class="content container">
        <nav>
            <div class="container-fluid">
                <label for="length">Length:</label>
                <input type="number" id="length" placeholder="Length">
                <label for="width">Width:</label>
                <input type="number" id="width" placeholder="Width">
                <button onclick="addShape()">Add Shape</button>
                <h2>Shapes List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Shape</th>
                            <th>Length</th>
                            <th>Width</th>
                            <th>X</th>
                            <th>Y</th>
                        </tr>
                    </thead>
                    <tbody id="shapesList"></tbody>
                </table>
                <canvas id="canvas" width="500" height="500"></canvas>
            </div>
        </nav>
    </div> -->
</div>
<div class="container">
  <div class="row">
    <!-- <div class="col-sm-6" style="position: static;">
      <div id="modules">
        <p class="drag"><a class="btn btn-default">Text</a></p>

        <p class="drag"><a class="btn btn-default">Textarea</a></p>

        <p class="drag"><a class="btn btn-default">Number</a></p>
      </div>
    </div> -->

    <div class="col-sm-12" style="position: static;">
      <!-- <div id="dropzone" width="500" height="500"></div> -->
      <canvas id="dropzone" width="500" height="500"></canvas>
    </div>
  </div>
</div>
<script src="http://code.jquery.com/jquery-3.2.1.min.js"
        integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
    <script>
        $('.drag').draggable({
            appendTo: 'body',
            helper: 'clone' 
        });

        // $('#canvas').droppable({
        $('#dropzone').droppable({
            activeClass: 'active',
            hoverClass: 'hover',
            accept: ":not(.ui-sortable-helper)", // Reject clones generated by sortable
            drop: function (e, ui) {
                addShape(120,120);
                // var $el = $('<div class="drop-item"><details><summary>' + ui.draggable.text() + '</summary><div><label>More Data</label><input type="text" /></div></details></div>');
                // $el.append($('<button type="button" class="btn btn-default btn-xs remove"><span class="bi bi-trash"></span></button>').click(function () {$(this).parent().detach();}));
                // $(this).append($el);
            } });
        //     .sortable({
        //     items: '.drop-item',
        //     sort: function () {
        //         // gets added unintentionally by droppable interacting with sortable
        //         // using connectWithSortable fixes this, but doesn't allow you to customize active/hoverClass options
        //         $(this).removeClass("active");
        // } });
        //# sourceURL=pen.js
    </script>
    <script>
        (function ($) {
            "use strict";
            var fullHeight = function () {
                $(".js-fullheight").css("height", $(window).height());
                $(window).resize(function () {
                    $(".js-fullheight").css("height", $(window).height());
                });
            };
            fullHeight();
            $("#sidebarCollapse").on("click", function () {
                $("#sidebar").toggleClass("active");
            });
        })(jQuery);

        // const canvas = document.getElementById("dropzone");
        const canvas = document.getElementById("dropzone");
        const ctx = canvas.getContext("2d");
        let shapes = [];

        canvas.addEventListener("mousedown", onMouseDown);
        canvas.addEventListener("mouseup", onMouseUp);
        canvas.addEventListener("mousemove", onMouseMove);
        canvas.addEventListener("dblclick", onDoubleClick);

        function addShape(length,width) {
            const x = Math.random() * (canvas.width - length);
            const y = Math.random() * (canvas.height - width);

            // Snap to the right next to other shapes
            for (const shape of shapes) {
                if (Math.abs(x - shape.x) < 10) {
                    x += shape.length + 10;
                }
            }

            shapes.push({ x, y, length, width });
            drawShapes();
            updateShapesList();
        }

        function drawShapes() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            shapes.forEach(shape => {
                ctx.fillStyle = "lightgrey";
                ctx.fillRect(shape.x, shape.y, shape.length, shape.width);
                ctx.strokeStyle = "black";
                ctx.lineWidth = 2;
                ctx.strokeRect(shape.x, shape.y, shape.length, shape.width);
            });
        }

        function updateShapesList() {
            const shapesList = document.getElementById("shapesList");
            shapesList.innerHTML = "";
            shapes.forEach((shape, index) => {
                const row = shapesList.insertRow();
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${shape.length}</td>
                    <td>${shape.width}</td>
                    <td id="x-${index}">${shape.x}</td>
                    <td id="y-${index}">${shape.y}</td>
                `;
            });
        }

        let isDragging = false;
        let selectedShape = null;
        let offsetX, offsetY;

        function onMouseDown(e) {
            const mouseX = e.clientX - canvas.getBoundingClientRect().left;
            const mouseY = e.clientY - canvas.getBoundingClientRect().top;

            for (let i = shapes.length - 1; i >= 0; i--) {
                const shape = shapes[i];
                if (
                    mouseX >= shape.x &&
                    mouseX <= shape.x + shape.length &&
                    mouseY >= shape.y &&
                    mouseY <= shape.y + shape.width
                ) {
                    isDragging = true;
                    selectedShape = shape;
                    offsetX = mouseX - shape.x;
                    offsetY = mouseY - shape.y;
                    break;
                }
            }
        }

        function onMouseUp() {
            isDragging = false;
            selectedShape = null;
        }

        function onMouseMove(e) {
            if (isDragging && selectedShape) {
                const mouseX = e.clientX - canvas.getBoundingClientRect().left;
                const mouseY = e.clientY - canvas.getBoundingClientRect().top;

                // Calculate the new position while keeping the shape within the canvas boundaries
                selectedShape.x = mouseX - offsetX;
                selectedShape.y = mouseY - offsetY;

                // Ensure the shape doesn't move outside the canvas boundaries
                if (selectedShape.x < 0) {
                    selectedShape.x = 0;
                }
                if (selectedShape.y < 0) {
                    selectedShape.y = 0;
                }
                if (selectedShape.x + selectedShape.length > canvas.width) {
                    selectedShape.x = canvas.width - selectedShape.length;
                }
                if (selectedShape.y + selectedShape.width > canvas.height) {
                    selectedShape.y = canvas.height - selectedShape.width;
                }

                // Snap to the border if the shape is within a threshold distance
                const snapThreshold = 10;
                if (selectedShape.x < snapThreshold) {
                    selectedShape.x = 0;
                }
                if (selectedShape.y < snapThreshold) {
                    selectedShape.y = 0;
                }
                if (canvas.width - (selectedShape.x + selectedShape.length) < snapThreshold) {
                    selectedShape.x = canvas.width - selectedShape.length;
                }
                if (canvas.height - (selectedShape.y + selectedShape.width) < snapThreshold) {
                    selectedShape.y = canvas.height - selectedShape.width;
                }

                drawShapes();
                updateShapesList();
            }
        }

        function onDoubleClick(e) {
            const mouseX = e.clientX - canvas.getBoundingClientRect().left;
            const mouseY = e.clientY - canvas.getBoundingClientRect().top;

            for (let i = shapes.length - 1; i >= 0; i--) {
                const shape = shapes[i];
                if (
                    mouseX >= shape.x &&
                    mouseX <= shape.x + shape.length &&
                    mouseY >= shape.y &&
                    mouseY <= shape.y + shape.width
                ) {
                    shapes.splice(i, 1);
                    drawShapes();
                    updateShapesList();
                    break;
                }
            }
        }

        drawShapes();
    </script>
    
</body>
</html>
