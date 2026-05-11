<?php

// Check if a static file was requested directly in the built-in server
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($path !== '/' && file_exists(__DIR__ . $path)) {
        return false; // Let the built-in server handle the static file
    }
}

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Helper for database connection
function getDb() {
    $dbFile = __DIR__ . '/database.sqlite';
    if (!file_exists($dbFile)) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Database not found.']);
        exit;
    }
    return new SQLite3($dbFile);
}

// Router
if ($requestUri === '/' || $requestUri === '/index.html') {
    // Serve frontend
    header('Content-Type: text/html');
    require 'index.html';

} elseif ($requestUri === '/api/candidates' && $method === 'GET') {
    // Fetch candidates
    header('Content-Type: application/json');
    $db = getDb();
    $results = $db->query("SELECT * FROM candidates ORDER BY votes DESC, name ASC");
    $candidates = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $candidates[] = $row;
    }
    echo json_encode($candidates);
    $db->close();

} elseif ($requestUri === '/api/vote' && $method === 'POST') {
    // Submit a vote
    header('Content-Type: application/json');
    $db = getDb();
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['id'])) {
        $id = intval($data['id']);

        $stmt = $db->prepare("UPDATE candidates SET votes = votes + 1 WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();

        if ($result && $db->changes() > 0) {
            echo json_encode(['success' => true, 'message' => 'Vote recorded successfully.']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Candidate not found.']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing candidate ID.']);
    }
    $db->close();

} else {
    // 404 Not Found
    http_response_code(404);
    echo "404 Not Found";
}
