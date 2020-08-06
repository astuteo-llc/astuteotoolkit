<?php
/**
 * Astuteo Toolkit plugin for Craft CMS 3.x
 *
 * Various tools that we use across client sites. Only useful for Astuteo projects
 *
 * @link      https://astuteo.com
 * @copyright Copyright (c) 2018 Astuteo
 */

namespace astuteo\astuteotoolkit\twigextensions;

use astuteo\astuteotoolkit\AstuteoToolkit;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * @author    Astuteo
 * @package   AstuteoToolkit
 * @since     1.0.0
 */
class AstuteoToolkitTwigExtension extends AbstractExtension
{
    private $base_path;
    // Public Methods
    // =========================================================================

    /**
     * @return string The extension name
     */
    public function getName()
    {
        return 'AstuteoToolkit';
    }

    /**
     * Returns an array of Twig filters, used in Twig templates via:
     *      <link rel="stylesheet" media="screen" href="{{ '/site-assets/css/global.css' | astuteoRev }}"/>
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            new TwigFilter('astuteoRev', [$this, 'astuteoRev']),
        ];
    }

    /**
     * Returns an array of Twig functions, used in Twig templates via:
     *
     *      {% set this = someFunction('something') %}
     *
    * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('astuteoRev', [$this, 'astuteoRev']),
        ];
    }

    /**
     * Our function called via Twig; it can do anything you want
     *
     * @param $file
     * @return string
     */
    public function astuteoRev($file)
    {
        static $manifest = null;
        $asset_path = AstuteoToolkit::$plugin->getSettings()->assetPath;
        $path           = $this->_preparePath($file, $asset_path);
        $manifest_path  = $_SERVER['DOCUMENT_ROOT'];
        $manifest_path .= $asset_path . '/rev-manifest.json';

        if (is_null($manifest) && file_exists($manifest_path)) {
            $manifest = json_decode(file_get_contents($manifest_path), true);
        }
        if (isset($manifest[$path])) {
            $path = $manifest[$path];
        }
        $path = $this->_addBasePath($path);
        return $asset_path . $path;
    }
    private function _addBasePath($path)
    {
        return $this->base_path . $path;
    }

    private function _preparePath($path, $asset_path) {
        $updatePath = str_replace($asset_path, '', $path);
        $updatePath = $this->_stripBasePath($updatePath);
        return $updatePath;
    }

    private function _stripBasePath($path)
    {
        if (substr($path, 0, 1) === '/') {
            $path = substr($path, 1);
        }
        return $path;
    }
}
