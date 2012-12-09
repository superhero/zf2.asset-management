<?php

namespace AssetManagement;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface,
    Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements AutoloaderProviderInterface,
                        ConfigProviderInterface
{
  public function getConfig()
  {
    return include __DIR__ . '/config/module.php';
  }

  public function getAutoloaderConfig()
  {
    return
      [ 'Zend\Loader\ClassMapAutoloader' =>
        [ __DIR__ . '/autoload/classmap.php' ],

        'Zend\Loader\StandardAutoloader' =>
        [ 'namespaces' =>
          [ // 'Assetic'     => __DIR__ . '/src/Assetic',
            __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__ ] ] ];
  }
}