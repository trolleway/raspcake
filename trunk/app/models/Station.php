<?php
    class Station extends AppModel {
    	
	/**
	 * Добавление набора станций с проверкой по коду ЭКСПРЕСС на дублирование
	 * @param object $stations
	 * @return 
	 */	
	function AddStations($stations){
		
		//составляем список кодов, и сохраняем ключи массива станций
			foreach($stations AS $key=>$element){
				$station_codes[]=$element['Station']['ext_code'];
				$keys[$element['Station']['ext_code']]=$key;
			}
		//выбираем из базы станции с кодами
		$statons_in_base=$this->find('list',array(
		'conditions'=>array('Station.ext_code'=>$station_codes),
		'fields'=>array('Station.ext_code','Station.name')
			)
			);

		//выкидываем из массива станции которые есть
		foreach($statons_in_base AS $key=>$element){

			unset($stations[$keys[$key]]);
		$results[]="Не записываем в базу станцию $key-$element, уже есть";	
		}
		

		
		//добавляем в базу если что осталось
		foreach($stations AS $element){
			$results[]="Сохраняем в базе станцию ".$element['Station']['name'];
		}
		$this->saveAll($stations);
		return $results;
	}
	
		
	}
?>