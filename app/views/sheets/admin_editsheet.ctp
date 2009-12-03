Редактирование расписания <?php echo $sheet_data['Sheet']['name'];?>

<?php
//pr($sheet_data);
?>

<?php echo $form->create('Sheet',array('action'=>'editsheet_save/'.$sheet_data['Sheet']['id'].'')); ?>




<table>
	<tr>
		
	</tr>
	<?php //foreach($)
	
	
	foreach($sheet_data['Station'] AS $station_ic => $station){
		
		echo $form->input('SheetsStation.'.$station['SheetsStation']['id'].'.id', array(
		'default'	=>$station['SheetsStation']['id'],
		'type'		=> 'hidden'
		));
		
		echo $html->tableCells(array(
		$station['id'],
		$station['name'],
		$form->input('SheetsStation.'.$station['SheetsStation']['id'].'.ord', array(
			'default'=>$station['SheetsStation']['ord'],
			'label'=>'')),
		'('.$station['SheetsStation']['ord'].')',
		$html->link('Х','/admin/sheets/editsheet_del/'.$sheet_data['Sheet']['id'].'/'.$station['SheetsStation']['id'])
		));
	}
	?>
</table>

<?php echo $form->end('Редактировать порядок'); ?>

<?php echo $form->create('Sheet',array('action'=>'editsheet_add/'.$sheet_data['Sheet']['id'].'')); ?>


<?php
echo $form->input('Sheet.SheetsStation.sheet_id', array(
			'type'		=>'hidden',
			'default'	=>$sheet_data['Sheet']['id']
			))
?>


<?
echo $form->input('SheetsStation.station_id', array(
	'multiple'	=>true,
	'options'	=> $stations
	)	);
	
	echo $form->end('Добавить'); 
?>

Вставить станции в обратном порядке из простыни:
<?php  echo $form->create('Sheet',array('action'=>'editsheet_copy/'.$sheet_data['Sheet']['id'].'')); ?>

<?php

echo $form->input('Sheet.sheet_id', array(
			
			'options'	=>$sheets
			))
?>
<?php echo $form->end('Копировать'); ?>