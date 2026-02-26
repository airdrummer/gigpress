<h4 class="gigpress-
<?php
	if( count($programs) == 0)
		echo 'empty">No programs found';

    if(!empty($terms) || 
       !empty($selected_genres) )
    {
		if( count($programs) > 0)
			echo 'results">'  . count($programs) . " program"
					. (count($programs) > 1 ? "s" : '') . " found";
    	echo ' matching ';
		echo bc_build_matches("",
						array_filter( [
							bc_build_matches("text",  $terms,           $logic),
							bc_build_matches("genre", $selected_genres, $logic)
    					]), $logic);
    	echo '</h4>';
        echo "<style type='text/css'>.hero-image { display: none; } </style>";
    }
    else
    	echo '</h4>';
?>
