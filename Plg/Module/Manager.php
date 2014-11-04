<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Plg\Module;

use Mod\AdminPageInterface;

/**
 *
 *
 * @package Plg\Module
 */
class Manager extends \Mod\Module
{

    /**
     * __construct
     */
    public function __construct()
    {
        $this->setPageTitle('Plugin Manager');

        $this->addEvent('act', 'doActivate');
        $this->addEvent('deact', 'doDeactivate');
        $this->addEvent('del', 'doDelete');

        $this->set(AdminPageInterface::CRUMBS_RESET, true);
        $this->set(AdminPageInterface::PANEL_CONTENT_PADDING, true);
        $this->set(AdminPageInterface::PANEL_CONTENT_ENABLE, false);
    }


    /**
     * init
     */
    public function init()
    {

        $ff = \Form\Factory::getInstance();
        $this->form = $ff->createForm('UploadPlugin');
        $this->form->attach(new Upload('upload'));

        $this->form->addField($ff->createFieldFile('package'))->setRequired();

        $this->addChild($ff->createFormRenderer($this->form), $this->form->getId());



    }

    /**
     * doDefault
     */
    public function doDefault()
    {

    }

    /**
     * doActivate
     */
    public function doActivate()
    {
        $pluginName = strip_tags($this->getRequest()->get('act'));
        if (!$pluginName) {
            \Mod\Notice::addWarning('Error: Unknown Plugin...');
            return;
        }

        $this->getConfig()->getPluginFactory()->activatePlugin($pluginName);
        \Mod\Notice::addSuccess('Plugin `' . $pluginName . '` activated successfuly');
        tklog('Plugin `' . $pluginName . '` activated successfuly');
        $this->getUri()->reset()->redirect();
    }

    /**
     * doDeactivate
     */
    public function doDeactivate()
    {
        $pluginName = strip_tags($this->getRequest()->get('deact'));
        if (!$pluginName) {
            \Mod\Notice::addWarning('Error: Unknown Plugin...');
            return;
        }

        // TODO: Add a flag to remove all data....
        //$this->getConfig()->getPluginFactory()->deactivatePlugin($pluginName, __$removeAll__  );

        $this->getConfig()->getPluginFactory()->deactivatePlugin($pluginName);
        \Mod\Notice::addSuccess('Plugin `' . $pluginName . '` deactivated successfuly');
        tklog('Plugin `' . $pluginName . '` deactivated successfuly');
        $this->getUri()->reset()->redirect();
    }

    /**
     * doDelete
     */
    public function doDelete()
    {
        $pluginName = strip_tags($this->getRequest()->get('del'));
        if (!$pluginName) {
            \Mod\Notice::addWarning('Unknown Plugin...');
            return;
        }

        $path = $this->getConfig()->getPluginFactory()->getPluginPath().'/'.$pluginName;
        if (is_dir($path)) {
            \Tk\Path::rmdir($path);
            if (is_file($path.'.zip'))  unlink($path.'.zip');
            if (is_file($path.'.tar.gz'))  unlink($path.'.tar.gz');
            if (is_file($path.'.tgz'))  unlink($path.'.tgz');
            \Mod\Notice::addSuccess('Plugin `' . $pluginName . '` deleted successfuly');
        } else {
            \Mod\Notice::addWarning('Plugin `' . $pluginName . '` path not found');
        }


        $this->getUri()->reset()->redirect();
    }

    /**
     * show
     */
    public function show()
    {
        $template = $this->getTemplate();

        $list = $this->getConfig()->getPluginFactory()->getAvailablePlugins();
        foreach ($list as $pluginName) {
            $repeat = $template->getRepeat('row');
            $repeat->insertText('title', $pluginName);
            $repeat->setAttr('icon', 'src', $this->getConfig()->getPluginFactory()->getPluginUrl().'/'.$pluginName.'/icon.png');
            if ($this->getConfig()->getPluginFactory()->isActive($pluginName)) {
                $plugin = $this->getConfig()->getPluginFactory()->getPlugin($pluginName);

                $repeat->setChoice('active');
                if ($plugin) {
                    $repeat->setAttr('cfg', 'href', \Tk\Url::create($plugin->getConfigUrl()));
                    $repeat->setAttr('title', 'href', \Tk\Url::create($plugin->getConfigUrl()));
                }
                $repeat->setAttr('deact', 'href', $this->getUri()->reset()->set('deact', $pluginName));
            } else {
                $repeat->setChoice('inactive');
                $repeat->setAttr('act', 'href', $this->getUri()->reset()->set('act', $pluginName));
                $repeat->setAttr('del', 'href', $this->getUri()->reset()->set('del', $pluginName));
            }

            $info = $this->getConfig()->getPluginFactory()->getPluginInfo($pluginName);
            if ($info) {
                if ($info->version) {
                    $repeat->insertText('version', $info->version);
                    $repeat->setChoice('version');
                }
                $repeat->insertText('name', $info->name);
                $repeat->insertText('desc', $info->description);
                $repeat->insertText('author', $info->authors[0]->name);
                $repeat->setAttr('www', 'href', $info->homepage);
                $repeat->insertText('www', $info->homepage);
                $repeat->setChoice('info');
            } else {
                $repeat->insertText('desc', 'Err: No metadata file found!');
            }


            $repeat->appendRepeat();
        }

        $js = <<<JS
jQuery(function ($) {
    $('.act').click(function (e) {
        return confirm('Are you sure you want to insstall this plugin?');
    });
    $('.del').click(function (e) {
        return confirm('Are you sure you want to delete this plugin?');
    });
    $('.deact').click(function (e) {
        return confirm('Are you sure you want to uninstall this plugin?');
    });


});
JS;
        $template->appendJs($js);

    }

