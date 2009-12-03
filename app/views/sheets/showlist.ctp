Доступные расписания для листа <?php echo $sheet_data['Sheet']['name'];?>

<?php
    $paginator->options(
            array(
                    'url'=>array('controller'=>'sheets', 'action'=>'showlist/'.$sheet_data['Sheet']['id']), 
                    'indicator' => 'LoadingDiv'));
?> 


<?php
	//pr($calculations_data);
	?>
	<table>
<?php 
	foreach($calculations_data AS $element){
		echo '<tr>';
		echo $html->tableCells(array(
		$element[0]['created'],
		$html->link ('html','/sheets/show/'.$element['Calculation']['id'].'/html'),
		$html->link ('pdf','/sheets/show/'.$element['Calculation']['id'].'/pdf'),
		)
		);
		
	}
?>
	
</table>


<?php echo $paginator->numbers(); ?>

<?php echo $paginator->counter(); ?>