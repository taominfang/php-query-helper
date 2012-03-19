<?php




if (!function_exists('curPageURL')) {
	//error_log("define fucntion:curPageURL");
	function curPageURL() {
		$pageURL = 'http';
		if (isset($_SERVER["HTTPS"]) &&  $_SERVER["HTTPS"] == "on")
		{
			$pageURL .= "s";
		}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}

}




if (!function_exists('dt')) {
	//error_log("define fucntion:dt");
	/**
	* Alias for debug().
	*/
	function dt($message,$packed)
	{
		//error_log("hello");

		$allDPath=debug_backtrace();
		$printOutStr="\n";
		$levelNumber=0;
		$buffArray=array();
		$callTraceSum=0;
		$lastFileLine=-1;
		
		$_lastFileName="";
		$_lastLine="";
		foreach ($allDPath as $one){
			if(isset($packed)){
				$buffArray[]="(".
				((isset($one['type']) && isset($one['class']))?$one['class'].$one['type']:'').
				(isset($one['function'])?$one['function']:'').") [".
				(isset($one['file'])?$one['file']:'')."] <".
				(isset($one['line'])?$one['line']:'').">";
				
				if(isset($one['line'])){
					//avoid dupicate calculate the sum
					if(isset($one['file'])){
						$_fn=$one['file'];
					}
					else{
						$fn="";
					}
					if($_lastFileName != $_fn || $_lastLine != $one['line']){
						$callTraceSum+=(int)$one['line'];
					}
					
					$_lastLine=$one['line'];
					$_lastFileName=$_fn;
					
					
					if($lastFileLine == -1){
						$lastFileLine=(int)$one['line'];
					}
				}
			}
			else{
				//error_log(var_export($one,true));
				$printOutStr.="Level [".$levelNumber."] \n";
				$space="    ";
				foreach($one as $key => $value){
					$strr=$space.$key;
					$vt=gettype($value);
					if($vt=='string'){
						$strr.=" -- > ". $value;
					}
					else if( $vt == 'integer'){
						$strr.=":". $value;
					}
					else if( $vt == 'object'){
						$strr.=" -- > Class:". get_class($value);
					}
					else if( $vt == 'array'){
						//$strr.=" -- > array:". var_export($value,true);
						$strr.=" -- > it is array, ignore it";
					}
					else{
						$strr.=", Sorry Can not get string inforamtion from type:".$vt;
					}
					$printOutStr.=$strr."\n";

					$space.="    ";
				}
				$levelNumber++;
			}
		}

		$callTraceSum-=$lastFileLine;
		if(isset($packed)){
				
			global $callin_url_id;
			$str1=$message.$callin_url_id.' --call trace sum:'.$callTraceSum."\n";
			$str2="";
			for($i1=count($buffArray)-1;$i1>=0;$i1--){
				$str1.=$str2.$buffArray[$i1]."\n";
				$str2.="--";
				
			}
			error_log($str1);
		}
		else{
			$printOutStr.="Income Url:".curPageURL()."\n";
			error_log($printOutStr);
		}
	}
}


if (!function_exists('dd')) {


	//error_log("define fucntion:dd");
	/**
	* Alias for debug().
	*/
	function dd( $var,$id="")
	{


		if(!empty($id)){
			error_log($id);
		}
		if(isset($var)){
			error_log(var_export($var,true));
		}

	}
}

if(!function_exists('_debug_simplexmlload_file')){
	function _debug_simplexmlload_file($url){
		global  $callin_url_id;
		
		
		$start_time=microtime(true);
		
		$re=simplexml_load_file($url);
		
		$spendTime= number_format(microtime(true)-$start_time, 3, '.', '');
		
		dt("\n\n\n".
		'****************************************************************************'.
		"\nMy Thread id"."{$callin_url_id} call function: simplexml_load_file \nURL:::{$url}".
		"\nSpend time:{$spendTime}\n",1);
		
		
		return $re; 
	}
}


function redirect($newUrl){
	header('Location:'.$newUrl);
}
function debug($message){
	error_log( $message);
	//echo substr($message,0,300)."\n";
}

function load_one_assoc_from_sql($db,$sql){
	$result=run_sql($db,$sql);

	if($result->num_rows >0){
		$row=$result->fetch_assoc();
		$result->close();
		return $row;
	}
	$result->close();
	return array();
}

function fetch_assoc_arrays_from_sql($db,$sql){
	$result=run_sql($db,$sql);
	$re=array();
	if($result->num_rows >0){
		while($row=$result->fetch_assoc()){
			$re[]=$row;
		}
	}
	$result->close();
	return $re;
}

function fetch_arrays_from_sql($db,$sql){
	$result=run_sql($db,$sql);
	$re=array();
	if($result->num_rows >0){
		while($row=$result->fetch_row()){
			$re[]=$row;
		}
	}
	$result->close();
	return $re;
}


function run_sql($db,$sql,$mode=MYSQLI_STORE_RESULT){
	debug($sql);
	$st=microtime(true);
	$result=$db->query($sql,$mode);
	$sp=sprintf('%.3f',(microtime(true)-$st));
	debug("The sql Spend {$sp} seconds");
	if(!$result){
		throw new Exception("SQL :[$sql] is wrong, error message from mysql:{$db->error}");
	}



	return $result;
}

?>