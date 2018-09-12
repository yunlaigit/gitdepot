<?php
class Bootstrap extends Yaf_Bootstrap_Abstract{

	/**
	 * 把配置存到注册表
	 */
	function _initConfig(){
		Yaf_Registry::set("config", $config = Yaf_Application::app()->getConfig());
		define('PATH_APP', $config->application->directory);
		define('PATH_TPL', PATH_APP . '/views');
		define('USER_IP', Tool_Fnc::realip());
	}

	function _initRoute(){
		# 路由
		$router = Yaf_Dispatcher::getInstance()->getRouter();
		# 静态页面
		$router->addRoute('html', new Yaf_Route_Regex('/([a-z]+)\.html$/', array('controller' => 'Index', 'action' => 'html'), array(1 => 'page')));
	}

	/**
	 * 采用布局
	 * @param Yaf_Dispatcher $dispatcher
	 */
	function _initLayout(Yaf_Dispatcher $dispatcher){
		define('REDIRECT_URL', empty($_SERVER['REQUEST_URI'])? '/': strtolower($_SERVER['REQUEST_URI']));
		# 用户后台
        if(false !== strpos(REDIRECT_URL, '/user_emailverify')){ return ;} 
		if(false !== strpos(REDIRECT_URL, '/user_') || false !== strpos(REDIRECT_URL, '/huodong_') || false !== strpos(REDIRECT_URL, '/loan_')){
			$layout = new LayoutPlugin('user/tpl.layout.phtml');
			Yaf_Registry::set('layout', $layout);
			$dispatcher->registerPlugin($layout);
        }
	}
}

