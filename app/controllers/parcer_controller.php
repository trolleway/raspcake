<?php
class ParcerController extends AppController {

	var $components = array('Validation');
	var $uses=array();
	
	
	/**
	 * Парсинг новых станций в базу со страницы схемы линии
	 * @param object $server
	 * @param object $linecode
	 * @return 
	 */
    public function admin_importstations($server,$linecode)
    {   
			
		/*
		 * (код линии) 
		 * [пейджер]
		 * (html)
		 * [parcer]
		 * (станции)
		 * [stations]
		 * (record flag)
		 * */	    
			//-- Админская авторизация
			$this->Validation->doAuth(); $this->layout = 'admin';
	
		    $this->loadModel('Pager');
			$this->loadModel('Parcer');
			$this->loadModel('Station');
		
			$html = $this->Pager->GetLinePage($server, $linecode);	
			$stations = $this->Parcer->ParceLine2Station($html, $linecode);
		
			$save_report = $this->Station->AddStations($stations);
		 
		 	$this->set('save_report',$save_report);
    }
	
	/**
	 * Парсинг времени прохода поезддов по станциям
	 * @param object $server
	 * @param object $parceset_id код набора станций
	 * @param object $local [optional] читать из сохранённых страниц с этого сервера, для отладки.
	 * @return 
	 */
	public function admin_importtimes($server,$parceset_id, $local=false){
		
			//-- Админская авторизация
			$this->Validation->doAuth(); $this->layout = 'admin';
			
		set_time_limit(600);
		
		$this->loadModel('Parcer');
		$this->Parcer->importtimes($server,$parceset_id, $local);

		$save_report=array();
		$this->set('save_report',$save_report);
	}
	
	/**
	 * Пересчёт поездов следующих через полночь
	 * @return 
	 */
	public function admin_midnighting(){
		
			//-- Админская авторизация
			$this->Validation->doAuth(); $this->layout = 'admin';
			$this->loadModel('Train');
			$this->Train->midnighting();
	}
	
	/**
	 * Расчёт простыни
	 * @param object $sheet_id
	 * @return 
	 */
	public function admin_calculate_sheet($sheet_id){
		
			//-- Админская авторизация
			$this->Validation->doAuth(); $this->layout = 'admin';
		$this->loadModel('Parcer');
		$sheet_data = $this->Parcer->get_sheet($sheet_id);
		
		$this->loadModel('Calculation');
		
		pr($sheet_data);
		
		$this->Calculation->Create();
		$this->Calculation->Save($sheet_data);
		
		$this->set('sheet_data',$sheet_data);
		$this->set('statistic',$sheet_data['statistic']);
	}
	
	public function admin_generate_files($calculation_id) {
		
		
			//-- Админская авторизация
			$this->Validation->doAuth(); $this->layout = 'admin';
		
	}
	
}
?>
