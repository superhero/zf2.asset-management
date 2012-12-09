<?php

namespace AssetManagement\Controller;

use Zend\Mvc\Controller\AbstractActionController,
    Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
  public function indexAction()
  {
    $vm = new ViewModel();
    $vm->assets = $this->params( 'assets' );
    return $vm;
  }
}
