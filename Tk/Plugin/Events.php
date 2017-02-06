<?php
namespace Tk\Plugin;

/**
 * Class MailEvents
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
final class Events
{


    /**
     * 
     *
     * @event \Tk\EventDispatcher\Event
     */
    const INSTALL = 'plugin.onInstall';

    /**
     *
     *
     * @event \Tk\EventDispatcher\Event
     */
    const ACTIVATE = 'plugin.onActivate';
    
    /**
     *
     *
     * @event \Tk\EventDispatcher\Event
     */
    const DEACTIVATE = 'plugin.onDeactivate';

}