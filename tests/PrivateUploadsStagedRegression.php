<?php
/** Staged upload identity checks using fixtures created only in the system temp directory. */
namespace {
    error_reporting(E_ALL & ~E_DEPRECATED);
    function get_temp_dir() { return sys_get_temp_dir(); }
    require __DIR__ . '/../src/PrivateUploads.php';

    $id = bin2hex(random_bytes(16));
    $extension = 'pdf';
    $path = \WCKalkulator\PrivateUploads::temp_path($id, $extension);
    $contents = "%PDF-test-fixture";
    file_put_contents($path, $contents);
    $file = array('file_id' => $id, 'extension' => $extension, 'size' => strlen($contents));
    $checks = array(
        'server-derived staged path and matching size accepted' => \WCKalkulator\PrivateUploads::is_staged($file),
        'different recorded size rejected' => !\WCKalkulator\PrivateUploads::is_staged(array_merge($file, array('size' => strlen($contents) + 1))),
        'path-like file id rejected' => !\WCKalkulator\PrivateUploads::is_staged(array_merge($file, array('file_id' => '../' . $id))),
        'unsupported extension rejected' => !\WCKalkulator\PrivateUploads::is_staged(array_merge($file, array('extension' => 'php')))
    );
    @unlink($path);
    $failed = array();
    foreach ($checks as $name => $passed) { if (!$passed) { $failed[] = $name; } }
    echo 'Staged upload regression: ' . (count($checks) - count($failed)) . '/' . count($checks) . ' passed' . PHP_EOL;
    if ($failed) { echo implode(PHP_EOL, $failed) . PHP_EOL; exit(1); }
}
