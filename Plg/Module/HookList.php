<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Plg\Module;

/**
 *
 *
 * @package Plg\Module
 */
class HookList extends \Mod\Module
{


    /**
     * init
     */
    public function init()
    {

    }

    /**
     * execute
     */
    public function doDefault()
    {

    }

    /**
     * show
     */
    public function show()
    {
        $template = $this->getTemplate();
        $list = \Plg\Hook::$hookList;

        $i = 0;
        foreach ($list as $k => $arr) {
            $method = $arr['method'];
            $args = '';
            foreach ($arr['args'] as $k => $v) {
                $url = "#"; // TODO: link to documentation
                $args .= '<span class="arg"><span class="type"><a href="' . $url . '" title="View API">\\' . $v . '</a></span> <span class="name">$' . $k . '</span></span>, ';
            }
            if ($args) {
                $args = substr($args, 0, -2);
            }
            //$args = implode(' &nbsp; , <br/>', $arr['args']);

            $repeat = $template->getRepeat('row');
            $css = ($i++ % 2) == 0 ? 'even' : 'odd';
            $repeat->addClass('row', $css);
            $repeat->insertHtml('method', '' . $method . '(' . $args . ')');
            $repeat->appendRepeat();
        }

        $css = <<<CSS
.HookList .method {
  font-weight: bold;
}
.HookList .arg {
  display: inline-block;
  padding: 0px 5px;
}
.HookList .arg span {
  display: inline-block;
  font-weight: normal;
}
.HookList .arg .type {

}
.HookList .arg .name {
  color: #643206;
}

CSS;
        $template->appendCss($css);


        $info = <<<XML
{
    "name": "vendor/plugin-name",
    "type": "tek-plugin",
    "description": "---",
    "keywords": ["keywords"],
    "time":  "2014-01-01",
    "homepage": "http://www.example.com/",
    "license": "MIT",
    "version": "trunk",
    "authors": [
        {
            "name": "Author Name",
            "homepage": "http://www.example.com/"
        }
    ],
    "extra": {
        "branch-alias": {
            "dev-trunk": "1.0.x-dev"
        }
    },
    "require": {
      "ttek/installers": "~1.0"
    }
}

XML;
        $template->insertText('info', $info);
    }


    /**
     * makeTemplate
     *
     * @return string
     */
    public function __makeTemplate()
    {
        $xmlStr = <<<HTML
<?xml version="1.0" encoding="UTF-8"?>
<div class="HookList">

  <div class="row-fluid">

    <div class="span12" var="aside">

      <div class="widget-box">
        <div class="widget-title">
          <span class="icon">
            <i class="icon-th-list"></i>
          </span>
          <h5>Available Plugin Methods</h5>
        </div>

        <div class="widget-content">
            <ul>
              <li repeat="row" var="row" style="float: left; width: 45%; margin: 5px 10px;">
                <span class="method" var="method"></span>
              </li>
            </ul>
            <div class="clearfix"></div>
        </div>

      </div>
    </div>
  </div>

  <p>&nbsp;</p>
  <div class="row-fluid">
    <div class="span12">
      <div class="widget-box">
        <div class="widget-title">
          <span class="icon">
            <i class="icon-th-list"></i>
          </span>
          <h5>Plugin Class</h5>
        </div>
        <div class="widget-content">
          <p>Replace 'Example' with your plugin name.</p>
<pre>
&lt;?php

namespace example;
/**
 * An example Plugin template class
 *
 */
class Plugin extends \Plg\Iface
{
    /**
     * __construct
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

   /**
     * Init the plugin
     * This is called when the session first registers the plugin to the queue
     * So it is the first called method after the constructor.....
     *
     */
    public function init()
    {
        // First register Pages for this plugin
        \$dispatcher = \$this->getConfig()->getDispatcherStatic();
        \$dispatcher->overwrite()->add(\$this->getConfigUrl()->getPath(true), '\example\Module\Config');
    }

    /**
     * This is the path to the page that the administrator
     * can configure/manage the plugin.
     *
     * @return \Tk\Url
     */
    public function getConfigUrl()
    {
        // Note: do not use \Tk\Url::createHomeUrl() here, won't work.
        return \Tk\Url::create('/admin/example/config.html');
    }

    /**
     * Activate/install the plugin, essentially
     * installing any DB and settings required to run
     * Will only be called when activating the plugin in the
     * plugin control panel
     *
     */
    public function activate()
    {
        vd('activate');
    }

    /**
     * Deactivate/uninstall the plugin removing any DB data and settings
     * Will only be called when deactivating the plugin in the
     * plugin control panel
     *
     */
    public function deactivate()
    {
        vd('deactivate');
    }

    //  ------- Define any hook methods, see plugin Hook List ------------

    /**
     * pre Front Controller Execute hook
     */
    function preFcExecute(\$args)
    {
        vd('test pre-execute');
    }

    /**
     * post Front Controller Execute hook
     */
    function postFcExecute(\$args)
    {
        vd('test post-execute');
    }




    // TODO: Define any other hook methods as you need them


}
?&gt;
</pre>

        </div>
      </div>
  <p>&nbsp;</p>
      <div class="widget-box">
        <div class="widget-title">
          <span class="icon">
            <i class="icon-th-list"></i>
          </span>
          <h5>composer.json</h5>
        </div>
        <div class="widget-content">

<pre var="info">

</pre>

        </div>
      </div>
    </div>


  </div>
  <p>&nbsp;</p>
</div>
HTML;
        $template = \Mod\Dom\Loader::load($xmlStr, $this->getClassName());
        return $template;
    }


}
