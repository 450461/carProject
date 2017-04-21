<?php

function autoloader_app($id){
	$id=mb_strtolower(str_replace('\\', '/', $id));
	include $id.'.php';
}
spl_autoload_register('autoloader_app');