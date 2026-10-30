<?php
/** Dependency-free sanitizer allowlist regressions. */
namespace {
    function wp_kses_allowed_html($context)
    {
        return array(
            'a' => array('href' => true, 'title' => true),
            'input' => array('type' => true, 'data-core' => true),
        );
    }

    require __DIR__ . '/../src/Sanitizer.php';

    $allowed = \WCKalkulator\Sanitizer::allowed_html();
    $passes = array(
        'WordPress anchor definition is preserved' => isset($allowed['a']['href'], $allowed['a']['title']),
        'WordPress duplicate input attributes are preserved' => isset($allowed['input']['type'], $allowed['input']['data-core']),
        'custom input attributes are added' => isset($allowed['input']['data-maxfilesize'], $allowed['input']['name']),
        'inline event handlers are excluded' => !isset($allowed['input']['onchange']),
    );
    $failed = array();
    foreach ($passes as $name => $passed) {
        if (!$passed) {
            $failed[] = $name;
        }
    }
    echo 'Sanitizer allowlist regression: ' . (count($passes) - count($failed)) . '/' . count($passes) . ' passed' . PHP_EOL;
    if ($failed) {
        echo implode(PHP_EOL, $failed) . PHP_EOL;
        exit(1);
    }
}
