jQuery(document).ready(function () {

	var cloneCount = 1000;

	//this functionality copies the hidden row when the add new row button is pressed
	$("button#addnewrow").click(function(){

		//increase clone count
		cloneCount++;

		//clone tr line and append to last line
		$tableBody = $('table#technical').find("tbody");
		$trLast = $tableBody.find("tr:last");
		$trNew = $trLast.clone();
		$trNew.show();
		$trNew.attr( "id", cloneCount );
		$trLast.before($trNew);

		//change name attribute and to increased clone count
		$( 'tr#' + cloneCount + ' select#system_id.form-control').attr('name', 'technical[' + cloneCount + '][source_id]');
		$( 'tr#' + cloneCount + ' select#type_id.form-control').attr('name', 'technical[' + cloneCount + '][type_id]');
		$( 'tr#' + cloneCount + ' input#content.form-control').attr('name', 'technical[' + cloneCount + '][content]');
		$( 'tr#' + cloneCount + ' input#description.form-control').attr('name', 'technical[' + cloneCount + '][description]');
		$( 'tr#' + cloneCount + ' input#action').attr('name', 'technical[' + cloneCount + '][action]');

	});

});