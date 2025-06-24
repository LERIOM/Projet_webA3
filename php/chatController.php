<?php
// Contrôleur pour gérer les requêtes du chatbot OpenAI
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
    // Initialisation du client OpenAI
    //$openai = OpenAI::client('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');

    // Définition de la fonction pour le function calling
    $functions = [
        [
            'name' => 'query_database',
            'description' => 'Exécute une requête SELECT sur la base et renvoie les résultats JSON.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'sql' => [
                        'type' => 'string',
                        'description' => 'Une requête SQL SELECT valide, sans opérations DML/DDL.'
                    ]
                ],
                'required' => ['sql']
            ]
        ]
    ];

    // Appel à l'API Chat Completions avec function_call
    $response = $openai->chat()->create([
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => 'Tu es un assistant SQL pour la base de données AIS.'],
            ['role' => 'user',   'content' => $prompt]
        ],
        'functions'     => $functions,
        'function_call' => ['name' => 'query_database']
    ]);

    $choice = $response['choices'][0] ?? null;

    // Vérification du function_call du modèle
    if ($choice && isset($choice['message']['function_call'])) {
        $args = json_decode($choice['message']['function_call']['arguments'], true);
        $sql  = $args['sql'] ?? '';

        // Sécurité : n'accepter que les SELECT
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
        $answer = $choice['message']['content'] ?? 'Désolé, je n\'ai pas compris.';
    }

    // Envoi de la réponse JSON au client
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['answer' => $answer]);
}