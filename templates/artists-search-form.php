<details id="filter-form" open="">
    <summary style="padding-top: 2px;" title="-- match titles/text ">search</summary>

 	<form method="post" class="gp-artist-search-form">
	    <?php wp_nonce_field('gp_artist_search_action', 'gp_artist_search_nonce'); ?>
	
	    <input type="text"
	           name="search"
	           value="<?php echo esc_attr($_POST['search'] ?? ''); ?>"
	           placeholder="Search programs">
	
	    <select name="logic">
	        <option value="AND" <?php selected($_POST['logic'] ?? '', 'AND'); ?>>
	            Match ALL words
	        </option>
	        <option value="OR" <?php selected($_POST['logic'] ?? '', 'OR'); ?>>
	            Match ANY word
	        </option>
	    </select>
	
	    <label>
	        <input type="checkbox"
	               name="search_note"
	               value="1"
	               <?php checked($_POST['search_note'] ?? '', '1'); ?>>
	        Include program notes in search
	    </label>
	
	    <button type="submit" name="gp_artist_search_submit">
	        Search
	    </button>
	    <br>
	    <a href="<?php echo esc_url( get_permalink() ); ?>" class="clear-button">Clear all</a>
  </form>
</details>