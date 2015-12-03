jQuery(document).ready(function () {

	//scroll to top on first load
	document.body.scrollTop = document.documentElement.scrollTop = 0;

	//set timeout function, don't fire any custom event or have to press the button twice
	setTimeout(function() {

		//call event when change button is clicked
		$("button#modal-update").on("click", function(event){

			//als de button oranje (warning) is, haal een nieuw formulier op vanuit de backend-template-update.php
			if ($('button#modal-update').hasClass('btn-warning')) {

				//retrieve content from the backend and fill in modal form
				/* $.ajax({
					cache: false,
					type: 'GET',
					url: "http://thuis.strengholt-online.nl/laravel/public/index.php/cell",
					data: {
						"template_id" : template_id, 
						"cell_id" : cell_id,
						"change" : "yes"
					},
					success: function(data) {
					
						console.log('change clicked');
					
						//show modal and set clone count
						$('#modalContent').show().html(data);
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

							//change name attribute and to increased clonecount
							$( 'tr#' + cloneCount + ' select#system.form-control').attr('name', 'technical[' + cloneCount + '][system]');
							$( 'tr#' + cloneCount + ' select#type.form-control').attr('name', 'technical[' + cloneCount + '][type]');
							$( 'tr#' + cloneCount + ' input#value.form-control').attr('name', 'technical[' + cloneCount + '][value]');
							$( 'tr#' + cloneCount + ' input#description.form-control').attr('name', 'technical[' + cloneCount + '][description]');
							$( 'tr#' + cloneCount + ' input#action').attr('name', 'technical[' + cloneCount + '][action]');

						});
						
					//change class and text in button
					$("button#modal-update").toggleClass('btn-warning btn-danger');
					$("button#modal-update").text("Submit changes");

					
					
					
					
						
					
				}); */
				
				window.location.replace('http://thuis.strengholt-online.nl/laravel/public/index.php/updatecell?template_id=' + template_id + '&cell_id=' + cell_id);

			//if button color is red, submit changes to the backend
			} else {

				//trigger hidden submit button in template change form and close pop-up
				$('button#approve-changes.btn').trigger('click');
				$('#template-modal').modal('hide');

				//enable html scrollbar - avoid double scrolling or unwanted client behaviour
				$("html").css("overflow", "initial");

			}

		});

	//end of time-out function
	}, 0);

	//function to rescale modal height
	function rescale(){
		//get current window size
		var size = {width: $(window).width() , height: $(window).height() }

		//calculate new size
		var offset = 20;
		var offsetBody = 175;

		//set new size for modal
		$('#myModal').css('height', size.height - offset );
		$('.modal-body').css('height', size.height - (offset + offsetBody));
		$('#myModal').css('top', 0);
	}
	$(window).bind("resize", rescale);

	//functionality when a field in the template is clicked
	$(".tablecell").click(function(){

		//disable html scrollbar - avoid double scrolling or unwanted client behaviour
		$("html").css("overflow", "hidden");

		//highlight last clicked cell
		$('table.template').find('.clicked').removeClass("clicked");
		$(this).addClass("clicked");

		//use css id from clicked value to retrieve information from backend
		cell_id = $(this).attr('id');
		template_id = $('div.templateId').attr('id');

		//retrieve content from the backend and fill in modal form
		$.ajax({
			cache: false,
			type: 'GET',
			url: "http://thuis.strengholt-online.nl/laravel/public/index.php/cell",
			data: {
				"template_id" : template_id, 
				"cell_id" : cell_id
			},
			success: function(data)
			{
				//show pop-up with information
				$('#template-modal').modal('show');
				//rescale modal height, see function
				rescale();
				//load html data into modal
				$('#modalContent').show().html(data);
			},
			failure: function (errMsg) {
				console.log(errMsg);
			}
		});

		//reset class and button text in case changes were made before
		$("button#modal-update").attr('class', 'btn btn-warning');
		$("button#modal-update").text("Change values");

	});

	//enable html scrollbar when closing modal
	$('#template-modal').on('hidden.bs.modal', function () {
	  $("html").css("overflow", "initial");
	});
	
	//mark hidden checkbox if cell in table is clicked
	$("table.template-structure td.value").click(function() {
		if ($(this).find('input').is(':checked')) {
			$(this).find('input').prop('checked', false);
			$(this).css("background-color", "transparent");
		} else {
			$(this).find('input').prop('checked', true);
			$(this).css("background-color", "LightGray");
		}
	});

	//uncheck radio buttons if clicked again
	$("table.template-structure input[type='radio']").click(function() {
		var previousValue = $(this).attr('previousValue');
		var id = $(this).attr('id');
		if (previousValue == 'checked') {
			$(this).removeAttr('checked');
			$(this).attr('previousValue', false);
		} else {
			$("input[id="+id+"]:radio").attr('previousValue', false);
			$(this).attr('previousValue', 'checked');
		}
	});
	

});