<?php

/**
 * Based off ContentONE C1DateField
 */

class DateField {
	
	// Standard functionality properties
	protected $sName;
	protected $nValue;
	protected $sDateFormat;
	protected $sJSDateFormat;
	
	// Configuration variables
	protected $aEventHandlers;
	protected $aTypes;
	protected $aYearOptions;
	protected $bEditable;
	protected $bDisablePreviousDays;
	protected $bInline;
	protected $bSingleField;
	protected $sBaseClassName;
	protected $sCalendarIcon;
	protected $sCalendarName;
	protected $sStyleSheet;
	protected $sInputClassName;
	
	/**
	 * Creates a new DateField object.
	 * @param	string $sName The name of the field to create
	 * @param	mixed $mValue The value to display either as a string or a UNIX timestamp
	 * @param	boolean $bSingleField If true a single field will be shown; false will shown date, month and year as separate fields
	 * @param	boolean $bEditable If true the field is editable; false will only allow the field to be changed by the calendar
	 * @param	string $sDateFormat The date format to use (using date() PHP function format)
	 */
	function __construct( $sName, $mValue = null, $bSingleField = true, $bEditable = true, $sDateFormat = 'd-M-Y' ) {
		$this->sName = $sName;
		$this->setDateFormat( $sDateFormat );
		$this->bEditable = $bEditable;
		$this->bSingleField = $bSingleField;
		$this->aTypes = array();
		$this->bDisablePreviousDays = false;
		$this->nValue = ( is_null( $mValue ) || $mValue == "" ? null : ( is_numeric( $mValue ) ? intval( $mValue ) : strtotime( strval( $mValue ) ) ) );
		$this->sBaseClassName = 'DATE';
		$this->aYearOptions = null;
		$this->aEventHandlers = array();
		$this->sStyleSheet = '';
		$this->sCalendarName = 'oCal' . $sName;
		$this->sCalendarIcon = 'images/cal.gif';
		$this->setInline( false );
		$this->sInputClassName = 'smallblue';
	}
	
	/**
	 * Adds an event handler to the calendar control.
	 * @param	string $sName The name of the event
	 * @param	string $sJSFunctionName The name of the JavaScript function
	 */
	public function addEventHandler( $sName, $sJSFunctionName ) {
		$this->aEventHandlers[strtolower($sName)] = $sJSFunctionName;
	}
	
	/**
	 * Adds a new type to the date field.
	 *
	 * Each configuration is an array with the following:
	 * - disable:		true to disable any dates of this type; false otherwise
	 * - class_name:	the class_name to append to the base class name
	 * @param	string $sName The type to add
	 * @param	array $aConfig The configuration for the type.
	 */
	public function addType( $sName, $aConfig ) {
		$aConfig['name'] = $sName;
		if( !isset( $aConfig['disable'] ) ) { $aConfig['disable'] = false; }
		if( !isset( $aConfig['class_name'] ) ) { $aConfig['class_name'] = $sName; }
		if( !isset( $aConfig['dates'] ) ) { $aConfig['dates'] = array(); }
		$this->aTypes[$sName] = $aConfig;
	}
	
	/**
	 * Adds a date range to a specific date type.
	 * @param	string $sName The type to add the dates to
	 * @param	mixed $mStartDate The date to start from. Can be numeric or string. If null it means before end date.
	 * @param	mixed $mEndDate The date to end at. Can be numberic or string. If null it means after start date.
	 */
	public function addTypeDateRange( $sName, $mStartDate, $mEndDate ) {
		if( !isset( $this->aTypes[$sName] ) ) {
			$this->addType( $sName, array() );
		}
		if( !is_null( $mStartDate ) && !is_numeric( $mStartDate ) ) { $mStartDate = strtotime( $mStartDate ); }
		if( !is_null( $mEndDate ) && !is_numeric( $mEndDate ) ) { $mEndDate = strtotime( $mEndDate ); }
		array_push( $this->aTypes[$sName]["dates"], array(
			is_null( $mStartDate ) ? null : date( 'd-M-Y', $mStartDate ),
			is_null( $mEndDate ) ? null : date( 'd-M-Y', $mEndDate ) ) );
	}
	
	/**
	 * Adds a single date to a specific date type.
	 * @param	string $sName The type to add the date to
	 * @param	mixed $mDate The date to add. Can be numeric or string.
	 */
	public function addTypeDate( $sName, $mDate ) {
		$this->addTypeDateRange( $sName, $mDate, $mDate );
	}
	
