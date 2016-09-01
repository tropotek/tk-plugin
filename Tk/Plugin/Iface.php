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
    private $info = null;



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
        // TODO: There  should be a faster way basename(dirname(__FILE__))
        return basename(dirname(__FILE__));
//        $ns = get_class($this);
//        $a = explode('\\', $ns);
//        array_pop($a);
//        return array_shift($a);
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
     * @return Factory
     */
    public function getPluginFactory()
    {
        return \Tk\Plugin\Factory::getInstance();
    }

    /**
     * Get Plugin Meta Data
     *
     * @return \stdClass
     */
    public function getInfo()
    {
        if (!$this->info) {
            $this->getPluginFactory()->getPluginInfo($this->getPluginName());
        }
        return $this->info;
    }



}
