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
        ]);
    }

    public function onTwigExtensions()
    {
         require_once(__DIR__ . '/twig/unitegallery_extension.php');
         $this->grav['twig']->twig->addExtension(new \UniteGalleryTwigExtension());
    }
}
