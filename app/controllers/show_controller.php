<?php
class ShowController extends AppController {

	var $components = array('Validation');
	var $uses=array();
	
	
	function index() {

	}

	function admin_index(){
		
	//-- Админская авторизация
	$this->Validation->doAuth(); $this->layout = 'admin';
			

		$this->loadModel('Extlines');
		$this->loadModel('Sheet');
		$this->loadModel('Parceset');
		
		$lines_list = $this->Extlines->find('all');
		$this->set('lines_codes',$lines_list);
		
				
		$sheets_list = $this->Sheet->find('all');
		$this->set('sheets_codes',$sheets_list);
		
		$this->set('parcesets',$this->Parceset->findall());
	}



}
?>
