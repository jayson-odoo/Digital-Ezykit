<script type="text/javascript">
  function deleteRecord(id, mastermodule,table) {
    var ok = confirm("Are you sure you want to delete " + mastermodule + "?");
    if (ok) {
      var UrlToPass = 'action=delete&id=' + id + '&table=' + table ;
      $.ajax({ // Send the credential values to another process_department.php using Ajax in POST menthod
					type: 'POST',
					data: UrlToPass,
					url: 'digital_ezykit/ezkit_process.php',
					success: function (responseText) { // Get the result and asign to each cases
						//alert(responseText);

						if (responseText == 0) {
							save_result.html('<div class="form-group has-error"><label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i> Error. Please check with IT</label></div>');
						}
						else if (responseText != 0) {
							window.location = '?module=digital_ezykit/ezkit_list';
						}
						else {
							alert('<div class="form-group has-error"><label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i> SQL Code error</label></div>');
						}
					}
				});
    }
    return ok;
  }
</script>

<?php
GetMyConnection();

$type_query = mysql_query('SELECT * FROM tblitem_master_ezkit_module_type;');
$type_rows = mysql_num_rows($type_query); // Get the number of rows
if ($type_rows > 0) { // If record found.
  while ($row = mysql_fetch_array($type_query)) {
    $type_arr[$row['id']] = $row['type'];
  }
}

CleanUpDB();
?>

<!-- content -->
<div id="content" class="app-content" role="main">
  <div class="box">
    <!-- Content Navbar -->
    <div class="navbar md-whiteframe-z1 no-radius blue">
      <!-- Open side - Naviation on mobile -->
      <a md-ink-ripple data-toggle="modal" data-target="#aside" class="navbar-item pull-left visible-xs visible-sm"><i
          class="mdi-navigation-menu i-24"></i></a>
      <!-- / -->
      <!-- Page title - Bind to $state's title -->
      <div class="navbar-item pull-left h4">Admin</div>
      <!-- / -->
      <!-- / -->
    </div>
    <!-- Content -->
    <div class="box-row">
      <div class="box-cell">
        <div class="box-inner padding">
          <div class="row">
            <div class="col-md-12">
              <div class="panel-heading bg-black">
                <div class="row row-sm">
                  <div class="col-xs-12 font-bold header">
                    <div class="navbar-brand">
                      <!-- <img src="digital_ezykit/images/kubiq_logo.png" alt="Kubiq Logo" height="50"> -->
                    </div>
                    <ul class="nav nav-tabs" role="tablist">
                      <?php
                      if (isset($type_arr)) {
                        $count = 0;
                        foreach ($type_arr as $key => $value) {
                          echo '<li class="nav-item" role="presentation">
																	<a class="nav-link ' . (($count == 0) ? 'active' : '') . '" data-target="#test_' . $count . '" id="load_ezkit_' . str_replace(' ', '', strtolower($value)) . '"
																		aria-controls="test_' . $count . '" role="tab" data-toggle="tab"
																		data-src="">' . $value . '</a>
																</li>';
                          $count++;
                        }
                      }
                      ?>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="panel panel-default">
            <div class="panel-heading">
              Ezikit Module List
            </div>
            <div class="table-responsive">

              <table id="example1" class="table table-striped b-t b-b">
                <thead id="header">
                  <tr>
                    <th style="width:5%">No</th>
                    <th style="width:15%">Module</th>
                    <th style="width:25%">Description</th>
                    <th style="width:10%">KJL Model</th>
                    <th style="width:5%">Width</th>
                    <th style="width:5%">Height</th>
                    <th style="width:5%">Depth</th>
                    <th style="width:5%">Installation</th>
                    <th style="width:10%">Average EP</th>
                    <th style="width:5%">Module Price</th>
                    <th style="width:5%">Total Price</th>
                    <th style="width:5%">Type</th>
                    <th style="width:5%">Action</th>
                  </tr>
                </thead>
                <tbody id="table_content">
                  <?php
                  GetMyConnection();

                  //execute the SQL query and return records
                  $result = mysql_query("select * from tblitem_master_ezkit");
                  $mycount = 0;
                  $total = 0;
                  //fetch tha data from the database 
                  while ($row = mysql_fetch_array($result)) {
                    $mycount++;

                    $total = $row { 'master_price'} + $row { 'master_ep'};
                    echo "<tr>";
                    echo "<td>" . $mycount . "</td>";
                    echo "<td>" . $row { 'master_module'} . "</td>";
                    echo "<td>" . $row { 'master_description'} . "</td>";
                    echo "<td>" . $row { 'master_kjl_model_id'} . "</td>";
                    echo "<td>" . $row { 'master_width'} . "</td>";
                    echo "<td>" . $row { 'master_height'} . "</td>";
                    echo "<td>" . $row { 'master_depth'} . "</td>";
                    echo "<td>" . $row { 'master_installation'} . "</td>";
                    echo "<td>" . $row { 'master_ep'} . "</td>";
                    echo "<td>RM" . $row { 'master_price'} . "</td>";
                    echo "<td>RM" . $total . "</td>";
                    echo "<td>" . $row { 'master_type'} . "</td>";
                    echo "<td>";
                    echo "<button onClick=\"location.href='?module=digital_ezykit/ezkit_create&action=edit&txtid=" . $row { 'id'} . "&tab=module'\" class=\"btn m-v-xs btn-sm btn-info\">&nbsp;&nbsp;&nbsp;Edit&nbsp;&nbsp;&nbsp;</button><br>";
                    echo "<button onClick=\"javascript:deleteRecord('" . $row['id'] . "','" . $row['master_module'] . "','tblitem_master_ezkit');\" id=\"deleterecord\" name=\"deleterecord\" class=\"btn m-v-xs btn-sm btn-danger\">Delete</button>";
                    echo "</td>";
                    echo "</tr>";
                  }
                  CleanUpDB();
                  ?>
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>
    <!-- / -->
  </div>

