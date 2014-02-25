=== PhotoPress - Image Taxonomies ===
Contributors: padams
Donate link: http://www.photopressdev.com
Tags: photos, images, taxonomies, Lightroom, meta, meta data, image taxonomies, taxonomies, photo meta-data, Adobe Lightroom, CaptureOne, digital asset managers, photo catalogs
Requires at least: 3.2.1
Tested up to: 3.5
Stable tag: 1.9.1

This plugin extracts EXIF and XMP meta-data of uploaded images for use in populating a variety of photo specific taxonomies.

== Description ==

This plugin extracts a full range of XMP, IPTC, and EXIF meta-data from uploaded images for use in populating a variety of photo specific WordPress taxonomies such as *keywords*, *people*, *city*, *state*, *country*, *camera*, and *lens*. Corresponding image meta-data embedded by Adobe Photoshop, Adobe Lightroom, and Capture One will automatically be populated into editable fields on WordPress's image attachment/edit forms.

= Features Include: =

* Extract EXIF meta-data (aperture, shutter speed, ISO, caption, etc.).
* Extract XMP/IPTC meta-data (title, keywords, camera, lens, Country, state, city, etc.).
* Automatically populates several custom image taxonomies (Camera, Lens, Country, State, City, Keywords, People).
* Image/Attachment page Widget for displaying EXIF meta-data
* Image/Attachment page Widget for displaying XMP/IPTC meta-data
* Template functions for displaying meta-data

= Premium Support =
The PhotoPress team does not provide support for this plugin on the WordPress.org forums. One on one email support is available to users that purchase one of our [Premium Support Plans](http://www.photopressdev.com).  

= The Guide To WordPress For Photographers =
For more information on ways to use PhotoPress and other plugins to build a photpgraphy website check out the [WordPress For Photographers e-Book](http://wpphotog.com/product/the-guide-to-wordpress-for-photographers/ "WordPress For Photographers").

== Installation ==

1. Upload the `photo-tools-image-taxonomies` plugin folder to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add the PhotoPress widgets sidebar by adding `<?php dynamic_sidebar('papt-image-sidebar'); ?>` to your attachment page template.
1. Populate this sidebar with PhotoPress Widgets from the `Appearance > Widgets` menu in WordPress.

== Frequently Asked Questions ==

= Why do I need this plugin? =

You want an easy way to transfer and display the image meta-data that you added using programs such as Adobe Lightroom on your WordPress powered website.

== Screenshots ==

1. Taxonomy fields added to image attachment form.
2. Single Image EXIF Widget and Taxonomy Widget.

== Changelog ==

= 1.9 =

Change taxonomy query to use post_status of 'all' to fix conflict with WP menu system which is also a taxonomy.

= 1.8 = 

Fixing broken widget admin control params
Fixing various php notices that were causing issues for some users under WP 3.5.

= 1.7 =

Fixes php warner for missing variable.
Properly sets exifwidget ID.

= 1.6 =

Fix case where taxonomy term changes made fromthe edit attachment page were not working under WP 3.5
Fix case where ALT text changes were not populating correctly and being lost on re-edit.
General plugin code cleanup and doco

= 1.5 =

Added support for showing taxonomy columns on the Media Library screen in WP 3.5.
Added a new taxonomy for describing print sizes that are availabel for an image.

= 1.4 =

Added explicit term count update callback to fix bug where counts were not updating.

= 1.3 =

Adding fix for showing images on taxonomy pages in themese that use the default loop query.

= 1.2 =

Switched to using file paths instead of attachment urls to read image file. Fixes problems with web hosters that have disabled php's allow_url_fopen directive. 

= 1.1 =

Name change to PhotoPress - Image Taxonomies

= 1.0 =

Initial version of plugin.
