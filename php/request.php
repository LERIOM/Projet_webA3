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

$router->POST('/boat', ["id ","mmsi ","base_date_time ","lat ","lon ","sog ","cog ","heading ","vessel_name ","imo ","call_sign ","vessel_type ","status ","length ","width ","draft ","cargo ","transceiver_class"], function($mmsi, $password){
  global $pdo;
  postBoat($pdo,$id, $mmsi, $base_date_time, $lat, $lon, $sog, $cog, $heading, $vessel_name, $imo, $call_sign, $vessel_type, $status, $length, $width, $draft, $cargo, $transceiver_class);
});

$router->GET('/boatMmsi',["mmsi"], function($mmsi){
  global $pdo;
  getTabMmsi($pdo, $mmsi);
});

$router->GET('/boatAll', [], function(){
  global $pdo;
  getAllBoats($pdo);
});

$router->GET('/predictCluster', ["cog", "sog", "lat", "lon"], function($cog, $sog, $lat, $lon){
  global $pdo;
  getredictCluster($pdo, $cog, $sog, $lat, $lon);
});

$router->run();

?>