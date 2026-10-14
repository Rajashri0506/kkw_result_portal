/* kkw_anim.js - small animations for the portal (no library needed) */
(function () {
    // 1. Hide page loader when page is ready (max 1.5 s)
    function hideLoader() {
        var l = document.getElementById('kkwLoader');
        if (l) l.classList.add('kkw-hide');
    }
    window.addEventListener('load', hideLoader);
    setTimeout(hideLoader, 1500);

    document.addEventListener('DOMContentLoaded', function () {
        // 2. Table rows appear one after another
        var rows = document.querySelectorAll('.kkw-row');
        rows.forEach(function (r, i) { r.style.animationDelay = (0.3 + i * 0.07) + 's'; });

        // 3. Numbers count up (percentage, total)  -> <span data-kkw-count="81.8" data-kkw-dec="2">
        document.querySelectorAll('[data-kkw-count]').forEach(function (el) {
            var end = parseFloat(el.getAttribute('data-kkw-count')) || 0;
            var dec = parseInt(el.getAttribute('data-kkw-dec') || '0', 10);
            var suffix = el.getAttribute('data-kkw-suffix') || '';
            var start = null, dur = 1200;
            function step(ts) {
                if (!start) start = ts;
                var p = Math.min((ts - start) / dur, 1);
                var eased = 1 - Math.pow(1 - p, 3);
                el.textContent = (end * eased).toFixed(dec) + suffix;
                if (p < 1) requestAnimationFrame(step);
            }
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                el.textContent = end.toFixed(dec) + suffix;
            } else {
                requestAnimationFrame(step);
            }
        });

        // 4. Shake the form card when there are errors
        var bad = document.querySelector('.kkw-has-error');
        if (bad) { bad.classList.add('kkw-shake'); }

        // 5. Auto close success messages after 4 seconds
        document.querySelectorAll('.alert-success.kkw-alert').forEach(function (a) {
            setTimeout(function () {
                if (window.bootstrap) { bootstrap.Alert.getOrCreateInstance(a).close(); }
            }, 4000);
        });
    });
})();
