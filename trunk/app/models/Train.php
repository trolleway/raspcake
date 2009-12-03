<?php
    class Train extends AppModel {
    	var $hasMany = 'Pass';
		var $train_cashe = array();////список кодов поездов, которые добавили в базу за время существования обьекта
		
		function test(){
			echo 'response';
		}
		
		
		
		
		public function add_trains_and_pass($trains)
		{


    foreach ($trains AS $train)
    {       
        unset($train_id);
        $train['Train']['id']=$this->get_id_from_cashe($train['Train']['external_code']);
        if (!($train['Train']['id']))
        {
        	$out['added_trains'][]=$train['Train'];
            echo "<br>Добавляем в базу поезд ".$train['Train']['roadnumber'];
           $this->Create();
		   $train = $this->Save($train);
		   $train['Train']['id']=$this->id;
		   $train['Pass']['train_id']=$this->id;
		   
		   $this->insert_to_cashe($train['Train']['external_code'],$train['Train']['id']);
		   
		}
		else{
			 $train['Pass']['train_id']=$train['Train']['id'];
		}
		$train['Pass']['dep'] = '1971-01-01 '.$train['Pass']['dep'];
		
										
		App::import('Model','Pass');
		$Pass_model = new Pass;
		
		$Pass_model->Create();
		$Pass_model->Save($train);
		}   
		return $out;
           /*
		    $sql="INSERT INTO trains SET 
            roadnumber='$train[roadnumber]',
            text_day='$train[text_day]',
            external_code = '$train[code]'";
            
            #echo $sql;
            $query = $this->db->query($sql);
            $train['inner_code']=$query->insert_id();
            $this->insert_to_cashe($train['code'],$train['inner_code']);
            $train_id =$train['inner_code']; 
            */
            
        
        //вставка записи о проследовании
		/*
            $sql="INSERT INTO pass SET train_id=$train_id, station_id = ".$train['pass']['station'].", dep='1971-01-01 ".$train['pass']['dep']."'";
            $query = $this->db->query($sql);
            #echo "\n$sql";
            */
    }

		
    
     function get_id_from_cashe($external_code)
    {
        if (array_key_exists($external_code,$this->train_cashe))
        {
        return $this->train_cashe[$external_code];
        }
        else
        {
        return false;
        }
        /*
        foreach ($this->train_cashe AS $arr)
        {
            if ($this->train_cashe['code']==$external_code) return $true;
        }
        */
    }
    
     function insert_to_cashe($code, $inner_code)
    {
        $this->train_cashe[$code] = $inner_code;
    }		
		
		
	
	function midnighting(){
		
    $MAX_TIME_SPAN = 60*60*10 ;//#max время перегонного хода
    $MAX_BOUND_TIME_SPAN = 60*60*20 ;//#max время между крайними станциями
    $prev_time_span = null;
    $temp_text = '';
	
	 /* граничное время для выборки поездов, 
	  * которые будем тут обрабатывать, что бы не обработать два раза.
	  * Если при импорте удаляются все поезда, то это в принципе неважно
	  */
	$BOUND_CONDITION_TIME = '1971-01-01 02:00';
	
	// Поехали
	
	App::import('Model','Pass');
	$Pass_model = new Pass;
	
	//Выбираем поезда, которые будем обрабаотывать (пока все из базы)
	
	$trains = $Pass_model->find('all',array(
	'fields'		=>	array('DISTINCT Pass.train_id'),
	'conditions'	=>	array('Pass.dep <' => $BOUND_CONDITION_TIME),
	
	'limit'			=>	0
	));
	
pr($trains);
	
	foreach($trains AS $train){
			$train_id = $train['Pass']['train_id'];
			//Данные о проследовании поезда по станциям
		unset ($passes);
		$passes = $Pass_model->find('all',array(
			'fields'		=>	array('UNIX_TIMESTAMP(dep) AS dep','dep AS dep_real'),
			'conditions'	=>	array('train_id'=>$train['Pass']['train_id']),
			'order'			=>	array('Pass.dep')
			)
		);
		
		echo "<hr><table bgcolor=grey><tr><td>train_data";
		pr($train);
		echo "<td> pass data<br>";
		pr($passes);
		echo "</table>";
		
		$prev_time_span=null; 
        $frist_station_time=0;
        $max_time_span = array('value'=>0,'time'=>0);
		
		foreach ($passes AS $pass)
            {   
			
                if (!$prev_time_span) $prev_time_span=$pass[0]['dep'];
                if (!$frist_station_time) $frist_station_time=$pass[0]['dep'];
                echo "<br>".$pass[0]['dep']." ".$pass['Pass']['dep_real'];
                
				$current_time_span=$pass[0]['dep']-$prev_time_span;
                if ($current_time_span>$max_time_span['value']) $max_time_span=array ('value'=>$current_time_span, 'time'=>$pass['Pass']['dep_real']);
                
				echo " - $current_time_span";
                $prev_time_span=$pass[0]['dep'];

            }
		
            echo "<br> максимальное время хода поезда: $max_time_span[value] во время $max_time_span[time]";
            $diffirence_last_frist = $pass[0]['dep']-$frist_station_time;
            echo "<br> Разница между крайними станциями - $diffirence_last_frist";
            if ($max_time_span['value']>$diffirence_last_frist) echo "\nПолуночный";
            
            /*
            Условие определения полуночного поезда: 
            если самое большое время перегонного хода больше 10 часов 
            И разница между крайними станциями больше 20 часов 
            И поезд пригородный            
            */
            
            if (    ($max_time_span['value']> $MAX_TIME_SPAN) &&
                    ($diffirence_last_frist > $MAX_BOUND_TIME_SPAN)
                )
                {
                    echo "<br> этот";
                    $sql="UPDATE ".$Pass_model->tablePrefix.$Pass_model->table." SET dep=addDATE( dep, INTERVAL 1 DAY) WHERE train_id=$train_id AND dep < '$max_time_span[time]' ";
					$Pass_model->query($sql);

					echo "<br> $sql";

                }
	}
	
	
	

return;

    $result = $db->query('SELECT DISTINCT train_id FROM pass WHERE  dep < "1971-01-01 02:00"');
    foreach($result->result_array(false) AS $row)
        {
            echo "\n Поезд ID=$row[train_id] ";
            unset ($passes);
            $result_train = $db->query("SELECT UNIX_TIMESTAMP(dep) AS dep, dep AS dep_real from pass where train_id =$row[train_id] ORDER BY dep");
            foreach($result_train->result_array(false) AS $row_pass)
            {
                $passes[]=$row_pass;
            }
            $prev_time_span=null; 
            $frist_station_time=0;
            $max_time_span = array('value'=>0,'time'=>0);
            foreach ($passes AS $pass)
            {   
                if (!$prev_time_span) $prev_time_span=$pass['dep'];
                if (!$frist_station_time) $frist_station_time=$pass['dep'];
                echo "\n$pass[dep] ($pass[dep_real])";
                $current_time_span=$pass['dep']-$prev_time_span;
                if ($current_time_span>$max_time_span['value']) $max_time_span=array ('value'=>$current_time_span, 'time'=>$pass['dep_real']);
                
                
                
                echo " - $current_time_span";
                
                $prev_time_span=$pass['dep'];
                #if (
            }
			
            echo "\n максимальное время хода поезда: $max_time_span[value] во время $max_time_span[time]";
            $diffirence_last_frist = $pass['dep']-$frist_station_time;
            echo "\n Разница между крайними станциями - $diffirence_last_frist";
            if ($max_time_span['value']>$diffirence_last_frist) echo "\nПолуночный";
            
            /*
            Условие определения полуночного поезда: 
            если самое большое время перегонного хода больше 10 часов 
            И разница между крайними станциями больше 20 часов 
            И поезд пригородный            
            */
            
            if (    ($max_time_span['value']> $MAX_TIME_SPAN) &&
                    ($diffirence_last_frist > $MAX_BOUND_TIME_SPAN)
                )
                {
                    echo "\n этот";
                    $sql="UPDATE pass SET dep=addDATE( dep, INTERVAL 1 DAY) WHERE train_id=$row[train_id] AND dep < '$max_time_span[time]' ";
                    echo "\n $sql";
                    $db->query($sql);
                }
            
        }
       
	}	
		
		
		
	}
?>