<?php
/**
 * Plugin Name: Image Video Watermark Plugin
 * Description: Basic watermarking for the Image and Video
 * Author: Saniul Ahsan
 * Author URI: saniulahsan.info
 * Version:1.0
 * Plugin URI: saniulahsan.info
 */

/* Creating New Metafields*/
define('TFM_IMAGE_STAMP_RESIZE_RATIO', 30);
add_action( 'rest_api_init', 'register_photo_video_meta_fields');
function register_photo_video_meta_fields(){
    register_meta( 'post', 'full_name', array(
        'type' => 'string',
        'description' => 'full name',
        'single' => true,
        'show_in_rest' => true
    ));

    register_meta( 'post', 'email_address', array(
        'type' => 'string',
        'description' => 'email address',
        'single' => true,
        'show_in_rest' => true
    ));

    register_meta( 'post', 'file_description', array(
        'type' => 'string',
        'description' => 'file description',
        'single' => true,
        'show_in_rest' => true
    ));

    register_meta( 'post', 'attr_user', array(
        'type' => 'string',
        'description' => 'attr user',
        'single' => true,
        'show_in_rest' => true
    ));

    register_meta( 'post', 'attr_type', array(
        'type' => 'string',
        'description' => 'attr type',
        'single' => true,
        'show_in_rest' => true
    ));

    register_meta( 'post', 'terms', array(
        'type' => 'string',
        'description' => 'terms',
        'single' => true,
        'show_in_rest' => true
    ));

    register_meta( 'post', 'site', array(
        'type' => 'string',
        'description' => 'site',
        'single' => true,
        'show_in_rest' => true
    ));

    register_meta( 'post', 'ip_address', array(
        'type' => 'string',
        'description' => 'ip_address',
        'single' => true,
        'show_in_rest' => true
    ));

    register_meta( 'post', 'attachment_ids', array(
        'type' => 'string',
        'description' => 'Attachment ids',
        'single' => true,
        'show_in_rest' => true
    ));

    register_meta( 'post', 'attachments', array(
        'type' => 'string',
        'description' => 'Attachments',
        'single' => true,
        'show_in_rest' => true
    ));

    register_meta( 'post', 'posted_media_links', array(
        'type' => 'string',
        'description' => 'Media Links (Human Readable)',
        'single' => true,
        'show_in_rest' => true
    ));

    register_meta( 'post', 'usp-file-single-original', array(
        'type' => 'string',
        'description' => 'USP File Single Original',
        'single' => true,
        'show_in_rest' => true
    ));

    register_meta( 'post', 'usp-file-single-thumbnail', array(
        'type' => 'string',
        'description' => 'USP File Single Thumbnail',
        'single' => true,
        'show_in_rest' => true
    ));

    register_meta( 'post', 'usp-file-single', array(
        'type' => 'string',
        'description' => 'USP File Single',
        'single' => true,
        'show_in_rest' => true
    ));

    register_meta( 'post', 'watermark-status', array(
        'type' => 'boolean',
        'description' => 'Watermark Status',
        'single' => true,
        'show_in_rest' => true
    ));
}

function wptp_add_tags_to_attachments() {
    register_taxonomy_for_object_type( 'post_tag', 'attachment' );
}
add_action( 'init' , 'wptp_add_tags_to_attachments' );

function wptp_add_category_to_attachments() {
    register_taxonomy_for_object_type( 'category', 'attachment' );
}
add_action( 'init' , 'wptp_add_category_to_attachments' );

// Function to get the client IP address
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

add_action( 'restrict_manage_posts', 'wpse45436_admin_posts_filter_restrict_manage_posts' );
/**
 * First create the dropdown
 * make sure to change POST_TYPE to the name of your custom post type
 *
 * @author Ohad Raz
 *
 * @return void
 */
function wpse45436_admin_posts_filter_restrict_manage_posts(){
    $type = 'post';
    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }

    //only add filter to post type you want
    if ('post' == $type){
        //change this to the list of values you want to show
        //in 'label' => 'value' format
        $values = array(
            'posted' => 'publish',
            'raw' => 'pending',
            'approved' => '',
            'declined' => 'trash',
        );
        ?>
        <select name="filter_by_post_status">
        <option value=""><?php _e('Filter By Post Status', 'wose45436'); ?></option>
        <?php
            $current_v = isset($_GET['filter_by_post_status'])? $_GET['filter_by_post_status']:'';
            foreach ($values as $label => $value) {
                printf
                    (
                        '<option value="%s"%s>%s</option>',
                        $value,
                        $value == $current_v? ' selected="selected"':'',
                        ucfirst($label)
                    );
                }
        ?>
        </select>
        <?php
    }
}


