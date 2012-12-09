<?php

namespace AssetManagement;

use Zend\EventManager\EventInterface,
    Zend\ModuleManager\Feature\AutoloaderProviderInterface,
    Zend\ModuleManager\Feature\BootstrapListenerInterface,
    Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements AutoloaderProviderInterface,
                        BootstrapListenerInterface,
                        ConfigProviderInterface
{
  public function onBootstrap( EventInterface $event )
  {
    $event
      ->getApplication()
        ->getEventManager()
          ->attach(
            'render',
            [ $this, 'tmp' ] );
  }

  public function tmp( EventInterface $e )
  {
    $config = $e->getApplication()->getConfig();

    if( !isset( $config[ 'asset_management' ][ 'assets' ] ) )
      return;

    $assets = $config[ 'asset_management' ][ 'assets' ];
  }

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
          [
           // 'Assetic'     => __DIR__ . '/src/Assetic',
            __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
          ] ] ];
  }
}