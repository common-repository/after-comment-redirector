<?php
if ( !defined( 'ABSPATH') ) exit; //Exit if accessed directly
/*
Plugin Name: After Comment Redirector
Plugin URI:  https://www.pascalprohl.de
Description: Redirect to custom page after commenting for all or new commentators.
Version:     1.2
Author:      Seitenhelden
Author URI:  https://www.pascalprohl.de
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

function wpacr_on_uninstall()
{
 // Important: Check if the file is the one that was registered during the uninstall hook.
    if ( __FILE__ != WP_UNINSTALL_PLUGIN ) 
           return;
	delete_option( 'wpacr_settings' );
}

/* Installation and Removal */
register_activation_hook( __FILE__, 'wpacr_on_activation' );
register_deactivation_hook( __FILE__, 'wpacr_on_deactivation' );
register_uninstall_hook( __FILE__, 'wpacr_on_uninstall' );

add_action( 'admin_menu', 'wpacr_add_admin_menu' );
add_action( 'admin_init', 'wpacr_settings_init' );
add_filter('comment_post_redirect', 'wpacr_comment_post_redirect', 10, 2);

function wpacr_comment_post_redirect ($url, $comment) {
	// Get stored plugin settings 
	$options = get_option( 'wpacr_settings' );

	if ( isset ($options) && ($options['wpacr_url'] != "")) {
		if ($options['wpacr_new_commentators'] == "1") {
			$has_approved_comment = get_comments( array( 'author_email' => $comment->comment_author_email, 'number' => 1, 'status' => 'approve' ) );
		}
		if ( empty( $has_approved_comment ) ) {
			$url = $options['wpacr_url'];
		}
	}
	return $url;
}

function wpacr_add_admin_menu(  ) { 
	add_options_page( 'After Comment Redirector', 'After Comment Redirector', 'manage_options', 'after_comment_redirector', 'wpacr_options_page' );
}

function wpacr_settings_init(  ) { 

	register_setting( 'pluginPage', 'wpacr_settings' );

	add_settings_section(
		'wpacr_pluginPage_section', 
		__( 'Settings', 'wordpress' ), 
		'wpacr_settings_section_callback', 
		'pluginPage'
	);

	add_settings_field( 
		'wpacr_url', 
		__( 'URL', 'wordpress' ), 
		'wpacr_url_render', 
		'pluginPage', 
		'wpacr_pluginPage_section'  
	);

	add_settings_field( 
		'wpacr_new_commentators', 
		__( 'Redirect only new commentators ', 'wordpress' ), 
		'wpacr_new_commentators_render', 
		'pluginPage', 
		'wpacr_pluginPage_section' 
	);
}

function wpacr_url_render(  ) { 

	$options = get_option( 'wpacr_settings' );
	?>
	<input type='text' name='wpacr_settings[wpacr_url]' size= "35" value='<?php echo esc_attr($options['wpacr_url']); ?>'>
	<?php
 
}


function wpacr_new_commentators_render(  ) { 

	$options = get_option( 'wpacr_settings' );
	?>
	<input type='checkbox' name='wpacr_settings[wpacr_new_commentators]' <?php checked( $options['wpacr_new_commentators'], 1 ); ?> value='1'>
	<?php

}


function wpacr_settings_section_callback(  ) { 

	echo __( 'On this page you can chose the URL you want to redirect to. You can also decide whether you want to redirect all commentators or just the ones that have no approved comment yet.', 'wordpress' );

}


function wpacr_options_page(  ) { 

	?>
	<form action='options.php' method='post'>

		<h2>After Comment Redirector</h2>

		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>

	</form>
	<?php

}
?>