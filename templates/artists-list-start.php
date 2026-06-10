<?php

	if ($atts['genres']) // shortcode or query arg
	{
	 	echo "<h3 class=ctr>" . $selected_genres . "</h3>";
	}
	else if(!empty($srchstrgs) || !empty($selected_genres)) 
	{
		$n = count($programs);
		if ( $n > 0 )
			foreach($programs as $program) 
				if (in_array($program->artist_id, $excluded_ids))
						--$n;
<<<<<<< HEAD
=======

>>>>>>> parent of 76fad1f (v2.3.29.5)
		echo "<h4 class=search-results>" 
		    . ($n   ? $n . " program" . ($n >1 ? "s" : '')
			       	: "no programs")
		    . " matching '" . implode("' " . strtolower($logic) . " '",
					                array_merge($srchstrgs, $selected_genres))
            . "'</h4>";
	 	echo '<button style="float:right;" onclick="openSearch(\'genre\');">search again</button>';
	}
?>
