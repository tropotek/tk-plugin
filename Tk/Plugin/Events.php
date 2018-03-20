<?php
namespace Tk\Plugin;

/**
 * Class MailEvents
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
final class Events
{


    /**
     * 
     *
     * @event \Tk\Event\Event
     */
    const INSTALL = 'plugin.onInstall';

    /**
     *
     *
     * @event \Tk\Event\Event
     */
    const ACTIVATE = 'plugin.onActivate';
    
    /**
     *
     *
     * @event \Tk\Event\Event
     */
    const DEACTIVATE = 'plugin.onDeactivate';

}