</div>
<!--  content -->
<?php include('footer.php'); ?>
<script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script type="text/javascript">
  $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {

    tab_id = $(this).attr("id"); // the remote url for content
    var target = $(this).data("target"); // the target pane
    var data_src = $(this).data("src");
    var tab = $(this); // this tab

    if (tab_id == 'load_ezkit_module') {
      if ($.fn.DataTable.isDataTable('#example1')) {
        // DataTable exists, clear and destroy
        $('#example1').DataTable().clear().destroy();

        $('#header').html('<tr><th style="width:5%">No</th><th style="width:15%">Module</th><th style="width:25%">Description</th><th style="width:10%">KJL Model</th><th style="width:5%">Width</th><th style="width:5%">Height</th><th style="width:5%">Depth</th><th style="width:5%">Installation</th><th style="width:10%">Average EP</th><th style="width:5%">Module Price</th><th style="width:5%">Total Price</th><th style="width:5%">Type</th><th style="width:5%">Action</th></tr>');
        var content_html = '<?php
        GetMyConnection();
        $result = mysql_query("select * from tblitem_master_ezkit");
        $mycount = 0;
        $total = 0;
        //fetch tha data from the database 
        while ($row = mysql_fetch_array($result)) {
          $mycount++;

          $total = $row { 'master_price'} + $row { 'master_ep'};
          echo "<tr>";
          echo "<td>" . $mycount . "</td>";
          echo "<td>" . $row { 'master_module'} . "</td>";
          echo "<td>" . $row { 'master_description'} . "</td>";
          echo "<td>" . $row { 'master_kjl_model_id'} . "</td>";
          echo "<td>" . $row { 'master_width'} . "</td>";
          echo "<td>" . $row { 'master_height'} . "</td>";
          echo "<td>" . $row { 'master_depth'} . "</td>";
          echo "<td>" . $row { 'master_installation'} . "</td>";
          echo "<td>" . $row { 'master_ep'} . "</td>";
          echo "<td>RM" . $row { 'master_price'} . "</td>";
          echo "<td>RM" . $total . "</td>";
          echo "<td>" . $row { 'master_type'} . "</td>";
          echo "<td>";
          echo "<button onClick=\"location.href=\'?module=digital_ezykit/ezkit_create&action=edit&txtid=" . $row { 'id'} . "&table=tblitem_master_ezkit\'\" class=\"btn m-v-xs btn-sm btn-info\">&nbsp;&nbsp;&nbsp;Edit&nbsp;&nbsp;&nbsp;</button><br>";
          echo "<button onClick=\"javascript:deleteRecord(\'" . $row['id'] . "\',\'" . $row['master_module'] . "\',\'tblitem_master_ezkit\');\" id=\"deleterecord\" name=\"deleterecord\" class=\"btn m-v-xs btn-sm btn-danger\">Delete</button>";
          echo "</td>";
          echo "</tr>";
        }
        CleanUpDB();
        ?>';

        $('#table_content').html(content_html);
        $('#example1').DataTable();
      }
    } else if (tab_id == 'load_ezkit_worktop') {
      if ($.fn.DataTable.isDataTable('#example1')) {
        // DataTable exists, clear and destroy

        $('#example1').DataTable().clear().destroy();
        $('#header').html('<tr><th style="width:5%">No</th><th style="width:15%">Name</th><th style="width:25%">Description</th><th style="width:5%">Width</th><th style="width:5%">Length</th><th style="width:5%">Depth</th><th style="width:5%">Material</th><th style="width:10%">Spec</th><th style="width:5%">Price</th><th style="width:5%">Action</th></tr>');
        var content_html = '<?php
        GetMyConnection();
        $result = mysql_query("select * from tblitem_master_ezkit_worktop");
        $mycount = 0;
        $total = 0;
        //fetch tha data from the database 
        while ($row = mysql_fetch_array($result)) {
          $mycount++;
          echo "<tr>";
          echo "<td>" . $mycount . "</td>";
          echo "<td>" . $row { 'name'} . "</td>";
          echo "<td>" . $row { 'description'} . "</td>";
          echo "<td>" . $row { 'width'} . "</td>";
          echo "<td>" . $row { 'length'} . "</td>";
          echo "<td>" . $row { 'depth'} . "</td>";
          echo "<td>" . $row { 'material'} . "</td>";
          echo "<td>" . $row { 'spec'} . "</td>";
          echo "<td>RM" . $row { 'price'} . "</td>";
          echo "<td>";
          echo "<button onClick=\"location.href=\'?module=digital_ezykit/ezkit_create&action=edit&txtid=" . $row { 'id'} . "&table=tblitem_master_ezkit_worktop\'\" class=\"btn m-v-xs btn-sm btn-info\">&nbsp;&nbsp;&nbsp;Edit&nbsp;&nbsp;&nbsp;</button><br>";
          echo "<button onClick=\"javascript:deleteRecord(\'" . $row['id'] . "\',\'" . $row['name'] . "\',\'tblitem_master_ezkit_worktop\');\" id=\"deleterecord\" name=\"deleterecord\" class=\"btn m-v-xs btn-sm btn-danger\">Delete</button>";
          echo "</td>";
          echo "</tr>";
        }
        CleanUpDB();
        ?>';

        $('#table_content').html(content_html);
        $('#example1').DataTable();
      }
    } else if (tab_id == 'load_ezkit_doorcolor') {
      if ($.fn.DataTable.isDataTable('#example1')) {
        // DataTable exists, clear and destroy
        $('#example1').DataTable().clear().destroy();
        $('#header').html('<tr><th style="width:5%">ID</th><th style="width:15%">Name</th><th style="width:5%">Action</th></tr>');
        var content_html = '<?php
        GetMyConnection();
        $result = mysql_query("select * from tblitem_master_ezkit_door_color");
        $mycount = 0;
        $total = 0;
        //fetch tha data from the database 
        while ($row = mysql_fetch_array($result)) {
          $mycount++;
          echo "<tr>";
          echo "<td>" . $mycount . "</td>";
          echo "<td>" . $row { 'name'} . "</td>";
          echo "<td>";
          echo "<button onClick=\"location.href=\'?module=digital_ezykit/ezkit_create&action=edit&txtid=" . $row { 'id'} . "&table=tblitem_master_ezkit_door_color\'\" class=\"btn m-v-xs btn-sm btn-info\">&nbsp;&nbsp;&nbsp;Edit&nbsp;&nbsp;&nbsp;</button><br>";
          echo "<button onClick=\"javascript:deleteRecord(\'" . $row['id'] . "\',\'" . $row['name'] . "\',\'tblitem_master_ezkit_door_color\');\" id=\"deleterecord\" name=\"deleterecord\" class=\"btn m-v-xs btn-sm btn-danger\">Delete</button>";
          echo "</td>";
          echo "</tr>";
        }
        CleanUpDB();
        ?>';

        $('#table_content').html(content_html);
        $('#example1').DataTable();
      }

    } else if (tab_id == 'load_ezkit_plinth') {
      if ($.fn.DataTable.isDataTable('#example1')) {
        // DataTable exists, clear and destroy
        $('#example1').DataTable().clear().destroy();

        $('#header').html('<tr><th style="width:5%">No</th><th style="width:15%">Name</th><th style="width:25%">Description</th><th style="width:5%">Width</th><th style="width:5%">Length</th><th style="width:5%">Depth</th><th style="width:10%">Kitchen/Wardrobe</th><th style="width:5%">UOM</th><th style="width:5%">Price</th><th style="width:5%">Action</th></tr>');
        var content_html = '<?php
        GetMyConnection();
        $result = mysql_query("select * from tblitem_master_ezkit_plinth");
        $mycount = 0;
        $total = 0;
        //fetch tha data from the database 
        while ($row = mysql_fetch_array($result)) {
          $mycount++;
          echo "<tr>";
          echo "<td>" . $mycount . "</td>";
          echo "<td>" . $row { 'name'} . "</td>";
          echo "<td>" . $row { 'description'} . "</td>";
          echo "<td>" . $row { 'width'} . "</td>";
          echo "<td>" . $row { 'length'} . "</td>";
          echo "<td>" . $row { 'depth'} . "</td>";
          echo "<td>" . $row { 'kitchen_wardrobe'} . "</td>";
          echo "<td>" . $row { 'uom'} . "</td>";
          echo "<td>RM" . $row { 'price'} . "</td>";
          echo "<td>";
          echo "<button onClick=\"location.href=\'?module=digital_ezykit/ezkit_create&action=edit&txtid=" . $row { 'id'} . "&table=tblitem_master_ezkit_plinth\'\" class=\"btn m-v-xs btn-sm btn-info\">&nbsp;&nbsp;&nbsp;Edit&nbsp;&nbsp;&nbsp;</button><br>";
          echo "<button onClick=\"javascript:deleteRecord(\'" . $row['id'] . "\',\'" . $row['name'] . "\',\'tblitem_master_ezkit_plinth\');\" id=\"deleterecord\" name=\"deleterecord\" class=\"btn m-v-xs btn-sm btn-danger\">Delete</button>";
          echo "</td>";
          echo "</tr>";
        }
        CleanUpDB();
        ?>';

        $('#table_content').html(content_html);
        $('#example1').DataTable();
      }
    } else if (tab_id == 'load_ezkit_handle') {
      if ($.fn.DataTable.isDataTable('#example1')) {
        // DataTable exists, clear and destroy
        $('#example1').DataTable().clear().destroy();

        $('#header').html('<tr><th style="width:5%">ID</th><th style="width:15%">Name</th><th style="width:15%">Description</th><th style="width:15%">Price</th><th style="width:5%">Action</th></tr>');
        var content_html = '<?php
        GetMyConnection();
        $result = mysql_query("select * from tblitem_master_ezkit_handle");
        $mycount = 0;
        $total = 0;
        //fetch tha data from the database 
        while ($row = mysql_fetch_array($result)) {
          $mycount++;
          echo "<tr>";
          echo "<td>" . $mycount . "</td>";
          echo "<td>" . $row { 'name'} . "</td>";
          echo "<td>" . $row { 'description'} . "</td>";
          echo "<td>RM" . $row { 'price'} . "</td>";
          echo "<td>";
          echo "<button onClick=\"location.href=\'?module=digital_ezykit/ezkit_create&action=edit&txtid=" . $row { 'id'} . "&table=tblitem_master_ezkit_handle\'\" class=\"btn m-v-xs btn-sm btn-info\">&nbsp;&nbsp;&nbsp;Edit&nbsp;&nbsp;&nbsp;</button><br>";
          echo "<button onClick=\"javascript:deleteRecord(\'" . $row['id'] . "\',\'" . $row['name'] . "\',\'tblitem_master_ezkit_handle\');\" id=\"deleterecord\" name=\"deleterecord\" class=\"btn m-v-xs btn-sm btn-danger\">Delete</button>";
          echo "</td>";
          echo "</tr>";
        }
        CleanUpDB();
        ?>';

        $('#table_content').html(content_html);
        $('#example1').DataTable();
      }
    } else if (tab_id == 'load_ezkit_infill') {
      if ($.fn.DataTable.isDataTable('#example1')) {
        // DataTable exists, clear and destroy
        $('#example1').DataTable().clear().destroy();

        $('#header').html('<tr><th style="width:5%">No</th><th style="width:15%">Name</th><th style="width:25%">Description</th><th style="width:5%">Width</th><th style="width:5%">Length</th><th style="width:5%">Depth</th><th style="width:5%">Price</th><th style="width:5%">Action</th></tr>');
        var content_html = '<?php
        GetMyConnection();
        $result = mysql_query("select * from tblitem_master_ezkit_infill");
        $mycount = 0;
        $total = 0;
        //fetch tha data from the database 
        while ($row = mysql_fetch_array($result)) {
          $mycount++;
          echo "<tr>";
          echo "<td>" . $mycount . "</td>";
          echo "<td>" . $row { 'name'} . "</td>";
          echo "<td>" . $row { 'description'} . "</td>";
          echo "<td>" . $row { 'width'} . "</td>";
          echo "<td>" . $row { 'length'} . "</td>";
          echo "<td>" . $row { 'depth'} . "</td>";
          echo "<td>RM" . $row { 'price'} . "</td>";
          echo "<td>";
          echo "<button onClick=\"location.href=\'?module=digital_ezykit/ezkit_create&action=edit&txtid=" . $row { 'id'} . "&table=tblitem_master_ezkit_infill\'\" class=\"btn m-v-xs btn-sm btn-info\">&nbsp;&nbsp;&nbsp;Edit&nbsp;&nbsp;&nbsp;</button><br>";
          echo "<button onClick=\"javascript:deleteRecord(\'" . $row['id'] . "\',\'" . $row['name'] . "\',\'tblitem_master_ezkit_infill\');\" id=\"deleterecord\" name=\"deleterecord\" class=\"btn m-v-xs btn-sm btn-danger\">Delete</button>";
          echo "</td>";
          echo "</tr>";
        }
        CleanUpDB();
        ?>';

        $('#table_content').html(content_html);
        $('#example1').DataTable();
      }
    }
  });

  var table = "";
  $(document).ready(function () {
    $('#example1').DataTable();
  });
</script>