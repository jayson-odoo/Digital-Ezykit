<?php
session_start();
include 'config.php'; // include the config
include "../db.php";
GetMyConnection();

// get user id
$sql = 'select id, userdesigncad from tbluser where username = "'.$_SESSION['username'].'"';
$r = mysql_query($sql);
$nr   = mysql_num_rows($r); // Get the number of rows
if($nr > 0){
    if ($nr > 1) {
        throw new Exception('Too many users returned!');
    }
    while ($row = mysql_fetch_assoc($r)) {
        $appuid = $row['id'];
    }
}

// kujiale		
require_once("../tools/kujiale.class.php");
$design_id = "3FO3UDKLEYK5";
$kjllogin = "crm_".trim($_SESSION['username'])."@signaturekitchen.com.my";
$objkjl = new KjlApi();
$response = "";
$appkey = "ND2R30MHvR";
$appsecret = "HvkjcBm0f15UoMsySC5tWMwj0Vpc3ozB";
$timestamp = $objkjl->get_timestamp();
$sign1 = $objkjl->getSign($appuid,$timestamp);
$sign2 = $objkjl->getSign('',$timestamp);

if($_SESSION['userdesigncad'] == "Y"){

    $url = "https://openapi.kujiale.com/v2/design/".$design_id."/copy?design_id=".$design_id."&appkey=".$appkey."&timestamp=".$timestamp."&sign=".$sign1."&appuid=".$appuid;

    $params = array();
    $response = $objkjl->curlPostJson($url,$params);
    // decode json
    $response_decode = json_decode($response, true);
    $new_design_id = $response_decode['d'];
    // create user first
    $response = "";
    $url = "https://openapi.kujiale.com/v2/register?appkey=".$appkey."&timestamp=".$timestamp."&sign=".$sign1."&appuid=".$appuid;
    $url_show = "https://openapi.kujiale.com/v2/register?appkey=".$appkey.htmlentities("&times")."tamp=".$timestamp."&sign=".$sign1."&appuid=".$appuid;
    $params = array('name'=>$kjllogin,'type'=>'0');
    $response = $objkjl->curlPostJson($url,$params);

    $response = "";
    $url = "https://openapi.kujiale.com/v2/sso/token?appkey=".$appkey."&timestamp=".$timestamp."&sign=".$sign1."&appuid=".$appuid."&dest=0";
    $url_show = "https://openapi.kujiale.com/v2/sso/token?appkey=".$appkey.htmlentities("&times")."tamp=".$timestamp."&sign=".$sign1."&appuid=".$appuid."&dest=0";
    $params = "";
    $response = $objkjl->curlPost($url,$params);

    $response_decode = json_decode($response, true);
    $c = $response_decode['c'];
    $m = $response_decode['m'];
    $accesstoken_kjl = $response_decode['d'];
    
    $url = "https://www.kujiale.com/open/login?access_token=$accesstoken_kjl";
} else {
	echo "No Access";
	echo "No Access to Design CAD.";
}
// $c = $response_decode['c'];
// $newdesignid = $response_decode['d'];
// // $data_string = json_encode($arrayserialnumber);
// // print $data_string;
// $_SESSION['ezikit'] = "";
CleanUpDB();
?>
var ifrm = document.createElement("iframe");
ifrm.setAttribute("src", "<?php echo $url; ?>");
ifrm.style.display = "none";
document.body.appendChild(ifrm);
window.open("https://yun.kujiale.com/cloud/tool/h5/bim?designid=<?php echo $new_design_id; ?>&launchMiniapp=3FO4K4VMKV3T&__rd=y&_gr_ds=true", "_openKJL");
window.location = '?module=proposal_create_kubiq&action=add&proposalid=&leadid=<?php echo $_GET['leadid']; ?>';