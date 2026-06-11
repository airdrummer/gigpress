<?php

// musician cpt utils

function mcpt_add_query_vars($qVars) //  shortcode atts are lowercase
{
        $qVars[] = "show_id";
        $qVars[] = 'revealheadshot';
        $qVars[] = 'list_instruments';
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
                        'revealheadshot' => false,
                        'list_instruments' => false,
                    	),
                    $atts, 'musician_list' ));

    if( (bool) ($atts['list_instruments'] ?? false))
        return bc_instruments_list();

	$show_id    = intval(strtolower(sanitize_text_field( $atts['show_id'] )));
	$revealheadshot = (bool) ($atts['revealheadshot'] ?? false);
	
	return bc_musician_list( $show_id, $revealheadshot, $content );
}
add_shortcode( 'musician_list', 'bc_musician_list_shortcode' );

function bc_musician_list( $show_id, $revealheadshot, $content ) 
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
		generate_musician_list($args, $instruments, $revealheadshot);

		$title       = $assist_data[0];
		$instruments = $assist_data[1];
		if ( ! empty($instruments) )
		{
			echo "<p title='" .$title. "' >Assisted by:</p>";
			$args['post__in'] = array_keys($instruments);
			generate_musician_list($args, $instruments, $revealheadshot);
		}
	}
	else
		generate_musician_list($args, [], $revealheadshot);

    echo '</div>';
    
    return ob_get_clean();
}

function generate_musician_list($args, $instruments, $revealheadshot)
{
	$query = new WP_Query( $args );
	$musicians_array = $query->posts; 

	foreach ( $musicians_array as $musician ) 
	{
		$musician_id = $musician->ID;
		echo '<div class="musician-entry'
		                . ($revealheadshot ? " revealheadshot" : "")
    		            . '" id=musician-'. $musician_id . ' >';
		$name = str_replace("&nbsp; ", "",
						trim(get_field( 'first_name', $musician_id ) . ' ' .
				     		 get_field( 'last_name',  $musician_id )));

		echo '<details class="cast-member-row"'
				        . ($revealheadshot ? "" : " open") . '>';
		echo '<summary><div class="cast-header">';
	    echo '<h2>' . esc_html($name) . '</h2>';
	    echo '<div class="instruments">';
	    if ( empty($instruments) )
	    {
			echo strip_tags(
					    get_the_term_list(
					        $musician_id, 'instrument', '', ', ' , ''));
 		}
 		else
			echo get_cast_instruments_string($instruments[$musician_id]);
        echo '</div>';
        echo '</div>';
			
        if ( ! $revealheadshot && has_post_thumbnail($musician_id) )
			echo get_the_post_thumbnail(
										$musician_id, 'thumbnail', 
										array(
											'class' => 'headshot floatleft',
											'title' => $name . ' headshot',
											'alt'   => $name . ' headshot'
										));
        echo '</summary>';

        echo '<div class="bio details-content">' ;
        if ( $revealheadshot && has_post_thumbnail($musician_id) )
			echo get_the_post_thumbnail(
										$musician_id, 'thumbnail', 
										array(
											'class' => 'headshot floatleft',
											'title' => $name . ' headshot',
											'alt'   => $name . ' headshot'
										));
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

/**
 * Display a flat list of all instruments sorted by your chosen numeric order.
 *
 * @return string HTML output of the flat list.
 */
function bc_instruments_list() 
{
	// 1. Fetch all terms inside the instrument taxonomy using the framework's order engine
	$terms = get_terms( [
		'taxonomy'   => 'instrument',
		'hide_empty' => false, 
		'orderby'    => 'order', // Instructs the order framework to handle sorting
		'order'      => 'ASC'
	] );

	if ( is_wp_error( $terms ) || empty( $terms ) )
		return '<p class="no-instruments">No instruments found.</p>';

	// 2. Fail-safe fallback sort (In case frontend query filters are bypassed)
	usort( $terms, function( $a, $b ) 
	{
		$order_a = (int) get_term_meta( $a->term_id, 'order', true );
		$order_b = (int) get_term_meta( $b->term_id, 'order', true );
		
		if ( $order_a === $order_b )
			return strcmp( $a->name, $b->name );
		
		return $order_a <=> $order_b;
	} );

	// 3. Render out the layout loop
	$output = '<h2>instruments by display order</h2>';
	$output .= '<ul class="flat-instruments-list">';
	foreach ( $terms as $term ) 
	{
		$term_order = (int) get_term_meta( $term->term_id, 'order', true );
		
		$output .= sprintf(
			'<li class="instrument-item item-%s" data-order="%d">%s</li>',
			esc_attr( $term->slug ),
			$term_order,
			esc_html( $term->name )
		);
	}
	$output .= '</ul>';

	return $output;
}
?>