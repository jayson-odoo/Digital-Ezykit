// set price if there is from quotation page
if (typeof quotation_price !== 'undefined' && quotation_price > 0) {
    updateParentTotalPrice(parseFloat(quotation_price, 2));
}
// global variables
var base_canvas, wall_canvas, base_ctx, wall_ctx, shapes, shape_increment;
var selected_canvas = "base";
var item_id = "";
var historicaluniqueid = []; // to store tag number (always 20 digit)
var arrayuniqueid = []; // to store converted tag number (between 1-2 digit)
var totalinstallationprice = 0; // for installation charge
var moduletotal = 0;

const objarraymodule = JSON.parse(arraymodule); // convert to javascript object
const objarraydescription = JSON.parse(arraydescription); // convert to javascript object
const objarrayprice = JSON.parse(arrayprice); // convert to javascript object
const objarrayepprice = JSON.parse(arrayepprices); // convert to javascript object
const objarrayinstallationprice = JSON.parse(arrayinstallationprice); // convert to javascript object

const LAYOUT_MAPPING = {
    0: "I",
    1: "L",
    2: "U"
}

init(); //first run

function init() {
    base_canvas = document.getElementById("base_dropzone");
    wall_canvas = document.getElementById("wall_dropzone");
    base_ctx = init_canvas(base_canvas);
    wall_ctx = init_canvas(wall_canvas);
    shapes = [];
    shape_increment = 0;
    reloadCanvas();
    selectCanvas('base');
}
// Define the input field names
var fieldNames = ["worktopUnitMeasurement", "worktopUnitPrice", "transportationDistance", "discountpercentage", "worktopcategory", "worktoptype"];

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

// Create list of item selection
var catalogue = document.getElementById("catalogue");
catalogue.innerHTML = '';
var catalogue_innerHTML = '';
Object.keys(item_array).forEach((type) => {
    catalogue_innerHTML += `<button class="btn btn-light btn-block text-left" type="button" data-toggle="collapse" data-target="#` + type + `Collapse" aria-expanded="` + (type == "Base" ? "true" : "false") + `" aria-controls="` + type + `Collapse">
                <i class="fas fa-chevron-down"></i>
                ` + type + `
            </button>
            <div class="collapse `+ (type == "Base" ? "show" : "") + `" id="` + type + `Collapse">
                <ul class="list-group" id="` + type + `-item-list-group">`;
    item_array[type].forEach((item) => {
        catalogue_innerHTML += `<li class="list-group-item btn btn-light" onclick='addShape(` +
            JSON.stringify({
                'name': item.name,
                'model_id': item.model_id,
                'x': item.width,
                'y': item.depth,
                'canvas_x': item.width / shape_increment,
                'canvas_y': item.depth / shape_increment,
                'height': parseFloat(item.height),
                'price': item.price,
                'installation': item.installation,
                'average_ep': item.average_ep,
                'type': item.type,
                'canvas': item.type == "Wall" ? "wall" : "base",
                'master_uid': item.master_uid,
                'id': item.id
            }) + `)'>
                    <div class="container">
                        <div class="text-wrap">
                            <span>` + item.name + ' (' + item.description + ')' + `</span>
                        </div>
                    </div>
                </li>`;

    })
    catalogue_innerHTML += `</ul>
            </div>`;
})
catalogue.innerHTML = catalogue_innerHTML;


document.addEventListener("keydown", onKeyDown);

/* 
    Name: resize_canvas
    Description: Redraw the grid of canvas based on insertion
    Input:
        None
    Output:
        None
*/
function resize_canvas() {
    reloadCanvas();
}

/* 
    Name: reset_canvas
    Description: Reset canvas to default value
    Input:
        None
    Output:
        None
*/
function reset_canvas() {
    $("#length").val(4500);
    $("#width").val(4500);
    reloadCanvas();
}

