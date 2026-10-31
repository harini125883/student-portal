<?php
/* edit.php - UPDATE student details, add/replace files, remove single files */
require 'config.php';
require 'upload_helper.php';
$title = 'Edit student';
$id = (int)($_GET['id'] ?? 0);

$st = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$st->execute([$id]);
$s = $st->fetch();
if (!$s) { flash('err', 'Student not found.'); header('Location: index.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s = array_merge($s, $_POST);
    $errors = validate_student($_POST, $COURSES);

    $files = [];
    foreach (['profile_image' => 'profile', 'id_proof' => 'id_proof', 'certificate' => 'certificate'] as $field => $kind) {
        $r = validate_upload($_FILES[$field] ?? ['error' => UPLOAD_ERR_NO_FILE], $kind);
        if ($r === null) continue;
        if ($r !== '') $errors[] = $r; else $files[$kind] = $_FILES[$field];
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare('UPDATE students SET name=?, roll_no=?, email=?, course=? WHERE id=?')
                ->execute([trim($_POST['name']), strtoupper(trim($_POST['roll_no'])), trim($_POST['email']), $_POST['course'], $id]);
            foreach ($files as $kind => $f) store_upload($pdo, $id, $f, $kind);
            $pdo->commit();
            flash('ok', 'Student updated.');
            header('Location: view.php?id=' . $id); exit;
        } catch (PDOException $ex) {
            $pdo->rollBack();
            $errors[] = $ex->getCode() === '23000' ? 'That roll number belongs to another student.' : 'Database error.';
        } catch (RuntimeException $ex) {
            $pdo->rollBack();
            $errors[] = $ex->getMessage();
        }
    }
}

$fs = $pdo->prepare('SELECT * FROM student_files WHERE student_id = ?');
$fs->execute([$id]);
$existing = $fs->fetchAll();

$mode = 'edit';
require 'header.php';
?>
<section class="card">
  <h1>Edit <?= e($s['name']) ?></h1>
  <?php foreach ($errors as $er): ?><div class="alert err" role="alert"><?= e($er) ?></div><?php endforeach; ?>
  <?php require '_form.php'; ?>

  <h2>Current files</h2>
  <?php if (!$existing): ?><p>No files yet.</p><?php endif; ?>
  <?php foreach ($existing as $f): ?>
    <form class="inline" method="post" action="delete.php">
      <input type="hidden" name="file_id" value="<?= (int)$f['id'] ?>">
      <input type="hidden" name="student_id" value="<?= $id ?>">
      <?= e(KIND_LABEL[$f['file_kind']]) ?>: <?= e($f['original_name']) ?>
      <button class="btn small danger" type="submit">Remove</button>
    </form><br>
  <?php endforeach; ?>
</section>
<?php require 'footer.php'; ?>
