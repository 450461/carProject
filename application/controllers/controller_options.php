<?php
use core\Controller;
use core\View;
use helpers\Help;

class Controller_Options extends Controller
{   
    function __construct()
	{
		$this->model = new Model_Options();
		$this->view = new View();
	}

    function action_index()
    {
        $this->response['data'] = helpers\App::$appConfig['default'];
        $this->response['status'] = 200;

        return $this->response ;  
    }
    
    function action_edit($params)
    {

        $currentConf = array(
            'car' => helpers\App::$appConfig['default']['car'],
            'fuel' => helpers\App::$appConfig['default']['fuel'],
            'azs' => helpers\App::$appConfig['default']['azs'],
        );
        if ( $currentConf['car']!=$params['car'] 
                || $currentConf['fuel']!=$params['fuel']
                    ||$currentConf['azs']!=$params['azs'] )
        {
            //удаляем лишний элемент в массиве
            unset($params['action']);
            helpers\Config::setValues($params);
        }


		// $this->response['data'] = App::$appConfig['db_host'];
        $this->response['status'] = 200;

        return $this->response ;		
    }

    function action_new($params)
    {
        print_r( $params );
    	$this->model->newCar($params);
    }


}