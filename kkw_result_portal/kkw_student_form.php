<?php
/* =====================================================
   PAGE 2 : kkw_student_form.php  -  Student Information
   New student : kkw_student_form.php
   Edit        : kkw_student_form.php?sid=5
   ===================================================== */
require 'kkw_db_config.php';
kkw_need_login();

$sid    = (int)($_POST['sid'] ?? ($_GET['sid'] ?? 0));
$isEdit = $sid > 0;
$errs   = [];

$f = ['roll_no'=>'', 'stu_name'=>'', 'father_name'=>'', 'dob'=>'', 'gender'=>'', 'email'=>'',
      'mobile'=>'', 'course'=>'', 'semester'=>'', 'address'=>'',
      'sub1'=>'', 'sub2'=>'', 'sub3'=>'', 'sub4'=>'', 'sub5'=>''];

// ---- Load student for edit ----
if ($isEdit && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $st = $kkw_con->prepare('SELECT * FROM kkw_student_master WHERE sid = ?');
    $st->bind_param('i', $sid);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    if (!$row) {
        kkw_msg('Student not found.', 'danger');
        header('Location: kkw_student_list.php');
        exit;
    }
    foreach ($f as $k => $v) { $f[$k] = $row[$k]; }
}

// ---- Save form ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($f as $k => $v) { $f[$k] = trim($_POST[$k] ?? ''); }

    if (!preg_match('/^[A-Za-z0-9\-\/]{1,20}$/', $f['roll_no']))  $errs[] = 'Roll number: letters, numbers, - or / (max 20).';
    if (!preg_match('/^[A-Za-z .]{2,100}$/', $f['stu_name']))     $errs[] = 'Student name: letters and spaces only.';
    if (!preg_match('/^[A-Za-z .]{2,100}$/', $f['father_name']))  $errs[] = "Father's name: letters and spaces only.";
    $d = DateTime::createFromFormat('Y-m-d', $f['dob']);
    if (!$d || $d->format('Y-m-d') !== $f['dob'] || $d > new DateTime()) $errs[] = 'Enter a valid date of birth.';
    if (!in_array($f['gender'], ['Male', 'Female', 'Other'], true)) $errs[] = 'Select gender.';
    if (!filter_var($f['email'], FILTER_VALIDATE_EMAIL))           $errs[] = 'Enter a valid email address.';
    if (!preg_match('/^[6-9][0-9]{9}$/', $f['mobile']))            $errs[] = 'Mobile: 10 digits starting with 6, 7, 8 or 9.';
    if (!in_array($f['course'], $KKW_COURSES, true))               $errs[] = 'Select course.';
    if (!in_array($f['semester'], $KKW_SEMESTERS, true))           $errs[] = 'Select semester.';
    if ($f['address'] === '')                                      $errs[] = 'Address is required.';

    if (!$errs) {
        try {
            if ($isEdit) {
                $st = $kkw_con->prepare('UPDATE kkw_student_master SET roll_no=?, stu_name=?, father_name=?, dob=?, gender=?,
                        email=?, mobile=?, course=?, semester=?, address=? WHERE sid=?');
                $st->bind_param('ssssssssssi', $f['roll_no'], $f['stu_name'], $f['father_name'], $f['dob'], $f['gender'],
                        $f['email'], $f['mobile'], $f['course'], $f['semester'], $f['address'], $sid);
                $st->execute();
                $st = $kkw_con->prepare('DELETE FROM kkw_result_store WHERE sid = ?');
                $st->bind_param('i', $sid);
                $st->execute();
                kkw_msg('Student details updated. Continue to marks entry.');
            } else {
                $zero1 = 0; $zero2 = 0; $zero3 = 0; $zero4 = 0; $zero5 = 0;
                $st = $kkw_con->prepare('INSERT INTO kkw_student_master (roll_no, stu_name, father_name, dob, gender, email, mobile,
                        course, semester, address, sub1, sub2, sub3, sub4, sub5) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
                $st->bind_param('ssssssssssiiiii', $f['roll_no'], $f['stu_name'], $f['father_name'], $f['dob'], $f['gender'],
                        $f['email'], $f['mobile'], $f['course'], $f['semester'], $f['address'], $zero1, $zero2, $zero3, $zero4, $zero5);
                $st->execute();
                $sid = $kkw_con->insert_id;
                kkw_msg('Student details saved. Enter the subject marks next.');
            }
            header('Location: kkw_student_marks.php?sid=' . $sid);
            exit;
        } catch (mysqli_sql_exception $ex) {
            $errs[] = ($ex->getCode() == 1062)
                ? 'Roll number ' . $f['roll_no'] . ' is already registered. Use a different roll number.'
                : 'Database error: ' . $ex->getMessage();
        }
    }
}

