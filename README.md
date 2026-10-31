# Student Admission Portal - U21CS501 Web Technology, Assignment II

Name: ____________  Roll No: ____________  Section: V / A  
GitHub: https://github.com/<your-username>/student-portal  *(add your link here)*

## 1. Folder structure
```
StudentPortal/
├── database/schema.sql              DB schema + sample data
├── frontend/                        Q1: register.html, css/style.css, js/validate.js
├── php-admin/                       Q2: PHP + MySQL CRUD and file upload
│   ├── config.php  upload_helper.php  header.php  footer.php  _form.php
│   ├── index.php (View list)  add.php (Add)  view.php (View one)
│   ├── edit.php (Edit)        delete.php (Delete)  uploads/ (stored files)
└── servlet-portal/                  Q3: Servlet + JDBC + JSP (Maven WAR)
    ├── pom.xml
    └── src/main/
        ├── java/com/kpr/portal/  DBUtil, Student, AuthFilter, LoginServlet,
        │                         LogoutServlet, StudentsServlet, FileServlet
        └── webapp/  login.jsp, css/style.css,
                     WEB-INF/web.xml, WEB-INF/views/students.jsp
```

## 2. How to run
**Database:** `mysql -u root -p < database/schema.sql`

**Q1 + Q2 (XAMPP/WAMP, PHP 8+):**
1. Copy the whole `StudentPortal` folder to `htdocs`. Start Apache + MySQL.
2. Set DB password in `php-admin/config.php` if needed.
3. Open `http://localhost/StudentPortal/frontend/register.html` (form) and
   `http://localhost/StudentPortal/php-admin/index.php` (admin panel).
4. Enable `extension=fileinfo` in php.ini (usually on by default).

**Q3 (Tomcat 9, JDK 11+, Maven):**
1. In `servlet-portal/src/main/webapp/WEB-INF/web.xml` set `uploadRoot` to the absolute path of `php-admin`
   (the folder that contains `uploads/`).
2. Set the MySQL password in `DBUtil.java`.
3. `cd servlet-portal && mvn clean package`, copy `target/student-portal-1.0.war` to Tomcat `webapps/`.
4. Open `http://localhost:8080/student-portal-1.0/` and log in with **admin / Admin@123**.
5. Register students with photos first (Q1/Q2) so the portal has files to show.

## 3. Code explanation
### Q1 - Registration form (HTML/CSS/JS)
* `register.html` uses semantic HTML5: labelled inputs, `select` for course, three file inputs, `novalidate`
  so our JavaScript shows friendly messages beside each field.
* `style.css` uses CSS variables, a 2-column grid that collapses to 1 column under 640 px (responsive),
  styled inputs/buttons, visible focus rings, red `.invalid` fields and `.error` messages.
* `validate.js` checks on blur and on submit: required fields, name/roll-number patterns, e-mail regex,
  **password strength** (8+ chars, upper + lower, digit, symbol) with a live strength meter, password match,
  and file type + size. Submission is blocked until every field is valid.

### Q2 - PHP CRUD + file upload
* `config.php`: PDO connection (exceptions, real prepared statements), `e()` helper for XSS-safe output, flash messages.
* **Create** `add.php`, **Read** `index.php`/`view.php`, **Update** `edit.php`, **Delete** `delete.php` (POST only).
  Every query uses prepared statements (no SQL injection).
* `upload_helper.php`:
  * **Size check** per kind (photo 2 MB, documents 5 MB).
  * **Type check** using `finfo` on the real file contents (not the browser-supplied type), allow-list JPG/PNG/PDF.
  * **No overwriting**: files saved as random unique names (`bin2hex(random_bytes())`) and checked with `file_exists`.
  * **Metadata** (path, MIME type, size, upload date, student id) saved in `student_files`.
  * `uploads/.htaccess` stops script execution in the upload folder.
* Files are validated *before* the DB insert and the insert runs in a **transaction**, so a bad file never leaves a half-saved student.
* `view.php` shows the photo and every document (images as thumbnails, PDFs as links) with metadata.
* Duplicate roll numbers are caught (MySQL error 23000) and shown as a friendly message.

### Q3 - Servlet + JDBC + JSP
* `DBUtil` opens JDBC connections. `StudentsServlet` loads students and each student's file list with
  `PreparedStatement`s, puts them in the request and forwards to `students.jsp` (kept in `WEB-INF` so it can't be opened directly).
* `FileServlet` (`/file?id=N`) looks up the path in MySQL, resolves it inside `uploadRoot`, blocks `../` traversal,
  sets Content-Type/Length and **streams** the bytes. JSP uses `<img src="file?id=N">`.
* `students.jsp` uses JSTL: `c:forEach` to loop over students and files, `c:choose/c:when/c:if` for conditions
  (empty list, photo vs placeholder, image vs PDF). `c:out` escapes all data.
* **Session handling**: `LoginServlet` verifies the user (SHA-256), invalidates any old session and creates a new one
  (prevents session fixation), 15-minute timeout. `AuthFilter` guards `/students` and `/file`, redirecting
  anonymous users to the login page. `LogoutServlet` invalidates the session.

## 4. Screenshots to capture (paste into your report)
1. `register.html` empty, then with validation errors, then password strength meter, then mobile width (F12 device mode)
2. PHP `index.php` list, `add.php` success, `edit.php`, `view.php` showing files, delete confirmation
3. Upload error (file too large / .exe renamed to .jpg)
4. phpMyAdmin: `students` and `student_files` tables
5. Servlet `login.jsp`, wrong-password error, `/students` page with photos, logout, then opening `/students` directly after logout (redirects to login)

## 5. Conclusion
This project builds a complete admission workflow: a responsive, validated registration form, a PHP/MySQL admin panel with secure
file uploads, and a Servlet/JSP portal that streams stored files to logged-in users. Prepared statements, output escaping,
content-based file checks and session filters keep the application safe. It showed me how the same MySQL data can be shared
between PHP and Java web layers.
