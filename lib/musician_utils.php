<?php

// musician cpt utils

function mcpt_add_query_vars($qVars) //  shortcode atts are lowercase
{
        $qVars[] = "show_id";
        $qVars[] = "program_id";
        $qVars[] = 'revealheadshot';
        $qVars[] = 'list_instruments';
        $qVars[] = 'past';
        $qVars[] = 'allow_initial_desktop_expand';
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
                        'program_id' => 0, // display musicians/instr in a show's cast 
                        'past' => false,
                        'revealheadshot' => false,
                        'list_instruments' => false,
                        'allow_initial_desktop_expand' => false,
                    ),
                    $atts, 'musician_list' ));

    if( (bool) ($atts['list_instruments'] ?? false))
        return bc_instruments_list();

	$show_id                      = intval(strtolower(sanitize_text_field( $atts['show_id'] )));
	$program_id                   = intval(strtolower(sanitize_text_field( $atts['program_id'] )));
	$past                         = intval(strtolower(sanitize_text_field( $atts['past'] )));
	$revealheadshot               = (bool) ($atts['revealheadshot'] ?? false);
	$allow_initial_desktop_expand = (bool) ($atts['allow_initial_desktop_expand'] ?? false);

	return $content . bc_musician_list( $show_id, $past, $revealheadshot, $allow_initial_desktop_expand, $content );
}
add_shortcode( 'musician_list', 'bc_musician_list_shortcode' );

function bc_musician_list( $show_id, $past, $revealheadshot, $allow_initial_desktop_expand, $content ) 
{
    $post_status = array('publish');
    if ($past > 1)
        $post_status = array('pending');  // show only pending==not active
    else if ($past) // pending==not active, but still might be in a past cast
        $post_status[] = 'pending';  // show both, as some still might be in a past cast

    $args = array(
                    'post_type'      => 'musician',
                    'post_status'    =>  $post_status,
                    'posts_per_page' => -1
                 );

    ob_start();

    echo '<div class=musician-list>';

	if ( $show_id > 0 )
	{
		[$show_title, $program_id, $cast_data, $assist_data]
		    = get_gigpress_show_cast_data($show_id, $past);

		$cast_title  = $cast_data[0];
		$instruments = $cast_data[1]; // Holds the ordered list of instruments by cast member
		if ( empty($instruments) )
		{
		    echo "<br><div class=ctr>no cast assigned to this" 
		                . ($past ? " past" : '') . " performance of "
		            . "<h2 class='show-title' id='prog-$program_id'>"
		                . "<a href='/performances/?condensed=0&program_id=" . $program_id . "'"
		                        . " title='click to view upcoming performances'" 
		                        . "  alt='click to view upcoming performances'" . " >"
		                    . $show_title . "</a></h2></div>";
            echo '</div>';
            return ob_get_clean();
		}

		echo "<h2 class='cast-title' id='show-" . $show_id . "'"
		        . " title='click to view programs&#39;s description'" 
		         . "  alt='click to view programs&#39;s description'" . " >"; 
		    echo "<a href=/performances/"
			            		. ($past 
			            			? "past-performances/" 
			            			: '')
		    	           	. "?condensed=0&program_id=" . $program_id
			    	           . "&show_id="    . $show_id . "'>";
		        echo $show_title;
		echo "</a></h2><hr>";

		// CRITICAL: Force WordPress to output elements in array key order
		$args['post__in'] = array_keys($instruments);
		$args['orderby']  = 'post__in';
		generate_musician_list($args, $instruments, $revealheadshot);

		$cast_title  = $assist_data[0];
		$instruments = $assist_data[1];
		if ( ! empty($instruments) )
		{
			echo "<p title='" . $cast_title. "' >Assisted by:</p>";
			$args['post__in'] = array_keys($instruments);
			$args['orderby']  = 'post__in';
			generate_musician_list($args, $instruments, $revealheadshot);
		}
	}
	else
	{
		// Fall back to alpha sort if executing a blind loop without an assigned Cast ID context
		$args['orderby'] = 'title';
		$args['order']   = 'ASC';
		generate_musician_list($args, [], $revealheadshot);
	}

    echo '</div>';
        
include GIGPRESS_PLUGIN_DIR . '/scripts/toggle-details.js';

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
							trim(get_field( 'first_name', $musician_id ) . ' ' 
								. get_field( 'last_name',  $musician_id )));

		echo '<details class="cast-member-row"' . ($revealheadshot ? "" : " open") . '>';
		echo '<summary><div class="cast-header">';
	    echo '<h2>' . esc_html($name) . '</h2>';
	    echo '<div class="instruments">';
	    
	    if ( empty($instruments) )
	    {
			echo strip_tags( // remove links
					get_the_term_list( // alphabetical order
						$musician_id, 'instrument', '', ', ' , ''));
 		}
 		else
		{
			// Extract just the nested instruments in order for this particular row item
			$musician_cast_meta   = isset($instruments[$musician_id]) 
										? $instruments[$musician_id] : [];
			$musician_instruments = isset($musician_cast_meta['instruments']) 
										? $musician_cast_meta['instruments'] : [];
			echo get_cast_instruments_string($musician_instruments);
		}
        echo '</div>';
        echo '</div>';
        echo '</summary>';
        echo '<div class="bio details-content">' ;
        
        if ( has_post_thumbnail($musician_id) )
			echo get_the_post_thumbnail($musician_id, 'thumbnail', array(
				'class' => 'headshot floatleft',
				'title' => $name . ' headshot',
				'alt'   => $name . ' headshot'
			));
		else
			echo '<img src="/2021site/wp-content/uploads/2026/06/Headshot-Silhouette-Default-150x150.jpeg" alt="Headshot Silhouette Default" class="headshot floatleft" />';
			
		echo apply_filters( 'the_content', $musician->post_content );
		
		echo '<br clear=left></div>';
        echo '</details></div>';
        if ( ! $revealheadshot)
            echo '<hr>';
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
        'ID'         => $musician_id,
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
/**
 * Intercept and block the deletion of an Instrument term if it is actively linked 
 * to a musician profile or embedded inside a Cast's metadata assigned_instruments. 
 */
