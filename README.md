# Grav Unitegallery Plugin

`Unitegallery` is a [Grav](http://github.com/getgrav/grav) Plugin that provides
[Twig](http://github.com/twigphp/Twig) extension for creating images gallery using [Unitegallery](http://unitegallery.net)
javascript library

# Installation

## Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `unitegallery`.
You can find these files on [GitHub](https://github.com/getgrav/grav-plugin-unitegallery).

You should now have all the plugin files under

    /your/site/grav/user/plugins/unitegallery

>> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav), and a theme to be installed in order to operate.

# Usage

The `unitegallery` provides new [Twig](http://github.com/twigphp/Twig) extension that adds function `unite_gallery`
to be used in templates. Pass images collection as firts argument and gallery
options (json string) as optional second argument.

This functions goes through each image in passed collection, generates thumbnails (which are cached),
collects images metadata and outputs special div as required by [Unitegallery](http://unitegallery.net)
javascript library. Also all css and js files needed for selected theme are added to page assets.

Output html example:

```html
  <div id="unite-gallery" style="display:none;">
		<img alt="Image 1 Title" src="/images/6/6/8/a/2/668a2df0c6571575ae7dd9216234864a4f7c4bc0.jpg"
			data-image="/images/9/f/a/5/d/9fa5d6c0eb6c711fd4a9f58f9c13c3f191b66cb4.jpg"
			data-description="Image 1 Description">
	</div>

  <script type="text/javascript">
    jQuery(document).ready(function(){
      jQuery("#unite-gallery").unitegallery({"tiles_type":"justified","gallery_theme":"tiles"});
    });
  </script>
```

Grav images [metadata files](https://learn.getgrav.org/content/media#metafiles) are used to fill title and description using following properties mapping:
* meta.alt_text is used for alt attribute
* meta.description is used for data-description attribute

Examples:
```
// with default plugin configuration
{{ unite_gallery(page.media.images) }}

// with custom options (passed directly to unitegallery js function)
{{ unite_gallery(page.media.images, '{"gallery_theme":"tiles", "tiles_type":"justified"}') }}
```

>> NOTE: In order for this plugin to work never_cache_twig should be set to true for page where `unite_gallery` function is used (hope to relax this requirement in future)

# Config Defaults

```
enabled: true
gallery_theme: default
gallery_div_id: unite-gallery
thumb_width: 600
thumb_height: 600
```
