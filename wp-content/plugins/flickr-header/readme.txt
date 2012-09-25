=== Flickr Header ===
Contributors: ownlocal, mltsy
Tags: flickr, header, custom
Tested up to: 3.4.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Choose a Creative Commons photo from Flickr, crop it, and use it as your header.
Attribution is added automatically!

== Description ==

This WordPress plugin allows you to choose a photo from Flickr's collection of
Creative Commons licensed photos, crop it, and use it as the header for your
WordPress site.  You must setup your own Flickr API key to use it.

This plugin is still in development, and has only been tested with the
TwentyEleven theme.  There are several features that are not implemented,
including deactivating a Flickr header.  As it is my first wordpress plugin, I
expect it will break for some people. If it does, please create an issue
ticket in the GitHub repository, where this project is maintained:

https://github.com/ownlocal/flickr-header

Or even better - submit a pull request! ;)

== Installation ==

1. Install this plugin from the 'Plugins > Install' page in WordPress.
2. Activate the plugin for your site.
3. Update your header template to support Flickr Header...

To use it, simply add `<?php flickr_header_html(); ?>` in your header template
to insert the header.

It is possible to use this plugin along-side custom-headers (so you can choose
which you want to use at any time).  Similarly to the custom-header in WP core,
there is a functionto test whether a Flickr header image is selected:
`get_flickr_header_url()`, which can be used to test the existence of a Flickr
header, and display it if it exists, or else use the WP custom header.

Here is the recommended template code:

    if ( get_flickr_header_url() ) {
      flickr_header_html();
    } else {
      //Typical custom-header code:
      $header_image = get_header_image();
      if ( $header_image ) :
      // ...
    }

== Usage ==

To use the plugin, you must have a flickr API key.  Sign up for one at:
http://www.flickr.com/services/apps/create/apply

You must decide whether you will be using the plugin for commercial or
non-commercial putposes, and choose the corresponding API key.

Once your template is updated and you have your API key:

### 1. Enter your API key
#### Option A) Regular Wordpress Installation
From your site's Dashboard, go to "Appearance > Flickr Header", enter your API key and click "Update API key".

#### Option B) Multisite Installation
From the Network Dashboard, click "Settings > Flickr Header", enter your API key and hit "Update Key"

### 2. Select an image
1. Go to "Appearance > Flickr Header" from your site's dashboard
2. Type a term in the search box to search for an image
3. Click on an image to select it and hit "Save Changes"
4. Adjust the crop box to your hearts desire, and hit "Update Crop"
5. Load your site, and behold the beauty... or else, submit a bug report ;)


== Changelog ==

= 0.3 =
* Added a network-wide API key setting
* Changed author and copyright to OwnLocal

= 0.2 =
* Added cropping
* Finished initial attribution functionality
* Added necessary plugin readme file and comments

= 0.1 =
* Initial admin, Flickr search and header display functionality

