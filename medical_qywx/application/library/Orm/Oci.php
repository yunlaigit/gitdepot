<?php
class Orm_Oci
{	
	private $db = null;
	public function __construct(){
		if($this->db == NULL){
            header("Content-type: text/html; charset=utf-8"); 
            // $this->db = oci_connect('bqe','bqe','123.57.47.91:1521/bqedb','AL32UTF8');
            $this->db = oci_new_connect('csyt', 'csyt', '(DESCRIPTION =
    (ADDRESS = (PROTOCOL = TCP)(HOST = 10.10.190.217)(PORT = 1521))
    (CONNECT_DATA =
      (SERVER = DEDICATED)
      (SERVICE_NAME = ORCL)
    )
  )');
		}

		if(!$this->db){
        	$e = oci_error();
            Tool_Fnc::ajaxMsg(htmlentities($e['message'], ENT_QUOTES));
        }else{
            return $this->db; 
        }
				
	}

    public function getRow($tSql){
        $stid = oci_parse($this->db, $tSql);
        $r = oci_execute($stid);
        $row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
        return $row;
    }

    public function getAll($tSql){
        $stid = oci_parse($this->db, $tSql);
        $r = oci_execute($stid);
        $tDatas = array();
        while($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)){
            $tDatas[] = $row; 
        }
        return $tDatas;
    }
   
    public function exec($tSql){
        $stid = oci_parse($this->db, $tSql);
        return  oci_execute($stid);
    }

    /**
     * 添加记录
     * return lastid
     */
    public function insert($pData, $pReplace = false){
        if($this->_filter($pData)){
            $tField = '`'.join('`,`', array_keys($pData)).'`';
            $tVal = join("','", $pData);
//echo $tSql = ($pReplace? "REPLACE": "INSERT") . " INTO `$this->table`($tField) VALUES ('$tVal')";exit();

            #Tool_Fnc::writefile('/home/zhangyueru/xx',$tSql);
            if($this->exec(($pReplace? "REPLACE": "INSERT") . " INTO $this->table($tField) VALUES ('$tVal')")){
                return $this->db->lastInsertId();
            }
        }
        return 0;
    }

    /**
    *   更新记录
    **/
    public function update($pData){
        #   过滤
        if(!$this->_filter($pData)) return false;
        #   条件
        $tOpt = array();
        if(isset($pData[$this->pk])){
            $tOpt = array('where' => "$this->pk='{$pData[$this->pk]}'");

        }
        $tOpt = $this->_options($tOpt);
        #   更新
        if($pData && !empty($tOpt['where'])){
            foreach($pData as $k1 => $v1){
                $tSet[] = "`$k1`='$v1'";
                if($k1 == 'CREATED'){
                    $tSet = "`$k1`='to_date($v1, 'yyyy-mm-dd hh24:mi:ss')'";
                }
            }
            $tSql = "UPDATE `" . $tOpt['table'] . "` SET " . join(',', $tSet) . " WHERE " . $tOpt['where'];

            return $this->exec("UPDATE " . $tOpt['table'] . " SET " . join(',', $tSet) . " WHERE " . $tOpt['where']);
        }
        return false;
    }

	public function __destruct(){
		if(!empty($this->db)){
            oci_close($this->db);
		}			
	}
}

