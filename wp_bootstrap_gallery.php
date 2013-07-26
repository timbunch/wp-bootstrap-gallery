<?php

/**
 * Class Name: wp_bootstrap_gallery
 * GitHub URI: https://github.com/twittem/wp-bootstrap-gallery
 * Description: A custom Wordpress gallery for dynamic thumbnail layout using Twitter Bootstrap 2 thumbnail layouts.
 * Version: 1.0
 * Author: Edward McIntyre - @twittem
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

function bootstrap_gallery_shortcode($atts) {
 
    global $post;
 
    extract(shortcode_atts(array(
        'orderby' => 'menu_order ASC, ID ASC',
        'id' => $post->ID,
        'itemtag' => 'dl',
        'icontag' => 'dt',
        'captiontag' => 'dd',
        'columns' => 3,
        'size' => 'medium',
        'link' => 'file'
    ), $atts));
 
    $args = array(
        'post_type' => 'attachment',
        'post_parent' => $id,
        'numberposts' => -1,
        'orderby' => $orderby
    ); 
    $images = get_posts($args);
    $image_count = count($images);
    $heading = '<h1>'.$image_count.' Images</h1>';
    $clearfix = '<div class="clearfix"></div>';
    $ul_thumbnail = '<ul class="thumbnails">';
    foreach ( $images as $image ) {     
        $caption = $image->post_excerpt;
 
        $description = $image->post_content;
        if($description == '') $description = $title;

        $image_alt = get_post_meta($image->ID,'_wp_attachment_image_alt', true);
 
        $img = wp_get_attachment_image_src($image->ID, $size);
        
        // render your gallery here
        $li_open = '<li class="span4">';
        $image_html = "<img src='$img[0]' alt='$image_alt' />";
        $li_close = '</li>';
        
        $img_complete = $li_open.$image_html.$li_close;
        $image_set = $image_set.$img_complete;
        
    }
    $ul_close = '</ul>';
    
    // RETURN THE RESULTS
    return $clearfix.$heading.$ul_thumbnail.$image_set.$ul_close;
}

/**
 * Class Name: wp_bootstrap_gallery
 * GitHub URI: https://github.com/twittem/wp-bootstrap-gallery
 * Description: A custom Wordpress gallery for dynamic thumbnail layout using Twitter Bootstrap 2 thumbnail layouts.
 * Version: 1.0
 * Author: Edward McIntyre - @twittem
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

function wp_bootstrap_gallery( $content, $attr ) {
	global $instance, $post;
	$instance++;

	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( ! $attr['orderby'] )
			unset( $attr['orderby'] );
	}

	extract( shortcode_atts( array(
		'order'			=>	'ASC',
		'orderby'		=>	'menu_order ID',
		'id'			=>	$post->ID,
		'itemtag'		=>	'figure',
		'icontag'		=>	'div',
		'captiontag'	=>	'figcaption',
		'columns'		=>	3,
		'size'			=>	'thumbnail',
		'include'		=>	'',
		'exclude'		=>	''
	), $attr ) );

	$id = intval( $id );

	if ( 'RAND' == $order ) {
		$orderby = 'none';
	}

	if ( $include ) {

		$include = preg_replace( '/[^0-9,]+/', '', $include );

		$_attachments = get_posts( array(
			'include'			=>	$include,
			'post_status'		=>	'inherit',
			'post_type'			=>	'attachment',
			'post_mime_type'	=>	'image',
			'order'				=>	$order,
			'orderby'			=>	$orderby
		) );

		$attachments = array();

		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = $_attachments[$key];
		}

	} elseif ( $exclude ) {

		$exclude = preg_replace( '/[^0-9,]+/', '', $exclude );

		$attachments = get_children( array(
			'post_parent'		=>	$id,
			'exclude'			=>	$exclude,
			'post_status'		=>	'inherit',
			'post_type'			=>	'attachment',
			'post_mime_type'	=>	'image',
			'order'				=>	$order,
			'orderby'			=>	$orderby
		) );

	} else {

		$attachments = get_children( array(
			'post_parent'		=>	$id,
			'post_status'		=>	'inherit',
			'post_type'			=>	'attachment',
			'post_mime_type'	=>	'image',
			'order'				=>	$order,
			'orderby'			=>	$orderby
		) );

	}

	if ( empty( $attachments ) ) {
		return;
	}

	if ( is_feed() ) {
		$output = "\n";
		foreach ( $attachments as $att_id => $attachment )
			$output .= wp_get_attachment_link( $att_id, $size, true ) . "\n";
		return $output;
	}

	$itemtag	=	tag_escape( $itemtag );
	$captiontag	=	tag_escape( $captiontag );
	$columns	=	intval( min( array( 8, $columns ) ) );
	$float		=	(is_rtl()) ? 'right' : 'left';

	$selector	=	"gallery-{$instance}";
	$size_class	=	sanitize_html_class( $size );
	$output		=	"<ul class='thumbnails' id='$selector'>";

	/**
 	 * Count number of items in $attachments array, and assign a colum layout to $span_array
 	 * variable based on the mumber of images in the $attachments array
	 */
	$span_array = null;

	switch (count($attachments)) {
		case 1:
			/* One full width image */
	        $span_array = array(12);
            $row_break = 0;
	        break;
	    case 2:
	    	/* Two half width images */
	        $span_array = array(6,6);
            $row_break = 0;
	        break;
	    case 3:
	    	/* One 3/4 width image with two 1/4 width images to the right */
	        $span_array = array(8,4,4);
            $row_break = 0;
	        break;
	    case 4:
	    	/* One full width image with three 1/3 width images underneath */
	    	$span_array = array(3,3,3,3);
            $row_break = 0;
	        break;
	    case 5:
	    	/* Two half width images with fout 1/4 width images underneath */
	    	$span_array = array(4,4,4,6,6);
            $row_break = 3;
	        break;
	    case 6:
	    	/* One 2/3 width image with two 1/3 width images to the right,
	    	 * and three 1/3 width images underneath */
	    	$span_array = array(8,4,4,4,4,4);
            $row_break = 3;
	        break;
	    default:
	    	/* One full width image with two 1/2 width images underneath
	    	 * All remaining images 1/3 width underneath */
	    	$span_array = array(3,3,3,3);
            $row_break = 4;
	        break;
	}

	$attachment_count = 0;

	foreach ( $attachments as $id => $attachment ) {
        if ($row_break and ( $row_break == $attachment_count ) ) {
            $output .="</ul><ul class='thumbnails'>";
            $row_break = $row_break+$row_break;
        }
        
		$attachment_image = wp_get_attachment_image( $id, 'full');
		$attachment_link = wp_get_attachment_link( $id, 'full', ! ( isset( $attr['link'] ) AND 'file' == $attr['link'] ) );

		$output .= "<li class='span" . $span_array[$attachment_count] . "'>";
		$output .= $attachment_link . "\n";
		$output .= "</li>\n";

		/*if(count($attachments) >= 7 && $attachment_count == 3){
			$attachment_count = 3;
		} else {*/
        $attachment_count++;
		//}
        
            
	}

	$output .= "</ul>\n";

	return $output;
}

add_filter( 'post_gallery', 'wp_bootstrap_gallery', 10, 2 );

?>
