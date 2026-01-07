<?php
session_start();
require_once "pdo.php";
require_once "util.php";

if (!isset($_SESSION['user_id'])) {
    die("ACCESS DENIED");
}

if (!isset($_GET['profile_id'])) {
    $_SESSION['error'] = "Missing profile_id";
    header("Location: index.php");
    return;
}

$profile = loadProfileWithDetails($pdo, $_GET['profile_id'], $_SESSION['user_id']);
if ($profile === false) {
    $_SESSION['error'] = "Could not load profile";
    header("Location: index.php");
    return;
}

if (isset($_POST['cancel'])) {
    header("Location: index.php");
    return;
}

if (isset($_POST['first_name'])) {
    // Core field validation
    if (
        strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 ||
        strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1 ||
        strlen($_POST['summary']) < 1
    ) {
        $_SESSION['error'] = "All fields are required";
        header("Location: edit.php?profile_id=" . $_POST['profile_id']);
        return;
    }

    if (strpos($_POST['email'], '@') === false) {
        $_SESSION['error'] = "Email address must contain @";
        header("Location: edit.php?profile_id=" . $_POST['profile_id']);
        return;
    }

    $pos_error = validatePos();
    if ($pos_error !== true) {
        $_SESSION['error'] = $pos_error;
        header("Location: edit.php?profile_id=" . $_POST['profile_id']);
        return;
    }

    $edu_error = validateEdu();
    if ($edu_error !== true) {
        $_SESSION['error'] = $edu_error;
        header("Location: edit.php?profile_id=" . $_POST['profile_id']);
        return;
    }

    // Update main profile
    $stmt = $pdo->prepare('UPDATE Profile SET first_name=:fn, last_name=:ln, email=:em,
                            headline=:he, summary=:su WHERE profile_id=:pid AND user_id=:uid');
    $stmt->execute([
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
        ':su' => $_POST['summary'],
        ':pid' => $_POST['profile_id'],
        ':uid' => $_SESSION['user_id']
    ]);

    // Replace positions and education
    $pdo->prepare('DELETE FROM Position WHERE profile_id = :pid')->execute([':pid' => $_POST['profile_id']]);
    $pdo->prepare('DELETE FROM Education WHERE profile_id = :pid')->execute([':pid' => $_POST['profile_id']]);

    insertPositions($pdo, $_POST['profile_id']);
    insertEducations($pdo, $_POST['profile_id']);

    $_SESSION['success'] = "Profile updated";
    header("Location: index.php");
    return;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Haassani Lamya - Editing Profile</title>
    <?php require_once "head.php"; ?>
</head>

<body>
    <div class="container">
        <h1>Editing Profile</h1>
        <?php flashMessages(); ?>

        <form method="post">
            <input type="hidden" name="profile_id" value="<?= htmlentities($profile['profile_id']) ?>">

            <p>First Name: <input type="text" name="first_name" size="60"
                    value="<?= htmlentities($profile['first_name']) ?>"></p>
            <p>Last Name: <input type="text" name="last_name" size="60"
                    value="<?= htmlentities($profile['last_name']) ?>"></p>
            <p>Email: <input type="text" name="email" size="30" value="<?= htmlentities($profile['email']) ?>"></p>
            <p>Headline:<br><input type="text" name="headline" size="80"
                    value="<?= htmlentities($profile['headline']) ?>"></p>
            <p>Summary:<br><textarea name="summary" rows="8"
                    cols="80"><?= htmlentities($profile['summary']) ?></textarea></p>

            <p>Position: <input type="button" id="addPos" value="+"></p>
            <div id="position_fields">
                <?php displayPositions($profile['positions']); ?>
            </div>

            <p>Education: <input type="button" id="addEdu" value="+"></p>
            <div id="education_fields">
                <?php displayEducations($profile['educations']); ?>
            </div>

            <p>
                <input type="submit" value="Save">
                <input type="submit" name="cancel" value="Cancel">
            </p>
        </form>
    </div>

    <script>
        $(document).ready(function () {
            // pos_count and edu_count are set by displayPositions() and displayEducations() in util.php using 'var'
            // So we do NOT redeclare them here → avoids "already declared" error

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
                // Re-apply autocomplete to new fields
                $('.school').autocomplete({ source: "school.php" });
            });

            // Apply autocomplete to all existing school fields on load
            $('.school').autocomplete({ source: "school.php" });
        });
    </script>
</body>

</html>