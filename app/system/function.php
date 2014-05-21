<?php

function redirect($newUrl){
	header('Location:'.$newUrl);
	exit;
}



?>