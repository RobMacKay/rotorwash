<?php
/**
 * RotorWash functions and definitions
 *
 * Sets up the theme and provides some helper functions. Some helper functions
 * are used in the theme as custom template tags. Others are attached to action and
 * filter hooks in WordPress to change core functionality.
 *
 * The first function, rw_setup(), sets up the theme by registering support
 * for various features in WordPress, such as post thumbnails, navigation menus, and the like.
 *
 * When using a child theme (see http://codex.wordpress.org/Theme_Development and
 * http://codex.wordpress.org/Child_Themes), you can override certain functions
 * (those wrapped in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before the parent
 * theme's file, so the child theme functions would be used.
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are instead attached
 * to a filter or action hook. The hook can be removed by using remove_action() or
 * remove_filter() and you can attach your own function to the hook.
 *
 * We can remove the parent theme's hook only after it is attached, which means we need to
 * wait until setting up the child theme:
 *
 * <code>
 * add_action( 'after_setup_theme', 'my_child_theme_setup' );
 * function my_child_theme_setup() {
 *     // We are providing our own filter for excerpt_length (or using the unfiltered value)
 *     remove_filter( 'excerpt_length', 'rw_excerpt_length' );
 *     ...
 * }
 * </code>
 *
 * For more information on hooks, actions, and filters, see http://codex.wordpress.org/Plugin_API.
 *
 * @package WordPress
 * @subpackage RotorWash
 * @since RotorWash 1.0
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * Used to set the width of images and content. Should be equal to the width the theme
 * is designed for, generally via the style.css stylesheet.
 */
if( !isset( $content_width ) )
{
    $content_width = 580;
}

/** Tell WordPress to run rw_setup() when the 'after_setup_theme' hook is run. */
add_action( 'after_setup_theme', 'rw_setup' );

if( !function_exists( 'rw_setup' ) ):
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 *
 * To override rw_setup() in a child theme, add your own rw_setup to your child theme's
 * functions.php file.
 *
 * @uses add_theme_support() To add support for post thumbnails and automatic feed links.
 * @uses register_nav_menus() To add support for navigation menus.
 * @uses add_editor_style() To style the visual editor.
 * @uses load_theme_textdomain() For translation/localization support.
 *
 * @since RotorWash 1.0
 */
function rw_setup(  )
{

    // This theme styles the visual editor with editor-style.css to match the theme style.
    add_editor_style();

    // This theme uses post thumbnails
    add_theme_support( 'post-thumbnails' );

    // Add default posts and comments RSS feed links to head
    add_theme_support( 'automatic-feed-links' );

    // Make theme available for translation
    // Translations can be filed in the /languages/ directory
    load_theme_textdomain( 'copterlabs', TEMPLATEPATH . '/languages' );

    $locale = get_locale();
    $locale_file = TEMPLATEPATH . "/languages/$locale.php";
    if( is_readable( $locale_file ) )
        require_once( $locale_file );

    // This theme uses wp_nav_menu() in one location.
    register_nav_menus( array(
        'primary' => __( 'Primary Navigation', 'copterlabs' ),
    ) );
}
endif;

/**
 * Makes some changes to the <title> tag, by filtering the output of wp_title().
 *
 * If we have a site description and we're viewing the home page or a blog posts
 * page (when using a static front page), then we will add the site description.
 *
 * If we're viewing a search result, then we're going to recreate the title entirely.
 * We're going to add page numbers to all titles as well, to the middle of a search
 * result title and the end of all other titles.
 *
 * The site title also gets added to all titles.
 *
 * @since RotorWash 1.0
 *
 * @param string $title Title generated by wp_title()
 * @param string $separator The separator passed to wp_title().
 * @return string The new title, ready for the <title> tag.
 */
