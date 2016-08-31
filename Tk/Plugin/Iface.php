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
            // TODO: Get the plugin path
//            $file = dirname($this->getClassPath()) . '/composer.json';
//            $this->info = json_decode(file_get_contents($file));
        }
        return $this->info;
    }



}
