<?php
class Item {
    public $name;
    public $description;
    public $model_id;
    public $height;
    public $width;
    public $depth;
    public $type;
    public $price;
    public $installation;
    public $average_ep;
    public $master_uid;

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
    function set_model_id ($model_id) {
        $this->model_id = $model_id;
    }
    function get_model_id() {
        return $this->model_id;
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
    function set_price ($price) {
        $this->price = $price;
    }
    function get_price() {
        return $this->price;
    }
    function set_installation ($installation) {
        $this->installation = $installation;
    }
    function get_installation() {
        return $this->installation;
    }
    function set_average_ep ($average_ep) {
        $this->average_ep = $average_ep;
    }
    function get_average_ep() {
        return $this->average_ep;
    }
    function set_master_uid ($master_uid) {
        $this->master_uid = $master_uid;
    }
    function get_master_uid() {
        return $this->master_uid;
    }
}
include '../config.php'; // include the config
include "../db.php";
GetMyConnection();

$worktop = isset($_GET['worktop']) ?: 0 ;
$unitprice = isset($_GET['unitprice']) ?: 1146 ;
$discount = isset($_GET['discount']) ?: 0 ;
$transportation = isset($_GET['transportation']) ?: 0 ;
$worktopcategory = isset($_GET['worktopcategory']) ?: '' ;
$worktoptype = isset($_GET['worktoptype']) ?: '' ;

// For serial number
$sql = 'select * from tblitem_master_ezkit order by `master_type` asc;';	
$r = mysql_query($sql);
$nr   = mysql_num_rows($r); // Get the number of rows
if($nr > 0){
    $item_array = array(); // array of serial number
    $type_array = array();
    while ($row = mysql_fetch_assoc($r)) {
        $new_item = new Item();
        $new_item->set_name($row['master_module']);
        $new_item->set_description($row['master_description']);
        $new_item->set_model_id($row['master_kjl_model_id']);
        $new_item->set_height($row['master_height']);
        $new_item->set_width($row['master_width']);
        $new_item->set_depth($row['master_depth']);
        $new_item->set_type($row['master_type']);
        $new_item->set_price($row['master_price']);
        $new_item->set_installation($row['master_installation']);
        $new_item->set_average_ep($row['master_ep']);
        $new_item->set_master_uid($row['master_uid']);
        if($row['master_type'] == "Tall"){
            $new_item->set_name($row['master_module']." (".$row['master_type'].")");
            $row['master_type'] = "Base";
        }
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles/kubiq_ezykit_design.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.css">
    <style>
        .axes {
            padding-left: 20px;
            font-size: 2rem;
        }
        #modules {
            padding: 20px;
            background: #eee;
            margin-bottom: 20px;
            z-index: 1;
            border-radius: 10px;
        }

        #base_dropzone,
        #wall_dropzone {
            /* padding: 20px; */
            background: #eee;
            position: absolute;
        }

