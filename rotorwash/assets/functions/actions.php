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
        trigger_error(
                'No Facebook App ID was supplied. Add this on the "' 
                . get_current_theme() 
                . '" Settings page in the Dashboard.'
            );
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
function rw_add_fb_og_tags()
{
    $opts = get_option('rw_theme_settings');
    if( empty($opts['fb_admins']) )
    {
        trigger_error(
                'No Facebook Admin IDs were supplied. Add this on the "' 
                . get_current_theme() 
                . '" Settings page in the Dashboard.'
            );
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
            $image = get_bloginfo('template_directory') . '/assets/images/rotorwash-default-image.jpg';
        }

        $excerpt = !empty($post->post_excerpt) ? $post->post_excerpt : apply_filters('get_the_excerpt', $post->post_content);

        // Gets entry-specific info for display
        $title       = $post->post_title;
        $url         = get_permalink($post->ID);
        $type        = "article";
        $description = trim(strip_tags($excerpt));
    }
    else
    {
        // For non-blog posts (pages, home page, etc.), we display website info only
        $title       = $site_name;
        $url         = site_url();
        $image       = get_bloginfo('template_directory') . '/assets/images/rotorwash-default-image.jpg';
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
add_action('wp_head', 'rw_add_fb_og_tags');

/**
 * Adds the HTML5shiv script to the head
 * 
 * @return void
 * @since RotorWash 1.0.1
 */
function rw_add_html5shiv(  )
{

?>

<!--[if lt IE 9]>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

<?php

}
add_action('wp_head', 'rw_add_html5shiv');

/**
 * Enqueues scripts for the theme
 * 
 * @return void
 * @since RotorWash 1.0
 */
function rw_enqueue_scripts(  )
{
    wp_enqueue_script('jquery');

    wp_register_script('twitter_widgets', 'http://platform.twitter.com/widgets.js', NULL, FALSE, TRUE);
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

    // Facebook Stuff
	add_settings_section('rw-facebook-settings', 'Facebook Settings', 'rw_fb_settings_text', 'rw-theme-settings');
    add_settings_field( 'fb_app_id', 'Facebook App ID', 'rw_fb_app_id', 'rw-theme-settings', 'rw-facebook-settings', array('label_for'=>'fb_app_id'));
    add_settings_field( 'fb_admins', 'Facebook Admins', 'rw_fb_admins', 'rw-theme-settings', 'rw-facebook-settings', array('label_for'=>'fb_admins'));

    // PayPal Donation Stuff
    add_settings_section('rw-paypal-settings', 'PayPal Donation Button Settings', 'rw_paypal_settings_text', 'rw-theme-settings');
    add_settings_field('paypal_title', 'Donation Button Title', 'rw_paypal_title', 'rw-theme-settings', 'rw-paypal-settings', array('label_for'=>'paypal_title'));
    add_settings_field('paypal_addr', 'PayPal Email', 'rw_paypal_addr', 'rw-theme-settings', 'rw-paypal-settings', array('label_for'=>'paypal_addr'));
    add_settings_field('paypal_item', 'PayPal Item Description', 'rw_paypal_item', 'rw-theme-settings', 'rw-paypal-settings', array('label_for'=>'paypal_item'));
    add_settings_field('paypal_currency', 'Choose Your Currency', 'rw_paypal_currency', 'rw-theme-settings', 'rw-paypal-settings', array('label_for'=>'paypal_currency'));
}

function rw_fb_settings_text(  )
{
    echo '<p>Facebook settings. These are for comment administration and other good stuff.</p>'
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

function rw_paypal_settings_text(  )
{
    echo '<p>These settings are required if you plan to place a donation button on the site.</p>'
        . '<p>The PayPal Email is where donations will be sent. This should be tied to a valid PayPal account.</p>'
        . '<p>The post ID from which the donation was made is used as the item number for the sake of tracking.</p>'
        . '<p>Supply a title for the form to tell the reader <em>why</em> they should donate (i.e. <em>Buy Me a Cup of Coffee</em>)</p>'
        . '<p>The item description shows up on the PayPal checkout page.</p>';
}

function rw_paypal_addr(  )
{
    $opts = get_option('rw_theme_settings');
    $paypal_addr = isset($opts['paypal_addr']) ? $opts['paypal_addr'] : '';
    echo '<input id="paypal_addr" name="rw_theme_settings[paypal_addr]" size="40" type="text" value="' . $paypal_addr . '" />';
}

function rw_paypal_item(  )
{
    $opts = get_option('rw_theme_settings');
    $paypal_item = isset($opts['paypal_item']) ? $opts['paypal_item'] : '';
    echo '<input id="paypal_item" name="rw_theme_settings[paypal_item]" size="40" type="text" value="' . $paypal_item . '" />';
}

function rw_paypal_currency(  )
{
    $opts = get_option('rw_theme_settings');
    $paypal_currency = isset($opts['paypal_currency']) ? $opts['paypal_currency'] : '';

    $supported_currencies = array(
            'USD' => '$',
            'AUD' => '$',
            'BRL' => 'R$',
            'GBP' => '£',
            'CZK' => '',
            'DKK' => '',
            'EUR' => '€',
            'HKD' => '$',
            'HUF' => '',
            'ILS' => '₪',
            'JPY' => '¥',
            'MXN' => '$',
            'TWD' => 'NT$',
            'NZD' => '$',
            'NOK' => '',
            'PHP' => 'P',
            'PLN' => '',
            'SGD' => '$',
            'SEK' => '',
            'CHF' => '',
            'THB' => '฿',
        );

?>
    <select id="paypal_currency" name="rw_theme_settings[paypal_currency]">
<?php foreach( $supported_currencies as $cur=>$sym ): ?>
        <option value="<?php echo $cur; ?>" 
                title="<?php echo $sym; ?>"<?php echo $cur===$paypal_currency ? ' selected="selected"' : ''; ?>><?php echo $cur; ?></option>
<?php endforeach; ?>
    </select>

<?php

}

function rw_paypal_title(  )
{
    $opts = get_option('rw_theme_settings');
    $paypal_title = isset($opts['paypal_title']) ? $opts['paypal_title'] : '';
    echo '<input id="paypal_title" name="rw_theme_settings[paypal_title]" size="40" type="text" value="' . $paypal_title . '" />';
}

function rw_settings_page(  )
{
    require_once TEMPLATEPATH . '/assets/includes/rotorwash-settings.php';
}
