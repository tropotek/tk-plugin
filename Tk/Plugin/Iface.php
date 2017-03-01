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
     * @var Factory
     */
    protected $pluginFactory = null;


    

    /**
     * Iface constructor.
     * @param string $name
     */
    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
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
     * @return bool
     */
    public function isActive()
    {
        return $this->getPluginFactory()->isActive($this->getName());
    }

    /**
     * Return the URI of the plugin's configuration page
     * Return null for none
     *
     * @return \Tk\Uri
     */
    public function getSettingsUrl()
    {
        return null;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the plugin/package name. Same name as stored in teh DB of active plugins
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Factory
     */
    public function getPluginFactory()
    {
        return $this->pluginFactory;
    }

    /**
     * @param Factory $pluginFactory
     */
    public function setPluginFactory($pluginFactory)
    {
        $this->pluginFactory = $pluginFactory;
    }

    /**
     * Get Plugin Meta Data
     *
     * @return \stdClass
     */
    public function getInfo()
    {
        if (!$this->info) {
            $this->info = $this->getPluginFactory()->getPluginInfo($this->getName());
        }
        return $this->info;
    }

    /**
     * @return \Tk\Config
     */
    public function getConfig()
    {
        return \Tk\Config::getInstance();
    }


}