add_filter( 'parse_query', 'wpse45436_posts_filter' );
/**
 * if submitted filter by post meta
 *
 * make sure to change META_KEY to the actual meta key
 * and POST_TYPE to the name of your custom post type
 * @author Ohad Raz
 * @param  (wp_query object) $query
 *
 * @return Void
 */
function wpse45436_posts_filter( $query ){
    global $pagenow;
    $type = 'post';
    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }
    if ( 'post' == $type && is_admin() && $pagenow=='edit.php' && isset($_GET['filter_by_post_status']) && $_GET['filter_by_post_status'] != '' && $query->is_main_query()) {
        // $query->query_vars['meta_key'] = 'post_status';
        // $query->query_vars['meta_value'] = $_GET['filter_by_post_status'];
        $query->query_vars['post_status'] = $_GET['filter_by_post_status'];
    }
}

function get_post_stat($param){
    $stat = [
        'publish' => 'Published',
        'pending' => 'Raw',
        'draft' => 'Approved',
        'trash' =>  'Declined',
    ];

    return ucfirst($stat[$param]);
}

function gdx_excerpt_length( $length ) {
    return 140;
}
add_filter( 'excerpt_length', 'gdx_excerpt_length', 999 );

add_action('delete_attachment', 'gdx_attachment_manipulation');
function gdx_attachment_manipulation($id) {
    try {
        $post_get = get_post($id);
        unlink(wp_upload_dir()['path'] . '/' . pathinfo($post_get->guid)['filename'] .'-original-copy'. '.' . pathinfo($post_get->guid)['extension']);
    } catch(Exception $e){

    }
}

add_action( 'add_meta_boxes', 'wpt_add_event_metaboxes' );
function wpt_add_event_metaboxes() {
    add_meta_box(
        'wpt_events_location',
        'Watermark Actions',
        'wpt_events_location',
        'post',
        'side',
        'default'
    );
}

function wpt_events_location() {
    global $post;
?>
    <form action="" method="post" onsubmit="return false;">
        <input onclick="sendWatermark(<?php echo $post->ID; ?>)" id="send_watermark" style="width: 100% !important; margin-bottom: 15px !important;" type="submit" name="send_watermark" class="components-button editor-post-publish-button is-button is-primary" value="Upload For Watermark">
        <input onclick="downloadWatermark(<?php echo $post->ID; ?>)" id="download_watermark" style="width: 100% !important; margin-bottom: 15px !important;" type="submit" name="download_watermark" class="components-button editor-post-publish-button is-button is-primary" value="Download Watermark">
    </form>
    <div class="watermark-log-status" style="width: 100%;"></div>
    <script>
        function sendWatermark(post_id) {
            var data = {
                'action': 'send_watermark',
                'post_id': post_id
            };

            jQuery.post(ajaxurl, data, function(response) {
                console.log(response);
                jQuery('.watermark-log-status').html(response);
                alert('File uploaded for watermarking');
            }).error(function(){
                alert('Could not upload for watermarking');
            });
        }

        function downloadWatermark(post_id) {
            var data = {
                'action': 'download_watermark',
                'post_id': post_id
            };

            jQuery.post(ajaxurl, data, function(response) {
                console.log(response);
                jQuery('.watermark-log-status').html(response);
                alert('Watermark successfully done.');
            });
        }
    </script>
<?php
}

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
    return $dst;
}

add_action( 'wp_ajax_send_watermark', 'send_watermark' );
add_action( 'wp_ajax_download_watermark', 'download_watermark' );

