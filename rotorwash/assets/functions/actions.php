<?php
/**
 * RotorWash actions
 *
 * @package WordPress
 * @subpackage RotorWash
 * @since RotorWash 1.0
 */

/**
 * Adds Facebook root to the footer of the site.
 * 
 * @return void
 */
function rw_add_fb_root(  )
{
    $opts = get_option('rw_theme_settings');
    if( empty($opts['fb_app_id']) )
    {
        trigger_error("No Facebook App ID was supplied. Please update " . TEMPLATEPATH . "/assets/config.php");
    }
?>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=<?php echo $opts['fb_app_id']; ?>";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<?php
}
add_action('wp_footer', 'rw_add_fb_root');

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
    $opts = get_option('rw_theme_settings');
    if( empty($opts['fb_admins']) )
    {
        trigger_error("No Facebook Admin IDs were supplied. Please update " . TEMPLATEPATH . "/assets/config.php");
    }
    $locale    = get_locale(); // This avoids a warning in the Facebook URL linter
    $site_name = get_bloginfo('name'); // Loads the name of the website
    $fb_admins = $opts['fb_admins']; // The Facebook ID of the site admin(s), separated by commas

    if( is_single() )
    {
        global $post; // Brings the post into the function scope
        if( get_the_post_thumbnail($post->ID, 'thumbnail') )
        {
            $thumbnail_id = get_post_thumbnail_id($post->ID, 'thumbnail');
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
           $description = apply_filters('get_the_excerpt', get_post($post->ID)->post_content);
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
 * Registers scripts for the theme
 * 
 * @return void
 * @since RotorWash 1.0
 */
function rw_register_scripts(  )
{
    wp_register_script('twitter_widgets', 'http://platform.twitter.com/widgets.js', NULL, FALSE, TRUE);
}
add_action('wp_register_scripts', 'rw_register_scripts');

/**
 * Enqueues scripts for the theme
 * 
 * @return void
 * @since RotorWash 1.0
 */
function rw_enqueue_scripts(  )
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('twitter_widgets');
}
add_action('wp_enqueue_scripts', 'rw_enqueue_scripts');

/**
 * Adds the theme settings button to the dashboard
 */
function rw_create_menu_item(  )
{
    $page_title =  'Settings for ' . get_current_theme();
    $btn_text = get_current_theme() . ' Settings';
    $btn_icon = get_bloginfo('template_directory') . '/assets/images/copter-wp-admin-icon.png';
    add_menu_page($page_title, $btn_text, 'administrator', __FILE__, 'rw_settings_page', $btn_icon);

    add_action( 'admin_init', 'register_custom_settings' );
}
add_action('admin_menu', 'rw_create_menu_item');

function register_custom_settings(  )
{
	register_setting('rw-theme-settings', 'rw_theme_settings');

	add_settings_section('rw-facebook-settings', 'Facebook Settings', 'rw_fb_settings_text', 'rw-theme-settings');

    add_settings_field( 'fb_app_id', 'Facebook App ID', 'rw_fb_app_id', 'rw-theme-settings', 'rw-facebook-settings', array('label_for'=>'fb_app_id'));
    add_settings_field( 'fb_admins', 'Facebook Admins', 'rw_fb_admins', 'rw-theme-settings', 'rw-facebook-settings', array('label_for'=>'fb_admins'));
}

function rw_fb_settings_text(  )
{
    echo '<p>Facebook settings. These make sure the "like" buttons work as expected.</p>'
        . '<p><a href="https://developers.facebook.com/apps/">Register your site with Facebook to get its app ID.</a></p>'
        . '<p>To get the Facebook admin ID(s), go to '
        . '<a href="https://graph.facebook.com/copterlabs">https://graph.facebook.com/copterlabs</a> '
        . 'and replace "copterlabs" with your Facebook username(s). Multiple values must be comma-separated.</p>';
}

function rw_fb_app_id(  )
{
    $opts = get_option('rw_theme_settings');
    echo '<input id="fb_app_id" name="rw_theme_settings[fb_app_id]" size="40" type="text" value="' . $opts['fb_app_id'] . '" />';
}

function rw_fb_admins(  )
{
    $opts = get_option('rw_theme_settings');
    echo '<input id="fb_admins" name="rw_theme_settings[fb_admins]" size="40" type="text" value="' . $opts['fb_admins'] . '" />';
}

function rw_settings_page(  )
{
    require_once TEMPLATEPATH . '/assets/includes/rotorwash-settings.php';
}
