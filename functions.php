<?php
/**
 * P2020 functions and definitions
 *
 * @package p2020
 */

namespace P2020;

/**
 * Load My Team widget
 */
require_once( get_template_directory() . '/widgets/my-team/my-team.php' );

/**
 * Load Filter widget
 */
if ( is_user_logged_in() ) {
	require_once( get_template_directory() . '/widgets/filter/load.php' );
}

/**
 * Load Pages widget
 */
require_once( get_template_directory() . '/widgets/pages/pages.php' );

/**
 * Load partner plugins loader file.
 */
require_once( 'thirdparty.php' );

/**
 * Global variable for WP core and various plugins to size embeds/images appropriately.
 * https://codex.wordpress.org/Content_Width
 */
if ( ! isset( $content_width ) )
	$content_width = 940; /* pixels */

/**
 * Set up social sharing and likes
 */
function social_init() {
	//Disable social media share button
	$sharing_services = get_option( 'sharing-services' );
	if ( ! empty( $sharing_services['visible'] ) ) {
		$sharing_services['visible'] = [];
		update_option( 'sharing-services', $sharing_services );
	}

	// Disable reblog button
	$disabled_reblogs = get_option( 'disabled_reblogs' );
	if ( 1 !== (int)$disabled_reblogs ) {
		update_option( 'disabled_reblogs', 1 );
	}

	// Enable like button
	$disabled_likes = get_option( 'disabled_likes' );
	if ( 1 === (int)$disabled_likes ) {
		update_option( 'disabled_likes', 0 );
	}

	// Show buttons everywhere
	$sharing_options = get_option( 'sharing-options' );
	$show_in_locations = [ 'index', 'post', 'page', 'attachment' ];
	if ( ! is_array( $sharing_options['global']['show'] ) ||
		count( array_intersect( $sharing_options['global']['show'], $show_in_locations ) ) !== count( $show_in_locations ) ) {
		$sharing_options['global']['show'] = $show_in_locations;
		update_option( 'sharing-options', $sharing_options );
	}
}

add_action( 'after_setup_theme', __NAMESPACE__ . '\social_init' );

/**
 * Disable related posts feature
 */
function disable_related_posts() {
	$jetpack_relatedposts = get_option( 'jetpack_relatedposts' );
	// We need to explicitly set it to false to avoid default behavior
	if ( ! isset( $jetpack_relatedposts['enabled'] ) || 0 !== (int)$jetpack_relatedposts['enabled'] ) {
		// Disable related posts
		$jetpack_relatedposts['enabled'] = 0;
		update_option( 'jetpack_relatedposts', $jetpack_relatedposts );

		// Remove related-posts from jetpack active modules
		$jetpack_active_modules = get_option( 'jetpack_active_modules' );
		if ( is_array( $jetpack_active_modules ) ) {
			update_option( 'jetpack_active_modules', array_diff( $jetpack_active_modules, ['related-posts'] ) );
		}
	}
}

add_action( 'after_setup_theme', __NAMESPACE__ . '\disable_related_posts' );

/*
 * Load Jetpack compatibility file.
 */
require( get_template_directory() . '/inc/jetpack.php' );

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 */
function setup() {

	/**
	 * Custom functions that act independently of the theme templates
	 */
	require( get_template_directory() . '/inc/extras.php' );

	/**
	 * Customizer additions
	 */
	require( get_template_directory() . '/inc/customizer.php' );

	/**
	 * Make theme available for translation
	 * Translations can be filed in the /languages/ directory
	 * If you're building a theme based on P2, use a find and replace
	 * to change 'p2020' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'p2020', get_template_directory() . '/languages' );

	/**
	 * Add default posts and comments RSS feed links to head
	 */
	add_theme_support( 'automatic-feed-links' );

	/**
	 * Enable support for Post Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );

	/**
 	 * Enable support for Full-Width Images
 	 */
	add_theme_support( 'align-wide' );

	/**
	 * Enable support for themed block editor styles
	 */
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style-editor.css' );

	/**
	 * This theme uses wp_nav_menu() in one location.
	 */
	register_nav_menus( [
		'primary' => __( 'Primary Menu', 'p2020' ),
	] );

	/**
	 * Add a menu with Home item, if location is empty.
	 */
	$locations = get_theme_mod( 'nav_menu_locations' );
	if ( empty( $locations['primary'] ) ) {
		// Check if menu object already exists
		$menu = wp_get_nav_menu_object( 'primary' );
		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( 'primary' );
			wp_update_nav_menu_item( $menu_id, 0, [
				'menu-item-title' => __( 'Home', 'p2020' ),
				'menu-item-url' => home_url( '/' ),
				'menu-item-status' => 'publish'
			] );
		} else {
			$menu_id = $menu->term_id;
		}

		$locations['primary'] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}
}
add_action( 'after_setup_theme', __NAMESPACE__ . '\setup' );

