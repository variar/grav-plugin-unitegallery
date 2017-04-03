# Grav Unitegallery Plugin

`Unitegallery` is a [Grav](http://github.com/getgrav/grav) Plugin that provides
[Twig](http://github.com/twigphp/Twig) extension for creating images gallery using [Unitegallery](http://unitegallery.net)
javascript library

# Installation

Installing the Unitegallery plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

## GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install unitegallery

This will install the Unitegallery plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/unitegallery`.

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

>> NOTE: When page with the gallery is loaded first time thumbnails for each new image are generated. This can be quite time consuming process. Consider loading page manually when new images are added, so that users of your site will not encounter long page load time.

To create the gallery you should call `unite_gallery` from twig template for desired page.
For example add template `gallery.html.twig` inside `<your_theme>/templates/modular` directory with simple content:
```
<div class="modular-row gallery-container {{ page.header.class }}">
	{{ unite_gallery(page.media.images) }}
</div>
```

Then create new subpage with name `gallery.md` inside your modular page and add some images so the structure looks like this:
```
user
|--pages
   |--page1
   |--page2
   |--page_with_gallery
      |--modular.md
      |--_pictures
         |--gallery.md
         |--image01.jpg
         |--image01.jpg.meta.yaml
         |--image02.jpg
         |--image02.jpg.meta.yaml
```

Content of `gallery.md` can be like this:
```
---
title: My fancy gallery
---
```

And `modular.md` simply includes child pages:
```
---
title: My Gallery Page

content:
  items: @self.modular
---
```

To pass custom gallery parameters modify twig template and pass them as json:
```
// with custom options (passed directly to unitegallery js function)
{{ unite_gallery(page.media.images, '{"gallery_theme":"tiles", "tiles_type":"justified"}') }}
```

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

# Issues with page caching
This plugin uses twig function to add assets (js and css) to the page.
This doesn't work very well with Grav caching system.
There are two workarounds:
 1. Disable twig caching for the gallery page.
    Adding `never_cache_twig: true` to `gallery.md` from example above should do the trick.
 2. Enabling `assets_in_meta` parameter for this plugin (either per page) or globally.
    This will save assets calculated during first twig processing and add them
    each time page is processed. This parameter is enabled by default.
    After switching it on cache for all gallery pages should be updated (either by cleaning all cache or by doing some changes to affected pages).
    For best perfomance consider disabling this mode globally and switching it on only for gallery pages.
    Using the example above `modular.md` should look like this:
    ```
    ---
    title: My Gallery Page

    content:
      items: @self.modular

    unitegallery:
      assets_in_meta: true
    ---
    ```

# Several galleries in one modular page
If several galleries are going to be used on the same page then optional third
argument should be passed to `unite_gallery` function to create `div` elements
with custom ids:
```
// with custom div id
{{ unite_gallery(page.media.images, '{"gallery_theme":"tiles", "tiles_type":"justified"}', 'gallery1') }}
{{ unite_gallery(page.media.images, '{}', 'gallery2') }}
```

Also custom id can be set in json options:
```
// with custom div id
{{ unite_gallery(page.media.images, '{"gallery_theme":"tiles", "grav_gallery_div_id":"gallery1"}') }}
```

This allows to create galleries from several subfolders of modular page:

```
user
|--pages
   |--page1
   |--page2
   |--page_with_gallery
      |--modular.md
      |--_photo1
      |  |--gallery.md
      |  |--image01.jpg
      |  |--image01.jpg.meta.yaml
      |  |--image02.jpg
      |  |--image02.jpg.meta.yaml
      |--_photo2
         |--gallery.md
         |--image01.jpg
         |--image01.jpg.meta.yaml
         |--image02.jpg
         |--image02.jpg.meta.yaml
```

To pass gallery ids from page header `gallery.html.twig` should be modified:
```
<div class="modular-row gallery-container {{ page.header.class }}">
	{{ unite_gallery(page.media.images, {"gallery_theme":"tiles"}, page.header.unitegallery.gallery_id) }}
</div>
```

Then in `gallery.md` files for individual galleries set gallery_id in page header:
```
---
title: My fancy gallery
unitegallery:
  gallery_id: gallery1
---
```

# Config Defaults

```
enabled: true
assets_in_meta: true
gallery_theme: default
gallery_div_id: unite-gallery
thumb_width: 600
thumb_height: 600
```