/* 
    Name: selectCanvas
    Description: Select the type of canvas and list of item according to input
    Input:
        1. canvas_string: available values - ['base', 'wall']
    Output:
        None
*/
function selectCanvas(canvas_string) {
    selected_canvas = canvas_string
    if (canvas_string == "base") {
        closeAllCollapses();
        openCollapse('BaseCollapse');
        document.getElementById("base_dropzone").style.opacity = 1
        var elementsWithNameYes = document.getElementsByName('base_button');
        // Convert the NodeList to an array and set the background color of each element to orange
        Array.from(elementsWithNameYes).forEach(function (element) {
            element.style.background = '#08244c';
        });
        var elementsWithNameYes = document.getElementsByName('wall_button');
        // Convert the NodeList to an array and set the background color of each element to orange
        Array.from(elementsWithNameYes).forEach(function (element) {
            element.style.background = '#8D99A3';
        });
        document.getElementById("wall_dropzone").style.zIndex = -1
        document.getElementById("base_dropzone").style.zIndex = 1
    } else {
        closeAllCollapses();
        openCollapse('WallCollapse');
        document.getElementById("wall_dropzone").style.opacity = 0.8
        var elementsWithNameYes = document.getElementsByName('wall_button');
        // Convert the NodeList to an array and set the background color of each element to orange
        Array.from(elementsWithNameYes).forEach(function (element) {
            element.style.background = '#08244c';
        });
        var elementsWithNameYes = document.getElementsByName('base_button');
        // Convert the NodeList to an array and set the background color of each element to orange
        Array.from(elementsWithNameYes).forEach(function (element) {
            element.style.background = '#8D99A3';
        });
        document.getElementById("base_dropzone").style.zIndex = -1
        document.getElementById("wall_dropzone").style.zIndex = 1
    }
    reloadCanvas();
}

/* 
    Name: openTab
    Description: Switches tab between item list and kitchen layout
    Input:
        1. tabName: available values - ['modules', 'kitchen_layout']
    Output:
        None
*/
function openTab(tabName) {
    var i, tabContent, tabs;
    tabContent = document.getElementsByClassName("tab-content");

    // hide all elements in all tabs
    for (i = 0; i < tabContent.length; i++) {
        tabContent[i].style.display = "none";
    }
    tabs = document.getElementsByClassName("tab_switch")[0].getElementsByTagName("div");
    for (i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove("active");
    }

    // set the input tabName as active and show elements in tab
    document.getElementById(tabName).style.display = "block";
    event.currentTarget.classList.add("active");
    $('.nav-link').removeClass('active');
    if (tabName == "module") {
        $('.nav-link:eq(0)').addClass('active');
    } else {
        $('.nav-link:eq(1)').addClass('active');
    }
}

/* 
    Name: openCollapse
    Description: Expand the selected list of dropdown
    Input:
        1. collapseId: available values - ['WallCollapse', 'BaseCollapse']
    Output:
        None
*/
function openCollapse(collapseId) {
    var collapseElement = document.getElementById(collapseId);

    if (collapseElement) {
        // Add the 'show' class to open the collapse
        collapseElement.classList.add('show');
        // Set aria-expanded to true
        collapseElement.setAttribute('aria-expanded', 'true');
    }
}

/* 
    Name: closeAllCollapses
    Description: Close all the dropdown
    Input:
        None
    Output:
        None
*/
function closeAllCollapses() {
    // Get all collapse elements on the page
    var collapseElements = document.querySelectorAll('.collapse');

    // Loop through each collapse element
    collapseElements.forEach(function (element) {
        // Close the collapse
        element.classList.remove('show');

        // Set aria-expanded to false
        element.setAttribute('aria-expanded', 'false');
    });
}

/* 
    Name: init_canvas
    Description: assign function to element selected and set width and height of the element
    Input:
        1. canvas: available values - ['document.getElementById("base_dropzone")', 'document.getElementById("wall_dropzone")']
    Output:
        ctx (2D context of the canvas)
*/
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

