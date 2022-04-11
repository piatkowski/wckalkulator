<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die();
}

include 'woocommerce-kalkulator.php';

use WCKalkulator\Plugin;

Plugin::uninstall();