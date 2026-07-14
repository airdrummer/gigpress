<!-- begin gigpress shows-list -->
<?php
/*
	This template displays all of our individual show data in the main shows listing (upcoming and past).
	If you're curious what all variables are available in the $showdata array, have a look at the docs: http://gigpress.com/docs/
*/
    $eventdtarray = explode("-", $showdata['date_mysql']);
    $yr = $eventdtarray['0'];
    $mo = intval($eventdtarray['1']);
    $day = intval($eventdtarray['2']);

    if($yr != $current_year) 
    {
    	$current_year = $yr;
    	$current_month = 0;
    	$current_day = 0;
    	//$current_program = "";
    	$current_venue   = "";
    }
    if($mo != $current_month) 
    {
    	$current_month = $mo;
    	$current_day = 0;
    	//$current_program = "";
    	$current_venue   = "";
    	$monthname = $monthnames[$mo];
    }
 ?>
       <!-- start month -->
    <div class="event" id="prog-<?php echo $showdata['id']; ?>">    <!-- start event -->
<?php
    $new_program = intval($showdata['artist_plain'] !== $current_program);
    if( $new_program > 0 )
    {
        $current_program = $showdata['artist_plain'];
 ?>
		<div class=title-block>
		    <a title='click to show/hide program description'
						 href="#prog-<?php echo $showdata['id']; ?>"
						onclick="return showInfo('prog-note-<?php echo $showdata['id']; ?>')" >
		        <div class="progtitle-wrapper">
		            <h2 class="progtitle" >
		                <?php echo bc_bankhead($current_program); ?>
		            </h2>
	            </div>
		    </a>
		</div>
<?php
    }
        echo '<div class="gig-date">';

        if($day != $current_day) 
		{
        	$current_day = $day;
        	echo ucwords(substr($monthname,0,3)) . "&nbsp;" . $current_day;

        	if($showdata['end_date'])
        	{ 
         		$end_date = explode("-", $showdata['end_date_mysql']);
        		$end_mo = intval($end_date[1]);
               	$end_yr = intval($end_date[0]);
				if ($end_yr != $yr)
					echo ',&nbsp;' . $yr . '&nbsp;';
   		        echo '-';
				if ($end_mo != $mo)
					echo '&nbsp;' . ucfirst(substr($monthnames[$end_mo],0,3)) . '&nbsp;';
        		echo intval($end_date[2]);
				echo ',&nbsp;' . $end_yr;
        	} 
        	else
				echo ',&nbsp;' . $yr . " ";

    		echo '<div class="gig-time">' .  $showdata['time'];
            if( $showdata['status']== 'active'
			  && ($scope != 'past') )  
	 	    { 			// Only show these links if this show is in the future 
			    echo "<br>";
			    echo $showdata['gcal'];
			    echo $showdata['ical'];
	        }
            echo "</div>";
	    } ?>
		</div>  

        <div class="gig-venue">        <!-- start descr -->
<?php if($showdata['venue'] != $current_venue)
	  {
        	$current_venue = $showdata['venue'];
         	$loc = $showdata['venue'].'<br>'; 
         	if(!empty($showdata['address'])) 
         		 $loc .= $showdata['address'].'<br>'; 
			$loc .= $showdata['city']; 
            if(!empty($showdata['state'])) 
            	$loc .= ',&nbsp;' . $showdata['state'];
      		if(!empty($gpo['display_country'])
		 		|| ($showdata['country'] != 'United States' ))
		 		$loc .= ', '.$showdata['country']; 
		 	echo $loc; 
		} ?>
    </div>
    
	<div class="gig-tix" title='click to purchase tickets' > 

<?php if(!empty($showdata['status']))
	{
		if( $showdata['status']== 'active'
		  && ($scope != 'past') )  
	 	{ 			// Only show these links if this show is in the future 
		    if ($showdata['ticket_link'] ) 
				  echo $showdata['ticket_link'];
			if(!empty($showdata['price']))
				echo "<br>" , $showdata['price'];
		}
		else if( $showdata['status']== 'postponed' ) 
			echo "<h3>Postponed!</h3>";
		else if( $showdata['status']== 'cancelled' ) 
			echo "<h3>Cancelled!</h3>";
		else if( $showdata['status']== 'soldout' )
			echo "<h3>Sold Out!</h3>";
	}
?> 
	</div> 
	
	<br style="clear:both;"> 
	
<?php
	echo '<div class="embed-viewall">';
    if( $showdata['cast_id'] > 0 )
        echo "<a  title='display this show&#39;s cast' alt='display this show&#39;s cast'"
                . " href='/about/company-collaborators/"
                    . sanitize_title(( $scope != 'past' 
                                        ? "this season&#39;s" 
                                        : "past")
                                    . " casts")
                . "?show_id=" . $showdata['id'] . "'"
                    . " title='view this performance&#39;s cast'"
                    . "   alt='view this performance&#39;s cast'"
                . "' class=viewall>Cast</a>";
    echo '</div>';
    
    if(!empty($showdata['notes']))
	{
		echo '<div class="gig-note" ' 
                . ( 1 < $condensed ?  "style='display:none;'" : "")
                . ' id="gignote-' . $showdata['id'] . '">';
		    echo $showdata['notes']; 
		echo "</div><!-- end gig-note -->";
    } 

	echo '<div class="prog-note "'
		 . ( (0 < $condensed) || ( ! $new_program ) 
		            ?  "style='display:none;'" : "")
		 . ' id="prog-note-' . $showdata['id'] . '"> <!-- start prog-note -->';
            echo $showdata['program_notes'];

		echo '<div class="prog-genre info-right" >' 
		            . $showdata['program_genres'] . '</div>'; 

	if(!empty($showdata['artist_url']))
		echo '<a class="more-info" href="' . $showdata['artist_url']
		        . '">read more...</a><!-- end artist_url -->';

    if($showdata['related_link']
		&& !empty($gpo['relatedlink_notes']))
		echo '<div class="info-right related-link">'
			. $showdata['related_link']
			. '</div> <!-- end related_link -->';

    if($showdata['external_link'])
		echo '<div class="info-right external-link">'
			. $showdata['external_link']
		    . '</div> <!-- end external_link -->';
?>
    </div><!-- end prog-note -->
    
<br class=clear-both>
<hr>
<!-- end gigpress shows-list -->
