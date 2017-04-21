<?php
//header('Content-Type: text/html; charset=utf-8');	
//header('Content-Type: application/json');	

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

use classes\App;

const DIRSEP = DIRECTORY_SEPARATOR;
require_once '..'.DIRSEP.'application'.DIRSEP.'autoload.php';
require_once '..'.DIRSEP.'application'.DIRSEP.'start_app.php';
