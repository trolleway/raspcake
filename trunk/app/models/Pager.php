<?php
    class Pager extends AppModel {
    	
	var $useTable='statistic';
	
	function GetLinePage($server, $linecode){
		App::import('Model', 'Extline');
		$extline = new Extline;
		$data = $extline->findByLine_code($linecode);
		
		
		return $this->GetHTML($data['Extline']['url'], 'yandex', true);
	}
	
	function GetStationPage($server,$station, $local){


		if ($local){
			return file_get_contents('develop/stations/'.$station['Station']['ext_code'].'.htm');
		}
		else{
		
		return $this->GetHTML('http://rasp.yandex.ru/tablo/station/'. $station['Station']['ext_code'].'?span=schedule&direction=all&type=suburban', 'yandex', true);
		}
	}
	
	function GetHTML($url,$server_code, $record=true){
		
		//записываем статистику обращения к серверу, если надо
		if ($record) {		
		echo '<br>',$url;
		
		$statistic['Pager']['url']=$url;
		$statistic['Pager']['server_code']=$server_code;
			$this->Create();
			$this->save($statistic);
		}
		return file_get_contents($url);
	}
	}
		