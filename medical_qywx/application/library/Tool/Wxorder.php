<?php
class WxorderModel extends Orm_Base{
    public $table = 'wxorder';
    public $field = array(
        'id' => array('type' => "int(11) unsigned",'comment' => 'id'),
        'checkin_no' => array('type' => "varchar(10)",'comment' => ''),
        'checkin_no_type' => array('type' => "tinyint(1)",'comment' => ''),
        'device_type' => array('type' => "tinyint(1)",'comment' => ''),
        'money' => array('type' => "decimal(10,2)",'comment' => ''),        
        'order_no' => array('type' => "varchar(32)",'comment' => ''),
        'create_time' => array('type' => "int(11)",'comment' => ''),
        'order_status' => array('type' => "tinyint(1)",'comment' => ''),
        'transaction_id' => array('type' => "varchar(50)",'comment' => ''),
        'uid' => array('type' => "int(11)",'comment' => ''),
    );
    public $pk = 'id';
}
?>
