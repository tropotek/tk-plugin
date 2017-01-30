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
     * @var int
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $name = null;

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
    public function __construct($id, $name, $config = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->config = $config;
        if (!$this->config) $this->config = \Tk\Config::getInstance();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * Return the URI of the plugin's configuration page
     *
     * @return \Tk\Uri
     */
    abstract function getSettingsUrl();



    /**
     * The plugin ID from the DB of active plugins
     *
     * @return int
     */
    public function getPluginId()
    {
        return $this->id;
    }

    /**
     * Get the plugin/package name. Same name as stored in teh DB of active plugins
     *
     * @return string
     */
    public function getPluginName()
    {
        return $this->name;
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
