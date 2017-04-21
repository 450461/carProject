<?php
use core\Model;
use helpers\App;
use helpers\Help;

class Model_Cars extends Model
{
	public $id;
	public $name;
	public $model;
	public $color;
	public $engine;
	public $gearbox;
	public $power;
	public $date_release;
	public $date_purchase;
	public $date_sale;
	const TABLE_NAME = 'cars';

    function __construct()
	{
		//проверка существования таблицы в бд
		if ( App::$db1->checkTable(self::TABLE_NAME) )
		{
			// return false;
		}
	}


	public function newCar($params)
	{

		// error_log(print_r($params, true));
		
		try {
			$sql ="INSERT INTO ".self::TABLE_NAME;
			$sql.=" (name,model,color,engine,gearbox,power,date_release,date_purchase,date_sale)";
			$sql.=" VALUES (:new_name,:new_model,:new_color,:new_engine,:new_gearbox,:new_power,:new_date_release,:new_date_purchase,:new_date_sale)";

	    	$query = App::$db1->mysqli->prepare($sql);
			$query->bindParam(':new_name', $params['name']);
			$query->bindParam(':new_model', $params['model']);
			$query->bindParam(':new_color', $params['color']);
			$query->bindParam(':new_engine', $params['engine']);
			$query->bindParam(':new_gearbox', $params['gearbox']);
			$query->bindParam(':new_power', $params['power']);
			$query->bindParam(':new_date_release', $params['date_release']);
			$query->bindParam(':new_date_purchase', $params['date_purchase']);
			$query->bindParam(':new_date_sale', $params['date_sale']);
	    	$query->execute();

	    	return true;

		} catch (\PDOException $e) {
// error_log($e->getMessage(), 0);
			$this->response['data'] = $e->getMessage();
	    	$this->response['status'] = 500;

	    	return false;
		}
	}


	public function edit($params)
	{
		try {
	 		$sql ="UPDATE ".self::TABLE_NAME;
	 		$sql.=" SET";
	 		$sql.=" name=:new_name,model=:new_model,color=:new_color,engine=:new_engine,gearbox=:new_gearbox,power=:new_power,date_release=:new_date_release,date_purchase=:new_date_purchase,date_sale=:new_date_sale";
	 		$sql.=" WHERE id=:id";

	    	$query = App::$db1->mysqli->prepare($sql);
			$query->bindParam(':new_name', $params['name']);
			$query->bindParam(':new_model', $params['model']);
			$query->bindParam(':new_color', $params['color']);
			$query->bindParam(':new_engine', $params['engine']);
			$query->bindParam(':new_gearbox', $params['gearbox']);
			$query->bindParam(':new_power', $params['power']);
			$query->bindParam(':new_date_release', $params['date_release']);
			$query->bindParam(':new_date_purchase', $params['date_purchase']);
			$query->bindParam(':new_date_sale', $params['date_sale']);
			$query->bindParam(':id', $params['id']);
	    	$query->execute();

	    	return true;

		} catch (\PDOException $e) {

			$this->response['data'] = $e->getMessage();
	    	$this->response['status'] = 500;        
error_log($e->getMessage(), 0);

	    	return false;
		}

	}


	public function delete()
	{
		try {
			$sql ="DELETE FROM ".self::TABLE_NAME;
			$sql.=" WHERE id=:id";

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
    	$car = new Model_Cars;
    	try {
    		$sql ="SELECT *";
    		$sql.=" FROM ".self::TABLE_NAME;
    		$sql.=" WHERE id=:id";

	    	$stmt = App::$db1->mysqli->prepare($sql);
	    	$stmt->bindParam(':id', $id);
	    	$stmt->execute();
	    	$row = $stmt->fetch();

	    	if (isset($row['id'])){
		    	$car->id = $row['id'];
		    	$car->name = $row['name'];
		    	$car->model = $row['model'];
				$car->color = $row['color'];
				$car->engine = $row['engine'];
				$car->gearbox = $row['gearbox'];
				$car->power = $row['power'];
				$car->date_release = $row['date_release'];
				$car->date_purchase = $row['date_purchase'];
				$car->date_sale = $row['date_sale'];

	    		return $car;
		    }else{
		    	
		    	return null;
		    }

	    } catch (\PDOException $e) {

			$car = $e->getMessage();
		}
	}


    public static function findAll()
    {
    	$cars = new Model_Cars;
    	try {	
    		$sql = "SELECT name, id";
    		$sql.= " FROM ".self::TABLE_NAME;

	    	$query = App::$db1->mysqli->query($sql);
	    	$query->execute();
	    	$cars = $query->fetchAll();

	    	return $cars;

	    } catch (\PDOException $e) {

			$car = $e->getMessage();
		}	    
	}
	
}