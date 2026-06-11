<?php
/**
 * 1. REGISTER THE "CASTS" CUSTOM POST TYPE
 */
function register_casts_custom_post_type() {
    $labels = [
        "name"               => esc_html__( "Casts", "custom-text-domain" ),
        "singular_name"      => esc_html__( "Cast", "custom-text-domain" ),
        "menu_name"          => esc_html__( "Casts", "custom-text-domain" ),
        "all_items"          => esc_html__( "All Casts", "custom-text-domain" ),
        "add_new"            => esc_html__( "Add New Cast", "custom-text-domain" ),
        "add_new_item"       => esc_html__( "Add New Cast", "custom-text-domain" ),
        "edit_item"          => esc_html__( "Edit Cast", "custom-text-domain" ),
        "new_item"           => esc_html__( "New Cast", "custom-text-domain" ),
        "view_item"          => esc_html__( "View Cast", "custom-text-domain" ),
        "search_items"       => esc_html__( "Search Casts", "custom-text-domain" ),
        "not_found"          => esc_html__( "No Casts found", "custom-text-domain" ),
        "not_found_in_trash" => esc_html__( "No Casts found in trash", "custom-text-domain" ),
    ];

    $args = [
        "label"               => esc_html__( "Casts", "custom-text-domain" ),
        "labels"              => $labels,
        "public"              => false, // Kept internal/admin-facing by default
        "show_ui"             => true,
        "show_in_menu"        => true,
        "capability_type"     => "post",
        "hierarchical"        => false,
        "rewrite"             => [ "slug" => "cast", "with_front" => true ],
        "query_var"           => true,
        "supports"            => [ "title" ],
        "show_in_rest"        => false,
    ];

    register_post_type( "cast", $args );
}
add_action( 'init', 'register_casts_custom_post_type' );

/**
 * 2. CAST CONFIGURATION META BOX (Musicians + Subsets of Instruments)
 */

function render_cast_meta_box_callback( $post ) 
{
	wp_nonce_field( 'save_cast_meta_action', 'cast_meta_box_nonce' );

	// Fetch just the clean, flat instruments array: [musician_id => [instrument_ids]]
	$saved_instruments = get_post_meta( $post->ID, '_cast_data', true ) ?: [];

	// Derive the active musicians instantly using your array_keys idea
	$saved_musicians = array_keys( $saved_instruments );

	$musicians = get_posts([
		'post_type'   => 'musician',
		'post_status' => 'publish',
		'orderby'     => 'title',
		'order'       => 'ASC',
		'posts_per_page' => -1
	]);

	echo '<div class="cast-meta-box-container">';
	if ( ! empty( $musicians ) ) 
	{
		foreach ( $musicians as $musician ) 
		{
			$terms = wp_get_object_terms( $musician->ID, 'instrument' );
			
			echo '<div class="musician-cast-row" style="margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #eee;">';
				// Master Musician Checkbox
				$musician_checked = in_array( $musician->ID, $saved_musicians ) ? 'checked' : '';
				echo '<label style="font-weight: bold; display: block; margin-bottom: 5px;">';
					echo '<input type="checkbox" name="cast_musicians[]" class="musician-toggle" value="' . $musician->ID . '" ' . $musician_checked . '> ';
					echo esc_html( $musician->post_title );
				echo '</label>';

				// Sub-list of Instruments
				if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) 
				{
					echo '<div class="instruments-sub-list" style="margin-left: 25px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 5px;">';
					foreach ( $terms as $term ) 
					{
						$inst_checked = ( isset( $saved_instruments[ $musician->ID ] ) 
										&& in_array( $term->term_id, $saved_instruments[ $musician->ID ] ) ) 
										? 'checked' : '';
						echo '<label style="font-size: 12px; color: #555;">';
							echo '<input type="checkbox" name="cast_instruments[' . $musician->ID 
										. '][]" class="instrument-toggle" value="' . $term->term_id . '" ' 
										. $inst_checked . '> ';
							echo esc_html( $term->name );
						echo '</label>';
					}
					echo '</div>';
				}
			echo '</div>';
		}
	}
	echo '</div>';

	add_action( 'admin_print_footer_scripts', 'inject_cast_meta_box_auto_toggle_js' );
}
/**
 * Register the Custom Cast Meta Box.
 */