    /**
     * makeTemplate
     *
     * @return string
     */
    public function __makeTemplate()
    {
        $xmlStr = <<<HTML
<?xml version="1.0" encoding="UTF-8" ?>
<div class="row">
  <div class="col-md-8 col-sm-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="glyphicon glyphicon-compressed"></i> Available Plugins</h3>
      </div>
      <div class="panel-body">


        <ul class="list-group">
          <li class="list-group-item" repeat="row">
            <div class="row">
              <div class="col-xs-2 col-md-1">
                <img class="media-object" src="#" var="icon" style="width: 100%; " />
              </div>
              <div class="col-xs-10 col-md-11">
                <div>
                  <h4><a href="#" var="title"></a></h4>
                  <p choice="info">
                    <small choice="version"><strong>Version:</strong> <span var="version"></span></small> <br choice="version" />
                    <small><strong>Package:</strong> <span var="name"></span></small> <br/>
<!--                     <small><strong>Author:</strong> <span var="author"></span></small> <br />  -->
                    <small><strong>Homepage:</strong> <a href="#" var="www" target="_blank">View Website</a></small>
                  </p>
                </div>
                <p class="comment-text" var="desc"></p>
                <div class="action">
                  <a href="#" class="btn btn-primary btn-xs noblock act" choice="inactive" var="act"><i class="glyphicon glyphicon-log-in"></i> Install</a>
                  <a href="#" class="btn btn-danger btn-xs noblock del" choice="inactive" var="del"><i class="glyphicon glyphicon-remove-circle"></i> Delete</a>
                  <a href="#" class="btn btn-warning btn-xs noblock deact" choice="active" var="deact"><i class="glyphicon glyphicon-log-out"></i> Uninstall</a>
                  <a href="#" class="btn btn-success btn-xs" choice="active" var="cfg"><i class="glyphicon glyphicon-edit"></i> Config</a>
                </div>
              </div>
            </div>
          </li>
        </ul>

      </div>
    </div>
  </div>

  <div class="col-md-4 col-sm-12">
    <div class="panel panel-default" id="uploadForm">
      <div class="panel-heading">
        <h3 class="panel-title"><span class="glyphicon glyphicon-log-out"></span> Upload Plugin</h3>
      </div>
      <div class="panel-body">
        <p>Select A zip/tgz plugin package to upload.</p>
        <div var="UploadPlugin"></div>
      </div>
    </div>
  </div>


</div>
HTML;

        $template = \Mod\Dom\Loader::load($xmlStr, $this->getClassName());
        return $template;
    }

}


/**
 *
 *
 * @package Plg\Module
 */
class Upload extends \Form\Event\Button
{

    /**
     * execute
     *
     * @param \Form\Form $form
     */
    public function update($form)
    {
        $field = $form->getField('package');
        if (!$field->isUploadedFile()) {
            $form->addFieldError('package', 'Please Select a valid package file.');
            return;
         }
         if (!preg_match('/\.(zip|gz|tgz)$/i', $field->getFileName())) {
              $form->addFieldError('package', 'Please Select a valid package file. (zip/tar.gz/tgz only)');
         }

        $field->validate();
        if ($form->hasErrors()) {
            return;
        }

        // Upload and unpack the file
        $dest = $this->getConfig()->get('plg.path').'/'.$field->getFileName();
        $field->moveUploadedFile($dest);

        // Unzip File
        if (\Tk\Path::getFileExtension($dest) == 'zip') {
            $cmd  = sprintf('cd %s && unzip %s', escapeshellarg(dirname($dest)), escapeshellarg(basename($dest)));
            $msg = exec($cmd);
        } else if (\Tk\Path::getFileExtension($dest) == 'tar.gz' || \Tk\Path::getFileExtension($dest) == 'tgz') {
            $cmd  = sprintf('cd %s && tar zxf %s', escapeshellarg(dirname($dest)), escapeshellarg(basename($dest)));
            $msg = exec($cmd);
        }

        \Mod\Notice::addSuccess('Plugin Successfully Uploaded.');
    }

}