	/**
	 * Adds more than one date to a specific date type.
	 * @param	string $sName The type to add the dates to
	 * @param	array $aDates An array containing the dates to add. Each value can be either numeric or string.
	 */
	public function addTypeDates( $sName, $aDates ) {
		for( $i = 0, $nLength = count( $aDates ); $i < $nLength; $i++ ) {
			$this->addTypeDateRange( $sName, $aDates[$i], $aDates[$i] );
		}
	}
	
	/**
	 * Returns the HTML to display the date field.
	 * @return	string The HTML to display the date field
	 */
	public function getHTML() {
		
		// Create the HTML
		$sHTML = '';
		
		// Create the HTML that should only be loaded once
		if( !isset( $GLOBALS['DATE_CALENDAR_INIT'] ) ) {
			$sHTML .=
				"<script type=\"text/javascript\" language=\"JavaScript\" src=\"includes/calendar.js\"></script>";
			$GLOBALS['DATE_CALENDAR_INIT'] = array();
		}
		
		// Add the custom style sheet
		if( $this->sStyleSheet != "" ) {
			$sHTML .=
				'<link rel="stylesheet" type="text/css" href="' . $this->sStyleSheet . '">';
		}
		
		// Create standard field properties
		$sLinkName = $this->sName . "_cal";
		$sContainerID = ( $this->sCalendarContainer == "" ? ( $this->sCalendarName . "_win" ) : $this->sCalendarContainer );
		$sOnChange =
			( isset( $this->aEventHandlers['onchange'] ) ?
				( ' onchange="return ' . $this->aEventHandlers['onchange'] . '.call(this)"' ) :
				( $this->bInline ?
					( " onchange=\"" . $this->sCalendarName . ".select('calField" . $this->sName . "','" . $sLinkName . "','" . $this->sJSDateFormat . "')\"" ) :
					( "" ) ) );
					
//		$sHTML .= '<table border="0" cellspacing="0" cellpadding="0"><tbody><tr><td>';

		// Create the HTML for the fields
		if( $this->bSingleField ) {
			$sHTML .=
				'<input type="text" ' .
				'id="calField' . $this->sName . '" ' .
				'name="' . $this->sName . '" ' .
				'value="' . ( $this->nValue === false || is_null( $this->nValue ) || $this->nValue == '' ? '' : date( $this->sDateFormat, $this->nValue ) ) . '" ' .
				'size="10"' .
				"onclick=\"" . $this->sCalendarName . ".select('calField" . $this->sName . "','" . $sLinkName . "','" . $this->sJSDateFormat . "')\" " .
				( $this->sInputClassName == "" ? "" : ( " class=\"" . $this->sInputClassName . "\"" ) ) . '>';

		// Create the HTML for multiple fields
		} else {
			
			// Get the default value
			$aDateValues = explode( "-", ( $this->nValue === false || is_null( $this->nValue ) || $this->nValue == '' ? '' : date( "j-M-Y", $this->nValue ) ) );
			if( count( $aDateValues ) != 3 ) { $aDateValues = array( "", "", "" ); }
			
			// Add the days
			$sHTML .=
				"<select name=\"" . $this->sName . "_Day\" id=\"calField" . $this->sName . "_Day\"" . $sOnChange . ">" .
					"<option value=\"\">\r\n";
			for( $i = 1; $i <= 31; $i++ ) {
				$sHTML .= "<option value=\"" . $i . "\"" . ( $i == $aDateValues[0] ? " selected" : "" ) . ">" . $i . "\r\n";
			}
			$sHTML .= "</select> ";
			
			// Add the months
			$sHTML .=
				"<select name=\"" . $this->sName . "_Month\" id=\"calField" . $this->sName . "_Month\"" . $sOnChange . ">" .
					"<option value=\"\">\r\n";
			for( $i = 1; $i <= 12; $i++ ) {
				$sMonth = date( "M", mktime( 0, 0, 0, $i, 1 ) );
				$sHTML .= "<option value=\"" . $sMonth . "\"" . ( $sMonth == $aDateValues[1] ? " selected" : "" ) . ">" . $sMonth . "\r\n";
			}
			$sHTML .= "</select> ";
			
			// Add the years
			if( is_null( $this->aYearOptions ) ) {
				$sHTML .= "<input type=\"text\" name=\"" . $this->sName . "_Year\" id=\"calField" . $this->sName . "_Year\" value=\"" . $aDateValues[2] . "\" size=\"4\"" . $sOnChange . ">";
			} else {
				$sHTML .=
					"<select name=\"" . $this->sName . "_Year\" id=\"calField" . $this->sName . "_Year\"" . $sOnChange . ">" .
						"<option value=\"\">\r\n";
				foreach( $this->aYearOptions as $sYear ) {
					$sHTML .= "<option value=\"" . $sYear . "\"" . ( $sYear == $aDateValues[2] ? " selected" : "" ) . ">" . $sYear . "\r\n";
				}
				$sHTML .= "</select>";
			}
			
		}
		
//		$sHTML .= '</td><td style="padding-left:3px">';
		
		// Add the calendar popup link
		if( !$this->bInline ) {
			$sHTML .=
				"<a href=\"#\" " .
				"onclick=\"" . $this->sCalendarName . ".select('calField" . $this->sName . "','" . $sLinkName . "','" . $this->sJSDateFormat . "');return false;\" " .
				"name=\"" . $sLinkName . "\" id=\"" . $sLinkName . "\" " .
				"class=\"calendarLink\" style=\"background-image:url('" . $this->sCalendarIcon . "');\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>";
		}
		
		// Create the call to add the calendar control
		if( !in_array( $this->sCalendarName, $GLOBALS['DATE_CALENDAR_INIT'] ) ) {
			$sHTML .=
				( $this->sCalendarContainer == "" ?
					( "<div id=\"" . $sContainerID . "\"" .
					  ( $this->bInline ? "" : ( " style=\"position: absolute; z-index: 1; visibility: hidden; background-color: white; layer-background-color: white;\"" ) ) .
				 	  "></div>\r\n" ) : "" ) .
				"<script type=\"text/javascript\" language=\"JavaScript\">\r\n" .
				"var " . $this->sCalendarName . " = new CalendarPopup(\"" . $sContainerID . "\");\r\n" .
				$this->sCalendarName . ".setCssPrefix(\"" . $this->sBaseClassName . "\");\r\n" .
				$this->getUpdateScript() .
				( $this->bInline ? ( $this->sCalendarName . ".setInline(true);\r\n" . $this->sCalendarName . ".select(\"calField" . $this->sName . "\",\"" . $sLinkName . "\",\"" . $this->sJSDateFormat . "\");\r\n" ) : "" ) .
				"</script>";
			array_push( $GLOBALS['DATE_CALENDAR_INIT'], $this->sCalendarName );
		}
		
//		$sHTML .= '</td></tr></tbody></table>';
		
		return $sHTML;
	}
	
