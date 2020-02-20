# tk-plugin 

__Project:__ tk-plugin    
__Web:__ <http://www.domtemplate.com/>  
__Authors:__ Michael Mifsud <http://www.tropotek.com/>  
  
A Plugin lib to support the Tk lib.

## Contents

- [Installation](#installation)
- [Introduction](#introduction)
- [Upgrade](#upgrade)


## Installation

Available on Packagist ([ttek/tk-plugin](http://packagist.org/packages/ttek/tk-plugin))
and installable via [Composer](http://getcomposer.org/).

```bash
composer require ttek/tk-plugin
```

Or add the following to your composer.json file:

```json
"ttek/tk-plugin": "~3.2.0"
```


## Introduction



## Upgrade

If you have DB migration issues you may manually update some system tables. 
See if you have any table without the underscore and rename them to the following.

```mysql
-- NOTE: This has to be run manually before upgrading to ver 3.2
RENAME TABLE migration TO _migration;
RENAME TABLE data TO _data;
RENAME TABLE session TO _session;
RENAME TABLE plugin TO _plugin;
```
Also check your src/config/application.php file and ensure that there are no manual
overrides for this table as you may get unexpected results
