<?php
if(!isset($____smartmain_php____)){
	$____smartmain_php____=true;
	if(!defined('__documents_root__')){
		define ( '__documents_root__', $_SERVER['DOCUMENT_ROOT'] );
	}
	require __documents_root__.'/smarty/libs/Smarty.class.php';
	$smarty = new Smarty();
	$smarty->template_dir = $smarty_template_dir;
	$smarty->compile_dir = __documents_root__.'/smarty/templates_c';
	$smarty->config_dir = __documents_root__.'/smarty/configs';
	$smarty->cache_dir = __documents_root__.'/smarty/cache';
	
	//$smarty->debugging = true;
	$smarty->caching = true;
	$smarty->cache_lifetime = 120;
}
?>