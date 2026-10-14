<?php
/* =====================================================
   PAGE 3 : kkw_student_list.php  -  Student Details
   Search, Edit, Delete, View Result
   ===================================================== */
require 'kkw_db_config.php';
kkw_need_login();

// ---- Delete ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['del_sid'])) {
    $d = (int)$_POST['del_sid'];
    $st = $kkw_con->prepare('DELETE FROM kkw_student_master WHERE sid = ?');
    $st->bind_param('i', $d);
    $st->execute();
    if ($st->affected_rows > 0) { kkw_msg('Student deleted.'); } else { kkw_msg('Student not found.', 'warning'); }
    header('Location: kkw_student_list.php');
    exit;
}

// ---- Search / list ----
$q = trim($_GET['q'] ?? '');
$sql = 'SELECT m.*, r.rid FROM kkw_student_master m LEFT JOIN kkw_result_store r ON r.sid = m.sid';
if ($q !== '') {
    $like = '%' . $q . '%';
    $st = $kkw_con->prepare($sql . ' WHERE m.stu_name LIKE ? OR m.roll_no LIKE ? OR m.course LIKE ? ORDER BY m.roll_no');
    $st->bind_param('sss', $like, $like, $like);
    $st->execute();
    $list = $st->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $list = $kkw_con->query($sql . ' ORDER BY m.roll_no')->fetch_all(MYSQLI_ASSOC);
}

$kkw_title  = 'Student Details';
$kkw_active = 'list';
include 'kkw_parts/kkw_top.php';
?>

<?php kkw_show_msg(); ?>

<div class="card shadow-sm kkw-fade">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="card-title mb-0"><i class="bi bi-people"></i> Student Details
                <span class="badge bg-secondary fs-6"><?= count($list) ?></span></h2>
            <div class="d-flex gap-2">
                <form class="d-flex" method="get" action="kkw_student_list.php">
                    <input class="form-control me-2" type="search" name="q" placeholder="Name, roll no or course" value="<?= kkw_e($q) ?>" aria-label="Search">
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
                </form>
                <a href="kkw_student_form.php" class="btn kkw-btn-gold text-nowrap"><i class="bi bi-plus-lg"></i> Add student</a>
            </div>
        </div>

        <?php if (!$list): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="fs-5 my-3"><?= $q !== '' ? 'No student matches "' . kkw_e($q) . '".' : 'No students registered yet.' ?></p>
                <a href="kkw_student_form.php" class="btn kkw-btn-navy">Register a student</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr><th>#</th><th>Roll no</th><th>Name</th><th>Father's name</th><th>DOB</th><th>Gender</th>
                            <th>Course / Sem</th><th>Mobile</th><th>Email</th><th>Total</th><th>%</th><th>Result</th>
                            <th class="text-center">Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($list as $i => $s): $r = kkw_calc($s); ?>
                        <tr class="kkw-row">
                            <td><?= $i + 1 ?></td>
                            <td><?= kkw_e($s['roll_no']) ?></td>
                            <td><?= kkw_e($s['stu_name']) ?></td>
                            <td><?= kkw_e($s['father_name']) ?></td>
                            <td class="text-nowrap"><?= kkw_e(date('d-m-Y', strtotime($s['dob']))) ?></td>
                            <td><?= kkw_e($s['gender']) ?></td>
                            <td><?= kkw_e($s['course'] . ' / ' . $s['semester']) ?></td>
                            <td><?= kkw_e($s['mobile']) ?></td>
                            <td><?= kkw_e($s['email']) ?></td>
                            <td><?= $r['total'] ?>/<?= $r['max'] ?></td>
                            <td><?= $r['per'] ?></td>
                            <td><span class="<?= $r['status'] === 'PASS' ? 'kkw-pass' : 'kkw-fail' ?>"><?= $r['status'] ?></span>
                                <?php if ($s['rid']): ?><br><small class="text-success"><i class="bi bi-check2"></i> Saved</small><?php endif; ?></td>
                            <td class="text-center text-nowrap">
                                <a href="kkw_result_view.php?sid=<?= $s['sid'] ?>" class="btn btn-sm kkw-btn-navy" title="View result"><i class="bi bi-file-earmark-text"></i></a>
                                <a href="kkw_student_form.php?sid=<?= $s['sid'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="post" action="kkw_student_list.php" class="d-inline"
                                      onsubmit="return confirm(<?= kkw_e(json_encode('Delete ' . $s['stu_name'] . '?')) ?>);">
                                    <input type="hidden" name="del_sid" value="<?= $s['sid'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'kkw_parts/kkw_bottom.php'; ?>
