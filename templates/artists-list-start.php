<!--gigpress artist/program-list-start -->
<?php
	 if(isset($_POST['search']))
		echo "<h4>" . count($programs) . " programs found matching matching '" . $_POST['search'] . "'</h4>";
?>
<!--gigpress list-start end -->