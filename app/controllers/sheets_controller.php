<?php
class SheetsController extends AppController {
	
	var $components = array('Validation');


	function index() {
		
		$sheets = $this->Sheet->find('all');		
		$this->set('sheets',$sheets);
	}
		
	function admin_index() {	
	//-- Админская авторизация
	$this->Validation->doAuth(); $this->layout = 'admin';
	
		$this->paginate = array(
				'Sheet'=>array(
			        'limit' => 30,
					'order' => array(
			            'Sheet.id' => 'asc'
			        				) 
							)
		    );
		
	
		$this->set('sheet_list',$this->paginate('Sheet'));
	}
	
	function admin_add() {
		//-- Админская авторизация
		$this->Validation->doAuth(); $this->layout = 'admin';
		
		if (!(empty($this->data))) {
			

			$this->Sheet->Create();
			$this->Sheet->Save($this->data['Sheets']);		
		} 
		
		$this->redirect(array('action' => 'admin_index'));
	}

/**
 * Список простыней пользователю
 * @param object $sheet_id
 * @return 
 */
	function showlist($sheet_id) {

		$sheet_data = $this->Sheet->FindById($sheet_id);
		
		
		$this->LoadModel('Calculation');

		$this->paginate = array(
		'Calculation'=>array(
				'conditions'	=>	array('Calculation.Sheet_id' => $sheet_id,
											'active'=>1),
		        'limit' 		=>	30,
				'fields'		=>	array('id','DATE_FORMAT(created,"%d.%m.%Y") AS created','sheet_id'),
				'order' 		=>	array(
		            						'Created' => 'asc'
		        ) 
			));	
		
		
		$this->Set('sheet_data',$sheet_data);
		$this->Set('calculations_data',$this->paginate('Calculation'));
	}

/**
 * Показывает пользователю расписание в выбраном им формате
 * @param object $calculation_id
 * @param object $method [optional]
 * @return 
 */
function show($calculation_id, $method='html'){
	
	$this->autoRender = false;
	
	$this->loadmodel('Calculation');
	$calculation_data = $this->Calculation->FindById($calculation_id);

	$string = $calculation_data['Calculation']['data'];

	$calculation_data['Calculation']['data'] = unserialize($string);

	

	$this->set('trains',$calculation_data['Calculation']['data']['Trains']);
	$this->set('stations',$calculation_data['Calculation']['data']['Stations']);
	$this->set('sheet',$calculation_data['Calculation']['data']['Sheet']['Sheet']);
	
	$this->set('trains_count',count($calculation_data['Calculation']['data']['Trains']));
	$this->set('stations_count',count($calculation_data['Calculation']['data']['Stations']));
	$this->set('sheet_count',count($calculation_data['Calculation']['data']['Sheet']));
	
	switch ($method){
		case 'html':
			$view_mode='html';
			break;
		case 'pdf':
			$view_mode='pdf';
			$this->_generate_pdf($calculation_data['Calculation']['data']['Trains'],
			$calculation_data['Calculation']['data']['Stations'],
			$calculation_data['Calculation']['data']['Sheet']['Sheet']);
	}
	
	
	$this->render('/sheets/show_'.$view_mode);
	
	
}


function _generate_pdf($trains, $stations, $sheet){
	
				function _prepare_time_for_sheet($data){
					return substr($data,-8,5);
				}
				
				function _make_remarks_text($data, $prev_counter=1){
					//pr($data);
					//echo 'prc='.$prev_counter;
					$text='';
					foreach($data AS $key=> $train){
						if ($key>$prev_counter) {
						$text.='[*'.$key.']  №'.$train['roadnumber'].' '.strip_tags($train['text_day']).";     ";
						}
					}
					return $text;
				}
	

	$res =  App::import('Vendor','tcpdf/tcpdf'); 
	$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);  
	  
	  

		//Разбиваем массив поездов по 50 штук
		$trains_per_part = 53;
		
		$train_counter=0;
		$train_page_counter=1;

