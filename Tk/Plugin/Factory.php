<?php
namespace Tk\Plugin;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Factory
{

    /**
     * @var string
     */
    static $dbTable = 'plugin';

    /**
     * @var Factory
     */
    static $instance = null;

    /**
     * Active plugins
     * @var array
     */
    protected $activePlugins = array();

    /**
     * @var \Tk\Config
     */
    protected $config = null;

    /**
     * @var \Tk\Db\Pdo
     */
    protected $db = null;


    /**
     * constructor
     *
     * @param \Tk\Config $config
     */
    protected function __construct($config)
    {
        $this->config = $config;
        $this->db = $config->getDb();

        $this->installPluginTable();
        $this->initActivePlugins();
    }

    /**
     * Get an instance of this object
     *
     * @param \Tk\Config $config
     * @return Factory
     */
    static function getInstance($config = null)
    {
        if (self::$instance === null) {
            if (!$config)
                $config = \Tk\Config::getInstance();
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * Get Plugin instance
     * Can only be called if the plugin is active
     * Returns null otherwise...
     *
     * @param $pluginName
     * @return Iface|null
     */
    public function getPlugin($pluginName)
    {
        if (isset($this->activePlugins[$pluginName]))
            return $this->activePlugins[$pluginName];
    }

    /**
     * Install the plugin tables
     */
    protected function installPluginTable()
    {
        if($this->db->tableExists(self::$dbTable)) {
            return;
        }
        $migrate = new \Tk\Util\SqlMigrate($this->db);
        $migrate->setTempPath($this->config->getTempPath());
        $migrate->migrate($this->config->getVendorPath() . '/ttek/tk-plugin/sql');
    }

    /**
     * Registration adds the plugin to the list of plugins, and also
     * includes it's code into our runtime.
     *
     */
    protected function initActivePlugins()
    {
        $available = $this->getAvailablePlugins();
        $this->activePlugins = array();
        foreach ($available as $pluginName) {
            if (!$this->isActive($pluginName)) continue;
            $plugin = $this->makePluginInstance($pluginName);
            $plugin->doInit();
            $this->activePlugins[$pluginName] = $plugin;
            
        }
        return $this->activePlugins;
    }

    /**
     * @param $pluginName
     * @return Iface
     * @throws Exception
     */
    protected function makePluginInstance($pluginName)
    {
        if (!$this->isActive($pluginName)) {
            throw new Exception('Cannot instantiate an inactive plugin: ' . $pluginName);
        }
        $class = $this->makePluginClassname($pluginName);
        if (!class_exists($class))
            include_once $this->makePluginPath($pluginName).'/Plugin.php';
        
        $data = $this->getDbPlugin($pluginName);
        
        /** @var Iface $plugin */
        $plugin = new $class($data->id, $pluginName, $this->config);
        if (!$plugin instanceof Iface) {
            throw new Exception('Plugin class uses the incorrect interface: ' . $class);
        }
        return $plugin;
    }

    /**
     * getAvailablePlugins
     *
     * @return array
     */
    public function getAvailablePlugins()
    {
        $path = $this->config->getPluginPath();
        $fileList = array();
        if (is_dir($path)) {
            $fileList = scandir($path);
            foreach ($fileList as $i => $plugPath) {
                if (preg_match('/^(\.|_)/', $plugPath) || !is_dir($path.'/'.$plugPath)) {
                    unset($fileList[$i]);
                }
            }
        }
        return array_merge($fileList);
    }

    /**
     * The plugin main executible classname is made using this function
     *
     * @param string $pluginName
     * @return string
     */
    public function makePluginClassname($pluginName)
    {
        // this may need to be made into a callback per plugin for custom configs?
        if (!empty($this->getPluginInfo($pluginName)->autoload->{'psr-0'})) {
            $ns = current(array_keys(get_object_vars($this->getPluginInfo($pluginName)->autoload->{'psr-0'})));
            $class = '\\' . $ns . 'Plugin';
            if (class_exists($class)) return $class;
        }
        return '\\' . $pluginName.'\\Plugin';
    }

    /**
     * Get the main path for the plugin
     *
     * @param $pluginName
     * @return string
     */
    public function makePluginPath($pluginName)
    {
        return $this->config->getPluginPath() . '/' . $pluginName;
    }

    /**
     * Get Plugin Meta Data
     *
     * @param $pluginName
     * @return \stdClass
     */
    public function getPluginInfo($pluginName)
    {
        $file = $this->config->getPluginPath().'/'.$pluginName.'/composer.json';
        if (is_readable($file))
            return json_decode(file_get_contents($file));
        $info = new \stdClass();
        $info->name = 'ttek-plg/' . $pluginName;
        $info->version = '0.0.1';
        return $info;
    }

    /**
     *
     * @param string $pluginName
     * @return bool
     */
    public function isActive($pluginName)
    {
        $pluginName = preg_replace('/[^a-zA-Z0-9_-]/', '', $pluginName);
        $sql = sprintf('SELECT * FROM plugin WHERE name = %s', $this->db->quote($pluginName));
        $res = $this->db->query($sql);
        if ($res->rowCount() > 0) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param string $pluginName
     * @return bool
     */
    public function getDbPlugin($pluginName)
    {
        $pluginName = preg_replace('/[^a-zA-Z0-9_-]/', '', $pluginName);
        $sql = sprintf('SELECT * FROM plugin WHERE name = %s', $this->db->quote($pluginName));
        $res = $this->db->query($sql);
        return $res->fetch();
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

        $pluginName = preg_replace('/[^a-zA-Z0-9_-]/', '', $pluginName);
        $version = $this->getPluginInfo($pluginName)->version;
        if (!$version) $version = '0.0.0';
        
        // Activate plugin by database entry
        $sql = sprintf('INSERT INTO plugin VALUES (NULL, %s, %s, NOW())', $this->db->quote($pluginName), $this->db->quote($version));
        $this->db->query($sql);
        
        $plugin = $this->makePluginInstance($pluginName);
        $plugin->doActivate();
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

        /** @var Iface $plugin */
        $plugin = $this->activePlugins[$pluginName];
        if (!$plugin) return;
        $plugin->doDeactivate();

        $pluginName = preg_replace('/[^a-zA-Z0-9_-]/', '', $pluginName);
        $sql = sprintf('DELETE FROM plugin WHERE name = %s', $this->db->quote($pluginName));
        $this->db->query($sql);
        unset($this->activePlugins[$pluginName]);
    }

    /**
     * @return \Tk\Config
     */
    public function getConfig()
    {
        return $this->config;
    }
}