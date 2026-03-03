<?php
	 if(!empty($srchstrgs) || !empty($selected_genres))
	 {
		echo "<h4>" 
		    . (count($programs)  
						? count($programs) . " program" . (count($programs) >1 ? "s" : '')
						: "no programs")
		    . " matching '" . implode("' $logic '",
					                array_merge($srchstrgs, $selected_genres))
            . "'</h4>";
	 }
?>
