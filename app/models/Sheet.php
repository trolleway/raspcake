<?php
    class Sheet extends AppModel {
    	

	var $hasAndBelongsToMany = array(
	'Station' => array(
		'class_name'	=> 'Station',
		'joinTable'		=> 'sheets_stations',
		'foreignKey' 	=> 'sheet_id',
		'order'			=> 'ord'
		
						)
						);
						
						
					
		
	}
?>