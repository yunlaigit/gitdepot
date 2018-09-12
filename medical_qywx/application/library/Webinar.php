<?php
/**
 * 微吼活动
 */
class Webinar{
    private $tAccountInfo = array(
        'auth_type'=>'1', 
    );
    public function __construct(){
        $tVHall = Yaf_Registry::get('config')->vhall->default->toArray(); 
        $this->tAccountInfo = array_merge($this->tAccountInfo,$tVHall);
    }

    //活动列表
    public function wlist(){
        $tUrl = 'http://e.vhall.com/api/vhallapi/v2/webinar/list'; 
        $tPostData = array(
            'type' => 1,
            'pos' => 0,
            'limit' => 10,
            'status' => '',
        );
        $tPostData = array_merge($this->tAccountInfo,$tPostData);

        $tRes = Tool_Fnc::httppost($tUrl,$tPostData);
        return json_decode($tRes,true);
    }

    //参与者 totken
    public function attendeeToken($pWebinarID,$pEmail,$pName){
        $tUrl = 'http://e.vhall.com/api/vhallapi/v2/attendee/gen-token'; 
        $tPostData = array(
            'webinar_id'  => $pWebinarID,
            'email' => $pEmail,
            'name' => $pName,
        );
        $tPostData = array_merge($this->tAccountInfo,$tPostData);
        $tRes = Tool_Fnc::httppost($tUrl,$tPostData);
        return json_decode($tRes,true);
    }
    //获取活动信息
    public function fetch($id){
        $tUrl = 'http://e.vhall.com/api/vhallapi/v2/webinar/fetch'; 
        $tPostData = array(
            'webinar_id'  => $id,
            #'webinar_id'  => '927825606',
            'fields' => 'subject,introduction,img_url',
        );
        $tPostData = array_merge($this->tAccountInfo,$tPostData);
        $tRes = Tool_Fnc::httppost($tUrl,$tPostData);
        return json_decode($tRes,true);
    }
}
