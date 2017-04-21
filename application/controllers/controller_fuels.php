<?php
use core\Controller;
use core\View;
use helpers\Help;

class Controller_Fuels extends Controller
{   
    function __construct()
    {
      
    }
    
    function action_index()
    {
        $fuels = Model_Fuels::findAll();

        $this->response['data'] = $fuels;
        $this->response['status'] = 200;

        return $this->response ;      
    }


    function action_new($params)
    {
        $fuel = new Model_Fuels();

        if ($fuel->newFuel($params)){

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
        $fuel = new Model_Fuels();

        $id = $params['id'];
        
        $fuel = Model_Fuels::findOne($id);
        $fuel->edit($params);
    }


    function action_delete($parameters)
    {
        $this->response['data'] = '';
        $this->response['status'] = 204;

        $fuel = Model_Fuels::findOne($parameters['id']);

        if (is_object($fuel))
        {
            $fuel->delete();

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
        $fuel = Model_Fuels::findOne($parameters['id']);

        $this->response['data'] = $fuel;
        $this->response['status'] = 200;

        return $this->response ;
    }

}