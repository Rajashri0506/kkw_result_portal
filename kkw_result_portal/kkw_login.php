<?php
/* =====================================================
   PAGE 1 : kkw_login.php  -  Login
   Default account: admin / admin123
   ===================================================== */
require 'kkw_db_config.php';

$kkw_use_bootstrap_cdn = true;

if (!empty($_SESSION['kkw_uid'])) {          // already logged in
    header('Location: kkw_student_form.php');
    exit;
}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lname = trim($_POST['login_name'] ?? '');
    $lpass = $_POST['login_pass'] ?? '';

    if ($lname === '' || $lpass === '') {
        $err = 'Enter both username and password.';
    } else {
        $st = $kkw_con->prepare('SELECT uid, full_name, pass_hash FROM kkw_login_users WHERE login_name = ?');
        $st->bind_param('s', $lname);
        $st->execute();
        $u = $st->get_result()->fetch_assoc();

        if ($u && password_verify($lpass, $u['pass_hash'])) {
            session_regenerate_id(true);
            $_SESSION['kkw_uid']   = $u['uid'];
            $_SESSION['kkw_login'] = $lname;
            $_SESSION['kkw_name']  = $u['full_name'];
            kkw_msg('Welcome, ' . $u['full_name'] . '! You are logged in.');
            header('Location: kkw_student_form.php');
            exit;
        }
        $err = 'Wrong username or password.';
    }
}

$kkw_title = 'Login';
include 'kkw_parts/kkw_top.php';
?>

<div class="kkw-auth-wrap kkw-login-page">
    <script>
        // Always start the login page at the banner after refresh or back navigation.
        window.addEventListener('pageshow', function () {
            window.setTimeout(function () { window.scrollTo(0, 0); }, 0);
        });
    </script>
    <div class="row justify-content-center align-items-center g-0 w-100">
        <div class="col-12 col-xl-10">
            <section class="kkw-department-heading text-center" aria-labelledby="department-title">
                <span class="kkw-heading-orbit kkw-heading-orbit-left"><i class="bi bi-shield-lock"></i></span>
                <span class="kkw-heading-orbit kkw-heading-orbit-right"><i class="bi bi-database-check"></i></span>
                <h1 id="department-title">Department of Computer Science and Application</h1>
                <span class="kkw-heading-line" aria-hidden="true"></span>
            </section>
            <div class="kkw-login-shell kkw-fade">
                <div class="card shadow <?= $err ? 'kkw-has-error' : '' ?>">
                    <div class="row g-0">
                        <div class="col-md-6">
                            <div class="card-body p-4 p-xl-5">
                        <div class="kkw-login-icon"><i class="bi bi-person-lock"></i></div>
                        <h2 class="card-title text-center mb-1">Welcome</h2>
                        <p class="text-center text-muted mb-4">Sign in with your registered account to continue</p>

                <?php kkw_show_msg(); ?>
                <?php if ($err): ?><div class="alert alert-danger kkw-alert"><?= kkw_e($err) ?></div><?php endif; ?>

                <form method="post" action="kkw_login.php">
                    <div class="mb-3">
                        <label for="login_name" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="login_name" name="login_name"
                                value="<?= kkw_e($_POST['login_name'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="login_pass" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" id="login_pass" name="login_pass" required>
                        </div>
                    </div>
                    <button type="submit" class="btn kkw-btn-navy w-100 py-2"><i class="bi bi-box-arrow-in-right"></i> Log in</button>
                </form>

                <div class="text-end mt-3">
                    <a href="kkw_forgot_password.php" class="btn btn-link p-0 text-decoration-none fw-semibold">Forgot password?</a>
                </div>

                        <div class="alert alert-light border small mt-3 mb-0 text-center">
                            Default login: <b>admin</b> / <b>admin123</b>
                        </div>
                        <hr>
                        <p class="text-center mb-0">New user? <a href="kkw_signup.php" class="fw-semibold">Create an account</a></p>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="kkw-login-visual" aria-label="Animated login security illustration">
                                <div class="kkw-window-dots"><span></span><span></span><span></span></div>
                                <div class="kkw-lock-art"><i class="bi bi-unlock-fill" aria-hidden="true"></i></div>
                                <div class="kkw-demo-field"><i class="bi bi-person-fill"></i><span class="kkw-moving-dots" aria-hidden="true"><b></b><b></b><b></b><b></b><b></b><b></b><b></b><b></b></span></div>
                                <div class="kkw-demo-field"><i class="bi bi-lock-fill"></i><span class="kkw-demo-line"></span></div>
                                <div class="kkw-orbit kkw-orbit-one"><i class="bi bi-key-fill"></i></div>
                                <div class="kkw-orbit kkw-orbit-two"><i class="bi bi-shield-check"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'kkw_parts/kkw_bottom.php'; ?>
