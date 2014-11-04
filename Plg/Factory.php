<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Plg;

/**
 * Plugin Hook Controller
 *
 *
 * Another example I liked is on this forum: http://www.sitepoint.com/forums/showthread.php?379440-Creating-dynamic-plugins-for-PHP-Classes
 * @link http://knowledgebox.blogspot.com.au/2008/06/do-it-yourselft-plugins.html
 * @package Plg
 */
class Factory extends \Tk\Object
{
    /**
     * @var \plg\Factory
     */
    static $instance = null;

    /**
     * Active plugins
     * @var array
     */
    protected $activePlugins = array();




    /**
     * Get an instance of this object
     *
     * @return Factory
     */
    static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * constructor
     *
     */
    private function __construct()
    {
        $this->getConfig()->nset('plg.path', $this->getConfig()->getSitePath().'/plugin');
        $this->getConfig()->nset('plg.url', $this->getConfig()->getSiteUrl().'/plugin');
        $this->getConfig()->nset('plg.table', 'plugin');


        $this->initModules();
        $this->setupTable();
        $this->activePlugins = $this->initActivePlugins();

    }

    /**
     * Registration adds the plugin to the list of plugins, and also
     * includes it's code into our runtime.
     *
     */
    protected function initActivePlugins()
    {
        $available = $this->getAvailablePlugins();
        $active = array();
        foreach ($available as $pluginName) {
            if ($this->isActive($pluginName)) {
                $class = $this->makePluginClassname($pluginName);
                $plug = new $class();
                if (!$plug instanceof Iface) {
                    throw new Exception('Plugin class not an instance of \Plg\Iface: ' . $class);
                }
                if (method_exists($plug, 'init')) {
                    $plug->getInfo();
                    $plug->init();
                    \Tk\Log\Log::write($plug->getClassName().'::init()');
                }
                $active[$pluginName] = $plug;
            }
        }
        return $active;
    }


    protected function initModules()
    {
        $dispatcher = $this->getConfig()->getDispatcherStatic();

        $dispatcher->add('/admin/plugin/manager.html', '\Plg\Module\Manager');
        $dispatcher->add('/admin/_dev/pluginHookList.html', '\Plg\Module\HookList');

    }

    /**
     * setupTable
     *
     * @return void
     * @development Should use the migration script
     */
    protected function setupTable()
    {
        $db = $this->getConfig()->getDb();
        $tbl = $this->getTableName();
        if ($db->tableExists($tbl)) return;
        $sql = file_get_contents(dirname(dirname(__FILE__)).'/sql/500-install.sql');
        $sql = str_replace('`plugin', '`'.$tbl, $sql);
        $db->multiQuery($sql);
    }



    /**
     * getTableName
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->getConfig()->get('plg.table');
    }

    /**
     * getPluginPath
     *
     * @return string
     */
    public function getPluginPath()
    {
        return $this->getConfig()->get('plg.path');
    }

    /**
     * getPluginUrl
     *
     * @return string
     */
    public function getPluginUrl()
    {
        return $this->getConfig()->get('plg.url');
    }

    /**
     * Get Plugin instance
     * Can only be called if the plugin is active
     * Returns null otherwise...
     *
     * @param $pluginName
     * @return \Plg\Iface
     */
    public function getPlugin($pluginName)
    {
        if (isset($this->activePlugins[$pluginName]))
            return $this->activePlugins[$pluginName];
    }

    /**
     * Get Plugin Meta Data
     *
     * @param $pluginName
     * @return stdClass
     */
    public function getPluginInfo($pluginName)
    {
        $file = $this->getPluginPath().'/'.$pluginName.'/composer.json';
        return json_decode(file_get_contents($file));
    }


    /**
     * getAvailablePlugins
     *
     * @return array
     */
    public function getAvailablePlugins()
    {
        $fileList = array();
        if (is_dir($this->getPluginPath())) {
            $fileList = scandir($this->getPluginPath());
            foreach ($fileList as $i => $plugPath) {
                if (preg_match('/^(\.|_)/', $plugPath) || !is_dir($this->getPluginPath().'/'.$plugPath)) {
                    unset($fileList[$i]);
                }
            }
        }
        return array_merge($fileList);
    }

    /**
     *
     * @param string $pluginName
     * @return bool
     */
    public function isActive($pluginName)
    {
        $db = $this->getConfig()->getDb();
        $pluginName = preg_replace('/[^a-zA-Z0-9_-]/', '', $pluginName);
        $pluginName = $db->quote($pluginName);
        $sql = <<<SQL
SELECT * FROM `plugin` WHERE `name` = $pluginName
SQL;
        vd($sql);
        $res = $db->query($sql);
        if ($res->rowCount() > 0) {
            return true;
        }
        return false;
    }

    /**
     * Activate and install the plugin
     * Calling the plugin activate method
     *
     * @param string $pluginName
     * @throws Exception
     */
    public function activatePlugin($pluginName)
    {
        if ($this->isActive($pluginName))
            throw new Exception ('Plugin currently active.');
        $class = $this->makePluginClassname($pluginName);
        $plugin = new $class();

        $plugin->activate($this);

        $db = $this->getConfig()->getDb();
        $pluginName = preg_replace('/[^a-zA-Z0-9_-]/', '', $pluginName);
        $pluginName = $db->quote($pluginName);
        $version = $plugin->getInfo()->version;
        $version = $db->quote($version);

        $sql = <<<SQL
INSERT INTO `plugin` VALUES (NULL, $pluginName, $version, NOW())
SQL;
        $db->query($sql);
        $this->activePlugins[$pluginName] = $plugin;
    }

    /**
     * deactivate the plugin calling the deactivate method
     *
     * @param string $pluginName
     * @throws Exception
     */
    public function deactivatePlugin($pluginName)
    {
        if (!$this->isActive($pluginName))
            throw new Exception ('Plugin currently inactive.');

        $plugin = $this->activePlugins[$pluginName];
        if (!$plugin) return;

        $plugin->deactivate($this);

        $db = $this->getConfig()->getDb();
        $pluginName = preg_replace('/[^a-zA-Z0-9_-]/', '', $pluginName);
        $pluginName = $db->quote($pluginName);
        $sql = <<<SQL
DELETE FROM `plugin` WHERE `name` = $pluginName
SQL;
        $db->query($sql);
        unset($this->activePlugins[$pluginName]);
    }



    /**
     * The plugin main executible classname is made using this function
     *
     * @param string $pluginName
     * @return string
     */
    public function makePluginClassname($pluginName)
    {
        return '\\'.$pluginName.'\\Plugin';
    }

    /**
     * Call a hook method on all active plugins that support it.
     * This will be called by the hook observers that get fired.
     *
     * @param string $hookMethod
     * @param array $args (Optional)
     */
    public function executeHook($hookMethod, $args = array())
    {
        foreach ($this->activePlugins as $plugin) {
            if ($plugin->isEnabled() && method_exists($plugin, $hookMethod)) {
                tklog($plugin->getClassName().'::'.$hookMethod . '()');
                call_user_func(array($plugin, $hookMethod), $args);
            }
        }
    }



}