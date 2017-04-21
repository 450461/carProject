<?php
use core\Controller;
use core\View;
use helpers\App;

class Controller_Refills extends Controller
{   
    function __construct()
    {
        
        $this->model = new Model_Refills();
    }
    
    //добавить новую заправку
    function action_new($data)
    {
        $this->response = $this->model->newRefill($data);
        return $this->response ;
    }
  

    //получить данные по всем заправкам  
    function action_index($data='')
    {

        if(isset($data['params'])){
            $parameters = json_decode( $data['params'], true );
            $this->response = $this->model->getRecords($parameters);
            
        }else{
            $this->response = $this->model->getRecords();
        }

        return $this->response ;
        // $this->view->generate('refill_view.php', 'template_view.php', $data);
    }


    //удалить заправку
    function action_delete($params)
    {
        $this->response = $this->model->deleteRefill($params);

        return $this->response ;
    }


    //получить "последнюю" цену бензина 
    function action_lastRefill()
    { 
        $this->response['data'] = $this->model->lastRefill();
        $this->response['status'] = 200;
        return $this->response ;
    }


    //получить сумму последней заправки
    function action_summ($parameters)
    {
        $this->response['data'] = $this->model->summ($parameters);
        $this->response['status'] = 200;
        
        return $this->response ;
    }


    //редактировать заправку
    function action_edit($parameters)
    {
        $this->response['data'] = $this->model->editRefill($parameters);
        $this->response['status'] = 200;
        
        return $this->response ;       
    }
}