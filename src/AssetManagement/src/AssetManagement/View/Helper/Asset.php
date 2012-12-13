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
          $filters,
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
   * @param array &$roots will return all whitelisted folders relative to the
   * returning paths
   * @return type
   */
  protected function getAbsolutePaths( &$roots )
  {
    $paths = [];
    $roots = [];

    foreach( $this->getAssets() as $asset )
    {
      $alias  = $asset[ 0 ];
      $path   = $asset[ 1 ];

      $public = $this->getPath( $alias );
      $public = realpath( $public );
      $path   = $public . DIRECTORY_SEPARATOR . $path;

      array_push( $paths, $path );
      array_push( $roots, $public );
    }

    $paths = array_unique( $paths );
    $roots = array_unique( $roots );

    return $paths;
  }

  /**
   * Will return all feeded assets in one string
   *
   * @param boolean $reset If true then the method will reset the asset and
   * filter stacks.
   * @return string
   */
  public function dump( $reset = true )
  {
    $paths   = $this->getAbsolutePaths( $roots );
    $options = [ 'root' => $roots ];
    $filters = $this->getFilters();
    $assets  = $this->getAssetFactory()->createAsset(
      $paths,
      $filters,
      $options );

    if( $reset )
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
      $this->clearAssets();

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
  protected function clearAssets()
  {
    $this->setAssets( [] );

    return $this;
  }

  /**
   * @param boolean $assets If true, asset stack will be reseted
   * @param boolean $filters If true, filter stack will be reseted
   * @return \AssetManagement\View\Helper\Asset
   */
  public function clear( $assets = true, $filters = true )
  {
    if( $assets )
      $this->clearAssets();

    if( $filters )
      $this->clearFilters();

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
      $assets,
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
      $assets,
      [ $alias, $path ] );

    $this->setAssets( $assets );

    return $this;
  }

  /**
   * @return array
   */
  protected function getFilters()
  {
    if( !isset( $this->filters ) )
      $this->filters = [];

    return $this->filters;
  }

  /**
   * @param array $filters
   * @return \AssetManagement\View\Helper\Asset
   */
  protected function setFilters( array $filters )
  {
    $this->filters = $filters;

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
  protected function clearFilters()
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
    $param = $this->encode();
    $hlper = $this->getServiceLocator()->get( 'url' );
    $url   = $hlper( 'asset', [ 'assets' => $param ] );

    $this->clearAssets();

    return $url;
  }

  /**
   * Encode the containing assets to a url friendly string
   *
   * @return string
   */
  public function encode()
  {
    $filt = $this->getFilters();
    $path = $this->getAssets();

    $data = [ $filt, $path ];

    $data = json_encode( $data );
    $data = gzdeflate( $data, 9 );
    $data = base64_encode( $data );
    $data = urlencode( $data );

    return $data;
  }

  /**
   * Mirror function for 'this->encodeAssets'
   *
   * @param string $data
   * @return \AssetManagement\View\Helper\Asset
   * @see encode()
   */
  public function decode( $data )
  {
    $data = urldecode( $data );
    $data = base64_decode( $data );
    $data = @gzinflate( $data );
    $data = json_decode( $data, true );

    if( empty( $data ) )
      return $this;

    $filt = $data[ 0 ];
    $path = $data[ 1 ];

    $this->setFilters( $filt );
    $this->setAssets( $path );

    return $this;
  }
}