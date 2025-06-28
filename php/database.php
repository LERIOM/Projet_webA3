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
/*
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

*/


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


function getPredictTrajectory(
    PDO $pdo,
    $id_position
) {
    // 1) Récupérer les données de la position
    $query = $pdo->prepare(
        'SELECT p.lat, p.lon, p.sog, p.cog, p.heading, 
                b.length, b.draft
         FROM position AS p
         JOIN boat AS b ON p.mmsi = b.mmsi
         WHERE p.id_position = :id_position'
    );
    $query->bindParam(':id_position', $id_position);
    $query->execute();
    $position = $query->fetch(PDO::FETCH_ASSOC);
    if (!$position) {
        return Response::HTTP404(['message' => 'Position not found']);
    }
    $lat = $position['lat'];
    $lon = $position['lon'];
    $sog = $position['sog'];
    $cog = $position['cog'];
    $heading = $position['heading'];
    $length = $position['length'];
    $draft = $position['draft'];        
    $delta = 600; 
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


function getPredictType(PDO $pdo, $mmsi) {
    // 1) Récupérer les dernières données de la position pour ce MMSI
    $query = $pdo->prepare(
        'SELECT p.sog, p.cog, p.heading, b.length, b.width, b.draft
         FROM position AS p
         JOIN boat AS b ON p.mmsi = b.mmsi
         WHERE p.mmsi = :mmsi
         ORDER BY p.id_position DESC
         LIMIT 1'
    );
    $query->bindParam(':mmsi', $mmsi);
    $query->execute();
    $position = $query->fetch(PDO::FETCH_ASSOC);

    if (!$position) {
        return Response::HTTP404(['message' => 'Position not found']);
    }

    // 2) Exécuter le script de prédiction de type
    $cmd = sprintf(
        'python3 /var/www/html/Projet_webA3/python/predict_vessel.py ' .
        '--sog %s --cog %s --heading %s --length %s --width %s --draft %s 2>&1',
        escapeshellarg($position['sog']),
        escapeshellarg($position['cog']),
        escapeshellarg($position['heading']),
        escapeshellarg($position['length']),
        escapeshellarg($position['width']),
        escapeshellarg($position['draft'])
    );
    $output = shell_exec($cmd);

     // 3) Décoder le JSON renvoyé par le script
    $decoded = json_decode($output, true);
    if (isset($decoded['type'])) {
        return Response::HTTP200(['predicted_type' => $decoded['type']]);
    } else {
        return Response::HTTP404(['message' => 'prediction error']);
    }
}


function getPredictCluster(PDO $pdo, $mmsi, $lat, $lon, $sog, $cog, $heading) {
    // Vérification si le cluster_kmeans existe déjà pour ce bateau
    $check = $pdo->prepare('SELECT cluster_kmeans FROM boat WHERE mmsi = :mmsi');
    $check->bindParam(':mmsi', $mmsi);
    $check->execute();
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    if ($existing && $existing['cluster_kmeans'] !== null) {
        return [['cluster' => $existing['cluster_kmeans']]];
    }
 
    // 2) Construire et exécuter la commande Python
    $cmd = sprintf(
        'python3 /var/www/html/Projet_webA3/python/cluster.py ' .
        '--LAT %s --LON %s --SOG %s --COG %s --Heading %s 2>&1',
        escapeshellarg($lat),
        escapeshellarg($lon),
        escapeshellarg($sog),
        escapeshellarg($cog),
        escapeshellarg($heading),
    );
    $output = shell_exec($cmd);

    // 3) Décoder le JSON renvoyé par le script
    $result = json_decode($output, true);
    if ($result !== null) {
        return $result;
    }

    // En cas d'erreur ou JSON invalide
    return Response::HTTP500([
        'message' => 'Erreur lors de l\'exécution du script cluster.py ou JSON malformé',
        'output'  => $output
    ]);
}



 function GetTabVesselsName($pdo){
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

function getPositionTab($pdo, $name) {
    $query = $pdo->prepare(
        'SELECT 
            p.id_position,
            p.base_date_time,
            p.lat,
            p.lon,
            p.sog,
            p.cog,
            p.heading,
            b.vessel_name,
            ns.description AS status_description
         FROM position AS p
         JOIN boat AS b 
           ON p.mmsi = b.mmsi
         JOIN navigation_status AS ns 
           ON p.id_status = ns.id_status
         WHERE b.vessel_name = :name
         ORDER BY p.base_date_time DESC'
    );
    $query->bindParam(':name', $name);
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($result)) {
        return Response::HTTP200($result);
    } else {
        return Response::HTTP404(['message' => 'No position data found']);
    }
}


function postBoat($pdo,$mmsi, $timestamp, $lat, $lon, $sog, $cog, $heading, $name, $status, $length, $width, $draft) {
    try {
        $pdo->beginTransaction();

        // 1) Upsert dans boat (vérifie si le bateau existe déjà) et calcul du cluster_kmeans
        $sqlBoat = <<<SQL
        INSERT INTO boat (mmsi, vessel_name, length, width, draft)
        VALUES (:mmsi, :vessel_name, :length, :width, :draft)
        ON CONFLICT (mmsi) DO UPDATE
        SET vessel_name = EXCLUDED.vessel_name,
            length      = EXCLUDED.length,
            width       = EXCLUDED.width,
            draft       = EXCLUDED.draft
        SQL;
        $stmtBoat = $pdo->prepare($sqlBoat);
        $stmtBoat->execute([
            ':mmsi'        => $mmsi,
            ':vessel_name' => $name,
            ':length'      => $length,
            ':width'       => $width,
            ':draft'       => $draft,
        ]);

        // 2) Insère le code de navigation s’il n’existe pas
        $sqlStatus = <<<SQL
        INSERT INTO navigation_status (id_status)
        VALUES (:status)
        ON CONFLICT (id_status) DO NOTHING
        SQL;
        $stmtStatus = $pdo->prepare($sqlStatus);
        $stmtStatus->execute([':status' => $status]);

        // 3) Insert du point dans position
        $sqlPos = <<<SQL
        INSERT INTO position
        (base_date_time, lat, lon, sog, cog, heading, id_status, mmsi)
        VALUES
        (:timestamp, :lat, :lon, :sog, :cog, :heading, :status, :mmsi)
        SQL;
        $stmtPos = $pdo->prepare($sqlPos);
        $stmtPos->execute([
            ':timestamp' => $timestamp,
            ':lat'       => $lat,
            ':lon'       => $lon,
            ':sog'       => $sog,
            ':cog'       => $cog,
            ':heading'   => $heading,
            ':status'    => $status,
            ':mmsi'      => $mmsi,
        ]);

        // 4) Calcul du cluster_kmeans via getPredictCluster
        // Ici on choisit un delta par défaut de 600 secondes (10 minutes)
        $clusterResp = getPredictCluster($pdo,$mmsi, $lat,$lon,$sog,$cog,$heading) ;
        $cluster = null;
        echo "Cluster Response: ";
        print_r($clusterResp);
        if (is_array($clusterResp) && isset($clusterResp[0]['cluster'])) {
            $cluster = $clusterResp[0]['cluster'];
        }

        // 5) Mettre à jour le champ cluster_kmeans dans la table boat
        if ($cluster !== null) {
            $updateCluster = $pdo->prepare(
                'UPDATE boat SET cluster_kmeans = :cluster WHERE mmsi = :mmsi'
            );
            $updateCluster->execute([
                ':cluster' => $cluster,
                ':mmsi'    => $mmsi,
            ]);
        }

        $pdo->commit();
        return Response::HTTP200(['message'=>'Point de donnée ajouté avec succès']);
    } catch (Exception $e) {
        $pdo->rollBack();
        return Response::HTTP404(['message'=>'Erreur : '.$e->getMessage()]);
    }
}

/*
function getVesselNotype(PDO $pdo) {
    $stmt = $pdo->prepare("SELECT *
      FROM boat
      WHERE vessel_type IS NULL
      OR vessel_type = ''");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($result)) {
        return Response::HTTP200($result);
    } else {
        return Response::HTTP404(['message' => 'Aucun bateau sans type trouvé']);
    }
}*/

function getAllVesselsPos(PDO $pdo) {
    $query = $pdo->prepare(
        'SELECT b.vessel_name, p.lat, p.lon, b.cluster_kmeans
         FROM (
           SELECT * FROM boat
           ORDER BY vessel_name ASC
           LIMIT 150
         ) AS b
         JOIN LATERAL (
           SELECT lat, lon
           FROM position AS p2
           WHERE p2.mmsi = b.mmsi
           ORDER BY p2.base_date_time DESC
           LIMIT 500
         ) AS p ON true'
    );
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($result)) {
        return Response::HTTP200($result);
    } else {
        return Response::HTTP404(['message' => 'No vessels found']);
    }
}

