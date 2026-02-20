<?php
	 if(isset($_POST['search']))
		echo "<h4>" . count($programs) . " programs found matching '" . $_POST['search'] . "'</h4>";
?>
