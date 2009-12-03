<!-- Таблица с данными -->
<table class="admin">
	<caption>Cписок заводов</caption>
	<?php echo $html->tableHeaders(array(
	 $paginator->sort('ID', 'ID'),
	 $paginator->sort('Название', 'Name'),
	 $paginator->sort('Создание записи','created'),
	 $paginator->sort('Изменение записи','modified'),
	 '','',''
	 )); ?>
	
	
	<?php foreach($stations_list AS $element){
			 echo $html->tableCells(array(
			$element['Station']['id'],
			'<a href="'.$element['Station']['id'].'">'.$element['Station']['name'].'</a>',
			$element['Station']['created'],
			$element['Station']['modified']
			)); 
	}?>
</table>
<div class="page_section">
<!-- Shows the page numbers -->
<?php echo $paginator->numbers(); ?>
<!-- Shows the next and previous links -->
<?php
	echo $paginator->prev('« Назад ', null, null, array('class' => 'disabled'));
	echo $paginator->next(' Вперёд »', null, null, array('class' => 'disabled'));
?> 
<!-- prints X of Y, where X is current page and Y is number of pages -->
<?php  echo $paginator->counter(array(
	'format' => 'Страница %page% из %pages%, показывается %current% записей
			из %count%, на участке %start%..%end%'
)); 
 ?>
</div>
<!-- Таблица с данными -->