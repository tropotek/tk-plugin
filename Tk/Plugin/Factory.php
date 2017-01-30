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
    public static $STARTUP_CLASS = 'Plugin';

    /**
     * @var string
     */
    public static $DB_TABLE = 'plugin';

    /**
     * @var Factory
     */
    public static $instance = null;

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
        $this->setDb($config->getDb());
        $this->initActivePlugins();
    }

    /**
     * Get an instance of this object
     *
     * @param \Tk\Config $config
     * @return Factory
     */
    public static function getInstance($config = null)
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
        return null;
    }

    /**
     * Install the plugin tables
     */
    protected function install()
    {
        if($this->db->tableExists(self::$DB_TABLE)) {
            return $this;
        }

        if ($this->getDb()->tableExists($this->getTable())) return $this;
        $tbl = $this->getDb()->quoteParameter($this->getTable());
        // mysql
        $sql = '';
        if ($this->getDb()->getDriver() == 'mysql') {
            $sql = <<<SQL
CREATE TABLE IF NOT EXISTS $tbl (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL,
  `version` VARCHAR(16) NOT NULL,
  `created` TIMESTAMP NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`)
) ENGINE=InnoDB;
SQL;
        } else if ($this->getDb()->getDriver() == 'pgsql') {
            $sql = <<<SQL
CREATE TABLE IF NOT EXISTS $tbl (
  id SERIAL PRIMARY KEY,
  name VARCHAR(128) NOT NULL,
  version VARCHAR(16) NOT NULL,
  created TIMESTAMP NOT NULL,
  CONSTRAINT plugin_name UNIQUE (name)
);
SQL;
        } else if ($this->getDb()->getDriver() == 'sqlite') {
            $sql = <<<SQL
CREATE TABLE IF NOT EXISTS $tbl (
  id INTEGER PRIMARY KEY,
  name TEXT NOT NULL,
  version TEXT NOT NULL,
  created TIMESTAMP NOT NULL,
  UNIQUE (name)
);
SQL;
        }

        if ($sql)
            $this->getDb()->exec($sql);

        return $this;


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
            include_once $this->makePluginPath($pluginName).'/'.self::$STARTUP_CLASS.'.php';
        
        $data = $this->getDbPlugin($pluginName);
        
        /* @var Iface $plugin */
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
            $class = '\\' . $ns . self::$STARTUP_CLASS;
            if (class_exists($class)) return $class;
        }
        return '\\' . $pluginName.'\\'.self::$STARTUP_CLASS;
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
        // info not found return a default info object
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
        $sql = sprintf('SELECT * FROM %s WHERE name = %s', $this->getDb()->quoteParameter(self::$DB_TABLE), $this->getDb()->quote($pluginName));
        $res = $this->getDb()->query($sql);
        if ($res->rowCount() > 0) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param string $pluginName
     * @return \StdClass
     */
    public function getDbPlugin($pluginName)
    {
        $pluginName = preg_replace('/[^a-zA-Z0-9_-]/', '', $pluginName);
        $sql = sprintf('SELECT * FROM %s WHERE name = %s', $this->getDb()->quoteParameter(self::$DB_TABLE), $this->getDb()->quote($pluginName));
        $res = $this->getDb()->query($sql);
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
        $sql = sprintf('INSERT INTO %s VALUES (NULL, %s, %s, NOW())', $this->getDb()->quoteParameter(self::$DB_TABLE), $this->getDb()->quote($pluginName), $this->getDb()->quote($version));
        $this->getDb()->query($sql);
        
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

        /* @var Iface $plugin */
        $plugin = $this->activePlugins[$pluginName];
        if (!$plugin) return;
        $plugin->doDeactivate();

        $pluginName = preg_replace('/[^a-zA-Z0-9_-]/', '', $pluginName);
        $sql = sprintf('DELETE FROM %s WHERE name = %s', $this->getDb()->quoteParameter(self::$DB_TABLE), $this->getDb()->quote($pluginName));
        $this->getDb()->query($sql);
        unset($this->activePlugins[$pluginName]);
    }

    /**
     * @return \Tk\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get the table name for queries
     *
     * @return string
     */
    protected function getTable()
    {
        return self::$DB_TABLE;
    }

    /**
     * @return \Tk\Db\Pdo
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param \Tk\Db\Pdo $db
     * @return $this
     */
    public function setDb($db)
    {
        $this->db = $db;
        $this->install();
        return $this;
    }


}