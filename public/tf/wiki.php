<?php 
$handle = fopen("tags.txt", "r");
?>
<html>
<body>
<table>
<tbody>
<?
var $rowBr = false;
while (!feof($handle)) {
	if(!$rowBr){
		echo "<tr>"
	}
    $buffer = fgets($handle);
    echo "<td>".$buffer."</td>";
	if($rowBr){
		echo "</tr>"
	}
	$rowBr = !$rowBr;
}
fclose($handle);
?>
</tbody>
</table>
</body>
</html>