function rw_filter_wp_title( $title, $separator="&rsaquo;" )
{
    // Don't affect wp_title() calls in feeds.
    if( is_feed() )
    {
        return $title;
    }

    // The $paged global variable contains the page number of a listing of posts.
    // The $page global variable contains the page number of a single post that is paged.
    // We'll display whichever one applies, if we're not looking at the first page.
    global $paged, $page;

    if( is_search() )
    {
        // If we're a search, let's start over:
        $title = sprintf( __( 'Search results for %s', 'rotorwash' ), '"' . get_search_query() . '"' );

        // Add a page number if we're on page 2 or more:
        if( $paged >= 2 )
        {
            $title .= " $separator " . sprintf( __( 'Page %s', 'rotorwash' ), $paged );
        }

        // Add the site name to the end:
        $title .= " $separator " . get_bloginfo( 'name', 'display' );

        return $title;
    }

    // Otherwise, let's start by adding the site name to the end:
    $title .= get_bloginfo( 'name', 'display' );

    // If we have a site description and we're on the home/front page, add the description:
    $site_description = get_bloginfo( 'description', 'display' );
    if( $site_description && ( is_home() || is_front_page() ) )
    {
        $title .= " $separator " . $site_description;
    }

    // Add a page number if necessary:
    if( $paged >= 2 || $page >= 2 )
    {
        $title .= " $separator " . sprintf( __( 'Page %s', 'rotorwash' ), max( $paged, $page ) );
    }

    // Return the new title to wp_title():
    return $title;
}
add_filter( 'wp_title', 'rw_filter_wp_title', 10, 2 );

/**
 * Adds Facebook Open Graph tags to the website using the wp_head action.
 *
 * Depending on what type of post/page we're displaying, different information will
 * be used. This makes for those pretty-looking shared links in the Facebook timeline.
 * 
 * @see http://codex.wordpress.org/Plugin_API/Action_Reference/wp_head
 * @since RotorWash 1.0
 *
 * @return void
 */
function add_fb_open_graph_tags()
{
	$locale    = get_locale(); // This avoids a warning in the Facebook URL linter
	$site_name = get_bloginfo('name'); // Loads the name of the website
	$fb_admins = "633550295"; // The Facebook ID of the site admin(s), separated by commas

	if( is_single() )
	{
        global $post; // Brings the post into the function scope
        if( get_the_post_thumbnail($post->ID, 'thumbnail') )
		{
			$thumbnail_id = get_post_thumbnail_id($post->ID);
			$thumbnail_object = get_post($thumbnail_id);
			$image = $thumbnail_object->guid;
		}
        else
        {
            $image = 'http://www.robkingfitness.com/wp-content/themes/copterTheme/images/RK_logo_footer.png';
        }

        // Gets entry-specific info for display
        $title       = get_the_title($post->ID);
        $url         = get_permalink($post->ID);
        $type        = "article";
   		$description = get_the_excerpt($post->ID);
   	}
   	else
   	{
   	    // For non-blog posts (pages, home page, etc.), we display website info only
   	    $title       = $site_name;
   	    $url         = site_url();
   	    $image       = 'http://www.robkingfitness.com/wp-content/themes/copterTheme/images/RK_logo_footer.png';
        $type        = "website";
        $description = get_bloginfo('description');
   	}

    // Output the OG tags directly
?>

<!-- Facebook Open Graph tags -->
<meta property="og:title"       content="<?php echo $title; ?>" />
<meta property="og:type"        content="<?php echo $type; ?>" />
<meta property="og:image"       content="<?php echo $image; ?>" />
<meta property="og:url"         content="<?php echo $url; ?>" />
<meta property="og:description" content="<?php echo $description ?>" />
<meta property="og:site_name"   content="<?php echo $site_name; ?>" />
<meta property="og:locale"      content="<?php echo $locale; ?>" />
<meta property="fb:admins"      content="<?php echo $fb_admins; ?>" />

<?php
}
add_action('wp_head', 'add_fb_open_graph_tags');

/**
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
 *
 * To override this in a child theme, remove the filter and optionally add
 * your own function tied to the wp_page_menu_args filter hook.
 *
 * @since RotorWash 1.0
 */
function rw_page_menu_args( $args )
{
    $args['show_home'] = true;
    return $args;
}
add_filter( 'wp_page_menu_args', 'rw_page_menu_args' );

/**
 * Sets the default post excerpt length to 100 characters.
 *
 * To override this length in a child theme, remove the filter and add your own
 * function tied to the excerpt_length filter hook.
 *
 * @since RotorWash 1.0
 * @return int
 */
function rw_excerpt_length( $length=100 )
{
    return $length;
}
add_filter( 'excerpt_length', 'rw_excerpt_length', 10, 1 );

