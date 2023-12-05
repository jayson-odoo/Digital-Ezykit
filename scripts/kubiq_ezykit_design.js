// set price if there is from quotation page
if (typeof quotation_price !== 'undefined' && quotation_price > 0) {
    updateParentTotalPrice(parseFloat(quotation_price, 2));
}
// global variables
var base_canvas, wall_canvas, layout_canvas, worktop_canvas, base_ctx, wall_ctx, layout_ctx, worktop_ctx, shapes, shape_increment, walls;
var selected_canvas = "base";
var item_id = "";
var historicaluniqueid = []; // to store tag number (always 20 digit)
var arrayuniqueid = []; // to store converted tag number (between 1-2 digit)
var totalinstallationprice = 0; // for installation charge
var moduletotal = 0;
const max_dimension = 4500;

let isDragging = false;
let isAdjustingWall = false;
let isDrawing = false;
let canvas_resized = false;
let selectedShape = null;
let selectedWall = null;
let wallDrawn = false;
let filled_shape = false;
let offsetX, offsetY, startX, startY, endX, endY;

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

const BOUNDARY_MARGIN = 15;
init(); //first run

function init() {
    base_canvas = document.getElementById("base_dropzone");
    wall_canvas = document.getElementById("wall_dropzone");
    layout_canvas = document.getElementById("layout_dropzone");
    worktop_canvas = document.getElementById("worktop_dropzone");
    base_ctx = init_canvas(base_canvas);
    wall_ctx = init_canvas(wall_canvas);
    layout_ctx = init_canvas(layout_canvas);
    worktop_ctx = init_canvas(worktop_canvas);
    shapes = [];
    walls = [];
    shape_increment = 0;
    catalogue_list_generate();
    reloadCanvas();
    selectCanvas('layout');
}
// Define the input field names
var fieldNames = ["worktopUnitMeasurement", "worktopUnitPrice", "transportationDistance", "discountpercentage", "worktopcategory", "worktoptype", "worktopLabourSelection","doorColorSelection"];

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

document.addEventListener("keydown", onKeyDown);

// Assuming your canvas has the id "your_canvas_id"
const canvasXInput = document.getElementById("canvas_x");
const canvasYInput = document.getElementById("canvas_y");

// Add an event listener for the mousemove event
layout_canvas.addEventListener("mousemove", function (event) {
    // Get the mouse coordinates relative to the canvas
    const rect = layout_canvas.getBoundingClientRect();
    var mouseX = event.clientX - rect.left;
    var mouseY = event.clientY - rect.top;

    mouseX = mouseX * max_dimension / 45 / shape_increment;
    mouseY = mouseY * max_dimension / 45 / shape_increment;

    // Update the input values
    canvasXInput.value = mouseX.toFixed(2);
    canvasYInput.value = mouseY.toFixed(2);

});

// Add an event listener for the mouseout event
layout_canvas.addEventListener("mouseout", function () {
    // Reset the cursor style to default
    layout_canvas.style.cursor = "default";

    // Update the input values
    canvasXInput.value = 0.00;
    canvasYInput.value = 0.00;
});

function handleImageError(img) {
    // Set src image indicator to show that image is not there 
    img.src='images/image_indicator.png';
}

function catalogue_list_generate() {
    // Create list of item selection
    var catalogue = document.getElementById("catalogue");
    catalogue.innerHTML = '';
    var catalogue_innerHTML = '';
    Object.keys(item_array).forEach((type) => {
        catalogue_innerHTML += `<button class="btn btn-light btn-block text-left ` + (type == "Base" || type == "Wall" || type == "Worktop" ? "Kitchen" : "Wardrobe") + ` ` + type + ` " type="button" data-toggle="collapse" data-target="#` + type + `Collapse" aria-expanded="` + (type == "Base" ? "true" : "false") + `" aria-controls="` + type + `Collapse">
                <i class="fas fa-chevron-down"></i>
                ` + type + `
            </button>
            <div class="collapse `+ (type == "Base" ? "show" : "") + ` ` + (type == "Base" || type == "Wall" || type == "Worktop" ? "Kitchen" : "Wardrobe") + ` ` + type + `" id="` + type + `Collapse" >
                <ul class="list-group" id="` + type + `-item-list-group">`;
        item_array[type].forEach((item) => {
            catalogue_innerHTML += `<li class="list-group-item btn btn-light ` + type + ` ` + (type == "Base" || type == "Wall" || type == "Worktop" ? "Kitchen" : "Wardrobe") + `" onclick='addShape(` +
                JSON.stringify({
                    'name': item.name,
                    'model_id': item.model_id,
                    'x': item.width,
                    'y': item.type == 'Worktop' ? item.height : item.depth,
                    'canvas_x': item.width / shape_increment,
                    'canvas_y': item.depth / shape_increment,
                    'height': parseFloat(item.height),
                    'price': item.price,
                    'installation': item.installation,
                    'average_ep': item.average_ep,
                    'type': item.type,
                    'canvas': item.type == "Wall" ? "wall" : item.type == "Base" || item.type == "Tall" ? "base" : "worktop",
                    'master_uid': item.master_uid,
                    'id': item.id,
                    'kitchen_wardrobe': type == "Base" || type == "Wall" || type == "Worktop" ? "Kitchen" : "Wardrobe",
                    'item_code': item.item_code,
                    'description': item.description
                }) + `)' style="padding: 5px;">
                    <div class="container">
                        <div class="row">
                            <div class="col-3">
                            <img src="`+ item.master_img + `" alt="` + item.master_module + `" width="50" height="50" onerror="handleImageError(this)">
                            </div>
                            <div class="col">
                            <span>` + item.name + ' (' + (type == "Worktop" ? "L: " + item.height + "mm" : item.description) + ') RM' + item.price + `</span>
                            </div>
                        </div>
                    </div>
                </li>`;

        })
        catalogue_innerHTML += `</ul>
            </div>`;
    })
    catalogue.innerHTML = catalogue_innerHTML;
}

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
        1. canvas_string: available values - ['base', 'wall', 'worktop']
    Output:
        None
