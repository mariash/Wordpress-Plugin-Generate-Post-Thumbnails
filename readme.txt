=== Generate Post Thumbnails ===
Contributors: marynixie
Donate link: http://wordpress.shaldybina.com/donate
Tags: thumbnails, thumbnail
Requires at least: 2.9
Tested up to: 2.9.2
Stable tag: trunk

Tool for mass generation of Wordpress posts thumbnails using the post images.

== Description ==

This plugin will generate post thumbnails using the post images.

Wordpress 2.9 introduced new feature Post Thumbnails, that allows to specify post thumbnails sizes in theme, assign thumbnails for each post and easily display post thumbnail of required size in theme. 

This plugin will be useful if you need automatically assign thumbnails for already existing blog posts using post images, that were uploaded at your Wordpress blog. This situation may occur if you decided to use this Wordpress feature in your theme either by upgrading your Wordpress to 2.9 or by upgrading your blog theme.

Related Links:

* <a href="http://wordpress.shaldybina.com/plugins/generate-post-thumbnails/" title="Generate Post Thumbnails Plugin for WordPress">Plugin Homepage</a>
* <a href="http://codex.wordpress.org/Post_Thumbnails" title="Wordpress page about Post Thumbnails feature">Wordpress Post Thumbnails feature page</a>
* <a href="http://markjaquith.wordpress.com/2009/12/23/new-in-wordpress-2-9-post-thumbnail-images/" title="New in WordPress 2.9: Post Thumbnail Images - Mark on WordPress">New in WordPress 2.9: Post Thumbnail Images &laquo;  Mark on WordPress</a>
* <a href="http://wordpress.org/extend/plugins/regenerate-thumbnails/" title="Regenerate Thumbnails plugin">Regenerate Thumbnails</a> - very useful plugin for changed size options of Wordpress attachments as well as already assigned thumbnails.

== Installation ==

1. Extract zip in the /wp-content/plugins/ directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Open the plugin management page, which is located under Tools -> Generate Thumbnails. If you get plugin warning, that means either your theme does not support Wordpress Post Thumbnails feature or your Wordpress version is lower than 2.9. See related links for more information.
1. Set overwrite parameter, if you want existing post thumbnails to be overwritten by generated thumbnails.
1. Set the number of the post image, that you want to be used as your post thumbnail.
1. Click on Generate Thumbnails and wait until process is finished.

== Frequently Asked Questions ==

= What images will be used as a thumbnail? =

Images that are shown in your blog post and saved as its attachments in Wordpress, i.e. uploaded directly to your blog. No support of external images.

= What is the Image Number parameter? =

Usually the first image of your blog post is considered as the main image that is used as thumbnail. But rarely the second blog post image may be the main. 

If there is no image for specified image number in the post, then no thumbnail will be stored for this post. If *Overwrite* parameter is checked, then if the post already has thumbnail that thumbnail will be removed. 

== Screenshots ==

1. Plugin management page

== Changelog ==

= 0.4.1 =
* Released plugin initial version

== Upgrade Notice ==

= 0.4.1 =
The first released version

