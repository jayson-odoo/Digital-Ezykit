<?php
class Item
{
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
    public $id;

    function set_name($name)
    {
        $this->name = $name;
    }
    function get_name()
    {
        return $this->name;
    }
    function set_description($description)
    {
        $this->description = $description;
    }
    function get_description()
    {
        return $this->description;
    }
    function set_model_id($model_id)
    {
        $this->model_id = $model_id;
    }
    function get_model_id()
    {
        return $this->model_id;
    }
    function set_height($height)
    {
        $this->height = $height;
    }
    function get_height()
    {
        return $this->height;
    }
    function set_width($width)
    {
        $this->width = $width;
    }
    function get_width()
    {
        return $this->width;
    }
    function set_depth($depth)
    {
        $this->depth = $depth;
    }
    function get_depth()
    {
        return $this->depth;
    }
    function set_type($type)
    {
        $this->type = $type;
    }
    function get_type()
    {
        return $this->type;
    }
    function set_price($price)
    {
        $this->price = $price;
    }
    function get_price()
    {
        return $this->price;
    }
    function set_installation($installation)
    {
        $this->installation = $installation;
    }
    function get_installation()
    {
        return $this->installation;
    }
    function set_average_ep($average_ep)
    {
        $this->average_ep = $average_ep;
    }
    function get_average_ep()
    {
        return $this->average_ep;
    }
    function set_master_uid($master_uid)
    {
        $this->master_uid = $master_uid;
    }
    function get_master_uid()
    {
        return $this->master_uid;
    }
    function set_id($id)
    {
        $this->id = $id;
    }
    function get_id()
    {
        return $this->id;
    }
}
include '../config.php'; // include the config
include "../db.php";
GetMyConnection();

$worktop = isset($_GET['worktop']) ?: 0;
$unitprice = isset($_GET['unitprice']) ?: 1146;
$discount = isset($_GET['discount']) ?: 0;
$transportation = isset($_GET['transportation']) ?: 0;
$worktopcategory = isset($_GET['worktopcategory']) ?: '';
$worktoptype = isset($_GET['worktoptype']) ?: '';

