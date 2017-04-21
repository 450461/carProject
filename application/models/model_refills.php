<?php
use core\Model;
use helpers\App;

class Model_Refills extends Model
{

	const TABLE_NAME = 'refills';

    function __construct()
	{
		//проверка существования таблицы в бд
		if ( App::$db1->checkTable(self::TABLE_NAME) )
		{
			// return false;
		}

	} 
	

	//получить данные по всем заправкам  
    public function getRecords($parameters='')
    {
		//прикручиваем в запрос фильтр по машинам
		// $queryFileter = array();
		$queryFileter = [
			'car'=>'',
			'fuel'=>'',
			'azs'=>'',
		];

// error_log( print_r( $parameters , true) ); 

		//изначально запрос без фильтров 
		$sql = "SELECT re.id, re.data, re.price_litr, re.flooded_litr, re.sum_refill, re.comment,";
		$sql.= " az.name as azs_id, ca.name as car_id, fu.name as fuel_id";
		$sql.= " FROM ".self::TABLE_NAME." re";
		$sql.= " LEFT JOIN cars ca ON re.car_id=ca.id";
		$sql.= " LEFT JOIN azs az ON re.azs_id=az.id";
		$sql.= " LEFT JOIN fuels fu ON re.fuel_id=fu.id";

		//машина по умолчанию 
		$defaultCar = App::$appConfig['default']['car'];

		//добавляем фильтры к запросу
		if ( gettype($parameters)==='string' || empty($parameters) )
		{
			//если параметры не пришли, тогда выбираем из бд все заправки для машины по умолчанию
			// $queryFileter['car'] = ' WHERE `car_id`='.$defaultCar;
			// $query .= $queryFileter['car'];
			// error_log( '- - - - - zx', 0 ); 
		}else{

			//если данные для фильтра пришли то к запросу добавляем условие 'WHERE'
			$sql .=' WHERE ';

			//фильтр по машинам, если в фильтре пришли ид машин
			if(!empty($parameters['cars']))
			{
				//если количество машин в фильтре больше 1
				if ( count($parameters['cars'])>0  )
				{
					//добавляем открывающую скобку, которая понадобится если в запросе будут  фильтры по разным сущностям
					$queryFileter['car'] .='(';
					
					//если количество параметров в этом фильтре (по машине) больше одного тогда надо добавить 'OR', иначе просто добавляем фильтр по одной машине
					for ($i=0; $i < count($parameters['cars']); $i++) 
					{ 
						if ( $i>0 )
						{
							$queryFileter['car'] .=' OR `car_id`='.$parameters['cars'][$i];   
						}else{
							$queryFileter['car'] .= '`car_id`='.$parameters['cars'][$i];
						}
					}
					//закрываем скобку фильтра по машине
					$queryFileter['car'] .=')';
					//добавляем полученный результат к изначальному запросу
					$sql .= $queryFileter['car'];
				}
			}else{
				//если не пришли, тогда подставляем машину по умолчанию
				// $query .= '(`car_id`='.$defaultCar.')';
			}

			//и так далее, проходимся по всем сущностям.

			//фильтр по бензинам
			if(!empty($parameters['fuels']))
			{	
				//если в фильтре есть выборка по машинам или заправкам, тогда в запросе понадобится AND 
				if( array_key_exists('cars', $parameters) || array_key_exists('azss', $parameters)){
					$sql .=' AND ';
				}

				if ( count($parameters['fuels'])>0  )
				{
					// $queryFileter['fuel'] = ' WHERE ';
					$queryFileter['fuel'] .='(';

					for ($i=0; $i < count($parameters['fuels']); $i++) 
					{
						if ( $i>0 )
						{
							$queryFileter['fuel'] .=' OR `fuel_id`='.$parameters['fuels'][$i];   
						}else{
							$queryFileter['fuel'] .= '`fuel_id`='.$parameters['fuels'][$i];
						}
					}

					$queryFileter['fuel'] .=')';
					$sql .= $queryFileter['fuel'];
				}
			}

			//фильтр по заправкам
			if(!empty($parameters['azss']))
			{
				//если в фильтре есть выборка по машинам или бензинам, тогда в запросе понадобится AND 
				if( array_key_exists('cars', $parameters) || array_key_exists('fuels', $parameters)){
					$sql .=' AND ';
				}

				if ( count($parameters['azss'])>0  )
				{
					// $queryFileter['azs'] = ' WHERE ';
					$queryFileter['azs'] .='(';

					for ($i=0; $i < count($parameters['azss']); $i++) 
					{
						if ( $i>0 )
						{
							$queryFileter['azs'] .=' OR `azs_id`='.$parameters['azss'][$i];   
						}else{
							$queryFileter['azs'] .= '`azs_id`='.$parameters['azss'][$i];
						}
					}
					$queryFileter['azs'] .=')';
					$sql .= $queryFileter['azs'];
				}
			}
		}

/**/
// error_log( print_r( ($query), true ) );
// error_log( print_r( $parameters , true) ); 
/**/

	    try {
	    	$query = App::$db1->mysqli->prepare($sql." ORDER BY data DESC");

	    	$query->execute();
	    	$refills = $query->fetchAll();

	    	$this->response['data']=$refills;
			$this->response['status'] = 200;

	    } catch (\PDOException $e) {

			$this->response['data'] = $e->getMessage();
	    	$this->response['status'] = 500;

			error_log( print_r( $this->response, true ));
		}

		return $this->response;
    }


