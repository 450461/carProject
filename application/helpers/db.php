<?php
namespace helpers;

class Db{
	public $mysqli;
	private $db_host;
	private $db_name;
	private $db_port;
	private $db_user;
	private $db_pswd;
	
	public function __construct()
	{
		$db_host = App::$appConfig['db']['db_host'];
		$db_name = App::$appConfig['db']['db_name'];
		$db_user = App::$appConfig['db']['db_user'];
		$db_pswd = App::$appConfig['db']['db_pass'];

		if ($this->mysqli) return $this->mysqli;
		
			$dsn = "mysql:host=".$db_host.";dbname=".$db_name.";";
			$options = array(
				\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
				\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
			);
			try {
				$this->mysqli = new \PDO($dsn, $db_user, $db_pswd, $options);
			} catch (\PDOException $e) {
				print "Ошибка подключения к базе данных: " . $e->getMessage() . "<br/>";
				die('Проверьте настройки');
			}


	}
	
	public function select($query, $params=false)
	{
		$obResult = $this->mysqli->query($query, $params);
		if(!$obResult) return false;			
		return $obResult;
	}

	public function fetchData($model)
	{
		$arr=[];
		while($row = $model -> fetch_assoc())
		{
			$arr[] = $row ;

		}	
		
		return $arr;
	}

	public function checkTable( $tableName )
	{
		$query = App::$db1->mysqli->prepare("SHOW TABLES");
		$res = $query->execute();
		$res = $query->fetchAll();

		$arrTables = [];
		//смотрит на наличие таблицы, название которой задано в константе в базе, имя которой берется конфига 
		foreach($res as $table){
			$arrTables[] = $table["Tables_in_".App::$appConfig['db']['db_name']];
		};

		return (in_array($tableName, $arrTables)) ? true : false;
	}
}