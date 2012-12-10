<?php

namespace AssetManagement\Controller;

use Zend\Mvc\Controller\AbstractActionController,
    Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
  /**
   * Sets the viewmodel to a terminal mode and loads it with the assets
   * parameter
   *
   * @return \Zend\View\Model\ViewModel
   */
  public function indexAction()
  {
    $vm = new ViewModel();
    $vm->setTerminal( true );
    $vm->assets = $this->params( 'assets' );
    return $vm;
  }
}
