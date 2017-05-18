<?php
/**
 * @author Anton Filimonov <anton.filimonov@gmail.com>
 */
use Grav\Common\Grav;
use Grav\Common\Page\Medium\ImageFile;
use Grav\Common\Page\Medium\ImageMedium;
use Gregwar\Image\Image;

class UniteGalleryTwigExtension extends \Twig_Extension
{
    /** @var Grav */
    protected $grav;
    protected $gallery_theme;
    protected $gallery_div_id;
    protected $thumb_width;
    protected $thumb_height;

    protected $locator;
    protected $assets;

    public function __construct()
    {
        $this->grav = Grav::instance();
        $this->locator = $this->grav['locator'];
        $this->assets = $this->grav['assets'];

        $this->loadParamsFromConfig([
            'gallery_theme' => 'gallery_theme',
            'gallery_div_id' => 'gallery_div_id',
            'thumb_width' => 'thumb_width',
            'thumb_height' => 'thumb_height',
        ], 'plugins.unitegallery.');
    }

    public function getName() {
       return 'UniteGalleryTwigExtension';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('unite_gallery', [$this, 'uniteGallery']),
        ];
    }

    /**
     * @param ImageMedium[] $images
     * @param string $options
     * @param string $custom_gallery_id
     * @return string
     */
    public function uniteGallery(array $images, $options = '{}', $custom_gallery_id = null)
    {
      $gallery_theme = null;

      $page_gallery_theme = null;
      $page_header = $this->grav["page"]->header();
      if(property_exists($page_header, "unitegallery.merged")) {
        $page_gallery_theme = $page_header->{"unitegallery.merged"}->get("gallery_theme");
      }

      $gallery_options = json_decode($options);
      if (property_exists($gallery_options, 'gallery_theme')) {
        $gallery_theme = $gallery_options->gallery_theme;
      }
      else {
        $gallery_theme = $page_gallery_theme;
        is_null($gallery_theme) && $gallery_theme = $this->gallery_theme;
        is_null($gallery_theme) && $gallery_theme = 'default';

        $gallery_options->gallery_theme = $gallery_theme;
      }

      $this->addAssets($gallery_theme);

      $gallery_div_id = $custom_gallery_id;
      if (is_null($gallery_div_id) && property_exists($gallery_options, 'grav_gallery_div_id')) {
        $gallery_div_id = $gallery_options->grav_gallery_div_id;
      }
      is_null($gallery_div_id) && $gallery_div_id = $this->gallery_div_id;
      is_null($gallery_div_id) && $gallery_div_id = "unite-gallery";

      return $this->buildImagesDiv($images, $gallery_div_id)
              . $this->buildGalleryScript($gallery_options, $gallery_div_id);
    }

    /**
     * @param string $gallery_theme
     * @return $this
     */
    protected function addAssets($gallery_theme)
    {
      $assets_path = 'plugin://unitegallery/vendor/unitegallery/';
      $theme_assets_prefix = $assets_path . 'themes/' . $gallery_theme . '/ug-theme-'. $gallery_theme;

      $jsAssets = [$assets_path . 'js/unitegallery.min.js', $theme_assets_prefix . '.js'];
      $cssAssets = [$assets_path . 'css/unite-gallery.css', $theme_assets_prefix . '.css'];

      // saving assets in page meta to use in pageInitialzied event hook
      // for cached pages
      $page = $this->grav['page'];
      $pageMeta = $page->getContentMeta('unitegallery_assets');
      if (empty($pageMeta)) {
        $pageMeta = ['js'=> [], 'css' => []];
      }

      foreach ($jsAssets as $js) {
        $this->grav['assets']->addJs($js);
        array_push($pageMeta['js'], $js);
      }
      foreach ($cssAssets as $css) {
        $this->grav['assets']->addCss($css);
        array_push($pageMeta['css'], $css);
      }

      $page->addContentMeta('unitegallery_assets', $pageMeta);

      return $this;
    }

    /**
     * @param ImageMedium[] $images
     * @return string
     */
    protected function buildImagesDiv(array $images, $gallery_div_id = 'unite-gallery')
    {
      $output = '<div id="' . $gallery_div_id . '" style="display:none;">' . PHP_EOL;

      $cachePath = $this->locator->findResource('cache://images', true);
      foreach ($images as $image) {
          $thumbImagePath = Image::open($image->get('filepath'))
            ->setCacheDir($cachePath)
            ->setActualCacheDir($cachePath)
            ->cropResize($this->thumb_width, $this->thumb_height)
            ->jpeg();

          $thumbMedium = \Grav\Common\Page\Medium\MediumFactory::fromFile($thumbImagePath);

          $meta = $image->meta();

          $output .= '<img alt="' . $meta['alt_text'] . '"';
          $output .= ' data-description="' . $meta['description'] . '"';
          $output .= ' src="' . $thumbMedium->url() . '"';
          $output .= ' data-image="' . $image->url() . '"';
          $output .= '>' . PHP_EOL;
      }

      $output .= '</div>' . PHP_EOL;
      return $output;
    }

    /**
     * @param object $gallery_options
     * @param string $gallery_div_id
     * @return string
     */
    protected function buildGalleryScript($gallery_options, $gallery_div_id = 'unite-gallery')
    {
      return '<script type="text/javascript">
              jQuery(document).ready(function(){
                jQuery("#' . $gallery_div_id . '").unitegallery('.json_encode($gallery_options).');
              });
        </script>' . PHP_EOL;
    }

    /**
     * @param array $mapParams
     * @param string $configPrefix
     * @return $this
     */
    protected function loadParamsFromConfig(array $mapParams, $configPrefix) {
        foreach ($mapParams as $nameConfigParam => $nameParam) {
            $this->{$nameParam} = $this->grav['config']->get($configPrefix . $nameConfigParam);
        }
        return $this;
    }
}
