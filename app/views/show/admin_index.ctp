Это главная страница админки
<?php

?>
<ul>
	<li>Занести станции в базу
	<ul>
		<?php foreach($lines_codes AS $linecode){ ?>
		<li><a href="/admin/parcer/importstations/yandex/<?=$linecode['Extlines']['line_code']?>"><?=$linecode['Extlines']['line_code']?></a></li>
					<?php } ?>
	</ul>
	</li>
	<li>Парсинг проходов по станциям
	<ul>
		<?php foreach($parcesets AS $parceset){ ?>
		<li><a href="/admin/parcer/importtimes/yandex/<?=$parceset['Parceset']['id']?>"><?=$parceset['Parceset']['name']?></a></li>
		<?php } ?>
	</ul>
	</li>
	<li><a href="/admin/parcer/midnighting/">Принудительный запуск пересчёта ночных поездов</a></li>
	<li><a href="/admin/sheets/">Простыни</a></li>
	<li>Запуск расчёта простыни
	<ul>
		<?php foreach($sheets_codes AS $sheetcode){ ?>
		<li><a href="/admin/parcer/calculate_sheet/<?=$sheetcode['Sheet']['id']?>"><?=$sheetcode['Sheet']['name']?></a></li>
		<?php } ?>
	</ul>
	</li>
	<li><a href="/admin/stations/">Станции</a></li>
	
	<li>Сохранёные расчёты
	<ul>
		<?php foreach($sheets_codes AS $sheetcode){ ?>
		<li><a href="/admin/sheets/calculates/<?=$sheetcode['Sheet']['id']?>"><?=$sheetcode['Sheet']['name']?></a></li>
		<?php } ?>
	</ul>
	</li>
	
	<li><a href="/admin/parcesets/">Группы станций для парсинга</a></li>

</ul>
