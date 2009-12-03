<?php
    class Parcer extends AppModel {
    	var $useTable=false;
		

/**
 * Парсинг страницы со списком станций с Яндекса для добавления новых станций во БД
 * @param object $html html страницы из интернета
 * @param object $linecode [optional] Яндексовский код линии (для удобства дальнейшей сортировки)
 * @return Значения для добавления в базу
 */		
function ParceLine2Station($html, $linecode=''){
	
	preg_match_all ('/(b-scheme-station-list js-list g-hidden)(.*)<div class="b-scheme js-scheme/s', $html, $founds);
	preg_match_all ('/ablo\/station\/(.*?)\?type(?:.*?)">(.*?)<\/a>/s',$founds[0][0] , $founds2,PREG_SET_ORDER);
	 
	foreach($founds2 AS $element)
	{
		$station['ext_code']=$element[1];
		$station['name']=$element[2];
		$station['ext_line']=$linecode;
		$station['id'] = $station['ext_code'];	//ключ - это код Экспресс
		
		$thing['Station']=$station;
		$stations[]=$thing;
	}
	return $stations;
}
		
/**
 * Добавление в базу поездов и проходов по станциям
 * @param object $server Сервер
 * @param object $parceset_id Код набора станций
 * @param object $local [optional] Отладка - брать данные из сохранёных страниц
 * @return 
 */
function importtimes($server,$parceset_id, $local=false){
		App::import('Model','Train');
		$train_model = new Train;
		
		App::import('Model','Pass');
		$pass_model = new Pass;
				
		App::import('Model','Station');
		$station_model = new Station;
						
		App::import('Model','Pager');
		$pager_model = new Pager;
								
		App::import('Model','Parcer');
		$Parcer_model = new Parcer;
		
		App::import('Model','Parceset');
		$Parceset_model = new Parceset;
		
		//Удаляем все поезда и проходы из базы.
		
		$train_model->query('TRUNCATE '.$train_model->tablePrefix.$train_model->table); //согласно API
		$pass_model->query('TRUNCATE '.$pass_model->tablePrefix.$pass_model->table);
			
		//Выбираем нужные станции из базы
		$stations_data = $Parceset_model->findbyid($parceset_id);
		
			//Смена структуры данных, что бы они принимались функциями парсинга.	
			foreach($stations_data['Station'] AS $element){
				$st['Station']['id'] = $element['id'];
				$st['Station']['ext_code'] = $element['ext_code'];
				$st['Station']['name'] = $element['name'];
				$stations[]=$st;
			}
			
		//Выполнение парсинга и добавление в базу	
			foreach ($stations AS $station){			
		//получаем код страниц		
				$html=$pager_model->GetStationPage($server, $station,$local);	
				
		//получаем поезда
				$trains=$Parcer_model->parce_station($html,$station['Station']['id']);

		//сохраняем поезда с проходами в базе
				$train_model->add_trains_and_pass(&$trains);
		}	
}

/**
 * Парсинг страницы станции с яндекса, для получения данных о времения прохода поездов
 * @param object $html
 * @param object $station_id
 * @return 
 */
	function parce_station($html,$station_id)
    {
        
        preg_match_all ('/(<tbody>)(.*)<\/tbody>/s', $html, $founds);
        preg_match_all ('/\/thread\/(.*?)"(?:.*?)\}">(.*?)<\/strong(?:.*?)<td>(?:.*?)<\/td>(?:.*?)<td>(.*?)<\/td>/s',$founds[0][0] , $founds2,PREG_SET_ORDER);
        
        
        foreach($founds2 AS $element)
        {
        		//Поезд:
            $train['external_code']=trim($element[1]);
            $train['roadnumber'] = substr($element[1],0,4);
            $train['text_day']=trim($element[3]);
            $train['text_day']=$this->_process_day_string($train['text_day']);
			
				//Время прохода:
            $pass=array(
                                'station_id'=>$station_id,
                                'dep'=>$element[2]
                                );
								
			//Заполнение массива данных согласно схеме CakePHP					
            $push_element['Train'] = $train;
			$push_element['Pass'] = $pass;		
            $trains[]=$push_element;
        }
        return $trains;
    }
    
/**
 * Простая обработка строки о днях следования
 * @param object $input [optional]
 * @return 
 */
    private function _process_day_string($input='')
    {
        $day_string_transform_matrix['ежедневно']='';
        $day_string_transform_matrix['по будням']='Р';
        $day_string_transform_matrix['по выходным']='В';
        $day_string_transform_matrix['кроме сб']='КС';
        $day_string_transform_matrix['сб']='СБ';
        $day_string_transform_matrix['кроме пятниц и выходных']='КПВ';
        
     
        foreach ($day_string_transform_matrix AS $key=>$element)
        {
            $input = str_replace($key, $element, $input);
        }
        return $input;
    }	
	
	
	
/**
 * Расчёт листа расписания
 * @param object $sheet_id
 * @return 
 */	
	function get_sheet($sheet_id){
		
		App::import('Model','Sheet');
		$Sheet_model = new Sheet;
		
		App::import('Model','Train');
		$Train_model = new Train;
				
		$sheet = $Sheet_model->findById($sheet_id);	
			// порядок станций задаётся в модели Sheet

		$stations = $sheet['Station']; 
			//Все станции простыни по порядку, чтоб далеко не лазить			
		
		$trains = $Train_model->find('all');
			//Сейчас при парсинге из интернета, все другие поезда удаляются, и достаточно такой выборки

		
		
			// ---- В массиве проходов поезда мы заменяем ключи в [Pass] с простых на id станции. Так потом проще будет проверять
		foreach ($trains AS $train){
			$train2 = $train;
			$train2['Pass'] = $this->change_keys_to_idstation($train['Pass']);
			$trains2[]=$train2;
		}
		$trains = $trains2;

			// ---- Убираем поезда, следующие в неправильном направлении
		
		foreach($trains AS $key=>$train)
        {
            if ($this->is_train_direction_invalid($trains[$key], $stations))
            {
                unset($trains[$key]);
            } 	           
        }
		
		
		// --- Удаление поездов которые проходят только по 1 станции 
		
        foreach($trains AS $key=>$train)
        {
            $stations_count=0;
            foreach ($stations AS $station)
            {
                if ($train['Pass'][$station['id']]['dep'] != '') 
                {
                    $stations_count++;
                }
            }
			
            if ($stations_count<2)
            {
                unset($trains[$key]);  
            }                  
        }
		
		
        // --- Заносим в каждый поезд ключи всех станций - но с пустым значением. Это необходимо для сортировки
		
        foreach($trains AS $key=>$train)
        {           
            foreach($stations AS $station)
            {
            	$trains[$key]['pass_temp'][$station['id']]['dep'] = ($train['Pass'][$station['id']]['dep'] 	? $train['Pass'][$station['id']]['dep'] :' ');
            }
			$trains[$key]['Pass'] = $trains[$key]['pass_temp']; 
			unset($trains[$key]['pass_temp']);	 
        }
		
		// --- Выполнение сортировки поездов
		usort($trains,array('Parcer','cmp') );
		

		$out['data']['Stations']= $sheet['Station'];
		unset($sheet['Station']);
		$out['data']['Sheet']= $sheet;
		
		$out['data']['Trains']= $trains;
		$out['sheet_id'] = $sheet['Sheet']['id'];
		
		
		$out['data']  = serialize($out['data']);
		
		$out['statistic']['trains_count'] = count($trains);
		$out['statistic']['stations_count'] = count($out['data']['Stations']);
		
		return $out;
		
	}
	
   /**
    *   Определение правильности направления поезда
    *   @param $pass array 
    *   @param $stations array 
    *   @return boolean
    */
function is_train_direction_invalid($train, $stations, $debug=false)
{   

	error_reporting(0);      
	$saved_time=0;
	

	if ($debug) pr($train);	      
	foreach($stations AS $station)
	{
		if ($debug) echo "<br> station=$station[id] ";
		
		if (isset($train['Pass'][$station['id']]['dep']))
		{
			
		    $now_time=$train['Pass'][$station['id']]['dep'];     
		}
		else
		{
			
		    $now_time=0;
		}
		
		if ($debug) echo "
		<br> now_time=$now_time  
		saved_time=$saved_time 
		already_saved=$already_saved 
		";
		
		if (($saved_time==0) AND ($already_saved==false))
		{

		    if ($this->is_time_valid($now_time))
		    {
		        if ($debug) echo "cхороняю";
		        $saved_time=$now_time;
		        $already_saved = true;
		    }
			else
			{
				
			}
		}
		
		      
		if ($this->is_time_valid($now_time))
		{
		       
		    if ($now_time < $saved_time) 
		    {
		        return true;
		    }
		
		}
	}

	
	return false;
	        
		}
		
function 	change_keys_to_idstation ($data){
	foreach($data AS $key=>$element){
		$out[$element['station_id']] = $element;
	}
	return $out;
}	

private function is_time_valid($time)
	{
	 	return strpos($time,':');
	}
	
// --- Функции сравнения поездов --	
	
	protected    function cmp_2($a, $b)
                {
                    if ($a<$b) return -1;
                    if ($a>$b) return 1;
                    return 0;
                }    
    static   function cmp($a,$b,  $debug=false)
    {

               if ($debug) pr($a);
			   
               if ($debug) pr($b);
                             
               if ($debug) echo '<pre>';
			   
                foreach($a['Pass'] AS $station_id => $pass)
                { 
                    $a_time=$pass['dep'];
                    $b_time=$b['Pass'][$station_id]['dep'];
                    
                    if (trim($a_time)=='') continue;
                    if (trim($b_time)=='') continue;
                    
                   if ($debug) echo "\n station_id=$station_id | A=$a_time | B=$b_time";
                   
                   $temp_result=Parcer::cmp_2($a_time, $b_time);
                   if ($temp_result) return $temp_result; 
                   
                   if($debug) {
                       echo " temp_result = $temp_result";
                       
                   }

                }
                
                if ($debug) echo "\n нет общий станции";
                
                //Если же у поездов нет общий станций
                $a_saved = 0;
                $b_saved = 0;
                #$stations = $this->stations;
                //цикл для всех станций
                
                
                
                foreach($a['Pass'] AS $station_id => $pass)
                {                 
                    $a_time = trim($pass['dep']);
                    $b_time = trim($b['Pass'][$station_id]['dep']);
                    
                   if ($debug) echo "\n station=$station_id | a_time=$a_time | b_time=$b_time | a_saved=$a_saved | b_saved=$b_saved"; 
                //если правое время не пустое, а сохранёное правое пустое, и левое  сохранёное не пустое, то сравнение (правое текущее и левое сохранёное)
                    if ($b_time && !($b_saved) && $a_saved) 
                    {
                        if ($debug) echo "Тип 1: правое время не пустое, а сохранёное правое пустое, и левое  сохранёное не пустое";
                       
					    //$result = Sheet_Model::cmp_2($b_time, $a_saved);
						$result = Parcer::cmp_2($a_saved,$b_time );
						
						return $result;
                        
                    }
                //Если левое  время не пустое, а сохранёное левое  пустое, и правое сохранёное не пустое, то сравнение (левое текущее и правое сохранёное)
                     if ($a_time && !($a_saved) && $b_saved) 
                     {
                         if ($debug) echo " левое  время не пустое, а сохранёное левое  пустое, и правое сохранёное не пустое";
                         return Parcer::cmp_2($a_time, $b_saved);
                     }
                
                //Запоминаем время последнего прохода по правому и левому
                     if ($a_time) $a_saved = trim($a_time);
                     if ($b_time) $b_saved = trim($b_time);
                
                }


                return 0;
                if ($debug)                {                    die ("\nчто тебе надо у меня  дома?");                }    
    }
	
	}
?>