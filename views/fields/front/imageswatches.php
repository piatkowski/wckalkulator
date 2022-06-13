<?php
if (!defined('ABSPATH')) {
    exit;
}

$view->css_class .= ' wck-imageswatches';
$view->hide_caption = true;

include 'imageselect.php';