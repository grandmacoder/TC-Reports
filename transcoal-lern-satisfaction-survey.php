<?php
function add_lern_satisfaction_survey_list(){
if(!current_user_can('manage_options')){
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}
	global $wpdb;
	$satisfaction_survey_list = new LERNSatisfactionSurveyListClass();
?>
	<div class='wrap'>
   <div id='icon-users' class='icon32'></div>
    <h2>LERN Satisfaction Survey</h2>
	<form name="satisfactionSurveyList" id="satisfactionSurveyList">
	<span style="font-size: 16px; padding-right: 92px;"> <b>State:</b> </span>
	<select name="satisfaction_survey_state" id="satisfaction_survey_state">
									<option value="All">All</option>
									<option value='AL' >Alabama</option>
									<option value='AK' >Alaska</option>
									<option value='AZ' >Arizona</option>
									<option value='AR' >Arkansas</option>
									<option value='CA' >California</option>
									<option value='CO' >Colorado</option>
									<option value='CT' >Connecticut</option>
									<option value='DE' >Delaware</option>
									<option value='DC' >District of Columbia</option>
									<option value='FL' >Florida</option>
									<option value='GA' >Georgia</option>
									<option value='HI' >Hawaii</option>
									<option value='ID' >Idaho</option>
									<option value='IL' >Illinois</option>
									<option value='IN' >Indiana</option>
									<option value='IA' >Iowa</option>
									<option value='KS' >Kansas</option>
									<option value='KY' >Kentucky</option>
									<option value='LA' >Louisiana</option>
									<option value='ME' >Maine</option>
									<option value='MD' >Maryland</option>
									<option value='MA' >Massachusetts</option>
									<option value='MI' >Michigan</option>
									<option value='MN' >Minnesota</option>
									<option value='MS' >Mississippi</option>
									<option value='MO' >Missouri</option>
									<option value='MT' >Montana</option>
									<option value='NE' >Nebraska</option>
									<option value='NV' >Nevada</option>
									<option value='NH' >New Hampshire</option>
									<option value='NJ' >New Jersey</option>
									<option value='NM' >New Mexico</option>
									<option value='NY' >New York</option>
									<option value='NC' >North Carolina</option>
									<option value='ND' >North Dakota</option>
									<option value='OH' >Ohio</option>
									<option value='OK' >Oklahoma</option>
									<option value='OR' >Oregon</option>
									<option value='PA' >Pennsylvania</option>
									<option value='RI' >Rhode Island</option>
									<option value='SC' >South Carolina</option>
									<option value='SD' >South Dakota</option>
									<option value='TN' >Tennessee</option>
									<option value='TX' >Texas</option>
									<option value='UT' >Utah</option>
									<option value='VT' >Vermont</option>
									<option value='VA' >Virginia</option>
									<option value='WV' >West Virginia</option>
									<option value='WI' >Wisconsin</option>
									<option value='WY' >Wyoming</option>   
							</select><br>
		<span style="font-size: 16px; padding-right: 29px;"> <b>Group Roster:</b> </span>
		<select name="satisfaction_survey_group_rosters" id="satisfaction_survey_group_rosters">
        <option value='All' selected>All</option>		
	<?php 
	$pd_hub_user_group = $satisfaction_survey_list->tc_pdhub_get_users_group();
     foreach($pd_hub_user_group as $pd_hub_user){
		//get all rosters for every user that has created a roster. They should be a roster leader.
		$user_rosters = $satisfaction_survey_list->tc_pdhub_get_roster_leaders($pd_hub_user->object_id);
			if($wpdb->num_rows > 0){
				$user_data = get_userdata($pd_hub_user->object_id);
				$user_name = $user_data->last_name.", ".$user_data->first_name;
				echo "<optgroup label='".$user_name."'>";
				foreach($user_rosters as $user_roster){
					echo "<option value='".$user_roster->term_id."'>".$user_roster->name."</option>";	
				}
				echo "</optgroup>";	
			}
		}
		?>
</select><br>
 <span style="font-size: 16px; padding-right: 53px;"> <b>Start Date:</b> </span>	
 <input type="text" class="custom_date" name="start_date" /><br>
 <span style="font-size: 16px; padding-right: 62px;"> <b>End Date:</b> </span>	
 <input type="text" class="custom_date" name="end_date" /><br>
 <span style="font-size: 16px; padding-right: 62px;"> <b>LERN Satisfaction Survey:</b> </span>	
<select name="lern_list_data" id="lern_list_data">
	<?php
	$course_list=$satisfaction_survey_list->ModuleListData();
        foreach ($course_list as $course){
			echo "<option value='".$course->id."'>".$course->name."</option>";
		}
	?>
</select><br>
<span style="font-size: 16px; padding-right: 62px;"> <b>LERN Topic:</b> </span>	
<select name="lern_topic" id="lern_topic">
	<?php
	$topic_list=$satisfaction_survey_list->LernTopicListData();
        foreach ($topic_list as $topic){
	    echo "<option value='".$topic->course_id."'>".$topic->course_title."</option>";
		}
	?>
</select><br>

<INPUT TYPE="button" name="btn_submit_lern_satisfactionsurvey" id="btn_submit_lern_satisfactionsurvey" value="Download File"><div id="messageareasatisfactionsurvey" ></div>
 </form>
</div>
<?php
}
//START HELPER FUNCTION CLASS
class LERNSatisfactionSurveyListClass{
	    public function tc_pdhub_get_users_group(){
			global $wpdb;												
			$pd_hub_user_group = $wpdb->get_results($wpdb->prepare("SELECT object_id FROM wp_term_relationships WHERE term_taxonomy_id = %d ORDER BY `object_id` ASC",38, OBJECT));
			return $pd_hub_user_group;
		}
		public function tc_pdhub_get_roster_leaders($pd_hub_user_id){
			global $wpdb;
			$user_rosters = $wpdb->get_results($wpdb->prepare("select t.term_id, term_order, name FROM
								wp_term_taxonomy t, wp_term_relationships w, wp_terms x 
								WHERE
								w.object_id = %d and 
								t.term_taxonomy_id = w.term_taxonomy_id
								AND
								t.term_id= x.term_id
								and w.term_order = %d
								and taxonomy ='%s' order by term_id desc", $pd_hub_user_id,1, 'user-group', OBJECT));
			return $user_rosters;
		} 
	    public function ModuleListData(){	
		global $wpdb;
		$course_list = $wpdb->get_results("select `id`, `name` from wp_wp_pro_quiz_master where (`name` like '%Satisfaction%' && `name` like '%LERN%')order by `name`", OBJECT);
        return $course_list;
	   }
        public function LernTopicListData(){	
		global $wpdb;
		$course_list = $wpdb->get_results("select course_title, c.course_id from wp_wpcw_course_extras x, wp_wpcw_courses c  where c.course_id = x.course_id and  course_type='LERN'", OBJECT);
        return $course_list;
	   }		   
}//END CLASS
?>