function cptui_register_my_cpts_musician() {

	/**
	 * Post Type: Musicians.
	 */

	$labels = [
		"name" => esc_html__( "Musicians", "custom-post-type-ui" ),
		"singular_name" => esc_html__( "Musician", "custom-post-type-ui" ),
		"menu_name" => esc_html__( "musicians", "custom-post-type-ui" ),
		"all_items" => esc_html__( "All musicians", "custom-post-type-ui" ),
		"add_new" => esc_html__( "Add new", "custom-post-type-ui" ),
		"add_new_item" => esc_html__( "Add new musician", "custom-post-type-ui" ),
		"edit_item" => esc_html__( "Edit musician", "custom-post-type-ui" ),
		"new_item" => esc_html__( "New musician", "custom-post-type-ui" ),
		"view_item" => esc_html__( "View musician", "custom-post-type-ui" ),
		"view_items" => esc_html__( "View musicians", "custom-post-type-ui" ),
		"search_items" => esc_html__( "Search musicians", "custom-post-type-ui" ),
		"not_found" => esc_html__( "No musicians found", "custom-post-type-ui" ),
		"not_found_in_trash" => esc_html__( "No musicians found in trash", "custom-post-type-ui" ),
		"parent" => esc_html__( "Parent musician:", "custom-post-type-ui" ),
		"featured_image" => esc_html__( "Featured image for this musician", "custom-post-type-ui" ),
		"set_featured_image" => esc_html__( "Set featured image for this musician", "custom-post-type-ui" ),
		"remove_featured_image" => esc_html__( "Remove featured image for this musician", "custom-post-type-ui" ),
		"use_featured_image" => esc_html__( "Use as featured image for this musician", "custom-post-type-ui" ),
		"archives" => esc_html__( "musician archives", "custom-post-type-ui" ),
		"insert_into_item" => esc_html__( "Insert into musician", "custom-post-type-ui" ),
		"uploaded_to_this_item" => esc_html__( "Upload to this musician", "custom-post-type-ui" ),
		"filter_items_list" => esc_html__( "Filter musicians list", "custom-post-type-ui" ),
		"filter_by_date" => esc_html__( "Filter musicians by date", "custom-post-type-ui" ),
		"items_list_navigation" => esc_html__( "musicians list navigation", "custom-post-type-ui" ),
		"items_list" => esc_html__( "musicians list", "custom-post-type-ui" ),
		"attributes" => esc_html__( "musicians attributes", "custom-post-type-ui" ),
		"name_admin_bar" => esc_html__( "musician", "custom-post-type-ui" ),
		"item_published" => esc_html__( "musician published", "custom-post-type-ui" ),
		"item_published_privately" => esc_html__( "musician published privately.", "custom-post-type-ui" ),
		"item_reverted_to_draft" => esc_html__( "musician reverted to draft.", "custom-post-type-ui" ),
		"item_trashed" => esc_html__( "musician trashed.", "custom-post-type-ui" ),
		"item_scheduled" => esc_html__( "musician scheduled", "custom-post-type-ui" ),
		"item_updated" => esc_html__( "musician updated.", "custom-post-type-ui" ),
		"template_name" => esc_html__( "Single musician: musician", "custom-post-type-ui" ),
		"parent_item_colon" => esc_html__( "Parent musician:", "custom-post-type-ui" ),
	];

	$args = [
		"label" => esc_html__( "Musicians", "custom-post-type-ui" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => true,
		"rewrite" => [ "slug" => "musician", "with_front" => true ],
		"query_var" => true,
		"supports" => [ "title", "editor", "thumbnail", "custom-fields" ],
		"taxonomies" => [ "instrument" ],
		"show_in_graphql" => false,
	];

	register_post_type( "musician", $args );
}

add_action( 'init', 'cptui_register_my_cpts_musician' );