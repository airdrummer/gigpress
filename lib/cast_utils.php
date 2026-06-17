<?php
/**
 * 1. REGISTER THE "CASTS" CUSTOM POST TYPE
 */
function register_casts_custom_post_type() {
    $labels = [
        "name"               => esc_html__( "Casts", "boston-camerata" ),
        "singular_name"      => esc_html__( "Cast", "boston-camerata" ),
        "menu_name"          => esc_html__( "Casts", "boston-camerata" ),
        "all_items"          => esc_html__( "All Casts", "boston-camerata" ),
        "add_new"            => esc_html__( "Add New Cast", "boston-camerata" ),
        "add_new_item"       => esc_html__( "Add New Cast", "boston-camerata" ),
        "edit_item"          => esc_html__( "Edit Cast", "boston-camerata" ),
        "new_item"           => esc_html__( "New Cast", "boston-camerata" ),
        "view_item"          => esc_html__( "View Cast", "boston-camerata" ),
        "search_items"       => esc_html__( "Search Casts", "boston-camerata" ),
        "not_found"          => esc_html__( "No Casts found", "boston-camerata" ),
        "not_found_in_trash" => esc_html__( "No Casts found in trash", "boston-camerata" ),
    ];

    $args = [
        "label"               => esc_html__( "Casts", "boston-camerata" ),
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
 * 2. CAST CONFIGURATION META BOX (Musicians + Subsets of Instruments with Custom Orders)
 */
function render_cast_meta_box_callback( $post ) 
{
	wp_nonce_field( 'save_cast_meta_action', 'cast_meta_box_nonce' );

	// Fetch the updated structured cast meta array
	$saved_cast_data = get_post_meta( $post->ID, '_cast_data', true ) ?: [];
	$saved_musicians = array_keys( $saved_cast_data );

	$musicians = get_posts([
		'post_type'      => 'musician',
		'post_status'    => 'publish',
		'orderby'        => 'title',
		'order'          => 'ASC',
		'posts_per_page' => -1
	]);

	echo '<div class="cast-meta-box-container">';
	if ( ! empty( $musicians ) ) 
	{
		foreach ( $musicians as $musician ) 
		{
			$terms = wp_get_object_terms( $musician->ID, 'instrument' );
			$musician_meta = isset( $saved_cast_data[ $musician->ID ] ) ? $saved_cast_data[ $musician->ID ] : [];
			$musician_order = isset( $musician_meta['musician_order'] ) ? intval( $musician_meta['musician_order'] ) : 0;
			$saved_inst_weights = isset( $musician_meta['instruments'] ) ? $musician_meta['instruments'] : [];
			
			echo '<div class="musician-cast-row" style="margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #eee;">';
				
				// Master Musician Checkbox + Order Input
				$musician_checked = in_array( $musician->ID, $saved_musicians ) ? 'checked' : '';
				echo '<div style="display: flex; align-items: center; gap: 15px; margin-bottom: 8px;">';
					echo '<label style="font-weight: bold; cursor: pointer;">';
						echo '<input type="checkbox" name="cast_musicians[]" class="musician-toggle" value="' . $musician->ID . '" ' . $musician_checked . '> ';
						echo esc_html( $musician->post_title );
					echo '</label>';
					echo '<label style="font-size: 12px; color: #666;">Musician Display Order: ';
						echo '<input type="number" name="cast_musician_order[' . $musician->ID . ']" value="' . $musician_order . '" style="width: 60px; padding: 2px 4px; height: 24px; text-align: center;">';
					echo '</label>';
				echo '</div>';

				// Sub-list of Instruments + Individual Order Weights
				if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) 
				{
					echo '<div class="instruments-sub-list" style="margin-left: 25px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">';
					foreach ( $terms as $term ) 
					{
						$inst_checked = isset( $saved_inst_weights[ $term->term_id ] ) ? 'checked' : '';
						$inst_order = isset( $saved_inst_weights[ $term->term_id ] ) ? intval( $saved_inst_weights[ $term->term_id ] ) : 0;
						
						echo '<div style="display: flex; align-items: center; justify-content: space-between; background: #fafafa; padding: 4px 8px; border: 1px solid #e2e8f0; border-radius: 3px;">';
							echo '<label style="font-size: 12px; color: #555; cursor: pointer; flex-grow: 1; display: inline-block;">';
								echo '<input type="checkbox" name="cast_instruments[' . $musician->ID . '][]" class="instrument-toggle" value="' . $term->term_id . '" ' . $inst_checked . '> ';
								echo esc_html( $term->name );
							echo '</label>';
							echo '<input type="number" name="cast_instrument_order[' . $musician->ID . '][' . $term->term_id . ']" value="' . $inst_order . '" placeholder="0" style="width: 45px; font-size: 11px; padding: 1px 3px; height: 20px; text-align: center;">';
						echo '</div>';
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

	// Parse custom post matrices based on active primary checkboxes
	if ( isset( $_POST['cast_musicians'] ) && is_array( $_POST['cast_musicians'] ) ) 
	{
		foreach ( $_POST['cast_musicians'] as $musician_id ) 
		{
			$m_id = intval( $musician_id );
			$m_order = isset( $_POST['cast_musician_order'][ $m_id ] ) ? intval( $_POST['cast_musician_order'][ $m_id ] ) : 0;
			
			$m_instruments = [];
			if ( isset( $_POST['cast_instruments'][ $m_id ] ) && is_array( $_POST['cast_instruments'][ $m_id ] ) ) 
			{
				foreach ( $_POST['cast_instruments'][ $m_id ] as $term_id ) 
				{
					$t_id = intval( $term_id );
					$t_order = isset( $_POST['cast_instrument_order'][ $m_id ][ $t_id ] ) ? intval( $_POST['cast_instrument_order'][ $m_id ][ $t_id ] ) : 0;
					$m_instruments[ $t_id ] = $t_order;
				}
				// Pre-sort internal instruments by context order weight
				asort( $m_instruments, SORT_NUMERIC );
			}

			$clean_cast_data[ $m_id ] = [
				'musician_order' => $m_order,
				'instruments'    => $m_instruments
			];
		}

		// Sort structural musician parent nodes by their weight values
		uasort( $clean_cast_data, function( $a, $b ) {
			return $a['musician_order'] <=> $b['musician_order'];
		});
	}

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
        . ' <span class=show-venue>%s</span>'
        . ' <span class=show-date>%s</span>',
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
                        'show_id' => 0, // display musicians/instr in a show's cast 
                        'past'    => false, // display past casts (in mcpt_add_query_vars)
                    	),
                    $atts, 'cast_list' ));
 
    $past  = (bool) ($atts['past'] ?? false);
	$what  = ( $past ? "this season&#39;s" : "past seasons&#39;") . " casts";
    $where = sanitize_title($what); //-hardcoded path-\

    $show_id = intval(sanitize_text_field( $atts['show_id'] ));

    if ($show_id > 0)
    {
	    $click_what =  "click to display this performance&#39;s " . $what; // for title links

    	return "<div class='top-viewall'><a href='/about/company-collaborators/". $where
                                . "/?show_id="   . $show_id . "'"
                    			. " title='$click_what'"
                    			. "   alt='$click_what'" . " >"
                    			. "<button class=viewall>$what</button>"
                        	. "</a>"
                    . "<h3 class='gig-pup'><br>Of " 
                        . ($show_id ? 'An ' : '') . ($scope != 'past' ? "Upcoming" : "Past")
                            . " performance" . ($show_id ? '' : 's') . " of this program</h3>"
            	."</div>"
            	. bc_musician_list($show_id, $past, true, false, $content);
    }

    global $wpdb;

    // Query the show details along with its corresponding artist and venue
    $shows = $wpdb->get_results( 
    			$wpdb->prepare(
			        "SELECT s.show_id, s.cast_id, s.assist_id, s.show_date, a.artist_id, a.artist_name, v.venue_name
				         FROM " . GIGPRESS_SHOWS . " AS s"
					         . " LEFT JOIN " . GIGPRESS_ARTISTS . " AS a ON s.show_artist_id = a.artist_id"
						         . " LEFT JOIN " . GIGPRESS_VENUES . " AS v ON s.show_venue_id = v.venue_id"
                                    . " WHERE s.show_status != 'deleted' AND s.cast_id > 0"
                                        . " AND s.show_expire " . ($past ? "<" : ">=") . " '" . GIGPRESS_NOW . "'"
							. ' ORDER BY s.show_date ' . ($past ? "DESC" : "ASC") . ';'
						) );
    ob_start();

    echo  "<div class='top-viewall'><a href='/about/company-collaborators/"
                        . sanitize_title($what) . "'"
                        . " title='click to display $what'"
            			. "  alt='click to display $what'" . " >"
            			. "<button class='viewall'>$what</button>"
                . "</a></div>";

	echo "<div class=" . ($past ? "past" : "upcoming") . "-casts>";
	
	if (! $shows )
	    echo "-- no casts assigned --";
	else
	{
	    $what = ( ! $past ? "this season&#39;s" : "past seasons&#39;") . " casts";
	    $previous_show = (object) ['artist_name' => '']; // initial for compare
	    $click_what =  "click to display this performance&#39;s cast"; // for title links

	    foreach($shows as $show)
	    {
	        if( $previous_show->artist_name == $show->artist_name) 
	            $show->artist_name = '';

            echo "<h2 class=" . (empty($show->artist_name)  ? "same-prog" : "next-prog") . ">";
		        echo "<a href='/about/company-collaborators/" . sanitize_title($what)
                        . "/?show_id="   . $show->show_id
                        . "&program_id=" . $show->artist_id . "'"
            			. " title='$click_what'"
            			. "   alt='$click_what'"
                    . " >";
                    echo show_title($show);
            echo "</a></h2>";

	        if( $show->artist_name == '')
	            $show->artist_name = $previous_show->artist_name;

	        $previous_show = $show;	
		}
	}
	echo "</div>";
	
	return ob_get_clean();
}
add_shortcode( 'upcoming_casts', 'bc_list_upcoming_casts_shortcode' );

function get_gigpress_show_title_cast_ids( $show_id, $past ) : array
{
	if( ! $show_id )
    	return ['', 0, 0, 0];

    global $wpdb;
    // Query the show details along with its corresponding artist and venue
    $show = $wpdb->get_row( 
    			$wpdb->prepare(
			        "SELECT s.show_date, s.cast_id, s.assist_id, a.artist_id, a.artist_name, v.venue_name
				         FROM " . GIGPRESS_SHOWS . " AS s"
					         . " LEFT JOIN " . GIGPRESS_ARTISTS . " AS a ON s.show_artist_id = a.artist_id"
						         . " LEFT JOIN " . GIGPRESS_VENUES . " AS v ON s.show_venue_id = v.venue_id"
							         . " WHERE s.show_id = %d"
                                        . " AND s.show_expire " . ($past ? "<" : ">=") . " '" . GIGPRESS_NOW . "'"
							. ' ORDER BY s.show_date ' . ($past ? "DESC" : "ASC") . ';',
							        intval( $show_id )
					      ) );
	if( ! $show )
    	return ['', 0, 0, 0];
    return [show_title($show), intval( $show->artist_id ), intval( $show->cast_id ), intval( $show->assist_id )];
}

function get_gigpress_show_cast_data( $show_id, $past ): array
{
	[$show_title, $program_id, $cast_id, $assist_id]
	            = get_gigpress_show_title_cast_ids($show_id, $past);
	$cast_data   = get_gigpress_cast_data( $cast_id );
	$assist_data = get_gigpress_cast_data( $assist_id );

	return [$show_title, $program_id, $cast_data, $assist_data];
}

function get_gigpress_cast_data( $cast_id ): array
{
	if ( ! $cast_id ) 
	    return ['', [] ];

    $cast_title   = get_the_title($cast_id);
    $instruments  = get_post_meta($cast_id, '_cast_data', true);

  	return [$cast_title, $instruments];
}

function get_cast_instruments_string( $musician_instruments )
{
	if ( empty( $musician_instruments ) 
	   || ! is_array( $musician_instruments ) ) 
		return '';

	// Sort associative instruments array by custom saved order weights
	asort( $musician_instruments, SORT_NUMERIC );

	$instruments = [];
	foreach ( array_keys( $musician_instruments ) as $term_id )
	{
		$term = get_term( $term_id, 'instrument' );
		if ( $term && ! is_wp_error( $term ) ) {
			$instruments[] = $term->name;
		}
	}
	return esc_html( implode( ', ', $instruments ) );
}

/**
 * Enforce cascading data integrity across custom post types and internal relational tables. 
 */
function bc_enforce_relational_integrity_constraints( $post_id ) 
{
	// GUARDRAIL 1: Explicitly sanitize and verify we have a valid positive post ID
	$post_id = intval( $post_id );
	if ( $post_id <= 0 ) 
		return;

	$post_type = get_post_type( $post_id );

	// CONSTRAINT A: Prevent deletion of a Musician if they are pinned inside a Cast
	if ( $post_type === 'musician' ) 
	{
		global $wpdb;
		$casts_meta = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_cast_data'" );
		
		foreach ( $casts_meta as $meta ) 
		{
			$cast_data = maybe_unserialize( $meta->meta_value );
			if ( is_array( $cast_data ) && array_key_exists( $post_id, $cast_data ) ) 
			{
				$cast_title = get_the_title( $meta->post_id );
				wp_die( sprintf(
					__( '<h3>Deletion Blocked</h3>'
						. '<p>The musician <strong>%s</strong> cannot be deleted because they are  assigned to the Cast '
						. '<strong><a href="%s" target="_blank">%s</a></strong>.</p>'
						. '<p>Please edit that Cast and uncheck this musician before deleting them.</p>', 'boston-camerata' ),
					esc_html( get_the_title( $post_id ) ),
					esc_url( get_edit_post_link( $meta->post_id ) ),
					esc_html( $cast_title )
				), '', [ 'back_link' => true ] );
			}
		}
	}
	// CONSTRAINT B: Prevent deletion of a Cast post if linked to an active GigPress Show
	else if ( $post_type === 'cast' ) 
	{
		global $wpdb;
		if ( defined( 'GIGPRESS_SHOWS' ) ) 
		{
			// GUARDRAIL 2: Added cast_id > 0 condition to eliminate unassigned '0' index matching
			$linked_show_id = $wpdb->get_var( $wpdb->prepare(
				"SELECT show_id FROM " . GIGPRESS_SHOWS . " 
				 WHERE (cast_id = %d OR assist_id = %d) 
				   AND cast_id > 0 
				   AND show_status != 'deleted' 
				 LIMIT 1",
				$post_id,
				$post_id
			) );

			if ( $linked_show_id ) 
			{
				wp_die( sprintf(
					__( '<h3>Deletion Blocked</h3>'
						. '<p>The Cast <strong>%s</strong> cannot be removed because it is assigned to <a target=_blank href=/2021site/wp-admin/admin.php?page=gigpress&gpaction=edit&show_id=%d>Show #%d</a>.</p>'
						. '<p>Please remove that show cast assignment to allow cast deletion.</p>', 'boston-camerata' ),
					esc_html( get_the_title( $post_id ) ),
					intval( $linked_show_id )
				), '', [ 'back_link' => true ] );
			}
		}
	}
}
add_action( 'wp_trash_post', 'bc_enforce_relational_integrity_constraints', 10, 1 );
add_action( 'before_delete_post', 'bc_enforce_relational_integrity_constraints', 10, 1 );

/**
 * CONSTRAINT C: Prevent deletion of an Instrument taxonomy term if it is 
 * assigned to a Musician or inside a Cast musician assignment,
 * with graceful handling of AJAX admin requests.
 */
/**
 * Helper: Validates if an instrument is locked by active relational constraints.
 * * @param int $term_id The instrument term ID.
 * @return string|false Error message string if constraint violated, false otherwise.
 */
function bc_get_instrument_integrity_error( $term_id ) 
{
	$term_id = intval( $term_id );
	$term    = get_term( $term_id, 'instrument' );

	if ( is_wp_error( $term ) || ! $term ) 
		return false;

	// 1. Check standard counts (Musician profiles directly assigned this term)
	if ( $term->count > 0 ) 
		return sprintf(
			__( 'The instrument "%s" cannot be deleted because it is assigned to'
			. ' %d musician profile(s). Please update those performer assets first.', 'boston-camerata' ),
			esc_html($term->name),
			intval( $term->count )
		);

	// 2. Check nested matrices (Serialized layout arrays stored inside Cast postmeta)
	global $wpdb;
	$casts_meta = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_cast_data'" );

	foreach ( $casts_meta as $meta ) 
	{
		$cast_data = maybe_unserialize( $meta->meta_value );
		
		if ( is_array( $cast_data ) ) 
		{
			foreach ( $cast_data as $musician_id => $payload ) 
			{
				if ( isset( $payload['instruments'] ) && is_array( $payload['instruments'] ) ) 
				{
					if ( array_key_exists( $term_id, $payload['instruments'] ) ) 
					{
						$cast_title = get_the_title( $meta->post_id );
						return sprintf(
							__( 'The instrument "%s" cannot be deleted because it is assigned to a musician assigned to Cast "%s".'
							.' Please remove it from that cast first.', 'boston-camerata' ),
							esc_html($term->name),
							esc_html($cast_title)
						);
					}
				}
			}
		}
	}

	return false;
}

/**
 * Hook 1: UI Interceptor for the Instrument Taxonomy Table Rows
 * Rewrites the delete trigger to present a transparent alert instead of firing broken AJAX threads.
 */
add_filter( 'instrument_row_actions', 'bc_enforce_instrument_integrity_ui', 10, 2 );
function bc_enforce_instrument_integrity_ui( $actions, $tag ) 
{
	$error_message = bc_get_instrument_integrity_error( $tag->term_id );

	if ( $error_message ) 
	{
		// Strip HTML markers and escape for inline JS delivery
		$clean_message = esc_js( strip_tags( $error_message ) );

		// Overwrite the default delete link. 
		// Stripping the 'delete-tag' class isolates it from WordPress' core AJAX.
		$actions['delete'] = sprintf(
			'<a href="#" onclick="alert(\'%s\'); return false;" style="color:#dc3232;" aria-label="%s">%s</a>',
			$clean_message,
			esc_attr( sprintf( __( 'View restriction rules for %s' ), $tag->name ) ),
			__( 'Delete' )
		);
	}

	return $actions;
}
/**
 * Hook 2: Backend Fail-safe Protection Guard
 * Acts as an airtight enforcement barrier for Bulk Actions, direct API hits, or alternative workflows.
 */
add_action( 'pre_delete_term', 'bc_enforce_instrument_integrity_backend', 10, 2 );
function bc_enforce_instrument_integrity_backend( $term_id, $taxonomy ) 
{
	if ( 'instrument' !== $taxonomy ) 
		return;

	$error_message = bc_get_instrument_integrity_error( $term_id );

		// Bulk action deletions post back to a standard page refresh, allowing clean error rendering
	if ( $error_message )
		wp_die( wp_kses_post( $error_message ) );
}

/**
 * 1. REGISTER THE IMPORT SUBMENU UNDER CASTS
 */
add_action( 'admin_menu', 'bc_register_cast_import_submenu' );
function bc_register_cast_import_submenu() 
{
	add_submenu_page(
		'edit.php?post_type=cast',
		__( 'Import Cast from CSV', 'boston-camerata' ),
		__( 'Import Cast', 'boston-camerata' ),
		'manage_options',
		'cast-csv-import',
		'bc_render_cast_import_page'
	);
}

/**
 * 2. RENDER AND PROCESS THE IMPORT UTILITY
 */
function bc_render_cast_import_page() 
{
	if ( ! current_user_can( 'manage_options' ) )
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'boston-camerata' ) );

	$action           = isset( $_POST['import_action'] ) 
							? sanitize_text_field( $_POST['import_action'] ) : '';
	$show_upload_form = true;
	$notice_message   = '';
	$notice_type      = 'success';

	// STEP A: INITIAL FILE UPLOAD & PARSING
	if ( $action === 'upload_csv' ) 
	{
		check_admin_referer( 'bc_cast_csv_upload_action', 'bc_import_nonce' );

		if ( ! empty( $_FILES['cast_csv']['tmp_name'] ) ) 
		{
			$file = $_FILES['cast_csv']['tmp_name'];
			if ( ( $handle = fopen( $file, 'r' ) ) !== false ) 
			{
				$row_index      = 0;
				$cast_title     = '';
				$musicians_data = [];

				while ( ( $row = fgetcsv( $handle, 1000, ',' ) ) !== false ) 
				{
					if ( $row_index === 0 ) 
					{
						// Line 1 is the Cast Title
						$cast_title = isset( $row[0] ) ? trim( sanitize_text_field( $row[0] ) ) : '';
					} 
					else 
					{
						if ( empty( $row ) || empty( $row[0] ) ) 
							continue;

						$musicians_data[] = array_map( 'trim', $row );
					}
					$row_index++;
				}
				fclose( $handle );

				if ( empty( $cast_title ) ) 
				{
					$notice_message = __( 'Error: The CSV file is missing a Cast title on the first line.', 'boston-camerata' );
					$notice_type    = 'error';
				} 
				else 
				{
					// Check if a Cast with this exact title already exists
					$existing_cast = new WP_Query([
							'post_type'      => 'cast',
							'title'          => $cast_title,
							'posts_per_page' => 1,
							'post_status'    => 'any',
							'no_found_rows'  => true,
						]);

					if ( $existing_cast->have_posts() ) 
					{
						// STOP: Prompt user for replacement permission
						$show_upload_form = false;
						?>
						<div class="wrap">
							<h1><?php _e( 'Cast Already Exists', 'boston-camerata' ); ?></h1>
							<div class="notice notice-warning is-dismissible">
								<p><?php echo sprintf( __( 'A performance Cast titled <strong>"%s"</strong> already exists in the database.', 'boston-camerata' ), esc_html( $cast_title ) ); ?></p>
							</div>
							<p><?php _e( 'Would you like to overwrite this existing Cast configuration matrix with the incoming CSV datasets?', 'boston-camerata' ); ?></p>
							
							<form method="post" action="">
								<?php wp_nonce_field( 'bc_cast_csv_execute_action', 'bc_import_nonce' ); ?>
								<input type="hidden" name="import_action" value="execute_import">
								<input type="hidden" name="cast_title" value="<?php echo esc_attr( $cast_title ); ?>">
								<input type="hidden" name="serialized_musicians" value="<?php echo esc_attr( json_encode( $musicians_data ) ); ?>">
								
								<?php submit_button( __( 'Yes, Replace Existing Cast Data', 'boston-camerata' ), 'primary', 'submit_overwrite', false ); ?>
								<a href="<?php echo admin_url( 'edit.php?post_type=cast&page=cast-csv-import' ); ?>" class="button button-secondary"><?php _e( 'No, Cancel Import', 'boston-camerata' ); ?></a>
							</form>
						</div>
						<?php
						return;
					} 
					else 
					{
						// No naming collision found -> Execute straight away
						$notice_message   = bc_execute_cast_csv_import( $cast_title, $musicians_data );
						$show_upload_form = true;
					}
				}
			} 
			else 
			{
				$notice_message = __( 'Error: Failed to open the uploaded CSV file stream.', 'boston-camerata' );
				$notice_type    = 'error';
			}
		} 
		else 
		{
			$notice_message = __( 'Error: Please select a valid CSV file to upload.', 'boston-camerata' );
			$notice_type    = 'error';
		}
	}

	// STEP B: CONFIRMED OVERWRITE EXECUTION
	if ( $action === 'execute_import' ) 
	{
		check_admin_referer( 'bc_cast_csv_execute_action', 'bc_import_nonce' );

		$cast_title     = isset( $_POST['cast_title'] ) ? sanitize_text_field( $_POST['cast_title'] ) : '';
		$musicians_json = isset( $_POST['serialized_musicians'] ) ? wp_unslash( $_POST['serialized_musicians'] ) : '[]';
		$musicians_data = json_decode( $musicians_json, true );

		if ( ! empty( $cast_title ) && is_array( $musicians_data ) ) 
		{
			$notice_message = bc_execute_cast_csv_import( $cast_title, $musicians_data, true );
		} 
		else 
		{
			$notice_message = __( 'Error: Corrupted processing payload detected during conversion pass.', 'boston-camerata' );
			$notice_type    = 'error';
		}
	}

	// DISPLAY DEFAULT UPLOAD INTERFACE
	if ( $show_upload_form ) 
	{
		?>
		<div class="wrap">
			<h1><?php _e( 'Import Cast Configuration Matrix', 'boston-camerata' ); ?></h1>
			
			<?php if ( ! empty( $notice_message ) ) : ?>
				<div class="notice notice-<?php echo esc_attr( $notice_type ); ?> is-dismissible">
					<p><?php echo $notice_message; ?></p>
				</div>
			<?php endif; ?>

			<div class="card" style="max-width: 600px; margin-top: 20px;">
				<h2><?php _e( 'Upload CSV Roster File', 'boston-camerata' ); ?></h2>
				<p class="description">
					<?php _e( 'The CSV formatting parameters require line 1 to contain the <strong>Cast Title</strong>. Subsequent rows must be structured as: <code>First Name, Last Name, Instrument 1, Instrument 2, ...</code>', 'boston-camerata' ); ?>
				</p>
				<hr>
				<form method="post" action="" enctype="multipart/form-data">
					<?php wp_nonce_field( 'bc_cast_csv_upload_action', 'bc_import_nonce' ); ?>
					<input type="hidden" name="import_action" value="upload_csv">
					
					<p>
						<label Welfare for="cast_csv"><strong><?php _e( 'Choose CSV File:', 'boston-camerata' ); ?></strong></label><br><br>
						<input type="file" name="cast_csv" id="cast_csv" accept=".csv" required>
					</p>
					
					<?php submit_button( __( 'Process and Parse Cast File', 'boston-camerata' ), 'primary' ); ?>
				</form>
			</div>
		</div>
		<?php
	}
}

