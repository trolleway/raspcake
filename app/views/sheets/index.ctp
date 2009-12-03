Выберите расписание.
<ul>
	
<?php
//pr($sheets);
	foreach($sheets AS $sheet){
		//pr($sheet['Sheet']);
		echo '<li><a href="/sheets/showlist/'.$sheet['Sheet']['id'].'">'.$sheet['Sheet']['name']."</a></li>\n";
	}
?>
</ul>