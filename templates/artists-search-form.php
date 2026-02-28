<div class="filter-form gamma gig-pup">
<details id="filter-form" >
    <summary style="padding-top: 2px;" title="-- match titles/text ">search</summary>

 	<form method="post" class="gp-artist-search-form">
	    <?php wp_nonce_field('gp_artist_search_action', 'gp_artist_search_nonce'); ?>
	
	    <input type="text" name="search" style="width:90%;"
	           value="<?php echo wp_unslash(esc_attr($_POST['search'] ?? '')); ?>"
	           placeholder=' enclose "phrases in quotes"'>
	
	    <select name="logic">
	        <option value="OR" <?php selected($_POST['logic'] ?? '', 'OR'); ?>>
	            Match ANY word
	        </option>
	        <option value="AND" <?php selected($_POST['logic'] ?? '', 'AND'); ?>>
	            Match ALL words
	        </option>
	    </select>
	<br>
	    <label>
	        <input type="checkbox"
	               name="search_note"
	               value="1"
	               <?php checked($_POST['search_note'] ?? '', '1'); ?>>
	        Include&nbsp;program&nbsp;notes in&nbsp;search
	    </label>
	<br>
<?php	$selected_genres = $_POST['genre'];
print_r($selected_genres);
		$selected_names = bc_display_taxonomy_checkboxes("genre",$selected_genres);
print_r($selected_names);
?> 
	    <button type="submit" name="gp_artist_search_submit">apply</button>
	    <br>
	    <a href="<?php echo esc_url( get_permalink() ); ?>" class="clear-button">Clear all</a>
  </form>
</details>
</div>
<script>
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