<?php
namespace Tk\Plugin;

/**
 * Class Iface
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
abstract class Iface
{

    /**
     * @var bool
     */
    private $enable = true;

    /**
     * @var \stdClass
     */
    private $name = null;

    /**
     * @var \stdClass
     */
    private $info = null;

    /**
     * @var \Tk\Config
     */
    private $config = null;
    
    

    /**
     * Iface constructor.
     * @param string $name
     * @param \Tk\Config $config
     */
    public function __construct($name, $config = null)
    {
        $this->name = $name;
        if (!$config) $config = \Tk\Config::getInstance();
        $this->config = $config;
    }


    /**
     * Init the plugin
     * This is called when the session first registers the plugin to the queue
     * So it is the first called method after the constructor.....
     *
     */
    abstract function doInit();

    /**
     * Activate the plugin, essentially
     * installing any DB and settings required to run
     * Will only be called when activating the plugin in the
     * plugin control panel
     *
     */
    abstract function doActivate();

    /**
     * Deactivate the plugin removing any DB data and settings
     * Will only be called when deactivating the plugin in the
     * plugin control panel
     *
     */
    abstract function doDeactivate();



    /**
     * Get the plugin name
     *
     * @return string
     */
    public function getPluginName()
    {
        return $this->name;
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
     * @return \stdClass
     */
    public function getInfo()
    {
        if (!$this->info) {
            $this->info = $this->getPluginFactory()->getPluginInfo($this->getPluginName());
        }
        return $this->info;
    }

    /**
     * @return \Tk\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return Factory
     */
    public function getPluginFactory()
    {
        return \Tk\Plugin\Factory::getInstance();
    }



}
