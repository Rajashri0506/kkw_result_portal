</main>

<footer class="kkw-footer">
    <div class="container text-center">
        &copy; <?= date('Y') ?> Karmaveer Kakasaheb Wagh Education Society &nbsp;|&nbsp; Student Result Portal (PHP + MySQL + Bootstrap)
    </div>
</footer>

<?php if (!empty($kkw_use_bootstrap_cdn)): ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    onerror="this.onerror=null;this.src='kkw_assets/kkw_bootstrap/bootstrap.bundle.min.js';"></script>
<?php else: ?>
<script src="kkw_assets/kkw_bootstrap/bootstrap.bundle.min.js"></script>
<?php endif; ?>
<script src="kkw_assets/kkw_js/kkw_anim.js"></script>
</body>
</html>
