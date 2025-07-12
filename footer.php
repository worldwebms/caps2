<script type="text/javascript">
function keepSessionAlive() {
	try {
		request = new XMLHttpRequest();
	} catch(trymicrosoft) {
		try {
			request = new ActiveXObject('Msxml2.XMLHTTP');
		} catch (failed) {
			return false;
		}
	}
	request.open('GET', 'welcome.php', true);
	request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	request.send('');
}
setInterval(keepSessionAlive, 5 * 60 * 1000);
</script>
