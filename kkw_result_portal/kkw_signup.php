<?php
/* =====================================================
   kkw_signup.php  -  Create a new login account
   ===================================================== */
require 'kkw_db_config.php';

$errs = [];
$fname = $lname = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = trim($_POST['full_name'] ?? '');
    $lname = trim($_POST['login_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $p1    = $_POST['pass1'] ?? '';
    $p2    = $_POST['pass2'] ?? '';

    if ($fname === '')                                   $errs[] = 'Full name is required.';
    if (!preg_match('/^[A-Za-z0-9_]{4,30}$/', $lname))   $errs[] = 'Username: 4-30 letters, numbers or underscore.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))      $errs[] = 'Enter a valid email address.';
    if (strlen($p1) < 6)                                 $errs[] = 'Password must be at least 6 characters.';
    if ($p1 !== $p2)                                     $errs[] = 'Both passwords must match.';

    if (!$errs) {
        $st = $kkw_con->prepare('SELECT uid FROM kkw_login_users WHERE login_name = ? OR email = ?');
        $st->bind_param('ss', $lname, $email);
        $st->execute();
        if ($st->get_result()->num_rows > 0) {
            $errs[] = 'That username or email is already registered. Choose another.';
        } else {
            $h  = password_hash($p1, PASSWORD_DEFAULT);
            $st = $kkw_con->prepare('INSERT INTO kkw_login_users (full_name, login_name, email, pass_hash) VALUES (?, ?, ?, ?)');
            $st->bind_param('ssss', $fname, $lname, $email, $h);
            $st->execute();
            kkw_msg('Account created. Log in with your new username and password.');
            header('Location: kkw_login.php');
            exit;
        }
    }
}

$kkw_title = 'Create Account';
include 'kkw_parts/kkw_top.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <div class="card shadow kkw-fade <?= $errs ? 'kkw-has-error' : '' ?>">
            <div class="card-body p-4">
                <div class="kkw-login-icon"><i class="bi bi-person-plus"></i></div>
                <h2 class="card-title text-center mb-4">Create Account</h2>

                <?php if ($errs): ?>
                    <div class="alert alert-danger kkw-alert"><ul class="mb-0">
                        <?php foreach ($errs as $x): ?><li><?= kkw_e($x) ?></li><?php endforeach; ?>
                    </ul></div>
                <?php endif; ?>

                <form method="post" action="kkw_signup.php">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?= kkw_e($fname) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="login_name" class="form-label">Username</label>
                        <input type="text" class="form-control" id="login_name" name="login_name" value="<?= kkw_e($lname) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= kkw_e($email) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="pass1" class="form-label">Password</label>
                        <input type="password" class="form-control" id="pass1" name="pass1" required>
                    </div>
                    <div class="mb-4">
                        <label for="pass2" class="form-label">Confirm password</label>
                        <input type="password" class="form-control" id="pass2" name="pass2" required>
                    </div>
                    <button type="submit" class="btn kkw-btn-navy w-100 py-2">Create account</button>
                </form>
                <hr>
                <p class="text-center mb-0">Already have an account? <a href="kkw_login.php" class="fw-semibold">Log in</a></p>
            </div>
        </div>
    </div>
</div>

<?php include 'kkw_parts/kkw_bottom.php'; ?>
