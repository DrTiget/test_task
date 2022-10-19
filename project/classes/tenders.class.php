<?php
class Tenders{
    private $db,$result;
    function __construct($db,$active_type,$active_info) {
        $this->db = $db;
        $this->result = array();
        switch($active_type) {
            case "add_tender":
                $this->result = $this->CreateTender($active_info);
            break;
            case "get_tender":
                if (isset($active_info['tender_id'])) {
                    $tender_id = $active_info['tender_id'];
                    $this->result = $this->GetTender($tender_id);
                }else{
                    $this->result = array("result" => false, "note" => "Недостаточно параметра tender_id");
                }
            break;
            case "get_tenders":
                $this->result = $this->GetTenders($active_info);
            break;
            default:
                $this->result = array("result" => false, "note" => "Неизвестный тип действия");
            break;
        }
    }
    
    public function GetResult() {
        return $this->result;
    }
    
    private function CreateTender($tender_info) {
        $result = array();
        $result['result'] = false;
        $check_params = CheckParams($tender_info,array("name","number","date","time","status","code"));
        if ($check_params['result']) {
            if (is_int($tender_info['code'])) {
                $insert_values = array(
                    "data_code" => intval($tender_info['code']),
                    "data_number" => $tender_info['number'],
                    "data_modify_date" => date("Y-m-d", strtotime($tender_info['date'])),
                    "data_modify_time" => date("H:i:s", strtotime($tender_info['time'])),
                    "data_status" => $tender_info['status'],
                    "data_name" => $tender_info['name']
                );
                $new_tender = $this->db->insert("data",$insert_values);
                if ($new_tender != 0) {
                    $result['result'] = true;
                    $result['data'] = $new_tender;
                }else{
                    $result['note'] = "Произошла ошибка при записи в базу данных";
                }
            }else{
                $result['note'] = "Параметр code должен быть типа int";
            }
        }else{
            $result['note'] = $check_params['note'];
        }
        return $result;
    }
    
    private function GetTender($data_id) {
        $result = array();
        $get_tender = $this->db->select(false,array("*"),"data","data_id='$data_id'");
        if ($get_tender != 0) {
            $result = array("result" => true, "data" => $get_tender);
        }else{
            $result = array("result" => false, "note" => "Тендер не найден");
        }
        return $result;
    }
    
    private function GetTenders($filter) {
        $result = array();
        $where = array();

        $bad_params = array();
        $list = array("date","date_start","date_end","time","time_start","time_end");
        for($i=0;$i<count($list);$i++) {
            if (isset($filter[$list[$i]])) {
                if (!is_numeric(strtotime($filter[$list[$i]]))) {
                    array_push($bad_params,$list[$i]);
                }                
            }
        }
        if (count($bad_params) != 0) {
            $result['result'] = false;
            $result['note'] = "Параметры: ".implode(", ",$bad_params)." - неверный формат даты или времени";
        }else{
            if (isset($filter['date'])) {
                array_push($where,"data_modify_date='".date("Y-m-d",strtotime($filter['date']))."'");
            }
            if (isset($filter['date_start'])) {
                array_push($where,"data_modify_date>='".date("Y-m-d",strtotime($filter['date_start']))."'");
            }
            if (isset($filter['date_end'])) {
                array_push($where,"data_modify_date<='".date("Y-m-d",strtotime($filter['date_end']))."'");
            }
            if (isset($filter['name'])) {
                array_push($where,"data_name LIKE '%".$filter['name']."%'");
            }
            if (isset($filter['time'])) {
                array_push($where,"data_modify_time='".$filter['time']."'");
            }
            if (isset($filter['time_start'])) {
                array_push($where,"data_modify_time>='".$filter['time_start']."'");
            }
            if (isset($filter['time_end'])) {
                array_push($where,"data_modify_time<='".$filter['time_end']."'");
            }
            
            if (count($where) != 0) {
                $where = implode(" AND ",$where);
            }else{
                $where = "";
            }
            $get_tenders = $this->db->select(true,array("*"),"data",$where);
            if ($get_tenders == 0) {
                $get_tenders = array();
            }
            $result['data'] = $get_tenders;
            $result['count'] = count($get_tenders);
        }
        return $result;
    }
}
?>