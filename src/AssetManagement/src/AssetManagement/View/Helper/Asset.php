<?php

namespace AssetManagement\View\Helper;

use Assetic\AssetManager,
    Assetic\Factory\AssetFactory,
    Assetic\FilterManager,
    Zend\ServiceManager\ServiceLocatorAwareInterface,
    Zend\ServiceManager\ServiceLocatorInterface,
    Zend\View\Helper\AbstractHelper;

/**
 * This is a view helper that acts as a facade for assetic.The vision is to
 * keep it easy and clean to use..
 */
class Asset extends AbstractHelper
         implements ServiceLocatorAwareInterface
{
  private $assetFactory,
          $assetManager,
          $assets,
          $config,
          $debug,
          $filterManager,
          $paths,
          $serviceLocator;

  /**
   * @return \Zend\ServiceManager\ServiceLocatorInterface
   */
  public function getServiceLocator()
  {
    return $this->serviceLocator;
  }

  /**
   * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
   * @return \AssetManagement\View\Helper\Asset
   */
  public function setServiceLocator( ServiceLocatorInterface $serviceLocator )
  {
    $this->serviceLocator = $serviceLocator;

    return $this;
  }

  /**
   * @param array $paths
   */
  protected function setPaths( array $paths )
  {
    $this->paths = $paths;
  }

  /**
   * @param strimg $alias
   * @param string $path
   * @return \AssetManagement\View\Helper\Asset
   */
  protected function addPath( $alias, $path )
  {
    $paths = array_merge( $this->getPaths(), [ $alias => $path ] );
    $this->setPaths( $paths );

    return $this;
  }

  /**
   * @param array $paths [ $alias => $path ]
   * @return \AssetManagement\View\Helper\Asset
   */
  protected function addPaths( array $paths )
  {
    foreach( $paths as $alias => $path )
      $this->addPath( $alias, $path );

    return $this;
  }

  /**
   * Retrives the whitelisted path connected to the given alias
   *
   * @param string $alias
   * @return string
   */
  protected function getPath( $alias )
  {
    $paths = $this->getPaths();

    return isset( $paths[ $alias ] )
      ? $paths[ $alias ]
      : '';
  }

  /**
   * Retrives the configuration for the 'asset_management' namespace
   *
   * @return array
   */
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

  /**
   * Lazyloads all whitelisted paths from the configuration and returns them
   *
   * @return array
   */
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

  /**
   * @param string $alias
   * @return \AssetManagement\View\Helper\Asset
   */
  protected function removePath( $alias )
  {
    unset( $this->paths[ $alias ] );

    return $this;
  }

  /**
   * @return \AssetManagement\View\Helper\Asset
   */
  protected function clearPaths()
  {
    $this->paths = [];

    return $this;
  }

  /**
   * Output ref map
   * [ 'paths'   ]
   * [ 'filters' ]
   * [.'options' ]
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
   * Will return all feeded assets in one string
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
   * Lazyloads a populated instance of Assetics AssetFactory
   *
   * @return \Assetic\Factory\AssetFactory
   */
  protected function getAssetFactory()
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
   * Lazyloads Assetics AssetManager
   *
   * @return \Assetic\AssetManager
   */
  protected function getAssetManager()
  {
    if( !isset( $this->assetManager ) )
      $this->assetManager = new AssetManager();

    return $this->assetManager;
  }

  /**
   * Lazyloads Assetics FilterManager and populates it with all the filters
   * defined in the configurations
   *
   * @todo This will load all filters even if they are not used...
   *
   * @return \Assetic\FilterManager
   */
  protected function getFilterManager()
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

  /**
   * Lazyloads from config and returns if we are in debug mode or not
   *
   * @return boolean
   */
  protected function isDebug()
  {
    if( !isset( $this->debug ) )
    {
      $config = $this->getConfig();
      $this->debug = $config[ 'debug' ];
    }

    return $this->debug;
  }

  /**
   * @return array
   */
  protected function getAssets()
  {
    if( !isset( $this->assets ) )
      $this->clear();

    return $this->assets;
  }

  /**
   * @param array $assets
   * @return \AssetManagement\View\Helper\Asset
   */
  protected function setAssets( array $assets )
  {
    $this->assets = $assets;

    return $this;
  }

  /**
   * @return \AssetManagement\View\Helper\Asset
   */
  public function clear()
  {
    $this->assets = [ 'filters' => [],
                      'paths'   => [] ];

    return $this;
  }

  /**
   * Appends an asset to the asset stack
   *
   * @param string $alias
   * @param string $path
   * @return \AssetManagement\View\Helper\Asset
   */
  public function append( $alias, $path )
  {
    $assets = $this->getAssets();

    array_push(
      $assets[ 'paths' ],
      [ $alias, $path ] );

    $this->setAssets( $assets );

    return $this;
  }

  /**
   * Prepends an asset to the asset stack
   *
   * @param string $alias
   * @param string $path
   * @return \AssetManagement\View\Helper\Asset
   */
  public function prepend( $alias, $path )
  {
    $assets = $this->getAssets();

    array_unshift(
      $assets[ 'paths' ],
      [ $alias, $path ] );

    $this->setAssets( $assets );

    return $this;
  }

  /**
   * @return array
   */
  protected function getFilters()
  {
    $assets = $this->getAssets();

    return $assets[ 'filters' ];
  }

  /**
   * @param array $filters
   * @return \AssetManagement\View\Helper\Asset
   */
  protected function setFilters( array $filters )
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

  /**
   * @return \AssetManagement\View\Helper\Asset
   */
  public function clearFilters()
  {
    $this->setFilters( [] );

    return $this;
  }

  /**
   * If $alias and $path parameters are entered the append method is used
   * If the $filters parameter is entered the method setFilters is used
   *
   * @param string $alias
   * @param string $path
   * @param array $filters
   * @return \AssetManagement\View\Helper\Asset
   */
  public function __invoke( $alias = null, $path = null, array $filters = null )
  {
    if( !is_null( $alias ) && !is_null( $path ) )
      $this->append( $alias, $path, $filters );

    if( !is_null( $filters ) )
      $this->setFilters( $filters );

    return $this;
  }

  /**
   * When the object is called upon as a string then it will dump as a link and
   * reset the asset stack
   *
   * @return string
   */
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
    $assets = @gzinflate( $assets );
    $assets = json_decode( $assets, true );

    return $assets;
  }
}