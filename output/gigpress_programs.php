<?php

require_once WP_PLUGIN_DIR . '/gigpress/admin/handlers.php';

function gp_add_query_vars($qVars) //  shortcode atts are lowercase
{
        $qVars[] = "artist";
        $qVars[] = "program_id";
        $qVars[] = "exclude";
        $qVars[] = "artist_order";
        $qVars[] = "genres";
        $qVars[] = "logic";
        return $qVars;
}
// hook add_query_vars function into query_vars
add_filter('query_vars', 'gp_add_query_vars');

function gp_query_atts( $atts ) // query params override shortcode atts
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

function gigpress_programs($atts = null, $content = null) 
{
	global $wpdb;
	
    $atts = gp_query_atts( // url query params
			  shortcode_atts(
				array(
					'artist' => FALSE,
					'program_id' => FALSE,
					'exclude' => FALSE,
					'artist_order' => 'alpha',
					'logic' => 'OR',
					'genres' => FALSE
					), 
				$atts),  'gigpress_programs');

	if($atts['artist'])
		$atts['program_id'] = $atts['artist'];
    $excluded_ids     = $atts['exclude'] ? explode(",",$atts['exclude']) : array();
    $selected_genres  = $atts['genres']  ? explode(",",sanitize_text_field($atts['genres'])) : array();
	$logic = ($atts['logic'] && strtoupper($atts['logic']) === 'AND') ? 'AND' : 'OR';
    $srchstrgs = [];
        
	ob_start();
	
	include gigpress_template('artists-search-form');

	$query = "SELECT * FROM " . GIGPRESS_ARTISTS;
    $where_parts = array();
	$params = array();

	if ( $_SERVER['REQUEST_METHOD'] === 'POST' 
			&& (! empty($_POST['search']) || ! empty($_POST['gp_artist_genres'])
			&& ! empty($_POST['gp_artist_search_nonce']) &&
                wp_verify_nonce($_POST['gp_artist_search_nonce'], 'gp_artist_search_action')))
 	{
	    $logic = (isset($_POST['logic']) 
	    			&& strtoupper($_POST['logic']) === 'AND')
			        ? 'AND'
			        : 'OR';
    	$search_notes = !empty($_POST['search_note']);
    	
 		$search_string = sanitize_text_field( wp_unslash($_POST['search']) );
        // 1. Strip any slashes added by WordPress/PHP magic quotes
        // 2. Extract phrases in quotes OR individual words
        // PREG_SET_ORDER keeps the match groups tied to the specific hit
        preg_match_all('/"([^"]+)"|(\S+)/', $search_string, $matches, PREG_SET_ORDER);
        
        $srchstrgs = [];
        foreach ($matches as $match) 
        {
            // Index 1 is the inner content of " "
            // Index 2 is the standalone word
            $srchstrg = !empty($match[1]) ? $match[1] : $match[2];
            if ($srchstrg)
                $srchstrgs[] = $srchstrg;
        }

	    if ( ! empty($srchstrgs) ) 
	    {
		    foreach ( $srchstrgs as $srchstrg ) 
		    {
		        $like = '%' . $wpdb->esc_like( $srchstrg ) . '%';
		        $where_parts[] = "(artist_name LIKE %s"
		        				 . ($search_notes
		        				 	?	" OR program_notes LIKE %s)"
		        				 	:	")");
		        $params[] = $like;
		        if ( $search_notes ) 
		            $params[] = $like;
		    }
	    }
	    
		$selected_genre_ids = gigpress_get_selected_genre_ids();
		if ( ! empty($selected_genre_ids))
		{
			$artist_ids = gigpress_get_artist_ids_from_genre_ids($selected_genre_ids, $logic);
	    	$format     = implode( ',', array_fill( 0, count( $artist_ids ), '%d' ) );
			$where_parts[] = "(artist_id IN ($format))";
			$params = array_merge($params, $artist_ids);
			$selected_genres = wp_list_pluck(
									gigpress_get_genre_terms($selected_genre_ids),
									'name');
		}
		else
		    $selected_genres = [];

		$query .= " where " . implode(" $logic ", $where_parts);
		$atts['genres'] = false;
 	}
	else if($atts['genres'])
	{
        $genres = gigpress_genre_slugs_to_genres($selected_genres);
        $selected_genres = gigpress_genre_string( $genres, " $logic ");
		$artist_ids = gigpress_get_genre_artist_ids($genres, $logic);
		if(empty($artist_ids))
			$artist_ids = [0];
	    $format     = implode( ',', array_fill( 0, count( $artist_ids ), '%d' ) );
	
	    $query .= $wpdb->prepare(" WHERE artist_id IN ($format)", $artist_ids);
	}
	else if( $atts['program_id'] )
	{
		$query .= ' where artist_id = %d' ;
		$params[] = $atts['program_id'];
		$atts['genres'] = false;
	}
	else 	
		echo $content;

	$query .= " ORDER BY " 
				. (	$atts['artist_order'] == 'alpha'
					 ? "artist_alpha" 
					 : "artist_order")
				. " ASC";
	$query    = $wpdb->prepare( $query, ...$params );
	$programs = $wpdb->get_results($query);

	include gigpress_template('artists-list-start');
	
	if ( count($programs) > 0 )
	{
		foreach($programs as $program) 
		{
			if (in_array($program->artist_id, $excluded_ids))
				continue;

			$showdata = array();
			$showdata['artist']        = $program->artist_name;
			$showdata['artist_id']     = $program->artist_id;
			$showdata['artist_url']    = $program->artist_url;
			$showdata['program_notes'] = $program->program_notes;
			$showdata['genres']        = gigpress_artist_genre_string($program->artist_id);
	
			include gigpress_template('artists-list');
		}
		include gigpress_template('artists-list-end');
	}
	
	echo('<!-- Generated by GigPress ' . GIGPRESS_VERSION . ' gigpress_programs -->
	');
	
	return ob_get_clean();	
}