*/
function selectCanvas(canvas_string) {
    selected_canvas = canvas_string
    if (selected_canvas == "base") {
        closeAllCollapses();
        openCollapse('BaseCollapse');
        toggleVisibility('Kitchen');
        buttoncolor(['base_button'], '#08244c');
        buttoncolor(['worktop_button', 'wall_button'], '#8D99A3');
        document.getElementById("show_x").style.display = 'none';
        document.getElementById("show_y").style.display = 'none';
        document.getElementById("base_dropzone").style.opacity = 0.8
        document.getElementById("layout_dropzone").style.opacity = 1

        document.getElementById("wall_dropzone").style.zIndex = -2
        document.getElementById("layout_dropzone").style.zIndex = -1
        document.getElementById("worktop_dropzone").style.zIndex = -3
        document.getElementById("base_dropzone").style.zIndex = 1
    } else if (canvas_string == "wall") {
        closeAllCollapses();
        openCollapse('WallCollapse');
        toggleVisibility('Kitchen');
        buttoncolor(['wall_button'], '#08244c');
        buttoncolor(['worktop_button', 'base_button'], '#8D99A3');
        document.getElementById("show_x").style.display = 'none';
        document.getElementById("show_y").style.display = 'none';
        document.getElementById("wall_dropzone").style.opacity = 0.7
        document.getElementById("base_dropzone").style.opacity = 0.7
        document.getElementById("layout_dropzone").style.opacity = 1

        document.getElementById("wall_dropzone").style.zIndex = 1
        document.getElementById("layout_dropzone").style.zIndex = -2
        document.getElementById("worktop_dropzone").style.zIndex = -3
        document.getElementById("base_dropzone").style.zIndex = -1
    } else if (canvas_string == "layout") {
        var elementsWithNameYes = document.getElementsByName('wall_button');
        document.getElementById("layout_dropzone").style.opacity = 0.6
        document.getElementById("base_dropzone").style.opacity = 0.7
        document.getElementById("wall_dropzone").style.opacity = 0.4
        document.getElementById("worktop_dropzone").style.opacity = 0.7
        // Convert the NodeList to an array and set the background color of each element to orange
        Array.from(elementsWithNameYes).forEach(function (element) {
            element.style.background = '#08244c';
        });
        var elementsWithNameYes = document.getElementsByName('base_button');
        // Convert the NodeList to an array and set the background color of each element to orange
        Array.from(elementsWithNameYes).forEach(function (element) {
            element.style.background = '#08244c';
        });
        document.getElementById("show_x").style.display = 'block';
        document.getElementById("show_y").style.display = 'block';
        document.getElementById("base_dropzone").style.zIndex = -2
        document.getElementById("wall_dropzone").style.zIndex = -1
        document.getElementById("layout_dropzone").style.zIndex = 1
        document.getElementById("worktop_dropzone").style.zIndex = -3
    } else if (canvas_string == "worktop") {
        closeAllCollapses();
        openCollapse('WorktopCollapse');
        toggleVisibility('Worktop');
        buttoncolor(['worktop_button'], '#08244c');
        buttoncolor(['wall_button', 'base_button'], '#8D99A3');
        document.getElementById("show_x").style.display = 'none';
        document.getElementById("show_y").style.display = 'none';
        document.getElementById("wall_dropzone").style.opacity = 0.7
        document.getElementById("base_dropzone").style.opacity = 0.7
        document.getElementById("worktop_dropzone").style.opacity = 0.7
        document.getElementById("layout_dropzone").style.opacity = 1

        document.getElementById("wall_dropzone").style.zIndex = -3
        document.getElementById("layout_dropzone").style.zIndex = -2
        document.getElementById("worktop_dropzone").style.zIndex = 1
        document.getElementById("base_dropzone").style.zIndex = -1
    }
    reloadCanvas();
}

// Function to toggle visibility based on the class
function toggleVisibility(className) {
    if (className) {
        $('#catalogue li:not(.' + className + ')').hide();
        $('#catalogue button:not(.' + className + ')').hide();
        $('#catalogue li.' + className).show();
        $('#catalogue button.' + className).show();
    } else {
        $('#catalogue li').show();
        $('#catalogue button.').show();
    }
}

