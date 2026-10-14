# KKW Student Result Portal (PHP + MySQL + Bootstrap 5)

## New names used (no clash with any old project)
| Item | Name |
|---|---|
| Project folder | `kkw_result_portal` |
| Database | `kkw_srms_db2026` (created automatically) |
| Tables | `kkw_login_users`, `kkw_student_master`, `kkw_result_store` |
| Default login | username `admin`, password `admin123` |

## Files
```
kkw_result_portal/
├── index.php               opens the login page
├── kkw_db_config.php       MySQL settings + AUTO creates database & tables
├── kkw_check.php           system check page (open if anything fails)
├── kkw_login.php           PAGE 1  Login
├── kkw_signup.php          Create account
├── kkw_student_form.php    PAGE 2  Student Information (add / edit)
├── kkw_student_list.php    PAGE 3  Student Details (search, edit, delete)
├── kkw_result_view.php     PAGE 4  Result: display, Save, Download PDF, Print
├── kkw_logout.php
├── kkw_setup.sql           optional (not needed, auto setup is done)
├── kkw_parts/kkw_top.php   banner + menu (every page)
├── kkw_parts/kkw_bottom.php
├── kkw_images/kkw_banner.png
└── kkw_assets/             kkw_style.css (animations), Bootstrap, Icons, html2pdf, kkw_anim.js
```

## Step-by-step: how to run (XAMPP on Windows)
1. Open **XAMPP Control Panel**. Click **Start** for **Apache** and **MySQL**. Both must turn **green**.
2. Right-click the ZIP → **Extract All**. You get the folder `kkw_result_portal`.
3. Copy that whole folder into `C:\xampp\htdocs\`
   Correct path: `C:\xampp\htdocs\kkw_result_portal\kkw_login.php`
   (Wrong: `C:\xampp\htdocs\kkw_result_portal\kkw_result_portal\...`)
4. Open the browser and type: `http://localhost/kkw_result_portal/kkw_check.php`
   All lines should show a green tick and "MySQL connected".
5. Click **Go to Login page** (or open `http://localhost/kkw_result_portal/`).
6. Log in with **admin / admin123** (or create your own account).
7. Page 2: fill the student form → **Register student**.
8. Page 3: see all students → view result / edit / delete.
9. Page 4: select student → **Save result**, **Download PDF**, **Print**.

Do NOT double-click the .php files and do NOT open them as `file:///C:/...`.
PHP runs only through `http://localhost/...`.

## If it still does not run
| Problem | Fix |
|---|---|
| Page shows PHP code as text / downloads file | You opened it as a file. Use `http://localhost/kkw_result_portal/` |
| "Not Found" | Folder is not directly inside `htdocs`, or name is different |
| MySQL connection problem | Start MySQL in XAMPP. If MySQL uses port 3307, set `$KKW_PORT = 3307;` in `kkw_db_config.php` |
| "Access denied for user root" | Put your MySQL password in `$KKW_PASS` in `kkw_db_config.php` |
| Apache does not start (port 80 busy, Skype/IIS) | XAMPP → Apache Config → httpd.conf → change `Listen 80` to `Listen 8080`, then use `http://localhost:8080/kkw_result_portal/` |
| MySQL does not start | Another MySQL service is running. Stop "MySQL80" in Windows Services, or change the port |

## Animations added
Page loader spinner, banner slide-down with gold shine, menu underline slide, cards fade up,
table rows appear one by one, buttons lift on hover, login icon float, form shakes on error,
alerts slide in and success messages auto close, marksheet total and percentage count up,
grade and PASS/FAIL pop in, "Preparing PDF..." spinner on download.
Animations switch off automatically if the computer has "reduce motion" turned on.

## Viva points
Sessions (own session name), password_hash / password_verify, prepared statements (no SQL injection),
htmlspecialchars (no XSS), server-side validation, unique roll number, foreign key with ON DELETE CASCADE,
Bootstrap 5 grid/cards/tables, CSS keyframe animations, html2pdf.js PDF, @media print.
