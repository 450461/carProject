<?php

namespace helpers;


class Config{

	const DB_CONF = '../application/etc/db.conf';
	const DEFAULT_VALUES = '../application/etc/defaultValues';

	public function __construct()
	{

		return $this->get();
		// error_log(print_r($config, true));
	}
	

	public static function get()
	{
		$appConfig = [];// $data = array();

	    $file = fopen(Config::DB_CONF, 'r');
	    if ($file){

	    	while (!feof($file)) 
	    	{
		        $line = fgets($file, 9999);
		 		
		 		$line = preg_split("/[=]/", $line);
		 		switch ($line[0]){
		 			case 'db_host':	$appConfig['db']['db_host'] = trim($line[1]); break;
		 			case 'db_port':	$appConfig['db']['db_port'] = trim($line[1]); break;
		 			case 'db_name':	$appConfig['db']['db_name'] = trim($line[1]); break;
		 			case 'db_user':	$appConfig['db']['db_user'] = trim($line[1]); break;
		 			case 'db_pass':	$appConfig['db']['db_pass'] = trim($line[1]); break;
		 		}
			}			
		 	fclose ($file);		 	
		}else{
			echo 'Error read DB configuration file'.Config::DB_CONF;
		}

		$file = false;

		$file = fopen(Config::DEFAULT_VALUES, 'r');
		if ($file){

	    	while (!feof($file)) 
	    	{
		        $line = fgets($file, 9999);
		 		
		 		$line = preg_split("/[=]/", $line);
		 		switch ($line[0]){
		 			case 'car':	$appConfig['default']['car'] = trim($line[1]); break;
		 			case 'fuel':	$appConfig['default']['fuel']= trim($line[1]); break;
		 			case 'azs':	$appConfig['default']['azs'] = trim($line[1]); break;
		 		}
			}			
		 	fclose ($file);		 	
		}else{
			echo 'Error read file with default values '.Config::DEFAULT_VALUES;
		}

		return $appConfig;
	}

	public static function setValues($valuesArr)
	{
		$file = fopen(Config::DEFAULT_VALUES, 'w');
		if (is_writeable(Config::DEFAULT_VALUES))
		{
			foreach ($valuesArr as $item => $value)
			{
				fwrite($file, $item.'='.$value."\n");
				echo $item.'='.$value."\n";
			}
		}else{
			error_log( 'Error writing to file', 0 );
		}
		fclose ($file);
	}

	public static function test()
	{
		error_log(' - - - -', 0);
	}

}