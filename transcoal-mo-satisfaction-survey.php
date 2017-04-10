<?php
function add_mo_satisfaction_survey_list(){
if(!current_user_can('manage_options')){
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}
	global $wpdb;
	$mo_satisfaction_survey_list = new MoSatisfactionSurveyListClass();

	?>
	<div class='wrap'>
   <div id='icon-users' class='icon32'></div>
    <h2>Missouri Satisfaction Survey</h2>
	<form name="satisfactionSurveyList" id="satisfactionSurveyList">
 <span style="font-size: 16px; padding-right: 53px;"> <b>Start Date:</b> </span>	
 <input type="text" class="custom_date" name="start_date" /><br>
 <span style="font-size: 16px; padding-right: 62px;"> <b>End Date:</b> </span>	
 <input type="text" class="custom_date" name="end_date" /><br>
 <span style="font-size: 16px; padding-right: 62px;"> <b>Module:</b> </span>	
 <select name="module_list_data" id="module_list_data">
	<?php
	$course_list =$mo_satisfaction_survey_list->ModuleListData();
		foreach ($course_list as $course){
			echo "<option value='".$course->course_id."'>".$course->course_title."</option>";
		}
	?>
</select><br>
 <INPUT TYPE="button" name="btn_submit_mo_satisfactionsurvey" id="btn_submit_mo_satisfactionsurvey" value="Download File"><div id="messageareasatisfactionsurvey" ></div>
 </form>
	</div>

<?php
}
//START HELPER FUNCTION CLASS
class MoSatisfactionSurveyListClass{
	    public function ModuleListData(){	
		global $wpdb;
		$course_list = $wpdb->get_results("select course_id, course_title from wp_wpcw_courses where course_id in (17,18,19,21)", OBJECT);
		return $course_list;
	}
		
}//END CLASS
?>