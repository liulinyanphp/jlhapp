<?php
namespace Admin\Controller;

class IndexController extends AdminBaseController {

	public function indexAction(){
		$this->display('index');
	}
}