<?php
namespace helpers;

class Router
{   
    private $instance;
    private $routerData ;	//данные по маршруту
    private $routes;
    private $response;	//внутреняя переменная для ответа фронту

	function __construct()
	{		

		// смотрим что за url пришел и разбираем его в массив 
		$this->routes = explode('/', $_SERVER['REQUEST_URI']);

		 // удаляет элемент со слешем
		array_shift($this->routes);

		 // если первый элемент апи значит обращение к апи
		if ($this->routes[0]==='api')
		{
			$this->httpMethod();
			$this->response = $this->start_action();
			$this->send_response();
			
		}else{

			// если нет то обращение html шаблону
			$this->start_template();
		}
	}

	private function start_template()
	{
		include 'template.html';
	}


	private function start_action()
	{

		$action_name = $this->routerData['action'];
		$controller_name = $this->routerData['controller'];

		$model_name = 'Model_'.$controller_name;
		$controller_name = 'Controller_'.$controller_name;
		$action_name = 'action_'.$action_name;

		$model_file = strtolower($model_name).'.php';
		$model_path = '..'.DIRSEP."application/models/".$model_file;
		if(file_exists($model_path))
		{
			include '..'.DIRSEP."application/models/".$model_file;
		}

		$controller_file = strtolower($controller_name).'.php';
		$controller_path = '..'.DIRSEP."application/controllers/".$controller_file;

		include '..'.DIRSEP."application/controllers/".$controller_file;

        $controller = new $controller_name;
		$action = $action_name;

        if(method_exists($controller, $action))
		{
			return $controller->$action( $this->routerData['parameters'] );
		}else{
			// $this->ErrorPage404();
		}

	}


   
	private function httpMethod()
	{

		$this->routerData['controller'] = $this->routes[1];
		$this->routerData['parameters'] = '';	//по умолчанию


		switch (strtolower($_SERVER['REQUEST_METHOD'])) 
		{
			case 'get':

					if( empty($this->routes[2]) || isset($_GET['params']))	//	/refills
					{
						$this->routerData['action'] = 'index';

						if(!empty($_GET)){

							$this->routerData['parameters']= $_GET;
						}
					}elseif($this->routes[2] == 'lastrefill')	//	/refills/lastrefill
					{
						$this->routerData['action'] = 'lastrefill';

					}elseif($this->routes[1].$this->routes[2] == 'refills'.$this->routes[2])	//	/refills/azs/18
					{
						$this->routerData['parameters']['instance'] = $this->routes[2];
						$this->routerData['parameters']['id'] = $this->routes[3];
						$this->routerData['action'] = 'summ';

					}else{	//	/azs/18

						$this->routerData['parameters']['id'] = $this->routes[2];
						$this->routerData['action'] = 'findOne';
					}

				break;

			case 'post':
					$this->routerData['parameters'] = json_decode(file_get_contents('php://input'),true);
					$this->routerData['action'] = 'new';
				break;

			case 'put':
					//порядок строк должен быть соблюден
					$this->routerData['parameters'] = json_decode(file_get_contents('php://input'),true);	//вытаскиваем данные
					$this->routerData['parameters']['id'] = $this->routes[2];	//берем ид из урл
					$this->routerData['action'] = 'edit';	//action редактирование
				break;

			case 'delete':				
					$this->routerData['parameters']['id'] = $this->routes[2];	//берем ид из урл
					$this->routerData['action'] = 'delete';
				break;
		}

// error_log( print_r($this->routes, true) );
// error_log( print_r($this->routerData, true) );
		
	}


	//рендерит ответ в json и отправляет фронтэнду
	private function send_response()
	{
		header("HTTP/1.1 ".$this->response['status']);	//отправляет статус ответа
		header('Content-Type: application/json; charset=utf-8');	//тип данных в ответе
		echo json_encode($this->response['data'], JSON_UNESCAPED_UNICODE);	//данные по запросу

	}


}