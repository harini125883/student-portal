<?php
/* index.php - READ: list all students with optional search */
require 'config.php';
$title = 'Students';
$q = trim($_GET['q'] ?? '');

// Prepared statement: the search text is bound as a parameter, never concatenated
$st = $pdo->prepare('SELECT s.*, (SELECT COUNT(*) FROM student_files f WHERE f.student_id = s.id) AS file_count
                     FROM students s
                     WHERE s.name LIKE :q OR s.roll_no LIKE :q OR s.course LIKE :q
                     ORDER BY s.id DESC');
$st->execute([':q' => "%$q%"]);
$students = $st->fetchAll();

require 'header.php';
?>
<h1>Student records</h1>
<form class="toolbar" method="get">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search by name, roll number or course" aria-label="Search">
  <button class="btn" type="submit">Search</button>
  <a class="btn accent" href="add.php">Add student</a>
</form>

<div class="card table-scroll">
<?php if (!$students): ?>
  <p>No students found. <a href="add.php">Add the first student.</a></p>
<?php else: ?>
  <table>
    <thead><tr><th>Roll no</th><th>Name</th><th>Course</th><th>Files</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($students as $s): ?>
      <tr>
        <td><?= e($s['roll_no']) ?></td>
        <td><?= e($s['name']) ?></td>
        <td><?= e($s['course']) ?></td>
        <td><?= (int)$s['file_count'] ?></td>
        <td>
          <a class="btn small ghost" href="view.php?id=<?= (int)$s['id'] ?>">View</a>
          <a class="btn small" href="edit.php?id=<?= (int)$s['id'] ?>">Edit</a>
          <form class="inline" method="post" action="delete.php"
                onsubmit="return confirm('Delete this student and all their files?');">
            <input type="hidden" name="student_id" value="<?= (int)$s['id'] ?>">
            <button class="btn small danger" type="submit">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
</div>
<?php require 'footer.php'; ?>
