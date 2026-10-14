<?php
/* =====================================================
   PAGE 4 : kkw_result_view.php  -  Result Display / Save / Download
   kkw_result_view.php         -> choose student + saved list
   kkw_result_view.php?sid=5   -> marksheet of that student
   ===================================================== */
require 'kkw_db_config.php';
kkw_need_login();

$sid = (int)($_GET['sid'] ?? 0);

// ---- SAVE RESULT ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_sid'])) {
    $ss = (int)$_POST['save_sid'];
    $st = $kkw_con->prepare('SELECT * FROM kkw_student_master WHERE sid = ?');
    $st->bind_param('i', $ss);
    $st->execute();
    $stu = $st->get_result()->fetch_assoc();

    if ($stu) {
        $r   = kkw_calc($stu);
        $by  = $_SESSION['kkw_login'];
        $now = date('Y-m-d H:i:s');

        $chk = $kkw_con->prepare('SELECT rid FROM kkw_result_store WHERE sid = ?');
        $chk->bind_param('i', $ss);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $st = $kkw_con->prepare('UPDATE kkw_result_store SET total_marks=?, percent=?, grade=?, final_status=?, saved_by=?, saved_on=? WHERE sid=?');
            $st->bind_param('idssssi', $r['total'], $r['per'], $r['grade'], $r['status'], $by, $now, $ss);
        } else {
            $st = $kkw_con->prepare('INSERT INTO kkw_result_store (sid, total_marks, percent, grade, final_status, saved_by, saved_on) VALUES (?,?,?,?,?,?,?)');
            $st->bind_param('iidssss', $ss, $r['total'], $r['per'], $r['grade'], $r['status'], $by, $now);
        }
        $st->execute();
        kkw_msg('Result of ' . $stu['stu_name'] . ' saved successfully.');
    } else {
        kkw_msg('Student not found.', 'danger');
    }
    header('Location: kkw_result_view.php?sid=' . $ss);
    exit;
}

// ---- Load selected student ----
$stu = null; $saved = null; $res = null;
if ($sid > 0) {
    $st = $kkw_con->prepare('SELECT * FROM kkw_student_master WHERE sid = ?');
    $st->bind_param('i', $sid);
    $st->execute();
    $stu = $st->get_result()->fetch_assoc();
    if ($stu) {
        $res = kkw_calc($stu);
        $st  = $kkw_con->prepare('SELECT * FROM kkw_result_store WHERE sid = ?');
        $st->bind_param('i', $sid);
        $st->execute();
        $saved = $st->get_result()->fetch_assoc();
    }
}

