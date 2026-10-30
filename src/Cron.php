<?php

namespace WCKalkulator;

/**
 * Class Cron
 *
 * Schedule and execute cron jobs
 *
 * @package WCKalkulator
 * @author Krzysztof Piątkowski
 * @license GPLv2
 * @since 1.3.0
 */
class Cron
{
    const PREFIX = "wck_cron_";

    /**
     * @var array
     */
    private static $jobs;

    /**
     * Initialize Cron static class, add actions and filters. Initialize properties
     *
     * @return void
     * @since 1.3.0
     */
    public static function init()
    {
        self::$jobs = array(
            /*
             * Delete custom uploads
             */
            self::PREFIX . 'delete_customer_uploads',
            /*
             * Delete temporary upload files (abandoned carts)
             */
            self::PREFIX . 'delete_cart_uploads'
        );

        add_filter('cron_schedules', array(__CLASS__, 'cron_interval'));
        add_action('init', array(__CLASS__, 'schedule_jobs'));

        /*
         * Register cron jobs
         */
        foreach (self::$jobs as $job) {
            add_action($job, array(__CLASS__, str_replace(self::PREFIX, '', $job)));
        }
    }

    /**
     * Deactivate all cron jobs. This method is used in plugin's deactivation hook
     *
     * @return void
     * @since 1.3.0
     */
    public static function deactivate()
    {
        foreach (self::job_names() as $job) {
            wp_clear_scheduled_hook($job);
        }
    }

    private static function job_names()
    {
        return array(
            self::PREFIX . 'delete_customer_uploads',
            self::PREFIX . 'delete_cart_uploads'
        );
    }

    /**
     * Set custom cron intervals
     *
     * @param array $schedules
     * @return array
     * @since 1.3.0
     */
    public static function cron_interval($schedules)
    {
        $schedules['wck_interval_1'] = array(
            'interval' => 8 * 60 * 60,
            'display' => 'WCK Inteval #1'
        );

        return $schedules;
    }

    /**
     * Keep jobs scheduled
     *
     * @return void
     * @since 1.3.0
     */
    public static function schedule_jobs()
    {
        foreach(self::$jobs as $job) {
            if (!wp_next_scheduled($job)) {
                wp_schedule_event(time(), 'wck_interval_1', $job);
            }
        }
    }

    /**
     * ------------------
     *     CRON JOBS
     * ------------------
     */

    /**
     * Delete customer upload files
     *
     * @return void
     */
    public static function delete_customer_uploads()
    {

        $time_keep = time() - ((int)Settings::get('upload_retain_time') * 24 * 60 * 60);
        foreach (PrivateUploads::registered_uploads() as $upload) {
            if (isset($upload['record']['created']) && $upload['record']['created'] < $time_keep) {
                PrivateUploads::delete_registered($upload['record'], $upload['path'], $time_keep);
            }
        }
    }

    /**
     * Delete uploaded file to the temp directory
     *
     * @return void
     */
    public static function delete_cart_uploads()
    {
        $time_keep = time() - ((int)Settings::get('upload_temp_retain_time') * 24 * 60 * 60);

        foreach (glob(rtrim(get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'wckalkulator_tmp_*') as $path) {
            if (is_link($path) || !is_file($path) || !preg_match('/^wckalkulator_tmp_[a-f0-9]{32}\.[a-z0-9]{1,10}$/i', basename($path))) {
                continue;
            }
            $handle = @fopen($path, 'rb');
            if (!$handle || !flock($handle, LOCK_EX)) {
                if ($handle) {
                    fclose($handle);
                }
                continue;
            }
            clearstatcache(true, $path);
            if (file_exists($path) && filemtime($path) < $time_keep) {
                @unlink($path);
            }
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

}
