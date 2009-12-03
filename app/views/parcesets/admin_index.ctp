
<!-- Таблица с данными -->
<table class="admin">
	<caption>Cписок групп парсинга</caption>
	<?php 

	
	echo $html->tableHeaders(array('id','name'
	 
	 )); ?>
	
	
	<?php 
	
	foreach($parceset AS $element){
			 echo $html->tableCells(array(
			$element['Parceset']['id'],
			$element['Parceset']['name'],
			'<a href="/admin/parcesets/edit/'.$element['Parceset']['id'].'"> редактировать </a>'
			)); 
	}
	
	?>
</table>

<? echo $form->create('Parcesets', array('action'=>'')); ?>
<? echo $form->input('name', array('label'=>'Название')); ?>

<? /*echo $form->input('Parcesets.Station.station_id', array(
	'multiple'	=>true,
	'options'	=> $stations
	)	);
	*/
	?>

<? echo $form->end('Добавить группу'); ?>

