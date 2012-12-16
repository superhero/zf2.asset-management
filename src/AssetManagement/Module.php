<?php

namespace AssetManagement;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface,
    Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements AutoloaderProviderInterface,
                        ConfigProviderInterface
{
  /**
   * Returns the modules configurations
   *
   * @return array
   */
  public function getConfig()
  {
    return include __DIR__ . '/config/module.php';
  }

  /**
   * Returns configurations for the autoloader
   *
   * @return array
   */
  public function getAutoloaderConfig()
  {
    return
      [ 'Zend\Loader\ClassMapAutoloader' =>
        [ __DIR__ . '/autoload/classmap.php' ],

        'Zend\Loader\StandardAutoloader' =>
        [ 'namespaces' =>
          [ __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__ ] ] ];
  }
}