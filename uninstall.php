<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die();
}

include 'wc-kalkulator.php';

use WCKalkulator\Plugin;

Plugin::uninstall();