/**
 * 3. CORE LOGIC ENGINE TO DATABASE ENTRIES
 */
function bc_execute_cast_csv_import( $cast_title, $musicians_data, $overwrite = false ) 
{
	$cast_id = 0;
	// Resolve or spawn target Cast Post object
	if ( $overwrite ) 
	{
		$existing_cast = new WP_Query(
								[
									'post_type'      => 'cast',
									'title'          => $cast_title,
									'posts_per_page' => 1,
									'post_status'    => 'any',
									'no_found_rows'  => true,
								]);
		if ( $existing_cast->have_posts() )
			$cast_id = $existing_cast->posts[0]->ID;
	}

	if ( empty( $cast_id ) ) 
		$cast_id = wp_insert_post([
							'post_title'  => $cast_title,
							'post_type'   => 'cast',
							'post_status' => 'publish',
							]);

	if ( is_wp_error( $cast_id ) || ! $cast_id )
		return sprintf( __( 'Critical Failure: Unable to establish an initialization target for Cast "%s".', 'boston-camerata' ), esc_html( $cast_title ) );

	$clean_cast_data   = [];
	$musician_order    = 0;
	$musicians_added   = 0;
	$instruments_added = 0;

	// Loop through rows: subsequent lines containing musicians, instruments
	foreach ( $musicians_data as $row ) 
	{
		if ( count( $row ) < 2 ) 
			continue;

		$first_name = trim(sanitize_text_field( $row[0] ));
		$last_name  = trim(sanitize_text_field( $row[1] ));
		if ( empty( $first_name ) && empty( $last_name ) ) 
			continue;

		// Calculate standardized title pattern managed by your custom title generator
		$musician_title = str_replace( ", &nbsp;", "", "$last_name, $first_name" ); // kludge to allow boston singers to sort & display in alpha order

		// Check if Musician exists, create if missing
		$musician_query = new WP_Query(
									[
										'post_type'      => 'musician',
										'title'          => $musician_title,
										'posts_per_page' => 1,
										'post_status'    => 'any',
										'no_found_rows'  => true,
									]);

		if ( $musician_query->have_posts() ) 
			$musician_id = $musician_query->posts[0]->ID;
		else 
		{
			$musician_id = wp_insert_post(
									[
										'post_title'  => $musician_title,
										'post_type'   => 'musician',
										'post_status' => 'publish',
									]);

			// Populate custom fields safely, supporting ACF integrations
			if ( ! is_wp_error( $musician_id ) ) 
			{
				update_post_meta( $musician_id, 'first_name', $first_name );
				update_post_meta( $musician_id, 'last_name', $last_name );
				++$musicians_added;
			}
		}

		if ( is_wp_error( $musician_id ) || ! $musician_id )
			continue;

		// Extract custom instruments parameters (columns index 2 and onward)
		$instruments_input   = array_slice( $row, 2 );
		$assigned_instrument = [];
		$instrument_order    = 0;
		$core_taxonomy_terms = [];

		foreach ( $instruments_input as $instrument_name ) 
		{
			$instrument_name = sanitize_text_field( trim( $instrument_name ) );
			if ( empty( $instrument_name ) ) 
				continue;

			// Check if taxonomy term exists, create if missing
			$term = term_exists( $instrument_name, 'instrument' );
			if ( ! $term )
			{
				$term = wp_insert_term( $instrument_name, 'instrument' );
				++$instruments_added;
			}

			if ( ! is_wp_error( $term ) && isset( $term['term_id'] ) ) 
			{
				$term_id = intval( $term['term_id'] );
				$assigned_instrument[ $term_id ] = $instrument_order;
				$core_taxonomy_terms[]           = $term_id;
				$instrument_order               += 10;
			}
		}

		// Sync Core Taxonomy associations to Musician profile target
		if ( ! empty( $core_taxonomy_terms ) ) 
			wp_set_object_terms( $musician_id, $core_taxonomy_terms, 'instrument', true );

		// Append matrix settings into relational meta array
		$clean_cast_data[ $musician_id ] = [
								'musician_order' => $musician_order,
								'instruments'    => $assigned_instrument,
								];
		$musician_order += 10;
	}

	// Update the relational multi-dimensional ordering
	if ( ! empty( $clean_cast_data ) ) 
	{
		update_post_meta( $cast_id, '_cast_data', $clean_cast_data );
		return sprintf( __( '<strong>Success:</strong> Cast "<strong>%s</strong>" has %d musician entries,', 'boston-camerata' ), 
						esc_html( $cast_title ), 
						count( $clean_cast_data ))
				. sprintf( __( '<br> %d musicians added', 'boston-camerata' ), 
						$musicians_added)
				. sprintf( __( '<br> %d instruments added', 'boston-camerata' ), 
						$instruments_added)
				 ;
	}
	else
	{
		delete_post_meta( $cast_id, '_cast_data' );
		return __( '<strong>Notice:</strong> Target cast updated, but no valid musician roster could be extracted from input lines.', 'boston-camerata' );
	}
}

?>