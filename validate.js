/* Client-side validation for the registration form.
   The server (add.php) repeats these checks - never trust the browser alone. */
(function () {
  'use strict';
  var form = document.getElementById('regForm');
  if (!form) return;

  var EMAIL = /^[^\s@]+@[^\s@]+\.[A-Za-z]{2,}$/;   // basic e-mail format
  var ROLL  = /^\d{2}[A-Za-z]{2,4}\d{3}$/;         // e.g. 21CS001
  var NAME  = /^[A-Za-z][A-Za-z .'-]{2,59}$/;      // letters, 3-60 chars

  /* Password strength score 0-4: length, mixed case, digit, symbol */
  function strength(p) {
    var s = 0;
    if (p.length >= 8) s++;
    if (/[a-z]/.test(p) && /[A-Z]/.test(p)) s++;
    if (/\d/.test(p)) s++;
    if (/[^A-Za-z0-9]/.test(p)) s++;
    return s;
  }

  /* Each rule returns '' when valid, otherwise an error message */
  var rules = {
    name:    function (v) { return NAME.test(v)  ? '' : 'Enter your full name (3-60 letters).'; },
    roll_no: function (v) { return ROLL.test(v)  ? '' : 'Use a roll number like 21CS001.'; },
    email:   function (v) { return EMAIL.test(v) ? '' : 'Enter a valid email, e.g. name@example.com.'; },
    course:  function (v) { return v ? '' : 'Select your course.'; },
    password: function (v) {
      return strength(v) === 4 ? '' :
        'Password needs 8+ characters with upper, lower, number and symbol.';
    },
    confirm: function (v) { return form.password && v === form.password.value ? '' : 'Passwords do not match.'; }
  };

  /* File rule: required check, MIME type check, size check */
  function fileRule(input) {
    var f = input.files[0];
    if (!f) return input.required ? 'Choose a file.' : '';
    var types = (input.dataset.types || '').split(',');
    var maxMb = parseFloat(input.dataset.maxMb || '5');
    if (types.indexOf(f.type) === -1) return 'Unsupported file type.';
    if (f.size > maxMb * 1024 * 1024) return 'File is larger than ' + maxMb + ' MB.';
    return '';
  }

  /* Validate one field, update its message and styling */
  function check(el) {
    var value = el.type === 'password' ? el.value : (el.value || '').trim();
    var msg = el.type === 'file' ? fileRule(el) : (rules[el.name] ? rules[el.name](value) : '');
    var out = form.querySelector('[data-for="' + el.name + '"]');
    if (out) out.textContent = msg;
    el.classList.toggle('invalid', !!msg);
    el.classList.toggle('valid', !msg && !!el.value);
    el.setAttribute('aria-invalid', msg ? 'true' : 'false');
    return !msg;
  }

  /* Live password strength meter */
  var bar = document.getElementById('meterBar'), txt = document.getElementById('meterText');
  if (form.password) form.password.addEventListener('input', function () {
    var s = strength(form.password.value);
    var labels = ['Too weak', 'Weak', 'Fair', 'Good', 'Strong'];
    bar.style.width = (s * 25) + '%';
    bar.style.background = s < 3 ? '#b3261e' : (s === 3 ? '#e36414' : '#1e7a46');
    txt.textContent = form.password.value ? labels[s] : 'Use 8+ characters with upper, lower, number and symbol.';
  });

  /* Validate when the user leaves a field, and again on submit */
  Array.prototype.forEach.call(form.elements, function (el) {
    if (!el.name) return;
    el.addEventListener('blur', function () { check(el); });
    el.addEventListener('change', function () { check(el); });
  });

  form.addEventListener('submit', function (e) {
    var ok = true, first = null;
    Array.prototype.forEach.call(form.elements, function (el) {
      if (!el.name || el.type === 'submit' || el.type === 'reset') return;
      if (!check(el)) { ok = false; first = first || el; }
    });
    if (!ok) { e.preventDefault(); first.focus(); }
  });
})();
