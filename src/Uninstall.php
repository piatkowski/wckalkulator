<?php

namespace WCKalkulator;

/** Destructive cleanup used only by uninstall.php, without booting the plugin. */
class Uninstall
{
    const BATCH_SIZE = 100;

    /** Remove plugin configuration from every site while preserving order history and uploads. */
    public static function run()
    {
        if (is_multisite()) {
            $offset = 0;
            do {
                $site_ids = get_sites(array(
                    'fields' => 'ids',
                    'number' => self::BATCH_SIZE,
                    'offset' => $offset
                ));
                foreach ($site_ids as $site_id) {
                    switch_to_blog($site_id);
                    try {
                        self::cleanup_site();
                    } finally {
                        restore_current_blog();
                    }
                }
                $offset += self::BATCH_SIZE;
            } while (count($site_ids) === self::BATCH_SIZE);
            return;
        }

        self::cleanup_site();
    }

    /** Remove settings, scheduled events, term metadata, and plugin configuration posts. */
    private static function cleanup_site()
    {
        global $wpdb;

        wp_clear_scheduled_hook('wck_admin_notice_schedule');
        wp_clear_scheduled_hook('wck_cron_delete_customer_uploads');
        wp_clear_scheduled_hook('wck_cron_delete_cart_uploads');

        delete_option('wck_version');
        delete_option('wck_options');
        delete_option('wck_admin_notice_state');

        delete_metadata('term', 0, 'wck_value', '', true);

        // Query IDs in bounded batches and use WordPress deletion APIs so core
        // removes the associated metadata, terms, comments, and relationships.
        do {
            $sql = $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type IN (%s, %s) ORDER BY ID ASC LIMIT %d",
                'wck_fieldset',
                'wck_global_parameter',
                self::BATCH_SIZE
            );
            $post_ids = $wpdb->get_col($sql);
            foreach ($post_ids as $post_id) {
                wp_delete_post((int) $post_id, true);
            }
        } while (count($post_ids) === self::BATCH_SIZE);
    }
}
