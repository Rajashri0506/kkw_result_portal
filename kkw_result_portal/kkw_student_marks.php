<?php
/* =====================================================
   PAGE 2B : kkw_student_marks.php  -  Subject marks entry
   ===================================================== */
require 'kkw_db_config.php';
kkw_need_login();

$sid = (int)($_GET['sid'] ?? ($_POST['sid'] ?? 0));
if ($sid <= 0) {
    header('Location: kkw_student_list.php');
    exit;
}

$st = $kkw_con->prepare('SELECT * FROM kkw_student_master WHERE sid = ?');
$st->bind_param('i', $sid);
$st->execute();
$student = $st->get_result()->fetch_assoc();

if (!$student) {
    kkw_msg('Student not found.', 'danger');
    header('Location: kkw_student_list.php');
    exit;
}

$course = trim($_POST['course'] ?? $student['course']);
$semester = trim($_POST['semester'] ?? $student['semester']);
$subjects = kkw_get_subjects($course, $semester);
$marks = [];
foreach (['sub1', 'sub2', 'sub3', 'sub4', 'sub5'] as $k) {
    $marks[$k] = trim($_POST[$k] ?? (string)($student[$k] ?? ''));
}

$errs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['sub1', 'sub2', 'sub3', 'sub4', 'sub5'] as $k) {
        $value = trim($_POST[$k] ?? '');
        if ($value === '' || !ctype_digit($value) || (int)$value < 0 || (int)$value > $KKW_MAX) {
            $errs[] = 'Marks for ' . kkw_e($subjects[$k]) . ' must be 0 to ' . $KKW_MAX . '.';
        }
        $marks[$k] = $value;
    }

    if (!$errs) {
        $m1 = (int)$marks['sub1'];
        $m2 = (int)$marks['sub2'];
        $m3 = (int)$marks['sub3'];
        $m4 = (int)$marks['sub4'];
        $m5 = (int)$marks['sub5'];

        $st = $kkw_con->prepare('UPDATE kkw_student_master SET course=?, semester=?, sub1=?, sub2=?, sub3=?, sub4=?, sub5=? WHERE sid=?');
        $st->bind_param('ssiiiiii', $course, $semester, $m1, $m2, $m3, $m4, $m5, $sid);
        $st->execute();

        $st = $kkw_con->prepare('DELETE FROM kkw_result_store WHERE sid = ?');
        $st->bind_param('i', $sid);
        $st->execute();

        kkw_msg('Marks saved successfully for ' . $student['stu_name'] . '.');
        header('Location: kkw_result_view.php?sid=' . $sid);
        exit;
    }
}

$kkw_title  = 'Student Marks';
$kkw_active = 'form';
include 'kkw_parts/kkw_top.php';
?>

<?php kkw_show_msg(); ?>

<div class="kkw-stepper mb-4 kkw-fade">
    <div class="kkw-step done"><span>1</span> Student Information</div>
    <div class="kkw-step active"><span>2</span> Subject Marks</div>
</div>

<div class="card shadow-sm kkw-fade <?= $errs ? 'kkw-has-error' : '' ?>">
    <div class="card-body p-4">
        <h2 class="card-title mb-1"><i class="bi bi-journal-check"></i> Enter Subject Marks</h2>
        <p class="text-muted mb-4">The available subjects update automatically when the course and semester change.</p>

        <?php if ($errs): ?>
            <div class="alert alert-danger kkw-alert"><b>Please correct:</b><ul class="mb-0">
                <?php foreach ($errs as $x): ?><li><?= kkw_e($x) ?></li><?php endforeach; ?>
            </ul></div>
        <?php endif; ?>

        <div class="alert alert-light border mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <strong>Student:</strong> <?= kkw_e($student['stu_name']) ?>
                </div>
                <div class="col-md-3">
                    <strong>Roll no:</strong> <?= kkw_e($student['roll_no']) ?>
                </div>
                <div class="col-md-3">
                    <strong>Course:</strong> <?= kkw_e($course) ?>
                </div>
            </div>
        </div>

        <form method="post" action="kkw_student_marks.php" class="kkw-mark-form">
            <input type="hidden" name="sid" value="<?= $sid ?>">

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="courseSelect" class="form-label">Course</label>
                    <select id="courseSelect" name="course" class="form-select" required>
                        <?php foreach ($KKW_COURSES as $c): ?>
                            <option value="<?= kkw_e($c) ?>" <?= $c === $course ? 'selected' : '' ?>><?= kkw_e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="semesterSelect" class="form-label">Semester</label>
                    <select id="semesterSelect" name="semester" class="form-select" required>
                        <?php foreach ($KKW_SEMESTERS as $s): ?>
                            <option value="<?= kkw_e($s) ?>" <?= $s === $semester ? 'selected' : '' ?>><?= kkw_e($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row g-3 align-items-stretch">
                <?php foreach (['sub1', 'sub2', 'sub3', 'sub4', 'sub5'] as $k): ?>
                    <div class="col-md-4 col-lg-2-5 kkw-subject-col" data-subject-card>
                        <div class="kkw-subject-card h-100">
                            <label for="<?= $k ?>" class="form-label subject-name"><?= kkw_e($subjects[$k]) ?></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-clipboard-check"></i></span>
                                <input type="number" class="form-control" id="<?= $k ?>" name="<?= $k ?>" min="0" max="<?= $KKW_MAX ?>"
                                       value="<?= kkw_e($marks[$k] ?? '') ?>" required>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-4 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn kkw-btn-navy px-4"><i class="bi bi-save2"></i> Save marks</button>
                <a href="kkw_student_form.php?sid=<?= $sid ?>" class="btn btn-outline-secondary">Back to student info</a>
                <a href="kkw_student_list.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const courseSelect = document.getElementById('courseSelect');
    const semesterSelect = document.getElementById('semesterSelect');
    const subjectMap = <?= json_encode($KKW_SUBJECT_MAP, JSON_UNESCAPED_UNICODE) ?>;

    function updateSubjects() {
        const course = courseSelect.value;
        const semester = semesterSelect.value;
        const names = subjectMap[course]?.[semester] || ['Subject 1', 'Subject 2', 'Subject 3', 'Subject 4', 'Subject 5'];

        document.querySelectorAll('[data-subject-card]').forEach(function (card, index) {
            const label = card.querySelector('.subject-name');
            const input = card.querySelector('input');
            const name = names[index] || 'Subject ' + (index + 1);
            label.textContent = name;
            input.setAttribute('aria-label', name);
            input.setAttribute('title', name);
        });
    }

    courseSelect.addEventListener('change', updateSubjects);
    semesterSelect.addEventListener('change', updateSubjects);
    updateSubjects();
})();
</script>

<?php include 'kkw_parts/kkw_bottom.php'; ?>
