LemoGrid Module for Zend Framework 2
========

The LemoGrid module provides building of data grids similar to Zend Form quickly and easily for many platforms.

#### Supported plaforms

* jqGrid - jQuery Grid Plugin ([trirand.com](http://www.trirand.com/blog/))

#### Supported data adapters

* Doctrine\QueryBuilder ([doctrine-project.org](http://www.doctrine-project.org/))
* Laminas\Db\Sql ([framework.zend.com](http://framework.zend.com/))

Requirements
------------

* [Zend Framework 2](https://github.com/zendframework/zf2) (latest master)

Features / Goals
----------------

* Add column Concat [In progress]
* Add column Route [In progress]
* Write documentation
* Write tests

Installation
----------

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

Examples
----------

```php
<?php

namespace Foo;

use LemoGrid\ModuleManager\Feature\GridProviderInterface;
use Laminas\ModuleManager\Feature\ControllerProviderInterface;
use Laminas\ModuleManager\Feature\ServiceProviderInterface;

class Module implements
    ControllerProviderInterface,
    GridProviderInterface,
    ServiceProviderInterface
{
    ...

    /**
     * @inheritdoc
     */
    public function getControllerConfig()
    {
        return array(
            'factories' => array(
                'Foo\Controller\Bar' => function($controllerManager) {
                    $controller = new Controller\BarController();
                    $controller->setGridBar($controllerManager->getServiceLocator()->get('Foo\Grid\Bar'));
                    $controller->setServiceBar($controllerManager->getServiceLocator()->get('Foo\Service\Bar'));
                    return $controller;
                },
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function getGridConfig()
    {
        return array(
            'factories' => array(
                'Foo\Grid\Bar' => function () {
                    $grid = new Grid\Bar();
                    return $grid;
                },
            )
        );
    }
    
    /**
     * @inheritdoc
     */
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Foo\Service\Bar' => function ($serviceManager) {
                    $service = new Service\Bar($serviceManager);
                    $service->setEntityManager($serviceManager->get('Doctrine\ORM\EntityManager'));
                    return $service;
                },
            ),
        );
    }

    ...
}
```

```php
<?php

namespace Foo\Controller;

use Foo\Grid\Bar as GridBar;
use Foo\Service\Bar as ServiceBar;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class BarController extends AbstractActionController
{
    /**
     * @var GridBar
     */
    protected $gridBar;

    /**
     * @var ServiceBar
     */
    protected $serviceBar;

    /**
     * Page with grid example
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $adapter = new \LemoGrid\Adapter\Doctrine\QueryBuilder();
        $adapter->setQueryBuilder($this->getServiceBar()->getQueryBuilderInstanceForGrid());

        $platform = new \LemoGrid\Platform\JqGrid();

        $grid = $this->getGridBar();
        $grid->setAdapter($adapter);
        $grid->setPlatform($platform);
        $grid->setParams($this->params()->fromQuery());

        return new ViewModel(array(
            'grid' => $grid
        ));
    }
    
    ...
    
    /**
     * @param  GridBar $gridBar
     * @return BarController
     */
    public function setGridBar(GridBar $gridBar)
    {
        $this->gridBar = $gridBar;

        return $this;
    }

    /**
     * @return GridBar
     */
    public function getGridBar()
    {
        return $this->gridBar;
    }
    
    /**
     * @param  ServiceBar $serviceBar
     * @return BarController
     */
    public function setServiceBar(ServiceBar $serviceBar)
    {
        $this->serviceBar = $serviceBar;

        return $this;
    }

    /**
     * @return ServiceBar
     */
    public function getServiceBar()
    {
        return $this->serviceBar;
    }
}
```

```php
<?php

namespace Foo\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class Bar
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Return instance of QueryBuilder for grid
     *
     * @return QueryBuilder
     */
    public function queryGrid()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select(array('rootAlias'))
            ->from('Foo\Entity\Bar', 'rootAlias');

        return $qb;
    }
    
    /**
     * Set entity manager
     *
     * @param  EntityManager $entityManager
     * @return Bar;
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        return $this;
    }

    /**
     * Get entity manager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }
}
```

```php
<?php

namespace Foo\Grid;

use LemoGrid\Grid;

class Bar extends Grid
{
    public function init()
    {
        $this->setName('GridBar');

        // NAME
        $this->add(array(
            'name' => 'name',
            'type' => 'text',
            'identifier' => 'rootAlias.name',
            'attributes' => array(
                'label' => 'Name',
                'width' => '70',
            )
        ));

        // VERSION
        $this->add(array(
            'name' => 'version',
            'type' => 'text',
            'identifier' => 'rootAlias.version',
            'attributes' => array(
                'label' => 'Version',
                'width' => '20',
            )
        ));

        // EDIT
        $this->add(array(
            'name' => 'edit',
            'type' => 'route',
            'options' => array(
                'text' => '<i class="icon-pencil icon-white"></i>',
                'template' => '<a href="%s" class="btn btn-mini btn-primary">%s</a>',
                'route' => 'foo/bar',
                'params' => array(
                    'action' => 'edit',
                    'id' => '%rootAlias.id%'
                ),
                'reuseMatchedParams' => true,
            ),
            'attributes' => array(
                'width' => '5',
                'align' => 'center',
                'isSortable' => false,
                'isSearchable' => false,
            )
        ));

        // DELETE
        $this->add(array(
            'name' => 'delete',
            'type' => 'route',
            'options' => array(
                'text' => '<i class="icon-trash icon-white"></i>',
                'template' => '<a href="%s" class="btn btn-mini btn-primary dialog-delete">%s</a>',
                'route' => 'foo/bar',
                'params' => array(
                    'action' => 'delete',
                    'id' => '%rootAlias.id%'
                ),
                'reuseMatchedParams' => true,
            ),
            'attributes' => array(
                'width' => '5',
                'align' => 'center',
                'isSortable' => false,
                'isSearchable' => false,
            )
        ));
    }
}

```

```php
<?php
$this->headLink()->appendStylesheet('/css/jqGrid/jqGrid.css');
$this->headScript()->appendFile('/js/jqGrid/jqGrid.js');
?>
<div class="row-fluid">
    <div class="span12 box">
        <?= $this->jqgrid($this->grid) ?>
    </div>
</div>
```
