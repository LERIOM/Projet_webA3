<?php
// Démarrage de la session pour l'historique de chat
session_start();
// Contrôleur pour gérer les requêtes du chatbot OpenAIke fkre fkf ekf ek erfke e
require __DIR__ . '/../vendor/autoload.php';
use OpenAI;


/**
 * Gère la requête de chat, appelle l'API OpenAI pour générer du SQL,
 * exécute la requête sur la base de données et renvoie la réponse JSON.
 *
 * @param PDO    $pdo    Instance PDO de la base de données.
 * @param string $prompt Texte saisi par l'utilisateur.
 */
function handleChat(PDO $pdo, string $prompt) {

    // Définition de la clé API OpenAI (à sécuriser dans une variable d'environnement en production)
   
    $openai = OpenAI::client('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');
    

    // Génération de la description du schéma de la base
    $tables = $pdo
        ->query("SELECT table_name FROM information_schema.tables WHERE table_schema='public'")
        ->fetchAll(PDO::FETCH_COLUMN);
    $schemaDesc = "Schéma de la base de données :";
    foreach ($tables as $tableName) {
        $columns = $pdo
            ->query("
                SELECT column_name, data_type
                FROM information_schema.columns
                WHERE table_schema = 'public'
                  AND table_name   = '{$tableName}'
            ")
            ->fetchAll(PDO::FETCH_ASSOC);
        $colList = array_map(
            fn($col) => "{$col['column_name']}:{$col['data_type']}",
            $columns
        );
        $schemaDesc .= "\n- {$tableName} : " . implode(', ', $colList) . ";";
    }

    // Initialisation de l'historique de chat
    if (!isset($_SESSION['chat_history'])) {
        $_SESSION['chat_history'] = [
            ['role'=>'system','content'=>'Tu es un assistant SQL pour la base de données AIS.'],
            ['role'=>'system','content'=>$schemaDesc]
        ];
    }
    // Ajout du prompt utilisateur
    $_SESSION['chat_history'][] = ['role'=>'user','content'=>$prompt];

    // Définition de la fonction pour le function calling
    $functions = [
        [
            'name'        => 'query_database',
            'description' => 'Exécute une requête SELECT sur la base et renvoie les résultats JSON.',
            'parameters'  => [
                'type'       => 'object',
                'properties' => [
                    'sql' => [
                        'type'        => 'string',
                        'description' => 'Une requête SQL SELECT valide, sans opérations DML/DDL.'
                    ]
                ],
                'required'   => ['sql']
            ]
        ]
    ];

    // Appel à l'API Chat Completions avec function_call
    $response = $openai->chat()->create([
        'model'         => 'gpt-4o-mini',
        'messages'      => $_SESSION['chat_history'],
        'functions'     => $functions,
        'function_call' => 'auto',
    ]);

    $choice = $response['choices'][0] ?? null;
    $answer = '';

    // Traitement du function_call
    if ($choice && isset($choice['message']['function_call'])) {
        $args = json_decode($choice['message']['function_call']['arguments'], true);
        $sql  = $args['sql'] ?? '';

        // Sécurité : n’accepter que les SELECT
        if (stripos(trim($sql), 'select') !== 0) {
            $answer = "Je ne peux exécuter que des requêtes SELECT.";
        } else {
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $answer = json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            } catch (Exception $e) {
                $answer = 'Erreur SQL : ' . $e->getMessage();
            }
        }
    } else {
        // Fallback si pas de function_call
        $answer = $choice['message']['content'] ?? 'Désolé, je n’ai pas compris.';
    }

    // Ajout de la réponse du bot à l'historique
    $_SESSION['chat_history'][] = ['role'=>'assistant','content'=>$answer];

    // Envoi de la réponse JSON au client
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['answer' => $answer]);
}