<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the wordpress construct of pages
 * and that other 'pages' on your wordpress site will use a
 * different template.
 *
 * @package WordPress
 * @subpackage RotorWash
 * @since RotorWash 1.0
 */

get_header();

if( have_posts() ):
    while( have_posts() ):
        the_post();

?>

            <article class="post">
                <h2><?php the_title(); ?></h2>

<?php 

the_content();
wp_link_pages(array('before' => '<p class="post-pagination">' . __('Pages:', 'rotorwash'), 'after' => '</p>'));
edit_post_link(__('Edit', 'rotorwash'), '', '');
comments_template('', TRUE);

?>

            </article><!-- end .post -->
<?php 

    endwhile;
endif;

get_sidebar();
get_footer();
