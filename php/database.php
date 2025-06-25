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
  $query = $db->prepare(
      'SELECT b.*, 
              p.base_date_time, p.lat, p.lon, p.sog, p.cog, p.heading, 
              ns.description AS status_description
       FROM boat AS b
       LEFT JOIN LATERAL (
           SELECT * FROM position AS p2
           WHERE p2.mmsi = b.mmsi
           ORDER BY p2.base_date_time DESC
           LIMIT 1
       ) AS p ON true
       LEFT JOIN navigation_status AS ns ON ns.id_status = p.id_status
       WHERE b.mmsi = 366872110;'
  );
  $query->execute();
  $result = $query->fetchAll(PDO::FETCH_ASSOC);
  if (!empty($result)) {
      return Response::HTTP200($result);
    } else {
      return Response::HTTP200(['resp_capacity' => 20]);
  }
}

function postBoat($pdo,$id, $mmsi, $base_date_time, $lat, $lon, $sog, $cog, $heading, $vessel_name, $imo, $call_sign, $vessel_type, $status, $length, $width, $draft, $cargo, $transceiver_class){
  // 1) Insertion ou mise à jour du bateau
  $boatStmt = $pdo->prepare(
      'INSERT INTO boat (mmsi, vessel_name, length, width, draft, vessel_type,
                         imo, call_sign, cargo, transceiver_class)
       VALUES (:mmsi, :vessel_name, :length, :width, :draft, :vessel_type,
               :imo, :call_sign, :cargo, :transceiver_class)
       ON CONFLICT (mmsi) DO UPDATE
         SET vessel_name       = EXCLUDED.vessel_name,
             length            = EXCLUDED.length,
             width             = EXCLUDED.width,
             draft             = EXCLUDED.draft,
             vessel_type       = EXCLUDED.vessel_type,
             imo               = EXCLUDED.imo,
             call_sign         = EXCLUDED.call_sign,
             cargo             = EXCLUDED.cargo,
             transceiver_class = EXCLUDED.transceiver_class'
  );
  $boatStmt->execute([
      ':mmsi'             => $mmsi,
      ':vessel_name'      => $vessel_name,
      ':length'           => $length,
      ':width'            => $width,
      ':draft'            => $draft,
      ':vessel_type'      => $vessel_type,
      ':imo'              => $imo,
      ':call_sign'        => $call_sign,
      ':cargo'            => $cargo ?: null,   // NULL si vide
      ':transceiver_class'=> $transceiver_class
  ]);

  // 2) S’assurer que le statut existe
  $statusStmt = $pdo->prepare(
      'INSERT INTO navigation_status (id_status) VALUES (:status)
       ON CONFLICT DO NOTHING'
  );
  $statusStmt->execute([':status' => $status]);

  // 3) Insertion de la position
  $posStmt = $pdo->prepare(
      'INSERT INTO position (base_date_time, lat, lon, sog, cog, heading,
                             id_status, mmsi)
       VALUES (:base_date_time, :lat, :lon, :sog, :cog, :heading, :status, :mmsi)'
  );
  $posStmt->execute([
      ':base_date_time' => $base_date_time,
      ':lat'            => $lat,
      ':lon'            => $lon,
      ':sog'            => $sog,
      ':cog'            => $cog,
      ':heading'        => $heading,
      ':status'         => $status,
      ':mmsi'           => $mmsi
  ]);

  return Response::HTTP201();
}


function getTabMmsi($pdo, $mmsi) {
  $query = $pdo->prepare(
      'SELECT b.*, 
              p.base_date_time, p.lat, p.lon, p.sog, p.cog, p.heading,
              ns.description AS status_description
       FROM boat AS b
       LEFT JOIN LATERAL (
           SELECT * FROM position AS p2
           WHERE p2.mmsi = b.mmsi
           ORDER BY p2.base_date_time DESC
           LIMIT 1
       ) AS p ON true
       LEFT JOIN navigation_status AS ns ON ns.id_status = p.id_status
       WHERE b.mmsi = :mmsi'
  );
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
  $query = $pdo->prepare(
      'SELECT b.mmsi,
              b.length, b.width, b.draft,
              p.cog, p.sog, p.heading,
              p.lat, p.lon
       FROM boat AS b
       LEFT JOIN LATERAL (
           SELECT * FROM position AS p2
           WHERE p2.mmsi = b.mmsi
           ORDER BY p2.base_date_time DESC
           LIMIT 1
       ) AS p ON true'
  );
  $query->execute();
  $result = $query->fetchAll(PDO::FETCH_ASSOC);
  if (!empty($result)) {
    return Response::HTTP200($result);
  } else {
    return Response::HTTP404(['message' => 'No boats found']);
  }
}

function getredictCluster($pdo, $cog, $sog, $lat, $lon) {

    $args = [
        escapeshellarg($cog),
        escapeshellarg($sog),
        escapeshellarg($lat),
        escapeshellarg($lon)
    ];

    $cmd = 'python3 /var/www/html/Projet_webA3/python/maintraj.py ' .
           implode(' ', $args) . ' 2>&1';

    $output = shell_exec($cmd);

    // On suppose que le script renvoie du JSON
    $data = json_decode($output, true);

    if ($data !== null) {
        // Succès : on renvoie les données produites par Python
        return Response::HTTP200($data);
    }

    // Échec : JSON invalide ou autre erreur
    return Response::HTTP500([
        'message' => 'Erreur lors de l\'exécution du script Python ou JSON malformé',
        'output'  => $output          // utile pour le débogage
    ]);
}

function getPredictTrajectory(
    PDO $pdo,
    $cog,
    $sog,
    $lat,
    $lon,
    $delta,
    $heading,
    $length,
    $draft
) {
    // 1) Construire la ligne de commande avec options nommées
    $cmd = sprintf(
        'python3 /var/www/html/Projet_webA3/python/maintraj.py ' .
        '--lat %s --lon %s --sog %s --cog %s --heading %s --length %s --draft %s --delta_seconds %s 2>&1',
        escapeshellarg($lat),
        escapeshellarg($lon),
        escapeshellarg($sog),
        escapeshellarg($cog),
        escapeshellarg($heading),
        escapeshellarg($length),
        escapeshellarg($draft),
        escapeshellarg($delta)
    );

    // 2) Exécuter
    $output = shell_exec($cmd);

    // 3) Décoder le JSON
    $result = json_decode($output, true);

    if (!empty($result)) {
      return Response::HTTP200($result);
    } else {
      return Response::HTTP404(['message' => 'error']);
  }
}

 function GetTabVessselsName($pdo){
    $query = $pdo->prepare(
        'SELECT DISTINCT vessel_name FROM boat ORDER BY vessel_name'
    );
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($result)) {
        return Response::HTTP200($result);
    } else {
        return Response::HTTP404(['message' => 'No vessel names found']);
    }
 }

 function getInfoByName($pdo, $name) {
    $query = $pdo->prepare(
        'SELECT b.*, 
                p.base_date_time, p.lat, p.lon, p.sog, p.cog, p.heading,
                ns.description AS status_description
         FROM boat AS b
         LEFT JOIN LATERAL (
             SELECT * FROM position AS p2
             WHERE p2.mmsi = b.mmsi
             ORDER BY p2.base_date_time DESC
             LIMIT 1
         ) AS p ON true
         LEFT JOIN navigation_status AS ns ON ns.id_status = p.id_status
         WHERE b.vessel_name = :name'
    );
    $query->bindParam(':name', $name);
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($result)) {
        return Response::HTTP200($result);
    } else {
        return Response::HTTP404(['message' => 'No vessel found with the given name']);
    }
 }
?> 