<?php
/*
Plugin Name: ClixCorp Post Category Excluder for WordPress
Plugin URI: http://clixcorp.com/wordpress/plugins/UPE
Description: Provides a row of checkboxes on the post edit screen to exclude them from archive, tag, search, home page, and logged in status. Can save a lot of time from editing template files. This well documented plug-in is easy to modify for other exclusions.
Version: 1.0
Author: ClixCorp
Author URI: http://clixcorp.com

Copyright (c) 2010
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/*
	$bail_out function credit:
	Simon Wheatley - Exclude Pages from Navigation
	Author URI: http://simonwheatley.co.uk/wordpress/
	Thanks Simon!
*/
/* ***********************************************************************************************
// INITIALIZATION  (delete the asterisk lines if you don't like them, it helps to separate functions).
*/
//
// 'the_posts' - This filter takes the array $posts->xx, and checks to see if they are listed as
// excluded for the particular situation, archive, tag, search, home, and is_user_logged_in().
add_filter( 'the_posts', 'clix_uppe_post_exclude',1 );

// 'the_content' - Filters out content that has been flagged for is_user_logged_in() only, if user
// is not actually logged in.
add_filter( 'the_content', 'clix_uppe_post_display' );

// 'admin_menu' - Add a custom meta box to the edit screen. This displays a check box with the options available.
add_action( 'admin_menu', 'clix_uppe_add_meta_box' );

// 'save_post' - Add this action to save the options that have been checked in the meta-boxes for the post.
add_action( 'save_post', 'clix_uppe_save_postdata' );

// Get the text to display in the event there are posts that can only be viewed by logged
// in users, and they aren't.
$clix_rss_fail=get_option( 'clix_rss_fail' );
$clix_content_fail=get_option( 'clix_content_fail' );

/*
	Notes on disable_unlesslogin, and disable_unlessshow:

	If a post has the item-> Disable unless logged in checked, then two things happen:
	1.  If the actual url is entered for the disabled post, a 404 error page will be displayed.
	2.  The disabled post links will NOT be displayed by wordpress.

	Now, if a post has the item-> Disable but show if logged in checked, then two different things happen:
	1.  If the post url is clicked on, the message defined by 'clix_content_fail' (or rss_fail) will be
		displayed to the browser.  Something like 'Sorry, but you must be logged in to view this content.'
	2.  The disabled posts WILL be displayed as links in your site.
*/

/* ***********************************************************************************************
	clix_uppe_post_display( $content )
	This function stops content from being displayed if user is not logged in, and it is required.
	Note this function is different from that incorporated within WP posts requiring a specific
	password - per - post.
*/
function clix_uppe_post_display( $content ){
	$bail_out = ( ( defined( 'WP_ADMIN' ) && WP_ADMIN == true ) || ( strpos( $_SERVER[ 'PHP_SELF' ], 'wp-admin' ) !== false ) );
	if($bail_out){
		return $content;
		}
	global $id, $clix_rss_fail, $clix_content_fail;
	if( !is_user_logged_in() && is_single() ){
		if( is_feed() && get_post_meta( $id, '_clix_uppe_disable_unlessshow', true )==1 ){
			$content=$clix_rss_fail;
			}
		elseif( get_post_meta( $id, '_clix_uppe_disable_unlessshow', true )==1 ){
			$content=$clix_content_fail;
			}
		}
	return $content;
}

/* ***********************************************************************************************
	clix_uppe_post_exclude
	This function filters the post links (and excludes them) that will appear on the archive,
	tag, search, and home pages/categories.  It also will exclude from visibility if the
	user isn't logged in, and it is required.  If we are editing as admin, then we bail out.
*/
function clix_uppe_post_exclude( $posts ){
	$bail_out = ( ( defined( 'WP_ADMIN' ) && WP_ADMIN == true ) || ( strpos( $_SERVER[ 'PHP_SELF' ], 'wp-admin' ) !== false ) );
	if($bail_out){
		return $posts;
		}
	if( is_category() ){
		$uppefield='_clix_uppe_disable_archive';
		}
	elseif( is_tag() ){
		$uppefield='_clix_uppe_disable_tag';
		}
	elseif( is_search() ){
		$uppefield='_clix_uppe_disable_search';
		}
	elseif( is_home() ){
		$uppefield='_clix_uppe_disable_home';
		}
	//create an array to hold the posts we want to show
	$new_posts = array();
	//loop through all the post objects
	foreach( $posts as $post ){
	   	if(get_post_meta( $post->ID, $uppefield, true)==1 ){
	   		continue;
	   		}
	   	elseif( !is_user_logged_in() && get_post_meta( $post->ID, '_clix_uppe_disable_unlesslogin', true )==1){
	   		continue;
	   		}
	   	$new_posts[] = $post;
		}
	return $new_posts;
}

/* ***********************************************************************************************
	clix_uppe_add_meta_box()
	Adds a section to the full size Post edit screens, this 'registers' the box code - clix_uppe_meta_box
	for display on the post edit screen.
*/
function clix_uppe_add_meta_box() {
	if( function_exists('add_meta_box') ) {
		add_meta_box( 'clix_uppe_meta_box', 'Clix Category Exclusion', 'clix_uppe_meta_box', post, 'side' );
  	}
}

