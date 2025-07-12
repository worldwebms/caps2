<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* technical template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 12/5/2003
* Client: WorldWeb, Domain: database intranet
* This template consists of 3 sections: Navigation, SQL Queries & formatting, HTML output
* Important files included in this template are: header.inc (head section and javascript library),
* globals.php (connection string),
* Last Modified: 16/1/2004 By Aviv Efrat
* Please remember to backup template before modifying
*
*********************************************************************************************************
*/
//begin session
session_start();
//include a globals file for db connection
include_once("includes/globals.php");
//define date
$today = date("d/m/Y" ,time());

//establish a persistent connection and get all required data.
// select valuations for  valuer
$db = mysql_pconnect ($hostName, $userName, $password);
mysql_select_db($database);

//event->delete_domain delete from domains table
if ($event == "delete_domain") {
	$query = "DELETE FROM domains WHERE domain_id='$domain_id' AND client_id='$client_id'";
	$result = mysql_query($query, $db);
}

//event->update_domains update domains table
if (isset($update_domains)) {
	//loop from 1 to i-1 and update
	for ($i=1;$i<$domain_counter;$i++) {
		$str = "domain_name$i";
		$str1 = "domain_key$i";
		$str4 = "domain_pwd$i";
		$str2 = "registrar$i";
		$str3 = "domain_id$i";
		$str10 = $$str;
		$str11 = $$str1;
		$str14 = $$str4;
		$str12 = $$str2;
		$str13 = $$str3;
		$query = "UPDATE domains SET domain_name='$str10', domain_key='$str11', domain_pwd='$str14', registrar='$str12' WHERE domain_id='$str13' AND client_id='$client_id'";
		$result = mysql_query($query, $db);
	}
//end event
}

//event->add_domain add record to domains table
if (isset($add_domain)) {
	$query = "INSERT INTO domains VALUES ('$domain_id', '$client_id', '$domain_name', '$domain_key', '$domain_pwd', '$registrar')";
	$result = mysql_query($query, $db);
}

//event->update_tec when user updates technical section with all hosting options
if (isset($update_tec)) {
	//format the services
	if ($wwedit == "on") {$wwedit = "yes";} else {$wwedit = "no";}
	if ($avatar == "on") {$avatar = "yes";} else {$avatar = "no";}
	if ($php == "on") {$php = "yes";} else {$php = "no";}
	if ($mysql == "on") {$mysql = "yes";} else {$mysql = "no";}
	if ($psql == "on") {$psql = "yes";} else {$psql = "no";}
	if ($webreport == "on") {$webreport = "yes";} else {$webreport = "no";}
	if ($coldfusion == "on") {$coldfusion = "yes";} else {$coldfusion = "no";}
	if ($cronjob == "on") {$cronjob = "yes";} else {$cronjob = "no";}
	//check if record exists and if not insert one.
	//if it does exist update it
	$query = "SELECT tec_id FROM technical WHERE client_id = $client_id";
	@$result = mysql_query($query, $db);
	$num_rows = mysql_num_rows($result);
	if ($num_rows == 1) {
		//update the record
		$query1 = "UPDATE technical SET ftp_host='$ftp_host', ftp_user='$ftp_user', ftp_pwd='$ftp_pwd', wwedit='$wwedit', avatar='$avatar', php='$php', mysql='$mysql', psql='$psql', webreport='$webreport', coldfusion='$coldfusion', cronjob='$cronjob' WHERE tec_id='$tec_id' AND client_id='$client_id'";
		$result1 = mysql_query($query1, $db);
	} else {
		//insert a record
		$tec_id = "";
		$query1 = "INSERT INTO technical VALUES ('$tec_id', '$client_id', '$ftp_host', '$ftp_user', '$ftp_pwd', '$wwedit', '$avatar', '$php', '$mysql', '$psql', '$webreport', '$coldfusion', '$cronjob')";
		$result1 = mysql_query($query1, $db);
		$err = mysql_error();
		if ($err) {$message .= "Could not insert new technical record $err";}
	}
}

