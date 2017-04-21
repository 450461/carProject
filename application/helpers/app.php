<?php
namespace helpers;

class App{
    public static $router;

    public static $db1;
	  public static $appConfig;
	
    public static function run()
	{
      App::$appConfig = Config::get();  //получить настройки по умолчанию
      App::$db1 = new Db();             //объект для работы с БД
      App::$router=new Router();        //запуск роутера

	}

}