<?php
/* =====================================================================
   kkw_db_config.php
   Database connection + AUTO SETUP + common helper functions
   ---------------------------------------------------------------------
   Database and tables are created AUTOMATICALLY the first time you
   open the project. No need to import any SQL file.
   ===================================================================== */

// Show PHP errors on screen (so the page is never just blank)
ini_set('display_errors', '1');
error_reporting(E_ALL);
date_default_timezone_set('Asia/Kolkata');

// ------------- 1. MySQL settings (XAMPP default) --------------------
$KKW_HOST = 'localhost';
$KKW_USER = 'root';
$KKW_PASS = '';          // XAMPP default password is blank
$KKW_PORT = 3306;        // if your XAMPP MySQL runs on 3307, write 3307
$KKW_DB   = 'kkw_srms_db2026';

// ------------- 2. Connect to MySQL server ---------------------------
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $kkw_con = new mysqli($KKW_HOST, $KKW_USER, $KKW_PASS, '', $KKW_PORT);
    $kkw_con->set_charset('utf8mb4');

    // ------------- 3. Auto create database + tables -----------------
    $kkw_con->query("CREATE DATABASE IF NOT EXISTS `$KKW_DB` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $kkw_con->select_db($KKW_DB);

    $kkw_con->query("CREATE TABLE IF NOT EXISTS kkw_login_users (
        uid        INT AUTO_INCREMENT PRIMARY KEY,
        full_name  VARCHAR(100) NOT NULL,
        login_name VARCHAR(50)  NOT NULL UNIQUE,
        email      VARCHAR(150) NOT NULL DEFAULT '',
        pass_hash  VARCHAR(255) NOT NULL,
        made_on    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Add the recovery email when upgrading an existing installation.
    $email_column = $kkw_con->query("SHOW COLUMNS FROM kkw_login_users LIKE 'email'");
    if ($email_column->num_rows === 0) {
        $kkw_con->query("ALTER TABLE kkw_login_users ADD email VARCHAR(150) NOT NULL DEFAULT '' AFTER login_name");
    }
    $kkw_con->query("UPDATE kkw_login_users SET email = 'admin@kkw.local' WHERE login_name = 'admin' AND email = ''");

    $kkw_con->query("CREATE TABLE IF NOT EXISTS kkw_student_master (
        sid          INT AUTO_INCREMENT PRIMARY KEY,
        roll_no      VARCHAR(20)  NOT NULL UNIQUE,
        stu_name     VARCHAR(100) NOT NULL,
        father_name  VARCHAR(100) NOT NULL,
        dob          DATE         NOT NULL,
        gender       VARCHAR(10)  NOT NULL,
        email        VARCHAR(100) NOT NULL,
        mobile       VARCHAR(15)  NOT NULL,
        course       VARCHAR(50)  NOT NULL,
        semester     VARCHAR(10)  NOT NULL,
        address      TEXT         NOT NULL,
        sub1 INT NOT NULL DEFAULT 0,
        sub2 INT NOT NULL DEFAULT 0,
        sub3 INT NOT NULL DEFAULT 0,
        sub4 INT NOT NULL DEFAULT 0,
        sub5 INT NOT NULL DEFAULT 0,
        added_on     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $kkw_con->query("CREATE TABLE IF NOT EXISTS kkw_result_store (
        rid          INT AUTO_INCREMENT PRIMARY KEY,
        sid          INT NOT NULL UNIQUE,
        total_marks  INT NOT NULL,
        percent      DECIMAL(5,2) NOT NULL,
        grade        VARCHAR(5)  NOT NULL,
        final_status VARCHAR(10) NOT NULL,
        saved_by     VARCHAR(50) NOT NULL,
        saved_on     DATETIME    NOT NULL,
        CONSTRAINT fk_kkw_result_student FOREIGN KEY (sid)
            REFERENCES kkw_student_master(sid) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    // Default login account (created only once): admin / admin123
    $cnt = $kkw_con->query("SELECT COUNT(*) AS c FROM kkw_login_users")->fetch_assoc();
    if ((int)$cnt['c'] === 0) {
        $h = password_hash('admin123', PASSWORD_DEFAULT);
        $st = $kkw_con->prepare("INSERT INTO kkw_login_users (full_name, login_name, email, pass_hash) VALUES ('Administrator', 'admin', 'admin@kkw.local', ?)");
        $st->bind_param('s', $h);
        $st->execute();
    }
} catch (mysqli_sql_exception $ex) {
    echo '<div style="font-family:Arial;max-width:700px;margin:40px auto;padding:20px;border:2px solid #b00;border-radius:8px">';
    echo '<h2 style="color:#b00">MySQL connection problem</h2>';
    echo '<p><b>Error:</b> ' . htmlspecialchars($ex->getMessage()) . '</p>';
    echo '<ol><li>Open XAMPP Control Panel and click <b>Start</b> for MySQL (it must turn green).</li>';
    echo '<li>If MySQL uses port 3307, set <code>$KKW_PORT = 3307;</code> in kkw_db_config.php.</li>';
    echo '<li>If your root user has a password, write it in <code>$KKW_PASS</code>.</li></ol></div>';
    exit;
}

// ------------- 4. Session (own name, so other projects don't clash) --
if (session_status() === PHP_SESSION_NONE) {
    session_name('KKW_SRMS_SESSION');
    session_start();
}

// ------------- 5. Subjects / courses (change as you like) -----------
$KKW_SUBJECTS = [
    'sub1' => 'Advanced DBMS',
    'sub2' => 'Web Technologies (PHP)',
    'sub3' => 'Data Structures & Algorithms',
    'sub4' => 'Computer Networks',
    'sub5' => 'Machine Learning',
];
$KKW_MAX  = 100;   // max marks per subject
$KKW_PASS_MARK = 40;    // pass marks per subject
$KKW_COURSES   = ['M.Sc. Computer Science', 'MCA', 'B.Sc. Computer Science', 'BCA'];
$KKW_SEMESTERS = ['I', 'II', 'III', 'IV', 'V', 'VI'];

$KKW_SUBJECT_MAP = [
    'M.Sc. Computer Science' => [
        'I' => ['Advanced DBMS', 'Research Methodology', 'Data Structures & Algorithms', 'Operating Systems', 'Computer Networks'],
        'II' => ['Software Engineering', 'Web Technologies (PHP)', 'Machine Learning', 'Cloud Computing', 'Cyber Security'],
        'III' => ['Big Data Analytics', 'Artificial Intelligence', 'Digital Image Processing', 'Distributed Systems', 'Data Mining'],
        'IV' => ['Project Management', 'Advanced Java', 'Mobile Application Development', 'Blockchain', 'IoT Systems'],
        'V' => ['Compiler Design', 'Database Tuning', 'Optimization Techniques', 'Embedded Systems', 'Deep Learning'],
        'VI' => ['Thesis Seminar', 'Enterprise Architecture', 'Natural Language Processing', 'Quantum Computing', 'Capstone Project'],
    ],
    'MCA' => [
        'I' => ['C Programming', 'Digital Logic', 'Discrete Mathematics', 'Web Fundamentals', 'Operating Systems'],
        'II' => ['DBMS', 'Data Structures', 'Computer Networks', 'OOP in Java', 'Software Engineering'],
        'III' => ['Python Programming', 'Advanced DBMS', 'Data Mining', 'Linux Administration', 'AI Basics'],
        'IV' => ['Mobile App Design', 'Cloud Computing', 'Cyber Security', 'DevOps', 'Project Work'],
        'V' => ['Machine Learning', 'Big Data', 'Internet of Things', 'Data Analytics', 'Seminar'],
        'VI' => ['Industrial Training', 'Advanced Web', 'Enterprise Systems', 'Capstone Project', 'Viva Voce'],
    ],
    'B.Sc. Computer Science' => [
        'I' => ['Programming in C', 'Computer Fundamentals', 'Mathematics', 'Electronics', 'English Communication'],
        'II' => ['C++ Programming', 'Web Design', 'Statistics', 'DBMS', 'Environment Science'],
        'III' => ['Data Structures', 'Java Programming', 'Operating Systems', 'Computer Architecture', 'Numerical Methods'],
        'IV' => ['Python Programming', 'Computer Networks', 'Software Engineering', 'Database Management', 'Theory of Computation'],
        'V' => ['Artificial Intelligence', 'Cyber Security', 'Machine Learning', 'Cloud Computing', 'Project Work'],
        'VI' => ['Internet Technologies', 'Multimedia Systems', 'Data Analytics', 'Major Project', 'Seminar'],
    ],
    'BCA' => [
        'I' => ['Computer Fundamentals', 'C Programming', 'Digital Electronics', 'Office Automation', 'Communication Skills'],
        'II' => ['Data Structures', 'Web Programming', 'Discrete Mathematics', 'DBMS', 'Environmental Studies'],
        'III' => ['OOP in C++', 'Computer Networks', 'Operating Systems', 'Java', 'Statistics'],
        'IV' => ['Python', 'Software Engineering', 'Advanced DBMS', 'Linux', 'E-Commerce'],
        'V' => ['Machine Learning', 'Cloud Computing', 'Cyber Security', 'Project Design', 'Data Analytics'],
        'VI' => ['Mobile App Development', 'Big Data', 'Major Project', 'Seminar', 'Viva Voce'],
    ],
];

function kkw_get_subjects($course, $semester) {
    global $KKW_SUBJECT_MAP, $KKW_SUBJECTS;
    $course = (string)($course ?? '');
    $semester = (string)($semester ?? '');

    if (!empty($KKW_SUBJECT_MAP[$course][$semester])) {
        $names = $KKW_SUBJECT_MAP[$course][$semester];
        $out = [];
        foreach (['sub1', 'sub2', 'sub3', 'sub4', 'sub5'] as $i => $key) {
            $out[$key] = $names[$i] ?? $KKW_SUBJECTS[$key] ?? 'Subject ' . ($i + 1);
        }
        return $out;
    }

    $out = [];
    foreach (['sub1', 'sub2', 'sub3', 'sub4', 'sub5'] as $i => $key) {
        $out[$key] = $KKW_SUBJECTS[$key] ?? 'Subject ' . ($i + 1);
    }
    return $out;
}

// ------------- 6. Helper functions ----------------------------------
function kkw_e($v) {                       // safe output (stops XSS)
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function kkw_need_login() {                // protect pages
    if (empty($_SESSION['kkw_uid'])) {
        header('Location: kkw_login.php');
        exit;
    }
}

function kkw_msg($text, $type = 'success') {   // message for next page
    $_SESSION['kkw_msg'] = ['text' => $text, 'type' => $type];
}

function kkw_show_msg() {
    if (!empty($_SESSION['kkw_msg'])) {
        $m = $_SESSION['kkw_msg'];
        unset($_SESSION['kkw_msg']);
        echo '<div class="alert alert-' . kkw_e($m['type']) . ' alert-dismissible fade show kkw-alert" role="alert">'
           . kkw_e($m['text'])
           . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }
}

function kkw_grade($p) {
    if ($p >= 90) return 'O';
    if ($p >= 80) return 'A+';
    if ($p >= 70) return 'A';
    if ($p >= 60) return 'B+';
    if ($p >= 50) return 'B';
    if ($p >= 40) return 'C';
    return 'F';
}

function kkw_calc($row) {                  // total, %, grade, PASS/FAIL
    global $KKW_MAX, $KKW_PASS_MARK;
    $subjects = kkw_get_subjects($row['course'] ?? '', $row['semester'] ?? '');
    $total = 0;
    $fail  = false;
    foreach ($subjects as $k => $name) {
        $m = (int)($row[$k] ?? 0);
        $total += $m;
        if ($m < $KKW_PASS_MARK) $fail = true;
    }
    $max = count($subjects) * $KKW_MAX;
    $per = round($total * 100 / $max, 2);
    return [
        'total'  => $total,
        'max'    => $max,
        'per'    => $per,
        'grade'  => $fail ? 'F' : kkw_grade($per),
        'status' => $fail ? 'FAIL' : 'PASS',
    ];
}
