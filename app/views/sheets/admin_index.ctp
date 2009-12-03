
<!-- Таблица с данными -->
<table class="admin">
	<caption>Cписок листов</caption>
	<?php 

	
	echo $html->tableHeaders(array('id','name'
	 
	 )); ?>
	
	
	<?php foreach($sheet_list AS $element){
			 echo $html->tableCells(array(
			$element['Sheet']['id'],
			$element['Sheet']['name'],
			'<a href="/admin/sheets/editsheet/'.$element['Sheet']['id'].'"> редактировать </a>'
			)); 
	}?>
</table>

<? echo $form->create('Sheets'); ?>
<? echo $form->input('name', array('label'=>'Название')); ?>

<? echo $form->end('Добавить простыню'); ?>

