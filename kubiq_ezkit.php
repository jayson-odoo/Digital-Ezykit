<style>
  .nav-tabs>li.active>a,
  .nav-tabs>li.active>a:focus,
  .nav-tabs>li.active>a:hover {
    background-color: red;
    color: #fff;
  }

  .holds-the-iframe {
    background: url(images/Preloader_21.gif) center fixed no-repeat;
  }
</style>
<?php
require_once("kujiale.class.php");
$objkjl = new KjlApi();
$kjllogin = "crm_" . trim($_SESSION['username']) . "@signaturekitchen.com.my";
// $kjlappuid = $_SESSION['userid'];
// $accesstoken_kjl = $objkjl->login($kjllogin,$kjlappuid);

$appkey = "ND2R30MHvR";
$appsecret = "HvkjcBm0f15UoMsySC5tWMwj0Vpc3ozB";
$kjlappuid = $_SESSION['userid'];
// echo $kjlappuid;
$timestamp = $objkjl->get_timestamp();
$sign1 = $objkjl->getSign($kjlappuid, $timestamp);
//$sign1 = $objkjl->getSign('',$timestamp);
//echo $sign1;
?>
<style>
  .header {
    display: flex;
    align-items: center;
    padding: 16px;
    background-color: #f5f5f5;
  }

  #logo {
    height: 40px;
    margin-right: 25px;
  }

  #title {
    font-size: 24px;
    font-weight: bold;
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
    border-bottom: 1px solid #dee2e6;
  }

  .nav-tabs .nav-item {
    margin-bottom: -1px;
  }

  .nav-tabs .nav-link {
    border: 1px solid transparent;
    border-top-left-radius: 0.25rem;
    border-top-right-radius: 0.25rem;
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
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
  }

  .nav-tabs .dropdown-menu {
    margin-top: -1px;
    border-top-left-radius: 0;
    border-top-right-radius: 0;
  }

  .navbar-brand {
    display: inline-block;
    padding-top: 0.3125rem;
    padding-bottom: 0.3125rem;
    margin-right: 1rem;
    font-size: 1.25rem;
    line-height: inherit;
    white-space: nowrap;
  }

  .navbar-brand:hover,
  .navbar-brand:focus {
    text-decoration: none;
  }

  .ml-5,
  .mx-5 {
    margin-left: 3rem !important;
  }

  .ml-auto,
  .mx-auto {
    margin-left: auto !important;
  }
</style>
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
      <div class="navbar-item pull-left h4">Instant Quotation</div>
      <!-- / -->
      <!-- Common tools -->

    </div>
    <!-- Content -->
    <div class="box-row">
      <div class="box-cell">

        <div class="box-inner padding">
          <div class="row">
            <div class="col-md-12">
              <div class="panel panel-default">
                <div class="panel-heading bg-white">
                  <div class="row row-sm">
                    <div class="col-xs-12 font-bold header">
                      <div class="navbar-brand">
                        <!-- <img src="digital_ezykit/images/kubiq_logo.png" alt="Kubiq Logo" height="50"> -->
                      </div>
                      <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item" ro le="presentation">
                          <a class="nav-link active" data-target="#test_1" id="load_ezkit_design" aria-controls="test_1"
                            role="tab" data-toggle="tab" data-src="">Design</a>
                        </li>
                        <li class="nav-item" role="presentation">
                          <a class="nav-link" href="digital_ezykit/kubiq_quotation_test.php" data-target="#test_2"
                            aria-controls="test_2" role="tab" data-toggle="tab" data-src="javascript:;"
                            id="load_summary_quotation">Quotation</a>
                        </li>
                      </ul>
                      <div class="ml-5 d-flex justify-content-center flex-grow-1">
                        <form class="form-inline">
                          <div class="form-group">
                            <label for="total_price">Total (RM):</label>
                            <input type="text" class="form-control ml-1" id="total_price" placeholder="0.00" readonly>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                  <div class="row row-sm">
                    <div class="tab-content" style="margin-right: -8px;margin-left: -8px;">
                      <div role="tabpanel" class="tab-pane active" id="test_1">
                        <div class="holds-the-iframe">
                          <iframe id="iFrameEzkitDesign" name="iFrameEzkitDesign"
                            src="digital_ezykit/kubiq_ezykit_design.php" width="100%" height="1000" frameborder="0">
                            <img src='images/Preloader_21.gif'>
                          </iframe>
                        </div>
                      </div>
                      <div role="tabpanel" class="tab-pane" id="test_2">
                        <div class="holds-the-iframe">
                          <iframe id="iFrameSummaryQuotation" name="iFrameSummaryQuotation" src="about:blank"
                            width="100%" height="1000" frameborder="0">
                            <img src='images/Preloader_21.gif'>
                          </iframe>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>


            </div>
          </div>
        </div>

      </div>

    </div>
  </div>

  <!-- / -->
</div>

</div>
<!-- / content -->




