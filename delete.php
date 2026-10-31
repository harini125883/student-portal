<?php
/* delete.php - DELETE a student (and all files) or a single file. POST only. */
require 'config.php';
require 'upload_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Use the Delete button.'); }

$studentId = (int)($_POST['student_id'] ?? 0);
$fileId    = (int)($_POST['file_id'] ?? 0);

try {
    if ($fileId > 0) {                                   // remove only one file
        remove_one_file($pdo, $fileId);
        flash('ok', 'File removed.');
        header('Location: edit.php?id=' . $studentId); exit;
    }
    if ($studentId > 0) {                                // remove student: first the files on disk,
        remove_files($pdo, $studentId);                  // then the row (FK cascade also clears metadata)
        $pdo->prepare('DELETE FROM students WHERE id = ?')->execute([$studentId]);
        flash('ok', 'Student deleted.');
    }
} catch (PDOException $ex) {
    flash('err', 'Could not delete. Please try again.');
}
header('Location: index.php');
