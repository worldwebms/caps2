<?php

//establish a persistent connection to mysql
$db = mysql_pconnect ($hostName, $userName, $password);
mysql_select_db($database);

//define date
$today = date("d/m/Y" ,time());

// Get representatives
$reps = array();
$result = mysql_query('SELECT p_id, first_name, last_name FROM ps ORDER BY first_name, last_name');
while ($row = mysql_fetch_object($result))
	$reps[intval($row->p_id)] = $row->first_name . ' ' . $row->last_name;

$where_sql = array();

//add by day clause
$order_by = "ASC";
$target = "opener";
if( !isset( $group_by_day ) ) { $group_by_day = false; }
if( $group_by_day == "true" ) { $group_by_day = true; }
if( isset( $day_start ) && isset( $day_end ) ) {
	$where_sql[] = "next_contact>='" . $day_start . "' AND next_contact <='" . $day_end . "'";
	$order_by = "ASC";
	$group_by_day = true;
	$target = "";
}

// generate criteria
$selected_status = array_safe($_GET, 'status', array());
if (count($selected_status) > 0) {
	$sql = '';
	foreach ($selected_status as $v)
		$sql .= ($sql == '' ? '' : ' OR ') . "status='" . $v . "'";
	$where_sql[] .= "( " . $sql . " )";
} else {
	$where_sql[] .= "( status != 'd' && status != 'o' )";
}

$query =
	"SELECT client_id, client_name, trading_name, agreement_number, next_contact, status, rep_id " .
	"FROM clients " .
	($where_sql > 0 ? ('WHERE ' . implode(' AND ', $where_sql)) : '') .
	" ORDER BY next_contact " . $order_by;
$result = mysql_query($query, $db);

?>

<script type="text/javascript">
var Contacts = {
	'open': function( el, client_id ) {
		el.parentNode.parentNode.className += ' completed';
		window.opener.location = 'index1.php?client_id=' + client_id;
		return false;
	}
};
</script>

      <table border="0" cellspacing="1" cellpadding="1" class="admin_list">
<?php

	if( !$group_by_day ) {

?>
        <tr class="text">
          <td width="50" class="text">Ref:</td>
          <td width="70" class="text" style="text-align:center">Contact</td>
          <td width="50" class="text">Status</td>
          <td class="text">Client</td>
          <td width="90" class="text">Manager</td>
        </tr>
<?php

	}

?>
        <?php
        $bOdd = true;
        $prev_contact = "";
		while($row = mysql_fetch_object($result)) {
			$client_id = $row->client_id;
			$client_name = $row->client_name;
			$trading_name = $row->trading_name;
			if (empty($trading_name)) {
				$mytrading = "";
			} else {$mytrading = "trading as $trading_name";}
			$ref = $row->agreement_number;
			$next_contact = $row->next_contact;
			$status = $row->status;
			//format next contact
			$next_contact = ToNextContact($next_contact);
			
			if( $group_by_day && $next_contact != $prev_contact ) {
				echo "<tr><td colspan='3' class='text' style='font-weight:bold'><br>" . date( "l, F j, Y", strtotime( $next_contact ) ) . "</td></tr>";
				echo "<tr class=\"text\"><td width=\"50\" class=\"text\">Ref:</td><td width=\"50\" class=\"text\">Status</td><td class=\"text\">Client</td><td width=\"90\" class=\"text\">Manager</td></tr>";
				$bOdd = true;
			}
			
			echo "<tr class='" . ( $bOdd ? "odd" : "even" ) . "'><td class='text'><font color='#B9E9FF'>$ref</font></td>";
			if( !$group_by_day ) {
				echo "<td class='text' style='text-align:center'>$next_contact</td>";
			}
			echo "<td class='text'>" . ( array_key_exists( $status, $status_options ) ? $status_options[$status] : $status ) . "</td>";
			echo "<td class='text'><a " . ( $target == "" ? "href=\"index1.php?client_id=$client_id\"" : "href=\"#\" onClick=\"javascript:Contacts.open(this,$client_id); return false;\"" ) . "><font color='#B9E9FF'>$client_name $mytrading</font></a></td>";
			echo "<td class='text'>" . array_safe($reps, intval($row->rep_id), '') . "</td>";
			echo "</tr>";
			$bOdd = !$bOdd;
			$prev_contact = $next_contact;
		}
		?>
      </table>
