<?php
use core\Model;
use helpers\App;
use helpers\Help;

class Model_Options extends Model
{
    function __construct()
	{		
		
	}  
	
    public function getRecords()
    {

    	$this->response = App::$db->getAllCar();

		return $this->response ;
	}    

	public function newCar($params)
	{
		App::$db->insertNewCar($params);
	}

	public function deleteCar($params)
	{
		App::$db->deleteCar($params);
	}

	
}