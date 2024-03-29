<?php
/**
 * RotorWash filters
 *
 * @package WordPress
 * @subpackage RotorWash
 * @since RotorWash 1.0
 */

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
 * Allows for excerpt formatting outside the loop
 * 
 * For whatever reason, the WP core doesn't allow trimming of 
 * content outside the loop. Also, allowing get_the_excerpt()
 * outside of the loop has been deprecated. What this means to
 * me is that I can either add this filter with duplicate code
 * or create an unecessary loop for pulling a one-off excerpt.
 * 
 * Way to go, WP. You ruined Christmas.
 * 
 * @param string $text  The text to be trimmed
 * @return string       The trimmed text
 */
function rw_trim_excerpt( $text='' )
{
	$text = strip_shortcodes( $text );
	$text = apply_filters('the_content', $text);
	$text = str_replace(']]>', ']]&gt;', $text);
	$excerpt_length = apply_filters('excerpt_length', 55);
	$excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
	return wp_trim_words( $text, $excerpt_length, $excerpt_more );
}
add_filter('wp_trim_excerpt', 'rw_trim_excerpt');

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

if( !function_exists('rw_continue_reading_link') ):
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
endif;

/**
 * Replaces "[...]" (appended to automatically generated excerpts) with 
 * an ellipsis and rw_continue_reading_link().
 *
 * Additionally, this will remove the "read more" link from any excerpts 
 * generated outside the loop.
 *
 * To override this in a child theme, remove the filter and add your own
 * function tied to the excerpt_more filter hook.
 *
 * @since RotorWash 1.0
 * @return string An ellipsis
 */
function rw_auto_excerpt_more(  )
{
    return in_the_loop() ? '&hellip;' . rw_continue_reading_link() : '&hellip;';
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
