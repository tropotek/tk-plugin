<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Plg;

/**
 *
 *
 * @package Plg
 */
abstract class Iface extends \Tk\Object
{
    const PLUGIN_TABLE = 'pluginData';

    /**
     * @var bool
     */
    private $enable = true;

    /**
     * Plugin information data
     * @var stdClass
     */
    private $info = null;

    /**
     * Plugin data
     * @var \Tk\Db\Registry
     */
    private $data = null;



    /**
     *
     *
     */
    public function __construct()
    {
        $this->data = \Tk\Db\Registry::createDbRegistry(self::PLUGIN_TABLE, strtolower($this->getPluginName()));
    }




    /**
     * Get the plugin registry
     *
     * @return \Tk\Db\Registry
     */
    public function getDataArray()
    {
        return $this->data;
    }

    public function dataGet($key)
    {
        return $this->data->get($key);
    }
    public function dataSet($key, $val)
    {
        $this->data->set($key, $val);
    }
    public function dataExists($key)
    {
        return $this->data->exists($key);
    }
    public function dataDelete($key)
    {
        return $this->data->delete($key);
    }

    /**
     * Get the plugin name
     *
     * @return string
     */
    public function getPluginName()
    {
        return $this->getNamespace();
    }

    /**
     * Set the plugin enabled status
     *
     * @param bool $b
     * @return $this
     */
    public function setEnable($b = true)
    {
        $this->enable = $b;
        return $this;
    }

    /**
     * Is the plugin enabled.
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enable;
    }


    /**
     * Get Plugin Meta Data
     *
     * @return stdClass
     */
    public function getInfo()
    {
        if (!$this->info) {
            $file = dirname($this->getClassPath()) . '/composer.json';
            $this->info = json_decode(file_get_contents($file));
        }
        return $this->info;
    }

    /**
     * Get the plugin Factory instance
     *
     * @return \Plg\Factory
     */
    public function getPluginFactory()
    {
        return \Plg\Factory::getInstance();
    }

    /**
     * Init the plugin
     * This is called when the session first registers the plugin to the queue
     * So it is the first called method after the constructor.....
     *
     */
    abstract function init();


    /**
     * This is the path to the page that the administrator
     * can configure/manage the plugin.
     *
     * @return \Tk\Url
     */
    abstract function getConfigUrl();


    /**
     * Activate the plugin, essentially
     * installing any DB and settings required to run
     * Will only be called when activating the plugin in the
     * plugin control panel
     *
     */
    abstract function activate();

    /**
     * Deactivate the plugin removing any DB data and settings
     * Will only be called when deactivating the plugin in the
     * plugin control panel
     *
     */
    abstract function deactivate();



}
