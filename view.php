<?php
session_start();
require_once "pdo.php";
require_once "util.php";

if (!isset($_GET['profile_id'])) {
    $_SESSION['error'] = "Missing profile_id";
    header("Location: index.php");
    return;
}

$profile = loadProfileWithDetails($pdo, $_GET['profile_id']);
if ($profile === false) {
    $_SESSION['error'] = "Could not load profile";
    header("Location: index.php");
    return;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Haassani Lamya - View Profile</title>
    <?php require_once "head.php"; ?>
</head>

<body>
    <div class="container">
        <h1>Profile Information</h1>
        <p>First Name: <?= htmlentities($profile['first_name']) ?></p>
        <p>Last Name: <?= htmlentities($profile['last_name']) ?></p>
        <p>Email: <?= htmlentities($profile['email']) ?></p>
        <p>Headline:<br><?= htmlentities($profile['headline']) ?></p>
        <p>Summary:<br><?= htmlentities($profile['summary']) ?></p>

        <?php if (count($profile['positions']) > 0): ?>
            <h2>Positions</h2>
            <ul>
                <?php foreach ($profile['positions'] as $pos): ?>
                    <li><?= htmlentities($pos['year']) ?>: <?= htmlentities($pos['description']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (count($profile['educations']) > 0): ?>
            <h2>Education</h2>
            <ul>
                <?php foreach ($profile['educations'] as $edu): ?>
                    <li><?= htmlentities($edu['year']) ?>: <?= htmlentities($edu['name']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <p><a href="index.php">Done</a></p>
    </div>
</body>

</html>