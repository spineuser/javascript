<?php
session_start();
require_once "pdo.php";
require_once "util.php";

if (!isset($_SESSION['user_id'])) {
    die("ACCESS DENIED");
}

if (isset($_POST['cancel'])) {
    header("Location: index.php");
    return;
}

// Handle form submission
if (isset($_POST['first_name'])) {
    // Validate core fields
    if (
        strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 ||
        strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1 ||
        strlen($_POST['summary']) < 1
    ) {
        $_SESSION['error'] = "All fields are required";
        header("Location: add.php");
        return;
    }

    if (strpos($_POST['email'], '@') === false) {
        $_SESSION['error'] = "Email address must contain @";
        header("Location: add.php");
        return;
    }

    // Validate positions
    $pos_error = validatePos();
    if ($pos_error !== true) {
        $_SESSION['error'] = $pos_error;
        header("Location: add.php");
        return;
    }

    // Validate education
    $edu_error = validateEdu();
    if ($edu_error !== true) {
        $_SESSION['error'] = $edu_error;
        header("Location: add.php");
        return;
    }

    // Insert Profile
    $stmt = $pdo->prepare('INSERT INTO Profile (user_id, first_name, last_name, email, headline, summary)
                            VALUES (:uid, :fn, :ln, :em, :he, :su)');
    $stmt->execute([
        ':uid' => $_SESSION['user_id'],
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
        ':su' => $_POST['summary']
    ]);
    $profile_id = $pdo->lastInsertId();

    // Insert Positions
    insertPositions($pdo, $profile_id);

    // Insert Education
    insertEducations($pdo, $profile_id);

    $_SESSION['success'] = "Profile added";
    header("Location: index.php");
    return;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Haassani Lamya - Adding Profile</title>
    <?php require_once "head.php"; ?>
</head>

<body>
    <div class="container">
        <h1>Adding Profile</h1>
        <?php flashMessages(); ?>

        <form method="post">
            <p>First Name: <input type="text" name="first_name" size="60"></p>
            <p>Last Name: <input type="text" name="last_name" size="60"></p>
            <p>Email: <input type="text" name="email" size="30"></p>
            <p>Headline:<br><input type="text" name="headline" size="80"></p>
            <p>Summary:<br><textarea name="summary" rows="8" cols="80"></textarea></p>

            <p>Position: <input type="button" id="addPos" value="+"></p>
            <div id="position_fields"></div>

            <p>Education: <input type="button" id="addEdu" value="+"></p>
            <div id="education_fields"></div>

            <p>
                <input type="submit" value="Add">
                <input type="submit" name="cancel" value="Cancel">
            </p>
        </form>
    </div>

    <script>
        let pos_count = 0;
        let edu_count = 0;

        $("#addPos").click(function (e) {
            e.preventDefault();
            if (pos_count >= 9) {
                alert("Maximum of nine position entries exceeded");
                return;
            }
            pos_count++;
            $("#position_fields").append(
                '<div id="position' + pos_count + '"> \
            <p>Year: <input type="text" name="year' + pos_count + '"> \
            <input type="button" value="-" onclick="$(\'#position' + pos_count + '\').remove();"></p> \
            <textarea name="desc' + pos_count + '" rows="8" cols="80"></textarea> \
        </div>'
            );
        });

        $("#addEdu").click(function (e) {
            e.preventDefault();
            if (edu_count >= 9) {
                alert("Maximum of nine education entries exceeded");
                return;
            }
            edu_count++;
            $("#education_fields").append(
                '<div id="edu' + edu_count + '"> \
            <p>Year: <input type="text" name="edu_year' + edu_count + '"> \
            <input type="button" value="-" onclick="$(\'#edu' + edu_count + '\').remove();"></p> \
            <p>School: <input type="text" size="80" name="edu_school' + edu_count + '" class="school"></p> \
        </div>'
            );
            $('.school').autocomplete({ source: "school.php" });
        });

        // Initialize autocomplete on page load
        $(document).ready(function () {
            $('.school').autocomplete({ source: "school.php" });
        });
    </script>
</body>

</html>