</main><!-- /#admin-main -->

<!-- ══ Scripts dùng chung ══ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ── Sidebar Toggle (Mobile) ── */
function toggleSidebar() {
    document.getElementById('admin-sidebar').classList.toggle('open');
    document.getElementById('sidebar-overlay').classList.toggle('show');
}
function closeSidebar() {
    document.getElementById('admin-sidebar').classList.remove('open');
    document.getElementById('sidebar-overlay').classList.remove('show');
}

/* ── Toast Notification System (Bottom-Right, Stackable) ──
 * Cách dùng:  adminToast('Nội dung thông báo', 'success');
 * type: 'success' | 'error' | 'warning'
 */
function adminToast(message, type = 'success') {
    const container = document.getElementById('admin-toast-container');
    if (!container) return;

    const cfg = {
        success: { icon: 'fas fa-check-circle' },
        error:   { icon: 'fas fa-times-circle'  },
        warning: { icon: 'fas fa-exclamation-triangle' },
    };
    const c = cfg[type] || cfg.success;

    const toast = document.createElement('div');
    toast.className = `admin-toast ${type}`;
    toast.innerHTML = `
        <i class="t-icon ${c.icon}"></i>
        <span class="t-msg">${message}</span>
        <button class="t-close" onclick="dismissAdminToast(this.parentElement)">
            <i class="fas fa-times"></i>
        </button>
        <div class="t-bar" style="
            position:absolute; bottom:0; left:0; height:3px;
            width:100%; background:currentColor; opacity:0.25;
            transform-origin:left;
            animation: toastBarShrink 4s linear forwards;
        "></div>
    `;
    toast.style.position = 'relative';
    container.appendChild(toast);

    // Trigger slide-up + fade-in
    requestAnimationFrame(() => {
        requestAnimationFrame(() => toast.classList.add('show'));
    });

    // Auto dismiss
    const timer = setTimeout(() => dismissAdminToast(toast), 4000);
    toast._timer = timer;
}

function dismissAdminToast(el) {
    if (!el) return;
    clearTimeout(el._timer);
    el.classList.remove('show');
    el.classList.add('hide');
    setTimeout(() => el.remove(), 350);
}

// Progress bar keyframe
(function() {
    if (document.getElementById('admin-toast-kf')) return;
    const s = document.createElement('style');
    s.id = 'admin-toast-kf';
    s.textContent = `@keyframes toastBarShrink { from { transform:scaleX(1); } to { transform:scaleX(0); } }`;
    document.head.appendChild(s);
})();

/* ── Hiển thị PHP session toast (nếu có) ── */
<?php if (isset($_SESSION['toast_msg'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    adminToast(
        <?php echo json_encode(htmlspecialchars($_SESSION['toast_msg'])); ?>,
        <?php echo json_encode($_SESSION['toast_type'] ?? 'success'); ?>
    );
});
<?php
    unset($_SESSION['toast_msg']);
    unset($_SESSION['toast_type']);
endif;
?>
</script>
</body>
</html>
<?php if (ob_get_level() > 0) ob_end_flush(); ?>