function register_cast_meta_box() {
	add_meta_box(
		'cast_meta_box_id',                  // Unique ID for the meta box container HTML
		__( 'Cast Members & Instruments', 'text-domain' ), // Visible Title of the meta box
		'render_cast_meta_box_callback',     // CRITICAL: The exact name of your callback function
		'cast',                              // CRITICAL: Must match the exact CPT slug of your Casts post type
		'normal',                            // Context: where it appears ('normal', 'side', 'advanced')
		'high'                               // Priority: how high up the page it loads
	);
}
// Hook the registration execution loop into the WordPress backend admin pool
add_action( 'add_meta_boxes', 'register_cast_meta_box' );

/**
 * Inject jQuery automation handlers cleanly into the WordPress admin footer pool.
 */
function inject_cast_meta_box_auto_toggle_js() 
{
	// Verify we are working on the proper screen context matrix
	$screen = get_current_screen();
	if ( ! $screen || $screen->post_type !== 'cast' ) {
		return;
	}
	?>
	<script id=cast_meta_box_auto_toggle_js type="text/javascript">
		jQuery(document).ready(function($) {
			
			// 1. DIRECTION A: Selecting instruments selects/unselects the musician row automatically
			$('.cast-meta-box-container').on('change', '.instrument-toggle', function() {
				var $row = $(this).closest('.musician-cast-row');
				var $musicianCheckbox = $row.find('.musician-toggle');
				
				// Count how many instruments are currently checked inside this specific row context
				var checkedInstrumentsCount = $row.find('.instrument-toggle:checked').length;
				
				if (checkedInstrumentsCount > 0) {
					// If at least one instrument is selected, ensure the parent musician stays checked
					$musicianCheckbox.prop('checked', true);
				} else {
					// If all instruments are cleared out, completely uncheck the parent musician
					$musicianCheckbox.prop('checked', false);
				}
			});

			// 2. DIRECTION B: (UX Safeguard) If manually unchecking a musician, clear all their sub-selections
			$('.cast-meta-box-container').on('change', '.musician-toggle', function() {
				var $row = $(this).closest('.musician-cast-row');
				
				// If a content administrator turns off a musician entirely, clear down all sub-toggles
				if (!this.checked) {
					$row.find('.instrument-toggle').prop('checked', false);
				}
			});
			
		});
	</script>
<?php
}

function save_cast_meta_data_handler( $post_id ) 
{
	if ( ! isset( $_POST['cast_meta_box_nonce'] ) 
		|| ! wp_verify_nonce( $_POST['cast_meta_box_nonce'], 'save_cast_meta_action' ) ) 
		return;

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		return;

	if ( ! current_user_can( 'edit_post', $post_id ) ) 
		return;

	$clean_cast_data = [];

	// Loop strictly through the instrument arrays
	if ( isset( $_POST['cast_instruments'] ) && is_array( $_POST['cast_instruments'] ) ) 
	{
		foreach ( $_POST['cast_instruments'] as $musician_id => $instrument_ids ) 
		{
			if ( is_array( $instrument_ids ) && ! empty( $instrument_ids ) ) 
			{
				// Only save rows that actually contain checked instruments
				$clean_cast_data[ intval( $musician_id ) ] = array_map( 'intval', $instrument_ids );
			}
		}
	}

	// Update or prune database entries
	if ( ! empty( $clean_cast_data ) ) 
		update_post_meta( $post_id, '_cast_data', $clean_cast_data );
	else
		delete_post_meta( $post_id, '_cast_data' );
}
add_action( 'save_post_cast', 'save_cast_meta_data_handler' );

/**
 * Generate a human-readable title for a GigPress show from its ID.
 *
 * @param int $show_id The GigPress show ID.
 * @return string The constructed show title (e.g., "Artist Name @ Venue Name (June 4, 2026)").
 */
function show_title($show)
{
	    // Assemble the parts into a clean display title
    // Format the date using your WordPress site's local date configuration
    $formatted_date = date_i18n( get_option( 'date_format' ), strtotime( $show->show_date ) );
    return sprintf(
        '<span class=show-title>%s</span>'
        . ' <span class=prog-title>%s</span>'
        . ' <span class=venue-name>%s</span>',
        $show->artist_name,
        $show->venue_name,
        $formatted_date
    );
}

