<?php
header('Content-Type: text/plain');
echo "PHP " . PHP_VERSION . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "folder bisa ditulis: " . (is_writable(__DIR__) ? 'ya' : 'tidak') . "\n";