function buttoncolor(name_list, color) {
    $.each(name_list, function (index, name) {
        var elementsWithNameYes = document.getElementsByName(name);
        // Convert the NodeList to an array and set the background color of each element to orange
        Array.from(elementsWithNameYes).forEach(function (element) {
            element.style.background = color;
        });
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
    if (tabName == 'kitchen_layout') {
        selectCanvas('layout')
    } else if (tabName == 'module') {
        selectCanvas('base')
    }

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
        $('.nav-link:eq(1)').addClass('active');
    } else {
        $('.nav-link:eq(0)').addClass('active');
    }
}

/* 
    Name: openCollapse
    Description: Expand the selected list of dropdown
    Input:
        1. collapseId: available values - ['WallCollapse', 'BaseCollapse','WorktopCollapse']
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
        1. canvas: available values - ['document.getElementById("base_dropzone")', 'document.getElementById("wall_dropzone")', 'document.getElementById("worktop_dropzone")']
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
    } else if (data.canvas == 'wall') {
        selectCanvas('wall');
        canvas = wall_canvas;
    } else if (data.canvas == 'worktop') {
        selectCanvas('worktop');
        canvas = worktop_canvas;
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
                x += shape.canvas_length + shape.tf;
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
        "id": data.id,
        "kitchen_wardrobe": data.kitchen_wardrobe,
        "item_code": data.item_code,
        "description": data.description
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
        2. canvas: available values - ['wall_canvas','base_canvas','worktop_canvas']
    Output:
        None
*/
function draw_grid(ctx, canvas) {
    const container_width = document.getElementById('content').clientWidth
    padding = 0;
    var counter = 0;
    // decide the biggest width and height to be set for grid
    if ($("#length").val() >= $("#width").val()) {
        shape_increment = canvas.width / ($("#length").val() * 45 / max_dimension);
        canvas.height = shape_increment * ($("#length").val() * 45 / max_dimension);
    } else {
        shape_increment = canvas.height / ($("#width").val() * 45 / max_dimension);
        const width = shape_increment * ($("#width").val() * 45 / max_dimension);

        if (width >= container_width) {
            canvas.width = container_width;
            shape_increment = canvas.width / ($("#length").val() * 45 / max_dimension);
            canvas.height = shape_increment * ($("#width").val() * 45 / max_dimension);
        } else {
            canvas.width = shape_increment * ($("#width").val() * 45 / max_dimension);
        }
    }
    // loop for the width grid
    ctx.strokeStyle = "#cdd1ce";
    for (var x = 0; x <= canvas.width; x += shape_increment) {
        ctx.lineWidth = counter % 10 == 0 ? 3 : 1
        ctx.beginPath();
        ctx.moveTo(x + padding, padding);
        ctx.lineTo(x + padding, canvas.height + padding);
        ctx.stroke();
        counter += 1;
    }
    counter = 0;
    // loop for the height grid
    for (var x = 0; x <= canvas.height; x += shape_increment) {
        ctx.lineWidth = counter % 10 == 0 ? 3 : 1
        ctx.beginPath();
        ctx.moveTo(padding, x + padding);
        ctx.lineTo(canvas.width + padding, x + padding);
        ctx.stroke();
        counter += 1;
    }


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
    var total_price = 0.00;
    base_ctx.beginPath();
    wall_ctx.beginPath();
    layout_ctx.beginPath();
    worktop_ctx.beginPath();
    base_ctx.clearRect(0, 0, base_canvas.width, base_canvas.height);
    wall_ctx.clearRect(0, 0, wall_canvas.width, wall_canvas.height);
    layout_ctx.clearRect(0, 0, layout_canvas.width, layout_canvas.height);
    worktop_ctx.clearRect(0, 0, worktop_canvas.width, worktop_canvas.height);
    // draw the grid of the canvas
    draw_grid(base_ctx, base_canvas);
    draw_grid(wall_ctx, wall_canvas);
    draw_grid(layout_ctx, layout_canvas);
    draw_grid(worktop_ctx, worktop_canvas);
    // generate all shape based one selected item
    shapes.forEach(shape => {
        if (shape.type == "Base" || shape.type == "Tall") {
            draw_canvas(base_ctx, shape)
        } else if (shape.type == "Wall") {
            draw_canvas(wall_ctx, shape)
        } else if (shape.type == "Worktop") {
            draw_canvas(worktop_ctx, shape)
        }
    });

    drawWalls(walls, selectedWall)
    fillEnclosedArea(layout_ctx, layout_canvas, walls)
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
    if (shape.type != "Worktop") {
        ctx.strokeStyle = "#5bc0de";
        ctx.lineWidth = 5;
    }

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
    var ctx;
    if (e.target.id == 'base_dropzone') {
        canvas = base_canvas;
        ctx = base_ctx;
    } else if (e.target.id == 'wall_dropzone') {
        canvas = wall_canvas;
        ctx = wall_ctx;
    } else if (e.target.id == 'layout_dropzone') {
        canvas = layout_canvas;
        ctx = layout_ctx
    } else if (e.target.id == 'worktop_dropzone') {
        canvas = worktop_canvas;
        ctx = worktop_ctx;
    }
    const mouseX = e.clientX - canvas.getBoundingClientRect().left;
    const mouseY = e.clientY - canvas.getBoundingClientRect().top;

    if (canvas != layout_canvas) {
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
    } else {
        if (wallDrawn) {
            for (let i = walls.length - 1; i >= 0; i--) {
                const wall = walls[i];
                var selected = false;
                if (wall.startX == wall.endX) {
                    if (
                        mouseX >= Math.min(wall.startX, wall.endX) - ctx.lineWidth / 2 &&
                        mouseX <= Math.max(wall.startX, wall.endX) + ctx.lineWidth / 2 &&
                        mouseY >= Math.min(wall.startY, wall.endY) &&
                        mouseY <= Math.max(wall.startY, wall.endY)
                    ) {
                        selected = true;
                    }
                } else if (wall.startY == wall.endY) {
                    if (
                        mouseX >= Math.min(wall.startX, wall.endX) &&
                        mouseX <= Math.max(wall.startX, wall.endX) &&
                        mouseY >= Math.min(wall.startY, wall.endY) - ctx.lineWidth / 2 &&
                        mouseY <= Math.max(wall.startY, wall.endY) + ctx.lineWidth / 2
                    ) {
                        selected = true;
                    }
                }
                if (selected) {
                    isAdjustingWall = true;
                    selectedWall = wall;
                    offsetX = mouseX - wall.startX;
                    offsetY = mouseY - wall.startY;
                    break;
                }
            }
        } else if (!wallDrawn && canvas_resized) {
            if (isDrawing) {
                if (walls.length > 0) {
                    if (endX == walls[walls.length - 1].endX && walls[walls.length - 1].endX == walls[walls.length - 1].startX) {
                        walls[walls.length - 1].endY = endY
                    } else if (endY == walls[walls.length - 1].endY && walls[walls.length - 1].endY == walls[walls.length - 1].startY) {
                        walls[walls.length - 1].endX = endX
                    } else {
                        walls.push({
                            "startX": startX,
                            "startY": startY,
                            "endX": endX,
                            "endY": endY,
                            "type": startX == endX ? "vertical" : "horizontal"
                        })
                    }
                } else {
                    walls.push({
                        "startX": startX,
                        "startY": startY,
                        "endX": endX,
                        "endY": endY,
                        "type": startX == endX ? "vertical" : "horizontal"
                    })
                }

            }
            // Check if the user clicked on one of the four boundaries to quit drawing
            const boundaryClicked = isBoundaryClicked(e.clientX - canvas.getBoundingClientRect().left, e.clientY - canvas.getBoundingClientRect().top, canvas, BOUNDARY_MARGIN);
            if (walls.length == 0) {
                if (boundaryClicked) {
                    startX = e.clientX - canvas.getBoundingClientRect().left;
                    startY = e.clientY - canvas.getBoundingClientRect().top;
                    if (startX < BOUNDARY_MARGIN) {
                        startX = 0;
                    }
                    if (startX > (canvas.width - BOUNDARY_MARGIN)) {
                        startX = canvas.width;
                    }

                    if (startY < BOUNDARY_MARGIN) {
                        startY = 0;
                    }
                    if (startY > (canvas.height - BOUNDARY_MARGIN)) {
                        startY = canvas.height;
                    }
                    isDrawing = true;
                }
            } else {
                if (boundaryClicked) {
                    var obj = closeLoop(ctx, canvas, walls);
                    walls = obj.walls;
                    isDrawing = false;
                    wallDrawn = true;
                    drawWalls(walls)
                    fillEnclosedArea(ctx, canvas, walls, obj.endPoint);
                    // draw_grid(ctx, canvas)

                } else {
                    startX = walls[walls.length - 1].endX;
                    startY = walls[walls.length - 1].endY;
                }
            }
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
    isAdjustingWall = false;

    selectedShape = null;
    selectedWall = null;
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
    if (isDrawing) {
        if (e.target.id == 'base_dropzone') {
            canvas = base_canvas;
        } else if (e.target.id == 'wall_dropzone') {
            canvas = wall_canvas;
        } else if (e.target.id == 'layout_dropzone') {
            canvas = layout_canvas
        }
        endX = e.clientX - canvas.getBoundingClientRect().left;
        endY = e.clientY - canvas.getBoundingClientRect().top;

        // Calculate the corrected end coordinates to ensure horizontal or vertical lines
        endX = Math.abs(endX - startX) > Math.abs(endY - startY) ? endX : startX;
        endY = Math.abs(endX - startX) > Math.abs(endY - startY) ? startY : endY;
        if (endX < BOUNDARY_MARGIN) {
            endX = 0;
        }
        if (endX > (canvas.width - BOUNDARY_MARGIN)) {
            endX = canvas.width;
        }

        if (endY < BOUNDARY_MARGIN) {
            endY = 0;
        }
        if (endY > (canvas.height - BOUNDARY_MARGIN)) {
            endY = canvas.height;
        }
        draw_grid(layout_ctx, layout_canvas)
        drawLine(layout_ctx, startX, startY, endX, endY);
        drawWalls(walls, selectedWall)
    }

    if (isAdjustingWall && selectedWall && canvas_resized) {
        canvas = layout_canvas
        const mouseX = e.clientX - canvas.getBoundingClientRect().left;
        const mouseY = e.clientY - canvas.getBoundingClientRect().top;
        // Calculate the new position while keeping the shape within the canvas boundaries
        if (selectedWall.startX == selectedWall.endX) {
            selectedWall.startX = mouseX - offsetX
            selectedWall.endX = selectedWall.startX
        } else if (selectedWall.startY == selectedWall.endY) {
            selectedWall.startY = mouseY - offsetY
            selectedWall.endY = selectedWall.startY
        }
        reloadCanvas()
    }

    if (isDragging && selectedShape) {
        var canvas;
        if (e.target.id == 'base_dropzone') {
            canvas = base_canvas;
        } else if (e.target.id == 'wall_dropzone') {
            canvas = wall_canvas;
        } else if (e.target.id == 'layout_dropzone') {
            canvas = layout_canvas
        } else if (e.target.id == 'worktop_dropzone') {
            canvas = worktop_canvas;
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
    } else if (e.target.id == 'layout_dropzone') {
        canvas = layout_canvas
    } else if (e.target.id == 'worktop_dropzone') {
        canvas = worktop_canvas;
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
            'id': shape.id,
            'kitchen_wardrobe': shape.kitchen_wardrobe,
            'item_code': shape.item_code,
            'price': shape.price,
            'description': shape.description
        }
        items.push(item_json)
    });
    // Check for overlaps
    var groupedObjects = {};
    items.forEach(function (object) {
        // Put as same category for checking
        if (!groupedObjects[object.canvas]) {
            groupedObjects[object.canvas] = [];
        }
        groupedObjects[object.canvas].push(object);
    });
    // Check for overlaps within each group
    for (const canvas in groupedObjects) {
        const shapes = groupedObjects[canvas];
        for (let i = 0; i < shapes.length; i++) {
            for (let j = i + 1; j < shapes.length; j++) {
                if (checkShapesOverlap(shapes[i], shapes[j])) {
                    console.log(`Overlap detected within ${canvas} group.`);
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
    directionChanges['base'] = countDirectionChanges(shapes.filter((shape) => shape.type == 'Base' || shape.type == 'Tall'), center)
    directionChanges['wall'] = countDirectionChanges(shapes.filter((shape) => shape.type == 'Wall'), center)
    Object.keys(directionChanges).forEach((key) => {
        layoutIdentified[key] = LAYOUT_MAPPING[directionChanges[key]]
        // document.getElementById(key + '_layout_identified').value = layoutIdentified[key]
    })
    return directionChanges;
}

function plinthLengthCalculation() {
    let plinthLength = plinth_cap = 0;
    let sorted_shape = shapes.filter((shape) => shape.type == "Base" || shape.type == "Tall")
    var center = findCenter(sorted_shape)
    sorted_shape = sorted_shape.sort(function (previous, current) {
        return previous.x - previous.tf - (current.x - current.tf)
    })
    sorted_shape = sorted_shape.sort(function (previous, current) {
        const previousAngleToCenter = angleToCenter(previous, center)
        const currentAngleToCenter = angleToCenter(current, center)
        var angleDiff = - currentAngleToCenter + previousAngleToCenter;
        return angleDiff;
    })
    for (let i = 0; i < sorted_shape.length; i++) {
        const currentShape = sorted_shape[i];
        var previousShape;
        if (i == 0) {
            previousShape = sorted_shape[sorted_shape.length - 1]
        } else {
            previousShape = sorted_shape[i - 1];
        }
        if (currentShape.rotation - previousShape.rotation < Math.PI && currentShape.rotation % Math.PI != previousShape.rotation % Math.PI) {
            plinthLength += parseFloat(currentShape.length) - parseFloat(previousShape.width)
            plinth_cap ++;

        } else {
            plinthLength += parseFloat(currentShape.length)
        }
    }
    return {
        'kitchen': {
            'name': plinth_array[0].name,
            'description': plinth_array[0].description,
            'length': plinthLength / 1000,
            'uom': plinth_array[0].uom,
            'unit_price': plinth_array[0].price,
            'plinth_cap': plinth_cap
        }
    }
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
    // Loop through points starting from the 2nd point
    for (let i = 1; i < sorted_shape.length; i++) {
        const currentShape = sorted_shape[i];
        const previousShape = sorted_shape[i - 1];

        if (currentShape.rotation != previousShape.rotation) {
            directionChanges++;
        }
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


function drawLine(ctx, x1, y1, x2, y2) {
    const length = (Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2))) * max_dimension / 45 / shape_increment;
    const midX = (x1 + x2) / 2;
    const midY = (y1 + y2) / 2;

    // Offset for displaying text below the line
    const textOffset = 20;

    ctx.beginPath();
    ctx.strokeStyle = "black";
    ctx.lineWidth = 5;
    ctx.moveTo(x1, y1);
    ctx.lineTo(x2, y2);
    ctx.stroke();

    ctx.fillStyle = "black";
    ctx.font = "12px Arial";
    if (x1 === x2) {  // Vertical line
        // Display length in the middle and at the bottom
        ctx.fillText(`${length.toFixed(2)}`, midX, midY + 15);
    } else if (y1 === y2) {  // Horizontal line
        // Display length on the right and in the middle
        ctx.fillText(`${length.toFixed(2)}`, midX, midY + 15);
    }

}

function isBoundaryClicked(x, y, canvas, boundaryMargin) {
    return (
        x <= boundaryMargin ||
        x >= canvas.width - boundaryMargin ||
        y <= boundaryMargin ||
        y >= canvas.height - boundaryMargin
    );
}

function fillEnclosedArea(ctx, canvas, walls, endPoint) {
    if (walls.length > 0) {
        ctx.fillStyle = 'rgb(173, 94, 57)'; // Wood-like color
        ctx.beginPath();
        const fillStartX = walls[0].startX;
        const fillStartY = walls[0].startY;
        ctx.lineTo(fillStartX, fillStartY)
        for (const wall of walls) {
            ctx.lineTo(wall.endX, wall.endY);
        }
        ctx.closePath();
        ctx.fill();
        filled_shape = true;
    } else {
        filled_shape = false;
    }
    return
}

function configure_wall() {
    canvas_resized = true;
    document.getElementById('instruction_text').innerHTML = "2. Wall definition"
    document.getElementById('resize_container').style.display = "none"
    document.getElementById('kitchen_layout_button_row').innerHTML = `
                    <div class="col-sm-12">
                        <button class="btn btn-primary btn-block" class="form-control"
                            onclick="showResizeCanvas()">Back</button>
                    </div>
                    <div class="col-sm-12">
                        <button class="btn btn-secondary btn-block" style="background-color:#8D99A3;"
                            class="form-control" onclick="showModuleTab()">
                            Next
                        </button>
                    </div>
                    <div class="col-sm-12">
                        <button class="btn btn-secondary btn-block" style="background-color:#8D99A3;"
                            class="form-control" onclick="reset_wall()">
                            Reset
                        </button>
                    </div>`
    selectCanvas('layout')
    if (walls.length > 0) {
        drawWalls(walls, selectedWall);
        fillEnclosedArea(layout_ctx, layout_canvas, walls)
    }
}

function showResizeCanvas() {
    canvas_resized = false;
    document.getElementById('instruction_text').innerHTML = "1. Kitchen Size"
    document.getElementById('resize_container').style.display = "block"
    document.getElementById('kitchen_layout_button_row').innerHTML = `
                    <div class="col-sm-12">
                        <button class="btn btn-primary btn-block" class="form-control"
                            onclick="resize_canvas()">Apply</button>
                    </div>
                    <div class="col-sm-12">
                        <button class="btn btn-primary btn-block" class="form-control"
                            onclick="configure_wall()">Configure Wall</button>
                    </div>
                    <div class="col-sm-12">
                        <button class="btn btn-secondary btn-block" style="background-color:#8D99A3;"
                            class="form-control" onclick="reset_canvas()">
                            Reset
                        </button>
                    </div>`
}

function showModuleTab() {
    if(filled_shape){
        // document.getElementById('module_tab_button').style.display = "block";
        document.getElementById('module_tab_button').style.display = "block";
        showResizeCanvas();
        openTab('module')
        selectCanvas('base')
    } else {
        alert('Wall is not configured completely.Please make sure the wall is closed and filled with color.');
    }

}

function drawWalls(walls, selectedWall) {
    let wall;
    for (let i = 0; i < walls.length; i++) {
        wall = walls[i]
        if (selectedWall) {
            if (selectedWall == wall) {
                if (i == 0) {
                    walls[walls.length - 1].endX = wall.startX
                    walls[walls.length - 1].endY = wall.startY
                } else {
                    walls[i - 1].endX = wall.startX
                    walls[i - 1].endY = wall.startY
                }
                if (i < walls.length - 1) {
                    walls[i + 1].startX = wall.endX
                    walls[i + 1].startY = wall.endY
                }
            }
        }
        drawLine(layout_ctx, wall.startX, wall.startY, wall.endX, wall.endY);
    }

}

function reset_wall() {
    walls = []
    wallDrawn = false;
    isDrawing = false;
    reloadCanvas();
}

function infillIdentification() {
    var out_of_bound = false;
    var infill_no = {
        'long': {
            'unit_price': infill_array[1].price,
            'qty': 0,
            'description': infill_array[1].description,
            'name': infill_array[1].name
        },
        'short': {
            'unit_price': infill_array[0].price,
            'qty': 0,
            'description': infill_array[0].description,
            'name': infill_array[0].name
        },
        'lnc_end_cap': 0
    };
    var directionChanges = layoutIdentification();
    Object.keys(directionChanges).forEach((key) => {
        infill_no.short.qty += directionChanges[key] * 2
    })
    let temporay_infill = 0;
    shapes.forEach((shape) => {
        // get shrink down length and width of shape
        var length = parseFloat(shape.length * Math.abs(Math.cos(shape.rotation)) + shape.width * Math.abs(Math.sin(shape.rotation))) / (max_dimension / 45 / shape_increment);
        var width = parseFloat(shape.width * Math.abs(Math.cos(shape.rotation)) + shape.length * Math.abs(Math.sin(shape.rotation))) / (max_dimension / 45 / shape_increment);

        // get coordinate of 4 point of shape
        const shapePoints = [
            { x: shape.x - shape.tf, y: shape.y + shape.tf},
            { x: shape.x - shape.tf + length, y: shape.y + shape.tf },
            { x: shape.x - shape.tf, y: shape.y + shape.tf + width },
            { x: shape.x - shape.tf + length, y: shape.y + shape.tf + width }
            // Add more shape points as needed
        ];
        if (shape.type != 'Worktop') {
            // Check if all four points of the shape are within the walls area
            const areAllPointsInWallsArea = shapePoints.every(point => isPointInPolygon(point, walls));
            // get infill when all four points of shape is in the walls
            if (areAllPointsInWallsArea) {
                for (const wall of walls) {
                    if (((shape.rotation == 0 || shape.rotation == Math.PI) && wall.type == "horizontal") ||
                        ((shape.rotation == Math.PI / 2 || shape.rotation == 3 * Math.PI / 2) && wall.type == "vertical")) {
                        continue;
                    }
                    const distance = distancePointToLine(shape, wall);
                    // console.log("Wall")
                    // console.log(wall)
                    // console.log("distance " + distance)
                    if (shape.rotation == 0 || shape.rotation == Math.PI) {
                        if (wall.startX < shape.x) {
                            if (distance < 100 && distance > 10) {
                                console.log("Vertical left distance : " + distance)
                                console.log(shape)
                                console.log(wall)
                                if (shape.type == "Tall") {
                                    infill_no.long.qty++
                                } else {
                                    infill_no.short.qty++
                                }
                            }
                        } else if (wall.startX > shape.x) {
                            if (distance - shape.length < 100 && distance - shape.length > 10) {
                                console.log("Vertical right distance : " + (distance - shape.length))
                                console.log(shape)
                                console.log(wall)
                                if (shape.type == "Tall") {
                                    infill_no.long.qty++
                                } else {
                                    infill_no.short.qty++
                                }
                            }
                        }
                    } else if (shape.rotation == Math.PI / 2 || shape.rotation == 3 * Math.PI / 2) {
                        if (wall.startY < shape.y) {
                            if (distance < 100 && distance > 10) {
                                console.log("Horizontal top distance : " + distance)
                                console.log(shape)
                                console.log(wall)
                                if (shape.type == "Tall") {
                                    infill_no.long.qty++
                                } else {
                                    infill_no.short.qty++
                                }
                            }
                        } else if (wall.startY > shape.y) {
                            if (distance - shape.length < 100 && distance - shape.length > 10) {
                                console.log("Horizontal bottom distance : " + (distance - shape.length))
                                console.log(shape)
                                console.log(wall)
                                if (shape.type == "Tall") {
                                    infill_no.long.qty++
                                } else {
                                    infill_no.short.qty++
                                }
                            }
                        }
                    }
                    
                }
                temporay_infill++;
            } else {
                out_of_bound = true;
                console.log(shape.name);
                console.log(shape.type + " : At least one point of the shape is outside the walls area.");
            }
        }
    });
    infill_no.lnc_end_cap = 2 - temporay_infill; //Calculate the lnc end cap that is needed, Max 2
    return infill_no;
}

// Function to check if a point is within a polygon
function isPointInPolygon(point, polygon) {
    const x = point.x;
    const y = point.y;

    let inside = false;
    for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
        const xi = polygon[i].startX;
        const yi = polygon[i].startY;
        const xj = polygon[j].startX;
        const yj = polygon[j].startY;

        const intersect = ((yi > y) !== (yj > y)) &&
            (x < ((xj - xi) * (y - yi)) / (yj - yi) + xi);
        if (intersect) inside = !inside;
    }

    return inside;
}

function distancePointToLine(shape, wall) {
    const shapeEndY = shape.y + shape.tf + parseFloat(shape.length);
    const shapeEndX = shape.x - shape.tf + parseFloat(shape.length);
    Number.prototype.between = function (a, b) {
        var min = Math.min.apply(Math, [a, b]),
            max = Math.max.apply(Math, [a, b]);
        return this > min && this < max;
    };

    // Check if shape is within the y-range of the wall
    if (shape.rotation == 0 || shape.rotation == Math.PI) {
        console.log("shape end y : " + shapeEndY)
        console.log("min " + (wall.startY * max_dimension / 45 / shape_increment))
        console.log("max " + (wall.endY * max_dimension / 45 / shape_increment))
        if (shapeEndY.between(wall.startY * max_dimension / 45 / shape_increment, wall.endY * max_dimension / 45 / shape_increment)) {
            const numerator = Math.abs((wall.endX - wall.startX) * (wall.startY - (shape.y + shape.tf)) - (wall.startX - (shape.x - shape.tf)) * (wall.endY - wall.startY));
            const denominator = Math.sqrt(Math.pow(wall.endX - wall.startX, 2) + Math.pow(wall.endY - wall.startY, 2));
    
            return numerator / denominator * max_dimension / 45 / shape_increment;
        }
    } else if (shape.rotation == Math.PI / 2 || shape.rotation == 3 * Math.PI / 2) {
        if (shapeEndX.between(wall.startX * max_dimension / 45 / shape_increment, wall.endX * max_dimension / 45 / shape_increment)) {
            const numerator = Math.abs((wall.endX - wall.startX) * (wall.startY - (shape.y + shape.tf)) - (wall.startX - (shape.x - shape.tf)) * (wall.endY - wall.startY));
            const denominator = Math.sqrt(Math.pow(wall.endX - wall.startX, 2) + Math.pow(wall.endY - wall.startY, 2));
    
            return numerator / denominator * max_dimension / 45 / shape_increment;
        }
    } 
    return -1;
}

function closeLoop(ctx, canvas, walls) {
    const fillStartX = walls[0].startX;
    const fillStartY = walls[0].startY;
    const fillEndX = walls[walls.length - 1].endX;
    const fillEndY = walls[walls.length - 1].endY;
    var endPoint;
    if (fillStartX == 0 && fillEndY == 0) {
        endPoint = "TL"
    } else if (fillStartX == 0 && fillEndY == canvas.height) {
        endPoint = "BL"
    } else if (fillStartX == 0 && fillEndX == canvas.width) {
        endPoint = "T"
    } else if (fillStartY == canvas.height && fillEndY == 0) {
        endPoint = "L"
    } else if (fillStartY == 0 && fillEndX == 0) {
        endPoint = "TL"
    } else if (fillStartY == 0 && fillEndX == canvas.width) {
        endPoint = "TR"
    } else if (fillStartY == 0 && fillEndY == canvas.height) {
        endPoint = "L"
    } else if (fillStartY == 0 && fillEndY == 0) {
        endPoint = "TN"
    } else if (fillStartX == canvas.width && fillEndY == 0) {
        endPoint = "TR"
    } else if (fillStartX == canvas.width && fillEndY == canvas.height) {
        endPoint = "BR"
    } else if (fillStartX == canvas.width && fillEndX == 0) {
        endPoint = "T"
    } else if (fillStartX == canvas.width && fillEndX == canvas.width) {
        endPoint = "RN"
    } else if (fillStartY == canvas.height && fillEndX == 0) {
        endPoint = "BL"
    } else if (fillStartY == canvas.height && fillEndX == canvas.width) {
        endPoint = "BR"
    } else if (fillStartY == canvas.height && fillEndY == 0) {
        endPoint = "LN"
    } else if (fillStartY == canvas.height && fillEndY == canvas.height) {
        endPoint = "BN"
    }

    if (endPoint == "TL") {
        walls.push({
            "startX": walls[walls.length - 1].endX,
            "startY": walls[walls.length - 1].endY,
            "endX": 0,
            "endY": 0,
        })
        walls[walls.length - 1].type = walls[walls.length - 1].startX == walls[walls.length - 1].endX ? "vertical" : "horizontal"
        walls.push({
            "startX": 0,
            "startY": 0,
            "endX": walls[0].startX,
            "endY": walls[0].startY,
        })
        walls[walls.length - 1].type = walls[walls.length - 1].startX == walls[walls.length - 1].endX ? "vertical" : "horizontal"
    } else if (endPoint == "TR") {
        walls.push({
            "startX": walls[walls.length - 1].endX,
            "startY": walls[walls.length - 1].endY,
            "endX": canvas.width,
            "endY": 0,
        })
        walls[walls.length - 1].type = walls[walls.length - 1].startX == walls[walls.length - 1].endX ? "vertical" : "horizontal"
        walls.push({
            "startX": canvas.width,
            "startY": 0,
            "endX": walls[0].startX,
            "endY": walls[0].startY,
        })
        walls[walls.length - 1].type = walls[walls.length - 1].startX == walls[walls.length - 1].endX ? "vertical" : "horizontal"
    } else if (endPoint == "BL") {
        walls.push({
            "startX": walls[walls.length - 1].endX,
            "startY": walls[walls.length - 1].endY,
            "endX": 0,
            "endY": canvas.height,
        })
        walls[walls.length - 1].type = walls[walls.length - 1].startX == walls[walls.length - 1].endX ? "vertical" : "horizontal"
        walls.push({
            "startX": 0,
            "startY": canvas.height,
            "endX": walls[0].startX,
            "endY": walls[0].startY,
        })
        walls[walls.length - 1].type = walls[walls.length - 1].startX == walls[walls.length - 1].endX ? "vertical" : "horizontal"
    } else if (endPoint == "BR") {
        walls.push({
            "startX": walls[walls.length - 1].endX,
            "startY": walls[walls.length - 1].endY,
            "endX": canvas.width,
            "endY": canvas.height,
        })
        walls[walls.length - 1].type = walls[walls.length - 1].startX == walls[walls.length - 1].endX ? "vertical" : "horizontal"
        walls.push({
            "startX": canvas.width,
            "startY": canvas.height,
            "endX": walls[0].startX,
            "endY": walls[0].startY,
        })
        walls[walls.length - 1].type = walls[walls.length - 1].startX == walls[walls.length - 1].endX ? "vertical" : "horizontal"
    } else if (endPoint == "T") {
        walls.push({
            "startX": walls[walls.length - 1].endX,
            "startY": walls[walls.length - 1].endY,
            "endX": walls[walls.length - 1].endX,
            "endY": 0,
        })
        walls[walls.length - 1].type = walls[walls.length - 1].startX == walls[walls.length - 1].endX ? "vertical" : "horizontal"
        walls.push({
            "startX": walls[walls.length - 1].endX,
            "startY": 0,
            "endX": walls[walls.length - 1].endX == canvas.width ? 0 : canvas.width,
            "endY": 0,
        })
        walls[walls.length - 1].type = walls[walls.length - 1].startX == walls[walls.length - 1].endX ? "vertical" : "horizontal"
        walls.push({
            "startX": walls[walls.length - 1].endX,
            "startY": 0,
            "endX": walls[0].startX,
            "endY": walls[0].startY,
        })
        walls[walls.length - 1].type = walls[walls.length - 1].startX == walls[walls.length - 1].endX ? "vertical" : "horizontal"
    } else if (endPoint == "L") {
        walls.push({
            "startX": walls[walls.length - 1].endX,
            "startY": walls[walls.length - 1].endY,
            "endX": 0,
            "endY": walls[walls.length - 1].endY,
        })
        walls[walls.length - 1].type = walls[walls.length - 1].startX == walls[walls.length - 1].endX ? "vertical" : "horizontal"
        walls.push({
            "startX": 0,
            "startY": walls[walls.length - 1].endY,
            "endX": 0,
            "endY": walls[walls.length - 1].endY == canvas.height ? 0 : canvas.height,
        })
        walls[walls.length - 1].type = walls[walls.length - 1].startX == walls[walls.length - 1].endX ? "vertical" : "horizontal"
        walls.push({
            "startX": 0,
            "startY": walls[walls.length - 1].endY,
            "endX": walls[0].startX,
            "endY": walls[0].startY,
        })
        walls[walls.length - 1].type = walls[walls.length - 1].startX == walls[walls.length - 1].endX ? "vertical" : "horizontal"
    } else {
        walls.push({
            "startX": walls[walls.length - 1].endX,
            "startY": walls[walls.length - 1].endY,
            "endX": walls[0].startX,
            "endY": walls[0].startY,
        })
        walls[walls.length - 1].type = walls[walls.length - 1].startX == walls[walls.length - 1].endX ? "vertical" : "horizontal"
    }

    return {
        "endPoint": endPoint,
        "walls": walls
    }
}

// Attach an event listener to the search input
document.getElementById('searchInput').addEventListener('input', filterSidebarItems);
