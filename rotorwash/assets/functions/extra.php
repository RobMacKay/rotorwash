<?php
/**
 * Miscellaneous st
 *
 * @package WordPress
 * @subpackage Epikness
 * @since Epikness 1.0
 */
function rotor_social_sharing( $permalink=NULL, $su=TRUE, $tw=TRUE, $gp=TRUE, $fb=TRUE )
{
    if( $permalink===NULL )
    {
        trigger_error('No value supplied for <code>$permalink</code>.');
        return;
    }
?>
<span class="rw-social-sharing">

<?php if( $su ): ?>
    <!-- StumbleUpon -->
    <span class="stumble">
        <script src="http://www.stumbleupon.com/hostedbadge.php?s=2&amp;r=<?php echo $permalink; ?>"></script>
    </span>

<?php
endif;

if( $tw ):
    wp_enqueue_script('twitter_widgets');
?>
    <!-- Tweet Button -->
    <a href="http://twitter.com/share" class="twitter-share-button" 
       data-count="none" data-href="<?php echo $permalink; ?>">Tweet</a>
    <script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>

<?php
endif;

if( $gp ):
?>
    <!-- Google +1 -->
    <span class="gplus"><g:plusone size="medium" href="<?php the_permalink(); ?>"></g:plusone></span>

<?php

endif;
if( $fb );

?>
    <!-- Facebook Like Button -->
    <iframe src="http://www.facebook.com/plugins/like.php?href=<?php echo urlencode(get_permalink()); ?>&amp;layout=button_count&amp;show_faces=true&amp;width=80&amp;action=like&amp;colorscheme=light&amp;height=21"
            scrolling="no" frameborder="0"
            style="border:none; overflow:hidden; width:80px; height:21px;"
            allowTransparency="true" class="facebook">
    </iframe>

<?php endif; ?>
</span><!-- end .rw-social-sharing -->

<?php
}