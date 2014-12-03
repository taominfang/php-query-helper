<?php

spl_autoload_register('my_autoloader');
$currentDir = dirname(__FILE__);
include_once $currentDir . '/function.php';
include_once $currentDir . '/log.php';
include_once $currentDir . '/view.php';
include_once realpath($currentDir . '/../config') . '/system.php';
include_once realpath($currentDir . '/../config') . '/data_source.php';

date_default_timezone_set('America/Los_Angeles');



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
    error_showing("class file [{$classPath}] is not exist");

    return;
}

$parameters = array();

for ($i1 = 3; isset($uris[$i1]); $i1++) {
    $parameters = $uris[$i1];
}




include_once 'basic_controller.php';
include_once $classPath;

if (!class_exists($className)) {


    error_showing("Can not find class [{$className}]  in the file [{$classPath}]");
    return;
}


if (!method_exists($className, $method)) {

    error_showing("Can not find method [{$method}] in class [{$className}]  in the file [{$classPath}]");

    return;
}

$controller = new $className;


$viewClass = $controller->getViewClass();

if ($viewClass === null) {

    $view = new view();
} else {
    $view = new $viewClass;
}

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
    error_showing($ee);
    return;
}

function error_showing($errorMessage) {
    $v = new view();
    $v->assign('errorMessage', $errorMessage);
    $v->display('error/general_error.phtml');
}

function my_autoloader($class) {


    if (strpos($class, "db_") === 0) {
        $dbClassFile = realpath(dirname(__FILE__)) . '/../models/db/' . $class . '.php';
        if (is_file($dbClassFile)) {
            include_once $dbClassFile;
            return;
        }
    }


    if (strpos($class, "view_") === 0) {
        $viewClassFile = realpath(dirname(__FILE__)) . '/../views/containers/' . $class . '.php';
        if (is_file($viewClassFile)) {
            include_once $viewClassFile;
            return;
        } else {
            error_log("{$viewClassFile} is not a file");
        }
    }

    if (strpos($class, "Wrapper") !== false) {
        $viewClassFile = realpath(dirname(__FILE__)) . '/../extends/wrappers/' . $class . '.php';
        if (is_file($viewClassFile)) {
            include_once $viewClassFile;
            return;
        } else {
            error_log("{$viewClassFile} is not a file");
        }
    }
}

?>