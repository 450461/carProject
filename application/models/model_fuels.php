<?php
use core\Model;
use helpers\App;
use helpers\Help;

class Model_Fuels extends Model
{
	public $id;
	public $name;
	const TABLE_NAME = 'fuels';

    function __construct()
	{
		//проверка существования таблицы в бд
		if ( App::$db1->checkTable(self::TABLE_NAME) )
		{
			// return false;
		}
	}


	public function newFuel($params)
	{
		try {
			$sql = "INSERT INTO ".self::TABLE_NAME;
			$sql.= " (name)";
			$sql.= " VALUES (:fuel)";

	    	$query = App::$db1->mysqli->prepare($sql);
			$query->bindParam(':fuel', $params['name']);
	    	$query->execute();

	    	return true;

		} catch (\PDOException $e) {

			$this->response['data'] = $e->getMessage();
	    	$this->response['status'] = 500;

	    	return false;
		}
	}


	public function edit($params)
	{
		try {
			$sql = "UPDATE ".self::TABLE_NAME;
			$sql.= " SET name=:newName";
			$sql.= " WHERE id=:id";

	    	$query = App::$db1->mysqli->prepare($sql);
			$query->bindParam(':newName', $params['name']);
			$query->bindParam(':id', $params['id']);
	    	$query->execute();

	    	return true;

		} catch (\PDOException $e) {

			$this->response['data'] = $e->getMessage();
	    	$this->response['status'] = 500;

	    	return false;
		}

	}


	public function delete()
	{
		try {
			$sql = "DELETE FROM ".self::TABLE_NAME;
			$sql.= " WHERE id=:id";

			$query = App::$db1->mysqli->prepare($sql);
			$query->bindParam(':id', $this->id);
	    	$query->execute();

	    	return true;

		} catch (\PDOException $e) {

			$this->response['data'] = $e->getMessage();
	    	$this->response['status'] = 500;

	    	return false;
		}
	}


    public static function findOne($id)
    {
    	$fuel = new Model_Fuels;
    	try {
 			$sql = "SELECT name, id";
			$sql.= " FROM ".self::TABLE_NAME;
			$sql.= " WHERE id=:id";
			   		
	    	$query = App::$db1->mysqli->prepare($sql);
	    	$query->bindParam(':id', $id);
	    	$query->execute();
	    	$row = $query->fetch();

	    	if (isset($row['id'])){
		    	$fuel->id = $row['id'];
		    	$fuel->name = $row['name'];

	    		return $fuel;
		    }else{
		    	
		    	return null;
		    }

	    } catch (\PDOException $e) {

			$fuel = $e->getMessage();
		}	    
	}


    public static function findAll()
    {
    	$fuels = new Model_Fuels;

    	try {	
    		$sql = "SELECT name, id";
    		$sql.= " FROM ".self::TABLE_NAME;

	    	$query = App::$db1->mysqli->query($sql);
	    	$query->execute();
	    	$fuels = $query->fetchAll();

	    	return $fuels;

	    } catch (\PDOException $e) {

			$fuels = $e->getMessage();
		}	    
	}
	
}