function send_watermark() {
    //scanning for the files in the directory
    $token = '0A4A4A14-A0C0-48F4-ABBD-C46587FB9B57';
    $dev_server_post_url = 'http://159.203.166.86/watermark-dev/receiver.php?token=' . $token;
//    $dev_server_post_url = 'http://localhost/watermark-dev/receiver.php?token=' . $token;

    try {
        // posting to dev server
        $attachments = get_posts( array(
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_parent' => $_POST['post_id'],
//            'exclude' => get_post_thumbnail_id()
        ) );

        if (empty($attachments)) {
            echo 'No media attached to this post';
            return;
        }

        foreach ($attachments as $media) {
            $localFile = get_attached_file($media->ID);
            //check for all video formats or skip the loop
            $file_extension = pathinfo($localFile, PATHINFO_EXTENSION);

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
                //watermark the video here. actually send to the server for the job done
                if (function_exists('curl_file_create')) {
                    $cFile = curl_file_create($localFile);
                } else {
                    $cFile = '@' . realpath($localFile);
                }

                $post = ['file'=> $cFile];
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $dev_server_post_url);
                curl_setopt($ch, CURLOPT_POST,1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                $result = curl_exec ($ch);
                curl_close ($ch);
                echo "Video File moved for watermarking: " . basename($localFile);
            } else {

                $watermarkPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tfm-watermark.png';
                $stamp = imagecreatefrompng($watermarkPath);
                $stamp_ratio = imagesy($stamp) / imagesx($stamp); // to not break the image width to height ratio
                imagedestroy($stamp);

                $all_images = [];
                $original_image = wp_get_attachment_image_src( $media->ID, 'full' )[0];

                $dest_file = pathinfo($localFile)['dirname'] . '/' . pathinfo($localFile)['filename'] .'-original-copy'. '.' . pathinfo($localFile)['extension'];
                if(copy($localFile, $dest_file)) {
                    $dest_file_link = pathinfo($original_image)['dirname'] . '/' . pathinfo($dest_file)['basename'];
                } else {
                    $dest_file_link = $original_image;
                }

                foreach(get_intermediate_image_sizes() as $value){
                    $sized_image = basename(wp_get_attachment_image_src($media->ID, $value)[0]);
                    $all_images[] = $sized_image;
                }

                $all_images = array_unique($all_images);
                if(!empty($all_images)){
                    foreach ($all_images as $image){

                        $file_to_process = pathinfo($localFile)['dirname'] . DIRECTORY_SEPARATOR . $image;

                        if(strtolower(pathinfo($file_to_process, PATHINFO_EXTENSION)) == 'png'){
                            $im = imagecreatefrompng($file_to_process);
                        } else {
                            $im = imagecreatefromjpeg($file_to_process);
                        }

                        $crop_percentage = TFM_IMAGE_STAMP_RESIZE_RATIO/100;

                        $img_x = imagesx($im);
                        $img_y = imagesy($im);

                        $stamp = tfm_resize_watermark_stamp($watermarkPath, ceil($img_x*$crop_percentage), ceil($img_x*$crop_percentage*$stamp_ratio)); // resize the stamp image according to source image
                        $sx = imagesx($stamp);
                        $sy = imagesy($stamp);


                        imagecopy($im, $stamp, ($img_x - $sx) / 2, $img_y - $sy, 0, 0, $sx, $sy); // divided by 2 to make the image center at the bottom
                        imagepng($im, $file_to_process);
                        imagedestroy($im);
                        echo "Image File watermarked: " . $image;
                    }

                    update_post_meta($_POST['post_id'], 'usp-file-single-original', $dest_file_link);
                    update_post_meta($_POST['post_id'], 'usp-file-single', wp_get_attachment_image_src( $media->ID, 'full' )[0]);
                    update_post_meta($_POST['post_id'], 'usp-file-single-thumbnail', wp_get_attachment_image_src( $media->ID, 'medium' )[0]);

                    update_post_meta($_POST['post_id'], 'watermark-status', true);
                } else {
                    echo "No image found for processing.";
                    update_post_meta($_POST['post_id'], 'watermark-status', false);
                }
            }
        }
    } catch (Exception $e) {
        echo "File not moved";
        update_post_meta($_POST['post_id'], 'watermark-status', false);
    }
//    wp_die();
}

function download_watermark() {
    try {
        // posting to dev server
        $attachments = get_posts( array(
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_parent' => $_POST['post_id'],
//            'exclude' => get_post_thumbnail_id()
        ) );

        if (empty($attachments)) {
            echo 'No media attached to this post';
            return;
        }

        foreach ($attachments as $media) {
            $localFile = get_attached_file($media->ID);
            //check for all video formats or skip the loop
            $file_extension = pathinfo($localFile, PATHINFO_EXTENSION);

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
                $file_name = basename($localFile);

                // posting to wp engine server
                $wp_engine_post_url = 'http://159.203.166.86/watermark-dev/watermark.php?file_name=' . $file_name;
//                $wp_engine_post_url = 'http://localhost/watermark-dev/watermark.php?file_name=' . $file_name;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $wp_engine_post_url);
                $result = curl_exec ($ch);
                curl_close ($ch);
                echo $result;

                $water_mark_link = pathinfo( wp_get_attachment_url($media->ID) )['dirname'] . '/' . 'watermarked' . '/' . $file_name;

                update_post_meta($_POST['post_id'], 'usp-file-single-original', wp_get_attachment_url($media->ID));
                update_post_meta($_POST['post_id'], 'usp-file-single', $water_mark_link);
                update_post_meta($_POST['post_id'], 'usp-file-single-thumbnail', $water_mark_link);

                update_post_meta($_POST['post_id'], 'watermark-status', true);
            }
        }
    } catch (Exception $e) {
        echo "File not moved";

        update_post_meta($_POST['post_id'], 'watermark-status', false);
    }
//    wp_die();
}

