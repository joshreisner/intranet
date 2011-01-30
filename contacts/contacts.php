<?php
include('../include.php');

if (!isset($_GET['id'])) $_GET['id'] = "a";
	
echo drawTop();

if (!isset($_GET["print"])) {?>
<table class="navigation contacts" cellspacing="1">
	<tr class="contacts-hilite">
		<td width="3.846%"<?if ($_GET['id'] != "a") {?>><a href="contacts.php?id=a"><?}else{?> class="selected"><b><?}?>A</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "b") {?>><a href="contacts.php?id=b"><?}else{?> class="selected"><b><?}?>B</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "c") {?>><a href="contacts.php?id=c"><?}else{?> class="selected"><b><?}?>C</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "d") {?>><a href="contacts.php?id=d"><?}else{?> class="selected"><b><?}?>D</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "e") {?>><a href="contacts.php?id=e"><?}else{?> class="selected"><b><?}?>E</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "f") {?>><a href="contacts.php?id=f"><?}else{?> class="selected"><b><?}?>F</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "g") {?>><a href="contacts.php?id=g"><?}else{?> class="selected"><b><?}?>G</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "h") {?>><a href="contacts.php?id=h"><?}else{?> class="selected"><b><?}?>H</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "i") {?>><a href="contacts.php?id=i"><?}else{?> class="selected"><b><?}?>I</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "j") {?>><a href="contacts.php?id=j"><?}else{?> class="selected"><b><?}?>J</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "k") {?>><a href="contacts.php?id=k"><?}else{?> class="selected"><b><?}?>K</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "l") {?>><a href="contacts.php?id=l"><?}else{?> class="selected"><b><?}?>L</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "m") {?>><a href="contacts.php?id=m"><?}else{?> class="selected"><b><?}?>M</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "n") {?>><a href="contacts.php?id=n"><?}else{?> class="selected"><b><?}?>N</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "o") {?>><a href="contacts.php?id=o"><?}else{?> class="selected"><b><?}?>O</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "p") {?>><a href="contacts.php?id=p"><?}else{?> class="selected"><b><?}?>P</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "q") {?>><a href="contacts.php?id=q"><?}else{?> class="selected"><b><?}?>Q</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "r") {?>><a href="contacts.php?id=r"><?}else{?> class="selected"><b><?}?>R</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "s") {?>><a href="contacts.php?id=s"><?}else{?> class="selected"><b><?}?>S</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "t") {?>><a href="contacts.php?id=t"><?}else{?> class="selected"><b><?}?>T</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "u") {?>><a href="contacts.php?id=u"><?}else{?> class="selected"><b><?}?>U</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "v") {?>><a href="contacts.php?id=v"><?}else{?> class="selected"><b><?}?>V</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "w") {?>><a href="contacts.php?id=w"><?}else{?> class="selected"><b><?}?>W</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "x") {?>><a href="contacts.php?id=x"><?}else{?> class="selected"><b><?}?>X</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "y") {?>><a href="contacts.php?id=y"><?}else{?> class="selected"><b><?}?>Y</a></td>
		<td width="3.846%"<?if ($_GET['id'] != "z") {?>><a href="contacts.php?id=z"><?}else{?> class="selected"><b><?}?>Z</a></td>
	</tr>
</table>
<? }

?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow(strToUpper($_GET['id']), 4)?>
	<tr>
		<th width="" align="left">Name</th>
		<th width="" align="left">Company</th>
		<th width="" align="left">Phone</th>
	</tr>
	<?
	$contacts = db_query("SELECT
						o.id,
						o.is_active,
						i.varchar_01 as firstname,
						i.varchar_02 as lastname,
						i.varchar_04 as organization,
						i.varchar_08 as phone,
						i.varchar_11 as email
					FROM contacts o
					INNER JOIN contacts_instances i ON o.instanceCurrentID = i.id
					WHERE o.is_active = 1 AND i.varchar_02 LIKE '" . $_GET['id'] . "%'
					ORDER BY i.varchar_02, i.varchar_01");
	while ($c = db_fetch($contacts)) {
		if (strlen($c["organization"]) > 40) $c["organization"] = substr($c["organization"], 0, 39) . "...";
		?>
	<tr>
		<td><a href="contact.php?id=<?=$c["id"]?>"><?=$c["lastname"]?>, <?=$c["firstname"]?></a></td>
		<td><?=$c["organization"]?></td>
		<td><?=$c["phone"]?><!--<br><?=$c["email"]?>--></td>
	</tr>
	<? }?>
</table>
<?=drawBottom();?>