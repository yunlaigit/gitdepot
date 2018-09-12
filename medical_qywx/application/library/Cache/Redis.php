<?php
class Cache_Redis {
	static  $obj = array();

	static function &instance($pConfig = 'default'){
	
    if (!isset(self::$obj[$pConfig])) {
		# 配置
		if(!$tConf = Yaf_Registry::get("config")->redis->$pConfig){
			exit('redis config error: '.$pConfig);
		}
		$tConf = $tConf->toArray();
		# 连接
		self::$obj[$pConfig] = new Redis();
		
		if (self::$obj[$pConfig]->connect($tConf['host'], $tConf['port'])) {
			self::$obj[$pConfig]->select($tConf['db']);
		}
    }
    return self::$obj[$pConfig];
  }

  /**
   * 将JSON处理为PHP数组
   *
   * @param string $pJson json_encode(数组)
   * @param string $pKey 数组键值
   * @param string $pDefault 默认值
   *
   * @return mixed
   * @demo Cache_Redis::json($json, 'uid', 0)
   * @demo Cache_Redis::json($json, null)
   */
  static function json(&$pJson, $pKey = null, $pDefault = '') {
    if (!$tArray = json_decode($pJson, true)) return $pDefault;
    return isset($tArray[$pKey]) ? $tArray[$pKey] : $pDefault;
  }

}