/* 
    Name: addShape
    Description: Draw shape into the canvas, Price calculation, Update price to HTML
    Input:
        1. data: {"name","model_id","x","y","canvas_x","canvas_y","height","price","installation",      "average_ep","type","canvas","master_uid","id"}
    Output:
        None
*/
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
        self_level = data.type == "Wall" ? 1 : 0
        other_level = shape.type == "Wall" ? 1 : 0
        if (shape.type == data.type) {
            if (Math.abs(x - shape.x - shape.tf) < 10) {
                x += shape.canvas_length + 10 + shape.tf;
            }
        }
    }
    const tf = (data.canvas_x - data.canvas_y) / 2 * Math.abs(Math.sin(rotation))

    // Add data into shapes array for price calculation
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
        "master_uid": data.master_uid,
        "id": data.id
    });
    item_id = data.id;
    total_price = calculateQuotation(4); //calculate price
    if (total_price != 0) {
        updateParentTotalPrice(parseFloat(quotation_price, 2)); //update display price
    } else {
        updateParentTotalPrice(null);
    }
    reloadCanvas(); //draw in canvas
}

/* 
    Name: draw_grid
    Description: Draw grid inside the canvas
    Input:
        1. ctx: available values - ['wall_ctx', 'base_ctx'] 
        2. canvas: available values - ['wall_canvas','base_canvas']
    Output:
        None
*/
function draw_grid(ctx, canvas) {
    const container_width = document.getElementById('content').clientWidth
    const max_dimension = 4500;
    padding = 0;
    var counter = 0;
    // decide the biggest width and height to be set for grid
    if ($("#length").val() >= $("#width").val()) {
        shape_increment = canvas.width / ($("#length").val() * 45 / max_dimension);
        canvas.height = shape_increment * ($("#width").val() * 45 / max_dimension);
    } else {
        shape_increment = canvas.height / ($("#width").val() * 45 / max_dimension);
        const width = shape_increment * ($("#length").val() * 45 / max_dimension);

        if (width >= container_width) {
            canvas.width = container_width;
            shape_increment = canvas.width / ($("#length").val() * 45 / max_dimension);
            canvas.height = shape_increment * ($("#width").val() * 45 / max_dimension);
        } else {
            canvas.width = shape_increment * ($("#length").val() * 45 / max_dimension);
        }
    }
    // loop for the width grid
    for (var x = 0; x <= canvas.width; x += shape_increment) {
        counter += 1;
        ctx.moveTo(x + padding, padding);
        ctx.lineTo(x + padding, canvas.height + padding);
    }
    // loop for the height grid
    for (var x = 0; x <= canvas.height; x += shape_increment) {
        ctx.moveTo(padding, x + padding);
        ctx.lineTo(canvas.width + padding, x + padding);
    }
    ctx.strokeStyle = "#cdd1ce";
    ctx.stroke();
}
/* 
    Name: reloadCanvas
    Description: Draw all shape that is selected into canvase
    Input:
        None
    Output:
        None
*/
function reloadCanvas() {
    layoutIdentification();
    var total_price = 0.00;
    base_ctx.beginPath();
    wall_ctx.beginPath();
    base_ctx.clearRect(0, 0, base_canvas.width, base_canvas.height);
    wall_ctx.clearRect(0, 0, wall_canvas.width, wall_canvas.height);
    // draw the grid of the canvas
    draw_grid(base_ctx, base_canvas);
    draw_grid(wall_ctx, wall_canvas);
    // generate all shape based one selected item
    shapes.forEach(shape => {
        if (shape.type != "Wall") {
            draw_canvas(base_ctx, shape)
        } else {
            draw_canvas(wall_ctx, shape)
        }
    });
}

