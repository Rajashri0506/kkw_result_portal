<?php
/* =====================================================
   kkw_parts/kkw_top.php  -  Banner + Menu (every page)
   Set $kkw_title and $kkw_active before including.
   ===================================================== */
$kkw_title  = $kkw_title  ?? 'Student Result Portal';
$kkw_active = $kkw_active ?? '';
$kkw_logged = !empty($_SESSION['kkw_uid']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= kkw_e($kkw_title) ?> | KKWES Result Portal</title>
    <?php if (!empty($kkw_use_bootstrap_cdn)): ?>
    <!-- Bootstrap 5 CDN: easy to demonstrate in class; local files remain the fallback. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php endif; ?>
    <!-- Bootstrap 5 + Icons (local copy - works without internet) -->
    <link rel="stylesheet" href="kkw_assets/kkw_bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="kkw_assets/kkw_icons/bootstrap-icons.min.css">
    <!-- Project style + animations -->
    <link rel="stylesheet" href="kkw_assets/kkw_style.css?v=<?= is_file(__DIR__ . '/../kkw_assets/kkw_style.css') ? filemtime(__DIR__ . '/../kkw_assets/kkw_style.css') : '1' ?>">
</head>
<body>

<!-- Page loader animation -->
<div id="kkwLoader" class="<?= !empty($kkw_use_bootstrap_cdn) ? 'kkw-login-loader' : '' ?>"><div class="kkw-spinner"></div></div>
<script>
    /* Safety: hide loader and show all content after 2 seconds, whatever happens */
    setTimeout(function () {
        var l = document.getElementById('kkwLoader');
        if (l) l.classList.add('kkw-hide');
        document.documentElement.classList.add('kkw-done');
    }, 2000);
</script>

<!-- ===== Banner (same on all pages) ===== -->
<header class="kkw-banner">
<?php if (is_file(__DIR__ . '/../kkw_images/kkw_banner.png')): ?>
    <img src="kkw_images/kkw_banner.png" alt="Karmaveer Kakasaheb Wagh Education Society - NAAC B+">
<?php else: ?>
    <!-- banner image file not found: text banner is shown instead -->
    <div class="kkw-banner-text" style="display:block">Karmaveer Kakasaheb Wagh Education Society
        <small>NAAC Accredited with Grade B+</small></div>
<?php endif; ?>
</header>

<!-- ===== Menu ===== -->
<nav class="navbar navbar-expand-lg navbar-dark kkw-nav">
    <div class="container">
        <span class="navbar-brand"><i class="bi bi-mortarboard-fill"></i> Student Result Portal</span>
        <?php if ($kkw_logged): ?>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#kkwMenu"
                aria-controls="kkwMenu" aria-expanded="false" aria-label="Open menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="kkwMenu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link <?= $kkw_active === 'form' ? 'active' : '' ?>" href="kkw_student_form.php">
                    <span class="kkw-step">2</span> Student Information</a></li>
                <li class="nav-item"><a class="nav-link <?= $kkw_active === 'list' ? 'active' : '' ?>" href="kkw_student_list.php">
                    <span class="kkw-step">3</span> Student Details</a></li>
                <li class="nav-item"><a class="nav-link <?= $kkw_active === 'result' ? 'active' : '' ?>" href="kkw_result_view.php">
                    <span class="kkw-step">4</span> Result</a></li>
            </ul>
            <span class="navbar-text me-3 text-white-50"><i class="bi bi-person-circle"></i> <?= kkw_e($_SESSION['kkw_name']) ?></span>
            <a href="kkw_logout.php" class="btn btn-sm kkw-btn-gold"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
        <?php endif; ?>
    </div>
</nav>

<main class="container my-4 flex-grow-1">
