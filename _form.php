<?php
/* _form.php - the student form used by add.php and edit.php.
   Expects: $s (current values), $mode ('add' or 'edit'), $COURSES */
$req = $mode === 'add' ? 'required' : '';
?>
<form id="regForm" method="post" enctype="multipart/form-data" novalidate>
  <div class="form-grid">
    <div class="field">
      <label for="name">Full name *</label>
      <input id="name" name="name" value="<?= e($s['name'] ?? '') ?>" required>
      <span class="error" data-for="name"></span>
    </div>
    <div class="field">
      <label for="roll_no">Roll number *</label>
      <input id="roll_no" name="roll_no" value="<?= e($s['roll_no'] ?? '') ?>" required>
      <span class="error" data-for="roll_no"></span>
    </div>
    <div class="field">
      <label for="email">Email *</label>
      <input id="email" name="email" type="email" value="<?= e($s['email'] ?? '') ?>" required>
      <span class="error" data-for="email"></span>
    </div>
    <div class="field">
      <label for="course">Course *</label>
      <select id="course" name="course" required>
        <option value="">Select a course</option>
        <?php foreach ($COURSES as $c): ?>
          <option <?= ($s['course'] ?? '') === $c ? 'selected' : '' ?>><?= e($c) ?></option>
        <?php endforeach; ?>
      </select>
      <span class="error" data-for="course"></span>
    </div>

    <?php if ($mode === 'add'): ?>
    <div class="field">
      <label for="password">Password *</label>
      <input id="password" name="password" type="password" required>
      <div class="meter" aria-hidden="true"><span id="meterBar"></span></div>
      <span class="hint" id="meterText">Use 8+ characters with upper, lower, number and symbol.</span>
      <span class="error" data-for="password"></span>
    </div>
    <div class="field">
      <label for="confirm">Confirm password *</label>
      <input id="confirm" name="confirm" type="password" required>
      <span class="error" data-for="confirm"></span>
    </div>
    <?php endif; ?>

    <div class="field">
      <label for="profile_image">Profile photo <?= $mode === 'add' ? '*' : '(replaces current)' ?></label>
      <input id="profile_image" name="profile_image" type="file" accept=".jpg,.jpeg,.png"
             data-types="image/jpeg,image/png" data-max-mb="2" <?= $req ?>>
      <span class="error" data-for="profile_image"></span>
    </div>
    <div class="field">
      <label for="id_proof">ID proof (PDF/JPG/PNG, max 5 MB)</label>
      <input id="id_proof" name="id_proof" type="file" accept=".pdf,.jpg,.jpeg,.png"
             data-types="application/pdf,image/jpeg,image/png" data-max-mb="5">
      <span class="error" data-for="id_proof"></span>
    </div>
    <div class="field full">
      <label for="certificate">Certificate (PDF/JPG/PNG, max 5 MB)</label>
      <input id="certificate" name="certificate" type="file" accept=".pdf,.jpg,.jpeg,.png"
             data-types="application/pdf,image/jpeg,image/png" data-max-mb="5">
      <span class="error" data-for="certificate"></span>
    </div>
  </div>
  <p><button class="btn accent" type="submit"><?= $mode === 'add' ? 'Add student' : 'Save changes' ?></button>
     <a class="btn ghost" href="index.php">Cancel</a></p>
</form>
<script src="../frontend/js/validate.js"></script>