// For serial number
$sql = 'select * from tblitem_master_ezkit order by `master_type` asc;';
$r = mysql_query($sql);
$nr = mysql_num_rows($r); // Get the number of rows
if ($nr > 0) {
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
        $new_item->set_id($row['id']);
        if ($row['master_type'] == "Tall") {
            $new_item->set_name($row['master_module'] . " (" . $row['master_type'] . ")");
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
$sql_ezkit = 'select * from tblitem_master_ezkit where master_active = "' . $active . '"';
$r_ezkit = mysql_query($sql_ezkit);
$nr_ezkit = mysql_num_rows($r_ezkit); // Get the number of rows
if ($nr_ezkit > 0) {
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

$_SESSION['ezikit'] = "";
CleanUpDB();
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="styles/kubiq_ezykit_design.css">
<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.css">
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
    #wall_dropzone,
    #three_d_container {
        /* padding: 20px; */
        background: #eee;
        position: absolute;
    }

    #base_dropzone {
        z-index: 10;
    }

    .tab-content {
        display: none;
    }

    .active-tab {
        display: block;
    }
</style>
<script type="module" src="scripts/ezykit_share.js"></script>
</head>

<body>
    <header class="navbar navbar-expand-lg navbar-light bg-light">
        <button class="btn btn-secondary ml-4" name="base_button" id="base_button">Base</button>
        <button class="btn btn-secondary ml-4" name="wall_button" id="wall_button">Wall</button>
        <button class="btn btn-secondary ml-4" name="three_d_button" id="three_d_button">3D (Beta)</button>
    </header>
    <style>
        /* Apply styles to #sidebar */
        #sidebar ul li a {
            padding: 10px 18px;
            display: block;
            color: #fff;
            font-size: 14px;
        }

        .nav-tabs .nav-link.active,
        .nav-tabs .nav-item.show .nav-link {
            color: #fff;
            background-color: #007bff;
        }

        .nav {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            padding-left: 0;
            margin-bottom: 0;
            list-style: none;
        }

        .nav-tabs {
            border-bottom: 1px solid white;
        }

        .nav-tabs .nav-item {
            margin-bottom: -1px;
        }

        .nav-tabs .nav-link {
            border: 1px solid transparent;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }

        .nav-tabs .nav-link:hover,
        .nav-tabs .nav-link:focus {
            border-color: #e9ecef #e9ecef #dee2e6;
        }

        .nav-tabs .nav-link.disabled {
            color: #6c757d;
            background-color: transparent;
            border-color: transparent;
        }

        .nav-tabs .nav-link.active,
        .nav-tabs .nav-item.show .nav-link {
            color: #495057;
            background-color: #2196f3;
            border-color: #2196f3 #dee2e6 #fff;
        }

        .nav-tabs .dropdown-menu {
            margin-top: -1px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
    </style>
    <div id="clickable_area" class="wrapper d-flex align-items-stretch">
        <nav id="sidebar">
            <div class="container">
                <div class="tab_switch row row-sm">
                    <div class="col-sm-12 font-bold header" style="padding-right: 0px;padding-left: 0px;">
                        <ul class="nav nav-tabs">
                            <li class="nav-item col-md-6" style="padding-right: 0px;padding-left: 0px;">
                                <a href="#" id="module_tab" class="nav-link active">Module</a>
                            </li>
                            <li class="nav-item col-md-6" style="padding-right: 0px;padding-left: 0px;">
                                <a href="#" id="kitchen_layout_tab" class="nav-link">Kitchen Layout</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div id="kitchen_layout" class="tab-content container" style="padding-top:10px;">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label for="length">Width(mm):</label>
                            <input type="number" class="form-control" id="length" value="4500" placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-sm-1" style="padding-top: 40px;">
                        X
                    </div>
                    <div class="col-sm-5">
                        <div class="form-group">
                            <label for="width">Length(mm):</label>
                            <input type="number" class="form-control" id="width" value="4500" placeholder="0.00">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <button class="btn btn-primary btn-block" class="form-control" id="resize_canvas_button">Apply</button>
                    </div>
                    <div class="col-sm-12">
                        <button class="btn btn-secondary btn-block" style="background-color:#8D99A3;"
                            class="form-control" id="reset_canvas_button">
                            Reset Layout
                        </button>
                    </div>
                    <div class="col-sm-12">
                        <button class="btn btn-secondary btn-block" style="background-color:#8D99A3;" type="button"
                            id="new_design_button">Clear All Module</button>
                    </div>
                </div>
            </div>
            <div id="module" class="tab-content active-tab">
                <div class="container" style="padding-top:10px;">
                    <div class="row">
                        <div class="col">
                            <button class="btn btn-secondary btn-block" name="base_button" id="module_base_button">Base</button>
                        </div>
                        <div class="col">
                            <button class="btn btn-secondary btn-block" name="wall_button" id="module_wall_button">Wall</button>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <div class="input-group">
                        <div class="form-outline">
                            <!-- Search form -->
                            <div class="md-form mt-0">
                                <input class="form-control" style="height:25px !important;" type="text" id="searchInput"
                                    placeholder="Search modules..." aria-label="Search">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item" id="catalogue">

                        </li>
                    </ul>
                </div>
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
            <div class="container">
                <div id="three_d_container"/>
            </div>
        </div>
    </div>
    <form id="data"></form>
    <script>
        var item_array = JSON.parse('<?php echo json_encode($item_array); ?>');
    </script>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"
        integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.concat.min.js"></script>
    <script>
        // variable data get from php
        var item_array = JSON.parse('<?php echo json_encode($item_array); ?>');
        var arraymodule = '<?php echo json_encode($arraymodule); ?>';
        var arraydescription = '<?php echo json_encode($arraydescription); ?>';
        var arrayprice = '<?php echo json_encode($arrayprice); ?>';
        var arrayepprices = '<?php echo json_encode($arrayepprices); ?>';
        var arrayinstallationprice = '<?php echo json_encode($arrayinstallationprice); ?>';
    </script>
    <script type="module" src="scripts/kubiq_ezykit_3d.js"></script>
    <script type="module" src="scripts/kubiq_ezykit_design.js"></script>
