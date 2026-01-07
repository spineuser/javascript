<?php
session_start();
require_once "pdo.php";

// Only allow logged-in users
if (!isset($_SESSION['user_id'])) {
    die(json_encode([]));
}

if (!isset($_REQUEST['term'])) {
    die(json_encode([]));
}

$stmt = $pdo->prepare('SELECT name FROM Institution WHERE name LIKE :prefix');
$stmt->execute(array(':prefix' => $_REQUEST['term'] . "%"));

$retval = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $retval[] = $row['name'];
}

header("Content-Type: application/json");
echo json_encode($retval, JSON_PRETTY_PRINT);