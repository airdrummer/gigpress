<?php

// musician cpt utils

function mcpt_add_query_vars($qVars) //  shortcode atts are lowercase
{
        $qVars[] = "show_id";
        $qVars[] = 'noheadshot';
        return $qVars;
}
add_filter('query_vars', 'mcpt_add_query_vars');

function mcpt_query_atts( $atts ) // query params override shortcode atts
{
    global $wp_query;
    $atts = (array)$atts;
    $out  = array();
    foreach($atts as $name => $default) 
    {
        $qv = $wp_query->query_vars[$name];
        $out[$name] = ( !empty($qv) ? $qv : $default );
    }
    return $out;
}

function bc_musician_list_shortcode( $atts, $content=null ) 
{
    $atts = mcpt_query_atts( // url query params
                shortcode_atts( 
                    array(
                        'show_id'    => 0, // display musicians/instr in a show's cast 
                        'noheadshot' => 0
                    	),
                    $atts, 'musician_list' ));
                    
	$show_id    = intval(strtolower(sanitize_text_field( $atts['show_id'] )));
	$noheadshot = intval(strtolower(sanitize_text_field( $atts['noheadshot'] )));
	
	return bc_musician_list( $show_id, $noheadshot, $content );
}
add_shortcode( 'musician_list', 'bc_musician_list_shortcode' );

function bc_musician_list( $show_id, $noheadshot, $content ) 
{
    // Query args
    $args = array(
        'post_type'   => 'musician',
        'post_status' => 'publish',
       	'orderby'     => 'title',
        'order'       => 'ASC',
        'posts_per_page' => -1
    );

    ob_start();

    echo $content;
    echo '<div class=musician-list>';

	if ( $show_id > 0 )
	{
		[$show_title, $cast_data, $assist_data] = get_gigpress_show_cast_data($show_id);

		$title       = $cast_data[0];
		$instruments = $cast_data[1];

		echo "<h2 class=cast-title id='show-" . $show_id . "' title='". $title . "'>" . $show_title . "</h2><hr>";

		if ( empty($instruments) )
		{
		    echo "<h3>-- no cast assigned --</h3>";
            return ob_get_clean();
		}
		
		$args['post__in'] = array_keys($instruments);
		generate_musician_list($args, $instruments, $noheadshot);

		$title       = $assist_data[0];
		$instruments = $assist_data[1];
		if ( ! empty($instruments) )
		{
			echo "<h2 class=cast-title title='" .$title. "' >Assisted by:</h2>";
			$args['post__in'] = array_keys($instruments);
			generate_musician_list($args, $instruments, $noheadshot);
		}
	}
	else
		generate_musician_list($args, [], $noheadshot);

    echo '</div>';
    
    return ob_get_clean();
}

function generate_musician_list($args, $instruments, $noheadshot)
{
	$query = new WP_Query( $args );
	$musicians_array = $query->posts; 

	foreach ( $musicians_array as $musician ) 
	{
		$musician_id = $musician->ID;
		echo '<div class="musician-entry'
		                . ($noheadshot ? " noheadshot" : "")
    		            . '" id=musician-'. $musician_id . ' >';
		$name = str_replace("&nbsp; ", "",
						trim(get_field( 'first_name', $musician_id ) . ' ' .
				     		 get_field( 'last_name',  $musician_id )));

		echo '<details class="cast-member-row">';
		echo '<summary><div class="cast-header">';
	    echo '<h2>' . esc_html($name) . '</h2>';
        if ( ! $noheadshot && has_post_thumbnail($musician_id) )
			echo get_the_post_thumbnail(
										$musician_id, 'thumbnail', 
										array(
											'class' => 'headshot floatleft',
											'title' => $name . ' headshot',
											'alt'   => $name . ' headshot'
										));
	    echo '<div class="instruments">';
	    if ( empty($instruments) )
			echo strip_tags(
					    get_the_term_list(
					        $musician_id, 'instrument', '', ', ' , ''));
 		else 
			echo get_cast_instruments_string($instruments[$musician_id]);

        echo '</div></div></summary>';

        echo '<div class="bio details-content">' ;
		echo apply_filters( 'the_content', $musician->post_content );
		echo '</div>';

        echo '</details></div>';
    }    
    wp_reset_postdata();
}

/**
 * Auto-generate the Musician post title from ACF First Name + Last Name fields.
 */
add_action( 'acf/save_post', 'mcpt_set_title_from_name', 20 );
function mcpt_set_title_from_name( $musician_id ) 
{
    // Only run on the musician post type
    if ( get_post_type( $musician_id ) !== 'musician' )
        return;

    $first = trim( get_field( 'first_name', $musician_id ) );
    $last  = trim( get_field( 'last_name',  $musician_id ) );

    if ( ! $first && ! $last ) 
        return;

    $full_name = str_replace(", &nbsp;", "", trim( "$last, $first" ));

    // Avoid an infinite loop by unhooking before updating
    remove_action( 'acf/save_post', 'mcpt_set_title_from_name', 20 );

    wp_update_post( [
        'ID'         => $post_id,
        'post_title' => $full_name,
        'post_name'  => sanitize_title( $full_name ), // updates the slug too
    ] );

    add_action( 'acf/save_post', 'mcpt_set_title_from_name', 20 );
}

// Hide the native title field on musician edit screens
add_action( 'admin_head', function () {
    global $post;
    if ( $post && get_post_type( $post ) === 'musician' ) {
        echo '<style>#titlediv { display: none; }</style>';
    }
} );

?>