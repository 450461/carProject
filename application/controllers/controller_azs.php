<?php
use core\Controller;
use core\View;
use helpers\Help;

class Controller_Azs extends Controller
{   
    function __construct()
    {

    }
    
    function action_index()
    {
        $azss = Model_Azs::findAll();

        $this->response['data'] = $azss;
        $this->response['status'] = 200;

        return $this->response ;      
    }


    function action_new($params)
    {
        $azs = new Model_Azs();

        if ($azs->newAzs($params)){

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
        $azs = new Model_Azs();

        $id = $params['id'];
        
        $azs = Model_Azs::findOne($id);
        $azs->edit($params);
    }


    function action_delete($parameters)
    {
        $this->response['data'] = '';
        $this->response['status'] = 204;

        $azs = Model_Azs::findOne($parameters['id']);

        if (is_object($azs))
        {
            $azs->delete();

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
        $azs = Model_Azs::findOne($parameters['id']);

        $this->response['data'] = $azs;
        $this->response['status'] = 200;

        return $this->response ;
    }

}