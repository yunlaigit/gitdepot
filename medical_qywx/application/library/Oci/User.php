<?php
class Oci_User {
    //判断手机号是否存在
    static function isPhone($pPhone){
        $Oci = new Orm_Oci;  
        $tSql = 'select CENTER_ID,PATIENT_ID,NAME,SEX,to_char(DATE_OF_BIRTH,\'yyyy-mm-dd\') DATE_OF_BIRTH,PHONE_NUMBER_HOME,photo from PAT_MASTER_INDEX  where PHONE_NUMBER_HOME = \''.$pPhone.'\'';
        $tRow = $Oci->getRow($tSql);
        return $tRow;
    } 
    //提取干体重
    static function getDryWeight($id){
        $Oci = new Orm_Oci;
        $tSql = 'select * from (select to_char(TREATE_DATE,\'yyyy-mm-dd\') TREATE_DATE,BODY_WEIGHT from TREATMENT_RECORD where PATIENT_ID = \''.$id.'\' order by TREATMENT_TIMES desc) where rownum = 1';
        $tRow = $Oci->getRow($tSql);
        return $tRow;
    }
    //获取健康档案
    static function getAssessment($id){
        $Oci = new Orm_Oci;
        $tSql = 'select PATIENT_ID,INP_NO,to_char(MEDICAL_DATE_01,\'yyyy-mm-dd\') MEDICAL_DATE_01,to_char(MEDICAL_DATE_02,\'yyyy-mm-dd\') MEDICAL_DATE_02,to_char(MEDICAL_DATE_03,\'yyyy-mm-dd\') MEDICAL_DATE_03,to_char(MEDICAL_DATE_04,\'yyyy-mm-dd\') MEDICAL_DATE_04,PRESENT_01,PRESENT_02,PRESENT_03,PRESENT_04,PRESENT_05,PRESENT_06,PRESENT_07,PRESENT_08,PRESENT_09,PRESENT_10,PRESENT_11,PRESENT_12,PRESENT_13,PRESENT_14,PRESENT_15,PRESENT_16,PRESENT_17,PRESENT_18,PRESENT_19,PRESENT_20,PRESENT_21,PRESENT_22,PRESENT_23,PRESENT_24,COMPLICATION_01,COMPLICATION_02,COMPLICATION_03,COMPLICATION_04,COMPLICATION_05,COMPLICATION_06,MED_PAST_011,MED_PAST_012,MED_PAST_013,MED_PAST_021,MED_PAST_022,MED_PAST_031,MED_PAST_032,MED_PAST_033,MED_PAST_034,MED_PAST_035,MED_PAST_036,MED_PAST_037,MED_PAST_041,MED_PAST_042,MED_PAST_043,MED_PAST_044,MED_PAST_045,MED_PAST_046,MED_PAST_05,MED_PAST_06,MED_PAST_07,MED_PAST_08,MED_PAST_09,MED_PAST_10,MED_PAST_11,MED_PAST_12,MED_PAST_13,BODY_HEIGHT,DIALYSIS_NO,ANTICOAGULATION,IF_IN,WEEKS,PRESENT_10_1,PRESENT_04_1,PRESENT_20_1,PRESENT_24_1,MED_PAST_05_1,MED_PAST_06_1,MED_PAST_07_1,MED_PAST_08_1,MED_PAST_09_1,MED_PAST_10_1,MED_PAST_11_1,MED_PAST_12_1,MED_PAST_13_1,MED_PAST_10_2 from PAT_ASSESSMENT where PATIENT_ID = \''.$id.'\'';
        $tRow = $Oci->getRow($tSql);
        return $tRow;
    }
    //获取中心字典
    static function getCenterdict($id){
        $Oci = new Orm_Oci;
        $tSql = ' select CENTER_ID,CENTER_CODE,CENTER_NAME,POSITION from CENTER_DICT where CENTER_ID = \''.$id.'\'';
        $tRow = $Oci->getRow($tSql);
        return $tRow;
    }
}

