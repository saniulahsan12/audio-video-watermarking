<?php
require 'vendor/autoload.php';
define('TFM_IMAGE_STAMP_RESIZE_RATIO', 30);
function tfm_resize_watermark_stamp($file, $w, $h) {
    list($width, $height) = getimagesize($file);
    $newheight = $h;
    $newwidth = $w;

    $src = imagecreatefrompng($file);
    $dst = imagecreatetruecolor($newwidth, $newheight);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
    imagefilledrectangle($dst, 0, 0, $newwidth, $newheight, $transparent);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
    imagepng($dst, "resized-stamp/tfm-watermark.png");
    imagedestroy($dst);
    return [
        'watermark_source' => "resized-stamp/tfm-watermark.png",
        'watermark_width' => $newwidth
    ];
}

function processVideoWaterMarkCrawler($videoSource, $reqExtension, $watermark = 'tfm-watermark.png') {
    $ffmpeg = FFMpeg\FFMpeg::create();

    $video = $ffmpeg->open($videoSource);

    $video_width = 0;
    $video_height = 0;

    foreach ($video->getStreams()->videos() as $stream) {
        if ($stream->has('width') && $stream->has('height')) {
            $video_width = $stream->get('width');
            $video_height = $stream->get('height');
            break;
        }
    }

    $watermarkPath = $watermark;
    $stamp = imagecreatefrompng($watermarkPath);
    $stamp_ratio = imagesy($stamp) / imagesx($stamp); // to not break the image width to height ratio
    imagedestroy($stamp);

    $crop_percentage = TFM_IMAGE_STAMP_RESIZE_RATIO/100;
    $stamp = tfm_resize_watermark_stamp($watermark, ceil($video_width*$crop_percentage), ceil($video_width*$crop_percentage*$stamp_ratio)); // resize the stamp image according to source image

    if($video_width == 0) {
        $video_width = $stamp['watermark_width'];
    }

    $format = new FFMpeg\Format\Video\X264('libmp3lame', 'libx264');
    $video->filters()->watermark($stamp['watermark_source'], array(
        'position' => 'relative',
        'bottom' => 0,
        'right' => ($video_width - $stamp['watermark_width'])/2,
    ));

    $format->setKiloBitrate(1000)->setAudioChannels(2)->setAudioKiloBitrate(256);

    $fileLoc = getcwd() . '/completed/';
    $saveLocation = $fileLoc . basename($videoSource);
    if($video->save($format, $saveLocation)){
        unlink($videoSource);
        echo "Watermarked success: " . basename($videoSource) . "\n";

        //scanning for the files in the directory sender codes
        $token = '0A4A4A14-A0C0-48F4-ABBD-C46587FB9B57';
        $wp_engine_post_url = 'https://dev.totalfratmove.com/wp-content/uploads/watermark-wp-engine/receiver.php?token=' . $token;
//        $wp_engine_post_url = 'http://localhost/wp-grandex/wp-content/uploads/watermark-wp-engine/receiver.php?token=' . $token;
        $search_dir = './completed';
        try {
            // posting to dev server
            $localFile = $search_dir . '/' . trim($_GET['file_name']);
            if (function_exists('curl_file_create')) {
                $cFile = curl_file_create($localFile);
            } else { //
                $cFile = '@' . realpath($localFile);
            }
            $post = ['file'=> $cFile];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $wp_engine_post_url);
            curl_setopt($ch, CURLOPT_POST,1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            $result = curl_exec ($ch);
            curl_close ($ch);
            echo "Watermarked file moved to wp_engine root: " . trim($_GET['file_name']);
            unlink($localFile);
        } catch (Exception $e) {
            echo "File not moved.\n";
        }
        // sender code ends
    } else {
        echo "Watermarked failed: " . basename($videoSource) . "\n";
    }
}

// get contents of the saved directory first and move to running
$search_dir = './saved';
$running_dir = './running';
$contents = scandir($search_dir);
$video = trim($_GET['file_name']);
rename($search_dir . DIRECTORY_SEPARATOR . $video, $running_dir . DIRECTORY_SEPARATOR . $video);
$file_name = basename($video);
$file_extension = pathinfo($video, PATHINFO_EXTENSION);

try {
    $response = processVideoWaterMarkCrawler($running_dir . '/' . $file_name, $file_extension, 'tfm-watermark.png');
} catch (Exception $e) {
    echo 'Video processing error: '.  $e->getMessage(). "\n";
}

exit;
