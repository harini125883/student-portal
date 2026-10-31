<?php
/* view.php - READ one student and display the uploaded files */
require 'config.php';
require 'upload_helper.php';  // for KIND_LABEL
$title = 'Student details';
$id = (int)($_GET['id'] ?? 0);

$st = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$st->execute([$id]);
$s = $st->fetch();
if (!$s) { http_response_code(404); flash('err', 'Student not found.'); header('Location: index.php'); exit; }

$fs = $pdo->prepare('SELECT * FROM student_files WHERE student_id = ? ORDER BY uploaded_at DESC');
$fs->execute([$id]);
$files = $fs->fetchAll();
$profile = null;
foreach ($files as $f) if ($f['file_kind'] === 'profile') { $profile = $f; break; }

require 'header.php';
?>
<section class="card">
  <div class="profile">
    <?php if ($profile): ?>
      <img class="avatar" src="<?= e($profile['file_path']) ?>" alt="Photo of <?= e($s['name']) ?>">
    <?php else: ?>
      <div class="avatar" aria-label="No photo"></div>
    <?php endif; ?>
    <div>
      <h1><?= e($s['name']) ?></h1>
      <p><strong>Roll no:</strong> <?= e($s['roll_no']) ?><br>
         <strong>Email:</strong> <?= e($s['email']) ?><br>
         <strong>Course:</strong> <?= e($s['course']) ?><br>
         <strong>Registered:</strong> <?= e($s['created_at']) ?></p>
      <a class="btn" href="edit.php?id=<?= $id ?>">Edit</a>
      <a class="btn ghost" href="index.php">Back to list</a>
    </div>
  </div>

  <h2>Uploaded files</h2>
  <?php if (!$files): ?>
    <p>No files uploaded yet.</p>
  <?php else: ?>
  <div class="files">
    <?php foreach ($files as $f): ?>
      <div class="file-item">
        <?php if (str_starts_with($f['file_type'], 'image/')): ?>
          <a href="<?= e($f['file_path']) ?>" target="_blank" rel="noopener">
            <img src="<?= e($f['file_path']) ?>" alt="<?= e($f['original_name']) ?>"></a>
        <?php else: ?>
          <a class="btn small ghost" href="<?= e($f['file_path']) ?>" target="_blank" rel="noopener">Open PDF</a>
        <?php endif; ?>
        <div><strong><?= e(KIND_LABEL[$f['file_kind']] ?? $f['file_kind']) ?></strong></div>
        <div><?= e($f['original_name']) ?></div>
        <div class="hint"><?= e($f['file_type']) ?> &middot; <?= round($f['file_size'] / 1024) ?> KB<br>
          <?= e($f['uploaded_at']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
<?php require 'footer.php'; ?>