$allStu   = $kkw_con->query('SELECT sid, roll_no, stu_name FROM kkw_student_master ORDER BY roll_no')->fetch_all(MYSQLI_ASSOC);
$savedAll = $kkw_con->query('SELECT r.*, m.roll_no, m.stu_name, m.course, m.semester FROM kkw_result_store r
                             JOIN kkw_student_master m ON m.sid = r.sid ORDER BY r.saved_on DESC')->fetch_all(MYSQLI_ASSOC);

$kkw_title  = $stu ? 'Result - ' . $stu['stu_name'] : 'Result';
$kkw_active = 'result';
include 'kkw_parts/kkw_top.php';
?>

<?php kkw_show_msg(); ?>

<!-- Select student -->
<div class="card shadow-sm mb-4 kkw-noprint kkw-fade">
    <div class="card-body">
        <form method="get" action="kkw_result_view.php" class="row g-2 align-items-end">
            <div class="col-md-8">
                <label for="sid" class="form-label">Select student to view result</label>
                <select name="sid" id="sid" class="form-select" required>
                    <option value="">-- Select student --</option>
                    <?php foreach ($allStu as $a): ?>
                        <option value="<?= $a['sid'] ?>" <?= $a['sid'] == $sid ? 'selected' : '' ?>><?= kkw_e($a['roll_no'] . ' - ' . $a['stu_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4"><button type="submit" class="btn kkw-btn-navy w-100"><i class="bi bi-eye"></i> Show result</button></div>
        </form>
        <?php if (!$allStu): ?><p class="mt-3 mb-0">No students yet. <a href="kkw_student_form.php">Register a student</a> first.</p><?php endif; ?>
    </div>
</div>

<?php if ($sid > 0 && !$stu): ?><div class="alert alert-warning">Student not found. Select from the list.</div><?php endif; ?>

<?php if ($stu): ?>
<!-- Buttons -->
<div class="d-flex flex-wrap gap-2 mb-3 kkw-noprint kkw-fade kkw-d1">
    <form method="post" action="kkw_result_view.php" class="d-inline">
        <input type="hidden" name="save_sid" value="<?= $stu['sid'] ?>">
        <button type="submit" class="btn btn-success"><i class="bi bi-database-check"></i> <?= $saved ? 'Save result again' : 'Save result' ?></button>
    </form>
    <button type="button" class="btn kkw-btn-gold" id="kkwPdfBtn"><i class="bi bi-file-earmark-pdf"></i> Download PDF</button>
    <button type="button" class="btn btn-outline-primary" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
    <a href="kkw_student_list.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to details</a>
    <?php if ($saved): ?>
        <span class="align-self-center small text-success ms-md-2"><i class="bi bi-check-circle"></i>
            Saved on <?= kkw_e(date('d-m-Y h:i A', strtotime($saved['saved_on']))) ?> by <?= kkw_e($saved['saved_by']) ?></span>
    <?php else: ?>
        <span class="align-self-center small text-danger ms-md-2"><i class="bi bi-exclamation-circle"></i> Result not saved yet</span>
    <?php endif; ?>
</div>

<!-- Marksheet -->
<div id="kkwSheet" class="card p-4 kkw-fade kkw-d2">
    <img src="kkw_images/kkw_banner.png" class="kkw-print-banner mb-2" alt="KKWES banner">
    <div class="kkw-sheet-title">Statement of Marks</div>

    <table class="table table-bordered mb-4">
        <tr><th style="width:20%">Student name</th><td><?= kkw_e($stu['stu_name']) ?></td>
            <th style="width:20%">Roll number</th><td><?= kkw_e($stu['roll_no']) ?></td></tr>
        <tr><th>Father's name</th><td><?= kkw_e($stu['father_name']) ?></td>
            <th>Date of birth</th><td><?= kkw_e(date('d-m-Y', strtotime($stu['dob']))) ?></td></tr>
        <tr><th>Course</th><td><?= kkw_e($stu['course']) ?></td><th>Semester</th><td><?= kkw_e($stu['semester']) ?></td></tr>
        <tr><th>Gender</th><td><?= kkw_e($stu['gender']) ?></td><th>Email</th><td><?= kkw_e($stu['email']) ?></td></tr>
    </table>

    <table class="table table-bordered text-center align-middle">
        <thead><tr><th>Sr.</th><th class="text-start">Subject</th><th>Max marks</th><th>Min marks</th>
            <th>Marks obtained</th><th>Grade</th><th>Remark</th></tr></thead>
        <tbody>
        <?php $n = 1; $subjects = kkw_get_subjects($stu['course'], $stu['semester']); foreach ($subjects as $k => $subName): $mk = (int)$stu[$k]; ?>
            <tr class="kkw-row">
                <td><?= $n++ ?></td>
                <td class="text-start"><?= kkw_e($subName) ?></td>
                <td><?= $KKW_MAX ?></td>
                <td><?= $KKW_PASS_MARK ?></td>
                <td><?= $mk ?></td>
                <td><?= kkw_grade($mk * 100 / $KKW_MAX) ?></td>
                <td class="<?= $mk >= $KKW_PASS_MARK ? 'kkw-pass' : 'kkw-fail' ?>"><?= $mk >= $KKW_PASS_MARK ? 'Pass' : 'Fail' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot class="fw-bold"><tr><td colspan="2" class="text-end">Grand total</td><td><?= $res['max'] ?></td><td></td>
            <td><span data-kkw-count="<?= $res['total'] ?>"><?= $res['total'] ?></span></td><td colspan="2"></td></tr></tfoot>
    </table>

    <div class="row text-center g-3 mt-1">
        <div class="col-4"><div class="kkw-stat"><div class="small text-muted">Percentage</div>
            <div class="fs-4 fw-bold"><span data-kkw-count="<?= $res['per'] ?>" data-kkw-dec="2" data-kkw-suffix="%"><?= number_format($res['per'], 2) ?>%</span></div></div></div>
        <div class="col-4"><div class="kkw-stat"><div class="small text-muted">Overall grade</div>
            <div class="fs-4 fw-bold"><span class="kkw-pop"><?= $res['grade'] ?></span></div></div></div>
        <div class="col-4"><div class="kkw-stat"><div class="small text-muted">Result</div>
            <div class="fs-4"><span class="kkw-pop <?= $res['status'] === 'PASS' ? 'kkw-pass' : 'kkw-fail' ?>"><?= $res['status'] ?></span></div></div></div>
    </div>

    <p class="small text-muted mt-3">Grading: O &ge; 90, A+ &ge; 80, A &ge; 70, B+ &ge; 60, B &ge; 50, C &ge; 40, F &lt; 40.
        Minimum <?= $KKW_PASS_MARK ?> marks in every subject is required to pass.</p>

    <div class="d-flex justify-content-between mt-4 pt-4">
        <div>Date: <?= date('d-m-Y') ?></div>
        <div class="text-center">______________________<br>Controller of Examinations</div>
    </div>
</div>

<!-- PDF download (local library, works offline) -->
<script src="kkw_assets/kkw_js/html2pdf.bundle.min.js"></script>
<script>
document.getElementById('kkwPdfBtn').addEventListener('click', function () {
    var btn    = this;
    var sheet  = document.getElementById('kkwSheet');
    var banner = sheet.querySelector('.kkw-print-banner');
    var oldTxt = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Preparing PDF...';
    banner.style.display = 'block';
    sheet.classList.add('kkw-noanim');          // freeze animations for a clean PDF
    html2pdf().set({
        margin: 8,
        filename: <?= json_encode('Result_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $stu['roll_no']) . '.pdf') ?>,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    }).from(sheet).save().then(function () {
        banner.style.display = '';
        sheet.classList.remove('kkw-noanim');
        btn.disabled = false;
        btn.innerHTML = oldTxt;
    });
});
</script>
<?php endif; ?>

<!-- Saved results list -->
<div class="card shadow-sm mt-4 kkw-noprint kkw-fade kkw-d3">
    <div class="card-body">
        <h3 class="card-title h4"><i class="bi bi-archive"></i> Saved results</h3>
        <?php if (!$savedAll): ?>
            <p class="mb-0 text-muted">No results saved yet. Open a student's result and click Save result.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover align-middle mb-0">
                    <thead><tr><th>Roll no</th><th>Name</th><th>Course</th><th>Total</th><th>%</th><th>Grade</th>
                        <th>Result</th><th>Saved on</th><th>By</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($savedAll as $x): ?>
                        <tr class="kkw-row">
                            <td><?= kkw_e($x['roll_no']) ?></td>
                            <td><?= kkw_e($x['stu_name']) ?></td>
                            <td><?= kkw_e($x['course'] . ' / ' . $x['semester']) ?></td>
                            <td><?= $x['total_marks'] ?></td>
                            <td><?= $x['percent'] ?></td>
                            <td><?= kkw_e($x['grade']) ?></td>
                            <td class="<?= $x['final_status'] === 'PASS' ? 'kkw-pass' : 'kkw-fail' ?>"><?= kkw_e($x['final_status']) ?></td>
                            <td class="text-nowrap"><?= kkw_e(date('d-m-Y h:i A', strtotime($x['saved_on']))) ?></td>
                            <td><?= kkw_e($x['saved_by']) ?></td>
                            <td><a href="kkw_result_view.php?sid=<?= $x['sid'] ?>" class="btn btn-sm kkw-btn-navy">Open</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'kkw_parts/kkw_bottom.php'; ?>
