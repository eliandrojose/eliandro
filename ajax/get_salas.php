<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

header('Content-Type: application/json');

$escola_id = intval($_GET['escola_id'] ?? 0);

if ($escola_id <= 0) {
    echo json_encode([]);
    exit;
}

$conn = getConnection();
$stmt = $conn->prepare("SELECT id, nome FROM salas WHERE escola_id = ? AND ativa = 1 ORDER BY nome");
$stmt->execute([$escola_id]);
echo json_encode($stmt->fetchAll());
