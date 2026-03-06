<?php

	if ($atts['genres']) // shortcode or query arg
	{
	 	echo "<h3 class=ctr>" . $selected_genres . "</h3>";
	}
	else if(!empty($srchstrgs) || !empty($selected_genres))
	{
		$n = count($programs);
		echo "<h4 class=search-results>" 
		    . ($n
			        ? $n . " program" . ($n >1 ? "s" : '')
			       	: "no programs")
		    . " matching '" . implode("' " . strtolower($logic) . " '",
					                array_merge($srchstrgs, $selected_genres))
            . "'</h4>";
	 	echo '<button style="float:right;" onclick="openSearch(\'genre\');">search again</button>';
	}
?>
