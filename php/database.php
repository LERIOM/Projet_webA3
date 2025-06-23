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

  return $result;
}


?>