<?php
include '../../../wp-load.php';

$token = '0A4A4A14-A0C0-48F4-ABBD-C46587FB9B57';
if ($_GET['token'] != $token) {
    return;
}

$uploads = wp_upload_dir(); /* get uploads directory path*/
$upload_dir = $uploads['path'] . '/' . 'watermarked' . '/';

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$upload_file = $upload_dir . $_FILES['file']['name'];
if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_file)) {
    echo "File uploaded: " . $_FILES['file']['name'] . "\n";
} else {
    echo "File not uploaded.\n";
}
exit;