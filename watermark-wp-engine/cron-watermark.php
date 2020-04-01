<?php
include '../../../wp-load.php';
$args = array(
    'post_type'      => 'post',
    'posts_per_page' => 3,
    'order' => 'DESC',
    'orderby'=> 'ID',
    'post_status' => array('publish', 'pending', 'draft'),
    'meta_query'     => array(
        array(
            'key' => 'watermark-status',
            'compare' => 'NOT EXISTS'
        ),
    )
);
$event_query = new WP_Query( $args );

if ( $event_query->have_posts() ) {

    while ( $event_query->have_posts() ) {
        $event_query->the_post();


        $_POST['post_id'] = get_the_ID();

        send_watermark();
        //download_watermark();
    }
}

wp_reset_postdata();