<?php

foreach($calculation_data AS $calculation_data){
	
	$form->data = $calculation_data;
?>

<?php echo $form->create('Sheet',array('action'=>'calculates/'.$sheet_id.'')); ?>
<?php echo $form->input('Calculation.'.$calculation_data['Calculation']['id'].'.id', 
array('type'=>'hidden'));

 ?>
<table>
	<th>Код</th>
	<th>Создано</th>
	<th>Активно</th>
	<tr>
		<?php
				echo $html->tableCells(array(
		$calculation_data['Calculation']['id'],
		$calculation_data['Calculation']['created'],
		"\n".$form->input('Calculation.'.$calculation_data['Calculation']['id'].'.active', array(
			
			'value'=>$calculation_data['Calculation']['active'],
			'label'=>'')),
		
		$html->link('Х','/admin/sheets/calculates_del/'.$calculation_data['Calculation']['id'])
		,
		$form->end('Редактировать')));
		?>
	</tr>
</table>

<?php ?>


<?
}
?>