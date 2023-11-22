import { addShapeThreeD } from "./kubiq_ezykit_3d.js";
import { calculateQuotation, updateParentTotalPrice } from "./ezykit_share.js"

document.getElementById('base_button').addEventListener('click', () => {
    selectCanvas('base')
})
document.getElementById('module_base_button').addEventListener('click', () => {
    selectCanvas('base')
})
document.getElementById('wall_button').addEventListener('click', () => {
    selectCanvas('wall')
})
document.getElementById('module_wall_button').addEventListener('click', () => {
    selectCanvas('wall')
})
document.getElementById('three_d_button').addEventListener('click', () => {
    selectCanvas('three_d')
})
document.getElementById('module_tab').addEventListener('click', () => {
    openTab('module')
})
document.getElementById('kitchen_layout_tab').addEventListener('click', () => {
    openTab('kitchen_layout')
})
document.getElementById('resize_canvas_button').addEventListener('click', () => {
    resize_canvas()
})
document.getElementById('reset_canvas_button').addEventListener('click', () => {
    reset_canvas()
})
document.getElementById('new_design_button').addEventListener('click', () => {
    newDesign()
})
// set price if there is from quotation page
if (typeof grandTotal !== 'undefined' && grandTotal > 0) {
    updateParentTotalPrice(parseFloat(grandTotal, 2));
}
// global variables
var base_canvas, wall_canvas, base_ctx, wall_ctx, shapes, shape_increment;
var selected_canvas = "base";
var historicaluniqueid = []; // to store tag number (always 20 digit)
var arrayuniqueid = []; // to store converted tag number (between 1-2 digit)
var totalinstallationprice = 0; // for installation charge
var moduletotal = 0;

const objarraymodule = JSON.parse(arraymodule); // convert to javascript object
const objarraydescription = JSON.parse(arraydescription); // convert to javascript object
const objarrayprice = JSON.parse(arrayprice); // convert to javascript object
const objarrayepprice = JSON.parse(arrayepprices); // convert to javascript object
const objarrayinstallationprice = JSON.parse(arrayinstallationprice); // convert to javascript object

export { objarraymodule, objarraydescription, objarrayprice, objarrayepprice, objarrayinstallationprice }
init(); //first run

function init() {
    base_canvas = document.getElementById("base_dropzone");
    wall_canvas = document.getElementById("wall_dropzone");
    base_ctx = init_canvas(base_canvas);
    wall_ctx = init_canvas(wall_canvas);
    shapes = [];
    shape_increment = 0;
    reloadCanvas();
    selectCanvas('three_d');
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
var item_list_arr = []
Object.keys(item_array).forEach((type) => {
    catalogue_innerHTML += `<button class="btn btn-light btn-block text-left" type="button" data-toggle="collapse" data-target="#` + type + `Collapse" aria-expanded="` + (type == "Base" ? "true" : "false") + `" aria-controls="` + type + `Collapse">
                <i class="fas fa-chevron-down"></i>
                ` + type + `
            </button>
            <div class="collapse `+ (type == "Base" ? "show" : "") + `" id="` + type + `Collapse">
                <ul class="list-group" id="` + type + `-item-list-group">`;
    item_array[type].forEach((item) => {
        catalogue_innerHTML += `<li class="list-group-item btn btn-light" id="item_list` + item.id + `">
                    <div class="container">
                        <div class="text-wrap">
                            <span>` + item.name + ' (' + item.description + ')' + `</span>
                        </div>
                    </div>
                </li>`;
        item_list_arr.push(item)
    })
    catalogue_innerHTML += `</ul>
            </div>`;
})
catalogue.innerHTML = catalogue_innerHTML;
item_list_arr.forEach((item) => {
    document.getElementById('item_list' + item.id).setAttribute("item_data", JSON.stringify({
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
    }))
    document.getElementById('item_list' + item.id).addEventListener('click', (event) => {
        var shape_data = event.target.getAttribute("item_data")
        if (shape_data == null) {
            shape_data = event.target.offsetParent.getAttribute("item_data")
        }
        shape_data = JSON.parse(shape_data)
        addShape(shape_data)
    })
})

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
    closeAllCollapses();
    var dimension = canvas_string == "three_d" ? 3 : 2
    var focus = canvas_string;
    var not_focus = canvas_string == 'base' ? 'wall' : 'base';
    if (dimension == 2) {
        if (canvas_string == "base") {
            openCollapse('BaseCollapse');
            document.getElementById("base_dropzone").style.opacity = 1;
        } else {
            openCollapse('WallCollapse');
            document.getElementById("wall_dropzone").style.opacity = 0.8;
        }
        
        changeButtonColors(focus + '_button', '#08244c');
        changeButtonColors(not_focus + '_button', '#8D99A3');
        
        document.getElementById(not_focus + "_dropzone").style.zIndex = -1;
        document.getElementById(focus + "_dropzone").style.zIndex = 1;
        document.getElementById("three_d_container").style.zIndex = -2;
    } else {
        changeButtonColors('base_button', '#8D99A3');
        changeButtonColors('wall_button', '#8D99A3');
        changeButtonColors('three_d_button', '#08244c');
        
        document.getElementById("base_dropzone").style.zIndex = -2;
        document.getElementById("wall_dropzone").style.zIndex = -1;

        document.getElementById("three_d_container").style.zIndex = 1;
    }
    

    reloadCanvas();
}

