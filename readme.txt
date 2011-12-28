=== PhotoTools - Image Taxonomies ===
Contributors: padams
Donate link: http://example.com/
Tags: photos, images, taxonomies, Lightroom, meta, meta data
Requires at least: 3.2.1
Tested up to: 3.2.1
Stable tag: 1.0

This plugin extracts additional meta-data from the EXIF and XMP of uploaded images for use in populating a variety of photo specific taxonomies.

== Description ==

This plugin extracts XMP and EXIF meta-data from uploaded images for use in populating a variety of photo specific taxonomies such as "keywords", "people", "city", "state", "country", "camera", and "lens". Image meta-data embedded by Adobe Photoshop, Adobe Lightroom, and Capture One are supported.


== Installation ==

1. Upload the `photo-tools-image-taxonomies` plugin folder to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add the PhotoTools widgets sidebar by adding `<?php dynamic_sidebar('papt-image-sidebar'); ?>` to your attachment page template.
1. Populate this sidebar with PhotoTools Widgets from the 'Appearance > Widgets' menu in WordPress.

== Frequently Asked Questions ==

= A question that someone might have =

An answer to that question.

= What about foo bar? =

Answer to foo bar dilemma.

== Screenshots ==

1. Taxonomy fields added to image meta data admin panel.
2. This is the second screen shot

== Changelog ==

= 1.0 =
* A change since the previous version.
* Another change.

= 0.5 =
* List versions from most recent at top to oldest at bottom.

== Upgrade Notice ==

= 1.0 =
Upgrade notices describe the reason a user should upgrade.  No more than 300 characters.

= 0.5 =
This version fixes a security related bug.  Upgrade immediately.

== Arbitrary section ==

You may provide arbitrary sections, in the same format as the ones above.  This may be of use for extremely complicated
plugins where more information needs to be conveyed that doesn't fit into the categories of "description" or
"installation."  Arbitrary sections will be shown below the built-in sections outlined above.

== A brief Markdown Example ==

Ordered list:

1. Some feature
1. Another feature
1. Something else about the plugin

Unordered list:

* something
* something else
* third thing

Here's a link to [WordPress](http://wordpress.org/ "Your favorite software") and one to [Markdown's Syntax Documentation][markdown syntax].
Titles are optional, naturally.

[markdown syntax]: http://daringfireball.net/projects/markdown/syntax
            "Markdown is what the parser uses to process much of the readme file"

Markdown uses email style notation for blockquotes and I've been told:
> Asterisks for *emphasis*. Double it up  for **strong**.

`<?php code(); // goes in backticks ?>`