<?php
	 if(isset($_POST['search']))
		echo "<h4>" . count($programs) . " programs found matching '" . implode(" $logic ", $terms) . "'</h4>";
?>
