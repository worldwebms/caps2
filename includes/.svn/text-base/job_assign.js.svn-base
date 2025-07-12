<script type="text/javascript" language="JavaScript">
function rebuildJobAssign() {
	var oTable = document.getElementById( "job_assign" );
	var aCells = [];
	for( var i = oTable.rows.length - 1; i >= 0; i-- ) {
		var oRow = oTable.rows[i];
		var bRowHighlight = false;
		for( var j = oRow.cells.length - 1; j >= 0; j-- ) {
			var oCell = oRow.cells[j];
			var oInput = oCell.childNodes[0];
			bRowHighlight = ( bRowHighlight || ( oInput != null && oInput.checked == true ) );
			oCell.className = ( aCells[j] == true || bRowHighlight ? "highlight" : "" );
			if( oInput != null && oInput.checked == true ) {
				aCells[j] = true;
				oCell.className = "highlight2";
			}
		}
	}
}
rebuildJobAssign();
</script>