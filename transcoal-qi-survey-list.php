<?php

function add_qi_survey_list(){


if(!current_user_can('manage_options')){
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}
	global $wpdb;
	$qi_survey_list = new qiSurveyListClass();

	?>
	<div class='wrap'>
                <div id='icon-users' class='icon32'></div>
                <h2>QI Survey Report Page</h2>
	<form name="qiSurveyList" id="qiSurveyList">
	<span style="font-size: 16px; padding-right: 92px;"> <b>QI Version</b> </span>
	<select name="qi_quizid" id="qi_quizid">
	<option value="5">QI version 2 (scores 0-3)</option>
	<option value="32" >QI version 2.1 (scores 1-4)</option>
    </select><br>
	
<span style="font-size: 16px; padding-right: 92px;"> <b>State:</b> </span>
	<select name="qi_survey_state" id="qi_survey_state">
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
		<select name="qi_survey_group_rosters" id="qi_survey_group_rosters">
        <option value='All' selected>All</option>		
	<?php 
	
	$pd_hub_user_group = $qi_survey_list->tc_pdhub_get_users_group();

		foreach($pd_hub_user_group as $pd_hub_user){
		
			//get all rosters for every user that has created a roster. They should be a roster leader.
		$user_rosters = $qi_survey_list->tc_pdhub_get_roster_leaders($pd_hub_user->object_id);
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
 <span style="font-size: 16px; padding-right: 2px;"> <b>Survey Instances:</b> </span>
<select name="qi_survey_instance" id="qi_survey_instance">
	<option value="All">All instances</option>
	<option value='most_recent' >Most Recent instance</option>
	<option value='first_instance' >First instance</option>
	</select><br>
 <INPUT TYPE="button" name="btn_submit_qisurvey" id="btn_submit_qisurvey" value="Download File"><div id="messageareaqisurvey" ></div>
 </form>
	</div>

<?php
}
//START CLASS
class qiSurveyListClass{
	
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
	
	
}//END CLASS
?>