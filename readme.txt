=== Cross-Network posts ===
Contributors: DanielTulp
Donate link: 
Tags: network, mu, post, posts, blog, category, cross-network, other blog
Requires at least: 3.0
Tested up to: 3.5.1
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Embed a post or a category of posts that is within another website on the same Wordpress network (multisite). 
	
== Description ==
= Major overhaul =
As of version 2.0 CNP allows you to easily embed posts from other websites in the same Wordpress network with a shortcode builder that is available above your editor.

The manual way of entering the shortcode is still available, but you can chooses whichever you prefer.

Also included from version 2.0 is an options page where you set certain security measures:
1. You can restrict embedding of content to only the original author of the post (does not apply to administrators)
1. You can disallow other websites in the Wordpress network to embed posts from your website

= Usage = 	
This plugin is useful if you have a wordpress network (multisite) and you want to display (embed) a post or a category of posts that is on another website in the same network.

Use the shortcode builder above the editor or type the shortcode in manually: 
	
`[cnp blogid=1 postid=1]`

= Attributes =

__Mandatory__

**blogid** is the ID of the blog you wish to pull the post from (go to Network->Sites and hover over the site to view the ID).

Instead of **postid=** you can also use **catid=** to display all posts from a category. 

__Optional__
		
**header=1** -> header number (i.e. h1 ), no title if you set it to 0 (default is 2)

**excerpt=true** -> if you only want to display excerpts with read more links to the post on the other website

**numberofposts=5** -> for displaying a certain number of posts from a category (default is 5)

**titlelink=false** -> if you don't want the title to have a permalink (default is true)

__Working with other plugins__

If you want to display a plugin through a shortcode that is in one of the posts that you are displaying, make sure that the plugin is also active on the network site where you use CNP.

Note: not all plugins will work. Post about it if you find some that do and also that don't.

== Installation ==

1. install (or download zip and upload) and activate the plugin on the Plugins page
1. add shortcode `[cnp blogid=1 postid=1]` to page or post content manually or with the shortcode builder

== Screenshots ==

1. Set your personal options
2. Shortcode builder button

== Changelog ==

= 2.0 =
* added shortcode builder above Wordpress editor
* added options page
* added (security) restrictions

= 1.01 =
* Updated description on the plugin page in your WP network

= 1.0 =
* Initial release
