<?php
header('Content-Type: application/json');

$dbFile = 'database.sqlite';

if (!file_exists($dbFile)) {
    http_response_code(500);
    echo json_encode(['error' => 'Database not found.']);
    exit;
}

$db = new SQLite3($dbFile);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Return all candidates
    $results = $db->query("SELECT * FROM candidates ORDER BY votes DESC, name ASC");
    $candidates = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $candidates[] = $row;
    }
    echo json_encode($candidates);
} elseif ($method === 'POST') {
    // Process a vote
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
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
}

$db->close();
?>
