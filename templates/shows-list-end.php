<!-- begin gigpress shows-list-end -->
<?php

    if( $program_id )
    {
    	echo "<div class=top-viewall>";
    	
    	if ( ($program_id && $show_id) || ($scope == 'past'))
        	echo "<a href='/performances/"
        	            . "?program_id=" . $program_id 
        	            . "&condensed=1" . "'" 
                    . " title='view upcoming shows of this program'"
                    . "   alt='view upcoming shows of this program'"
    		            . " class='viewall'>upcoming shows</a>";

    	if ( ($program_id && $show_id) || ($scope !== 'past'))
            echo "<a href='/performances/past-performances/"
        	            . "?program_id=" . $program_id 
        	            . "&condensed=2" . "'" 
                    . " title='view past shows of this program'"
                    . "   alt='view past shows of this program'"
    		            . " class='viewall'>past shows</a>";

        $scope_str = ($scope != 'past' ? "Upcoming" : "Past");
    	echo "<a href='/performances"
		            . ( $scope == 'past' ? "/past-performances" : "") . "'"
                    . " title='view all " . $scope_str . " shows'"
		            . "   alt='view all " . $scope_str . " shows' "
                    . " class='viewall' >All " . $scope_str . " shows</a>";

        $scope_str = ($scope == 'past' ? "Upcoming" : "Past");
    	if ($program_id && $show_id) 
    	    echo "<a href='/about/company-collaborators/"
        	        . ($scope == 'past'
        	            ? "this-seasons-casts" 
        	            : 'past-seasons-casts')
    		        . "/?program_id=" . $program_id . "'"
                    . " title='view $scope_str casts of this program'"
                    . "   alt='view $scope_str casts of this program'"
    		            . " class='viewall'>$scope_str casts</a>";

        echo "</div>";
    }

	include GIGPRESS_PLUGIN_DIR . '/scripts/showInfo.js';

	include GIGPRESS_PLUGIN_DIR . '/lib/details-popup.html';
 ?>
<!-- end gigpress show-list-end  -->