function bc_list_upcoming_casts_shortcode( $atts, $content=null ) 
{
    $atts = mcpt_query_atts( // url query params
                shortcode_atts( 
                    array(
                        'show_id'    => 0 // display musicians/instr in a show's cast 
                    	),
                    $atts, 'cast_list' ));
    $show_id = intval(sanitize_text_field( $atts['show_id'] ));
    if ($show_id > 0)
    	return bc_musician_list($show_id, 1, $content);

    global $wpdb;
    // Query the show details along with its corresponding artist and venue
    $shows = $wpdb->get_results( 
    			$wpdb->prepare(
			        "SELECT s.show_id, s.cast_id, s.assist_id, s.show_date, a.artist_name, v.venue_name
				         FROM " . GIGPRESS_SHOWS . " AS s"
					         . " LEFT JOIN " . GIGPRESS_ARTISTS . " AS a ON s.show_artist_id = a.artist_id"
						         . " LEFT JOIN " . GIGPRESS_VENUES . " AS v ON s.show_venue_id = v.venue_id"
							       . " WHERE s.show_expire >= '" . GIGPRESS_NOW . "'"
									 . " AND s.show_status != 'deleted'"
								       . " AND s.cast_id > 0" 
							.' ORDER BY s.show_date ASC;'
						    	) );
    ob_start();
    echo $content;
	echo "<div class=upcoming-casts>";
	
	if (! $shows )
	    echo "-- no casts set --";
	else
	{
	    $previous_show = (object) ['artist_name' => '', 'cast_id' => 0];

	    foreach($shows as $show)
	    {
	        if( $previous_show->artist_name == $show->artist_name) 
	            $show->artist_name = '';

	        echo "<h2" . (empty($show->artist_name) 
	                   		? " class=same-prog" : "") . ">";
	        echo "<a href='/about/company-collaborators/this-seasons-casts/?show_id=" . $show->show_id . "'>";
	        echo show_title($show) . "</a></h2>";

	        if( $show->artist_name == '')
	            $show->artist_name = $previous_show->artist_name;

	        $previous_show = $show;	
		}
	}
	echo "</div>";
	return ob_get_clean();
}
add_shortcode( 'upcoming_casts', 'bc_list_upcoming_casts_shortcode' );

function get_gigpress_show_title_cast_ids( $show_id ) 
{
	if( ! $show_id )
    	return ['', 0, 0];

    global $wpdb;
    // Query the show details along with its corresponding artist and venue
    $show = $wpdb->get_row( 
    			$wpdb->prepare(
			        "SELECT s.show_date, s.cast_id, s.assist_id, a.artist_name, v.venue_name
				         FROM " . GIGPRESS_SHOWS . " AS s"
					         . " LEFT JOIN " . GIGPRESS_ARTISTS . " AS a ON s.show_artist_id = a.artist_id"
						         . " LEFT JOIN " . GIGPRESS_VENUES . " AS v ON s.show_venue_id = v.venue_id"
							         . " WHERE s.show_id = %d",
							        intval( $show_id )
					      ) );
    return [show_title($show), intval( $show->cast_id ), intval( $show->assist_id )];
}

function get_gigpress_show_cast_data( $show_id ): array
{
	[$show_title, $cast_id, $assist_id] = get_gigpress_show_title_cast_ids($show_id);
	$cast_data   = get_gigpress_cast_data( $cast_id );
	$assist_data = get_gigpress_cast_data( $assist_id );

	return [$show_title, $cast_data, $assist_data];
}

function get_gigpress_cast_data( $cast_id ): array
{
	if ($cast_id > 0) 
	{
	    $cast_title   = get_the_title($cast_id);
	    $instruments  = get_post_meta($cast_id, '_cast_data', true);

      	return [$cast_title, $instruments];
	}
	return ['', [] ];
}

function get_cast_instruments_string($instrument_ids)
{
	$instruments = [];
	if ( ! empty( $instrument_ids ) ) 
	{
		foreach ( $instrument_ids as $term_id )
		{
			$term = get_term( $term_id, 'instrument' );
			if ( $term && ! is_wp_error( $term ) )
			    if( $term->order )
				    $instruments[$term->order] = $term->name;
				else
				    $instruments[] = $term->name;
		}
		ksort($instruments, SORT_NUMERIC);
		return esc_html( implode( ', ', $instruments ) );
	}
}
?>