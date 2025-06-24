
<?php
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
    // Initialisation du client OpenAI (remplacez sk-… par votre clé)

    
    

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
        'messages'      => [
            ['role' => 'system', 'content' => 'Tu es un assistant SQL pour la base de données AIS.'],
            ['role' => 'system', 'content' => $schemaDesc],
            ['role' => 'user',   'content' => $prompt]
        ],
        'functions'     => $functions,
        'function_call' => ['name' => 'query_database']
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

    // Envoi de la réponse JSON au client
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['answer' => $answer]);
}