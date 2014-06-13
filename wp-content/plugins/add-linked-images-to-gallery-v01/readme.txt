=== Add Linked Images To Gallery ===
Contributors: bbqiguana 
Donate link: http://www.bbqiguana.com/donate/
Tags: images, gallery, photobloggers, attachments, photo, links, external, photographers, Flickr, save, download
Requires at least: 2.7
Tested up to: 3.3.1
Stable tag: 1.4

Makes local copies of all the linked images in a post, adding them as gallery attachments.

== Description ==

Create local copies of external images in the src attribute of img tags.  This plugin extracts a list of IMG tags in the post, saves copies of those images locally as gallery attachments on the post.

= Features =
* Finds all external images linked in the SRC attribute of IMG tags and makes local copies of those images
* Allows the SRC to be updated to point to those local copies
* Can be applied to posts in all categories, or only those selected
* Can be applied to all authors, or only selected authors

Administrator has the option to replace the external src with the url of the local copy. Another option allows the plugin to be applied to all external images, or only to those on Flickr.

This plugin is particularly useful for photobloggers, especially those who update using the mail2blog Flickr API.   The plugin will saved the linked image file from Flickr locally.

= Planned features: =
* Add internationalization support
* Integrate with Flickr API in order to allow always downloading the original image size regardless of which is linked
* Additional options to allow running the plugin only for specific users or categories

== Installation ==

1. Download the External Image Loader zip file.
2. Extract the files to your WordPress plugins directory.
3. Activate the plugin via the WordPress Plugins tab.

== Frequently Asked Questions ==

= How does this plugin work? =

The plugin examines the HTML source of your post when you save it, inspecting each IMG tag, and processing them according to the options you have selected.  

Under the default settings, it will find IMG tags with links to images on other web sites and copy those images to your web site, updating the IMG src to point to your copy.

= Is that illegal or unethical? =

I built this plugin for the purpose on one-click publishing of my photoblog. I publish my own photos (to which I have all rights) to Flickr, and then my plugin copies the file from Flickr's server to my own web server.

Yes, there are numerous was that this plugin could be used unethically, but there are just as many perfectly reasonable uses for it.  I leave it to you to make the right decision.

= How can I know that it is working? =

* Create a new post, and add a link to a test image (such as one on your Flickr account).
* Now click the "Save Draft" button.
* If your editor is in HTML mode, you will see that the SRC attribute has changed.
* If not, you can click on the Add Image icon and you will see a new image has been added to the Gallery for this post.

== Screenshots ==

none

== Changelog ==

= 1.4 = 
* Updated to fix a bug introduced with WordPress 3.1
* Default option is now "replace" rather than "custom tag"

= 1.3 =
* Added a function to process all posts in a database.
* Mime_type is parsed on incoming files

= 1.2 =
* Fixed a condition where images without a file extension were not processed

= 1.1 =
* Added a test for DOING_AUTOSAVE to prevent a dowload loop on autosaved drafts

= 1.0.1 = 
* Added require_once for necessary WP library functions

= 1.0 =
* Finally found the "WP_Error on line 48" issue, and I'm ready to call this a 1.0

= 0.9 =
* Replaced externimg_loadimage() function with a call to WordPress's media_handle_sideload()

= 0.8 =
* Fixes bad path, preventing imported images from showing up in WP media library

= 0.7 =
* Fixes a syntax error in creating the new attachment

= 0.6 =
* Suppresses safe_mode warnings from CURL
* Adds support for WordPress 2.9

= 0.5 =
* Fixes a bug that cause all img tags to be rewritten as the last matched image.

= 0.4 =
* Option added to option panel allowing the plugin to run only on posts in specific categories
* Option added to option panel allowing the plugin to run only on posts by specific authors

= 0.3 =
* Improved pattern matching for images
* 404 errors not processed
* Flickr "image-not-found" jpg not processed
* Improved local file naming
* Replace feature was replacing URL in entire text. Now only replaces in IMG src.
* Added feedback when options are saved.

= 0.2 =
* Added options panel
* User can apply plugin to all external images or choose only to apply to Flickr
* User can choose to either mark images by custom tag, or to replace image source
* Custom tag name is user-definable
* Improved regular expression matching

= 0.1 =
* Initial version.