//select all the details again
$query3 = "SELECT * FROM technical WHERE client_id = '$client_id'";
$result3 = mysql_query($query3, $db);
$row3 = mysql_fetch_object($result3);
$tec_id = $row3->tec_id;
$ftp_host = $row3->ftp_host;
$ftp_user = $row3->ftp_user;
$ftp_pwd = $row3->ftp_pwd;
$wwedit = $row3->wwedit;
$avatar = $row3->avatar;
$php = $row3->php;
$mysql = $row3->mysql;
$psql = $row3->psql;
$webreport = $row3->webreport;
$coldfusion = $row3->coldfusion;
$cronjob = $row3->cronjob;

//format checkboxes for services
if ($wwedit == "yes") {$is_wwedit = "checked";}
if ($avatar == "yes") {$is_avatar = "checked";}
if ($php == "yes") {$is_php = "checked";}
if ($mysql == "yes") {$is_mysql = "checked";}
if ($psql == "yes") {$is_psql = "checked";}
if ($webreport == "yes") {$is_webreport = "checked";}
if ($coldfusion == "yes") {$is_coldfusion = "checked";}
if ($cronjob == "yes") {$is_cronjob = "checked";}


//begin general display of information
$query4 = "SELECT client_name, trading_name, agreement_number, website_url, status FROM clients WHERE client_id = '$client_id'";
$result4 = mysql_query($query4, $db);
$row4 = mysql_fetch_object($result4);
$client_name = $row4->client_name;
$trading_name = $row4->trading_name;
$agreement_number = $row4->agreement_number;
$website_url = $row4->website_url;
$status = $row4->status;

//select all the details again
$query6 = "SELECT * FROM domains WHERE client_id = '$client_id'";
$result6 = mysql_query($query6, $db);

$mydomains = "";
$query7 = "SELECT domain_name FROM domains WHERE client_id = '$client_id'";
$result7 = mysql_query($query7, $db);
while ($row7=mysql_fetch_object($result7)) {
	$mydomains .= "<option>$row7->domain_name</option>";
}

//close connection
mysql_close($db);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>CAPS | WorldWeb Management Services</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="includes/caps_styles.css" rel="stylesheet" type="text/css">
<?php
//include javascript library
include_once("includes/worldweb.js");
?>
</head>

<body bgcolor="#006699" leftmargin="0" topmargin="0">
<?php 
include_once("includes/top.php"); 
include_once("includes/client_top.php");
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <?php include_once("includes/admin_links.php"); ?>
  <tr> 
    <td>&nbsp;</td>
    <td colspan="2" valign="top" class="text"><u>Domain Settings</u><br>
      <p class="subheading">Domain Names</p>
      <table width="500" border="0" cellspacing="0" cellpadding="0">
        <tr class="text"> 
          <td width="100" class="text">Domain Name</td>
          <td width="100" class="text">Registry Key</td>
		  <td width="100" class="text">Registry PWD</td>
          <td width="100" class="text">Registrar</td>
          <td width="10" class="text">&nbsp;</td>
          <td width="90" class="text">&nbsp;</td>
        </tr>
        <form action="technical.php" method="post">
          <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
          <?php
		$y=1;
		while ($row6 = mysql_fetch_object($result6)) {
			$domain_id = $row6->domain_id;
			echo "<tr><td><input name='domain_name$y' type='text' class='black' value='$row6->domain_name' size='30'></td>";
			echo "<td><input name='domain_key$y' type='text' class='black' value='$row6->domain_key' size='15'></td>";
			echo "<td><input name='domain_pwd$y' type='text' class='black' size='15' value='$row6->domain_pwd'></td>";
			echo "<td><select name='registrar$y' class='black'>
<option selected>$row6->registrar</option>
<option>WorldWeb MS</option>
<option>Melbourne IT</option>
<option>Connect</option>
<option>Network Solutions</option>
<option>Netregistry</option>
<option>jp-domains.com</option>
<option>register.australianwebsites.com</option>
</select></td>";
			echo "<td>&nbsp;</td>";
			echo "<td class='smalltext'><span class='smallwhite'><a href='technical.php?event=delete_domain&domain_id=$domain_id&client_id=$client_id'><font color='#CCFFFF'>&nbsp;delete</font></a></span></td></tr>";
			echo "<input name=\"domain_id$y\" type=\"hidden\" value=\"$row6->domain_id\">";
			$y++;
		}
		echo "<input name=\"domain_counter\" type=\"hidden\" value=\"$y\">";
		?>
          <tr valign="middle"> 
            <td colspan="4" align="right" height="22"> <input name="update_domains" type="submit" class="smallbluebutton" value="Update Domains"></td>
            <td></td>
            <td></td>
          </tr>
        </form>
        <tr> 
          <td class="text" colspan="6">Add new domain name </td>
        </tr>
        <form action="technical.php" method="post">
          <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
          <tr> 
            <td><input name="domain_name" type="text" class="black" size="30"></td>
            <td><input name="domain_key" type="text" class="black" size="15"></td>
            <td><input name="domain_pwd" type="text" class="black" size="15"></td>
            <td><select name="registrar" class="black">
                <option>WorldWeb MS</option>
                <option>Melbourne IT</option>
                <option>Connect</option>
                <option>Network Solutions</option>
		<option>Netregistry</option>
