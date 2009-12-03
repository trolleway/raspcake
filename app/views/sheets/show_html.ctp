	<style type="text/css">
table {
 font-size: 10px; /* Размер шрифта */
 font-family: Verdana, Arial, Helvetica, sans-serif; /* Семейство шрифта */
 border: 1px solid;
 border-collapse: collapse;
}
.station_field
{
    width: 150px;
}

table td
{
      border: 1px solid;
  border-collapse: collapse;
}

h1
{
    text-align: center;
}

.roadnumber
{
    vertical-align: bottom;
}


</style>
<title>Расписание движения пригородных поездов по линии <?php echo $sheet['name']; ?></title>
</head><body>
<h1>Расписание движения пригородных поездов по линии <?php echo $sheet['name']; ?></h1>
<lj-cut>
<!--пуще брежнева!-->

<?
//Разбиваем массив поездов по 50 штук
$trains_per_part = 50;

$train_counter=0;
$train_page_counter=1;



foreach($trains AS $train) 
{
    $train_counter++;
    $sheet_data['pass_by_page'][$train_page_counter][]=$train;
    if ($train_counter % $trains_per_part == 0) $train_page_counter++;
    
}

?>




<? foreach($sheet_data['pass_by_page'] AS $page=>$null) 
{

    ?>
    
<table>

<tr><td><img src="/images/null.png" width="200 px" height="1 px"></td>  

<?
foreach($sheet_data['pass_by_page'][$page] AS $train) 
{ ?>
<th class="roadnumber"><?=$train['Train']['roadnumber']?></th>
<?}?>

</tr>
<tr><td><img src="/images/null.png" width="200 px" height="1 px"></td>  

<?
foreach($sheet_data['pass_by_page'][$page] AS $train) 
{ ?>
<th class="roadnumber"><? echo $train['Train']['text_day']?></th>
<?}?>

</tr>
<?php 

 ?>
<? foreach($stations AS $station_key => $station) 
{?>
    <tr>
    <td class="station_field" width="500 px"><?=$station['name'] ?></td>
    
    <? foreach($sheet_data['pass_by_page'][$page] AS $train_key => $train) 
    {

	?>
        <td><?php echo 
		substr(
		$sheet_data['pass_by_page'][$page][$train_key]['Pass'][$station['id']]['dep'],
		-8,
		5
		);//Поздравляю, ты нашел быдлокод, возьми с полки пирожок!
		?></td>

    <?}?>
        <td class="station_field" width="500 px"><?=$station['name'] ?>
		<img src="/images/null.png" width="200 px" height="1 px"></td>
    </tr>
<?}?>
</table>



<?}?>

<p>
	Станций: <?php echo $stations_count;?> <br>
	Поездов: <?php echo $trains_count;?> <br>
	
</p>