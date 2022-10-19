<?php
class Auth{
    private $db,$result;
    function __construct($db,$method,$auth_info) {
        $this->db = $db;
        $this->result = array("auth" => false);
        if ($method == "get_access_token") {
            $check_params = CheckParams($auth_info,array("refresh_token","user_id"));
            if ($check_params['result']) {
                $get_user = $this->db->select(false,array("user_refresh_token"),"users","user_id='".$auth_info['user_id']."'");
                if ($get_user != 0) {
                    if ($get_user['user_refresh_token'] == $auth_info['refresh_token']) {
                        $tokens = $this->GenerateAccessToken($auth_info['user_id']);
                        $this->result = array("auth" => false, "access_token" => $tokens['access_token'], "refresh_token" => $tokens['refresh_token']);
                    }else{
                        $this->result = array("auth" => false, "note" => "Неверные данные для получения токена");
                    }
                }else{
                    $this->result = array("auth" => false, "note" => "Пользователь не найден");
                }
            }else{
                $this->result = array("auth" => false, "note" => $check_params['note']);
            }
        }else{
            $check_params = CheckParams($auth_info,array("access_token","user_id"));
            if ($check_params['result']) {
                $get_user = $this->db->select(false,array("user_access_token","user_expire_in"),"users","user_id='".$auth_info['user_id']."'");
                if ($get_user != 0) {
                    if ($get_user['user_access_token'] == $auth_info['access_token']) {
                        if ($get_user['user_expire_in'] >= time()) {
                            $this->result = array("auth" => true);
                        }else{
                            $this->result = array("auth" => false, "note" => "Ошибка аутентификации! Время активности токена закончилось");
                        }
                    }else{
                        $this->result = array("auth" => false, "note" => "Ошибка аутентификации! Неверный токен доступа");
                    }
                }else{
                    $this->result = array("auth" => false, "note" => "Ошибка аутентификации! Пользователь не найден");
                }
            }else{
                $this->result = array("auth" => false, "note" => "Ошибка аутентификации! ".$check_params['note']);
            }
        }
    }
    
    public function GetResult() {
        return $this->result;
    }
    
    private function GenerateAccessToken($user_id) {
        $result = array();
        $result['access_token'] = md5($user_id)."-".md5(time());
        $result['refresh_token'] = md5($user_id.md5(time()));
        $expire_in = time();
        $expire_in = intval($expire_in)+24*60*60;
        $update_values = array(
            "user_access_token" => $result['access_token'],
            "user_refresh_token" => $result['refresh_token'],
            "user_expire_in" => $expire_in
        );
        $this->db->update("users",$update_values,"user_id='$user_id'");
        return $result;
    }
}
?>