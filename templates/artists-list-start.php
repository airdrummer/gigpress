<?php
	 if(!empty($srchstrgs) || !empty($selected_genres))
	 {
		echo "<h4>" . (count($programs)  
						? count($programs) . " program" . (count($programs) >1 ? "s" : '')
						: "no programs")
		    ." matching '";
		echo implode(" $logic ",
					 [ implode("' $logic '", $srchstrgs),
					   implode("' $logic '", $selected_genres)
					 ]);
		echo "'</h4>";
	 }		
?>
