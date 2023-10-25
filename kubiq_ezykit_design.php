<?php
class Item {
    public $name;
    public $description;
    public $height;
    public $width;
    public $depth;
    public $type;

    function set_name ($name) {
        $this->name = $name;
    }
    function get_name() {
        return $this->name;
    }
    function set_description ($description) {
        $this->description = $description;
    }
    function get_description() {
        return $this->description;
    }
    function set_height ($height) {
        $this->height = $height;
    }
    function get_height() {
        return $this->height;
    }
    function set_width ($width) {
        $this->width = $width;
    }
    function get_width() {
        return $this->width;
    }
    function set_depth ($depth) {
        $this->depth = $depth;
    }
    function get_depth() {
        return $this->depth;
    }
    function set_type ($type) {
        $this->type = $type;
    }
    function get_type() {
        return $this->type;
    }
}
include 'config.php'; // include the config
// User must be logged in to access this page
if (!isset($_SESSION['auth']) || $_SESSION['auth'] != 1) {
    header('Location: login.php');
    exit();
}

// Read ezkit data from database
// include "db.php";
GetMyConnection();
// For serial number
$sql = 'select * from tblitem_master_ezkit';	
$r = mysql_query($sql);
$nr   = mysql_num_rows($r); // Get the number of rows
if($nr > 0){
    $item_array = array(); // array of serial number
    $type_array = array();
    while ($row = mysql_fetch_assoc($r)) {
        $new_item = new Item();
        $new_item->set_name($row['master_module']);
        $new_item->set_description($row['master_description']);
        $new_item->set_height($row['master_height']);
        $new_item->set_width($row['master_width']);
        $new_item->set_depth($row['master_depth']);
        $new_item->set_type($row['master_type']);
        if (!in_array($row['master_type'], $type_array)) {
            array_push($type_array, $row['master_type']);
            $item_array[$row['master_type']] = [];
        }
        array_push($item_array[$row['master_type']], $new_item); // add the serial number into the array
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
    <meta charset="utf-8" />
    <title>Kubiq Digital Ezykit</title>
    <meta name="description" content="app, web app, responsive, responsive layout, admin, admin panel, admin dashboard, flat, flat ui, ui kit, AngularJS, ui route, charts, widgets, components" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="digital_ezykit/styles/kubiq_ezykit_design.css">
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
            /* padding: 20px; */
            background: #eee;
            /* min-height: 100px;
            margin-bottom: 20px;
            z-index: 0;
            border-radius: 10px; */
        }

        /* #dropzone.active {
            outline: 1px solid blue;
        }

        #dropzone.hover {
            outline: 1px solid blue;
        } */

        /* .drop-item {
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
        } */
    </style>
</head>
<body>
    <header class="navbar navbar-expand-lg navbar-light bg-light">
        <a href="https://kubiq.com.my" class="navbar-brand">
            <img src="digital_ezykit/images/kubiq_logo.png" alt="Kubiq Logo" height="50"/>
        </a>
        <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">Design</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="index.php?module=kubiq_quotation">Quotation</a>
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
                <li class="nav-item" id="catalogue">
                    
                </li>
            </ul>
            <hr>
            </div>
        </nav>
        <div id="content" class="p-4 p-md-5 pt-5">
            <table class="table">
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
            <div class="container">
                <div class="row">
                    <div class="col-sm">
                        <canvas id="dropzone" width="400" height="400" ></canvas>
                    </div>
                    <div class="col-sm">
                        <div class="row">
                            <h3>Kitchen Layout</h3>
                        </div>
                        <div class="row">
                            <label for="length">Length:</label>
                        </div>
                        <div class="row">
                            <input type="number" id="length" placeholder="Length">
                        </div>
                        <div class="row">
                            <label for="width">Width:</label>
                        </div>
                        <div class="row">
                            <input type="number" id="width" placeholder="Width">
                        </div>
                        <div class="row">
                            <button onclick="resize_canvas()">Apply</button>
                        </div>
                    </div>
                </div>
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
        // $('.drag').draggable({
        //     appendTo: 'body',
        //     helper: 'clone' 
        // });

        // $('#canvas').droppable({
        // $('#dropzone').droppable({
        //     activeClass: 'active',
        //     hoverClass: 'hover',
        //     accept: ":not(.ui-sortable-helper)", // Reject clones generated by sortable
        //     drop: function (e, ui) {
                // addShape(120,120);
                // var $el = $('<div class="drop-item"><details><summary>' + ui.draggable.text() + '</summary><div><label>More Data</label><input type="text" /></div></details></div>');
                // $el.append($('<button type="button" class="btn btn-default btn-xs remove"><span class="bi bi-trash"></span></button>').click(function () {$(this).parent().detach();}));
                // $(this).append($el);
            // } });
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
        var item_array = JSON.parse('<?php echo json_encode($item_array);?>');
        console.log(item_array)
        var catalogue = document.getElementById("catalogue");
        catalogue.innerHTML = '';
        var catalogue_innerHTML = '';
        Object.keys(item_array).forEach((type) => {
            catalogue_innerHTML += `<button class="btn btn-light btn-block text-left" type="button" data-toggle="collapse" data-target="#` + type + `Collapse" aria-expanded="false" aria-controls="` + type + `Collapse">
                        <i class="fas fa-chevron-down"></i>
                        ` + type + `
                    </button>
                    <div class="collapse" id="` + type + `Collapse">
                        <ul class="list-group" id="` + type + `-item-list-group">`;
            item_array[type].forEach((item) => {
                catalogue_innerHTML += `<li class="list-group-item btn btn-light" onclick="addShape(` + item.height/10 + `,` + item.width/10 + `)">
                            <div class="container">
                                <div class="row">
                                    <div class="col">
                                        <div class="text-wrap">
                                            <small>` + item.name + `</small>
                                        </div>
                                        <div class="text-wrap">
                                            <small>` + item.description + `</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>`;
                        
            })
            catalogue_innerHTML += `</ul>
                    </div>`;
        })
        catalogue.innerHTML = catalogue_innerHTML;
        const canvas = document.getElementById("dropzone");
        var ctx = canvas.getContext("2d");
        let shapes = [];

        canvas.addEventListener("mousedown", onMouseDown);
        canvas.addEventListener("mouseup", onMouseUp);
        canvas.addEventListener("mousemove", onMouseMove);
        canvas.addEventListener("dblclick", onDoubleClick);

        function resize_canvas(){
            ctx.canvas.height = $("#length").val();
            ctx.canvas.width = $("#width").val();
        }

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
    <!-- <script src="digital_ezykit/scripts/kubiq_ezykit_design.js"></script> -->
</body>
</html>
<?php include('footer.php'); ?>
