<?php

class Log{

	public static  $DEBUG=1;
	public static  $INFO=2;
	public static  $ERROR=3;
	public static  $NONE=4;

	public static $level=1;

	public static function dd(&$printArray)
	{



		$outputString=PHP_EOL;
		$line=1;
		foreach ($printArray as &$v){


			if(is_string($v) || is_numeric($v) || is_bool($v) ){
				$outputString.=$line.'. ('.gettype($v).')'.$v.PHP_EOL;
			}
			else{
				$outputString.=$line.'. '.var_export($v,true).PHP_EOL;

			}

			$line++;

		}
		error_log($outputString);

	}

	public static function d(){
		if(Log::$level > Log::$DEBUG){
			return;
		}
		$printArray=array();
		for($i1=0,$pSize=func_num_args();$i1<$pSize;$i1++){


			$printArray[]=func_get_arg($i1);

		}

		if($i1 == 0){
			return;
		}
		error_log("Total:".$i1);

		self::dd($printArray);

	}
	public static function i(){
		if(Log::$level > Log::$INOF){
			return;
		}
		$printArray=array();
		for($i1=0,$pSize=func_num_args();$i1<$pSize;$i1++){


			$printArray[]=func_get_arg($i1);

		}

		if($i1 == 0){
			return;
		}

		self::dd($printArray);

	}
	public static function e(){
		if(Log::$level > Log::$ERROR){
			return;
		}
		$printArray=array();
		for($i1=0,$pSize=func_num_args();$i1<$pSize;$i1++){


			$printArray[]=func_get_arg($i1);

		}

		if($i1 == 0){
			return;
		}

		self::dd($printArray);

	}

	public static  function curPageURL() {
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

	public static  function dException(Exception $e){
		if(Log::$level > Log::$DEBUG){
			return;
		}
		self::printException($e);
	}

	public  static  function iException(Exception $e){
		if(Log::$level > Log::$INFO){
			return;
		}
		self::printException($e);
	}

	public static  function eException(Exception $e){
		if(Log::$level > Log::$ERROR){
			return;
		}
		self::printException($e);
	}

	public static  function printException(Exception $e){
		error_log($e->getTraceAsString());
	}

	public static  function callingTrace($message='call trace')
	{
		if(Log::$level > Log::$DEBUG){
			return;
		}

		$allDPath=debug_backtrace();

		$levelNumber=0;
		$buffArray=array();
		$callTraceSum=0;
		$lastFileLine=-1;

		$_lastFileName="";
		$_lastLine="";
		foreach ($allDPath as $one){

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


				$_lastLine=$one['line'];
				$_lastFileName=$_fn;


				if($lastFileLine == -1){
					$lastFileLine=(int)$one['line'];
				}
			}


		}




		global $callin_url_id;
		$str1='current url:'.self::curPageURL().PHP_EOL.$message.$callin_url_id.PHP_EOL;
		$str2="";
		for($i1=count($buffArray)-1;$i1>=0;$i1--){
			$str1.=$str2.$buffArray[$i1].PHP_EOL;
			$str2.="--";

		}
		error_log($str1);

	}

	
	public static function  logException(Exception $e){
		$allDPath=$e->getTrace();
		$printOutStr="\n";
		$levelNumber=0;
		$buffArray=array();
		$callTraceSum=0;
		$lastFileLine=-1;
	
		$_lastFileName="";
		$_lastLine="";
		$message=$e->getMessage(). ", Excption trace:";
		foreach ($allDPath as $one){
	
	
			if(!empty($one['function']) && !empty($one['file']) && strpos($one['file'], "function.php") && $one['function']=="logException"){
				//continue;
			}
	
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
	
		$callTraceSum-=$lastFileLine;
	
	
		global $callin_url_id;
		$str1=$message."\n";
		$str2="";
		for($i1=count($buffArray)-1;$i1>=0;$i1--){
			$str1.=$str2.$buffArray[$i1]."\n";
			$str2.="--";
	
		}
		error_log($str1);
	
	}
	

}



?>