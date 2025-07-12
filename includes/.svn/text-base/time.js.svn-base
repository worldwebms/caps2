function e_onTimeControlClick() {
	this.oContainer.oField.value = this.sTimeValue;
	this.oContainer.style.visibility = "hidden";
}
function closeTimeControl() {
	var oContainer = document.getElementById( "time_control" );
	if( oContainer != null ) {
		oContainer.style.visibility = "hidden";
	}
	if( document.oTimeControlMouseUp != null ) {
		document.oTimeControlMouseUp();
	}
}
function e_onTimeControlUp( event ) {
	if( event == null ) { event = window.event; }
	event.cancelBubble = true;
}
function openTimeControl( oField, nStartTime, nEndTime, nIncrement ) {
	if( nStartTime == null ) { nStartTime = 700; }
	if( nEndTime == null ) { nEndTime = 2345; }
	if( nIncrement == null ) { nIncrement = 15; }
	
	// Get the field value
	if( oField.tagName == null ) { oField = document.getElementById( oField ); }
	var aValues = oField.value.split( ":" );
	if( aValues.length >= 2 ) {
		sValue = aValues[0] + "" + aValues[1];
	} else {
		sValue = parseInt( aValues[0] );
		if( isNaN( sValue ) || sValue == 0 ) {
			var oDate = new Date();
			if( oField.name.indexOf( 'end' ) >= 0 && ( oDate.getMinutes() % nIncrement ) > 0 )
				oDate.setTime( oDate.getTime() + 1000 * 60 * nIncrement );
			var sMinutes = new String( "00" + oDate.getMinutes() );
			sValue = oDate.getHours() + "" + sMinutes.substr( sMinutes.length - 2 );
		}
	}
	
	// Get an existing control
	var oExistingControl = document.getElementById( "time_control" );

	// Create the time control
	var oTimeControl = document.createElement( "DIV" );
	oTimeControl.id = "time_control";
	oTimeControl.oField = oField;
	
	// While we haven't hit the end time
	var oSelect = null;
	while( nStartTime <= nEndTime ) {
	
		// Get the time information
		var nHour = parseInt( nStartTime / 100 );
		var nMinute = ( nStartTime % 100 );
		
		// Create the row
		var oRow = document.createElement( "DIV" );
		var sHour = new String( "00" + nHour );
		var sMinute = new String( "00" + nMinute );
		oRow.sTimeValue = sHour.substr( sHour.length - 2 ) + ":" + sMinute.substr( sMinute.length - 2 );
		oRow.title = oRow.sTimeValue;
		oRow.onclick = e_onTimeControlClick;
		oRow.oContainer = oTimeControl;
		if( nMinute == 0 ) {
			oRow.className = "hour";
			oRow.appendChild( document.createTextNode( ( nHour == 0 ? 12 : ( nHour > 12 ? ( nHour - 12 ) : nHour ) ) + " " + ( nHour >= 12 ? "PM" : "AM" ) ) );
		} else {
			oRow.className = "minute";
			oRow.appendChild( document.createTextNode( oRow.sTimeValue ) );
		}
		oTimeControl.appendChild( oRow );
		
		// If the current time is before the value, set the selected one
		// so that if the current time is after the value, this one will be selected
		if( nStartTime <= sValue ) {
			oSelect = oRow;
		}
	
		// Get the next time
		nStartTime += nIncrement;
		if( nStartTime % 100 >= 60 ) {
			nStartTime -= 60;
			nStartTime = ( parseInt( nStartTime / 100 ) * 100 ) + 100 + ( nStartTime % 100 );
		}
	
	}
	
	// Add the existing control
	if( oExistingControl == null ) {
		document.body.appendChild( oTimeControl );
	} else {
		document.body.replaceChild( oTimeControl, oExistingControl);
	}
	
	// Position and display the control
	var oPosition = getAnchorPosition( oField.id );
	oTimeControl.style.left = oPosition.x;
	oTimeControl.style.top = oPosition.y + oField.offsetHeight;
	oTimeControl.style.visibility = "visible";
	
	// Stop bubbling of mouse up event
	oTimeControl.onmouseup = e_onTimeControlUp;
	
	// Scroll the selected time into view
	if( oSelect != null ) {
		var nPosition = oSelect.offsetTop - ( oTimeControl.offsetHeight / 2 ) + oSelect.offsetHeight;
		oTimeControl.scrollTop = ( nPosition < 0 ? 0 : nPosition );
		oSelect.className = "selected";
	} else {
		oTimeControl.scrollTop = 0;
	}
	
	// Close the control on mouse up
	if( document.onmouseup != null && document.onmouseup != closeTimeControl ) {
		document.oTimeControlMouseUp = document.onmouseup;
	}
	document.onmouseup = closeTimeControl;
	
}