/**
 * Returns a "Continue Reading" link for excerpts
 *
 * @since RotorWash 1.0
 * @return string "Continue Reading" link
 */
function rw_continue_reading_link()
{
    return ' <a href="'. get_permalink() . '" class="more-link">' . __( 'More', 'rotorwash' ) . '</a>';
}

/**
 * Replaces "[...]" (appended to automatically generated excerpts) with an ellipsis and rw_continue_reading_link().
 *
 * To override this in a child theme, remove the filter and add your own
 * function tied to the excerpt_more filter hook.
 *
 * @since RotorWash 1.0
 * @return string An ellipsis
 */
function rw_auto_excerpt_more(  )
{
    return '&hellip;' . rw_continue_reading_link();
}
add_filter( 'excerpt_more', 'rw_auto_excerpt_more' );

/**
 * Adds a pretty "Continue Reading" link to custom post excerpts.
 *
 * To override this link in a child theme, remove the filter and add your own
 * function tied to the get_the_excerpt filter hook.
 *
 * @since RotorWash 1.0
 * @return string Excerpt with a pretty "Continue Reading" link
 */
function rw_custom_excerpt_more( $output )
{
    if( has_excerpt() && ! is_attachment() )
    {
        $output .= rw_continue_reading_link();
    }
    return $output;
}
add_filter( 'get_the_excerpt', 'rw_custom_excerpt_more' );

