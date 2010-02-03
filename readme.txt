=== ClixCorp Post Category Excluder for WordPress ===
Contributors: DougRyan
Tags: post, exclusion, category, hidden, content, membership, exclude
Tested up to: 2.9.1
Stable tag: 1.0
Requires at least: 2.7
== Description ==
This plugin allows the author to exclude certain posts  from archive, 
tag, search and home page listings. Authors can also exclude posts to
appear ONLY to logged in users, thereby forcing membership.  The 
exclusion check boxes appear directly on the edit post page,  no need to
access the program separately from the admin panel.  This is what makes 
the plugin better than some of the other excluder plugins out there. 

The editing checkboxes on the post edit screen are: 
	Disable listing on home page; 
	Disable on tag listing; 
	Disable listing in archives; 
	Disable listing in search; 
	Disable listing for users not Logged In; 
	Disable but show available if Logged In. 

The last two options allow hidden content to exist in the system, where it will be displayed if the user is logged in.
On these posts that are excluded from listing or display, the author can specify:
	(a.) Disable from listing if not logged in, or, 
	(b.) Disable from listing if not logged in but let browser know it is there if they do log in.
	
In situation a, the post is not displayed (direct url will show a 404), and it is not listed in the menus, 
(unless you have a different plugin/template scheme that will override wp filters).

In situation b, the post will be replaced with a customized message (admin->tools) like 
'Sorry, this content is available to logged in users only'.  The links to the protected content will be visible.

This plugin is designed to be customized by the end user.  The code is simple and heavily documented, and 
uses only WordPress functions.  You should be using WP version 2.7 or greater for this plugin to work properly.

== Installation ==
1. Copy plugin folder (clix-uppe-post-cat-excluder) into wp-content\plugins\
2. Go To your wordpress dashboard and activate plugin ClixCorp Post Category Excluder for WordPress.
3. Plugin interface can be found in the Settings section.

== How to uninstall Wordpress Cleanup ==
1. Deactivate plugin ClixCorp Post Category Excluder for WordPress
2. Delete folder cleanup-wordpress from wp-content\plugins\

== Screenshots ==
1. Plugin Interface shown on a post edit screen.
2. Plugin Admin interface for editing the disabled content messages.

== Changelog ==
= 1.0 =
* First version.
