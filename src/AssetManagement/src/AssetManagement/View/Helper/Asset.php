<?php

namespace AssetManagement\View\Helper;

use Assetic\AssetManager,
    Assetic\Factory\AssetFactory,
    Assetic\FilterManager,
    Zend\ServiceManager\ServiceLocatorAwareInterface,
    Zend\ServiceManager\ServiceLocatorInterface,
    Zend\View\Helper\AbstractHelper;

class Asset extends AbstractHelper
         implements ServiceLocatorAwareInterface
{
  protected $assetFactory,
            $assetManager,
            $assets,
            $config,
            $debug,
            $factory,
            $filterManager,
            $paths,
            $serviceLocator;

  /**
   * @return ServiceLocatorInterface
   */
  public function getServiceLocator()
  {
    return $this->serviceLocator;
  }

  public function setServiceLocator( ServiceLocatorInterface $serviceLocator )
  {
    $this->serviceLocator = $serviceLocator;

    return $this;
  }

  protected function setPaths( array $paths )
  {
    $this->paths = $paths;
  }

  protected function addPath( $alias, $path )
  {
    $paths = array_merge( $this->getPaths(), [ $alias => $path ] );
    $this->setPaths( $paths );

    return $this;
  }

  protected function addPaths( array $paths )
  {
    foreach( $paths as $alias => $path )
      $this->addPath( $alias, $path );

    return $this;
  }

  protected function getPath( $alias )
  {
    $paths = $this->getPaths();

    return isset( $paths[ $alias ] )
      ? $paths[ $alias ]
      : '';
  }

  protected function getConfig()
  {
    if( !isset( $this->config ) )
    {
      $manager = $this->getServiceLocator()->getServiceLocator();
      $config  = $manager->get( 'application' )->getConfig();

      $this->config = $config[ 'asset_management' ];
    }

    return $this->config;
  }

  protected function getPaths()
  {
    if( !isset( $this->paths ) )
    {
      $this->paths = [];

      $config = $this->getConfig();

      if( isset( $config[ 'assets' ] ) )
        if( is_array( $config[ 'assets' ] ) )
          $this->addPaths( $config[ 'assets' ] );
    }

    return $this->paths;
  }

  protected function removePath( $alias )
  {
    unset( $this->paths[ $alias ] );

    return $this;
  }

  protected function clearPaths()
  {
    $this->paths = [];

    return $this;
  }

  /**
   * Output ref map
   * [ 'paths'   ]
   * [ 'filters' ]
   * 1.'options' ]
   *
   * @return array
   */
  protected function getCompleteData()
  {
    $assets = $this->getAssets();
    $assets[ 'options' ] = [];
    $assets[ 'options' ][ 'root' ] = [];

    foreach( $assets[ 'paths' ] as $key => &$path )
    {
      $public   = $this->getPath( $path[ 0 ] );
      $public   = realpath( $public );
      $relative = $path[ 1 ];
      $path     = $public . DIRECTORY_SEPARATOR . $relative;

      if( !in_array( $public, $assets[ 'options' ][ 'root' ] ) )
        array_push( $assets[ 'options' ][ 'root' ], $public );
    }

    return $assets;
  }

  /**
   * Will return all feeded assets
   *
   * @param array $asset
   * @return string
   */
  public function dump( array $asset = null )
  {
    if( !is_null( $asset ) )
      $this->setAssets( $asset );

    $assets = $this->getCompleteData();
    $assets = $this->getAssetFactory()->createAsset(
      $assets[ 'paths' ],
      $assets[ 'filters' ],
      $assets[ 'options' ] );

    $this->clear();

    return $assets->dump();
  }

  /**
   * @return AssetFactory
   */
  public function getAssetFactory()
  {
    if( !isset( $this->assetFactory ) )
    {
      $this->assetFactory = new AssetFactory( '' );
      $this->assetFactory->setAssetManager( $this->getAssetManager() );
      $this->assetFactory->setFilterManager( $this->getFilterManager() );
      $this->assetFactory->setDebug( $this->isDebug() );
    }

    return $this->assetFactory;
  }

  /**
   * @return AssetManager
   */
  public function getAssetManager()
  {
    if( !isset( $this->assetManager ) )
      $this->assetManager = new AssetManager();

    return $this->assetManager;
  }

  /**
   * @return FilterManager
   */
  public function getFilterManager()
  {
    if( !isset( $this->filterManager ) )
    {
      $this->filterManager = new FilterManager();
      $config = $this->getConfig();

      if( isset( $config[ 'filter_map' ] ) )
        foreach( $config[ 'filter_map' ] as $filter )
          $this->filterManager->set(
            $filter[ 'alias' ],
            isset( $filter[ 'param' ] )
            ? new $filter[ 'class' ]( $filter[ 'param' ] )
            : new $filter[ 'class' ]() );
    }

    return $this->filterManager;
  }

  public function isDebug()
  {
    if( !isset( $this->debug ) )
    {
      $config = $this->getConfig();
      $this->debug = $config[ 'debug' ];
    }

    return $this->debug;
  }

  protected function getAssets()
  {
    if( !isset( $this->assets ) )
      $this->clear();

    return $this->assets;
  }

  protected function setAssets( array $assets )
  {
    $this->assets = $assets;

    return $this;
  }

  public function clear()
  {
    $this->assets = [ 'filters' => [],
                      'paths'   => [] ];

    return $this;
  }

  public function append( $alias, $path )
  {
    $assets = $this->getAssets();

    array_push(
      $assets[ 'paths' ],
      [ $alias, $path ] );

    $this->setAssets( $assets );

    return $this;
  }

  public function prepend( $alias, $path )
  {
    $assets = $this->getAssets();

    array_unshift(
      $assets[ 'paths' ],
      [ $alias, $path ] );

    $this->setAssets( $assets );

    return $this;
  }

  public function getFilters()
  {
    $assets = $this->getAssets();

    return $assets[ 'filters' ];
  }

  public function setFilters( array $filters )
  {
    if( !isset( $this->assets ) || !isset( $this->assets[ 'filters' ] ) )
      $this->clear();

    $this->assets[ 'filters' ] = $filters;

    return $this;
  }

  /**
   * Appending a filter to the filters stack
   *
   * @param string $filter
   * @return \AssetManagement\View\Helper\Asset
   */
  public function filter( $filter )
  {
    $filters = $this->getFilters();
    array_push( $filters, $filter );
    $this->setFilters( $filters );

    return $this;
  }

  /**
   * Appending multiple filters to the filters stack
   *
   * @param array $filters
   * @return \AssetManagement\View\Helper\Asset
   */
  public function filters( array $filters )
  {
    foreach( $filters as $filter )
      $this->filter( $filter );

    return $this;
  }

  public function clearFilters()
  {
    $this->setFilters( [] );

    return $this;
  }

  public function __invoke( $alias = null, $path = null, $filters = null )
  {
    if( !is_null( $alias ) && !is_null( $path ) )
      $this->append( $alias, $path, $filters );

    if( !is_null( $filters ) )
      $this->setFilters( $filters );

    return $this;
  }

  public function __toString()
  {
    $par = $this->encodeAssets( $this->getAssets() );
    $hlp = $this->getServiceLocator()->get( 'url' );
    $url = $hlp( 'asset', [ 'assets' => $par ] );

    $this->clear();

    return $url;
  }

  /**
   * Encode the containing assets to a url friendly string
   *
   * @param array $assets
   * @return string
   */
  public function encodeAssets( array $assets )
  {
    $assets = json_encode( $assets );
    $assets = gzdeflate( $assets, 9 );
    $assets = base64_encode( $assets );
    $assets = urlencode( $assets );

    return $assets;
  }

  /**
   * Mirror function for 'this->encodeAssets'
   *
   * @param string $assets
   * @return array
   * @see encodeAssets()
   */
  public function decodeAssets( $assets )
  {
    $assets = urldecode( $assets );
    $assets = base64_decode( $assets );
    $assets = gzinflate( $assets );
    $assets = json_decode( $assets, true );

    return $assets;
  }
}