function bc_prevent_active_instrument_deletion( $term_id, $taxonomy ) 
{
	if ( $taxonomy !== 'instrument' )
		return $term_id;

	// 1. Check if the instrument is actively assigned to any musician posts via core taxonomy relations
	$assigned_musicians = get_objects_in_term( $term_id, 'instrument' );
	if ( ! empty( $assigned_musicians ) && ! is_wp_error( $assigned_musicians ) ) 
	{
		return new WP_Error(
			'instrument_in_use_by_musician',
			__( '<strong>Deletion Blocked:</strong> This instrument is assigned to one or more musicians.', 'custom-text-domain' )
		);
	}

	// 2. Check if the instrument is stored inside any Cast meta arrays
	global $wpdb;
	$all_casts_meta = $wpdb->get_col( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_cast_data'" );
	
	if ( ! empty( $all_casts_meta ) ) 
	{
		foreach ( $all_casts_meta as $meta_value ) 
		{
			$cast_data = maybe_unserialize( $meta_value );
			if ( is_array( $cast_data ) ) 
			{
				foreach ( $cast_data as $musician_id => $assigned_instruments ) 
				{
					if ( isset( $assigned_instruments['instruments'] ) 
						&& is_array( $assigned_instruments['instruments'] ) ) 
					{
						if ( array_key_exists( $term_id, $assigned_instruments['instruments'] ) ) 
						{
							return new WP_Error(
								'instrument_in_use_by_cast',
								__( '<strong>Deletion Blocked:</strong> This instrument is assigned to a musician in a cast.', 'custom-text-domain' )
							);
						}
					}
				}
			}
		}
	}

	return $term_id;
}
add_filter( 'pre_delete_term', 'bc_prevent_active_instrument_deletion', 10, 2 );

?>