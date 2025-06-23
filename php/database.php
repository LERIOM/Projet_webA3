<?php

  require_once('constants.php');
  require_once('Routeur/response.php');
  #require_once("model/global.php");

/**
 * Create the connection to the database.
 * @return PDO|false
 */
  function dbConnect()
  {
    try
    {
      $db = new PDO('pgsql:host='.DB_SERVER.';port='.DB_PORT.';dbname='.DB_NAME, DB_USER, DB_PASSWORD);
      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch (PDOException $exception)
    {
      error_log('Connection error: '.$exception->getMessage());
      return false;
    }
    return $db;
  }
 
function test($db){
  $query = $db->prepare('SELECT * FROM vessel_total_clean_final WHERE mmsi=366872110;');
  $query->execute();
  $result = $query->fetchAll(PDO::FETCH_ASSOC);
  if (!empty($result)) {
      return Response::HTTP200($result);
    } else {
      return Response::HTTP200(['resp_capacity' => 20]);
  }
}

function postBoat($pdo,$id, $mmsi, $base_date_time, $lat, $lon, $sog, $cog, $heading, $vessel_name, $imo, $call_sign, $vessel_type, $status, $length, $width, $draft, $cargo, $transceiver_class){
  $query = $pdo->prepare('INSERT INTO vessel_total_clean_final (id, mmsi, base_date_time, lat, lon, sog, cog, heading, vessel_name, imo, call_sign, vessel_type, status, length, width, draft, cargo, transceiver_class) VALUES (:id, :mmsi, :base_date_time, :lat, :lon, :sog, :cog, :heading, :vessel_name, :imo, :call_sign, :vessel_type, :status, :length, :width, :draft, :cargo, :transceiver_class)');
  $query->bindParam(':id', $id);
  $query->bindParam(':mmsi', $mmsi);
  $query->bindParam(':base_date_time', $base_date_time);
  $query->bindParam(':lat', $lat);
  $query->bindParam(':lon', $lon);
  $query->bindParam(':sog', $sog);
  $query->bindParam(':cog', $cog);
  $query->bindParam(':heading', $heading);
  $query->bindParam(':vessel_name', $vessel_name);
  $query->bindParam(':imo', $imo);
  $query->bindParam(':call_sign', $call_sign);
  $query->bindParam(':vessel_type', $vessel_type);
  $query->bindParam(':status', $status);
  $query->bindParam(':length', $length);
  $query->bindParam(':width', $width);
  $query->bindParam(':draft', $draft);

  $cargo = $cargo ?: null; // Handle null value
  $query->bindParam(':cargo', $cargo);

  $query->bindParam(':transceiver_class', $transceiver_class);
  $query->execute();
  return Response::HTTP201();
}


function getTabMmsi($pdo, $mmsi) {
  $query = $pdo->prepare('SELECT * FROM vessel_total_clean_final WHERE mmsi = :mmsi LIMIT 1');
  $query->bindParam(':mmsi', $mmsi);
  $query->execute();
  $result = $query->fetchAll(PDO::FETCH_ASSOC);
  
  if (!empty($result)) {
    return Response::HTTP200($result);
  } else {
    return Response::HTTP404(['message' => 'No boat found with the given MMSI']);
  }
}

function getAllBoats($pdo) {
  $query = $pdo->prepare('SELECT length , width, draft, cog, sog, heading , lat, long FROM vessel_total_clean_final');
  $query->execute();
  $result = $query->fetchAll(PDO::FETCH_ASSOC);
  if (!empty($result)) {
    return Response::HTTP200($result);
  } else {
    return Response::HTTP404(['message' => 'No boats found']);
  }
}
?> 