/* 
    Name: draw_canvas
    Description: Draw all shape that is selected into canvas
    Input:
        1. ctx: available values - ['wall_ctx', 'base_ctx']
        2. shape: {"name","model_id","x","y","canvas_x","canvas_y","height","price","installation","average_ep","type","canvas","master_uid","id"}
    Output:
        None
*/
function draw_canvas(ctx, shape) {
    // don't modify value of shape here
    shape.canvas_length = shape.length / (100) * shape_increment;
    shape.canvas_width = shape.width / (100) * shape_increment;
    ctx.save();
    if (shape.type == "Wall") {
        ctx.fillStyle = "white"
    } else {
        ctx.fillStyle = "lightgrey";
    }
    // Rotate shape 
    ctx.globalAlpha = 1.5;
    ctx.translate(shape.x + shape.canvas_length / 2, shape.y + shape.canvas_width / 2);
    ctx.rotate(shape.rotation);
    ctx.translate(-(shape.x + shape.canvas_length / 2), -(shape.y + shape.canvas_width / 2));
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
    ctx.translate(shape.x + shape.canvas_length / 2, shape.y + shape.canvas_width / 2);
    ctx.translate(-(shape.x + shape.canvas_length / 2), -(shape.y + shape.canvas_width / 2));
    ctx.restore();
    ctx.fillStyle = "#000"
    const max_dimension = 4500;
    if (selected_canvas == "wall" && shape.type != "Wall") {
        return;
    }
    // fill in text to the shape
    ctx.fillText(shape.name, shape.x + 2 - shape.tf, shape.y + shape.tf + 10)
    ctx.fillText("x: " + Math.round((shape.x - shape.tf) * max_dimension / 45 / shape_increment), shape.x + 2 - shape.tf, shape.y + shape.tf + 20)
    ctx.fillText("y: " + Math.round((shape.y + shape.tf) * max_dimension / 45 / shape_increment), shape.x + 2 - shape.tf, shape.y + shape.tf + 30)
}

let isDragging = false;
let selectedShape = null;
let offsetX, offsetY;

/* 
    Name: onMouseDown
    Description: On mouse click, lock the shape and allow dragging
    Input:
        1. e: MouseEvent
    Output:
        None
*/
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

/* 
    Name: onMouseUp
    Description: On mouse Up, unlock the shape and deny dragging
    Input:
        None
    Output:
        None
*/
function onMouseUp() {
    isDragging = false;
    selectedShape = null;
}

/* 
    Name: onMouseMove
    Description: On mouse selected shape and move, change coordinate of the shape and redraw in canvas
    Input:
        1. e: MouseEvent
    Output:
        None
*/
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
        var snapped = 0;
        // Ensure the shape doesn't move outside the canvas boundaries
        if (selectedShape.x - selectedShape.tf < snapThreshold) {
            selectedShape.x = 0 + selectedShape.tf;
            snapped = 1;
        }
        if (selectedShape.y + selectedShape.tf < snapThreshold) {
            selectedShape.y = 0 - selectedShape.tf;
            snapped = 1;
        }

        if (selectedShape.x + selectedShape.canvas_length - selectedShape.tf > canvas.width - snapThreshold) {
            selectedShape.x = canvas.width - selectedShape.canvas_length - selectedShape.tf;
            snapped = 1;
        }
        if (selectedShape.y + selectedShape.canvas_width + selectedShape.tf > canvas.height - snapThreshold) {
            selectedShape.y = canvas.height - selectedShape.canvas_width + selectedShape.tf;
            snapped = 1;
        }

        // Snap to the border if the shape is within a threshold distance

        for (const shape of shapes) {
            self_level = selectedShape.type == "Wall" ? 1 : 0
            other_level = shape.type == "Wall" ? 1 : 0
            if (self_level == other_level) {
                if (shape !== selectedShape) {
                    var selectedShape_min_x = selectedShape.x - selectedShape.tf
                    var shape_min_x = shape.x - shape.tf
                    var selectedShape_max_x = selectedShape.x - selectedShape.tf + selectedShape.canvas_width * Math.abs(Math.sin(selectedShape.rotation)) + selectedShape.canvas_length * Math.abs(Math.cos(selectedShape.rotation)) + snapThreshold
                    var shape_max_x = shape.x - shape.tf + shape.canvas_width * Math.abs(Math.sin(shape.rotation)) + shape.canvas_length * Math.abs(Math.cos(shape.rotation)) + snapThreshold

                    var selectedShape_min_y = selectedShape.y + selectedShape.tf
                    var shape_min_y = shape.y + shape.tf
                    var selectedShape_max_y = selectedShape.y + selectedShape.tf + selectedShape.canvas_length * Math.abs(Math.sin(selectedShape.rotation)) + selectedShape.canvas_width * Math.abs(Math.cos(selectedShape.rotation)) + snapThreshold
                    var shape_max_y = shape.y + shape.tf + shape.canvas_length * Math.abs(Math.sin(shape.rotation)) + shape.canvas_width * Math.abs(Math.cos(shape.rotation)) + snapThreshold

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
        reloadCanvas();
    }
}

/* 
    Name: onDoubleClick
    Description: On mouse doubleclick, remove shape and re-calculate price and draw shape in canvas
    Input:
        1. e: MouseEvent
    Output:
        None
*/
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
            total_price = calculateQuotation(4);
            if (shapes.length == 1) {
                updateParentTotalPrice(null);
            }

            if (total_price != 0) {
                updateParentTotalPrice(parseFloat(total_price, 2));
            } else {
                updateParentTotalPrice(null);
            }
            reloadCanvas();
            break;
        }
    }
}

