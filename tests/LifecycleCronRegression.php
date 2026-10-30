<?php
/** Verify that deactivation clears every plugin-owned cron task. */
namespace {
    $wck_cleared_hooks = array();
    function wp_clear_scheduled_hook($hook) { global $wck_cleared_hooks; $wck_cleared_hooks[] = $hook; }
    require __DIR__ . '/../src/Cron.php';
    require __DIR__ . '/../src/AdminNotice.php';

    \WCKalkulator\Cron::deactivate();
    \WCKalkulator\AdminNotice::deactivate();
    sort($wck_cleared_hooks);
    $expected = array('wck_admin_notice_schedule', 'wck_cron_delete_cart_uploads', 'wck_cron_delete_customer_uploads');
    sort($expected);
    if ($wck_cleared_hooks !== $expected) {
        fwrite(STDERR, "Deactivation did not clear every plugin cron task.\n");
        exit(1);
    }
    echo "Lifecycle cron regression: 3/3 tasks cleared\n";
}
