function cptui_register_my_taxes_product() {

	/**
	 * Taxonomy: Products.
	 */

	$labels = [
		"name" => esc_html__( "Products", "custom-post-type-ui" ),
		"singular_name" => esc_html__( "Product", "custom-post-type-ui" ),
	];

	
	$args = [
		"label" => esc_html__( "Products", "custom-post-type-ui" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => true,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'product', 'with_front' => true, ],
		"show_admin_column" => true,
		"show_in_rest" => true,
		"show_tagcloud" => false,
		"rest_base" => "product",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace" => "wp/v2",
		"show_in_quick_edit" => true,
		"sort" => false,
		"show_in_graphql" => false,
	];
	register_taxonomy( "product", [ "recordings" ], $args );
}
add_action( 'init', 'cptui_register_my_taxes_product' );