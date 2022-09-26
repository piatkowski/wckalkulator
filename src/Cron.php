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
        foreach(self::$jobs as $job) {
            wp_unschedule_event(wp_next_scheduled($job), $job);
        }
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

        //Since v.1.3.1 upload path is defined in WCK Settings
        //$customer_dir = '/wc-kalkulator/customer-data/';
        //$upload_path = wp_upload_dir()['basedir'] . $customer_dir;
        $upload_path = Settings::get('upload_customer_data_dir');

        if(!file_exists($upload_path)) {
            return;
        }

        $ext = array('jpg', 'jpeg', 'png', 'gif');
        $dir = new \RecursiveDirectoryIterator($upload_path);
        $files = new \RecursiveIteratorIterator($dir);
        $time_keep = time() - ((int)Settings::get('upload_retain_time') * 24 * 60 * 60);

        foreach($files as $file){
            if ($file->isFile() && in_array($file->getExtension(), $ext)) {
                if($file->getMTime() < $time_keep) {
                    unlink($file->getPathname());
                }
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

        foreach (new \DirectoryIterator(get_temp_dir()) as $file) {
            if($file->isFile() && substr($file->getFilename(), 0 ,17) === 'wckalkulator_tmp_') {
                if($file->getMTime() < $time_keep) {
                    unlink($file->getPathname());
                }
            }
        }
    }

}