    //добавить новую заправку
    public function newRefill($param)
	{
		try {

			$sql = "INSERT INTO ".self::TABLE_NAME;
			$sql.= " (car_id, data, fuel_id, price_litr, flooded_litr, sum_refill, azs_id, comment)";
			$sql.= " VALUES (?,?,?,?,?,?,?,?)";

			$query = App::$db1->mysqli->prepare($sql);

			if (!$param['comment']) {
				$param['comment'] = false;
			};

			//используется в случае с именованными плейсхолдерами
			// $query->bindParam();

	    	$query->execute(array($param['car_id'], $param['data'], $param['fuel_id'], $param['price_litr'], $param['flooded_litr'], $param['sum_refill'], $param['azs_id'], $param['comment']));

	    	$this->response['data'] = '';
	    	$this->response['status'] = 201;

		} catch (\PDOException $e) {

			$this->response['data'] = $e->getMessage();
	    	$this->response['status'] = 500;

			error_log( print_r( $this->response, true ));

		}
	    	return $this->response;
	}


    //удалить заправку
	public function deleteRefill($params)
	{
		try {

			$sql = "DELETE FROM ".self::TABLE_NAME;
			$sql.= " WHERE id=:refill";

			$query = App::$db1->mysqli->prepare($sql);
			$query->bindParam(':refill', $params['id']);
	    	$query->execute();

	    	$this->response['data'] = '';
	    	$this->response['status'] = 204;

		} catch (\PDOException $e) {

			$this->response['data'] = $e->getMessage();
	    	$this->response['status'] = 500;

	    	error_log( print_r( $this->response, true ));
		}

	    	return $this->response;
	}


    //редактировать заправку
	public function editRefill($params)
	{
		try {

			$sql = "UPDATE ".self::TABLE_NAME;
			$sql.= " SET";
			$sql.= " data=:new_data, fuel_id=:new_fuel_id, price_litr=:new_price_litr,";
			$sql.= " flooded_litr=:new_flooded_litr, sum_refill=:new_sum_refill,";
			$sql.= " azs_id=:new_azs_id, comment=:new_comment";
			$sql.= " WHERE id=:id";

			$query = App::$db1->mysqli->prepare($sql);

			$query->bindParam(':new_data', $params['data']);
			$query->bindParam(':new_fuel_id', $params['fuel_id']);
			$query->bindParam(':new_price_litr', $params['price_litr']);
			$query->bindParam(':new_flooded_litr', $params['flooded_litr']);
			$query->bindParam(':new_sum_refill', $params['sum_refill']);
			$query->bindParam(':new_azs_id', $params['azs_id']);
			$query->bindParam(':new_comment', $params['comment']);
			$query->bindParam(':id', $params['id']);
	    	$query->execute();

	    	$this->response['data'] = '';
	    	$this->response['status'] = 204;

		} catch (\PDOException $e) {

			$this->response['data'] = $e->getMessage();
	    	$this->response['status'] = 500;

			error_log( print_r( $this->response, true ));
		}
		
	    return $this->response;
	}


	//стоимость бензина при последней заправке, либо стоимость выбранного бензина при последней заправке 
	public function lastRefill($fuel_id = false)
	{
		//если передан ид бензина, то добавляем в запрос условие
		if ($fuel_id)
		{
			$fuel_id = 'WHERE `fuel_id`='.$fuel_id;
		}
		//получает id последней вставленой строки 

		$sql ="SELECT MAX(`id`)";
		$sql.=" FROM ".self::TABLE_NAME;
		$sql.=" ".$fuel_id;

		// error_log( print_r( $sql, true ));

		$query = App::$db1->mysqli->prepare($sql);
		$query->execute();
		$lastId = $query->fetch()['MAX(`id`)'];

		//$DBH->lastInsertId();

		$sql = '';
		$sql ="SELECT price_litr, data";
		$sql.=" FROM ".self::TABLE_NAME;
		$sql.=" WHERE id=".$lastId;

		//вытаскивает цену бензина при этой заправке
		$query = App::$db1->mysqli->prepare($sql);
		$query->execute();
		$lastFuelPrice = $query->fetch();

    	return $lastFuelPrice;
	}


	//количество заправок  или залитого бензина по конкретному ид
	public function summ($data)
    {
	    $instance = '';
	    switch ($data['instance'])
	    {
	    	case 'azs': $instance = 'azs_id'; $lastFuelPrice['price_litr']=''; break;
	    	case 'fuel': $instance = 'fuel_id'; $lastFuelPrice = $this->lastRefill($data['id']); break;
	    }


	    $sql = "SELECT COUNT(".$instance."), SUM(sum_refill)";
	    $sql.= " FROM ".self::TABLE_NAME;
	    $sql.= " WHERE ".$instance."=".$data['id'];


    	$query = App::$db1->mysqli->prepare($sql);
	    $query->execute();
	    $summ = $query->fetch();

	    return [
	    	'id'=>$data['id'],
	    	'count'=>(int) $summ['COUNT('.$instance.')'],
	    	'sum_refill'=>round ((float) $summ['SUM(sum_refill)'], 2),
	    	'lastFuelPrice' => $lastFuelPrice,
	    ];

    }
	
}