	/**
	 * Returns javascript that will update an existing date field.
	 * @return	string The JavaScript to update an existing date field
	 */
	public function getUpdateScript() {
		
		// Display the configuration script
		$sScript = "";
		$aNames = array_keys( $this->aTypes );
		for( $i = count( $aNames ) - 1; $i >= 0; $i-- ) {
			
			// Get the configuration
			$sName = $aNames[$i];
			$aConfig = $this->aTypes[$sName];
			$sScript .=
				$this->sCalendarName . ".addType(\"" . $sName . "\",{";
			$bIsFirst = true;
			
			// Add the configuration properties
			foreach( $aConfig as $sKey => $mValue ) {
				switch( $sKey ) {
					case "name":
					case "dates":
						break;
					default:
						$sScript .=
							( $bIsFirst ? "" : "," ) .
							$sKey . ":" .
							( is_bool( $mValue ) ? ( $mValue == true ? "true" : "false" ) :
								( is_string( $mValue ) ? ( "\"" . str_replace( "\"", "\\\"", $mValue ) . "\"" ) :
									( $mValue ) ) );
				}
				$bIsFirst = false;
			}
			$sScript .=
				"});\r\n";
				
			// Add the dates
			if( isset( $aConfig["dates"] ) ) {
				foreach( $aConfig["dates"] as $aDates ) {
					$sScript .= $this->sCalendarName . ".addTypeDates(" .
						"\"" . $sName . "\"," .
						( is_null( $aDates[0] ) ? "null" : ( "\"" . $aDates[0] . "\"" ) ) .
						( $aDates[0] === $aDates[1] ? "" : ( is_null( $aDates[1] ) ? ",null" : ( ",\"" . $aDates[1] . "\"" ) ) ) . ");\r\n";
				}
			}
			
		}
		
		// Add the disabled date scripts
		if( $this->bDisablePreviousDays ) {
			$sScript .=
				$this->sCalendarName . ".addDisabledDates(null,\"" . date( "d-M-Y", strtotime( "yesterday" ) ) . "\");\r\n";
		}
		
		// Add events
		foreach( $this->aEventHandlers as $sEvent => $sJSFunctionName ) {
			switch( $sEvent ) {
				case "onchange":
					break;
				default:
					$sScript .= $this->sCalendarName . ".addEventHandler(\"" . $sEvent . "\"," . $sJSFunctionName . ");\r\n";
			}
		}
		
		return $sScript;
	}
	
