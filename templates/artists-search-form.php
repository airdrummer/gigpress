<div id=gp-artist-search-form class="filter-form gamma gig-pup">
<details id="filter-form" >
    <summary title="-- match titles/text ">search</summary>

 	<form method="post" class="gp-artist-search-form">
	    <?php wp_nonce_field('gp_artist_search_action', 'gp_artist_search_nonce'); ?>
	
	    <input type="text" name="search" style="width:100%;"
	           value="<?php echo wp_unslash(esc_attr($_POST['search'] ?? '')); ?>"
	           placeholder='      enclose "phrase in quotes"'>
	<br>
	    <label>
	        <input type="checkbox"
	               name="search_note"
	               value="1"
	               <?php checked($_POST['search_note'] ?? '', '1'); ?> >
	         search&nbsp;description&nbsp;too
	    </label>
	<br>
	    <select name="logic">
	        <option value="OR" <?php selected($_POST['logic'] ?? '', 'OR'); ?> >
	            Match ANY word or genre
	        </option>
	        <option value="AND" <?php selected($_POST['logic'] ?? '', 'AND'); ?> >
	            Match ALL words and genres
	        </option>
	    </select>
	<br>
		<details id="genre-filter" open=""><summary>by genre</summary>
<?php
		gigpress_genre_checkboxes($atts['program_id']); 
?>
		</details>
	    <button type="submit" name="gp_artist_search_submit">Search</button>
	    <br>
	    <a href="<?php echo esc_url( get_permalink() ); ?>" class="clear-button">Clear all</a>
  </form>
</details>
</div>
<script>
    function openSearch(tax_tags) 
    {
        document.getElementById("filter-form").open = true;
        tax_tags.split(',').forEach(
            function(currentString) 
            {
                document.getElementById(currentString + "-filter").open = true;
            });
    }
    document.addEventListener("DOMContentLoaded", function() 
    {
        const ff  = document.querySelector('.filter-form');
        const hdr = document.querySelector('#masthead');
        if (ff && hdr) 
            hdr.appendChild(ff);
        else
            console.warn("filter-form not found");
    });
</script>
<style>
    .site-header .search-form 
    {
        display: none;
    }
</style>