function cptui_register_my_cpts_recordings() {

	/**
	 * Post Type: recordings.
	 */

	$labels = [
		"name" => esc_html__( "recordings", "custom-post-type-ui" ),
		"singular_name" => esc_html__( "recording", "custom-post-type-ui" ),
		"menu_name" => esc_html__( "recordings", "custom-post-type-ui" ),
		"all_items" => esc_html__( "All recordings", "custom-post-type-ui" ),
		"add_new" => esc_html__( "Add new", "custom-post-type-ui" ),
		"add_new_item" => esc_html__( "Add new recording", "custom-post-type-ui" ),
		"edit_item" => esc_html__( "Edit recording", "custom-post-type-ui" ),
		"new_item" => esc_html__( "New recording", "custom-post-type-ui" ),
		"view_item" => esc_html__( "View recording", "custom-post-type-ui" ),
		"view_items" => esc_html__( "View recordings", "custom-post-type-ui" ),
		"search_items" => esc_html__( "Search recordings", "custom-post-type-ui" ),
		"not_found" => esc_html__( "No recordings found", "custom-post-type-ui" ),
		"not_found_in_trash" => esc_html__( "No recordings found in trash", "custom-post-type-ui" ),
		"parent" => esc_html__( "Parent recording:", "custom-post-type-ui" ),
		"featured_image" => esc_html__( "Featured image for this recording", "custom-post-type-ui" ),
		"set_featured_image" => esc_html__( "Set featured image for this recording", "custom-post-type-ui" ),
		"remove_featured_image" => esc_html__( "Remove featured image for this recording", "custom-post-type-ui" ),
		"use_featured_image" => esc_html__( "Use as featured image for this recording", "custom-post-type-ui" ),
		"archives" => esc_html__( "recording archives", "custom-post-type-ui" ),
		"insert_into_item" => esc_html__( "Insert into recording", "custom-post-type-ui" ),
		"uploaded_to_this_item" => esc_html__( "Upload to this recording", "custom-post-type-ui" ),
		"filter_items_list" => esc_html__( "Filter recordings list", "custom-post-type-ui" ),
		"items_list_navigation" => esc_html__( "recordings list navigation", "custom-post-type-ui" ),
		"items_list" => esc_html__( "recordings list", "custom-post-type-ui" ),
		"attributes" => esc_html__( "recordings attributes", "custom-post-type-ui" ),
		"name_admin_bar" => esc_html__( "recording", "custom-post-type-ui" ),
		"item_published" => esc_html__( "recording published", "custom-post-type-ui" ),
		"item_published_privately" => esc_html__( "recording published privately.", "custom-post-type-ui" ),
		"item_reverted_to_draft" => esc_html__( "recording reverted to draft.", "custom-post-type-ui" ),
		"item_trashed" => esc_html__( "recording trashed.", "custom-post-type-ui" ),
		"item_scheduled" => esc_html__( "recording scheduled", "custom-post-type-ui" ),
		"item_updated" => esc_html__( "recording updated.", "custom-post-type-ui" ),
		"template_name" => esc_html__( "Single recording: recording", "custom-post-type-ui" ),
		"parent_item_colon" => esc_html__( "Parent recording:", "custom-post-type-ui" ),
	];

	$args = [
		"label" => esc_html__( "recordings", "custom-post-type-ui" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => "recordings",
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => true,
		"rewrite" => [ "slug" => "recordings", "with_front" => true ],
		"query_var" => true,
		"menu_icon" => "dashicons-format-audio",
		"supports" => [ "title", "editor", "thumbnail", "custom-fields" ],
		"taxonomies" => [ "genre", "label" ],
		"show_in_graphql" => false,
	];

	register_post_type( "recordings", $args );
}

add_action( 'init', 'cptui_register_my_cpts_recordings' );