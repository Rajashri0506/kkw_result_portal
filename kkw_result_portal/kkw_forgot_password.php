<?php
/* =====================================================
   kkw_forgot_password.php  -  Password reset
   ===================================================== */
require 'kkw_db_config.php';

$err = '';
$done = '';
$email = strtolower(trim($_POST['email'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_pass = $_POST['new_pass'] ?? '';
    $confirm_pass = $_POST['confirm_pass'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Enter the email address registered with your account.';
    } elseif (strlen($new_pass) < 6) {
        $err = 'New password must be at least 6 characters.';
    } elseif ($new_pass !== $confirm_pass) {
        $err = 'Both new password fields must match.';
    } else {
        $st = $kkw_con->prepare('SELECT uid FROM kkw_login_users WHERE email = ?');
        $st->bind_param('s', $email);
        $st->execute();
        $user = $st->get_result()->fetch_assoc();

        if (!$user) {
            $err = 'No account was found with that email address.';
        } else {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $st = $kkw_con->prepare('UPDATE kkw_login_users SET pass_hash = ? WHERE uid = ?');
            $st->bind_param('si', $hash, $user['uid']);
            $st->execute();
            $done = 'Your password has been reset. You can now log in with the new password.';
            $email = '';
        }
    }
}

$kkw_title = 'Forgot Password';
include 'kkw_parts/kkw_top.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow kkw-fade">
            <div class="card-body p-4 text-center">
                <div class="kkw-login-icon"><i class="bi bi-shield-lock"></i></div>
                <h2 class="card-title mb-3">Forgot password?</h2>
                <p class="text-muted mb-4">Enter your registered email address and choose a new password.</p>

                <?php if ($err): ?><div class="alert alert-danger kkw-alert"><?= kkw_e($err) ?></div><?php endif; ?>
                <?php if ($done): ?><div class="alert alert-success kkw-alert"><?= kkw_e($done) ?></div><?php endif; ?>

                <form method="post" action="kkw_forgot_password.php" class="text-start">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= kkw_e($email) ?>" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="new_pass" class="form-label">New password</label>
                        <input type="password" class="form-control" id="new_pass" name="new_pass" minlength="6" required>
                    </div>
                    <div class="mb-4">
                        <label for="confirm_pass" class="form-label">Confirm new password</label>
                        <input type="password" class="form-control" id="confirm_pass" name="confirm_pass" minlength="6" required>
                    </div>
                    <button type="submit" class="btn kkw-btn-navy w-100 py-2"><i class="bi bi-key"></i> Reset password</button>
                </form>

                <a href="kkw_login.php" class="btn btn-link mt-3"><i class="bi bi-arrow-left"></i> Back to login</a>
            </div>
        </div>
    </div>
</div>

<?php include 'kkw_parts/kkw_bottom.php'; ?>
