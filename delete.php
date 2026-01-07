<?php
session_start();
require_once "pdo.php";
require_once "util.php";
checkLogin(); // ACCESS DENIED if not logged in

if (isset($_POST['cancel'])) {
    header("Location: index.php");
    return;
}

if (!isset($_GET['profile_id']) && !isset($_POST['profile_id'])) {
    $_SESSION['error'] = "Missing profile_id";
    header("Location: index.php");
    return;
}

$profile_id = isset($_POST['profile_id']) ? $_POST['profile_id'] : $_GET['profile_id'];

if (isset($_POST['delete'])) {
    // Delete all positions first
    $stmt = $pdo->prepare("DELETE FROM Position WHERE profile_id = :pid");
    $stmt->execute([ ':pid' => $profile_id ]);

    // Now delete the profile itself
    $stmt = $pdo->prepare("DELETE FROM Profile WHERE profile_id = :pid AND user_id = :uid");
    $stmt->execute([
        ':pid' => $profile_id,
        ':uid' => $_SESSION['user_id']
    ]);

    $_SESSION['success'] = "Record deleted";
    header("Location: index.php");
    return;
}

// Load profile to show
$stmt = $pdo->prepare("SELECT * FROM Profile WHERE profile_id = :pid AND user_id = :uid");
$stmt->execute([
    ':pid' => $profile_id,
    ':uid' => $_SESSION['user_id']
]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row === false) {
    $_SESSION['error'] = "Access denied";
    header("Location: index.php");
    return;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Haassani Lamya</title>
</head>
<body>
<div class="container">
    <h1>Deleting Profile</h1>
    <p>First Name: <?= htmlentities($row['first_name']) ?></p>
    <p>Last Name: <?= htmlentities($row['last_name']) ?></p>

    <form method="post">
        <input type="hidden" name="profile_id" value="<?= htmlentities($row['profile_id']) ?>">
        <input type="submit" name="delete" value="Delete">
        <input type="submit" name="cancel" value="Cancel">
    </form>
</div>
</body>
</html>
