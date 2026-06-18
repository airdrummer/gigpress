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

	echo "<div class=top-viewall>";
    if( $program_id )
    {
        $scope_str = ($scope != 'past' ? "Upcoming" : "Past");
 
		echo "<a href='/performances"
		            . ( $scope == 'past' ? "/past-performances" : "") . "'"
                    . " title='view all " . $scope_str . " shows'"
		            . "   alt='view all " . $scope_str . " shows' >"
                    . "<button class='viewall'>All "
                            . $scope_str . " shows</button></a>";

    	if ($program_id && $show_id) 
    	    echo "<a href='/about/company-collaborators/"
        	        . ($scope == 'past'
        	            ? "this-seasons-casts" 
        	            : 'past-seasons-casts')
    		        . "/?program_id=" . $program_id . "'"
                    . " title='view past casts of this program'"
                    . "   alt='view past casts of this program'" . " >"
    		            . "<button class='viewall'>past casts</button></a>" ;
    
    	if ( ($program_id && $show_id) || ($scope !== 'past'))
            echo " <a href='/performances/past-performances/"
        	            . "?program_id=" . $program_id 
        	            . "&condensed=2" . "'" 
                    . " title='view past shows of this program'"
                    . "   alt='view past shows of this program'" ." >"
    		            . "<button class='viewall'>past shows</button></a>" ;
        
    	if ( ($program_id && $show_id) || ($scope == 'past'))
        	echo " <a href='/performances/"
        	            . "?program_id=" . $program_id 
        	            . "&condensed=1" . "'" 
                    . " title='view upcoming shows of this program'"
                    . "   alt='view upcoming shows of this program'" ." >"
    		            . "<button class='viewall'>upcoming shows</button></a>";

        echo "<h3 class='gig-pup'><br>" 
                        . ($show_id ? 'A' . ($scope != 'past' ? "n " : " ")
                                    : '') . $scope_str
                        . " performance" . ($show_id ? '' : 's')
                        . " of this program</h3>";
    }
    echo "</div>";
 ?>
<!-- end gigpress shows-list-start-->
