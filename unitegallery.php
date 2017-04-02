<?php
namespace Grav\Plugin;
use Grav\Common\Grav;
use Grav\Common\Plugin;

class UniteGalleryPlugin extends Plugin
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
        ];
    }

    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        $this->enable([
                'onTwigExtensions'    => ['onTwigExtensions', 0],
                'onPageInitialized'    => ['onPageInitialized', 0],
        ]);
    }

    public function onTwigExtensions()
    {
         require_once(__DIR__ . '/twig/unitegallery_extension.php');
         $this->grav['twig']->twig->addExtension(new \UniteGalleryTwigExtension());
    }

    public function onPageInitialized()
    {
      $page = $this->grav['page'];

      $config = $this->mergeConfig($page);
      if (!$config->get('assets_in_meta', true))
        return;

      $meta = [];

      // Initialize all page content up front before Twig happens
      if (isset($page->header()->content['items'])) {
          foreach ($page->collection() as $item) {
              $item->content();
              $item_meta = $item->getContentMeta('unitegallery_assets');
              if ($item_meta) {
                  $meta = array_merge_recursive($meta, $item_meta);
              }
          }
      }
      $page->content();

      // get the meta and check for assets
      $page_meta = $page->getContentMeta('unitegallery_assets');
      if ($page_meta) {
          $meta = array_merge_recursive($meta, $page_meta);
      }

      if (!empty($meta)) {
        foreach ($meta['js'] as $js) {
          $this->grav['assets']->addJs($js);
        }
        foreach ($meta['css'] as $css) {
          $this->grav['assets']->addCss($css);
        }
      }
    }
}