/**
 * Register widgetized area and update sidebar with default widgets
 */
function widgets_init() {
	register_sidebar( [
		'name' => __( 'Sidebar for Posts', 'p2020' ),
		'id' => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h2 class="widget-title">',
		'after_title' => '</h2>',
	] );

	register_sidebar( [
		'name' => __( 'Sidebar for Pages', 'p2020' ),
		'id' => 'sidebar-pages',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h2 class="widget-title">',
		'after_title' => '</h2>',
	] );
}
add_action( 'widgets_init', __NAMESPACE__ . '\widgets_init' );

/**
 * Enqueue Google Fonts
 */
function fonts() {
	/**
	 * translators: If there are characters in your language that are not supported
	 * by Inter, translate this to 'off'. Do not translate into your own language.
	 */
	if ( 'off' !== _x( 'on', 'Sans: on or off', 'p2020' ) ) {
		wp_register_style( 'p2020-sans', "https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" );
	}
}
add_action( 'init', __NAMESPACE__ . '\fonts' );

/**
 * Enqueue font styles in custom header admin
 */
function admin_fonts( $hook_suffix ) {
	if ( 'appearance_page_custom-header' != $hook_suffix )
		return;

	wp_enqueue_style( 'p2020-sans' );
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\admin_fonts' );

/**
 * Enqueue scripts and styles
 */
function scripts() {
	wp_enqueue_style( 'p2020-style', get_stylesheet_uri() );
	wp_style_add_data( 'p2020-style', 'rtl', 'replace' );
	wp_enqueue_style( 'p2020-sans' );

	// Vendor
	wp_enqueue_script( 'p2020-modernizr', get_template_directory_uri() . '/js/vendor/modernizr-custom.js', [], '20200416', true );
	wp_enqueue_script( 'p2020-debounce', get_template_directory_uri() . '/js/vendor/jquery.ba-throttle-debounce.min.js', [], '20200416', true );

	// Main enqueued file
	wp_enqueue_script( 'p2020-js', get_template_directory_uri() . '/js/enqueued-main.js', [ 'o2-enquire', 'p2020-modernizr' ], '20200416', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	if ( is_singular() && wp_attachment_is_image() ) {
		wp_enqueue_script( 'p2020-keyboard-image-navigation', get_template_directory_uri() . '/js/vendor/keyboard-image-navigation.js', [ 'jquery' ], '20120202' );
	}
}

// Our stylesheets need to be loaded after the O2 stylesheets to take priority
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\scripts', 11 );

/**
 * Enqueue editor scripts and styles
 */
function editor_assets() {
	// Main editor enqueued file
	wp_enqueue_script( 'p2020-editor-js', get_template_directory_uri() . '/js/enqueued-editor-main.js', [  ], '20200430', true );
}

add_action( 'enqueue_block_editor_assets',  __NAMESPACE__ . '\editor_assets', 11 );

/**
 * Add a no-sidebar body class, if there are no widgets in the sidebar.
 */
function check_no_sidebar( $body_classes ) {
	if( ! is_active_sidebar( 'sidebar-1' ) && ! ( is_page() && is_active_sidebar( 'sidebar-pages' ) ) )
		$body_classes[] = 'no-sidebar';

	return $body_classes;
}
add_filter( 'body_class', __NAMESPACE__ . '\check_no_sidebar' );

/**
 * Implement the Custom Header feature
 */
require( get_template_directory() . '/inc/custom-header.php' );

/**
 * Set Homepage display to latest posts.
 */
function set_homepage_display() {
	$show_on_front = get_option( 'show_on_front' );
	if ( $show_on_front !== 'posts' ) {
		update_option( 'show_on_front', 'posts' );
	}
}
add_action( 'after_setup_theme', __NAMESPACE__ . '\set_homepage_display', 102 );

/**
 * Add recommended widgets to sidebar
 */
function enable_default_widgets() {
	$setup_option = get_option( 'p2020_sidebar_setup' );
	if ( $setup_option === 'reset' ) {
		$sidebars_widgets['sidebar-1'] = [];
		$sidebars_widgets['sidebar-pages'] = [];
	} else {
		$sidebars_widgets = get_option( 'sidebars_widgets' );
	}

	if ( $setup_option === 'reset' || $setup_option === 'add' ) {
		$widget_no = 3;

		// My Team widget (widgets/myteam)
		if ( empty( $sidebars_widgets['sidebar-1'] ) ||
				! in_array( "p2020-my-team-widget-{$widget_no}", $sidebars_widgets['sidebar-1'] ) ) {
			$team_widget_settings = [
				$widget_no => [
					'title' => __( 'Team', 'p2020' ),
					'limit' => 17,
				]
			];
			update_option( 'widget_p2020-my-team-widget', $team_widget_settings );
			$sidebars_widgets['sidebar-1'][] = "p2020-my-team-widget-{$widget_no}";
		}

		// P2020 Filter widget (widgets/filter)
		if ( empty( $sidebars_widgets['sidebar-1'] ) ||
				! in_array( "p2020-filter-widget-{$widget_no}", $sidebars_widgets['sidebar-1'] ) ) {
			$filter_widget_settings = [
				$widget_no => []
			];
			update_option( 'widget_p2020-filter-widget', $filter_widget_settings );
			$sidebars_widgets['sidebar-1'][] = "p2020-filter-widget-{$widget_no}";
		}

		// Pages widget
		if ( empty( $sidebars_widgets['sidebar-pages'] ) ||
				! in_array( "p2020-pages-widget-{$widget_no}", $sidebars_widgets['sidebar-pages'] ) ) {

			$pages_widget_settings = [
				$widget_no => [
					'title' => __( 'Pages', 'p2020' ),
					'sortby' => 'menu_order',
					'conditions' => $show_for_pages,
				],
			];
			update_option( 'widget_p2020-pages-widget', $pages_widget_settings );
			$sidebars_widgets['sidebar-pages'][] = "p2020-pages-widget-{$widget_no}";
		}

		// Save sidebar updates
		$sidebars_widgets['array_version'] = 3;
		update_option( 'sidebars_widgets', $sidebars_widgets );

		// Clear setup flag afterwards
		update_option( 'p2020_sidebar_setup', '' );

		// Refresh sidebars_widgets cache
		global $_wp_sidebars_widgets;
		$_wp_sidebars_widgets = $sidebars_widgets;
	}
}
add_action( 'after_setup_theme', __NAMESPACE__ . '\enable_default_widgets' );

/**
 * Hide editor for certain views:
 *     - search results page
 *     - tag archives
 */
function hide_o2_editor( $o2_options ) {
	if ( is_search() || is_tag() ) {
		$o2_options['options']['showFrontSidePostBox'] = false;
	}

	return $o2_options;
}
add_filter( 'o2_options', __NAMESPACE__ . '\hide_o2_editor' );

/**
 * Append Contributor block to content on single pages
 */
function append_contributors_block( $content ) {
	if ( !is_page() || !in_the_loop() || !is_main_query() ) {
		return $content;
	}

	require_once( get_template_directory() . '/inc/contributors.php' );
	return $content . get_contributors_block();
}
add_filter( 'the_content', __NAMESPACE__ . '\append_contributors_block' );

/**
 * Add a "More" container for overflowed menu items
 */
function append_more_container( string $items, object $args ): string {
	$more_container = '<li class="menu-item menu-item-has-children" hidden data-header-menu-more>'
					. '<a href="#">'
					. esc_html( 'More', 'p2020' )
					. '</a>'
					. '<ul class="sub-menu" />'
					. '</li>';

	if ( $args->theme_location === 'primary' ) {
		$items .= $more_container;
	}

	return $items;
}
add_filter( 'wp_nav_menu_items', __NAMESPACE__ . '\append_more_container', 10, 2 );
