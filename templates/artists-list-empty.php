<!-- begin gigpress artist-list-empty -->
<?php
	
// 	STOP! DO NOT MODIFY THIS FILE!
//	If you wish to customize the output, you can safely do so by COPYING this file
//	into a new folder called 'gigpress-templates' in your 'wp-content' directory
//	and then making your changes there. When in place, that file will load in place of this one.

// This template displays when you have no shows.

?>

<h4 class="gigpress-empty">No programs found
    <?php 
    	if(!empty($_POST['search']))
    	{
            echo " matching '" . implode("' $logic '", $terms) . "'";
            echo "<style type='text/css'>.hero-image { display: none; } </style>";
		    echo "<div class=top-viewall><a href='" . get_permalink() . "'>"
                . '<button class=viewall>view all programs</button>'
            . "</a></div>";
    	}
    ?>
</h4>
<!-- end gigpress artist-list-empty -->