		foreach($trains AS $train) 
		{
		    $train_counter++;
		    $sheet_data['pass_by_page'][$train_page_counter][]=$train;
		    if ($train_counter % $trains_per_part == 0) $train_page_counter++;
		    
		}
		//разбили
		
		
	//Задаём параметры PDF файла. Единица измерения - милиметры.
		
	$pdf->SetTitle('TCPDF output');
	$pdf->SetSubject('TCPDF output');

	  // убираем на всякий случай шапку и футер документа
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false); 

	$pdf->SetMargins(10, 10, 10); // устанавливаем отступы (слева, сверху, справа)


	//$pdf->SetXY(15, 10);           // устанавливаем координаты вывода текста в рамке:
	                               // 90 мм - отступ от левого края бумаги, 10 мм - от верхнего

	$pdf->SetDrawColor(200, 200, 200); // устанавливаем цвет рамки (синий)
	$pdf->SetTextColor(0, 0, 0); // устанавливаем цвет текста (зеленый)


	$CELL_TIME_W = 5;	//Ширина ячейки с цифрами
	$CELL_HEIGHT = 2;	//Высота ячеек
	$CELL_STATION_W=10;	//Ширина ячейки со станциями.
	
	$remark_counter=0;	//Инициализация счётчиков для вывода примечаний к поездам
	$prev_remark_counter=0;


	//Цикл по страницам расписания
	foreach($sheet_data['pass_by_page'] AS $page=>$null){
		
		$pdf->AddPage('L', 'A4');
		
		$pdf->SetFont('arial', '', 5);
		$pdf->write(1,'расписание '. $sheet['name'],0);
		$pdf->Ln();
		 
		 
		$pdf->Cell($CELL_STATION_W, $CELL_HEIGHT, '', 1, 0, 'C');	//пустая верхняя левая
		
		// --Номер поезда
		foreach($sheet_data['pass_by_page'][$page] AS $train) {
			$pdf->Cell($CELL_TIME_W, $CELL_HEIGHT, $train['Train']['roadnumber'], 1, 0, 'C',0, '', 2);
		}
	
		$pdf->Ln();
		

		$pdf->Cell($CELL_STATION_W, $CELL_HEIGHT, '', 1, 0, 'C');	//пустая верхняя левая
		// --Дни обращения
		foreach($sheet_data['pass_by_page'][$page] AS $train) {
			
				//Если в тексте о днях обращения много букв, то засовываем его особым образом в массив
				//@TODO это нужно вынести в отделную функцию
				$text_day = $train['Train']['text_day'];
				if (strlen($text_day)>5){
					$remarks[++$remark_counter]=$train['Train'];
					$text_day='[*'.$remark_counter.']';
				}
			
			$pdf->Cell($CELL_TIME_W, $CELL_HEIGHT, $text_day, 1, 0, 'C',0, '', 1);
	
		}

		$pdf->Ln();
		
	foreach($stations AS $station_key => $station){
		// --название станции
		$pdf->Cell($CELL_STATION_W, $CELL_HEIGHT, $station['name'], 1, 0, 'R',0, '', 1);
		
		foreach($sheet_data['pass_by_page'][$page] AS $train_key => $train){
			$time = _prepare_time_for_sheet($sheet_data['pass_by_page'][$page][$train_key]['Pass'][$station['id']]['dep']);
			$pdf->Cell($CELL_TIME_W, $CELL_HEIGHT, $time, 1, 0, 'C',0, '', 2);
		}
		$pdf->Ln();
		
	}
	// --Конец блока
	$pdf->Ln();
	
	
	$remarks_text=_make_remarks_text($remarks,$prev_remark_counter);

	$prev_remark_counter = $remark_counter;
	
	
	$pdf->SetFont('arial', '', 5);
	$pdf->write(1,$remarks_text,0);

}

	$pdf->Output('doc.pdf', 'I'); // выводим документ в браузер, заставляя его включить плагин для отображения PDF (если имеется)
	die;

}