<?php include('footer.php'); ?>
<script>
  // tab switch
  $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
    const iframe = document.getElementById('iFrameEzkitDesign');
    const iframeContent = iframe.contentWindow || iframe.contentDocument;
    const iframeDocument = iframe.contentDocument || iframe.contentWindow.document;
    // get value passed from quotation page
    if (typeof grandTotal !== 'undefined' && grandTotal > 0) {
      
      // document.getElementById("total_price").value = parseFloat(grandTotal, 2);
      iframeDocument.getElementById("transportationDistance").value = document.getElementById("transportationDistance").value;
      iframeDocument.getElementById("discountpercentage").value = document.getElementById("discountpercentage").value;
      iframeDocument.getElementById("worktopLabourSinkSelection").value = document.getElementById("worktopLabourSinkSelection").value;
      iframeDocument.getElementById("worktopLabourOpeningSelection").value = document.getElementById("worktopLabourOpeningSelection").value;
    }
    // generate json of the item list
    var output = iframeContent.generate_3D_JSON();
    var infill_no = iframeContent.infillIdentification();
    console.log(infill_no)
    var plinth_length = iframeContent.plinthLengthCalculation(infill_no.open_end_plinth, infill_no.open_end_plinth_cap);
    if (output.items === false) {
      alert(output.error);
    } else {
      // prepare parameter to pass to quotation page
      var query_arr = [];
      query_arr['ezkit'] = 'true';
      query_arr['items'] = iframeContent.items;
      query_arr['worktop'] = document.getElementById("worktopUnitMeasurement");
      query_arr['worktop'] = (query_arr['worktop'] && query_arr['worktop'].value) ? parseFloat(query_arr['worktop'].value) : 0;
      query_arr['unitprice'] = document.getElementById("worktopUnitPrice");
      query_arr['unitprice'] = (query_arr['unitprice'] && query_arr['unitprice'].value) ? parseFloat(query_arr['unitprice'].value) : 0;
      query_arr['transportation'] = document.getElementById("transportationDistance");
      query_arr['transportation'] = (query_arr['transportation'] && query_arr['transportation'].value) ? parseFloat(query_arr['transportation'].value) : -1;
      query_arr['discount'] = document.getElementById("discountpercentage");
      query_arr['discount'] = (query_arr['discount'] && query_arr['discount'].value) ? parseFloat(query_arr['discount'].value) : 0;
      var worktopcategorySelect = document.getElementById("worktopcategory");
      query_arr['worktopcategory'] = (worktopcategorySelect && worktopcategorySelect.value) ? worktopcategorySelect.options[worktopcategorySelect.selectedIndex].value : "";
      var worktoptypeSelect = document.getElementById("worktoptype");
      query_arr['worktoptype'] = (worktoptypeSelect && worktoptypeSelect.value) ? worktoptypeSelect.options[worktoptypeSelect.selectedIndex].value : "";
      query_arr['infill'] = JSON.stringify(infill_no)
      query_arr['plinth'] = JSON.stringify(plinth_length)
      query_arr['worktop_labour_sink'] = document.getElementById("worktopLabourSinkSelection");
      query_arr['worktop_labour_sink'] = (query_arr['worktop_labour_sink'] && query_arr['worktop_labour_sink'].value) ? query_arr['worktop_labour_sink'].value : 0;
      query_arr['door_color'] = document.getElementById("doorColorSelection");
      query_arr['worktop_labour_opening'] = document.getElementById("worktopLabourOpeningSelection");
      query_arr['worktop_labour_opening'] = (query_arr['worktop_labour_opening'] && query_arr['worktop_labour_opening'].value) ? query_arr['worktop_labour_opening'].value : 0;
      query_arr['door_color'] = document.getElementById("doorColorSelection");
      query_arr['door_color'] = (query_arr['door_color'] && query_arr['door_color'].value) ? query_arr['door_color'].value : 0;
      
      // Create a new URLSearchParams object
      const searchParams = new URLSearchParams();

      // Iterate through the object's properties and append them to the URLSearchParams object
      for (const key in query_arr) {
        if (key === 'items') {
          continue;
        } else {
          // If the value is not an array, append it as a single key-value pair
          searchParams.append(key, query_arr[key]);
        }
      }

      // Get the final query string
      const queryString = searchParams.toString();

      var url = $(this).attr("href"); // the remote url for content
      var tab_id = $(this).attr("id"); // the remote url for content
      var target = $(this).data("target"); // the target pane
      var data_src = $(this).data("src");
      var tab = $(this); // this tab
      if (!infill_no) {
        url = "undefined";
      }
      url = url + "?" + queryString;
      localStorage.setItem("items", JSON.stringify(query_arr.items))

      // ajax load from data-url
      var iframeSQ = $("#iFrameSummaryQuotation");
      var blankpage = "about:blank";
      var run = false;
      if (tab_id == 'load_summary_quotation') {
        iframeSQ.attr("src", data_src);
        run = true;
      } else if (tab_id != 'load_ezkit_design') {
        iframeSQ.attr("src", blankpage);
        run = true;
      }

      if (run == true) {
        $(target).load(url, function (result) {
          tab.tab('show');
        });
      }
    }
  });

  // initially activate the first tab..
  <?php if (isset($_GET['new'])) { ?>
    $('#load_summary_quotation').tab('show');
  <?php } else { ?>
    $('#tab1').tab('show');
  <?php } ?>
</script>


<script src="../libs/jquery/validator/jquery.form-validator.min.js"></script>
<script> $.validate(); </script>