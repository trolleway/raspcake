
<?php
pr($parceset_data);

?>


<?php echo $form->create('Parceset',array('action'=>'populate/'.$parceset_data['Parceset']['id'].'')); ?>


<?php
/*
echo $form->input('Parceset.Station.id', array(
			'type'		=>'hidden',
			'default'	=>$sheet_data['Sheet']['id']
			));
			*/
?>



<?
echo $form->input('Station.station_id', array(
	'multiple'	=>true,
	'options'	=> $stations
	)	);
	
	echo $form->end('Добавить'); 
?>
