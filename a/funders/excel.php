<?php
include("../../include.php");
echo drawTop();
?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("Reports", 2)?>
	<tr>
		<th align="left" width="82%">Report Name</th>
		<th align="right" width="18%" align="right">Date Created</th>
	</tr>
	<tr height="40">
		<td><a href="excel_funders_prospects.php"><b>Funders & Prospects</b></a><br>
			Report requested by Ben 2008.
		</td>
		<td align="right"><nobr>Feb 04, 2008</nobr></td>
	</tr>
	<tr height="40">
		<td><a href="excel_all_awards.php"><b>All Awards</b></a><br>
			This report returns general information on all awards in the system.
		</td>
		<td align="right"><nobr>Dec 1, 2005</nobr></td>
	</tr>
	<tr height="40">
		<td><a href="excel_big_list.php"><b>Big List</b></a><br>
			This report shows all active awards, proposals, and strategies in development.  It is designed for
			use at resource development meetings.
		</td>
		<td align="right"><nobr>Nov 10, 2003</nobr></td>
	</tr>
	<tr height="40">
		<td><a href="excel_duedates.php"><b>Reports and Due Dates</b></a><br>
			This report returns all due dates for reports associated with active awards in Excel format.
		</td>
		<td align="right"><nobr>May 20, 2003</nobr></td>
	</tr>
</table>
<?=drawBottom();?>