<?php


include_once 'function.php';


$smarty_template_dir=$_SERVER['DOCUMENT_ROOT'].'/smarty/templates';

require($_SERVER['DOCUMENT_ROOT'].'/smarty/configs/smartmain.php');





dd($_SERVER);




date_default_timezone_set('America/Los_Angeles');

$t1=explode('?', $_SERVER['REQUEST_URI']);

if(isset($t1[0])){
	$uri=$t1[0];
}
else{
	$uri='/';
}


$uris=explode('/', $uri);



if(!empty($uris[1])){
	$className=ucfirst(strtolower($uris[1]))."Controller";
	$classPath=$_SERVER['DOCUMENT_ROOT'].'/controllers/'.strtolower($uris[1]).'.php';
	$defaultFolder=strtolower($uris[1]);
}

else{
	$className="IndexController";
	$classPath=$_SERVER['DOCUMENT_ROOT'].'/controllers/index.php';
	$defaultFolder='index';
	
}



if(!empty($uris[2])){
	$method=strtolower($uris[2]);
}
else{
	$method='index';
}


if(!is_file($classPath)){
	$smarty->assign('errorMessage',"class file [{$classPath}] is not exist");
	$smarty->display('general_error.tpl');
	return;
}

$parameters=array();

for($i1=3;isset($uris[$i1]);$i1++){
	$parameters=$uris[$i1];
}




include_once 'basic_controller.php';
include_once $classPath;

if(! class_exists($className)){

	$smarty->assign('errorMessage',"Can not find class [{$className}]  in the file [{$classPath}]");
	$smarty->display('general_error.tpl');
	return;
}

if(! method_exists($className,$method)){

	$smarty->assign('errorMessage',"Can not find method [{$method}] in class [{$className}]  in the file [{$classPath}]");
	$smarty->display('general_error.tpl');
	return;
}

$controller=new $className;


try {
	if(method_exists($controller,'pre_filter')){

		$controller->pre_filter();
	}

	
	$smarty->assign('title',"{$className}->{$method}");
	
	$controller->$method($parameters);



	if(method_exists($controller,'post_filter')){

		$controller->post_filter();
	}
	
	
	
	if(!property_exists($controller,'tpl_name') || empty($controller->tpl_name)){
		$contentTpl=$defaultFolder.'/'.$method.'.tpl';
	}
	else{
		if(substr($controller->tpl_name,0,1) == '/'){
			$contentTpl=substr($controller->tpl_name,1);
		}
		else{
			$contentTpl=$defaultFolder.'/'.$controller->tpl_name;
		}
		
		if(strlen($contentTpl)<4 || strtolower(substr($contentTpl, -4))!='.tpl'){
			$contentTpl.='.tpl';
		}
		
	}
	
	
	if(!property_exists($controller,'decorator') || empty($controller->decorator)){
		$decoratorTpl='decorators/index.tpl';
	}
	else{
		if(substr($controller->decorator,0,1) == '/'){
			$decoratorTpl=substr($controller->decorator,1);
		}
		else{
			$decoratorTpl='decorators/'.$controller->decorator;
		}
		
		if(strlen($decoratorTpl)<4 || strtolower(substr($decoratorTpl, -4))!='.tpl'){
			$decoratorTpl.='.tpl';
		}
	}
	
	$decoratorPath=$smarty_template_dir.'/'.$decoratorTpl;
	$contentPath=$smarty_template_dir.'/'.$contentTpl;
	

	
	if(!is_file($decoratorPath)){
		
		$smarty->assign('errorMessage',"Can not find decorator file [{$decoratorPath}]");
		$smarty->display('general_error.tpl');
	}
	
	
	
	else if(!is_file($contentPath)){
	
		$smarty->assign('errorMessage',"Can not find content file [{$contentPath}]");
		$smarty->display('general_error.tpl');
	}
	
	else{
		
		
		$smarty->registerPlugin("function","html_script", "smarty_plugin_func_html_script");
		$smarty->registerPlugin("function","html_css", "smarty_plugin_func_html_css");
		
		
		
		$csses=array();
		
		$jses=array();
		
		
		
		
		ob_start();
		$smarty->display($contentTpl);
		$content = ob_get_contents();
		ob_end_clean();
		
		$smarty->assign('content',$content);
		$smarty->assign('csses',$csses);
		$smarty->assign('jses',$jses);
		
		$smarty->display($decoratorTpl);
		
	}
	
	dd("we are end!");
	
} catch (Exception $e) {
	$smarty->assign('errorMessage',"Exception:".$e->getMessage().' in file:'.$e->getFile().' line ['.$e->getLine().']');
	$smarty->display('general_error.tpl');
	return;
}

function smarty_plugin_func_html_script($parameters,$smarty){
	global $jses ,$defaultFolder;
	
	$jsPath='/js';
	
	if(!isset($jses) || !is_array($jses)){
		$jses=array();
	}
	
	if(!empty($parameters['src'])){
		$src=$parameters['src'];		
		if(substr($src,0,1) == '/'){
			$src=$jsPath.$src;
		}
		else{
			$src=$jsPath.'/'.$defaultFolder.'/'.$src;
		}
		
		if(strlen($src)<3 || strtolower(substr($src, -3))!='.js'){
			$src.='.js';
		}
		$jses[]=$src;
	}
	

}

function smarty_plugin_func_html_css($parameters,$smarty){
	global $csses ,$defaultFolder;
	
	$cssPath='/css';
	
	if(!isset($csses) || !is_array($csses)){
		$csses=array();
	}
	
	if(!empty($parameters['src'])){
		$src=$parameters['src'];		
		if(substr($src,0,1) == '/'){
			$src=$cssPath.$src;
		}
		else{
			$src=$cssPath.'/'.$defaultFolder.'/'.$src;
		}
		
		if(strlen($src)<4 || strtolower(substr($src, -4))!='.css'){
			$src.='.css';
		}
		$csses[]=$src;
	}
	
	
}



?>