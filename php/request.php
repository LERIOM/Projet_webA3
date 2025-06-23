<?php

require_once 'Routeur/routeur.php';
require_once __DIR__ . '/constants.php';
require_once 'database.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
$pdo = dbConnect();

if (!$pdo) {
    echo "Error connecting to the database";
    exit();
}


$router = new Router();


$router->GET('/test', [], function(){
  global $pdo;
  test($pdo);

});


$router->run();

?>