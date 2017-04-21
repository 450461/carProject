<?php
use core\Controller;
use core\View;
use helpers\Help;

class Controller_Cars extends Controller
{   
    function __construct()
	{
      
	}
    
    function action_index()
    {
        $cars = Model_Cars::findAll();

        $this->response['data'] = $cars;
        $this->response['status'] = 200;

        return $this->response ;		
    }


    function action_new($params)
    {
        $car = new Model_Cars();

        if ($car->newCar($params)){

            $this->response['data'] = '';
            $this->response['status'] = 204;
        }else{

            $this->response['data'] = '';
            $this->response['status'] = 500;
        }

        return $this->response ;
    }


    function action_edit($params)
    {
        $car = new Model_Cars();

        $id = $params['id'];
        
        $car = Model_Cars::findOne($id);
        $car->edit($params);

    }


    function action_delete($parameters)
    {
        $this->response['data'] = '';
        $this->response['status'] = 204;

        $car = Model_Cars::findOne($parameters['id']);

        if (is_object($car))
        {
            $car->delete();

            $this->response['data'] = '';
            $this->response['status'] = 204;

        }else{

            $this->response['data'] = 'Resouse not found';
            $this->response['status'] = 404;
        }
       
       return $this->response ;
    }


    function action_findOne($parameters)
    {
        $car = Model_Cars::findOne($parameters['id']);

        if (is_object($car))
        {
            $this->response['data'] = $car;
            $this->response['status'] = 200;
        }else{
            $this->response['data'] = 'Resouse not found';
            $this->response['status'] = 404;
        }
       
        return $this->response ;
    }

}