/**
 * Редактирование листа
 * @param object $sheet_id
 * @return 
 */
	function admin_editsheet($sheet_id) {
		
	//-- Админская авторизация
	$this->Validation->doAuth(); $this->layout = 'admin';

		$this->LoadModel('Station');
	
		if (!(empty($this->data))) {
			$this->LoadModel('SheetsStation');
			$this->SheetsStation->SaveAll($this->data['SheetsStation']);		
		} 
		$this->set('sheet_data',$this->Sheet->FindById($sheet_id));
		$this->set('stations',$this->Station->Find('list'));
		$this->set('sheets',$this->Sheet->Find('list'));
		
		
	}
	
	function admin_editsheet_save($sheet_id) {
		
	//-- Админская авторизация
	$this->Validation->doAuth(); $this->layout = 'admin';
		
		if (!(empty($this->data))) {
			$this->LoadModel('SheetsStation');
			$this->SheetsStation->SaveAll($this->data['SheetsStation']);		
		} 
		
		$this->redirect(array('action' => 'admin_editsheet', $sheet_id));
		
	}

	function admin_editsheet_add($sheet_id) {
		
	//-- Админская авторизация
	$this->Validation->doAuth(); $this->layout = 'admin';
		
		if (!(empty($this->data))) {
			$this->LoadModel('SheetsStation');
			foreach($this->data['SheetsStation']['station_id'] AS $station_id){
				$elem['station_id'] = $station_id;
				$elem['sheet_id'] = $sheet_id;
				$elements[]=$elem;
			}
			$this->data['SheetsStation'] = $elements;
			
			
			$this->SheetsStation->Create();
			$this->SheetsStation->Sheet_id = $sheet_id;
			$this->SheetsStation->SaveAll($this->data['SheetsStation']);		
		} 
		
		$this->redirect(array('action' => 'admin_editsheet', $sheet_id));
	
	}
	
	function admin_editsheet_del($sheet_id=1,$record_id=0){
		
	//-- Админская авторизация
	$this->Validation->doAuth(); $this->layout = 'admin';
		
		$this->LoadModel('SheetsStation');
		$this->SheetsStation->delete($record_id,false);
		$this->redirect(array('action' => 'admin_editsheet', $sheet_id));
	}

/**
 * Копирование станций из другого листа, но в обратном порядке. Используется один раз при создании
 * @param object $sheet_id [optional]
 * @return 
 */	
	function admin_editsheet_copy($sheet_id=1){
			
	//-- Админская авторизация
	$this->Validation->doAuth(); $this->layout = 'admin';
		
		$iterator = 0;
		//pr($this->data);
			if (!(empty($this->data))) {
			$this->LoadModel('SheetsStation');
			$source_stations = $this->SheetsStation->find('all', array(
				'conditions'=>array('SheetsStation.sheet_id'=>$this->data['Sheet']['sheet_id']),
				'order'		=> array('SheetsStation.ord'=>'asc')
				));
			//pr($source_stations);die;	
				$source_stations = array_reverse ($source_stations );
				foreach($source_stations AS $key=>$element){
					$source_stations[$key]['SheetsStation']['sheet_id'] = $sheet_id;
					$source_stations[$key]['SheetsStation']['ord'] = ++$iterator;
					unset($source_stations[$key]['SheetsStation']['id']);
				}
				
			$this->SheetsStation->Create();	
			$this->SheetsStation->SaveAll($source_stations);
					
		} 
		
		$this->redirect(array('action' => 'admin_editsheet', $sheet_id));
	}
	
	

/**
 * Управление расчётами
 * @param object $calculation_id
 * @return 
 */		
	function admin_calculates($sheet_id) {
		
	//-- Админская авторизация
	$this->Validation->doAuth(); $this->layout = 'admin';

		$this->LoadModel('Calculation');
		
		if ($this->data){
			$this->Calculation->SaveAll($this->data['Calculation']);
		}
		
		$calculation_data = $this->Calculation->FindAllBysheet_id($sheet_id);
		$this->set('calculation_data',$calculation_data);
		$this->set('sheet_id',$sheet_id);
		$this->set('pagename','Управление сохранёными расчётами');
	}
	
}
?>
