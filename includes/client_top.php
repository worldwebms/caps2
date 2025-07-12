<?php
	$client_top_db = mysql_connect ($hostName, $userName, $password);
	mysql_select_db($database, $client_top_db);
	$client_top_result = mysql_query("SELECT clients.*, ps.first_name, ps.last_name FROM clients LEFT JOIN ps ON ps.p_id=clients.rep_id WHERE client_id = '$client_id' LIMIT 1", $client_top_db);
	if ($client_top_result == false)
		echo mysql_error($client_top_db);
	$client_top_detail = mysql_fetch_object($client_top_result);
	mysql_close($client_top_db);
?>
<table width="100%" height="27" border="0" cellspacing="0" cellpadding="0">
	<tr> 
		<td width="1%"><img src="images/spacer.gif" width="8" height="27"></td>
		<td colspan="2" class="clienttitle">
			<?= $client_top_detail->client_name ?><span class="text"><img src="images/spacer.gif" width="12" height="10"></span>
			<span class="text"><?php if ($client_top_detail->trading_name) {echo "Trading as: " . $client_top_detail->trading_name;} ?><img src="images/spacer.gif" width="20" height="10">
			<?php if ($client_top_detail->agreement_number) {echo "Ref: " . $client_top_detail->agreement_number;} else {echo "Ref: N/A";} ?><img src="images/spacer.gif" width="20" height="10">Status: 
			<?= array_safe( $status_options, $client_top_detail->status, "(unknown)" ) ?>
			<img src="images/spacer.gif" width="20" height="10"><a href="<?= $client_top_detail->website_url ?>" target="_blank"><font color="#FFFFFF"><?= $client_top_detail->website_url ?></font></a></span>
			<img src="images/spacer.gif" width="20" height="10"><span class="text">Account Manager: <?= $client_top_detail->first_name ? ($client_top_detail->first_name . ' ' . $client_top_detail->last_name) : '(unknown)' ?></span>
		</td>
	</tr>
</table>
