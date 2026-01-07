<?php
// util.php

function flashMessages()
{
    if (isset($_SESSION['error'])) {
        echo '<p style="color:red">' . htmlentities($_SESSION['error']) . '</p>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<p style="color:green">' . htmlentities($_SESSION['success']) . '</p>';
        unset($_SESSION['success']);
    }
}

// Validate position entries
function validatePos()
{
    for ($i = 1; $i <= 9; $i++) {
        if (!isset($_POST['year' . $i]))
            continue;
        if (!isset($_POST['desc' . $i]))
            continue;

        $year = $_POST['year' . $i];
        $desc = $_POST['desc' . $i];

        if (strlen($year) == 0 || strlen($desc) == 0) {
            return "All fields are required";
        }
        if (!is_numeric($year)) {
            return "Position year must be numeric";
        }
    }
    return true;
}

// Validate education entries
function validateEdu()
{
    for ($i = 1; $i <= 9; $i++) {
        if (!isset($_POST['edu_year' . $i]))
            continue;
        if (!isset($_POST['edu_school' . $i]))
            continue;

        $year = $_POST['edu_year' . $i];
        $school = $_POST['edu_school' . $i];

        if (strlen($year) == 0 || strlen($school) == 0) {
            return "All fields are required";
        }
        if (!is_numeric($year)) {
            return "Education year must be numeric";
        }
    }
    return true;
}

// Insert positions into database
function insertPositions($pdo, $profile_id)
{
    $rank = 1;
    for ($i = 1; $i <= 9; $i++) {
        if (!isset($_POST['year' . $i]))
            continue;
        if (!isset($_POST['desc' . $i]))
            continue;

        $stmt = $pdo->prepare('INSERT INTO Position (profile_id, rank, year, description)
                               VALUES (:pid, :rank, :year, :desc)');
        $stmt->execute([
            ':pid' => $profile_id,
            ':rank' => $rank,
            ':year' => $_POST['year' . $i],
            ':desc' => $_POST['desc' . $i]
        ]);
        $rank++;
    }
}

// Insert education entries (with institution lookup/insert)
function insertEducations($pdo, $profile_id)
{
    $rank = 1;
    for ($i = 1; $i <= 9; $i++) {
        if (!isset($_POST['edu_year' . $i]))
            continue;
        if (!isset($_POST['edu_school' . $i]))
            continue;

        $year = $_POST['edu_year' . $i];
        $school = $_POST['edu_school' . $i];

        // Find or create institution
        $stmt = $pdo->prepare('SELECT institution_id FROM Institution WHERE name = :name');
        $stmt->execute([':name' => $school]);
        $row = $stmt->fetch();

        if ($row !== false) {
            $institution_id = $row['institution_id'];
        } else {
            $stmt = $pdo->prepare('INSERT INTO Institution (name) VALUES (:name)');
            $stmt->execute([':name' => $school]);
            $institution_id = $pdo->lastInsertId();
        }

        // Insert education
        $stmt = $pdo->prepare('INSERT INTO Education (profile_id, institution_id, rank, year)
                               VALUES (:pid, :iid, :rank, :year)');
        $stmt->execute([
            ':pid' => $profile_id,
            ':iid' => $institution_id,
            ':rank' => $rank,
            ':year' => $year
        ]);
        $rank++;
    }
}

// Load full profile with positions and educations
function loadProfileWithDetails($pdo, $profile_id, $user_id = null)
{
    $where = $user_id ? "AND user_id = :uid" : "";
    $params = $user_id ? [':pid' => $profile_id, ':uid' => $user_id] : [':pid' => $profile_id];

    $stmt = $pdo->prepare("SELECT * FROM Profile WHERE profile_id = :pid $where");
    $stmt->execute($params);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($profile === false)
        return false;

    // Load positions
    $stmt = $pdo->prepare("SELECT year, description FROM Position WHERE profile_id = :pid ORDER BY rank");
    $stmt->execute([':pid' => $profile_id]);
    $profile['positions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Load educations
    $stmt = $pdo->prepare("SELECT year, name FROM Education
                           JOIN Institution ON Education.institution_id = Institution.institution_id
                           WHERE profile_id = :pid ORDER BY rank");
    $stmt->execute([':pid' => $profile_id]);
    $profile['educations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $profile;
}

// Display positions in edit form
function displayPositions($positions)
{
    $count = 0;
    foreach ($positions as $pos) {
        $count++;
        echo '<div id="position' . $count . '">';
        echo '<p>Year: <input type="text" name="year' . $count . '" value="' . htmlentities($pos['year']) . '"> ';
        echo '<input type="button" value="-" onclick="$(\'#position' . $count . '\').remove();"></p>';
        echo '<textarea name="desc' . $count . '" rows="8" cols="80">' . htmlentities($pos['description']) . '</textarea>';
        echo '</div>';
    }
    echo '<script>var pos_count = ' . $count . ';</script>';
}

function displayEducations($educations)
{
    $count = 0;
    foreach ($educations as $edu) {
        $count++;
        echo '<div id="edu' . $count . '">';
        echo '<p>Year: <input type="text" name="edu_year' . $count . '" value="' . htmlentities($edu['year']) . '"> ';
        echo '<input type="button" value="-" onclick="$(\'#edu' . $count . '\').remove();"></p>';
        echo '<p>School: <input type="text" size="80" name="edu_school' . $count . '" class="school" value="' . htmlentities($edu['name']) . '"></p>';
        echo '</div>';
    }
    echo '<script>var edu_count = ' . $count . ';</script>';
}

function checkLogin()
{
    if (!isset($_SESSION['user_id'])) {
        die("Not logged in");
    }
}
?>