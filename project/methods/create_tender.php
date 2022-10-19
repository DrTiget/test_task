<?php
include "../config.php";
include "../classes/auth.class.php";
include "../classes/tenders.class.php";
$params = $_REQUEST;
$auth = new Auth($db,"auth",$params);
$get_auth = $auth->GetResult();
if ($get_auth['auth']) {
    $params = json_decode(file_get_contents('php://input'),true);
    $tender = new Tenders($db,"add_tender",$params);
    print_r(json_encode($tender->GetResult()));
}else{
    print_r(json_encode($get_auth));
}
?>