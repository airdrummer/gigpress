<?php
	 if(!empty($srchstrgs) || !empty($selected_genres))
	 {
		echo "<h4>" 
		    . (count($programs)  
					? count($programs) . " program" . (count($programs) >1 
							? "s" 
							: '')
					: "no programs")
		    . " matching '" . implode("' " . strtolower($logic) . " '",
					                array_merge($srchstrgs, $selected_genres))
            . "'</h4>";
        if (! $atts['genres'])
	 		echo '<button style="float:right;" onclick="openSearch(\'genre\');">search again</button>';
	 }
?>
