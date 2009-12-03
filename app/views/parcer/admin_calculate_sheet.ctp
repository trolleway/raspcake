Отчёт о расчёте простыни
<table>
	<tr>
		<td style="color:red">
			Станции
<?php
    //pr($sheet_data['Stations']);
?>

	<td style="color:green">
		<?php
    //pr($sheet_data['Trains']);
?>
	</td>
	
	<td style="color:green">
		<?php
    //pr($sheet_data['Sheet']);
?>
	</td>

	
</tr>

</table>

Статистика работы:
станций: <?php echo $statistic['stations_count']; ?>
поездов:  <?php echo $statistic['trains_count']; ?>