/* ***********************************************************************************************
	clix_uppe_meta_box()
	Displays metabox for post edit
*/
function clix_uppe_meta_box(){
	global $post;
	// Use nonce for verification
	echo '<input type="hidden" name="clix_uppe_nonce" id="clix_uppe_nonce" value="' .
	wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
	// Output checkboxes
	$options = array(
		'disable_home' 			=> 'Disable Listing on Home Page',
		'disable_tag' 			=> 'Disable on Tag Listings',
		'disable_archive' 		=> 'Disable Listing in Archives',
		'disable_search' 		=> 'Disable Listing in Search',
		'disable_unlesslogin' 	=> 'Disable Listing for Users Not Logged In',
		'disable_unlessshow'	=> 'Disable but show available if Logged In'
		);
	foreach( $options as $option=>$legend ){
?>
<label for="clix_uppe_<?php echo $option; ?>">
	<input type="checkbox" name="_clix_uppe_<?php echo $option; ?>" id="clix_uppe_<?php echo $option; ?>" <?php
		if ( get_post_meta( $post->ID, "_clix_uppe_$option", true ) == '1' )
			echo ' checked="checked"';
	?>/>
	<?php echo $legend; ?>
</label>
<br /><?php
	}
}

/* ***********************************************************************************************
	clix_uppe_save_postdata( $post_id )
	Once the submit post button is clicked, this function is called to
	record the exclusion settings to the post_meta fields in the db.
*/
function clix_uppe_save_postdata( $post_id ){
  	// use nonces to ensure the values came from our own edit screen.
	if ( !wp_verify_nonce( $_POST['clix_uppe_nonce'], plugin_basename(__FILE__) ) ){
		return $post_id;
		}
	// see if this is a page, if so then get out.
	if( 'page'==$_POST['post_type'] ){
      	return $post_id;
  		}
	// check to see if the current user has edit authority.
  	elseif( !current_user_can( 'edit_post',$post_id ) ){
      	return $post_id;
  		}

 	// It makes no sense to have both of these set, because disable_unlesslogin will always override.
	if( !empty( $_POST[_clix_uppe_disable_unlesslogin] ) && !empty( $_POST[_clix_uppe_disable_unlessshow] ) ){
		unset( $_POST[_clix_uppe_disable_unlessshow] );
		}

 	// all the option fields we are checking...
	$fields=array(
		'disable_home',
		'disable_tag',
		'disable_archive',
		'disable_search',
		'disable_unlesslogin',
		'disable_unlessshow'
		);

	// update/create or remove post_meta data for the specific exclusion specified by the author.
	foreach($fields as $field){
  	  	if( !empty($_POST['_clix_uppe_'.$field]) ){
			update_post_meta( $post_id, "_clix_uppe_$field", '1' );
	  		}
	  	else{
			delete_post_meta( $post_id, "_clix_uppe_$field" );
	  		}
		}
	return true;
}

/* ***********************************************************************************************
	clix_uppe_admin_menu_register()
	This adds the options page setting and the Tools-> Clix UPPE Settings menu item.
*/

add_action('admin_menu', 'clix_uppe_admin_menu_register');

function clix_uppe_admin_menu_register() {

	//create options menu
	add_options_page('Clix Post Exclude Settings', 'Clix Post Exclude', 'administrator', __FILE__, 'clix_uppe_admin_menu_page',plugins_url('/images/icon.png', __FILE__));

	//call register settings function
	add_action( 'admin_init', 'register_clix_uppe_admin_settings' );
}

/* ***********************************************************************************************
	register_clix_uppe_admin_settings()
	sets up the wp-options db for our two variables,
	which are simply messages to users telling them they need to login to view content.
*/

function register_clix_uppe_admin_settings() {
	//register our settings
	register_setting( 'clix_uppe_settings', 'clix_content_fail' );
	register_setting( 'clix_uppe_settings', 'clix_rss_fail' );
}

/* ***********************************************************************************************
	clix_uppe_admin_menu_page()
	This is the actual html echoed to the browser when our admin link is clicked on.
	Provides the admin a way of updating the two option fields, the display text to show when
	a user needs to be logged in to view the content that is available to them.  This only
	applies to posts that are marked to let them know content is there, but they must be logged
	in first to view it.
*/

function clix_uppe_admin_menu_page() {
?>
<div class="wrap">
<h2>ClixCorp Ultimate Post Category Excluder</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'clix_uppe_settings' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Display this when not logged in &amp; content is restricted: but yet we let them know it is available.</th>
        <td><textarea name="clix_content_fail" rows='10' cols='80'><?php echo get_option('clix_content_fail'); ?></textarea></td>
        </tr>

        <tr valign="top">
        <th scope="row">Ditto for the RSS Feed.</th>
        <td><textarea name="clix_rss_fail" rows='3' cols='80'><?php echo get_option('clix_rss_fail'); ?></textarea></td>
        </tr>
    </table>
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
</form>
<br />
Please visit <a href="http://clixcorp.com/wordpress/plugins/UPE" target='_blank'>ClixCorp.Com</a> for updates,
suggestions, or to report any problems you may be having with this plug-in.
</div>
<?php }
?>