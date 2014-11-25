function activate_license(){
	
	//send to backend using ajax call
	$.ajax({
		   type: "GET",
		   async: true,
		   url: "https://www.appnitro.com/licensemanager/activate.php",
		   data: {install_url: window.location.href,
		   		  license_key: $("#license_box").data("licensekey")
				  },
		   cache: false,
		   global: true,
		   dataType: "jsonp",
		   error: function(xhr,text_status,e){
		   },
		   success: function(response_data){
		   		$("#dialog-change-license").dialog('close');
		   		$("#dialog-change-license-btn-save-changes").prop("disabled",false);
				$("#dialog-change-license-btn-save-changes").text("Activate New License");

				if(response_data.status == "invalid_key" || response_data.status == "usage_exceed"){
					alert(response_data.message);
					$("#lic_activate").hide();
					$("#unregisted_holder").text('UNREGISTERED LICENSE');
					$("#lic_type").text("Invalid License");
					$("#lic_customer_id").text('-');
					$("#lic_customer_name").text('-');

					//send to backend using ajax call
					$.ajax({
						   type: "POST",
						   async: true,
						   url: "unregister.php",
						   data: {unregister: '1'},
						   cache: false,
						   global: false,
						   dataType: "json",
						   error: function(xhr,text_status,e){
								//error, display the generic error message		  
						   },
						   success: function(response_data){
								//do nothing   
						   }
					});
				}else if(response_data.status == "valid_key"){
					$("#lic_customer_id").text(response_data.customer_id);
					$("#lic_customer_name").text(response_data.customer_name);
					$("#lic_activate").hide();
					$("#unregisted_holder").text('');
					$("#lic_type").text(response_data.license_type);

					//send to backend using ajax call
					$.ajax({
						   type: "POST",
						   async: true,
						   url: "register.php",
						   data: {
						   		customer_name: response_data.customer_name,
						   		customer_id: response_data.customer_id,
						   		license_key: $("#license_box").data("licensekey")},
						   cache: false,
						   global: false,
						   dataType: "json",
						   error: function(xhr,text_status,e){
								//error, display the generic error message		  
						   },
						   success: function(response_data){
								//do nothing   
						   }
					});
				}
		   }
	});
}

$(function(){
    
	/***************************************************************************************************************/	
	/* 1. Load Tooltips															   				   				   */
	/***************************************************************************************************************/
	
	//we're using jquery tools for the tooltip	
	$(".helpmsg").tooltip({
		
		// place tooltip on the bottom
		position: "bottom center",
		
		// a little tweaking of the position
		offset: [10, 20],
		
		// use the built-in fadeIn/fadeOut effect
		effect: "fade",
		
		// custom opacity setting
		opacity: 0.8,
		
		events: {
			def: 'click,mouseout'
		}
		
	});
	
	/***************************************************************************************************************/	
	/* 2. SMTP Servers settings 														 		  				   */
	/***************************************************************************************************************/
	
	//attach event to 'send notification to my inbox' checkbox
	$("#smtp_enable").click(function(){
		if($(this).prop("checked") == true){
			$("#ms_box_smtp .ms_box_email").slideDown();
		}else{
			$("#ms_box_smtp .ms_box_email").slideUp();
		}
	});

	

	/***************************************************************************************************************/	
	/* 3. Misc Settings 																 		  				   */
	/***************************************************************************************************************/
	
	//Attach event to "advanced options" link 
	$("#more_option_misc_settings").click(function(){
		if($(this).text() == 'advanced options'){
			//expand more options
			$("#ms_box_misc .ms_box_more").slideDown();
			$(this).text('hide options');
			$("#misc_settings_img_arrow").attr("src","images/icons/38_topred_16.png");
		}else{
			$("#ms_box_misc .ms_box_more").slideUp();
			$(this).text('advanced options');
			$("#misc_settings_img_arrow").attr("src","images/icons/38_rightred_16.png");
		}
 
		return false;
	});


	
	/***************************************************************************************************************/	
	/* 4. Attach event to 'Save Settings' button																   */
	/***************************************************************************************************************/
	$("#button_save_main_settings").click(function(){
		
		if($("#button_save_main_settings").text() != 'Saving...'){
				
				//display loader while saving
				$("#button_save_main_settings").prop("disabled",true);
				$("#button_save_main_settings").text('Saving...');
				$("#button_save_main_settings").after("<img style=\"margin-left: 10px\" src='images/loader_small_grey.gif' />");
				
				$("#ms_form").submit();
		}
		
		
		return false;
	});


	/***************************************************************************************************************/	
	/* 5. Dialog Box for change license																		   */
	/***************************************************************************************************************/
	
	$("#dialog-change-license").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['center',150],
		draggable: false,
		resizable: false,
		buttons: [{
			text: 'Activate New License',
			id: 'dialog-change-license-btn-save-changes',
			'class': 'bb_button bb_small bb_green',
			click: function() {
				if($("#dialog-change-license-input").val() == ""){
					alert("Please enter your license key!");
				}else{
					$("#dialog-change-license-btn-save-changes").prop("disabled",true);
					$("#dialog-change-license-btn-save-changes").text("Activating...");

					$("#license_box").data("licensekey",$("#dialog-change-license-input").val());
					activate_license();
				}
			}
		},
		{
			text: 'Cancel',
			id: 'dialog-change-license-btn-cancel',
			'class': 'btn_secondary_action',
			click: function() {
				$("#dialog-change-license-btn-save-changes").prop("disabled",false);
				$("#dialog-change-license-btn-save-changes").text("Activate New License");
				$(this).dialog('close');
			}
		}]

	});


	$("#ms_change_license").click(function(){
		$("#dialog-change-license").dialog('open');
		return false;
	});

	$("#lic_activate").click(function(){
		if($(this).text() != 'activating...'){
			$(this).text('activating...');
			activate_license();
		}

		return false;
	});

	if($("#lic_activate").length > 0){
		activate_license();
	}

	$("#dialog-change-license-form").submit(function(){
		$("#dialog-change-license-btn-save-changes").click();
		return false;
	});
	
});