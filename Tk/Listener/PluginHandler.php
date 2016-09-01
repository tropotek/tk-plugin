<?php
namespace Tk\Listener;


use Tk\EventDispatcher\SubscriberInterface;
use Tk\Event\KernelEvent;
use Tk\Plugin\Factory;


/**
 * Class PluginHandler
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class PluginHandler implements SubscriberInterface
{

    /**
     * @var Factory
     */
    protected $pluginFactory = null;


    /**
     */
    function __construct()
    {

    }

    public function onStartup(KernelEvent $event)
    {
        $this->pluginFactory = Factory::getInstance(\Tk\Config::getInstance());

    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(\Tk\Kernel\KernelEvents::INIT  => 'onStartup');
    }
}