/* 
    Name: onKeyDown
    Description: On keyboard 'CTRL' event, rotate selected shape 90 degree, redraw shape in canvas
    Input:
        1. e: KeyboardEvent
    Output:
        None
*/
function onKeyDown(e) {
    if (e.key == "Control" && selectedShape) {
        selectedShape.rotation += Math.PI * 90 / 180;
        if (selectedShape.rotation == Math.PI * 360 / 180) {
            selectedShape.rotation = 0;
        }
        selectedShape.tf = (selectedShape.canvas_width - selectedShape.canvas_length) / 2 * Math.abs(Math.sin(selectedShape.rotation))
        reloadCanvas();
    }
}

/* 
    Name: newDesign
    Description: Reset all shape, price and all shape in canvas
    Input:
        None
    Output:
        None
*/
function newDesign() {
    shapes = [];
    total_price = 0;
    moduletotal = 0;
    totalinstallationprice = 0;
    updateParentTotalPrice(null);
    reloadCanvas();
}
var items = [];

/* 
    Name: generate_3D_JSON
    Description: Reformat data into kjl format
    Input:
        None
    Output:
        1. items : [
            {productId,"position":{x,y,z},"size":{x,y,z},"rotation":{x,y,z},type,name,master_uid,id},
            ...
        ]

*/
function generate_3D_JSON() {
    items = [];
    var item_json;
    const wall_fixed_height = 1500;
    const max_dimension = 4500;
    shapes.forEach((shape) => {
        item_json = {
            'productId': shape.model_id,
            'position': {
                'x': ((shape.x - shape.tf) * max_dimension / 45 / shape_increment + shape.length / 2 * Math.abs(Math.cos(shape.rotation)) + shape.width / 2 * Math.abs(Math.sin(shape.rotation))),
                'y': -((shape.y + shape.tf) * max_dimension / 45 / shape_increment + shape.width / 2 * Math.abs(Math.cos(shape.rotation)) + shape.length / 2 * Math.abs(Math.sin(shape.rotation))),
                'z': shape.type == "Wall" ? shape.height / 2 + wall_fixed_height : shape.height / 2
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
            'master_uid': shape.master_uid,
            'id': shape.id
        }
        items.push(item_json)
    });
    // Check for overlaps
    var groupedObjects = {};
    items.forEach(function (object) {
        // Put as same category for checking
        if (object.type == "Tall") {
            object.type = "Base";
        }
        if (!groupedObjects[object.type]) {
            groupedObjects[object.type] = [];
        }
        groupedObjects[object.type].push(object);
    });
    // Check for overlaps within each group
    for (const type in groupedObjects) {
        const shapes = groupedObjects[type];
        for (let i = 0; i < shapes.length; i++) {
            for (let j = i + 1; j < shapes.length; j++) {
                if (checkShapesOverlap(shapes[i], shapes[j])) {
                    console.log(`Overlap detected within ${type} group.`);
                }
            }
        }
    }

    return { 'items': items }
}

/* 
    Name: checkShapesOverlap
    Description: Reformat data into kjl format
    Input:
        1. object1: available values - ['shapes']
        2. object2: available values - ['shapes']
    Output:
        1. items : [
            {productId,"position":{x,y,z},"size":{x,y,z},"rotation":{x,y,z},type,name,master_uid,id},
            ...
        ]

*/
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
    // Check for vertical overlap
    var yOverlap = ((eoly2 > eoly1 && eoly2 < y1) || (y2 > eoly1 && y2 < y1)) || ((y1 <= y2 && y1 > eoly2) || (eoly1 <= y2 && eoly1 > eoly2));
    return xOverlap && yOverlap;
}
/* 
    Name: filterSidebarItems
    Description: Based on insertion of user, filter item list to show
    Input:
        None
    Output:
        None

*/
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