if( !function_exists( 'rw_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own rw_comment(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since RotorWash 1.0
 */
function rw_comment( $comment, $args, $depth ) {
    $GLOBALS['comment'] = $comment;
    switch ( $comment->comment_type ) :
        case '' :
    ?>
    <li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
        <div id="comment-<?php comment_ID(); ?>">
        <div class="comment-author vcard">
            <?php echo get_avatar( $comment, 40 ); ?>
            <?php printf( __( '%s <span class="says">says:</span>', 'rotorwash' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
        </div><!-- .comment-author .vcard -->
        <?php if( $comment->comment_approved == '0' ) : ?>
            <em><?php _e( 'Your comment is awaiting moderation.', 'rotorwash' ); ?></em>
            <br />
        <?php endif; ?>

        <div class="comment-meta commentmetadata"><a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
            <?php
                /* translators: 1: date, 2: time */
                printf( __( '%1$s at %2$s', 'rotorwash' ), get_comment_date(),  get_comment_time() ); ?></a><?php edit_comment_link( __( '(Edit)', 'rotorwash' ), ' ' );
            ?>
        </div><!-- .comment-meta .commentmetadata -->

        <div class="comment-body"><?php comment_text(); ?></div>

        <div class="reply">
            <?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
        </div><!-- .reply -->
    </div><!-- #comment-##  -->

    <?php
            break;
        case 'pingback'  :
        case 'trackback' :
    ?>
    <li class="post pingback">
        <p><?php _e( 'Pingback:', 'rotorwash' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __('(Edit)', 'rotorwash'), ' ' ); ?></p>
    <?php
            break;
    endswitch;
}
endif;


/**
 * Register widgetized areas, including two sidebars and four widget-ready columns in the footer.
 *
 * To override rw_widgets_init() in a child theme, remove the action hook and add your own
 * function tied to the after_setup_theme hook.
 *
 * @since RotorWash 1.0
 * @uses register_sidebar
 */
function rw_widgets_init() {
    // Located at the top of the sidebar.
    register_sidebar( array(
        'name' => __( 'Sidebar', 'rotorwash' ),
        'id' => 'sidebar-widget-area',
        'description' => __( 'The primary widget area', 'rotorwash' ),
        'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
        'after_widget' => '</li>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ) );

    // Located in the footer.
    register_sidebar( array(
        'name' => __( 'Footer Widget Area', 'rotorwash' ),
        'id' => 'footer-widget-area',
        'description' => __( 'The footer widget area', 'rotorwash' ),
        'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
        'after_widget' => '</li>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ) );
}
/** Register sidebars by running rw_widgets_init() on the widgets_init hook. */
add_action('widgets_init', 'rw_widgets_init', 10);

/** Register custom post types by running rw_add_custom_post_types() on the init hook. */
add_action('init', 'rw_add_custom_post_types');

if( !function_exists('rw_add_custom_post_types') ):
/**
 * Register custom post types.
 *
 * To override rw_add_custom_post_types() in a child theme, remove the action hook and add your own
 * function tied to the init hook.
 *
 * @since RotorWash 1.0
 * @uses register_sidebar
 */
function rw_add_custom_post_types()
{
    // Add a register_post_type() call for each needed custom post type
    $labels = array(
            'name'                  => _x('Slides', 'General post type descriptor'),
            'singular_name'         => _x('Slide', 'Singular post type descriptor'),
            'add_new'               => _x('Add New', 'slide'),
            'add_new_item'          => __('Add New Slide'),
            'edit_item'             => __('Edit Slide'),
            'new_item'              => __('New Slide'),
            'all_items'             => __('All Slides'),
            'view_item'             => __('View Slide'),
            'search_items'          => __('Search Slides'),
            'not_found'             => __('No slides found'),
            'not_found_in_trash'    => __('No slides in the trash'),
            'parent_item_colon'     => '',
            'menu_name'             => 'Slides',
        );
    $args = array(
            'labels'                => $labels,
            'public'                => TRUE,
            'publicly_queryable'    => TRUE,
            'show_ui'               => TRUE,
            'show_in_menu'          => TRUE,
            'query_var'             => TRUE,
            'rewrite'               => TRUE,
            'capability_type'       => 'post',
            'has_archive'           => TRUE,
            'hierarchical'          => FALSE,
            'menu_position'         => NULL,
            'supports'              => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
            
        );
    register_post_type('book', $args);
}
endif;

if( ! function_exists( 'rw_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post—date/time and author.
 *
 * @since RotorWash 1.0
 */
function rw_posted_on() {
    printf( __( '<span class="%1$s">Posted on</span> %2$s <span class="meta-sep">by</span> %3$s', 'rotorwash' ),
        'meta-prep meta-prep-author',
        sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><span class="entry-date">%3$s</span></a>',
            get_permalink(),
            esc_attr( get_the_time() ),
            get_the_date()
        ),
        sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>',
            get_author_posts_url( get_the_author_meta( 'ID' ) ),
            sprintf( esc_attr__( 'View all posts by %s', 'rotorwash' ), get_the_author() ),
            get_the_author()
        )
    );
}
endif;

if( ! function_exists( 'rw_posted_in' ) ) :
/**
 * Prints HTML with meta information for the current post (category, tags and permalink).
 *
 * @since RotorWash 1.0
 */
function rw_posted_in( $show_tags=TRUE )
{
    $tag_list = get_the_tag_list( '', ', ' );

    if( $tag_list && $show_tags )
    {
        $posted_in = __( 'Category: %1$s Tags: %2$s.', 'rotorwash' );
    }
    elseif( is_object_in_taxonomy( get_post_type(), 'category' ) )
    {
        $posted_in = __( 'Category %1$s.', 'rotorwash' );
    }
    else
    {
        $posted_in = __( '', 'rotorwash' );
    }

    printf($posted_in, get_the_category_list( ', ' ), $tag_list);
}
endif;

if( !function_exists('rw_get_social_links') ):
/**
 * Outputs a list of social media links.
 * 
 * To edit these links, use the Links tab in the dashboard. Links must be 
 * categorized with "Social Links" to be retrieved. The links are ordered by 
 * rating, ascending. It's all the way at the bottom of the link editor.
 *
 * @since RotorWash 1.0
 */
function rw_get_social_links( $id="social-links", $class=NULL )
{
?>

<ul id="<?php echo $id; ?>"
    class="<?php echo $class; ?>">
<?php

$links = get_bookmarks(array('category_name' => 'Social Links', 'orderby'=>'rating'));
foreach( $links as $link ):
    $slug = strtolower(preg_replace('/[^\w-]/', '', $link->link_name));

?>
    <li class="<?php echo $slug; ?>">
        <a href="<?php echo $link->link_url; ?>" 
           data-window="new"><?php echo $link->link_name; ?></a>
    </li>
<?php endforeach; ?>

</ul>

<?php
}
endif;

function rw_add_fb_root(  )
{
    $fb_app_id = "121970801204701";
?>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=<?php echo $fb_app_id; ?>";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<?php
}
add_action('wp_footer', 'rw_add_fb_root');

function rw_enqueue_scripts(  )
{
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'rw_enqueue_scripts');