	/**
	 * Returns HTML that will update an existing date field.
	 * @return	string The HTML to update an existing date field
	 */
	public function getUpdateHTML() {
		return
			"<script type=\"text/javascript\" language=\"JavaScript\">\r\n" .
			$this->getUpdateScript() .
			"</script>\r\n";
	}
	
	/**
	 * Returns the value from the request in the format specified.
	 * @param	string $sName The name of the field
	 * @param	string $sFormat The format of the field as per the date() function
	 * @return	string The formatted date; or FALSE if there was a problem
	 */
	public function getFieldValue( $sName, $sFormat = 'd/m/y' ) {

		// Check for single field
		$sDate = false;
		if( array_key_exists( $sName, $_REQUEST ) ) {
			$sDate = $_REQUEST[$sName];
			
		// Check for multiple fields
		} elseif( array_key_exists( $sName . '_Day', $_REQUEST ) ) {
			$sDate =
				$_REQUEST[$sName.'_Day'] . '-' .
				$_REQUEST[$sName.'_Month'] . '-' .
				$_REQUEST[$sName.'_Year'];
			
		}
		
		// If there is a date, confirm that it's valid
		if( !is_null( $sDate ) ) {
			$sDate = strtotime( $sDate );
			if( $sDate === false || $sDate < 0 ) {
				$sDate = null;
			} else {
				$sDate = date( $sFormat, $sDate );
			}
		}

		return $sDate;
	}
	
	/**
	 * Removes the specified date type.
	 * @param	string $sName The name of the date type
	 */
	public function removeType( $sName ) {
		if( isset( $this->aTypes[$sName] ) ) {
			unset( $this->aTypes[$sName] );
		}
	}
	
	//=======================================================================//
	//=======================================================================//

	/**
	 * If true, any dates before the current date will be automatically disabled.
	 * @param	boolean $bDisablePreviousDays True to disable all days before today; false otherwise
	 */
	public function setDisablePreviousDays( $bDisablePreviousDays ) { $this->bDisablePreviousDays = $bDisablePreviousDays; }
	
	/**
	 * Indicates if the date field should be separated into day, month and year components.
	 * @param	boolean $bMultipleFields True to separate date field into day, month and year components.
	 * @param	array $aYearOptions The options for the year; or null if any year is allowed
	 */
	public function setMultipleFields( $bMultipleFields, $aYearOptions = null ) {
		$this->bSingleField = !$bMultipleFields;
		$this->aYearOptions = $aYearOptions;
	}
	
	/**
	 * Sets the base class name used by the date control.
	 * @param	string $sBaseClassName The base class name
	 */
	public function setBaseClassName( $sBaseClassName ) { $this->sBaseClassName = $sBaseClassName; }
	
	/**
	 * Sets the URL of the custom style sheet that should be loaded with the fields.
	 * @param	string $sURL The URL of the custom style sheet.
	 */
	public function setStyleSheet( $sURL ) { $this->sStyleSheet = $sURL; }
	
	/**
	 * Sets the name of the calendar control.
	 * @param	string $sName The name of the calendar control.
	 */
	public function setCalendarName( $sName ) { $this->sCalendarName = $sName; }
	
	/**
	 * Updates the date format.
	 * @param	string $sDateFormat The date format to use (using PHP date() function)
	 */
	protected function setDateFormat( $sDateFormat ) {
		$this->sDateFormat = $sDateFormat;
		$this->sJSDateFormat =
			str_replace( array( 'd', 'M', 'm', 'F', 'y', 'Y' ), array( 'dd', 'NNN', 'MM', 'MMM', 'yy', 'yyyy' ), $this->sDateFormat );
	}
	
	/**
	 * Determines if the calendar control should be inline or a popup.
	 * @param	boolean $bInline True to have control inline; false to have as popup
	 * @param	string $sContainerID ID of the container to have the control; false to create one
	 */
	public function setInline( $bInline, $sContainerID = '' ) {
		$this->bInline = $bInline;
		$this->sCalendarContainer = $sContainerID;
	}
	
	public function setInputClassName( $sClassName ) {
		$this->sInputClassName = $sClassName;
	}
	
}

?>