function kkw_options($list, $sel) {
    $h = '<option value="">-- Select --</option>';
    foreach ($list as $it) {
        $h .= '<option value="' . kkw_e($it) . '"' . ($it === $sel ? ' selected' : '') . '>' . kkw_e($it) . '</option>';
    }
    return $h;
}

$kkw_title  = $isEdit ? 'Edit Student' : 'Student Information';
$kkw_active = 'form';
include 'kkw_parts/kkw_top.php';
?>

<?php kkw_show_msg(); ?>

<div class="kkw-stepper mb-4 kkw-fade">
    <div class="kkw-step active"><span>1</span> Student Information</div>
    <div class="kkw-step"><span>2</span> Subject Marks</div>
</div>

<div class="card shadow-sm kkw-fade <?= $errs ? 'kkw-has-error' : '' ?>">
    <div class="card-body p-4">
        <h2 class="card-title mb-1"><i class="bi bi-person-vcard"></i> <?= $isEdit ? 'Edit Student Information' : 'Student Registration Form' ?></h2>
        <p class="text-muted">Fill in the student details, then continue to the next step to enter marks.</p>

        <?php if ($errs): ?>
            <div class="alert alert-danger kkw-alert"><b>Please correct:</b><ul class="mb-0">
                <?php foreach ($errs as $x): ?><li><?= kkw_e($x) ?></li><?php endforeach; ?>
            </ul></div>
        <?php endif; ?>

        <form method="post" action="kkw_student_form.php">
            <input type="hidden" name="sid" value="<?= $sid ?>">

            <h3 class="kkw-section">Personal details</h3>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="roll_no" class="form-label">Roll number</label>
                    <input type="text" class="form-control" id="roll_no" name="roll_no" value="<?= kkw_e($f['roll_no']) ?>" placeholder="e.g. MSC-01" required>
                </div>
                <div class="col-md-4">
                    <label for="stu_name" class="form-label">Student name</label>
                    <input type="text" class="form-control" id="stu_name" name="stu_name" value="<?= kkw_e($f['stu_name']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="father_name" class="form-label">Father's name</label>
                    <input type="text" class="form-control" id="father_name" name="father_name" value="<?= kkw_e($f['father_name']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="dob" class="form-label">Date of birth</label>
                    <input type="date" class="form-control" id="dob" name="dob" value="<?= kkw_e($f['dob']) ?>" max="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label d-block">Gender</label>
                    <?php foreach (['Male', 'Female', 'Other'] as $g): ?>
                        <div class="form-check form-check-inline mt-1">
                            <input class="form-check-input" type="radio" name="gender" id="gen<?= $g ?>" value="<?= $g ?>" <?= $f['gender'] === $g ? 'checked' : '' ?>>
                            <label class="form-check-label" for="gen<?= $g ?>"><?= $g ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="col-md-4">
                    <label for="mobile" class="form-label">Mobile number</label>
                    <input type="tel" class="form-control" id="mobile" name="mobile" maxlength="10" value="<?= kkw_e($f['mobile']) ?>" placeholder="10 digits" required>
                </div>
                <div class="col-md-4">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= kkw_e($f['email']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="course" class="form-label">Course</label>
                    <select class="form-select" id="course" name="course" required><?= kkw_options($KKW_COURSES, $f['course']) ?></select>
                </div>
                <div class="col-md-4">
                    <label for="semester" class="form-label">Semester</label>
                    <select class="form-select" id="semester" name="semester" required><?= kkw_options($KKW_SEMESTERS, $f['semester']) ?></select>
                </div>
                <div class="col-12">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="2" required><?= kkw_e($f['address']) ?></textarea>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn kkw-btn-navy px-4">
                    <i class="bi bi-arrow-right-circle"></i> <?= $isEdit ? 'Update details & continue' : 'Save details & continue' ?></button>
                <?php if ($isEdit): ?>
                    <a href="kkw_student_list.php" class="btn btn-outline-secondary">Cancel</a>
                <?php else: ?>
                    <button type="reset" class="btn btn-outline-secondary">Clear form</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php include 'kkw_parts/kkw_bottom.php'; ?>
