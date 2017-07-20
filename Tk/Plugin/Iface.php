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
     * Use the type to extend the plugin system
     */
    const TYPE_SYSTEM = 'system';

    
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var string
     */
    protected $type = 'system';

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
     */
    abstract function doInit();

    /**
     * Activate the plugin, essentially
     * installing any DB and settings required to run
     * Will only be called when activating the plugin in the
     * plugin control panel
     */
    abstract function doActivate();


    /**
     * Upgrade the plugin
     * Called when the file version is larger than the version in the DB table
     *
     * @param string $oldVersion
     * @param string $newVersion
     */
    function doUpgrade($oldVersion, $newVersion) { }


    /**
     * Deactivate the plugin removing any DB data and settings
     * Will only be called when deactivating the plugin in the
     * plugin control panel
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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
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

    /**
     * @param string $zoneName
     * @param string $zoneId
     */
    public function doZoneEnable($zoneName, $zoneId) { }

    /**
     * @param string $zoneName
     * @param string $zoneId
     */
    public function doZoneDisable($zoneName, $zoneId) { }

    /**
     * @param string $zoneName
     * @param string $zoneId
     * @return bool
     */
    public function isZonePluginEnabled($zoneName, $zoneId)
    {
        return $this->getPluginFactory()->isZonePluginEnabled($this->getName(), $zoneName, $zoneId);
    }

    /**
     * Get the zone settings URL, if null then there is none
     * <code>
     *   // Some example code for zone setup urls
     *   switch ($zoneName) {
     *     case 'institution':
     *       return \Tk\Uri::create('/lti/institutionSettings.html');
     *   }
     * </code>
     * @return string|\Tk\Uri|null
     */
    public function getZoneSettingsUrl($zoneName)
    {
        return null;
    }



}
