=== WP Retina 2x Pro ===
Contributors: TigrouMeow
Tags: retina, images, image, responsive, gutenberg, lazysizes, lazy, attachment, media, high-dpi
Requires at least: 4.2
Tested up to: 5.1
Requires PHP: 7.0
Stable tag: 5.5.5

Make your website look beautiful and crisp on modern displays by creating and displaying retina images.

== Description ==

Make your website look beautiful and crisp on modern displays by creating and displaying retina images. More information and tutorial available one https://meowapps.com/plugin/wp-retina-2x/.

== Changelog ==

= 5.5.5 =
* Fix: Display Full-Size Retina uploader only if the option is active.

= 5.5.4 =
* Add: Filter for cropping plugins.

= 5.5.3 =
* Fix: Usage of Composer.
* Update: If available, will use the Full-Size Retina for generating Retina thumbnails.
* Fix: New version of HtmlDomParser.
* Update: New dashboard.

= 5.5.1 =
* Fix: Uploading a PNG as a Retina was turning its transparency into black.

= 5.5.0 =
* Fix: Now LazyLoad used with Keep SRC only loads one image, the right one (instead of two before). Thanks to Shane Bishop, the creator of EWWW (https://wordpress.org/plugins/ewww-image-optimizer/).

= 5.4.3 =
* Add: New hooks: wr2x_before_regenerate, wr2x_before_generate_thumbnails, wr2x_generate_thumbnails, wr2x_regenerate and wr2x_upload_retina.
* Fix: Issues where happening with a few themes (actually the pagebuilder they use) after the last update.
* Update: Lazysizes 4.0.4.

= 5.4.0 =
* Add: Direct upload of Retina for Full-Size.

= 5.2.9 =
* Add: New option to Regenerate Thumbnails.
* Fix: Tiny CSS fix, and update fix.

= 5.2.8 =
* Fix: Security update.
* Update: Lazysizes 4.0.3.

= 5.2.6 =
* Fix: Avoid re-generating non-retina thumbnails when Generate is used.
* Fix: Use ___DIR___ to include plugin's files.
* Fix: Better explanation.

= 5.2.4 =
* Fix: Sanitization to avoid cross-site scripting.

= 5.2.1 =
* Fix: SSL fix.
* Fix: When metadata is broken, displays a message.
* Fix: A few icons weren't displayed nicely.
* Update: From Lazysizes 3.0 to 4.0.1.
* Add: Option for forcing SSL Verify.

= 5.1.4 =
* Add: wr2x_retina_extension, wr2x_delete_attachment, wr2x_get_pathinfo_from_image_src, wr2x_picture_rewrite in the API.
