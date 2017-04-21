<?php
use core\Model;
use helpers\App;
use helpers\Help;

class Model_Azs extends Model
{
	public $id;
	public $name;
	public $adress;
	public $city;

	const TABLE_NAME = 'azs';

    function __construct()
	{	
		//проверка существования таблицы в бд
		if ( App::$db1->checkTable(self::TABLE_NAME) )
		{
			// return false;
		}
	}


	public function newAzs($params)
	{
		try {
			$sql = "INSERT INTO ".self::TABLE_NAME;
			$sql.= " (name,adress)";
			$sql.= " VALUES (:name, :adress)";

	    	$query = App::$db1->mysqli->prepare($sql);
			$query->bindParam(':name', $params['name']);
			$query->bindParam(':adress', $params['adress']);
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
			$sql.= " SET name=:newName,adress=:newAdress";
			$sql.= " WHERE id=:id";

	    	$query = App::$db1->mysqli->prepare($sql);
			$query->bindParam(':newName', $params['name']);
			$query->bindParam(':newAdress', $params['adress']);
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
    	$azs = new Model_Azs;
    	try {	
			// $sql = "SELECT name, id, adress";
			// $sql.= " FROM ".self::TABLE_NAME;
			// $sql.= " WHERE id=:id";
			$sql = "SELECT az.id, bname.name, az.adress, ci.name as city";
			$sql.= " FROM azs az ";
			$sql.= " LEFT JOIN azs_brandname bname ON az.brandname_id=bname.id";
			$sql.= " LEFT JOIN cities ci ON az.city_id=ci.id";
			$sql.= " WHERE az.id=:id";

	    	$query = App::$db1->mysqli->prepare($sql);
	    	$query->bindParam(':id', $id);
	    	$query->execute();
	    	$row = $query->fetch();

	    	if (isset($row['id'])){
		    	$azs->id = $row['id'];
		    	$azs->name = $row['name'];
		    	$azs->adress = $row['adress'];
		    	$azs->city = $row['city'];

	    		return $azs;
		    }else{
		    	
		    	return null;
		    }

	    } catch (\PDOException $e) {

			$azs = $e->getMessage();
		}	    
	}


    public static function findAll()
    {
    	$azss = new Model_Azs;
    	try {	
    		// $sql = "SELECT name, id, adress";
    		// $sql = "SELECT name, id, adress";
			// $sql.= " FROM ".self::TABLE_NAME;
			$sql = "SELECT az.id, bname.name, az.adress, ci.name as city";
			$sql.= " FROM azs az ";
			$sql.= " LEFT JOIN azs_brandname bname ON az.brandname_id=bname.id";
			$sql.= " LEFT JOIN cities ci ON az.city_id=ci.id";

	    	$query = App::$db1->mysqli->query($sql);
	    	$query->execute();
	    	$azss = $query->fetchAll();

	    	return $azss;

	    } catch (\PDOException $e) {

			$azss = $e->getMessage();
		}	    
	}
	
}