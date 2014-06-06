<?php

$currentDir = dirname(__FILE__);
include_once $currentDir . '/function.php';
include_once $currentDir . '/log.php';
include_once $currentDir . '/view.php';
include_once realpath($currentDir . '/../config') . '/system.php';
include_once realpath($currentDir . '/../config') . '/data_source.php';

date_default_timezone_set('America/Los_Angeles');

$view = new view();

$t1 = explode('?', $_SERVER['REQUEST_URI']);



if (isset($t1[0])) {
    $uri = substr($t1[0], __PROJECT_HEADER_LENGTH__);
} else {
    $uri = '/';
}


$uris = explode('/', $uri);




if (!empty($uris[1])) {
    $controllerName = strtolower($uris[1]);
} else {
    $controllerName = "index";
}
$className = ucfirst($controllerName) . "Controller";
$classPath = __PROJECT_ROOT__ . '/controllers/' . strtolower($controllerName) . '.php';



if (!empty($uris[2])) {
    $method = strtolower($uris[2]);
} else {
    $method = 'index';
}



if (!is_file($classPath)) {
    $view->assign('errorMessage', "class file [{$classPath}] is not exist");
    $view->display('error/general_error.phtml');
    return;
}

$parameters = array();

for ($i1 = 3; isset($uris[$i1]); $i1++) {
    $parameters = $uris[$i1];
}




include_once 'basic_controller.php';
include_once $classPath;

if (!class_exists($className)) {

    $view->assign('errorMessage', "Can not find class [{$className}]  in the file [{$classPath}]");
    $view->display('error/general_error.phtml');
    return;
}


if (!method_exists($className, $method)) {

    $view->assign('errorMessage', "Can not find method [{$method}] in class [{$className}]  in the file [{$classPath}]");
    $view->display('error/general_error.phtml');
    return;
}

$controller = new $className;

$controller->setView($view);

$view->setTemplate("{$controllerName}/{$method}.phtml");



try {
    $preResult = true;
    if (method_exists($controller, 'pre_filter')) {

        $preResult = $controller->pre_filter($method);
    }


    if ($preResult !== false) {

        $view->assign('title', "{$className}->{$method}");

        $controller->$method($parameters);
    }

    if (method_exists($controller, 'post_filter')) {

        $controller->post_filter($method);
    }

    if ($controller->redirect_url === null) {

        $v = $controller->getView();
        if ($v !== null) {
            $v->rendering();
        }
    } else {
        header("Location: {$controller->redirect_url}");
    }
} catch (Exception $e) {

    $ee = "Exception:" . $e->getMessage() . ' in file:' . $e->getFile() . ' line [' . $e->getLine() . ']';
    MLog::e($e->getTraceAsString());
    $view->assign('errorMessage', $ee);
    $view->display('error/general_error.phtml');
    return;
}
?>