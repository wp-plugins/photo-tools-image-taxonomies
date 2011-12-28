=== PhotoTools - Image Taxonomies ===
Contributors: padams
Donate link: http://example.com/
Tags: photos, images, taxonomies, Lightroom, meta, meta data
Requires at least: 3.2.1
Tested up to: 3.2.1
Stable tag: 1.0

This plugin extracts additional meta-data from the EXIF and XMP of uploaded images for use in populating a variety of photo specific taxonomies.

== Description ==

This plugin extracts XMP and EXIF meta-data from uploaded images for use in populating a variety of photo specific WordPress taxonomies such as *keywords*, *people*, *city*, *state*, *country*, *camera*, and *lens*. Corresponding image meta-data embedded by Adobe Photoshop, Adobe Lightroom, and Capture One will automatically be populated into editable fields on WordPress's image attachment/edit forms.

The plugin also provides widget and template functions for displaying EXIF and taxonomy data on single image (attachment) page templates.


== Installation ==

1. Upload the `photo-tools-image-taxonomies` plugin folder to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add the PhotoTools widgets sidebar by adding `<?php dynamic_sidebar('papt-image-sidebar'); ?>` to your attachment page template.
1. Populate this sidebar with PhotoTools Widgets from the `Appearance > Widgets` menu in WordPress.

== Frequently Asked Questions ==

= Why do I need this plugin? =

You want an easy way to transfer and display the image meta-data that you added using programs such as Adobe Lightroom on your WordPress powered site.

== Screenshots ==

1. Taxonomy fields added to image attachment form.
2. Single Image EXIF Widget and Taxonomy Widget.

== Changelog ==

= 1.0 =

Initial version of plugin.