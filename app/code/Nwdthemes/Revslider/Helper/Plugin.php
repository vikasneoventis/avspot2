<?php

namespace Nwdthemes\Revslider\Helper;

use \Magento\Framework\UrlInterface;
use \Magento\Framework\App\Filesystem\DirectoryList;
use \Nwdthemes\Revslider\Helper\Framework;

class Plugin extends \Magento\Framework\App\Helper\AbstractHelper {

	const WP_PLUGIN_DIR = 'revslider/plugins/';

    protected $_framework;
    protected $_storeManager;
    protected $_queryHelper;
    protected $_curlHelper;
    protected $_filesystemHelper;
    protected $_imagesHelper;
    protected $_resource;
    protected $_googleFonts;

    protected static $directory;
    protected static $storeManager;

    protected $_logger;
    protected static $logger;

    private $_plugins = null;
    private $_activePlugins = null;

	/**
	 *	Constructor
	 */

	public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Nwdthemes\Revslider\Helper\Framework $framework,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Nwdthemes\Revslider\Helper\Query $queryHelper,
        \Nwdthemes\Revslider\Helper\Curl $curlHelper,
        \Nwdthemes\Revslider\Helper\Filesystem $filesystemHelper,
        \Nwdthemes\Revslider\Helper\Images $imagesHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Nwdthemes\Revslider\Model\Revslider\GoogleFonts $googleFonts
    ) {
        $this->_framework = $framework;
        $this->_storeManager = $storeManager;
        $this->_queryHelper = $queryHelper;
        $this->_curlHelper = $curlHelper;
        $this->_filesystemHelper = $filesystemHelper;
        $this->_imagesHelper = $imagesHelper;
        $this->_resource = $resource;
        $this->_googleFonts = $googleFonts;

        self::$directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        self::$storeManager = $storeManager;

        $this->_logger = $context->getLogger();
        self::$logger = $this->_logger;

        parent::__construct($context);
	}

	/**
	 *	Checks if addon activated
	 *
	 *  @param  string  $plugin
	 *	@return	boolean
	 */

	public function is_plugin_active($plugin) {
		return $this->isPluginActive($plugin);
	}

	/**
	 *	Checks if addon not activated
	 *
	 *  @param  string  $plugin
	 *	@return	boolean
	 */

	public function is_plugin_inactive($plugin) {
		return ! $this->isPluginActive($plugin);
	}

	/**
	 *	Activate plugin
	 *
	 *  @param  string  $plugin
	 *	@return	boolean
	 */

	public function activate_plugin($plugin) {
		return $this->activatePlugin($plugin);
	}

	/**
	 *	Deactivate plugin
	 *
	 *  @param  string  $plugin
	 *	@return	boolean
	 */

	public function deactivate_plugins($plugin) {
		return $this->deactivatePlugin($plugin);
	}

	/**
	 *	Get plugins
	 *
	 *	return	boolean
	 */

    public function get_plugins() {
        return $this->getPlugins();
	}

	/**
	 *	Get plugins url
	 *
	 *  @param  string  $file
	 *  @param  string  $plugin
	 *	@return	string
	 */

	public function plugins_url($file, $plugin) {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            . self::WP_PLUGIN_DIR
            . basename($plugin, '.php')
            . '/'
            . $file;
	}

	/**
	 *	Get plugin dir path
	 *
	 *  @param  string  $plugin
	 *	@return	string
	 */

	public function plugin_dir_path($plugin) {
        return self::getPluginDir() . basename($plugin, '.php') . '/';
	}

    /**
     *  Get plugin dir
     */

    public static function getPluginDir() {
        return self::$directory->getAbsolutePath() . self::WP_PLUGIN_DIR;
    }

    /**
     *  Get installed plugins list
     *
     *  @return array
     */

    public function getPlugins() {
        if (is_null($this->_plugins)) {
            $this->_plugins = $this->_scanPlugins();
        }
        return $this->_plugins;
    }

    /**
     *  Check if plugin is active
     *
     *  @param  string  $plugin
     *  @return boolean
     */

    public function isPluginActive($plugin) {
        return in_array($plugin, $this->getActivePlugins());
    }

    /**
     *  Get list of active plugins
     *
     *  @return array
     */

    public function getActivePlugins() {
        if (is_null($this->_activePlugins)) {
            $activePlugins = $this->_framework->get_option('active_plugins');
            $this->_activePlugins = $activePlugins ? $activePlugins : array();
        }
        return $this->_activePlugins;
    }

    /**
     *  Activate plugin
     *
     *  @param  string  $plugin
     *  @return boolean
     */

    public function activatePlugin($plugin) {
        $activePlugins = $this->getActivePlugins();
        if ( ! in_array($plugin, $activePlugins)) {
            $activePlugins[] = $plugin;
            $this->_updateActivePlugins($activePlugins);
        }
        return true;
    }

    /**
     *  Deactivate plugin
     *
     *  @param  string  $plugin
     *  @return boolean
     */

    public function deactivatePlugin($plugin) {
        $activePlugins = $this->getActivePlugins();
        foreach ($activePlugins as $key => $_plugin) {
            if ($plugin == $_plugin) {
                unset($activePlugins[$key]);
            }
        }
        $this->_updateActivePlugins($activePlugins);
        return true;
    }

    /**
     *  Load active plugins
     */

    public function loadPlugins() {
        foreach ($this->getActivePlugins() as $plugin) {
            if (file_exists(self::getPluginDir() . $plugin)) {
                include_once self::getPluginDir() . $plugin;
            }
        }
        $this->_framework->do_action(
            'plugins_loaded',
            $this->_framework,
            $this->_queryHelper,
            $this->_curlHelper,
            $this->_filesystemHelper,
            $this->_imagesHelper,
            $this->_resource,
            $this->_googleFonts
        );
    }

    /**
     *  Find installed plugins
     *
     *  @return array
     */

    private function _scanPlugins() {
        $path = self::getPluginDir();
        $plugins = array();
        foreach (glob($path . '*' , GLOB_ONLYDIR) as $dir) {
            $dirName = basename($dir);
            $fileName = $dirName . '.php';
            $filePath = $dir . '/' . $fileName;
            if (file_exists($filePath)) {
                $plugin = array();
                $fileContent = file_get_contents($filePath);
                $fileContent = strstr($fileContent, '*/', true);
                foreach (explode("\n", $fileContent) as $line) {
                    $parts = explode(': ', $line);
                    if (count($parts) == 2) {
                        switch (trim(strtolower($parts[0]))) {
                            case 'plugin name' : $key = 'Name'; break;
                            case 'plugin uri' : $key = 'PluginURI'; break;
                            case 'description' : $key = 'Description'; break;
                            case 'author' : $key = 'Author'; break;
                            case 'version' : $key = 'Version'; break;
                            case 'author uri' : $key = 'AuthorURI'; break;
                            default: $key = str_replace(' ', '', trim($parts[0])); break;
                        }
                        $plugin[$key] = trim($parts[1]);
                    }
                }
                if (isset($plugin['Name']) && isset($plugin['Version'])) {
                    $plugin['Network'] = false;
                    $plugin['Title'] = $plugin['Name'];
                    $plugin['AuthorName'] = $plugin['Author'];
                    $plugins[$dirName . '/' . $fileName] = $plugin;
                }
            }
        }
		return $plugins;
    }

    /**
     *  Update active plugins
     *
     *  @param  array   $plugins
     */

    private function _updateActivePlugins($plugins) {
        $this->_activePlugins = $plugins;
        $this->_framework->update_option('active_plugins', $plugins);
    }

}