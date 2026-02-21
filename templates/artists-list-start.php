<?php
	 if(isset($_POST['search']))
		echo "<h4>" . count($programs) . " program". (count($programs) >1 ? "s" : '')
		    ." found matching '" . implode("' $logic '", $terms) . "'</h4>";
?>
