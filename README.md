LemoGrid Module for Zend Framework 2
========

The LemoGrid module provides building of data grids similar to Zend Form quickly and easily.

## Supported grids

* jQuery Grid Plugin – jqGrid ([www.trirand.com/blog/](http://www.trirand.com/blog/))

## Installation

Installation of this module uses composer. For composer documentation, please refer to
[getcomposer.org](http://getcomposer.org/).

#### Installation steps

  1. `cd my/project/directory`
  2. Create a `composer.json` file with following contents:

     ```json
     {
         "require": {
             "matycz/lemo-grid": "0.*"
         }
     }
     ```
  3. Run `php composer.phar install`
  4. Open `my/project/directory/config/application.config.php` and add following keys to your `modules`

     ```php
     'LemoGrid',
     ```

Installation without composer is not officially supported, and requires you to install and autoload
the dependencies specified in the `composer.json`.
