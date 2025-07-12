<script type="text/javascript" language="JavaScript">

function e_onPostalCopyChange( oField ) {
	var oForm = oField.form;
	if( oField.checked ) {
		var aMap = ["address_1","address_2","suburb","post_code","state"];
		for( var i = aMap.length - 1; i >= 0; i-- ) {
			var oField = eval( "oForm." + aMap[i] );
			if( oField != null ) {
				var oPostalField = eval( "oForm.postal_" + oField.name );
				if( oField.selectedIndex != null ) {
					oPostalField.selectedIndex = oField.selectedIndex;
				} else {
					oPostalField.value = oField.value;
				}
			}
		}
	}
}
function e_onAddressChange( oField ) {
	e_onPostalCopyChange( oField.form.postal_copy );
}

</script>