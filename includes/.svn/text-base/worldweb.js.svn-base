<script language="JavaScript">

//pop up window function with scroll bars-----------------------------------------------------------------------------
function NewWindow(url, name, width, height) {
var Win = window.open(url,"" + name + "",'width=0' + width + ',height=0' + height + ',resizable=yes,scrollbars=yes,menubar=no,status=yes' );
Win.focus();  
}

//pop up window function without scroll bars--------------------------------------------------------------------------
function NewWindow1(url, name, width, height) {
var Win = window.open(url,"" + name + "",'width=0' + width + ',height=0' + height + ',resizable=yes,scrollbars=no,menubar=no,status=no' );
Win.focus();  
}

//closeAll function to close all open windows-------------------------------------------------------------------------
function closeAll() {
  if (Win && Win.open && !Win.closed) Win.close();
}

//suppress errors function--------------------------------------------------------------------------------------------
function handleError() {
	return true;
}

window.onerror = handleError;

//functions to submit page
function Onsubmit_go()
{
	document.searchme.search_type.value = "company";
	document.searchme.submit();			// Submit the page
}

function Onsubmit_ref()
{
	document.searchme.search_type.value = "reference";
	document.searchme.submit();			// Submit the page
}

function Onsubmit_domain()
{
	document.searchme.search_type.value = "domain";
	document.searchme.submit();			// Submit the page
}

function Onsubmit_con()
{
	document.searchme.search_type.value = "contact";
	document.searchme.submit();			// Submit the page
}

function Onsubmit_updatelog()
{
	document.mylog.action_type.value = "rewrite_log";
	document.mylog.action = "index1.php";
	document.mylog.submit();			// Submit the page
}

function Onsubmit_addtolog()
{
	document.mylog.action_type.value = "add_to_log";
	document.mylog.action = "index1.php";
	document.mylog.submit();			// Submit the page
}

function Onsubmit_add_new_job()
{
	document.location.replace('job_control.php?client_id=<?php echo $client_id; ?>');			// Submit the page
}

//function launch_summary() launches the contact_summary.php file
function launch_summary(mylaunch)
{
	if (mylaunch == "contact")
	{
		NewWindow('contact_summary.php','contacts','480','400');
	} 
	else if (mylaunch == "client")
	{
		window.location.replace('add_client.php');
	}
	else if (mylaunch == "reports")
	{
		window.location.replace('reports.php');
	}
//end function
}

//function scrollToBottom will scrol to bottom of textarea
function scrollToBottom (element) {
  	if (document.all) {
    	element.scrollTop = element.scrollHeight;
	}
	else
	{
		var obj=element;
		obj.scrollTop=obj.scrollHeight-obj.offsetHeight;
	}
}

//function auto-tab will tab to next text box once reach maxsize
var isNN = (navigator.appName.indexOf("Netscape")!=-1);
function autoTab(input,len, e) {
var keyCode = (isNN) ? e.which : e.keyCode; 
var filter = (isNN) ? [0,8,9] : [0,8,9,16,17,18,37,38,39,40,46];
if(input.value.length >= len && !containsElement(filter,keyCode)) {
input.value = input.value.slice(0, len);
input.form[(getIndex(input)+1) % input.form.length].focus();
input.form[(getIndex(input)+1) % input.form.length].select();
}
function containsElement(arr, ele) {
var found = false, index = 0;
while(!found && index < arr.length)
if(arr[index] == ele)
found = true;
else
index++;
return found;
}
function getIndex(input) {
var index = -1, i = 0, found = false;
while (i < input.form.length && index == -1)
if (input.form[i] == input)index = i;
else i++;
return index;
}
return true;
}

//function add_attachments will launch the upload.php file
function add_attachments(myid)
{
NewWindow3('attach.php?id='+myid,'attach','400','200')
}

//will return a confirm box when user deletes the attachment
function confirmDelete()
{
var agree=confirm("Are you sure you wish to delete this attachment?\n If yes click OK else click Canel");
if (agree)
	return true ;
else
	return false ;
}

//function to format strings to title case---------------------------------------------------------------
function titleCase(sValue) 
{
  sValue = sValue.toLowerCase(); 
  var sReturn = '';
  var sWord = '';
  var sParts = sValue.split(" ");

  for (i = 0; i < sParts.length; i++){
     if (i > 0) {
        sReturn += ' ';
     }
     if (sParts[i].length > 0) {
        sWord = sParts[i].substr(0);
        sReturn += sParts[i].charAt(0).toUpperCase() + sParts[i].substr(1);
     }
  }
  return sReturn; 
}

function toggleExpand( sName, bForce ) {
	if( bForce == null ) { bForce = false; }
	var oElement = document.getElementById( sName );
	if( oElement != null ) {
		var nRows = oElement.getAttribute( "rows" );
		var nCollapseRows = oElement.getAttribute( "collapserows" );
		var nExpandRows = oElement.getAttribute( "expandrows" );
		if( nExpandRows != null ) {
			var bExpanded = false;
			if( !bForce && nRows == nExpandRows ) {
				oElement.setAttribute( "rows", nCollapseRows );
			} else {
				if( nRows != nExpandRows ) {
					oElement.setAttribute( "collapserows", nRows );
				}
				oElement.setAttribute( "rows", nExpandRows );
				bExpanded = true;
			}
			document.getElementById( sName + "_expand" ).innerHTML = ( bExpanded ? "collapse" : "expand" );
		}
	}
	return false;
}
</script>
