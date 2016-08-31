<?php
namespace Tk\Plugin;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Factory
{
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
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger = null;


    /**
     * constructor
     *
     * @param \Tk\Config $config
     */
    protected function __construct($config)
    {
        $this->config = $config;
        $this->db = $config->getDb();
        $this->logger = new \Psr\Log\NullLogger();
        if ($config->getLog())
            $this->logger = $config->getLog();

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
        $available = $this->getFolderList($this->config->getPluginPath());

        $active = array();
//        foreach ($available as $pluginName) {
//            if ($this->isActive($pluginName)) {
//                $class = $this->makePluginClassname($pluginName);
//                $plug = new $class();
//                if (!$plug instanceof Iface) {
//                    throw new Exception('Plugin class not an instance of \Plg\Iface: ' . $class);
//                }
//                if (method_exists($plug, 'init')) {
//                    $plug->getInfo();
//                    $plug->init();
//                    $this->logger->debug('Plugin Init: ' . $plug->getClassName());
//                }
//                $active[$pluginName] = $plug;
//            }
//        }
        return $active;
    }

    /**
     * getAvailablePlugins
     *
     * @return array
     */
    public function getFolderList($path)
    {
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

















}