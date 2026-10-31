<?php
/* add.php - CREATE: add a student plus optional uploads.
   Also the target of ../frontend/register.html. */
require 'config.php';
require 'upload_helper.php';
$title = 'Add student';
$errors = [];
$s = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validate_student($_POST, $COURSES);

    // Password rule (same as client side)
    $pw = $_POST['password'] ?? '';
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $pw)) $errors[] = 'Password is too weak.';
    if ($pw !== ($_POST['confirm'] ?? ''))                                             $errors[] = 'Passwords do not match.';

    // Validate files BEFORE touching the database
    $files = [];
    foreach (['profile_image' => 'profile', 'id_proof' => 'id_proof', 'certificate' => 'certificate'] as $field => $kind) {
        $f = $_FILES[$field] ?? ['error' => UPLOAD_ERR_NO_FILE];
        $r = validate_upload($f, $kind);
        if ($r === null) { if ($kind === 'profile') $errors[] = 'Profile photo is required.'; continue; }
        if ($r !== '') $errors[] = $r; else $files[$kind] = $f;
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare('INSERT INTO students (name, roll_no, email, course, password_hash) VALUES (?,?,?,?,?)')
                ->execute([trim($_POST['name']), strtoupper(trim($_POST['roll_no'])), trim($_POST['email']),
                           $_POST['course'], password_hash($pw, PASSWORD_DEFAULT)]);
            $id = (int)$pdo->lastInsertId();
            foreach ($files as $kind => $f) store_upload($pdo, $id, $f, $kind);
            $pdo->commit();
            flash('ok', 'Student added successfully.');
            header('Location: view.php?id=' . $id); exit;
        } catch (PDOException $ex) {
            $pdo->rollBack();
            $errors[] = $ex->getCode() === '23000' ? 'That roll number is already registered.' : 'Database error. Please try again.';
        } catch (RuntimeException $ex) {
            $pdo->rollBack();
            $errors[] = $ex->getMessage();
        }
    }
}

$mode = 'add';
require 'header.php';
?>
<section class="card">
  <h1>Add student</h1>
  <?php foreach ($errors as $er): ?><div class="alert err" role="alert"><?= e($er) ?></div><?php endforeach; ?>
  <?php require '_form.php'; ?>
</section>
<?php require 'footer.php'; ?>
