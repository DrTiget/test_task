<?php
    function CheckParams($params,$list) {
        $result = array();
        $lost = array();
        for($i=0;$i<count($list);$i++) {
            if (!isset($params[$list[$i]])) {
                array_push($lost,$list[$i]);
            }
        }
        if (count($lost) != 0) {
            $result['result'] = false;
            $result['note'] = "Недостаточно параметров: ".implode(", ",$lost);
        }else{
            $result['result'] = true;
        }
        return $result;
    }
?>