<?php
session_start();
require_once "pdo.php";

$salt = 'XyZzy12*_';

if (isset($_POST['cancel'])) {
    header("Location: index.php");
    return;
}

if (isset($_POST['email']) && isset($_POST['pass'])) {
    $email = trim($_POST['email']);
    $pass = trim($_POST['pass']);

    if (strlen($email) < 1 || strlen($pass) < 1) {
        $_SESSION['error'] = "Email and password are required";
        header("Location: login.php");
        return;
    }

    $check = hash('md5', $salt . $pass);
    $stmt = $pdo->prepare("SELECT user_id, name FROM users WHERE email = :em AND password = :pw");
    $stmt->execute([':em' => $email, ':pw' => $check]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row !== false) {
        $_SESSION['name'] = $row['name'];
        $_SESSION['user_id'] = $row['user_id'];
        header("Location: index.php");
        return;
    } else {
        $_SESSION['error'] = "Incorrect password";
        header("Location: login.php");
        return;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Resume Registry - Login</title>
    <script>
        function doValidate() {
            try {
                let em = document.getElementById("email").value;
                let pw = document.getElementById("id_1723").value;
                if (em === "" || pw === "") {
                    alert("Both fields must be filled out");
                    return false;
                }
                if (em.indexOf('@') === -1) {
                    alert("Invalid email address");
                    return false;
                }
                return true;
            } catch (e) {
                return false;
            }
        }
    </script>
</head>

<body>
    <h1>Please Log In</h1>

    <?php
    if (isset($_SESSION['error'])) {
        echo ('<p style="color:red">' . htmlentities($_SESSION['error']) . "</p>\n");
        unset($_SESSION['error']);
    }
    ?>

    <form method="POST" action="login.php">
        Email <input type="text" name="email" id="email"><br />
        Password <input type="password" name="pass" id="id_1723"><br />
        <input type="submit" onclick="return doValidate();" value="Log In">
        <input type="submit" name="cancel" value="Cancel">
    </form>

</body>

</html>