function isTypeUndifined($pdo,$mmsi){
    $query = $pdo->prepare(
        'SELECT vessel_type FROM boat WHERE mmsi = :mmsi'
    );
    $query->bindParam(':mmsi', $mmsi);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);

   if (!empty($result)) {
        return Response::HTTP200($result);
    } else {
        return Response::HTTP404(['message' => 'No vessels found']);
    }
}


function addTypeToBoat($pdo, $mmsi, $type){
    $query = $pdo->prepare(
        'SELECT vessel_type FROM boat WHERE mmsi = :mmsi'
    );
    $query->bindParam(':mmsi', $mmsi);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if ($result && !empty($result['vessel_type'])) {
        return Response::HTTP404(['message' => 'Type already defined for this boat']);
    }

    // 2) Mettre à jour le type du bateau
    $updateQuery = $pdo->prepare(
        'UPDATE boat SET vessel_type = :type WHERE mmsi = :mmsi'
    );
    $updateQuery->bindParam(':type', $type);
    $updateQuery->bindParam(':mmsi', $mmsi);
    
    if ($updateQuery->execute()) {
        return Response::HTTP200(['message' => 'Type added successfully']);
    } else {
        return Response::HTTP404(['message' => 'Failed to add type']);
    }
}

function addPosition($pdo, $mmsi, $timestamp, $lat, $lon, $sog, $cog, $heading, $status) {
    try {
        $pdo->beginTransaction();

        // Ensure navigation status exists
        $stmtStatus = $pdo->prepare(
            'INSERT INTO navigation_status (id_status) VALUES (:status)
             ON CONFLICT (id_status) DO NOTHING'
        );
        $stmtStatus->execute([':status' => $status]);

        // Insert new position record
        $stmt = $pdo->prepare(
            'INSERT INTO position (base_date_time, lat, lon, sog, cog, heading, id_status, mmsi)
             VALUES (:timestamp, :lat, :lon, :sog, :cog, :heading, :status, :mmsi)'
        );
        $stmt->execute([
            ':timestamp' => $timestamp,
            ':lat'       => $lat,
            ':lon'       => $lon,
            ':sog'       => $sog,
            ':cog'       => $cog,
            ':heading'   => $heading,
            ':status'    => $status,
            ':mmsi'      => $mmsi,
        ]);

        $pdo->commit();
        return Response::HTTP200(['message' => 'Point de donnée ajouté avec succès']);
    } catch (Exception $e) {
        $pdo->rollBack();
        return Response::HTTP404(['message' => 'Erreur lors de l\'ajout du point : ' . $e->getMessage()]);
    }
}