<option>jp-domains.com</option>
<option>register.australianwebsites.com</option>
              </select> </td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr> 
            <td height="20" colspan="4" align="right"> <input type="submit" name="add_domain" value="Add Domain" class="smallbluebutton"></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
        </form>
      </table>
      <p class="subheading">Hosting / FTP Details</p>
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td width="70%" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
              <form action="technical.php" method="post">
                <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
                <input type="hidden" name="tec_id" value="<?php echo $tec_id; ?>">
                <tr> 
                  <td width="9%" class="text">Host</td>
                  <td width="90%" colspan="3"><input name="ftp_host" type="text" class="black" value="<?php echo $ftp_host; ?>" size="30"></td>
                </tr>
                <tr> 
                  <td class="text">Username</td>
                  <td colspan="3"><input name="ftp_user" type="text" class="black" value="<?php echo $ftp_user; ?>" size="30"></td>
                </tr>
                <tr> 
                  <td class="text">Password</td>
                  <td colspan="3"><input name="ftp_pwd" type="text" class="black" value="<?php echo $ftp_pwd; ?>" size="30"></td>
                </tr>
                <tr> 
                  <td height="30" colspan="4" class="text">Additional Services</td>
                </tr>
                <tr> 
                  <td class="text"><input type="checkbox" name="wwedit" <?php echo $is_wwedit; ?>>
                    WWedit</td>
                  <td class="text" colspan="3"><input type="checkbox" name="avatar" <?php echo $is_avatar; ?>>
                    Avatar CMS</td>
                </tr>
                <tr>
                  <td class="text"><input type="checkbox" name="php" <?php echo $is_php; ?>>
                    PHP</td>
                  <td class="text" colspan="3"><input type="checkbox" name="coldfusion" <?php echo $is_coldfusion; ?>>
                    ColdFusion</td>
                </tr>
                <tr>
                  <td class="text"><input type="checkbox" name="mysql" <?php echo $is_mysql; ?>>
                    Mysql</td>
                  <td class="text" colspan="3"><input type="checkbox" name="psql" <?php echo $is_psql; ?>>
                    Postgres SQL</td>
                </tr>
                <tr>
                  <td class="text"><input type="checkbox" name="webreport" <?php echo $is_webreport; ?>>
                    Webreport</td>
                  <td class="text" colspan="3"><input type="checkbox" name="cronjob" <?php echo $is_cronjob; ?>>
                    Cron jobs</td>
                </tr>
                <tr> 
                  <td class="text">&nbsp;</td>
                  <td colspan="3"><img src="images/spacer.gif" width="10" height="5"><br> 
                    <input type="submit" name="update_tec" value="Update Host / FTP" class="smallbluebutton"></td>
                </tr>
              </form>
            </table></td>
        </tr>
        <tr> 
          <td valign="top">&nbsp;</td>
        </tr>
      </table>
     <img src="images/spacer.gif" width="39" height="10">
	 </td>
  </tr>
  <tr> 
    <td>&nbsp;</td>
    <td valign="top" class="text"> 
      <p><img src="images/spacer.gif" width="233" height="14"></p>
      </td>
    <td width="76%" valign="top" class="text"></td>
  </tr>
</table>
</body>
</html>
