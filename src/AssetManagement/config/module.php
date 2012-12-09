<?php

return
  [ 'router' =>
    [ 'routes' =>
      [ 'asset' =>
        [ 'type'    => 'segment',
          'options' =>
          [ 'route'     => '/asset[/:assets]/',
            'defaults'  =>
            [ 'controller' => 'AssetManagement\Index',
              'action'     => 'index',
              'assets'     => '' ] ] ] ] ],

    'controllers' =>
    [ 'invokables' =>
      [ 'AssetManagement\Index' => 'AssetManagement\Controller\IndexController' ] ],

    'view_manager' =>
    [ 'template_map' =>
      [ 'asset-management/index/index' => __DIR__ . '/../view/asset-management/index/index.phtml', ],

      'template_path_stack' =>
      [ __DIR__ . '/../view' ] ],

    'view_helpers' =>
    [ 'invokables' =>
      [ 'asset' => 'AssetManagement\View\Helper\Asset' ] ],

    'asset_management' =>
    [ 'debug' => false,
      'filter_map' =>
      [ /*[ 'alias' => 'css_embed',
          'class' => '\Assetic\Filter\CssEmbedFilter' ],
          'param' => 'something'
       */

        [ 'alias' => 'css_import',
          'class' => '\Assetic\Filter\CssImportFilter' ],

        [ 'alias' => 'css_min',
          'class' => '\Assetic\Filter\CssMinFilter' ],

        [ 'alias' => 'css_rewrite',
          'class' => '\Assetic\Filter\CssRewriteFilter' ] ] ] ];