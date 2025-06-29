<?php

require_once 'Routeur/routeur.php';
require_once __DIR__ . '/constants.php';
require_once 'database.php';
require_once __DIR__ . '/chatController.php';

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
/*
$router->POST('/boat', ["id ","mmsi ","base_date_time ","lat ","lon ","sog ","cog ","heading ","vessel_name ","imo ","call_sign ","vessel_type ","status ","length ","width ","draft ","cargo ","transceiver_class"], function($mmsi, $password){
  global $pdo;
  postBoat($pdo,$id, $mmsi, $base_date_time, $lat, $lon, $sog, $cog, $heading, $vessel_name, $imo, $call_sign, $vessel_type, $status, $length, $width, $draft, $cargo, $transceiver_class);
});*/



$router->POST('/boat', ["mmsi","timestamp","lat","lon","sog","cog","heading","name","status","length","width","draft"], function($mmsi, $timestamp, $lat, $lon, $sog, $cog, $heading, $name, $status, $length, $width, $draft){
  global $pdo;
  postBoat($pdo,$mmsi, $timestamp, $lat, $lon, $sog, $cog, $heading, $name, $status, $length, $width, $draft);
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

$router->GET( '/predictTrajectory',["id_position"], function ( $id_position) {
  global $pdo;
        getPredictTrajectory( $pdo, $id_position);
    }
);

$router->GET('/predictType', ["mmsi"], function($mmsi) {
    global $pdo;
    getPredictType($pdo, $mmsi);
});



$router->POST('/chat', [], function() {
    global $pdo;
    $input = json_decode(file_get_contents('php://input'), true);
    $prompt = $input['prompt'] ?? '';
    handleChat($pdo, $prompt);
});

$router->GET('/vesselname', [], function() {
  global $pdo;
  GetTabVesselsName($pdo);
});

$router->GET('/vesselInfo', ["name"], function($name) {
    global $pdo;
    getInfoByName($pdo, $name);
});

$router->GET('/positionTab', ["name"], function($name) {
    global $pdo;
    getPositionTab($pdo, $name);
});

$router->GET('/getAllVesselsPos', [], function() {
    global $pdo;
    getAllVesselsPos($pdo);
});

$router->GET('/isTypeUndifined', ["mmsi"], function($mmsi) {
    global $pdo;
    isTypeUndifined($pdo,$mmsi);
});

$router->PUT ('/addTypeToBoat', ["mmsi", "type"], function($mmsi, $type) {
    global $pdo;
    addTypeToBoat($pdo, $mmsi, $type);
});

$router->PUT ('/position', ["mmsi", "timestamp", "lat", "lon", "sog", "cog", "heading","status"], function($mmsi, $timestamp, $lat, $lon, $sog, $cog, $heading,$status) {
    global $pdo;
    addPosition($pdo, $mmsi, $timestamp, $lat, $lon, $sog, $cog, $heading,$status);
});

$router->run();

