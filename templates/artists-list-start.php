<!-- begin gigpress artist-list-start-->
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
	    echo '<div class="embed-viewall">';
		echo "<h3 class='gig-pup search-results'>" 
		    . ($n   ? $n . " program" . ($n >1 ? "s" : '')
			       	: "no programs")
		    . " matching '" . implode("' " . strtolower($logic) . " '",
					                array_merge($srchstrgs, $selected_genres))
            . "'</h3>&nbsp;";
	 	echo '<a class="viewall" href=# onclick="openSearch(\'genre\');">search again</a>';
        echo "</div>";
	}
?>
<!-- end gigpress artist-list-start-->
