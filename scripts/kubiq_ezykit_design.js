// global variables
var items_added = []

class Item {
    constructor(uid, name, height, width, x, y) {
        this.uid = uid;
        this.name = name;
        this.height = height;
        this.width = width;
        this.x = x;
        this.y = y;
    }
}
function uid(){
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
}

function addItem(obj) {
    const init_x = 0;
    const init_y = 0;
    var newItem = new Item(uid, obj.name, obj.height, obj.width, init_x, init_y)
    items_added.push(newItem)
    console.log(items_added)
    return true;
}

function newDesign() {
    items_added = []
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