        #base_dropzone {
            z-index: 10;
        }

        
        /* #base_dropzone.active {
            outline: 1px solid blue;
        }

        #base_dropzone.hover {
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
    <script src="scripts/ezykit_share.js"></script>
</head>
<body>
    <header class="navbar navbar-expand-lg navbar-light bg-light">
        <button class="btn btn-primary ml-5" type="button" onclick="newDesign()">New</button>
        <!-- <span class="text-info p-3">Hint: Press CTRL while moving an item to rotate, Blue side is front of module</span> -->
        <div class="d-flex justify-content-center flex-grow-1">
            <form class="form-inline">
                <div class="form-group">
                    <label for="total_price">Total (RM):</label>
                    <input type="text" class="form-control ml-1" id="total_price" placeholder="0.00" readonly>
                </div>
            </form>
        </div>
    </header>
    <div class="wrapper d-flex align-items-stretch">
        <nav id="sidebar">
            <div class="container">
                <h3 style="padding-top: 10px;">Kitchen Layout</h3>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label for="length">Length(mm):</label>
                            <input type="number" class="form-control" id="length" value="4500" placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-sm-1" style="padding-top: 40px;">
                        X
                    </div>
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label for="width">Width(mm):</label>
                            <input type="number" class="form-control" id="width" value="4500" placeholder="0.00">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-5">
                        <button class="btn btn-primary btn-block" class="form-control" onclick="resize_canvas()">Apply</button>
                    </div>
                    <div class="col-sm-1">
                        &nbsp;
                    </div>
                    <div class="col-sm-5">
                        <button class="btn btn-secondary btn-block" class="form-control" onclick="reset_canvas()">
                            <!-- reset button -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="24" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                                <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <hr>
            <div class="container">
                <h3>Modules</h3>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col">
                        <button class="btn btn-secondary btn-block" id="base_button" onclick="selectCanvas('base')">Base</button>
                    </div>
                    <div class="col">
                        <button class="btn btn-secondary btn-block" id="wall_button" onclick="selectCanvas('wall')">Wall</button>
                    </div>
                </div>
            </div>
            <div class="p-4">
            <div class="input-group">
                <div class="form-outline">
                <!-- Search form -->
                    <div class="md-form mt-0">
                        <input class="form-control" type="text" id="searchInput" placeholder="Search modules..." aria-label="Search">
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
        
        <div id="content">
            <div class="text-center">
            <!-- Price -->
            </div>
            <div id="base_container" class="container">
                <canvas id="base_dropzone"></canvas>
            </div>
            <div id="wall_container" class="container">
                <canvas id="wall_dropzone"></canvas>
            </div>
        </div>
    </div>
    <form id="data"></form>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"
            integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
            crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.concat.min.js"></script>
    <script>
        if (typeof quotation_price !== 'undefined' && quotation_price > 0){
            document.getElementById("total_price").value = parseFloat(quotation_price, 2);
        }
        var item_array = JSON.parse('<?php echo json_encode($item_array);?>');
        // global variables
        var base_canvas, wall_canvas, base_ctx, wall_ctx, shapes, shape_increment;
        var selected_canvas = "base";
        var item_id = "";
        var qty = 1;
        var historicaluniqueid = []; // to store tag number (always 20 digit)
        var arrayuniqueid = []; // to store converted tag number (between 1-2 digit)
        var totalinstallationprice = 0; // for installation charge
        var moduletotal = 0;

        var arraymodule = '<?php echo json_encode($arraymodule);?>';
        var arraydescription = '<?php echo json_encode($arraydescription);?>';
        var arrayprice = '<?php echo json_encode($arrayprice);?>';
        var arrayepprices = '<?php echo json_encode($arrayepprices);?>';
        var arrayinstallationprice = '<?php echo json_encode($arrayinstallationprice);?>';

        const objarraymodule = JSON.parse(arraymodule); // convert to javascript object
        const objarraydescription = JSON.parse(arraydescription); // convert to javascript object
        const objarrayprice = JSON.parse(arrayprice); // convert to javascript object
        const objarrayepprice = JSON.parse(arrayepprices); // convert to javascript object
        const objarrayinstallationprice = JSON.parse(arrayinstallationprice); // convert to javascript object

        init(); //first run

        function init() {
            base_canvas = document.getElementById("base_dropzone");
            wall_canvas = document.getElementById("wall_dropzone");
            base_ctx = init_canvas(base_canvas);
            wall_ctx = init_canvas(wall_canvas);
            shapes = [];
            shape_increment = 0;
            drawShapes();
            selectCanvas('base');
        }
        // Define the input field names
        var fieldNames = ["worktopUnitMeasurement", "worktopUnitPrice", "transportationDistance", "discountpercentage","worktopcategory","worktoptype"];

        // Get the form or container element where you want to append the hidden fields
        var form = document.getElementById("data"); // Replace "myForm" with the actual form ID or container ID

        // Loop through the field names and create hidden input fields
        for (var i = 0; i < fieldNames.length; i++) {
            var hiddenInput = document.createElement("input");
            hiddenInput.type = "hidden";
            hiddenInput.name = fieldNames[i];
            hiddenInput.id = fieldNames[i];
            form.appendChild(hiddenInput);
        }

        var catalogue = document.getElementById("catalogue");
        catalogue.innerHTML = '';
        var catalogue_innerHTML = '';
        Object.keys(item_array).forEach((type) => {
            catalogue_innerHTML += `<button class="btn btn-light btn-block text-left" type="button" data-toggle="collapse" data-target="#` + type + `Collapse" aria-expanded="`+(type=="Base"?"true":"false")+`" aria-controls="` + type + `Collapse">
                        <i class="fas fa-chevron-down"></i>
                        ` + type + `
                    </button>
                    <div class="collapse `+(type=="Base"?"show":"")+`" id="` + type + `Collapse">
                        <ul class="list-group" id="` + type + `-item-list-group">`;
            item_array[type].forEach((item) => {
                catalogue_innerHTML += `<li class="list-group-item btn btn-light" onclick='addShape(` + 
                            JSON.stringify({
                                'name': item.name,
                                'model_id': item.model_id,
                                'x': item.width, 
                                'y': item.depth,
                                'canvas_x': item.width/shape_increment, 
                                'canvas_y': item.depth/shape_increment,
                                'height': parseFloat(item.height),
                                'price': item.price,
                                'installation': item.installation,
                                'average_ep': item.average_ep,
                                'type': item.type,
                                'canvas': item.type == "Wall" ? "wall" : "base",
                                'master_uid':item.master_uid
                            }) + `)'>
                            <div class="container">
                                <div class="text-wrap">
                                    <span>` + item.name + `</span>
                                </div>
                                <div class="text-wrap">
                                    <span>` + item.description + `</span>
                                </div>
                            </div>
                        </li>`;
                        
            })
            catalogue_innerHTML += `</ul>
                    </div>`;
        })
        catalogue.innerHTML = catalogue_innerHTML;
        
                
        document.addEventListener("keydown", onKeyDown);

        function resize_canvas(){
            //no calculateQuotation
            console.log("resize");
            drawShapes();
        }

        function reset_canvas(){
            $("#length").val(4500);
            $("#width").val(4500);
            console.log("reset");
            //no calculateQuotation
            drawShapes();
        }

        function selectCanvas(canvas_string) {
            selected_canvas = canvas_string
            if (canvas_string == "base") {
                closeAllCollapses();
                openCollapse('BaseCollapse');
                document.getElementById("base_dropzone").style.opacity = 1
                document.getElementById("base_button").style.background = "#F16224"
                document.getElementById("wall_button").style.background = ""
                document.getElementById("wall_dropzone").style.zIndex = -1
                document.getElementById("base_dropzone").style.zIndex = 1
            } else {
                closeAllCollapses();
                openCollapse('WallCollapse');
                document.getElementById("wall_dropzone").style.opacity = 0.8
                document.getElementById("wall_button").style.background = "#F16224"
                document.getElementById("base_button").style.background = ""
                document.getElementById("base_dropzone").style.zIndex = -1
                document.getElementById("wall_dropzone").style.zIndex = 1
            }
            drawShapes();
        }
        
        function openCollapse(collapseId) {
            var collapseElement = document.getElementById(collapseId);

            if (collapseElement) {
                // Add the 'show' class to open the collapse
                collapseElement.classList.add('show');
                // Set aria-expanded to true
                collapseElement.setAttribute('aria-expanded', 'true');
            }
        }

        function closeAllCollapses() {
            // Get all collapse elements on the page
            var collapseElements = document.querySelectorAll('.collapse');
            
            // Loop through each collapse element
            collapseElements.forEach(function(element) {
                // Close the collapse
                element.classList.remove('show');

                // Set aria-expanded to false
                element.setAttribute('aria-expanded', 'false');
            });
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

        function init_canvas(canvas) {
            var ctx = canvas.getContext("2d")
            canvas.addEventListener("mousedown", onMouseDown);
            canvas.addEventListener("mouseup", onMouseUp);
            canvas.addEventListener("mousemove", onMouseMove);
            canvas.addEventListener("dblclick", onDoubleClick);
            const container_width = window.innerWidth - document.getElementById("sidebar").clientWidth - parseInt($(document.getElementById("base_container")).css('padding-left')) - parseInt($(document.getElementById("base_container")).css('padding-right'))
            const container_height = window.innerHeight / 2.5
            canvas.setAttribute('height', container_width)
            canvas.setAttribute('width', container_width)
            return ctx
        }
        function addShape(data) {
            var canvas;
            if (data.canvas == 'base') {
                selectCanvas('base');
                canvas = base_canvas;
            } else {
                selectCanvas('wall');
                canvas = wall_canvas;
            }
            var x = 0;
            var y = 0;
            var rotation = 0;
            var self_level, other_level;
            // Snap to the right next to other shapes
            for (const shape of shapes) {
                self_level = data.type == "Wall"? 1: 0
                other_level = shape.type == "Wall"? 1: 0
                if (shape.type == data.type) {
                    if (Math.abs(x - shape.x - shape.tf) < 10) {
                        x += shape.canvas_length + 10 + shape.tf;
                    }
                }
            }
            const tf = (data.canvas_x - data.canvas_y) / 2 * Math.abs(Math.sin(rotation))
            shapes.push({
                "name": data.name,
                "model_id": data.model_id,
                "x": x,
                "y": y,
                "length": data.x,
                "width": data.y,
                "canvas_length": data.canvas_x,
                "canvas_width": data.canvas_y,
                "tf": tf, 
                'height': data.height,
                "rotation": rotation,
                "price": data.price,
                "installation": data.installation,
                "average_ep": data.average_ep,
                "type": data.type,
                "canvas": data.canvas,
                "master_uid": data.master_uid
            });
            item_id = data.master_uid;
            total_price = calculateQuotation(4);
            if (total_price != 0) {
                document.getElementById("total_price").value = parseFloat(total_price, 2)
            } else {
                document.getElementById("total_price").value = null
            }
            drawShapes();
            // updateShapesList();
        }
        function draw_grid(ctx, canvas) {
            const container_width = document.getElementById('content').clientWidth
            const max_dimension = 4500;
            padding = 0;
            var counter = 0;
            if ($("#length").val() >= $("#width").val()){
                shape_increment = canvas.width/($("#length").val()*45/max_dimension);
                canvas.height = shape_increment*($("#width").val()*45/max_dimension);
            } else {
                shape_increment = canvas.height/($("#width").val()*45/max_dimension);
                const width = shape_increment*($("#length").val()*45/max_dimension);
                if (width >= container_width) {
                    canvas.width = container_width;
                    shape_increment = canvas.width/($("#length").val()*45/max_dimension);
                    canvas.height = shape_increment*($("#width").val()*45/max_dimension);
                } else {
                    canvas.width = shape_increment*($("#length").val()*45/max_dimension);
                }
            }
            for (var x = 0; x <= canvas.width; x += shape_increment) {
                counter += 1;
                ctx.moveTo(x + padding, padding);
                ctx.lineTo(x + padding, canvas.height + padding);
            }
            for (var x = 0; x <= canvas.height; x += shape_increment) {
                ctx.moveTo(padding, x + padding);
                ctx.lineTo(canvas.width + padding, x + padding);
            }
            ctx.strokeStyle = "#cdd1ce";
            ctx.stroke();
        }
        
        function drawShapes() {
            var total_price = 0.00;
            base_ctx.beginPath();
            wall_ctx.beginPath();
            base_ctx.clearRect(0, 0, base_canvas.width, base_canvas.height);
            wall_ctx.clearRect(0, 0, wall_canvas.width, wall_canvas.height);
            draw_grid(base_ctx, base_canvas);
            draw_grid(wall_ctx, wall_canvas);
            // console.log(shapes);
            shapes.forEach(shape => {
                // var count_price = 0;
                // total_price += Math.ceil(parseFloat(shape.price) + parseFloat(shape.average_ep)) + Math.ceil(parseFloat(shape.installation))
                if (shape.type != "Wall") {
                    draw_canvas(base_ctx, shape)
                } else {    
                    draw_canvas(wall_ctx, shape)
                }
            });
        }
        
        function draw_canvas(ctx, shape) {
            // don't modify value of shape here
            shape.canvas_length = shape.length/(100)*shape_increment;
            shape.canvas_width = shape.width/(100)*shape_increment;
            ctx.save();
            if (shape.type == "Wall") {
                ctx.fillStyle = "white"
            } else {
                ctx.fillStyle = "lightgrey";
            }
            ctx.globalAlpha = 1.5;
            ctx.translate(shape.x + shape.canvas_length/2, shape.y + shape.canvas_width/2);
            ctx.rotate(shape.rotation);
            ctx.translate(-(shape.x + shape.canvas_length/2), -(shape.y + shape.canvas_width/2));
            ctx.fillRect(shape.x, shape.y, shape.canvas_length, shape.canvas_width);
            ctx.strokeStyle = "black";
            ctx.lineWidth = 2;
            ctx.strokeRect(shape.x, shape.y, shape.canvas_length, shape.canvas_width);
            ctx.strokeStyle = "#5bc0de";
            ctx.lineWidth = 5;
            
            ctx.beginPath();
            ctx.moveTo(shape.x, shape.y + shape.canvas_width);
            ctx.lineTo(shape.x + shape.canvas_length, shape.y + shape.canvas_width);
            ctx.stroke();
            ctx.translate(shape.x + shape.canvas_length/2, shape.y + shape.canvas_width/2);
            // ctx.rotate(shape.rotation);
            ctx.translate(-(shape.x + shape.canvas_length/2), -(shape.y + shape.canvas_width/2));
            ctx.restore();
            ctx.fillStyle = "#000"
            const max_dimension = 4500;
            if (selected_canvas == "wall" && shape.type != "Wall") {
                return;
            }
            ctx.fillText(shape.name, shape.x + 2 - shape.tf, shape.y + shape.tf + 10)
            ctx.fillText("x: " + Math.round((shape.x - shape.tf)*max_dimension/45/shape_increment), shape.x + 2 - shape.tf, shape.y + shape.tf + 20)
            ctx.fillText("y: " + Math.round((shape.y + shape.tf)*max_dimension/45/shape_increment), shape.x + 2 - shape.tf, shape.y + shape.tf + 30)
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
                    <td>
                        <button onclick="rotateShape(${index})">Rotate 90°</button>
                    </td>
                `;
            });
        }

        function rotateShape(index) {
            const shape = shapes[index];
            shape.rotation += 0.1;
            drawShapes('rotate');
            // updateShapesList();
        }

        let isDragging = false;
        let selectedShape = null;
        let offsetX, offsetY;

        function onMouseDown(e) {
            var canvas;
            if (e.target.id == 'base_dropzone') {
                canvas = base_canvas;
            } else if (e.target.id == 'wall_dropzone') {
                canvas = wall_canvas;
            }
            const mouseX = e.clientX - canvas.getBoundingClientRect().left;
            const mouseY = e.clientY - canvas.getBoundingClientRect().top;

            for (let i = shapes.length - 1; i >= 0; i--) {
                const shape = shapes[i];
                if (
                    mouseX >= shape.x &&
                    mouseX <= shape.x + shape.canvas_length &&
                    mouseY >= shape.y &&
                    mouseY <= shape.y + shape.canvas_width &&
                    shape.canvas == selected_canvas
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
                var canvas;
                if (e.target.id == 'base_dropzone') {
                    canvas = base_canvas;
                } else if (e.target.id == 'wall_dropzone') {
                    canvas = wall_canvas;
                }
                const mouseX = e.clientX - canvas.getBoundingClientRect().left;
                const mouseY = e.clientY - canvas.getBoundingClientRect().top;
                // Calculate the new position while keeping the shape within the canvas boundaries
                if (selectedShape.rotation == 0 || selectedShape.rotation == Math.PI) {
                    selectedShape.x = mouseX - offsetX;
                    selectedShape.y = mouseY - offsetY;
                } else {
                    selectedShape.x = mouseX - offsetX;
                    selectedShape.y = mouseY - offsetY;
                }
                const snapThreshold = 15;
                // Ensure the shape doesn't move outside the canvas boundaries
                if (selectedShape.x - selectedShape.tf  < snapThreshold) {
                    selectedShape.x = 0 + selectedShape.tf;
                }
                if (selectedShape.y + selectedShape.tf < snapThreshold) {
                    selectedShape.y = 0 - selectedShape.tf;
                }
                
                if (selectedShape.x + selectedShape.canvas_length - selectedShape.tf > canvas.width - snapThreshold) {
                    selectedShape.x = canvas.width - selectedShape.canvas_length - selectedShape.tf;
                }
                if (selectedShape.y + selectedShape.canvas_width + selectedShape.tf > canvas.height - snapThreshold) {
                    selectedShape.y = canvas.height - selectedShape.canvas_width + selectedShape.tf;
                }

                // Snap to the border if the shape is within a threshold distance
                
                for (const shape of shapes) {
                    self_level = selectedShape.type == "Wall"? 1: 0
                    other_level = shape.type == "Wall"? 1: 0
                    if (self_level == other_level) {
                        if (shape !== selectedShape) {
                            var selectedShape_min_x = selectedShape.x - selectedShape.tf
                            var shape_min_x = shape.x - shape.tf
                            var selectedShape_max_x = selectedShape.x - selectedShape.tf + selectedShape.canvas_width*Math.abs(Math.sin(selectedShape.rotation)) + selectedShape.canvas_length*Math.abs(Math.cos(selectedShape.rotation)) + snapThreshold
                            var shape_max_x = shape.x - shape.tf + shape.canvas_width*Math.abs(Math.sin(shape.rotation)) + shape.canvas_length*Math.abs(Math.cos(shape.rotation)) + snapThreshold
                            
                            var selectedShape_min_y = selectedShape.y + selectedShape.tf
                            var shape_min_y = shape.y + shape.tf
                            var selectedShape_max_y = selectedShape.y + selectedShape.tf + selectedShape.canvas_length*Math.abs(Math.sin(selectedShape.rotation)) + selectedShape.canvas_width*Math.abs(Math.cos(selectedShape.rotation)) + snapThreshold
                            var shape_max_y = shape.y + shape.tf + shape.canvas_length*Math.abs(Math.sin(shape.rotation)) + shape.canvas_width*Math.abs(Math.cos(shape.rotation)) + snapThreshold
                            // console.log("selectedShapeminx " + selectedShape_min_x)
                            // console.log("shape_min_x " + shape_min_x)
                            // console.log("selectedShape_max_x " + selectedShape_max_x)
                            // console.log("shape_max_x " + shape_max_x)
                            // if (!(shape_max_y < selectedShape_min_y || selectedShape_max_y < shape_min_y)) {
                            //     if (Math.abs(selectedShape.x - selectedShape.tf - (shape.x + shape.canvas_length + shape.tf)) < snapThreshold) {
                            //         selectedShape.x = shape.x + shape.canvas_length + shape.tf + selectedShape.tf;
                            //     }
                            //     if (Math.abs(selectedShape.y + selectedShape.tf - (shape.y + shape.canvas_width - shape.tf)) < snapThreshold) {
                            //         selectedShape.y = shape.y + shape.canvas_width - shape.tf - selectedShape.tf;
                            //     }
                            // }
                            // console.log(shape_max_x)
                            // console.log(selectedShape_min_x)
                            // console.log(shape_min_x)
                            // console.log(selectedShape_max_x)
                            if (!(shape_max_x < selectedShape_min_x || selectedShape_max_x < shape_min_x || shape_max_y < selectedShape_min_y || selectedShape_max_y < shape_min_y)) {
                                if (Math.abs(selectedShape.x - selectedShape.tf - (shape.x + shape.canvas_length + shape.tf)) < snapThreshold) {
                                    selectedShape.x = shape.x + shape.canvas_length + shape.tf + selectedShape.tf;
                                }
                                if (Math.abs(selectedShape.y + selectedShape.tf - (shape.y + shape.canvas_width - shape.tf)) < snapThreshold) {
                                    selectedShape.y = shape.y + shape.canvas_width - shape.tf - selectedShape.tf;
                                }
                                if (Math.abs(selectedShape.x + selectedShape.tf + selectedShape.canvas_length - shape.x + shape.tf) < snapThreshold) {
                                    selectedShape.x = shape.x - selectedShape.canvas_length - shape.tf - selectedShape.tf;
                                }
                                if (Math.abs(selectedShape.y - selectedShape.tf + selectedShape.canvas_width - shape.y - shape.tf) < snapThreshold) {
                                    selectedShape.y = shape.y - selectedShape.canvas_width + shape.tf + selectedShape.tf;
                                }
                            }
                        }
                    }
                }
                drawShapes();
                // updateShapesList();
            }
        }

        function onDoubleClick(e) {
            var canvas;
            if (e.target.id == 'base_dropzone') {
                canvas = base_canvas;
            } else if (e.target.id == 'wall_dropzone') {
                canvas = wall_canvas;
            }
            const mouseX = e.clientX - canvas.getBoundingClientRect().left;
            const mouseY = e.clientY - canvas.getBoundingClientRect().top;

            for (let i = shapes.length - 1; i >= 0; i--) {
                const shape = shapes[i];
                if (
                    mouseX >= shape.x &&
                    mouseX <= shape.x + shape.canvas_length &&
                    mouseY >= shape.y &&
                    mouseY <= shape.y + shape.canvas_width &&
                    shape.canvas == selected_canvas
                ) {
                    shapes.splice(i, 1);
                    var total_price = 0.00;
                    total_price = document.getElementById("total_price").value;
                    moduletotal = moduletotal - Math.ceil(parseFloat(shape.installation)) - Math.ceil(parseFloat(shape.price) + parseFloat(shape.average_ep));
                    console.log(moduletotal);
                    total_price = total_price - Math.ceil(parseFloat(shape.installation)) - Math.ceil(parseFloat(shape.price) + parseFloat(shape.average_ep));
                    if (total_price >= 0) {
                        document.getElementById("total_price").value = parseFloat(total_price, 2)
                    } else {
                        document.getElementById("total_price").value = null
                    }
                    drawShapes();
                    // updateShapesList();
                    break;
                }
            }
        }

        function onKeyDown(e) {
            if (e.key == "Control" && selectedShape) {
                selectedShape.rotation += Math.PI * 90 / 180;
                if (selectedShape.rotation == Math.PI * 360 / 180) {
                    selectedShape.rotation = 0;
                }
                selectedShape.tf = (selectedShape.canvas_width - selectedShape.canvas_length) / 2 * Math.abs(Math.sin(selectedShape.rotation))
                console.log("onKeyDown");
                drawShapes();
                // updateShapesList();
            }
        }

        function newDesign() {
            shapes = [];
            console.log("newDesign");
            drawShapes();
        }
        var items = [];
        
        function generate_3D_JSON() {
            items = [];
            var item_json;
            const wall_fixed_height = 1500;
            const max_dimension = 4500;
            shapes.forEach((shape) => {
                item_json = {
                    'productId' : shape.model_id,
                    'position': {
                        'x': ((shape.x - shape.tf)*max_dimension/45/shape_increment + shape.length/2*Math.abs(Math.cos(shape.rotation)) + shape.width/2*Math.abs(Math.sin(shape.rotation))),
                        'y': -((shape.y + shape.tf)*max_dimension/45/shape_increment + shape.width/2*Math.abs(Math.cos(shape.rotation)) + shape.length/2*Math.abs(Math.sin(shape.rotation))),
                        'z': shape.type == "Wall" ? shape.height/2 + wall_fixed_height : shape.height/2
                    },
                    'size': {
                        'x': shape.length,
                        'y': shape.width,
                        'z': shape.height
                    },
                    'rotation': {
                        'x': 0,
                        'y': 0,
                        'z': -shape.rotation
                    },
                    'type': shape.type,
                    'name': shape.name,
                    'master_uid': shape.master_uid
                }
                items.push(item_json)
            });
            // Check for overlaps
            var groupedObjects = {};
            items.forEach(function(object) {
            // Put as same category for checking
            if(object.type == "Tall"){
                object.type = "Base";
            }
            if (!groupedObjects[object.type]) {
                groupedObjects[object.type] = [];
            }
            groupedObjects[object.type].push(object);
            });
            
            for (var type in groupedObjects) {
            var objects = groupedObjects[type];
            for (var i = 0; i < objects.length; i++) {
                for (var j = i + 1; j < objects.length; j++) {
                if (checkShapesOverlap(objects[i], objects[j])) {
                    return {
                        "items": false,
                        "error": "Overlap detected between " + objects[i].name + " and " + objects[j].name + " in type " + type
                    }

                }
                }
            }
            }
            return {'items': items}
        }

        // Function to check if two shapes overlap
        function checkShapesOverlap(object1, object2) {
            var x1 = parseFloat(object1.position.x);
            var y1 = parseFloat(object1.position.y);
            var width1 = parseFloat(object1.size.x);
            var height1 = parseFloat(object1.size.y);

            var x2 = parseFloat(object2.position.x);
            var y2 = parseFloat(object2.position.y);
            var width2 = parseFloat(object2.size.x);
            var height2 = parseFloat(object2.size.y);
            var eolx2 = x2 + width2;
            var eolx1 = x1 + width1;
            var eoly2 = y2 - height2;
            var eoly1 = y1 - height1;
            // Check for horizontal overlap
            var xOverlap = ((x2 >= x1 && x2 < eolx1) || (eolx2 > x1 && eolx2 < eolx1)) || ((x1 >= x2 && x1 < eolx2) || (eolx1 > x2 && eolx1 < eolx2));
            // console.log(xOverlap);
            // Check for vertical overlap
            var yOverlap = ((eoly2 > eoly1 && eoly2 < y1) || (y2 > eoly1 && y2 < y1)) || ((y1 <= y2 && y1 > eoly2) || (eoly1 <= y2 && eoly1 > eoly2));
            // console.log(yOverlap);
            return xOverlap && yOverlap;
        }

        function sleep(miliseconds) {
            var currentTime = new Date().getTime();
            while (currentTime + miliseconds >= new Date().getTime()) {
            }
        }

        function test() {
            $.ajax({ 
                type : 'POST',
                url  : 'kubiq_ezykit_process_kjl.php',
                success: function(responseText){
                    sleep(500);
                    window.open("https://yun.kujiale.com/cloud/tool/h5/bim?redirecturl=https%3A%2F%2Fwww.kujiale.com%2Fpub%2Fsaas%2Fworkbench%2Fdesign%2Fall%23&tre=000.000.001.pangu.mydesign&designid="+responseText+"&em=0&__rd=y&_gr_ds=true");
                }
            }
            )
        }
        function filterSidebarItems() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const sidebarItems = document.getElementById('catalogue').getElementsByTagName('li');

            for (let i = 0; i < sidebarItems.length; i++) {
                const itemText = sidebarItems[i].textContent.toLowerCase();
                if (itemText.includes(searchInput)) {
                    sidebarItems[i].style.display = 'block';
                } else {
                    sidebarItems[i].style.display = 'none';
                }
            }
        }

        // Attach an event listener to the search input
        document.getElementById('searchInput').addEventListener('input', filterSidebarItems);
        // window.addEventListener('resize', init, true);
    </script>
