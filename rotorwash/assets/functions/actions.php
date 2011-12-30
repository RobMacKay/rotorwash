<?php

function rotor_enqueue_scripts(  )
{
    wp_register_script('twitter_widgets', 'http://platform.twitter.com/widgets.js', NULL, FALSE, TRUE);
}