<?php
class DB_class 
{
	private $db_host,$db_name,$db_user,$db_pass,$db;
	function __construct($db_host,$db_name,$db_user,$db_pass)
	{
		if (!$this->db) {
			$con = @ new mysqli($db_host, $db_user, $db_pass, $db_name);
			if (!$con->connect_error) {
				$this->db = true;
				$con->set_charset("utf8");
				$this->con = $con;
				return true;
			}else{
				echo $con->connect_error."<br>";
				return false;
			}
		}
	}

	/*
	форматирование данных перед записью в базу данных
	получает значение и тип в который надо форматировать
	*/
	private function format_value($value,$format) {
		switch ($format) {
	    	case "date":
	    		$value = date("Y-m-d",strtotime($value));
	        break;
	        case "time":
	    		$value = date("H:i:s",strtotime($value));
	        break;
	        case "int(11)":
	        	$value = intval($value);
	        break;
	        case "tinyint(4)":
	        	$value = intval($value);
	        break;
	        case "tinyint(1)":
	        	$value = boolval($value);
	        break;
	        case "bigint(20)":
	        	$value = intval($value);
	        break;
	    }
	    return $value;
	}


	/*
		входные данные
		$table - название таблицы
		выходные данные
		{
			"Field" => Название поля
			"Type"  => Тип поля
		}
	*/
	private function get_table_fields ($table) {
		$sql = "DESCRIBE ".$table;
		$u_query = $this->con->query($sql);
		if ($u_query->num_rows != 0) {
		    $count_row = 0;
			while ($query_row = $u_query->fetch_array(MYSQLI_ASSOC)) {
				$select_count = 0;
				$count_select = count($query_row);
				$select_array = NULL;
				while ($select_count < $count_select) {
					$return_array[$count_row]['Field'] = $query_row['Field'];
					$return_array[$count_row]['Type'] = $query_row['Type'];
					$select_count++; 
				}
				$count_row++;
			}
		}else{
			$return_array = 0;
		}
		return $return_array;
	}


	function select($while,$select,$from,$where = null,$debug = false)
	{
		if ($where != NULL) {
			$where = "WHERE ".$where;
		}
		if (is_array($select)) {
			$select_query = implode(',', $select);
		}else{
			$select_query = $select;
		}
		$sql = "SELECT ".$select_query." FROM `".$from."` ".$where." ".$order."";
		if ($debug == true) {
		    print_r($sql);
		}
		$u_query = $this->con->query($sql);
		if ($u_query->num_rows != 0) {
		    $count_row = 0;
			while ($query_row = $u_query->fetch_array(MYSQLI_ASSOC)) {
				$select_count = 0;
				$count_select = count($query_row);
				$select_array = NULL;
				while ($select_count < $count_select) {
					if ($while == true) {
						$return_array[$count_row] = $query_row;
						$select_count++; 
					}else{
						$return_array = $query_row;
						$select_count++;
					}
				}
				$count_row++;
			}
			$fields = $this->get_table_fields($from);
			if ($while == true) {
				for($q=0;$q<count($return_array);$q++) {
					for($c=0;$c<count($fields);$c++) {
						if ($fields[$c]['Type'] == "date" AND isset($return_array[$q][$fields[$c]['Field']])) {
							$return_array[$q][$fields[$c]['Field']] = date($date_format,strtotime($return_array[$q][$fields[$c]['Field']]));
						}
						if ($fields[$c]['Type'] == "time" AND isset($return_array[$q][$fields[$c]['Field']])) {
							$return_array[$q][$fields[$c]['Field']] = date($time_format,strtotime($return_array[$q][$fields[$c]['Field']]));
						}
					}
				}
			}else{
				for($c=0;$c<count($fields);$c++) {
					if ($fields[$c]['Type'] == "date" AND isset($return_array[$fields[$c]['Field']])) {
						$return_array[$fields[$c]['Field']] = date($date_format,strtotime($return_array[$fields[$c]['Field']]));
					}
					if ($fields[$c]['Type'] == "time" AND isset($return_array[$fields[$c]['Field']])) {
						$return_array[$fields[$c]['Field']] = date($time_format,strtotime($return_array[$fields[$c]['Field']]));
					}
				}
			}
		}else{
			$return_array = 0;
		}
		return $return_array;
	}


	function update($from,$set,$where,$debug = false)
	{
		if ($where != NULL) {
			$where = "WHERE ".$where;
		}
		$fields = $this->get_table_fields($from);
		if (is_array($set)) {	
			for($i=0;$i<count($fields);$i++) {
				if (array_key_exists($fields[$i]['Field'],$set)) {
					$set[$fields[$i]['Field']] = $this->format_value($set[$fields[$i]['Field']],$fields[$i]['Type']);
				}
			}
		}
		if (is_array($set)) {
			$array_keys = array_keys($set);
			$set_query = "";
			for($i=0;$i<count($set);$i++) {
				if ($i+1 != count($set)) { 
					$set_query .= $array_keys[$i]."='".$set[$array_keys[$i]]."', ";
				}else{
					$set_query .= $array_keys[$i]."='".$set[$array_keys[$i]]."' ";					
				}
			}
		}else{
			$set_query = $set;
		}

		$update_sql = "UPDATE ".$from." SET ".$set_query." ".$where."";
		if ($debug == true) {
		    print_r($update_sql);
		}
		$update_query = $this->con->query($update_sql);
	}
	
	function delete($from,$where,$debug = false)
	{
		if ($where != NULL) {
			$where = "WHERE ".$where;
		}
		$delete_sql = "DELETE FROM ".$from." ".$where."";
		if ($debug == true) {
		    print_r($delete_sql);
		}
		$delete_query = $this->con->query($delete_sql);
		return $delete_query;
	}

	function insert($from,$insert,$check_array=array("*"),$debug = false)
	{
		$fields = $this->get_table_fields($from);
		if (is_array($insert)) {	
			for($i=0;$i<count($fields);$i++) {
				if (array_key_exists($fields[$i]['Field'],$insert)) {
					$insert[$fields[$i]['Field']] = $this->format_value($insert[$fields[$i]['Field']],$fields[$i]['Type']);
				}
			}
		}
		if (is_array($insert)) {
			$insert_query = array_keys($insert);
			for ($i=0;$i<count($insert_query);$i++) {
				$insert_query[$i] = "`".$insert_query[$i]."`";
			}
			$insert_query = implode(',',$insert_query);
			$values_query = array_values($insert);
			for ($i=0;$i<count($values_query);$i++) {
				$values_query[$i] = "'".$values_query[$i]."'";
			}
			$values_query = implode(',',$values_query);
		}else{
			return 0;
		}
		$insert_sql = "INSERT INTO ".$from." (".$insert_query.") VALUES (".$values_query.")";
		if ($debug == true) {
		    print_r($insert_sql);
		}
		$insert_query = $this->con->query($insert_sql);
		$where = array();
		$insert_keys = array_keys($insert);
		for($i=0;$i<count($insert);$i++) {
			array_push($where,$insert_keys[$i]."='".$insert[$insert_keys[$i]]."'");
		}
		$where = implode(" AND ",$where);
		$check = $this->select(false,$check_array,$from,$where);
		if ($debug == true) {
		    print_r($check);
		}
		return $check;
	}

	function __destruct()
	{
		mysqli_close($this->con);
		$this->db = false;
	}
}
?>