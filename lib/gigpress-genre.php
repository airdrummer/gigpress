<?php
/**
 * GigPress Artist–Genre bridge
 *
 * Stores associations between GigPress artist_ids and WP genre_ids
 * (from existing 'genre' CPT taxonomy) in a dedicated junction table.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// -------------------------------------------------------------------------
// CRUD
// -------------------------------------------------------------------------

/**
 * Get all genre_ids associated with an artist.
 *
 * @param  int   $artist_id
 * @return int[]
 */
function gigpress_get_artist_genre_ids( int $artist_id ): array 
{
    global $wpdb;

    $genre_ids = $wpdb->get_col(
        			$wpdb->prepare(
            			"SELECT genre_id FROM " . GIGPRESS_ARTIST_GENRE . " WHERE artist_id = %d",
            			$artist_id));

    return array_map( 'intval', $genre_ids );
}

/**
 * Sync genre genre_ids for an artist — replaces whatever was there before.
 *
 * @param int   $artist_id
 * @param int[] $genre_ids   Pass empty array to remove all.
 */
function gigpress_set_artist_genres( int $artist_id, array $genre_ids ): void 
{
    global $wpdb;

    // Delete existing rows for this artist
    $wpdb->delete(
        GIGPRESS_ARTIST_GENRE,
        [ 'artist_id' => $artist_id ],
        [ '%d' ]
    );

    // Re-insert the new set
    foreach ( array_unique( array_map( 'intval', array_filter( $genre_ids ) ) ) 
    			as $genre_id ) 
    {
        $wpdb->insert( GIGPRESS_ARTIST_GENRE,
			            [
			                'artist_id' => $artist_id,
			                'genre_id'  => $genre_id,
			            ],
			            [ '%d', '%d' ]
			        );
    }
}

/**
 * Delete all genre rows for an artist — call when deleting an artist.
 *
 * @param int $artist_id
 */
function gigpress_delete_artist_genres( int $artist_id ): void 
{
    gigpress_set_artist_genres($artist_id, []);
}

/**
 * Get all artist_ids associated with a term — useful for filtering shows by genre.
 *
 * @param  array   $genre_id
 * @return int[]
 */
function gigpress_get_genre_artist_ids( array $genre_ids, $logic ): array 
{
    global $wpdb;

    if ( empty( $genre_ids ) ) 
        return [];

    $genre_ids = array_map( 'intval', $genre_ids );
    $sql       = "SELECT DISTINCT artist_id FROM " . GIGPRESS_ARTIST_GENRE . " WHERE ";
    
    if( $logic == "OR" )
    {
    	$format = implode( ',', array_fill( 0, count( $genre_ids ), '%d' ) );
		$sql   .= $wpdb->prepare("genre_id IN ($format)", $genre_ids);
    	$artist_ids = $wpdb->get_col($sql);
    }
    else // AND
    {
		$artist_ids = [];
		foreach ( $genre_ids as $genre_id )
			$artist_ids[] = $wpdb->get_col(
								$wpdb->prepare($sql . "genre_id = %d", $genre_id));
		$artist_ids = array_values(
							array_intersect(...$artist_ids));
    }
	
    return array_map( 'intval', $artist_ids );
} 

// -------------------------------------------------------------------------
// Admin UI
// -------------------------------------------------------------------------

/**
 * Render a genre checkbox list for an artist add/edit form.
 *
 * @param int|null $artist_id  null on the Add form; artist_id on the Edit form.
 */
function gigpress_genre_checkboxes( ?int $artist_id = null ): void 
{
    $genres = get_terms( [ 'taxonomy' => 'genre', 'hide_empty' => false ] );
    if ( is_wp_error( $genres ) || empty( $genres ) ) 
    {
        echo '<p class="description">' . esc_html__( 'No genres found.', 'gigpress' ) . '</p>';
        return;
    }

    $selected_ids = $artist_id 
    					? gigpress_get_artist_genre_ids( $artist_id ) 
    					: gigpress_get_selected_genre_ids(); // from form 

echo '<ul style="margin:0;padding:0;list-style:none">';
    foreach ( $genres as $genre ) 
    {
        $checked = in_array( (int) $genre->term_id, $selected_ids, true ) ? 'checked="checked"' : '';
        printf(
            '<li><label><input type="checkbox" name="gp_artist_genres[]" value="%d" %s /> %s</label></li>',
            (int) $genre->term_id,
            $checked,
            esc_html( $genre->name )
        );
    }
    echo '</ul>';

    wp_nonce_field( 'gigpress_save_genres', '_gigpress_genre_nonce' );
}

/**
 * Process and save genre checkboxes from a submitted admin form.
 * Call this immediately after the INSERT or UPDATE of the artist row.
 *
 * @param  int  $artist_id
 * @return bool  true if nonce was valid and save ran
 */
function gigpress_get_selected_genre_ids() // from form checkboxes
{
	$genre_ids =  isset( $_POST['gp_artist_genres'] )
        		? array_map( 'intval', (array) $_POST['gp_artist_genres'] )
        		: [];
    return $genre_ids;
}

function gigpress_process_genre_save( int $artist_id ): bool 
{
    if (empty( $_POST['_gigpress_genre_nonce'] )
         	|| ! wp_verify_nonce( $_POST['_gigpress_genre_nonce'], 
         						 'gigpress_save_genres' )) 
        return false;

    gigpress_set_artist_genres( $artist_id, 
    							gigpress_get_selected_genre_ids() );
    return true;
}

// -------------------------------------------------------------------------
// Frontend helpers
// -------------------------------------------------------------------------

function gigpress_genre_slugs_to_ids( array $slugs ): array 
{
    $genre_ids = [];

    foreach ( $slugs as $slug ) 
    {
        $genre = get_term_by( 'slug', $slug, 'genre' );
        if ( $genre && ! is_wp_error( $genre ) )
            $genre_ids[] = (int) $genre->term_id;
    }

    return $genre_ids;
}

/**
 * Get WP_Term objects for an artist's genres.
 *
 * @param  int   $artist_id
 * @return WP_Term[]
 */
function gigpress_get_artist_genre_terms( int $artist_id ): array 
{
    $genre_ids = gigpress_get_artist_genre_ids( $artist_id );
    if ( empty( $genre_ids ) )
        return [];

    $genres = get_terms( [
        'taxonomy'   => 'genre',
        'include'    => $genre_ids,
        'hide_empty' => false,
    ] );

    return is_wp_error( $genres ) ? [] : (array) $genres;
}

/**
 * Comma-separated genre name string — ready for templates.
 *
 * @param  int    $artist_id
 * @param  string $sep
 * @param  bool   $add_label
 * @return string
 */
function gigpress_artist_genre_string( int $artist_id, 
                                       string $sep = ', ',
                                       bool $add_label = TRUE): string 
{
    $genres = gigpress_get_artist_genre_terms( $artist_id );
    if(! count($genres))
    	return '';

    return ($add_label  ? ("Genre" . (count($genres) > 1 
                                        ? "s" 
                                        : "") . ': ')
                        : '')
            . implode( $sep, array_map( 
                                function( $g ) 
                                    { return esc_html( $g->name ); }, 
                                $genres ) );
}
