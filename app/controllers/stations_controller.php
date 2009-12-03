<?php
class StationsController extends AppController {

var $components = array('Validation');
	
	
    public function admin_index()
    {       
	
	//-- Админская авторизация
	$this->Validation->doAuth(); $this->layout = 'admin';
			

$this->paginate = array(
		'Station'=>array(
	        'limit' => 30,
			'order' => array(
	            'Station.name' => 'asc'
	        ) 
		)
    );



$stations_list = $this->paginate('Station');
$this->set('stations_list',$stations_list);
	
    }
}
?>
