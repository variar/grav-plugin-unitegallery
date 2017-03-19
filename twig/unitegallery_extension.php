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
     * @return string
     */
    public function uniteGallery(array $images, $options = '{}')
    {
      $gallery_theme = null;

      $gallery_options = json_decode($options);
      if (property_exists($gallery_options, 'gallery_theme')) {
        $gallery_theme = $gallery_options->gallery_theme;
      }
      else {
        $gallery_theme = $this->gallery_theme;
        is_null($gallery_theme) && $gallery_theme = 'default';

        $gallery_options->gallery_theme = $gallery_theme;
      }

      $this->addAssets($gallery_theme);

      $gallery_div_id = $this->gallery_div_id;
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

      $this->grav['assets']
                    ->addJs($assets_path . 'js/unitegallery.min.js')
                    ->addCss($assets_path . 'css/unite-gallery.css')
                    ->addJs($theme_assets_prefix . '.js')
                    ->addCss($theme_assets_prefix . '.css');

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
