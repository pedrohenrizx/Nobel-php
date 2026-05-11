<?php
$dbFile = 'database.sqlite';

if (file_exists($dbFile)) {
    unlink($dbFile); // Remove existing database to start fresh
}

$db = new SQLite3($dbFile);

// Create candidates table
$query = "CREATE TABLE IF NOT EXISTS candidates (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT NOT NULL,
    votes INTEGER DEFAULT 0
)";
$db->exec($query);

// Seed data
$candidates = [
    [
        'name' => 'Albert Einstein',
        'description' => 'For his services to Theoretical Physics, and especially for his discovery of the law of the photoelectric effect.',
    ],
    [
        'name' => 'Marie Curie',
        'description' => 'In recognition of her services to the advancement of chemistry by the discovery of the elements radium and polonium.',
    ],
    [
        'name' => 'Niels Bohr',
        'description' => 'For his services in the investigation of the structure of atoms and of the radiation emanating from them.',
    ],
    [
        'name' => 'Max Planck',
        'description' => 'In recognition of the services he rendered to the advancement of Physics by his discovery of energy quanta.',
    ],
    [
        'name' => 'Richard Feynman',
        'description' => 'For their fundamental work in quantum electrodynamics, with deep-ploughing consequences for the physics of elementary particles.',
    ]
];

$stmt = $db->prepare("INSERT INTO candidates (name, description) VALUES (:name, :description)");

foreach ($candidates as $candidate) {
    $stmt->bindValue(':name', $candidate['name'], SQLITE3_TEXT);
    $stmt->bindValue(':description', $candidate['description'], SQLITE3_TEXT);
    $stmt->execute();
}

echo "Database initialized and seeded successfully.\n";
$db->close();
?>
