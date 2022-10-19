<?php
include "../config.php";
include "../classes/auth.class.php";
$params = $_REQUEST;
$auth = new Auth($db,"get_access_token",$params);
$get_auth = $auth->GetResult();
print_r(json_encode($get_auth));
?>