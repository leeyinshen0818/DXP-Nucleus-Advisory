<?php
require_once('wp-load.php');
$args = array('post_type' => 'nucleus_hf_set', 'numberposts' => -1);
$posts = get_posts($args);
if ($posts) {
    foreach($posts as $p) {
        echo "=== " . $p->post_title . " ===\n";
        $meta = get_post_meta($p->ID, '_nucleus_header_components', true);
        $data = is_string($meta) ? json_decode(base64_decode($meta), true) : $meta;
        print_r($data);
    }
}
