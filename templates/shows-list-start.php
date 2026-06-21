<!-- begin gigpress shows-list-start-->
<?php
	$current_year  = ( $artist ? date('Y', current_time('timestamp')) : 0 );
	$current_month = 0;
	$current_day = 0;
	$current_program = "";
	$current_venue   = "";
	$date_mysql_format = '%Y-%m-%d';
	$monthnames = array("0", "january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december");
	$title_out = FALSE;

    if( $program_id )
    {
    	echo "<div class=top-viewall>";
        $scope_str = ($scope != 'past' ? "Upcoming" : "Past");
 
		echo "<a href='/performances"
		            . ( $scope == 'past' ? "/past-performances" : "") . "'"
                    . " title='view all " . $scope_str . " shows'"
		            . "   alt='view all " . $scope_str . " shows' "
                    . " class='viewall' >All " . $scope_str . " shows</a>";

    	if ($program_id && $show_id) 
    	    echo "<a href='/about/company-collaborators/"
        	        . ($scope == 'past'
        	            ? "this-seasons-casts" 
        	            : 'past-seasons-casts')
    		        . "/?program_id=" . $program_id . "'"
                    . " title='view past casts of this program'"
                    . "   alt='view past casts of this program'"
    		            . " class='viewall'>past casts</a>";

    	if ( ($program_id && $show_id) || ($scope !== 'past'))
            echo "<a href='/performances/past-performances/"
        	            . "?program_id=" . $program_id 
        	            . "&condensed=2" . "'" 
                    . " title='view past shows of this program'"
                    . "   alt='view past shows of this program'"
    		            . " class='viewall'>past shows</a>";

    	if ( ($program_id && $show_id) || ($scope == 'past'))
        	echo "<a href='/performances/"
        	            . "?program_id=" . $program_id 
        	            . "&condensed=1" . "'" 
                    . " title='view upcoming shows of this program'"
                    . "   alt='view upcoming shows of this program'"
    		            . " class='viewall'>upcoming shows</a>";
        echo "</div>";

        echo "<h3 class='gig-pup'>" 
                        . ($show_id ? 'A' . ($scope != 'past' ? "n " : " ")
                                    : '') . $scope_str
                        . " performance" . ($show_id ? '' : 's')
                        . " of this program</h3>";
    }
 ?>
<!-- end gigpress shows-list-start-->
