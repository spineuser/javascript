<?php
session_start();
require_once "pdo.php";
require_once "util.php";
?>

<!DOCTYPE html>
<html>

<head>
    <title>Haassani Lamya's Resume Registry</title>
</head>

<body>
    <div class="container">
        <h1>Haassani Lamya's Resume Registry</h1>

        <?php flashMessages(); ?>

        <?php if (!isset($_SESSION['name'])): ?>
            <p><a href="login.php">Please log in</a></p>
        <?php else: ?>
            <p><a href="logout.php">Logout</a></p>
            <p><a href="add.php">Add New Entry</a></p>
        <?php endif; ?>

        <?php
        // Only load profiles if logged in
        $rows = array();
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("SELECT profile_id, first_name, last_name, headline 
                           FROM Profile 
                           WHERE user_id = :user_id 
                           ORDER BY first_name");
            $stmt->execute(array(':user_id' => $_SESSION['user_id']));
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        if (count($rows) > 0): ?>
            <table border="1">
                <tr>
                    <th>Name</th>
                    <th>Headline</th>
                    <?php if (isset($_SESSION['name'])): ?>
                        <th>Action</th>
                    <?php endif; ?>
                </tr>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td>
                            <a href="view.php?profile_id=<?= $row['profile_id'] ?>">
                                <?= htmlentities($row['first_name'] . ' ' . $row['last_name']) ?>
                            </a>
                        </td>
                        <td><?= htmlentities($row['headline']) ?></td>
                        <?php if (isset($_SESSION['name'])): ?>
                            <td>
                                <a href="edit.php?profile_id=<?= $row['profile_id'] ?>">Edit</a>
                                <a href="delete.php?profile_id=<?= $row['profile_id'] ?>">Delete</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php elseif (isset($_SESSION['name'])): ?>
            <p>No profiles found</p>
        <?php endif; ?>
    </div>
</body>

</html>