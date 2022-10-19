<?php
// хост БД
define('db_host','localhost');

// Имя БД
define('db_name','test_task');

// Пользователь БД
define('db_user','root');

// Пароль БД
define('db_pass','');

// Подключение файла соединения с БД
include_once 'classes/db.class.php';

// Обявление класса для подключения к бд
$db = new DB_class(db_host,db_name,db_user,db_pass);
include "functions.php";
?>