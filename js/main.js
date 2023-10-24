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

