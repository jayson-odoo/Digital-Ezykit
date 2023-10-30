<?php
class Item {
    public $name;
    public $description;
    public $height;
    public $width;
    public $depth;
    public $type;
    public $price;
    public $installation;

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
}
include '../config.php'; // include the config
include "../db.php";
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
        $new_item->set_price($row['master_price']);
        $new_item->set_installation($row['master_installation']);
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
        #modules {
            padding: 20px;
            background: #eee;
            margin-bottom: 20px;
            z-index: 1;
            border-radius: 10px;
        }

        #base_dropzone {
            /* padding: 20px; */
            background: #eee;
        }

        #wall_dropzone {
            /* padding: 20px; */
            margin-top: 20px;
            background: #eee;
            /* min-height: 100px;
            margin-bottom: 20px;
            z-index: 0;
            border-radius: 10px; */
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
</head>
<body>
    <header class="navbar navbar-expand-lg navbar-light bg-light">
        <button class="btn btn-primary ml-5" type="button" onclick="newDesign()">New</button>
        <button class="btn btn-primary ml-5" type="button" onclick="generate_3D_JSON()">JSON</button>
        <button class="btn btn-primary ml-5" type="button" onclick="test()">Test</button>
        <form class="form-inline ml-auto">
            <div class="form-group">
                <label for="total_price">Total (RM):</label>
                <input type="text" class="form-control ml-1" id="total_price" placeholder="0.00" readonly>
            </div>
            <!-- <button class="btn btn-primary ml-1" type="button">Continue</button> -->
        </form>
    </header>
    <div class="wrapper d-flex align-items-stretch">
        <nav id="sidebar">
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
        <div id="content" class="p-4 p-md-5 pt-5">
            <div class="container">
                <canvas id="base_dropzone" width="800" height="300"></canvas>
                <canvas id="wall_dropzone" width="800" height="300"></canvas>
            </div>
        </div>
        <nav id="rightbar">
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item" id="catalogue">
                    <h3>Kitchen Layout</h3>
                </li>
                <li class="nav-item" id="catalogue">
                    <label for="length">Length:</label>
                </li>
                <li class="nav-item" id="catalogue">
                    <input type="number" class="form-control" id="length" value="800" placeholder="0.00">
                </li>
                <li class="nav-item" id="catalogue">
                    <label for="width">Width:</label>
                </li>
                <li class="nav-item" id="catalogue">
                    <input type="number" class="form-control" id="width" value="300" placeholder="0.00">
                </li>
                <li class="nav-item" id="catalogue">
                    <button class="btn btn-secondary" class="form-control" onclick="resize_canvas()">Apply</button>
                </li>
            </ul>
        </nav>
    </div>
    <script src="http://code.jquery.com/jquery-3.2.1.min.js"
            integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
            crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
    <script>
        var item_array = JSON.parse('<?php echo json_encode($item_array);?>');
        
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
                catalogue_innerHTML += `<li class="list-group-item btn btn-light" onclick='addShape(` + 
                            JSON.stringify({
                                'name': item.name,
                                'x': item.width/10, 
                                'y': item.depth/10,
                                'height': parseFloat(item.height),
                                'price': item.price,
                                'installation': item.installation,
                                'type': item.type
                            }) + `)'>
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
        const base_canvas = document.getElementById("base_dropzone");
        const wall_canvas = document.getElementById("wall_dropzone");
        var base_ctx = init_canvas(base_canvas);
        var wall_ctx = init_canvas(wall_canvas);
        let shapes = [];
                
        document.addEventListener("keydown", onKeyDown);

        function resize_canvas(){
            base_ctx.canvas.style = 'height: ' + $("#length").val() + 'px !important;' + 'width: ' + $("#width").val() + 'px !important;';
            wall_ctx.canvas.style = 'height: ' + $("#length").val() + 'px !important;' + 'width: ' + $("#width").val() + 'px !important;';
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
            return ctx
        }
        function addShape(data) {
            var canvas;
            if (data.type == 'Base') {
                canvas = base_canvas;
            } else {
                canvas = wall_canvas;
            }
            var x = canvas.width/2;
            var y = canvas.height/2;
            var rotation = 0;
            // Snap to the right next to other shapes
            for (const shape of shapes) {
                if (Math.abs(x - shape.x) < 10) {
                    x += shape.length + 10;
                }
            }
            shapes.push({
                "name": data.name,
                "x": x,
                "y": y,
                "length": data.x,
                "width": data.y,
                'height': data.height,
                "rotation": rotation,
                "price": data.price,
                "installation": data.installation,
                "type": data.type
            });
            drawShapes();
            // updateShapesList();
        }
        function draw_grid(ctx, canvas) {
            padding = 0;
            increment = 20;
            for (var x = 0; x <= canvas.width; x += increment) {
                ctx.moveTo(x + padding, padding);
                ctx.lineTo(x + padding, canvas.height + padding);
            }

            for (var x = 0; x <= canvas.height; x += increment) {
                ctx.moveTo(padding, x + padding);
                ctx.lineTo(canvas.width + padding, x + padding);
            }
            ctx.strokeStyle = "#cdd1ce";
            ctx.stroke();
        }
        
        function drawShapes() {
            base_ctx.clearRect(0, 0, base_canvas.width, base_canvas.height);
            wall_ctx.clearRect(0, 0, wall_canvas.width, wall_canvas.height);
            draw_grid(base_ctx, base_canvas);
            draw_grid(wall_ctx, wall_canvas);
            var total_price = 0.00
            shapes.forEach(shape => {
                total_price += parseFloat(shape.price) + parseFloat(shape.installation)
                if (shape.type != "Wall") {
                    draw_canvas(base_ctx, shape)
                } else {
                    draw_canvas(wall_ctx, shape)
                }
            });
            if (total_price != 0) {
                document.getElementById("total_price").value = total_price
            } else {
                document.getElementById("total_price").value = null
            }
        }
        
        function draw_canvas(ctx, shape) {
            // don't modify value of shape here
            ctx.save();
            ctx.fillStyle = "lightgrey";
            ctx.translate(shape.x + shape.length/2, shape.y + shape.width/2);
            ctx.rotate(shape.rotation);
            ctx.translate(-(shape.x + shape.length/2), -(shape.y + shape.width/2));
            ctx.fillRect(shape.x, shape.y, shape.length, shape.width);
            ctx.strokeStyle = "black";
            ctx.lineWidth = 2;
            ctx.strokeRect(shape.x, shape.y, shape.length, shape.width);
            ctx.translate(shape.x + shape.length/2, shape.y + shape.width/2);
            // ctx.rotate(shape.rotation);
            ctx.translate(-(shape.x + shape.length/2), -(shape.y + shape.width/2));
            ctx.restore();
            ctx.fillStyle = "#000"
            ctx.fillText(shape.name, shape.x + 2, shape.y + shape.width/2)
            ctx.fillText("x: " + parseFloat(shape.x + shape.length/2, 0), shape.x + 2, shape.y + shape.width/2 + 10)
            ctx.fillText("y: " + parseFloat(shape.y + shape.width/2, 0), shape.x + 2, shape.y + shape.width/2 + 20)
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
                var canvas;
                if (e.target.id == 'base_dropzone') {
                    canvas = base_canvas;
                } else if (e.target.id == 'wall_dropzone') {
                    canvas = wall_canvas;
                }
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
                for (const shape of shapes) {
                    if (shape !== selectedShape) {
                        
                        if (shape.rotation == 0 || shape.rotation == Math.PI) {
                            left_right = 'length';
                            top_bottom = 'width';
                        } else {
                            left_right = 'width';
                            top_bottom = 'length';
                        }
                        if (Math.abs(selectedShape.x - (shape.x + shape[left_right])) < snapThreshold) {
                            selectedShape.x = shape.x + shape[left_right];
                        }
                        if (Math.abs(selectedShape.y - (shape.y + shape[top_bottom])) < snapThreshold) {
                            selectedShape.y = shape.y + shape[top_bottom];
                        }
                        if (Math.abs(selectedShape.x + selectedShape[left_right] - shape.x) < snapThreshold) {
                            selectedShape.x = shape.x - selectedShape[left_right];
                        }
                        if (Math.abs(selectedShape.y + selectedShape[top_bottom] - shape.y) < snapThreshold) {
                            selectedShape.y = shape.y - selectedShape[top_bottom];
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
                    mouseX <= shape.x + shape.length &&
                    mouseY >= shape.y &&
                    mouseY <= shape.y + shape.width
                ) {
                    shapes.splice(i, 1);
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
                drawShapes();
                // updateShapesList();
            }
        }

        function newDesign() {
            shapes = [];
            drawShapes();
        }
        function generate_3D_JSON() {
            var items = []
            var item_json;
            const wall_fixed_height = 100;
            shapes.forEach((shape) => {
                item_json = {
                    'position': {
                        'x': shape.x,
                        'y': shape.y,
                        'z': shape.type == "Wall" ? shape.height/2 + wall_fixed_height : shape.height
                    },
                    'size': {
                        'x': shape.width*10,
                        'y': shape.length*10,
                        'z': shape.height
                    },
                    'rotation': {
                        'x': 0,
                        'y': 0,
                        'z': shape.rotation
                    }
                }
                items.push(item_json)
            })
            return {'items': items}
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
        drawShapes();
        
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
    </script>
