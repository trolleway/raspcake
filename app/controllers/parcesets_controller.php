<?php
class ParcesetsController extends AppController {

var $components = array('Validation');
function admin_index() {
	
	//-- Админская авторизация
	$this->Validation->doAuth(); $this->layout = 'admin';
			
	$this->LoadModel('Parceset');
	$this->set('parceset',$this->Parceset->Find('all'));
	
	$this->LoadModel('Station');
	$this->set('stations',$this->Station->Find('list'));
}
	
function admin_add() {
	
	
	//-- Админская авторизация
	$this->Validation->doAuth(); $this->layout = 'admin';
			
			
	if (!(empty($this->data))){

		
		$this->Parceset->Create();
		$parceset_id = $this->Parceset->Save($this->data['Parcesets']);

		$this->redirect(array('action' => 'admin_index'));
	}

}

function admin_edit($parceset_id) {
	
	//-- Админская авторизация
	$this->Validation->doAuth(); $this->layout = 'admin';
			
	$parceset_data = $this->Parceset->findbyid($parceset_id);
	
	$this->set('parceset_data',$this->Parceset->findbyid($parceset_id));
	
	$this->LoadModel('Station');
	$this->set('stations',$this->Station->Find('list'));
}

function admin_populate($parceset_id) {
	
	//-- Админская авторизация
	$this->Validation->doAuth(); $this->layout = 'admin';
			
	
	if (!(empty($this->data))) {
		
		
		
			
			foreach($this->data['Station']['station_id'] AS $station_id){
				$elem['station_id'] = $station_id;
				$elem['parceset_id'] = $parceset_id;
				$elements[]=$elem;
			}
			$this->data['Station'] = $elements;
			
		pr($this->data);
			
			$this->LoadModel('ParcesetsStation');
			$this->ParcesetsStation->Create();
			$this->ParcesetsStation->Parceset_id = $parceset_id;
			$this->ParcesetsStation->SaveAll($this->data['Station']);		
		} 
}

	
}
?>
