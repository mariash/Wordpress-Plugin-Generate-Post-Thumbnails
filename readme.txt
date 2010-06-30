=== Generate Post Thumbnails ===
Contributors: marynixie
Donate link: http://wordpress.shaldybina.com/donate
Tags: thumbnails, thumbnail
Requires at least: 2.9
Tested up to: 2.9.2
Stable tag: 0.6

Tool for mass generation of Wordpress posts thumbnails using the post images.

== Description ==

This plugin will generate post thumbnails using the post images.

Wordpress 2.9 introduced new feature Post Thumbnails, that allows to specify post thumbnails sizes in themes, assign thumbnails for each post and easily display post thumbnail of required size in theme. 

This plugin will be useful if you need to assign post thumbnails for already existing blog posts using post images.By default it takes the first post image uploaded on server or externally hosted and assigns it as post thumbnail. Interface is based on Ajax to prevent timeout issues.

Related Links:

* <a href="http://wordpress.shaldybina.com/plugins/generate-post-thumbnails/" title="Generate Post Thumbnails Plugin for WordPress">Plugin Homepage</a>

== Installation ==

1. Extract zip in the /wp-content/plugins/ directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Open the plugin management page, which is located under Tools -> Generate Thumbnails. If you get plugin warning, that means either your theme does not support Wordpress Post Thumbnails feature or your Wordpress version is lower than 2.9. See related links for more information.
1. Set overwrite parameter, if you want existing post thumbnails to be overwritten by generated thumbnails.
1. Set the number of the post image, that you want to be used as your post thumbnail.
1. Click on Generate Thumbnails and wait until process is finished.

== Frequently Asked Questions ==

= What image will be used as post thumbnail? =

You can specify image number that will be used as post thumbnail. By default it takes the first image in the post body. If this image was uploaded on server plugin assigns it as a post thumbnail. If the image is externally hosted, plugin will upload it on server, attach to post and assign as thumbnail.

If there is no image for specified image number in the post, then no thumbnail will be stored for this post. If *Overwrite* parameter is checked, then if the post already has thumbnail old thumbnail will be removed. 

== Screenshots ==

1. Plugin management page

== Changelog ==

= 0.6 =
* Uploading of externally hosted images tries several methods for different configurations
* Relative paths support fixed

= 0.5 =
* Added support of externally hosted images

= 0.4.1 =
* Released plugin initial version

== Upgrade Notice ==

= 0.6 =
This version uses different methods to upload externally hosted images for different configurations and supports relative paths to images

= 0.5 =
This version supports externally hosted images

= 0.4.1 =
The first released version

