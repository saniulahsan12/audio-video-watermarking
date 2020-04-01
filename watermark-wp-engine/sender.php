<?php
include '../../../wp-load.php';

//scanning for the files in the directory
$token = '0A4A4A14-A0C0-48F4-ABBD-C46587FB9B57';
$dev_server_post_url = 'http://159.203.166.86/watermark-dev/receiver.php?token=' . $token;
//$dev_server_post_url = 'http://localhost/watermark-dev/receiver.php?token=' . $token;

$uploads = wp_upload_dir(); /* get uploads directory path*/
$upload_dir = $uploads['path'] . '/';
$search_dir = $upload_dir . 'for_process';
$contents = scandir($search_dir);
if (!empty($contents)) {
    foreach ($contents as $video) {
        if ($video == '.' || $video == '..') {
            continue;
        }
        //check for all video formats or skip the loop
        $file_name = basename($video);
        $file_extension = pathinfo($video, PATHINFO_EXTENSION);

        if (
            strtolower($file_extension) == 'mp4'
            || strtolower($file_extension) == 'mkv'
            || strtolower($file_extension) == 'mov'
            || strtolower($file_extension) == '3gp'
            || strtolower($file_extension) == 'mpeg'
            || strtolower($file_extension) == 'flv'
            || strtolower($file_extension) == 'wmv'
            || strtolower($file_extension) == 'mpg'
            || strtolower($file_extension) == 'avi'
        ) {
            //go further
        } else {
            continue;
        }

        try {
            // posting to dev server
            $localFile = $search_dir . '/' . $video;
            if (function_exists('curl_file_create')) {
                $cFile = curl_file_create($localFile);
            } else { //
                $cFile = '@' . realpath($localFile);
            }
            $post = ['file'=> $cFile];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$dev_server_post_url);
            curl_setopt($ch, CURLOPT_POST,1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            $result = curl_exec ($ch);
            curl_close ($ch);
            echo "File moved for watermarking: " . $video . "\n";
            unlink($localFile);
        } catch (Exception $e) {
            echo "File not moved.\n";
        }

        break;
    }
}
exit;