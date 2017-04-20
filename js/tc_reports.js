/*
	tc_reports.js
	
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
	
*/
jQuery(document).ready(function($) {
var current_page = $(location).attr('href');
var baseURL = window.location.protocol+"//"+window.location.host;
//qi filter by state 
$('#qi_table_state').change(function(){

	var selected = $('#qi_table_state').val();

	
	if(selected != ""){
		if(selected == "All"){
			window.location.search = "page=tc-reports";
		}else{
	
		window.location.search = "page=tc-reports&filterState="+selected;
		}
	}
	
});
//custom date picker
$('.custom_date').datepicker({
dateFormat : 'yy-mm-dd',
minDate: new Date(2015,06,17) 
});

$("#btnsubmitlernenrollment").click(function(event) {
	event.preventDefault();
        $( "#messageareaenrollment" ).html( "<p style='color:green;font-weight:bold;'>Please wait while the file downloads.</p>" );
		var baseURL = window.location.protocol+"//"+window.location.host
		var pluginAjaxURL = baseURL +'/wp-content/plugins/transcoal-reports/tc_reports_ajax.php';
		var data = $("#LernEnrollment").serialize();
		data=data+'&action=tc_report_lern_enrollment';
		window.open(pluginAjaxURL+'/?'+data,'_blank' );
});
$("#btnsubmitlernwaitlist").click(function(event) {
	event.preventDefault();
        $( "#messageareawaitlist" ).html( "<p style='color:green;font-weight:bold;'>Please wait while the file downloads.</p>" );
		var baseURL = window.location.protocol+"//"+window.location.host
		var pluginAjaxURL = baseURL +'/wp-content/plugins/transcoal-reports/tc_reports_ajax.php';
		var data = $("#LernWaitlist").serialize();
		data=data+'&action=tc_report_lern_waitlist';
		window.open(pluginAjaxURL+'/?'+data,'_blank' );
});

$("#btnsubmitprepost" ).click(function(event) {
    event.preventDefault();
	var startdate = new Date($("input[name='start_date']").val());
	var enddate = new  Date($("input[name='end_date']").val());
	var module = $("#module_list_data").val();
	var state = $("#pre_post_state").val();
    if(startdate > enddate){
		$( "#messageareaprepost" ).html( "<p style='color:red;font-weight:bold;'>Start Date is not correct. Please choose a start date before end date.</p>" );
	}else{
		$( "#messageareaprepost" ).html( "<p style='color:green;font-weight:bold;'>Your file will download momentarily.</p>" );
		var baseURL = window.location.protocol+"//"+window.location.host
		var pluginAjaxURL = baseURL +'/wp-content/plugins/transcoal-reports/tc_reports_ajax.php';
		var data = $( "#PrePostTest" ).serialize();
		data=data+'&action=tc_report_download';
		console.log(pluginAjaxURL+'/?'+data);
		window.open(pluginAjaxURL+'/?'+data,'_blank' );

		
	}
});
$("#btnsubmitgetattachments").click(function (event){
    var pluginAjaxURL = baseURL +'/wp-content/plugins/transcoal-reports/tc_reports_ajax.php';
	var forum_id = $("#forum_list").val();
        $.ajax({
                   type: "POST",
                   url: pluginAjaxURL,
                   dataType: 'json', 
                   data: {'action':'get_attachments_for_replies', 'forum_id':forum_id},
                   success: function(response){
				   var html = response['summary'];
				    //output the html return here
					$( "#fileAttachmentList" ).html(response['summary']);
						$("#btnSubmitDownloadZipChoices").click(function (event){
							event.preventDefault();
							var  selectedFiles = new Array();
							$('input:checkbox[name=zipFileChoices]').each(function() {    
							   if($(this).is(':checked'))
							   selectedFiles.push($(this).val());
							});
							  // convert to a string
								var attachments = selectedFiles.join(",");
								var baseURL = window.location.protocol+"//"+window.location.host
								var pluginAjaxURL = baseURL +'/wp-content/plugins/transcoal-reports/tc_reports_ajax.php';
								var data = 'action=zip_attachments_from_replies&attachments='+attachments;
								window.open(pluginAjaxURL+'/?'+data,'_blank' );
						});//end handling the download zip button
				 	},  //end success getting list
					error: function(xhr, textStatus, errorThrown){
						alert(textStatus);
					}, //end error on get list ajax
            });//end ajax call
			
}); //end handling the get attachments button click
$("#btnsubmitprepostquick" ).click(function(event) {
	event.preventDefault();
	var startdate = new Date($("input[name='start_date']").val());
	var enddate = new  Date($("input[name='end_date']").val());
	var module = $("#module_list_data").val();
	var state = $("#pre_post_state").val();
    if(startdate > enddate){
		$( "#messageareaprepost" ).html( "<p style='color:red;font-weight:bold;'>Start Date is not correct. Please choose a start date before end date.</p>" );
	}else{
		$( "#messageareaprepost" ).html( "<p style='color:green;font-weight:bold;'>Your file will download momentarily.</p>" );
		var baseURL = window.location.protocol+"//"+window.location.host
		var pluginAjaxURL = baseURL +'/wp-content/plugins/transcoal-reports/tc_reports_ajax.php';
		var data = $("#PrePostTestQuick").serialize();
		data=data+'&action=tc_report_download_quick';
		window.open(pluginAjaxURL+'/?'+data,'_blank' );
    }
});
$("#btnsubmitcaptureclicks" ).click(function(event) {
	event.preventDefault();
	var startdate = new Date($("input[name='start_date']").val());
	var enddate = new  Date($("input[name='end_date']").val());
	var state = $("#pre_post_state").val();
    if (startdate > enddate){
		$( "#messageareaprepost" ).html( "<p style='color:red;font-weight:bold;'>Start Date is not correct. Please choose a start date before end date.</p>" );
	}else{
		$( "#messageareaprepost" ).html( "<p style='color:green;font-weight:bold;'>Your file will download momentarily.</p>" );
		var baseURL = window.location.protocol+"//"+window.location.host
		var pluginAjaxURL = baseURL +'/wp-content/plugins/transcoal-reports/tc_reports_ajax.php';
		var data = $("#CapturedLinkClicks").serialize();
		data=data+'&action=tc_report_captured_clicks';
		
		window.open(pluginAjaxURL+'/?'+data,'_blank' );
    }
});

$("#btn_submit_qisurvey" ).click(function(event) {
	event.preventDefault();
	var startdate = new Date($("input[name='start_date']").val());
	var quizid=$("#qi_quizid").val();
	var enddate = new  Date($("input[name='end_date']").val());
	var state = $("#qi_survey_state").val();
	var qi_instance = $("qi_survey_instance").val();
	var changedquizdate=new Date('2016-08-15');
	if (startdate > changedquizdate && quizid == 5 ){
	$( "#messageareaqisurvey" ).html( "<p style='color:red;font-weight:bold;'>Start Date needs to be prior to Aug. 15, 2016 for the selected version of the QI.</p>" );	
	}
	else if (startdate < changedquizdate && quizid == 32 ){
    $( "#messageareaqisurvey" ).html( "<p style='color:red;font-weight:bold;'>Start Date needs to be after Aug. 14, 2016 for the selected version of the QI.</p>" );	
	}
    else if(startdate > enddate){
		$( "#messageareaqisurvey" ).html( "<p style='color:red;font-weight:bold;'>Start Date is not correct. Please choose a start date before end date.</p>" );
	}
   else{
		$( "#messageareaqisurvey" ).html( "<p style='color:green;font-weight:bold;'>Your file will download momentarily.</p>" );
		var baseURL = window.location.protocol+"//"+window.location.host
		var pluginAjaxURL = baseURL +'/wp-content/plugins/transcoal-reports/tc_reports_ajax.php';
		var data = $( "#qiSurveyList" ).serialize();
		data=data+'&action=qi_report_download';
        window.open(pluginAjaxURL+'/?'+data,'_blank' );
    }
});

$("#btn_submit_satisfactionsurvey" ).click(function(event) {
	event.preventDefault();
	var startdate = new Date($("input[name='start_date']").val());
	var enddate = new  Date($("input[name='end_date']").val());
	var state = $("#satisfaction_survey_state").val();
	var modules = $("module_list_data").val();
    if(startdate > enddate){
		$( "#messageareasatisfactionsurvey" ).html( "<p style='color:red;font-weight:bold;'>Start Date is not correct. Please choose a start date before end date.</p>" );
	}else{
		$( "#messageareasatisfactionsurvey" ).html( "<p style='color:green;font-weight:bold;'>Your file will download momentarily.</p>" );
		var baseURL = window.location.protocol+"//"+window.location.host
		var pluginAjaxURL = baseURL +'/wp-content/plugins/transcoal-reports/tc_reports_ajax.php';
		var data = $( "#satisfactionSurveyList" ).serialize();
		data=data+'&action=satisfaction_survey_report_download';
        window.open(pluginAjaxURL+'/?'+data,'_blank' );
    }
});
$("#btn_submit_lern_satisfactionsurvey" ).click(function(event) {
	event.preventDefault();
	var startdate = new Date($("input[name='start_date']").val());
	var enddate = new  Date($("input[name='end_date']").val());
	var state = $("#satisfaction_survey_state").val();
	var modules = $("module_list_data").val();
    if(startdate > enddate){
		$( "#messageareasatisfactionsurvey" ).html( "<p style='color:red;font-weight:bold;'>Start Date is not correct. Please choose a start date before end date.</p>" );
	}else{
		$( "#messageareasatisfactionsurvey" ).html( "<p style='color:green;font-weight:bold;'>Your file will download momentarily.</p>" );
		var baseURL = window.location.protocol+"//"+window.location.host
		var pluginAjaxURL = baseURL +'/wp-content/plugins/transcoal-reports/tc_reports_ajax.php';
		var data = $( "#satisfactionSurveyList" ).serialize();
		data=data+'&action=lern_satisfaction_survey_report_download';
        window.open(pluginAjaxURL+'/?'+data,'_blank' );
    }
});
$("#btn_submit_mo_satisfactionsurvey" ).click(function(event) {
	event.preventDefault();
	var startdate = new Date($("input[name='start_date']").val());
	var enddate = new  Date($("input[name='end_date']").val());
	var modules = $("module_list_data").val();
    if(startdate > enddate){
		$( "#messageareasatisfactionsurvey" ).html( "<p style='color:red;font-weight:bold;'>Start Date is not correct. Please choose a start date before end date.</p>" );
	}else{
		$( "#messageareasatisfactionsurvey" ).html( "<p style='color:green;font-weight:bold;'>Your file will download momentarily.</p>" );
		var baseURL = window.location.protocol+"//"+window.location.host
		var pluginAjaxURL = baseURL +'/wp-content/plugins/transcoal-reports/tc_reports_ajax.php';
		
		var data = $( "#satisfactionSurveyList" ).serialize();
		data=data+'&action=mo_satisfaction_survey_report_download';
	    window.open(pluginAjaxURL+'/?'+data,'_blank' );
    }
});
});