function layoutIdentification() {
    var directionChanges = {}
    var layoutIdentified = {}
    var center = findCenter(shapes)
    directionChanges['base'] = countDirectionChanges(shapes.filter((shape) => shape.type != 'Wall'), center)
    directionChanges['wall'] = countDirectionChanges(shapes.filter((shape) => shape.type == 'Wall'), center)
    console.log(directionChanges)
    Object.keys(directionChanges).forEach((key) => {
        layoutIdentified[key] = LAYOUT_MAPPING[directionChanges[key]]
        document.getElementById(key + '_layout_identified').value = layoutIdentified[key]
    })
}

function countDirectionChanges(shapes, center) {
    // Initialize variables
    let previousAngle = 0;
    let directionChanges = 0;
    let sorted_shape = shapes.sort(function (previous, current) {
        return previous.x - previous.tf - (current.x - current.tf)
    })
    sorted_shape = sorted_shape.sort(function (previous, current) {
        const previousAngleToCenter = angleToCenter(previous, center)
        const currentAngleToCenter = angleToCenter(current, center)
        var angleDiff = - currentAngleToCenter + previousAngleToCenter;
        return angleDiff;
    })
    console.log(sorted_shape)
    sorted_shape.forEach((shape) => {
        console.log(angleToCenter(shape, center)*180/Math.PI)
    })
    // Loop through points starting from the 2nd point
    for (let i = 1; i < sorted_shape.length; i++) {
      const currentShape = sorted_shape[i];
      const previousShape = sorted_shape[i - 1];
      
      // Calculate the angle between the current and previous point
      const currentAngle = Math.atan2(currentShape.y - previousShape.y, currentShape.x - previousShape.x);
      if (i == 1) {
        previousAngle = currentAngle;
        continue;
      }
      // Calculate the change in angle
      const angleChange = currentAngle - previousAngle;
    //   // Normalize the angle change to be between -180 and 180 degrees
    //   const normalizedAngleChange = (angleChange + Math.PI) % (2 * Math.PI) - Math.PI;
      // Check if the change in angle is greater than 60 degrees
    if (Math.abs(angleChange) > Math.PI / 3) {
        directionChanges++;
    }
      // Update the previous angle
      previousAngle = currentAngle;
    }
    
    // Return the number of direction changes
    return directionChanges;
}

function findCenter(shapes) {
    let sumX = 0;
    let sumY = 0;
    for (const shape of shapes) {
      sumX += shape.x - shape.tf;
      sumY += shape.y + shape.tf;
    }
    
    const centerX = sumX / shapes.length;
    const centerY = sumY / shapes.length;
    return { x: centerX, y: centerY };
}

function angleToCenter(shape, center) {
    const dx = shape.x - shape.tf - center.x;
    const dy = shape.y + shape.tf - center.y;
    if (Math.abs(dy) < 0.001) {
        angle = dx < 0 ? Math.PI : 0
    } else {
        if ((dx < 0 && dy > 0) || (dx > 0 && dy < 0)) {
            angle = Math.atan2(dy, dx)
        } else {
            angle = Math.atan2(dy, dx)
        }
    }
    if (Math.abs(angle) < 0.001) {
        angle = 0
    }
    if (angle <= 0) {
        angle = 2 * Math.PI + angle;
    }

    return angle;
}


// Attach an event listener to the search input
document.getElementById('searchInput').addEventListener('input', filterSidebarItems);