/*
    Name: changeButtonColors
    Description: Changes all button with button_name to color
    Input:
        1. button_name: element name of the button
        2. color: desired color change to the button
    Output:
        None
*/
function changeButtonColors(button_name, color) {
    var elementsWithNameYes = document.getElementsByName(button_name);
    // Convert the NodeList to an array and set the background color of each element to color
    Array.from(elementsWithNameYes).forEach(function (element) {
        element.style.background = color;
    });
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
    if (selected_canvas != "three_d") {
        if (data.canvas == 'base') {
            selectCanvas('base');
        } else {
            selectCanvas('wall');
        }
    }
    var x = 0;
    var y = 0;
    var rotation = 0;
    var self_level, other_level;
    var total_price;
    // Spawn next to previous shape
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
    const new_shape = {
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
    }
    // Add data into shapes array for price calculation
    shapes.push(new_shape);
    

    total_price = calculateQuotation(4); //calculate price
    updateParentTotalPrice(total_price != 0 ? parseFloat(total_price, 2) : null); //update display price
    if (selected_canvas != "three_d") {
        reloadCanvas(); //draw in canvas
    } else {
        addShapeThreeD(new_shape)
    }
    
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
    var padding = 0;
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
    // clear canvas
    base_ctx.beginPath();
    wall_ctx.beginPath();
    base_ctx.clearRect(0, 0, base_canvas.width, base_canvas.height);
    wall_ctx.clearRect(0, 0, wall_canvas.width, wall_canvas.height);

    // draw the grid of the canvas
    draw_grid(base_ctx, base_canvas);
    draw_grid(wall_ctx, wall_canvas);

    // generate all shape based one selected item
    shapes.forEach(shape => {
        console.log(shape)
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
    // Rotate shape, draw shape border
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

    // draw shape
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
    const { canvas, mouseX, mouseY } = getMousePosition(e);

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
    Name: getMousePosition
    Description: Get the x and y of mouse in given canvas, depending on the event
    Input:
        1. e: event
    Output:
        1. {"canvas". "mouseX", "mouseY"}
*/
function getMousePosition(e) {
    var canvas;
    if (e.target.id == 'base_dropzone') {
        canvas = base_canvas;
    } else if (e.target.id == 'wall_dropzone') {
        canvas = wall_canvas;
    }
    const mouseX = e.clientX - canvas.getBoundingClientRect().left;
    const mouseY = e.clientY - canvas.getBoundingClientRect().top;

    return { canvas, mouseX, mouseY }
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
        var self_level, other_level;
        const { canvas, mouseX, mouseY } = getMousePosition(e);
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
        if (selectedShape.x - selectedShape.tf < snapThreshold) {
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
    const { canvas, mouseX, mouseY } = getMousePosition(e);

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
            
            updateParentTotalPrice(total_price != 0 ? parseFloat(total_price, 2) : null); //update display price

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

        // calculate transformation
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

// Attach an event listener to the search input
document.getElementById('searchInput').addEventListener('input', filterSidebarItems);

export { shapes }