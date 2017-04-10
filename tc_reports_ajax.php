<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(!isset($wpdb))
{
    require_once('../../../wp-config.php');
    require_once('../../../wp-load.php');
    require_once('../../../wp-includes/wp-db.php');
}
global $wpdb;
$sReturnString="";
$cboString="";
$cboStringFinal="";
/************************************************************************************************************/
if($_GET['action'] == 'lern_satisfaction_survey_report_download'){
	global $wpdb;
	$quiz_id = $_GET['lern_list_data'];
	set_include_path(get_include_path() . PATH_SEPARATOR . '/home/transcoalition/pear/php');
	require_once('Spreadsheet/Excel/Writer.php');
	$workbook = new Spreadsheet_Excel_Writer();
	define('CUSTOM_GRAY', 40);
	define('LIGHT_GRAY', 41);
	$workbook->setCustomColor(CUSTOM_GRAY, 152, 152, 152);
	$workbook->setCustomColor(LIGHT_GRAY, 221, 221, 221);
    $format_header_left =& $workbook->addFormat();
	$format_header_left =& $workbook->addFormat(
		array(	'Bold'		=> 1,
				'Border'	=> 1,
				'Align'		=> 'left'));

	$format_row_even_left =& $workbook->addFormat();
	$format_row_even_left =& $workbook->addFormat(
		array(	'FgColor'	=> '1',
				'Color'		=> 'black',
				'Align'		=> 'left',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

	$format_row_even_center =& $workbook->addFormat();
	$format_row_even_center =& $workbook->addFormat(
		array(	'FgColor'	=> '1',
				'Color'		=> 'black',
				'Align'		=> 'center',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));
	
	$format_row_odd_left =& $workbook->addFormat();
	$format_row_odd_left =& $workbook->addFormat(
		array(	'FgColor'	=> LIGHT_GRAY,
				'Color'		=> 'black',
				'Align'		=> 'left',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

	$format_row_odd_center =& $workbook->addFormat();
	$format_row_odd_center =& $workbook->addFormat(
		array(	'FgColor'	=> LIGHT_GRAY,
				'Color'		=> 'black',
				'Align'		=> 'center',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

	$worksheet =&$workbook->addWorksheet('LERN Satisfaction Survey Data');
	
		// set column widths
    $worksheet->setColumn(0,0,20); // User id
	$worksheet->setColumn(1,1,20); // Last Name
	$worksheet->setColumn(2,2,20); // First Name
	$worksheet->setColumn(3,3,30); // E-mail
	$worksheet->setColumn(4,4,10); // Date
	$worksheet->setColumn(5,5,6); // State
	$worksheet->setColumn(6,6,30); // District
	$worksheet->setColumn(7,7,30); // Role
	$col = 7;
		//get header column data for spread sheet
	$quiz_categorys = $wpdb->get_results("SELECT distinct c.category_id, category_name FROM wp_wp_pro_quiz_question q, wp_wp_pro_quiz_category c 
										WHERE q.quiz_id = ". $quiz_id ." and q.category_id = c.category_id AND online = 1 ", OBJECT);
	$q_categories = array();
	$q_category_ids = array();
	foreach($quiz_categorys as $quiz_category){
		$category = $quiz_category->category_id;
		$q_category_ids[] = $quiz_category->category_id;
		$q_categories[] = $quiz_category->category_name;
		$category_questions = $wpdb->get_results("SELECT distinct q.id, question FROM wp_wp_pro_quiz_question q, wp_wp_pro_quiz_category c 
												WHERE q.quiz_id = ". $quiz_id." and q.category_id = ".$category." AND online = 1", OBJECT);
		foreach ($category_questions as $question) {
			$col++;
		}
		$col++;
		$worksheet->setColumn($col, $col, 13); // set widths of "Domain Score" columns
	}
    $worksheet->setColumn(6,100,4); // questions
    $worksheet->setColumn($col+1, $col+1,50); // Q1
	$worksheet->setColumn($col+2, $col+2,50); // Q2
	// build the header row
	$col = 0;
	$worksheet->write(1, $col++, "User ID", $format_header_left);
	$worksheet->write(1, $col++, "Last Name", $format_header_left);
	$worksheet->write(1, $col++, "First Name", $format_header_left);
	$worksheet->write(1, $col++, "E-mail", $format_header_left);
	$worksheet->write(1, $col++, "Date", $format_header_left);
	$worksheet->write(1, $col++, "State", $format_header_left);
	$worksheet->write(1, $col++, "District", $format_header_left);
	$worksheet->write(1, $col++, "Role", $format_header_left);
	$row = 2;
	$numquestions =0;
	// finish out the header rows with the domains and questions
	foreach ($quiz_categorys as $quiz_category) {
		$category = $quiz_category->category_id;
		$category_name = $quiz_category->category_name;
	    $category_questions = $wpdb->get_results("SELECT distinct q.id, question FROM wp_wp_pro_quiz_question q, wp_wp_pro_quiz_category c 
												WHERE q.quiz_id = ". $quiz_id. " and q.category_id = ".$category." AND online = 1 order by sort", OBJECT);
		$domaincol = $col;
		// first print the domain text
		$worksheet->write(0, $col++, $category_name, $format_header_left);
		// now put in an empty cell for each question
		$col--;
		$questioncount = 0;
		foreach ($category_questions as $question) {
			$numquestions++;
			$question_num = $questioncount+1;
			$worksheet->write(1, $col, "Q" . $numquestions, $format_header_left);
			//clean these up bc extra information goes with some questions
	        $question = str_replace('Identify the usefulness of each module element as it relates to improving transition practices and services.','',$question->question);
			$question =str_replace('Identify the relevance of each module element to your work.','',$question);
			$question =strip_tags($question);
			$worksheet->writeNote(1, $col++, ltrim($question));
			$questioncount++;
		}
		$worksheet->write(1, $col++, 'Domain Score', $format_header_left);
		// now merge the cells above the domain's questions
		$y = $domaincol + $questioncount;
		$worksheet->mergeCells(0, $domaincol, 0, $y);
	}
	//add columns for openended questions
	$extraQuestions = $wpdb->get_results("Select fieldname from wp_wp_pro_quiz_form where quiz_id = " .$quiz_id . " order by form_id", OBJECT);
	$qIndex =$questioncount;

	foreach ($extraQuestions as $q){
	$qIndex++;
	$question = $q->fieldname;
  	$worksheet->write(1, $col, 'Q'.$qIndex , $format_header_left);
	$worksheet->writeNote(1, $col++, ltrim($question));
	}
	//set up the form ids for extracting extra fields
	$aFormIDs=array();
		$formIDs = $wpdb->get_results("Select form_id from wp_wp_pro_quiz_form where quiz_id = ". $quiz_id . " order by form_id", OBJECT);
		foreach ($formIDs as $formID){
		array_push($aFormIDs,$formID->form_id);
		}
	//initialize variables
	$user_id = "";
	$qi_date = "";
	$question = "";
	$question_category = "";
	$answer_data = "";
	$points = "";
	$user_meta = "";
	$user_data = "";
	$email = "";
	$last_name = "";
	$first_name = "";
	$state = "";
	$role = "";
	//get variables from url string
	$state = $_GET['satisfaction_survey_state'];
	$roster_group = $_GET['satisfaction_survey_group_rosters'];

	$start_date = $_GET['start_date'];
	$end_date = $_GET['end_date'];
	$group_by_user_id = "";
	if ($roster_group <> 'All'){
	$users = $wpdb->get_results($wpdb->prepare("SELECT object_id, term_order 
        FROM wp_term_relationships r, wp_term_taxonomy x 
        WHERE
		r.term_taxonomy_id = x.term_taxonomy_id 
		AND 
		term_order in (3,4) AND x.term_id = %d
		ORDER BY term_order",$roster_group), OBJECT);
    foreach($users as $user){
		$s_users .= $user->object_id.",";
		}
		$s_users = substr($s_users, 0, -1);
	    $s_user_where = " AND user_id in(".$s_users.") ";
	}
	if ($start_date <> ""  && $end_date <> ""){
	$dateWhere = " AND FROM_UNIXTIME(create_time) >= '".$start_date."' AND FROM_UNIXTIME(create_time) <= '".$end_date."'" ;
	}
	$create_time = ' FROM_UNIXTIME(create_time) as qi_date';
	
	if ($state <> 'All'){
	$stateWhere =" and user_id in (select user_id from wp_usermeta where meta_key='state' and meta_value='". $state."') ";
    }
	//query to the database
	$report_users = $wpdb->get_results("SELECT distinct user_id, ".$create_time."
	FROM wp_wp_pro_quiz_category c, wp_wp_pro_quiz_statistic_ref r, wp_wp_pro_quiz_statistic s, wp_wp_pro_quiz_question q where q.category_id = c.category_id AND r.quiz_id =".$quiz_id." 
	AND q.quiz_id = ".$quiz_id." AND question_id = q.id AND r.statistic_ref_id = s.statistic_ref_id AND q.online=1
	".$s_user_where . $dateWhere . $stateWhere . $group_by_user_id." order by user_id, q.sort", OBJECT);
	$loop_count = 0;
	foreach($report_users as $row_a){
		if ($row & 1) {
			$format_row_left = $format_row_even_left;
			$format_row_center = $format_row_even_center;
		} else {
			$format_row_left = $format_row_odd_left;
			$format_row_center = $format_row_odd_center;
		}
		$user_id = $row_a->user_id;
		$qi_date = $row_a->qi_date;
		//get user data 
		$user_meta = get_user_meta($user_id);
		$user_data = get_userdata($user_id);
		$email = $user_data->user_email;
		$last_name = $user_meta['last_name'][0];
		$first_name = $user_meta['first_name'][0];
		$state = $user_meta['state'][0];
		$role = $user_meta['transition_profile_role'][0];
		if($user_meta['school_district'][0] != ""){
		$district = $user_meta['school_district'][0];
		}
		else{
		$district = "Other";
		}
		$col=0;
		$worksheet->write($row, $col++, $user_id, $format_row_left);
		$worksheet->write($row, $col++, $last_name, $format_row_left);
		$worksheet->write($row, $col++, $first_name, $format_row_left);
		$worksheet->write($row, $col++, $email, $format_row_left);
		$worksheet->write($row, $col++, date("m-d-Y", strtotime($qi_date)), $format_row_left);
		$worksheet->write($row, $col++, $state, $format_row_center);
		$worksheet->write($row, $col++, $district, $format_row_left);
		$worksheet->write($row, $col++, $role, $format_row_left);
	
		foreach($q_category_ids as $cat_id){
		$report_result = $wpdb->get_results("SELECT s.points
				FROM wp_wp_pro_quiz_category c, wp_wp_pro_quiz_statistic_ref r, wp_wp_pro_quiz_statistic s, wp_wp_pro_quiz_question q where q.category_id = c.category_id AND r.quiz_id =".$quiz_id." 
				AND FROM_UNIXTIME(create_time) = '".$qi_date."' AND user_id =".$user_id." AND q.category_id =".$cat_id." AND q.quiz_id = ".$quiz_id." AND question_id = q.id AND r.statistic_ref_id = s.statistic_ref_id AND q.online=1
				order by user_id, q.sort", OBJECT);
			$count_questions = 0;
			$qi_total_points = 0;
			$average_score = 0;
			foreach($report_result as $row_b){
				$qi_answer = $row_b->points;
		
					$worksheet->write($row, $col++, $qi_answer, $format_row_center);
					//keep track of points for a total to figure out an average
					$qi_total_points += $qi_answer;
					$count_questions++;	
				}
			//calculate average score
			$average_score = ($qi_total_points/$count_questions);
			$average_score = round($average_score, 1);
			$worksheet->write($row, $col++, $average_score, $format_row_center);
			}
		//get the users answers for the extra questions --- open ended
        $answerrows = $wpdb->get_results("select form_data, user_id from wp_wp_pro_quiz_statistic_ref where user_id=".$user_id." and quiz_id =". $quiz_id, OBJECT );
		$aAns=array();
		foreach ( $answerrows as $answerrow){
		if ($answerrow->form_data){
			$json = $answerrow->form_data;
			$obj =json_decode($json, true);
			for ($k=0; $k < count($obj); $k++){
			$theanswer=$obj[$aFormIDs[$k]];
			//write the answer to the column
			$worksheet->write($row, $col++, $theanswer , $format_row_left);
			}
		}
		else{
			for ($i=0; $i< count($aFormIDs); $i++){
			$theanswer='';
			//write the answer to the column
			$worksheet->write($row, $col++, $theanswer , $format_row_left);	
			}
		}
        }
	$row++;
    }
//now create the second worksheet with the LERN answers and rubric info
$worksheet =&$workbook->addWorksheet('LERN Answer and Rubric Data');
//set up data
$course_id =$_GET['lern_topic'];
$aQuestions=array();
//get the questions
$questions = $wpdb->get_results("select distinct(description) from wp_course_activities where post_id in (select unit_id from wp_wpcw_units_meta m, wp_posts p where p.ID = m.unit_id and parent_course_id = ".$course_id." and post_title like '%engage%')", OBJECT);
foreach ($questions as $question){
array_push($aQuestions,$question->description); 
}

// set column widths
    $worksheet->setColumn(1,1,20); // Q1
	$worksheet->setColumn(1,1,20); // Score 1
	$worksheet->setColumn(1,1,20); // Q2
	$worksheet->setColumn(1,1,20); // Score 2
// build the header row
	$col = 0;
    $worksheet->write(1, $col++, "Q1 Answer", $format_header_left);
	$worksheet->write(1, $col++, "Self Score Q1", $format_header_left);
    $worksheet->write(1, $col++, "Q2 Answer", $format_header_left);
	$worksheet->write(1, $col++, "Self Score Q2", $format_header_left);
	$row = 2;
	//get the answers from the activities table
	$activities =$wpdb->get_results("select * from wp_course_activities where post_id in  (select unit_id from wp_wpcw_units_meta m, wp_posts p where p.ID = m.unit_id and parent_course_id = ". $course_id ." and post_title like '%engage%')
order by user_id, page_order", OBJECT);
$col =0;	
	foreach ($activities as $activity){
        $worksheet->write($row, $col++, $activity->activity_value, $format_row_left);
		$worksheet->write($row, $col++, $activity->selfgrade, $format_row_left);
        if ($activity->page_order == 2)	{
		$row++;
        $col =0;		
		}	

	}
//add the questions for the last row
$row+=2;
$worksheet->write($row, $col++, "QUESTIONS", $format_header_left);
$row++;
$col=0;
$worksheet->write($row, $col++, $aQuestions[0], $format_row_left);
$row++;
$col=0;
$worksheet->write($row, $col++, $aQuestions[1], $format_row_left);

$workbook->send('lern_satisfaction_survey_results_'.date(YmdHis).'.xls');
$workbook->close();
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=lern_satisfaction_survey_data.xls");
echo $xls;


}//end if satisfaction survey report list

//****************************************************************************************************************
if($_GET['action'] == 'satisfaction_survey_report_download'){
	global $wpdb;
	$quiz_id = $_GET['module_list_data'];
	set_include_path(get_include_path() . PATH_SEPARATOR . '/home/transcoalition/pear/php');
	require_once('Spreadsheet/Excel/Writer.php');
	
	$workbook = new Spreadsheet_Excel_Writer();

	define('CUSTOM_GRAY', 40);
	define('LIGHT_GRAY', 41);
	$workbook->setCustomColor(CUSTOM_GRAY, 152, 152, 152);
	$workbook->setCustomColor(LIGHT_GRAY, 221, 221, 221);

	$format_header_left =& $workbook->addFormat();
	$format_header_left =& $workbook->addFormat(
		array(	'Bold'		=> 1,
				'Border'	=> 1,
				'Align'		=> 'left'));

	$format_row_even_left =& $workbook->addFormat();
	$format_row_even_left =& $workbook->addFormat(
		array(	'FgColor'	=> '1',
				'Color'		=> 'black',
				'Align'		=> 'left',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

	$format_row_even_center =& $workbook->addFormat();
	$format_row_even_center =& $workbook->addFormat(
		array(	'FgColor'	=> '1',
				'Color'		=> 'black',
				'Align'		=> 'center',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));
	
	$format_row_odd_left =& $workbook->addFormat();
	$format_row_odd_left =& $workbook->addFormat(
		array(	'FgColor'	=> LIGHT_GRAY,
				'Color'		=> 'black',
				'Align'		=> 'left',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

	$format_row_odd_center =& $workbook->addFormat();
	$format_row_odd_center =& $workbook->addFormat(
		array(	'FgColor'	=> LIGHT_GRAY,
				'Color'		=> 'black',
				'Align'		=> 'center',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

	$worksheet =& $workbook->addWorksheet('My First Worksheet');
	
		// set column widths
    $worksheet->setColumn(0,0,20); // User id
	$worksheet->setColumn(1,1,20); // Last Name
	$worksheet->setColumn(2,2,20); // First Name
	$worksheet->setColumn(3,3,30); // E-mail
	$worksheet->setColumn(4,4,10); // Date
	$worksheet->setColumn(5,5,6); // State
	$worksheet->setColumn(6,6,30); // District
	$worksheet->setColumn(7,7,30); // Role
	$col = 7;
		//get header column data for spread sheet
	$quiz_categorys = $wpdb->get_results("SELECT distinct c.category_id, category_name FROM wp_wp_pro_quiz_question q, wp_wp_pro_quiz_category c 
										WHERE q.quiz_id = ". $quiz_id ." and q.category_id = c.category_id AND online = 1 order by sort", OBJECT);
	$q_categories = array();
	$q_category_ids = array();

	foreach($quiz_categorys as $quiz_category){
		$category = $quiz_category->category_id;
		$q_category_ids[] = $quiz_category->category_id;
		$q_categories[] = $quiz_category->category_name;
		$category_questions = $wpdb->get_results("SELECT distinct q.id, question FROM wp_wp_pro_quiz_question q, wp_wp_pro_quiz_category c 
												WHERE q.quiz_id = ". $quiz_id." and q.category_id = ".$category." AND online = 1 order by sort", OBJECT);
		foreach ($category_questions as $question) {
			$col++;
		}
		$col++;
		$worksheet->setColumn($col, $col, 13); // set widths of "Domain Score" columns
	}
    
	$worksheet->setColumn(6,100,4); // questions
    $worksheet->setColumn($col+1, $col+1,50); // Q1
	$worksheet->setColumn($col+2, $col+2,50); // Q2
	// build the header row
	$col = 0;
	$worksheet->write(1, $col++, "User ID", $format_header_left);
	$worksheet->write(1, $col++, "Last Name", $format_header_left);
	$worksheet->write(1, $col++, "First Name", $format_header_left);
	$worksheet->write(1, $col++, "E-mail", $format_header_left);
	$worksheet->write(1, $col++, "Date", $format_header_left);
	$worksheet->write(1, $col++, "State", $format_header_left);
	$worksheet->write(1, $col++, "District", $format_header_left);
	$worksheet->write(1, $col++, "Role", $format_header_left);
	$row = 2;
	$numquestions =0;
	// finish out the header rows with the domains and questions
	foreach ($quiz_categorys as $quiz_category) {
		$category = $quiz_category->category_id;
		$category_name = $quiz_category->category_name;
	
		$category_questions = $wpdb->get_results("SELECT distinct q.id, question FROM wp_wp_pro_quiz_question q, wp_wp_pro_quiz_category c 
												WHERE q.quiz_id = ". $quiz_id. " and q.category_id = ".$category." AND online = 1 order by sort", OBJECT);
		$domaincol = $col;
		// first print the domain text
		$worksheet->write(0, $col++, $category_name, $format_header_left);
		// now put in an empty cell for each question
		$col--;
		$questioncount = 0;
		foreach ($category_questions as $question) {
			$numquestions++;
			$question_num = $questioncount+1;
			$worksheet->write(1, $col, "Q" . $numquestions, $format_header_left);
			//clean these up bc extra information goes with some questions
	        $question = str_replace('Identify the usefulness of each module element as it relates to improving transition practices and services.','',$question->question);
			$question =str_replace('Identify the relevance of each module element to your work.','',$question);
			$question =strip_tags($question);
			$worksheet->writeNote(1, $col++, ltrim($question));
			$questioncount++;
		}
		$worksheet->write(1, $col++, 'Domain Score', $format_header_left);
		// now merge the cells above the domain's questions
		$y = $domaincol + $questioncount;
		$worksheet->mergeCells(0, $domaincol, 0, $y);
	}
	//add columns for openended questions
	$question1 ="16. What did you like best about this module?";
	$question2="17. What would you change in this module?";
	$worksheet->write(1, $col, 'Q16', $format_header_left);
	$worksheet->writeNote(1, $col++, ltrim($question1));
	$worksheet->write(1, $col, 'Q17', $format_header_left);
	$worksheet->writeNote(1, $col++, ltrim($question2));
	//initialize variables
	$user_id = "";
	$qi_date = "";
	$question = "";
	$question_category = "";
	$answer_data = "";
	$points = "";
	$user_meta = "";
	$user_data = "";
	$email = "";
	$last_name = "";
	$first_name = "";
	$state = "";
	$role = "";
	//get variables from url string
	$state = $_GET['satisfaction_survey_state'];
	$roster_group = $_GET['satisfaction_survey_group_rosters'];

	$start_date = $_GET['start_date'];
	$end_date = $_GET['end_date'];
	$group_by_user_id = "";
	if ($roster_group <> 'All'){
	$users = $wpdb->get_results($wpdb->prepare("SELECT object_id, term_order 
        FROM wp_term_relationships r, wp_term_taxonomy x 
        WHERE
		r.term_taxonomy_id = x.term_taxonomy_id 
		AND 
		term_order in (3,4) AND x.term_id = %d
		ORDER BY term_order",$roster_group), OBJECT);
    foreach($users as $user){
		$s_users .= $user->object_id.",";
		}
		$s_users = substr($s_users, 0, -1);
	    $s_user_where = " AND user_id in(".$s_users.") ";
	}
	if ($start_date <> ""  && $end_date <> ""){
	$dateWhere = " AND FROM_UNIXTIME(create_time) >= '".$start_date."' AND FROM_UNIXTIME(create_time) <= '".$end_date."'" ;
	}
	$create_time = ' FROM_UNIXTIME(create_time) as qi_date';
	
	if ($state <> 'All'){
	$stateWhere =" and user_id in (select user_id from wp_usermeta where meta_key='state' and meta_value='". $state."') ";
    }
	//query to the database
	$report_users = $wpdb->get_results("SELECT distinct user_id, ".$create_time."
	FROM wp_wp_pro_quiz_category c, wp_wp_pro_quiz_statistic_ref r, wp_wp_pro_quiz_statistic s, wp_wp_pro_quiz_question q where q.category_id = c.category_id AND r.quiz_id =".$quiz_id." 
	AND q.quiz_id = ".$quiz_id." AND question_id = q.id AND r.statistic_ref_id = s.statistic_ref_id AND q.online=1
	".$s_user_where . $dateWhere . $stateWhere . $group_by_user_id." order by user_id, q.sort", OBJECT);
	$loop_count = 0;
	foreach($report_users as $row_a){
		if ($row & 1) {
			$format_row_left = $format_row_even_left;
			$format_row_center = $format_row_even_center;
		} else {
			$format_row_left = $format_row_odd_left;
			$format_row_center = $format_row_odd_center;
		}
		$user_id = $row_a->user_id;
		$qi_date = $row_a->qi_date;
		//get user data 
		$user_meta = get_user_meta($user_id);
		$user_data = get_userdata($user_id);
		$email = $user_data->user_email;
		$last_name = $user_meta['last_name'][0];
		$first_name = $user_meta['first_name'][0];
		$state = $user_meta['state'][0];
		$role = $user_meta['transition_profile_role'][0];
		if($user_meta['school_district'][0] != ""){
		$district = $user_meta['school_district'][0];
		}
		else{
				$district = "Other";
		}
		$col=0;
		$worksheet->write($row, $col++, $user_id, $format_row_left);
		$worksheet->write($row, $col++, $last_name, $format_row_left);
		$worksheet->write($row, $col++, $first_name, $format_row_left);
		$worksheet->write($row, $col++, $email, $format_row_left);
		$worksheet->write($row, $col++, date("m-d-Y", strtotime($qi_date)), $format_row_left);
		$worksheet->write($row, $col++, $state, $format_row_center);
		$worksheet->write($row, $col++, $district, $format_row_left);
		$worksheet->write($row, $col++, $role, $format_row_left);
	
		foreach($q_category_ids as $cat_id){
		$report_result = $wpdb->get_results("SELECT s.points
				FROM wp_wp_pro_quiz_category c, wp_wp_pro_quiz_statistic_ref r, wp_wp_pro_quiz_statistic s, wp_wp_pro_quiz_question q where q.category_id = c.category_id AND r.quiz_id =".$quiz_id." 
				AND FROM_UNIXTIME(create_time) = '".$qi_date."' AND user_id =".$user_id." AND q.category_id =".$cat_id." AND q.quiz_id = ".$quiz_id." AND question_id = q.id AND r.statistic_ref_id = s.statistic_ref_id AND q.online=1
				order by user_id, q.sort", OBJECT);
			$count_questions = 0;
			$qi_total_points = 0;
			$average_score = 0;
			foreach($report_result as $row_b){
				$qi_answer = $row_b->points;
		
					$worksheet->write($row, $col++, $qi_answer, $format_row_center);
					//keep track of points for a total to figure out an average
					$qi_total_points += $qi_answer;
					$count_questions++;	
				}
			//calculate average score
			$average_score = ($qi_total_points/$count_questions);
			$average_score = round($average_score, 1);
			$worksheet->write($row, $col++, $average_score, $format_row_center);
			}
			//get the users answers for the extra questions --- open ended
        $answerrows = $wpdb->get_results("select form_data from wp_wp_pro_quiz_statistic_ref where user_id=".$user_id." and quiz_id =". $quiz_id."  and form_data <> ''", OBJECT );
        $a1="n/a";
		$a2="n/a";
		$aAns=array();
        foreach ( $answerrows as $answerrow){
			$aAns = explode(":",$answerrow->form_data);
			$a1=substr($aAns[1], 0, strpos($aAns[1],','));
		    $a1=str_replace('"','',$a1);
			$a2= substr($aAns[2], 0, strpos($aAns[2],'}'));
			$a2=str_replace('"','',$a2);
			}
	    $worksheet->write($row, $col++, $a1, $format_row_left);
		$worksheet->write($row, $col++,$a2, $format_row_left);
		$row++;
	}

	$workbook->send('satisfaction_survey_results_'.date(YmdHis).'.xls');
	$workbook->close();

header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=satisfaction_survey_data.xls");
echo $xls;
}//end if satisfaction survey report list
//from a string of ids get the file paths and put them into a zip file
if ($_GET['action'] == 'zip_attachments_from_replies'){
$fileIDs= $_GET['attachments'];
$aFileIDs = explode(",", $fileIDs);
$files = array();
for ($i=0; $i<count($aFileIDs); $i++){
$filepath =get_attached_file( $aFileIDs[$i] );	
$files[$i]=  $filepath;
}
# create new zip opbject
$zip = new ZipArchive();
$filename = "attachments_".time().".zip";
$filepath = "/home/transcoalition/webapps/wordpress_test2/wp-content/uploads/attachment_zip/";
# create a temp file & open it
$res = $zip->open($filepath.$filename, ZipArchive::CREATE);
if ($res === TRUE) {

# loop through each file
foreach($files as $file){
    # download file
    $download_file = file_get_contents($file);
    $sql.=basename($file);
    #add it to the zip
   $zip->addFromString(basename($file),$download_file);
}
}
else{
$sql.="could not make file.";	
}
# close zip
$zip->close();
$filesize=filesize($filepath.$filename);
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"".$filename."\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".$filesize);
ob_end_flush();
@readfile($filepath.$filename);
//now delete the file
unlink($filepath.$filename);

}
if ($_POST['action'] == 'get_attachments_for_replies'){
//get the child posts (topics) for the forum into an array
$args = array(
	'post_parent' => $_POST['forum_id'],
	'post_type'   => 'topic', 
	'numberposts' => -1,
	'post_status' => 'publish'
); 
$topics = get_children( $args, OBJECT );
if ($topics){

	foreach ($topics as $topic){
		$topicTitle=$topic->post_title;
		$sTopicString="<h3>Topic is '" .$topicTitle."'</h3>"; 
		$topicID= $topic->ID;
	     $args = array(
	      'post_parent' => $topicID,
	      'post_type'   => 'reply', 
	      'numberposts' => -1,
	      'post_status' => 'publish'
         ); 
		 $replies = get_children( $args, OBJECT );
		 if ($replies){
			  foreach ($replies as $reply){
			    $reply_id = $reply->ID;
			      if ($reply_id > 0){
					$attachments = get_posts( array(
			                 'post_type' => 'attachment',
			                 'posts_per_page' => -1,
			                 'post_parent' =>  $reply_id,
			                 'exclude'     => get_post_thumbnail_id()
		            ) );
					
						if ($attachments){
							$cboString="";
						    foreach ($attachments as $attachment){
							$user = get_userdata( $attachment->post_author );
							$cboString.="<input type='checkbox' name='zipFileChoices' value=".$attachment->ID ."><a href='". wp_get_attachment_url( $attachment->ID ) ."' target=_blank>". $attachment->post_title ." </a> by <strong>" . $user->first_name ." " . $user->last_name ."</strong>  uploaded on: ". $attachment->post_date."</input><BR>";
							}
						 $cboStringFinal .= $sTopicString.$cboString;
						}
						
					}
			 }
		 }
		
	}   
}//end if there are topics
else{
$sReturnString.="<h3>There are no topics for the selected  forum</h3>";
}
if ($cboStringFinal <> ""){$sReturnString.="<form name='frmDownloadZipChoices' id='frmDownloadZipChoices'>". $cboStringFinal ."<br><input type=button id='btnSubmitDownloadZipChoices' name='btnSubmitDownloadZipChoices' value ='Submit for Zip Download'></form>";}
else{$sReturnString.= "<h4>There were no files attached to any topics.</h4>";}
$returnvars = array(
            "summary" => $sReturnString,
          );
print json_encode($returnvars);
}//end function
//mo satisfaction survey report from courseware quizzes
if($_GET['action'] == 'mo_satisfaction_survey_report_download'){
global $wpdb;
set_include_path(get_include_path() . PATH_SEPARATOR . '/home/transcoalition/pear/php');
require_once('Spreadsheet/Excel/Writer.php');
$workbook = new Spreadsheet_Excel_Writer();
    define('CUSTOM_GRAY', 40);
	define('LIGHT_GRAY', 41);
	$workbook->setCustomColor(CUSTOM_GRAY, 152, 152, 152);
	$workbook->setCustomColor(LIGHT_GRAY, 221, 221, 221);

	$format_header_left =& $workbook->addFormat();
	$format_header_left =& $workbook->addFormat(
		array(	'Bold'		=> 1,
				'Border'	=> 1,
				'Align'		=> 'left'));

	$format_row_even_left =& $workbook->addFormat();
	$format_row_even_left =& $workbook->addFormat(
		array(	'FgColor'	=> '1',
				'Color'		=> 'black',
				'Align'		=> 'left',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

	$format_row_even_center =& $workbook->addFormat();
	$format_row_even_center =& $workbook->addFormat(
		array(	'FgColor'	=> '1',
				'Color'		=> 'black',
				'Align'		=> 'center',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));
	
	$format_row_odd_left =& $workbook->addFormat();
	$format_row_odd_left =& $workbook->addFormat(
		array(	'FgColor'	=> LIGHT_GRAY,
				'Color'		=> 'black',
				'Align'		=> 'left',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

	$format_row_odd_center =& $workbook->addFormat();
	$format_row_odd_center =& $workbook->addFormat(
		array(	'FgColor'	=> LIGHT_GRAY,
				'Color'		=> 'black',
				'Align'		=> 'center',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));
	$worksheet = $workbook->addWorksheet('PrePostList');
		// set column widths
	$worksheet->setColumn(0,0,8); // User ID
	$worksheet->setColumn(1,1,50); // Last
	$worksheet->setColumn(1,1,50); // First
	$worksheet->setColumn(2,2,30); // Role
	$worksheet->setColumn(3,3,30); // Email
	$worksheet->setColumn(4,4,50); // District
     $worksheet->setColumn(4,4,15); // Date
	//Get data from post
	if (isset($_GET['module_list_data']) && is_numeric($_GET['module_list_data'])){
    $module_id = $_GET['module_list_data'];
	}
	$start_date = $_GET['start_date'];
	$end_date = $_GET['end_date'];
	//user selects to download report for all rosters
	//user selects the data range
	if ($start_date <> ""  && $end_date <> ""){
	$dateWhere = " AND quiz_completed_date >='". $start_date ."' AND quiz_completed_date <= '". $end_date ."' " ;
	}
    //get main rows about pre and post test based on form instructions
	$quiz_details = $wpdb->get_results("select distinct q.quiz_id, course_title,user_id, quiz_data, quiz_correct_questions, quiz_question_total, 
    quiz_completed_date as completed_date 
		from wp_wpcw_user_progress_quizzes p, 
		wp_wpcw_quizzes q ,
		wp_wpcw_courses c
		where q.quiz_id = p.quiz_id 
		and 
		c.course_id = q.parent_course_id". $dateWhere . " 
        and quiz_title LIKE '%survey%' and course_id =". $module_id ." ORDER BY user_id, q.quiz_id", OBJECT);
	  $numrows = $wpdb->num_rows;
	if ($numrows == 0){
	die( "there were no records");
	}
	else{ 
	$quiz_questions = $wpdb->get_results("SELECT question_question FROM `wp_wpcw_quizzes_questions` where parent_quiz_id in  (select min(quiz_id) from wp_wpcw_quizzes where parent_course_id = ". $module_id  ." and quiz_title like '%survey%') order by question_order", OBJECT);
    $numquestions = count($quiz_questions);
    $pre_test_id=$wpdb->get_var("select quiz_id from wp_wpcw_quizzes where parent_course_id = ". $module_id  ."  and quiz_title like '%survey%' ");
	$col = 0;
	$worksheet->write(1, $col++, "User ID", $format_header_left);
	$worksheet->write(1, $col++, "Last Name", $format_header_left);
	$worksheet->write(1, $col++, "First name", $format_header_left);
	$worksheet->write(1, $col++, "Role", $format_header_left);
	$worksheet->write(1, $col++, "Email", $format_header_left);
	$worksheet->write(1, $col++, "District", $format_header_left);
	$worksheet->write(1, $col++, "Date", $format_header_left);
    foreach($quiz_questions as $question){
    $worksheet->write(1, $col++, $question->question_question, $format_header_left);
    }
	//user and questions are now in the headings
		$col=0;
		$row=2;	
		$data=array();	
    foreach($quiz_details as $q_id){
	$current_user_id = $q_id->user_id;
	$course_title = str_replace(" ","_",$q_id->course_title);
			$user_meta = get_user_meta($q_id->user_id);
			$user_data = get_userdata($q_id->user_id);
			//now write a row with post test plus pre test
			$worksheet->write($row, $col++, $q_id->user_id, $format_row_left);
			$worksheet->write($row, $col++, $user_meta['last_name'][0], $format_row_left);
			$worksheet->write($row, $col++, $user_meta['first_name'][0], $format_row_left);
			$worksheet->write($row, $col++, $user_meta['transition_profile_role'][0], $format_row_left);
			$worksheet->write($row, $col++, $user_data->user_email, $format_row_left);
			$worksheet->write($row, $col++, $user_meta['transition_school_district'][0], $format_row_left);
			$worksheet->write($row, $col++, $q_id->completed_date, $format_row_left);
			$user_quiz->quiz_data = maybe_unserialize($q_id->quiz_data);
			$i=1;
			foreach ($user_quiz->quiz_data as $thedata){
				if ($i <> 5 && $i <> 6 && $i <> 7)
				$their_ans_str = substr($thedata['their_answer'], 3);
	            $worksheet->write($row, $col++, $their_ans_str, $format_row_left);
				$i++;
                }//letters
			$data=array();	
			$col=0;
			$row++;	
      }//end foreach record
}//end else we have records
//create file to download
$workbook->send("SS_".$course_title.date(YmdHis).".xls");
$workbook->close();	
}

//qi report
else if($_GET['action'] == 'qi_report_download'){
	global $wpdb;
	$qi_quiz_id=$_GET['qi_quizid'];
	set_include_path(get_include_path() . PATH_SEPARATOR . '/home/transcoalition/pear/php');
	require_once('Spreadsheet/Excel/Writer.php');
	$workbook = new Spreadsheet_Excel_Writer();
    define('CUSTOM_GRAY', 40);
	define('LIGHT_GRAY', 41);
	$workbook->setCustomColor(CUSTOM_GRAY, 152, 152, 152);
	$workbook->setCustomColor(LIGHT_GRAY, 221, 221, 221);

	$format_header_left =& $workbook->addFormat();
	$format_header_left =& $workbook->addFormat(
		array(	'Bold'		=> 1,
				'Border'	=> 1,
				'Align'		=> 'left'));

	$format_row_even_left =& $workbook->addFormat();
	$format_row_even_left =& $workbook->addFormat(
		array(	'FgColor'	=> '1',
				'Color'		=> 'black',
				'Align'		=> 'left',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

	$format_row_even_center =& $workbook->addFormat();
	$format_row_even_center =& $workbook->addFormat(
		array(	'FgColor'	=> '1',
				'Color'		=> 'black',
				'Align'		=> 'center',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));
	
	$format_row_odd_left =& $workbook->addFormat();
	$format_row_odd_left =& $workbook->addFormat(
		array(	'FgColor'	=> LIGHT_GRAY,
				'Color'		=> 'black',
				'Align'		=> 'left',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

	$format_row_odd_center =& $workbook->addFormat();
	$format_row_odd_center =& $workbook->addFormat(
		array(	'FgColor'	=> LIGHT_GRAY,
				'Color'		=> 'black',
				'Align'		=> 'center',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

	$worksheet =& $workbook->addWorksheet('My First Worksheet');
	
		// set column widths
	$worksheet->setColumn(0,0,20); // Last Name
	$worksheet->setColumn(1,1,20); // First Name
	$worksheet->setColumn(2,2,30); // E-mail
	$worksheet->setColumn(3,3,10); // Date
	$worksheet->setColumn(4,4,6); // State
	$worksheet->setColumn(5,5,30); // District
	$worksheet->setColumn(6,6,30); // Role
	$col = 6;
		//get header column data for spread sheet
	$quiz_categorys = $wpdb->get_results("SELECT distinct c.category_id, category_name FROM wp_wp_pro_quiz_question q, wp_wp_pro_quiz_category c 
										WHERE q.quiz_id = ".$qi_quiz_id." and q.category_id = c.category_id order by sort", OBJECT);
	$q_categories = array();
	$q_category_ids = array();

	foreach($quiz_categorys as $quiz_category){
		$category = $quiz_category->category_id;
		$q_category_ids[] = $quiz_category->category_id;
		$q_categories[] = $quiz_category->category_name;
		$category_questions = $wpdb->get_results("SELECT distinct q.id, question FROM wp_wp_pro_quiz_question q, wp_wp_pro_quiz_category c 
												WHERE q.quiz_id = ".$qi_quiz_id." and q.category_id = ".$category."  order by sort", OBJECT);
		foreach ($category_questions as $question) {
			$col++;
		}
		$col++;
		$worksheet->setColumn($col, $col, 13); // set widths of "Domain Score" columns
	}

	$worksheet->setColumn(6,100,4); // questions
	
	// build the header row
	$col = 0;
	$worksheet->write(1, $col++, "Last Name", $format_header_left);
	$worksheet->write(1, $col++, "First Name", $format_header_left);
	$worksheet->write(1, $col++, "E-mail", $format_header_left);
	$worksheet->write(1, $col++, "Date", $format_header_left);
	$worksheet->write(1, $col++, "State", $format_header_left);
	$worksheet->write(1, $col++, "District", $format_header_left);
	$worksheet->write(1, $col++, "Role", $format_header_left);
	

	$row = 2;

	// finish out the header rows with the domains and questions
	foreach ($quiz_categorys as $quiz_category) {
		$category = $quiz_category->category_id;
		$category_name = $quiz_category->category_name;
		$category_questions = $wpdb->get_results("SELECT distinct q.id, question FROM wp_wp_pro_quiz_question q, wp_wp_pro_quiz_category c 
												WHERE q.quiz_id =". $qi_quiz_id ." and q.category_id = ".$category." AND online = 1 order by sort", OBJECT);
		$domaincol = $col;
		// first print the domain text
		$worksheet->write(0, $col++, $category_name, $format_header_left);
		// now put in an empty cell for each question
		$col--;
		$questioncount = 0;
		foreach ($category_questions as $question) {
			$question_num = $questioncount+1;
			$worksheet->write(1, $col, "Q" . $question_num, $format_header_left);
			$worksheet->writeNote(1, $col++, strip_tags($question->question));
			$questioncount++;
		}
	$worksheet->write(1, $col++, 'Domain Score', $format_header_left);
	// now merge the cells above the domain's questions
	$y = $domaincol + $questioncount;
	$worksheet->mergeCells(0, $domaincol, 0, $y);
	}
	//initialize variables
	
	$user_id = "";
	$qi_date = "";
	$question = "";
	$question_category = "";
	$answer_data = "";
	$points = "";
	$user_meta = "";
	$user_data = "";
	$email = "";
	$last_name = "";
	$first_name = "";
	$state = "";
	$role = "";
	
    //get variables from url string
	$state = $_GET['qi_survey_state'];
	$roster_group = $_GET['qi_survey_group_rosters'];
	$qi_instance = $_GET['qi_survey_instance'];
	$start_date = $_GET['start_date'];
	$end_date = $_GET['end_date'];
	$group_by_user_id = "";
	if ($roster_group <> 'All'){
	$users = $wpdb->get_results($wpdb->prepare("SELECT object_id, term_order 
        FROM wp_term_relationships r, wp_term_taxonomy x 
        WHERE
		r.term_taxonomy_id = x.term_taxonomy_id 
		AND 
		term_order in (3,4) AND x.term_id = %d
		ORDER BY term_order",$roster_group), OBJECT);
		
		foreach($users as $user){
				$s_users .= $user->object_id.",";
		}
		$s_users = substr($s_users, 0, -1);
	
		$s_user_where = " AND user_id in(".$s_users.") ";
	}
	if ($start_date <> ""  && $end_date <> ""){
	$dateWhere = " AND FROM_UNIXTIME(create_time) >= '".$start_date."' AND FROM_UNIXTIME(create_time) <= '".$end_date."'" ;
	}
	if($qi_instance == 'most_recent'){
		$create_time = ' max(FROM_UNIXTIME(create_time)) as qi_date';
		$group_by_user_id = " group by user_id";
	}elseif($qi_instance == 'first_instance'){
		$create_time = ' min(FROM_UNIXTIME(create_time)) as qi_date';
		$group_by_user_id = " group by user_id";
	}else{//drop down select is all instances
		$create_time = ' FROM_UNIXTIME(create_time) as qi_date';
	}
	if ($state <> 'All'){
	$stateWhere =" and user_id in (select user_id from wp_usermeta where meta_key='state' and meta_value='". $state."') ";
    }
	//query to the database
	$report_users = $wpdb->get_results("SELECT distinct user_id, ".$create_time."
	FROM wp_wp_pro_quiz_category c, wp_wp_pro_quiz_statistic_ref r, wp_wp_pro_quiz_statistic s, wp_wp_pro_quiz_question q where q.category_id = c.category_id AND r.quiz_id =".$qi_quiz_id." 
	AND q.quiz_id = ".$qi_quiz_id." AND question_id = q.id AND r.statistic_ref_id = s.statistic_ref_id
	".$s_user_where . $dateWhere . $stateWhere . $group_by_user_id." order by user_id, q.sort", OBJECT);

	$loop_count = 0;
	foreach($report_users as $row_a){
		if ($row & 1) {
			$format_row_left = $format_row_even_left;
			$format_row_center = $format_row_even_center;
		} else {
			$format_row_left = $format_row_odd_left;
			$format_row_center = $format_row_odd_center;
		}
		$user_id = $row_a->user_id;
		$qi_date = $row_a->qi_date;
		//get user data 
		$user_meta = get_user_meta($user_id);
		$user_data = get_userdata($user_id);
		$email = $user_data->user_email;
		$last_name = $user_meta['last_name'][0];
		$first_name = $user_meta['first_name'][0];
		$state = $user_meta['state'][0];
		$role = $user_meta['transition_profile_role'][0];
		if($user_meta['school_district'][0] != ""){
				$district = $user_meta['school_district'][0];
		}
		else{
				$district = "Other";
		}
		$col=0;
		
		$worksheet->write($row, $col++, $last_name, $format_row_left);
		$worksheet->write($row, $col++, $first_name, $format_row_left);
		$worksheet->write($row, $col++, $email, $format_row_left);
		$worksheet->write($row, $col++, date("m-d-Y", strtotime($qi_date)), $format_row_left);
		$worksheet->write($row, $col++, $state, $format_row_center);
		$worksheet->write($row, $col++, $district, $format_row_left);
		$worksheet->write($row, $col++, $role, $format_row_left);
	
		foreach($q_category_ids as $cat_id){
		$report_result = $wpdb->get_results("SELECT s.points
				FROM wp_wp_pro_quiz_category c, wp_wp_pro_quiz_statistic_ref r, wp_wp_pro_quiz_statistic s, wp_wp_pro_quiz_question q where q.category_id = c.category_id AND r.quiz_id =".$qi_quiz_id." 
				AND FROM_UNIXTIME(create_time) = '".$qi_date."' AND user_id =".$user_id." AND q.category_id =".$cat_id." AND q.quiz_id = ".$qi_quiz_id." AND question_id = q.id AND r.statistic_ref_id = s.statistic_ref_id 
				order by user_id, q.sort", OBJECT);
			$count_questions = 0;
			$qi_total_points = 0;
			$average_score = 0;
			foreach($report_result as $row_b){
				$qi_answer = $row_b->points;
		
					$worksheet->write($row, $col++, $qi_answer, $format_row_center);
					//keep track of points for a total to figure out an average
					$qi_total_points += $qi_answer;
					$count_questions++;	
				}
			//calculate average score
			$average_score = ($qi_total_points/$count_questions);
			$average_score = round($average_score, 1);
			$worksheet->write($row, $col++, $average_score, $format_row_center);
			}
		$row++;
	}

	$workbook->send('qi_results_'.date(YmdHis).'.xls');
	$workbook->close();
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=survey_data.xls");
echo $xls;
}//end if qi report list

//the module report gets all records for pre and post or just pre if that is selected in a given date range
//some records are post tests with a pretest from a different date range
//others are pretests without post tests in the date range
//only show users data with pre and post tests in the date range
//as this loops through records it keys off the user id, (when the user id changes there is a new set of tests or a single test)
//if pre and post show data from post test, if pre show data from pre test

else if ($_GET['action'] == 'tc_report_download'){
global $wpdb;
set_include_path(get_include_path() . PATH_SEPARATOR . '/home/transcoalition/pear/php');
require_once('Spreadsheet/Excel/Writer.php');
$workbook = new Spreadsheet_Excel_Writer();
    define('CUSTOM_GRAY', 40);
	define('LIGHT_GRAY', 41);
	$workbook->setCustomColor(CUSTOM_GRAY, 152, 152, 152);
	$workbook->setCustomColor(LIGHT_GRAY, 221, 221, 221);

	$format_header_left =& $workbook->addFormat();
	$format_header_left =& $workbook->addFormat(
		array(	'Bold'		=> 1,
				'Border'	=> 1,
				'Align'		=> 'left'));

	$format_row_even_left =& $workbook->addFormat();
	$format_row_even_left =& $workbook->addFormat(
		array(	'FgColor'	=> '1',
				'Color'		=> 'black',
				'Align'		=> 'left',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

	$format_row_even_center =& $workbook->addFormat();
	$format_row_even_center =& $workbook->addFormat(
		array(	'FgColor'	=> '1',
				'Color'		=> 'black',
				'Align'		=> 'center',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));
	
	$format_row_odd_left =& $workbook->addFormat();
	$format_row_odd_left =& $workbook->addFormat(
		array(	'FgColor'	=> LIGHT_GRAY,
				'Color'		=> 'black',
				'Align'		=> 'left',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

	$format_row_odd_center =& $workbook->addFormat();
	$format_row_odd_center =& $workbook->addFormat(
		array(	'FgColor'	=> LIGHT_GRAY,
				'Color'		=> 'black',
				'Align'		=> 'center',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));
	$worksheet =& $workbook->addWorksheet('PrePostList');
		// set column widths
	$worksheet->setColumn(0,0,8); // User ID
	$worksheet->setColumn(1,1,50); // Last
	$worksheet->setColumn(1,1,50); // First
	$worksheet->setColumn(2,2,30); // Role
	$worksheet->setColumn(3,3,30); // Email
	$worksheet->setColumn(4,4,5); // State
	$worksheet->setColumn(4,4,5); // Prescore
	$worksheet->setColumn(4,4,15); // Predate
	$worksheet->setColumn(4,4,5); // PostScore
	$worksheet->setColumn(4,4,15); // PostDate\
	//Get data from post
	if (isset($_GET['module_list_data']) && is_numeric($_GET['module_list_data'])){
    $module_id = $_GET['module_list_data'];
	}
	$roster_group = $_GET['pre_post_group_rosters'];
	$state = $_GET['pre_post_state'];
	$type_test = $_GET['pre_post_type'];
	$start_date = $_GET['start_date'];
	$end_date = $_GET['end_date'];
	$bpv1endDate ="2016-09-05";
	$bpmov1endDate ="2016-09-18";
	$tav1endDate ="2016-12-28";
	//user selects to download report for all rosters
	if ($roster_group <> 'All'){
	$users = $wpdb->get_results($wpdb->prepare("SELECT object_id, term_order 
        FROM wp_term_relationships r, wp_term_taxonomy x 
        WHERE
		r.term_taxonomy_id = x.term_taxonomy_id 
		AND 
		term_order in (3,4) AND x.term_id = %d
		ORDER BY term_order",$roster_group), OBJECT);
		
		foreach($users as $user){
		$s_users .= $user->object_id.",";
		}
		$s_users = substr($s_users, 0, -1);
	
		$s_user_where = " AND user_id in(".$s_users.") ";
	}
	//user selects the data range
	if ($start_date <> ""  && $end_date <> ""){
	$dateWhere = " AND quiz_completed_date BETWEEN '". $start_date ."' AND  '". $end_date ."' " ;
	}
	//user selects a specific state for report
	if ($state <> 'All'){
	$stateWhere =" and user_id in (select user_id from wp_usermeta where meta_key='state' and meta_value='". $state."') ";
    }
	//get the quiz title from the course id
	$course_title= $wpdb->get_var("Select course_title from wp_wpcw_courses where course_id =". $module_id);
	//get the quiz_ids for pre
	if ($type_test =='pre'){
       if ($module_id == 4 && ($end_date <= $bpv1endDate)) {$quiz_ids='1';}
	   else if ($module_id == 17 && ($end_date <=$bpmov1endDate)) {$quiz_ids='43';}
	       else if ($module_id == 10 && ($end_date <= $tav1endDate)) {$quiz_ids='23';}
	            else{$quiz_ids=$wpdb->get_var("Select quiz_id from wp_wpcw_quizzes where parent_course_id =" . $module_id . " and quiz_title like '%pre%'"); }
	}
	else{
	//get the quiz ids for both pre and post
    if ($module_id == 4 && ($end_date <= $bpv1endDate)) {$quiz_ids='1,3';}	
		else if ($module_id == 17 && ($end_date <= $bpmov1endDate)) {$quiz_ids='43,44';}
		   else if ($module_id == 10 && ($end_date <= $tav1endDate)) {$quiz_ids='23,24';}
		     else{
			$quiz_id_rows=$wpdb->get_results("Select quiz_id from wp_wpcw_quizzes where parent_course_id = ".$module_id  ." and (quiz_title like '%pre%' || quiz_title like '%post%')", OBJECT);
			foreach ($quiz_id_rows as $item){
				$quiz_ids.=$item->quiz_id .",";
			}
			$quiz_ids =substr_replace($quiz_ids, "", -1);
             }
	}
	$prev_quizid =0;
	//get main rows about pre and post test based on form instructions
	$quiz_details=$wpdb->get_results("select distinct(quiz_id), user_id, quiz_data, quiz_correct_questions, quiz_question_total, quiz_completed_date as completed_date from wp_wpcw_user_progress_quizzes where quiz_id in (". $quiz_ids.")
    ". $s_user_where . $dateWhere .$stateWhere . " ORDER BY user_id, quiz_id", OBJECT);
	  $numrows = $wpdb->num_rows;
	//die($wpdb->last_query);
	$q1= $wpdb->last_query;
	if ($numrows == 0){
	die( "there were no records");
	}
	else{ 
	//write a column foreach question on the quiz
	 if ($module_id == 4 && ($end_date <= $bpv1endDate)) { 
	 $quiz_questions = $wpdb->get_results("SELECT question_question,question_correct_answer FROM `wp_wpcw_quizzes_questions` where parent_quiz_id = 1 order by question_order", OBJECT);}
	   elseif ($module_id == 17 && ($end_date <= $bpmov1endDate)) {$quiz_questions = $wpdb->get_results("SELECT question_question,question_correct_answer FROM `wp_wpcw_quizzes_questions` where parent_quiz_id = 43 order by question_order", OBJECT);}
	     	elseif ($module_id == 10 && ($end_date <= $tav1endDate)) {$quiz_questions = $wpdb->get_results("SELECT question_question,question_correct_answer FROM `wp_wpcw_quizzes_questions` where parent_quiz_id = 23 order by question_order", OBJECT);} 
                 else{$quiz_questions = $wpdb->get_results("SELECT question_question, question_correct_answer FROM `wp_wpcw_quizzes_questions` where parent_quiz_id in  (select min(quiz_id) from wp_wpcw_quizzes where parent_course_id = ". $module_id  ." and quiz_title like '%pre%') order by question_order", OBJECT);}
    //die ($q1 . "<BR>" . $wpdb->last_query ."<BR> end date " . $end_date . " variable " . $bpv1endDate);
	
	
	$numquestions = count($quiz_questions);
	$post_test_id=$wpdb->get_var("select quiz_id from wp_wpcw_quizzes where parent_course_id = ". $module_id  ."  and quiz_title like '%post%' ");
	$pre_test_id=$wpdb->get_var("select quiz_id from wp_wpcw_quizzes where parent_course_id = ". $module_id  ."  and quiz_title like '%pre%' ");
	$col = 0;
	$worksheet->write(1, $col++, "User ID", $format_header_left);
	$worksheet->write(1, $col++, "Last Name", $format_header_left);
	$worksheet->write(1, $col++, "First name", $format_header_left);
	$worksheet->write(1, $col++, "TC Role", $format_header_left);
	$worksheet->write(1, $col++, "Email", $format_header_left);
	$worksheet->write(1, $col++, "State", $format_header_left);
	$worksheet->write(1, $col++, "Pre score (" . $numquestions .")", $format_header_left);	
	$worksheet->write(1, $col++, "Pre Date", $format_header_left);
	$worksheet->write(1, $col++, "Post score (". $numquestions.")", $format_header_left);	
	$worksheet->write(1, $col++, "Post Date", $format_header_left);
	 
	foreach($quiz_questions as $question){
    $worksheet->write(1, $col++, "[".$question->question_correct_answer."] ".$question->question_question , $format_header_left);
    }
	//user and questions are now in the headings
		$col=0;
		$row=2;	
		$data=array();
		$prev_quiz_id=0;
	foreach($quiz_details as $q_id){
	$current_user_id = $q_id->user_id;
	$current_quiz_id = $q_id->quiz_id;
	$letters = range('A', 'Z');
    $their_ans_a = array();
    $correct_ans_a = array();
	
	if ($type_test <> "pre"){
	//check on user id, this record is the post test if they match, because the previous record was this users pre test, so add the pretest stuff to the post test and write the row
	if ($prev_user_id == $current_user_id){
			//echo "<br>quiz should be post " . $current_quiz_id . " and user is " . $current_user_id ." <b>now we would write a record</b>" .$pretestdate ." and score is ".$pretestcorrect ;
			$user_meta = get_user_meta($q_id->user_id);
			$user_data = get_userdata($q_id->user_id);
			//now write a row with post test plus pre test
			$worksheet->write($row, $col++, $q_id->user_id, $format_row_left);
			$worksheet->write($row, $col++, $user_meta['last_name'][0], $format_row_left);
			$worksheet->write($row, $col++,  $user_meta['first_name'][0], $format_row_left);
			$worksheet->write($row, $col++, $user_meta['transition_profile_role'][0], $format_row_left);
			$worksheet->write($row, $col++, $user_data->user_email, $format_row_left);
			$worksheet->write($row, $col++, $user_meta['state'][0], $format_row_left);
			$worksheet->write($row, $col++, $pretestcorrect, $format_row_left);	
			$worksheet->write($row, $col++, $pretestdate, $format_row_left);
			$worksheet->write($row, $col++, $q_id->quiz_correct_questions, $format_row_left);	
			$worksheet->write($row, $col++, $q_id->completed_date, $format_row_left);
			//load the post test answers from quiz data 
			$user_quiz->quiz_data = maybe_unserialize($q_id->quiz_data);
			
			foreach ($user_quiz->quiz_data as $thedata){
				$their_ans_str = substr($thedata['their_answer'], 0, 1);
				$correct_ans = substr($thedata['correct'], 0 ,1);
				$index = 1;
				for($l = 0; $l < sizeof($letters); $l++){
					if($letters[$l] == $their_ans_str){
					$their_ans = $index;
					if ($their_ans == 13){$their_ans = 4;}
					$worksheet->write($row, $col++, $their_ans, $format_row_left);
					}
					$index++;
				}//letters
			}//quiz_data
			$data=array();	
			$col=0;
			$row++;	
			//move on to the next one
			$prev_quiz_id = $current_quiz_id;
	        $prev_user_id = $current_user_id;
	}
	//on pretest , get the pre score and pre date, pretests list before of post tests so the user will have changed
	elseif ($prev_user_id <> $current_user_id && $current_quiz_id = $pre_test_id){
	    $pretestdate = $q_id->completed_date;
		$pretestcorrect = $q_id->quiz_correct_questions;
		//move on to the next one
		$prev_quiz_id = $current_quiz_id;
	    $prev_user_id = $current_user_id;
	}
	//move on to the next one these are post tests with no pre tesst
	$prev_quiz_id = $current_quiz_id;
	$prev_user_id = $current_user_id;
	}
	//they selected pre test only process records differently for pre test only, just output the list and add N/A for post test info, the answers will be from the pretest
	else{
			$user_meta = get_user_meta($q_id->user_id);
			$user_data = get_userdata($q_id->user_id);
			//now write a row with post test plus pre test
			$worksheet->write($row, $col++, $q_id->user_id, $format_row_left);
			$worksheet->write($row, $col++, $user_meta['last_name'][0], $format_row_left);
			$worksheet->write($row, $col++,  $user_meta['first_name'][0], $format_row_left);
			$worksheet->write($row, $col++, $user_meta['transition_profile_role'][0], $format_row_left);
			$worksheet->write($row, $col++, $user_data->user_email, $format_row_left);
			$worksheet->write($row, $col++, $user_meta['state'][0], $format_row_left);
			$worksheet->write($row, $col++,$q_id->quiz_correct_questions, $format_row_left);	
			$worksheet->write($row, $col++, $q_id->completed_date, $format_row_left);
			$worksheet->write($row, $col++,'N/A', $format_row_left);	
			$worksheet->write($row, $col++, 'N/A', $format_row_left);
			//load the pre test answers from quiz data 
			$user_quiz->quiz_data = maybe_unserialize($q_id->quiz_data);
			foreach ($user_quiz->quiz_data as $thedata){
				$their_ans_str = substr($thedata['their_answer'], 0, 1);
				$correct_ans = substr($thedata['correct'], 0 ,1);
				$index = 1;
				for($l = 0; $l<sizeOf($letters); $l++){
					if($letters[$l] == $their_ans_str){
					$their_ans = $index;
					if ($their_ans == 13){$their_ans = 4;}
					$worksheet->write($row, $col++, $their_ans, $format_row_left);
					}
					$index++;
				}//letters
			}//quiz_data
			$data=array();	
			$col=0;
			$row++;	
			//move on to the next one
			$prev_quiz_id = $current_quiz_id;
	        $prev_user_id = $current_user_id;
			
	}
}//end foreach record
}//end else we have records
//create file to download
$workbook->send("pptest_".$state."_".$course_title."_".date("m-d-Y-hiA").".xls");
$workbook->close();
}
else if ($_GET['action'] == 'tc_report_download_quick'){
   global $wpdb;
set_include_path(get_include_path() . PATH_SEPARATOR . '/home/transcoalition/pear/php');
require_once('Spreadsheet/Excel/Writer.php');
$workbook = new Spreadsheet_Excel_Writer();
    define('CUSTOM_GRAY', 40);
	define('LIGHT_GRAY', 41);
	$workbook->setCustomColor(CUSTOM_GRAY, 152, 152, 152);
	$workbook->setCustomColor(LIGHT_GRAY, 221, 221, 221);

	$format_header_left =& $workbook->addFormat();
	$format_header_left =& $workbook->addFormat(
		array(	'Bold'		=> 1,
				'Border'	=> 1,
				'Align'		=> 'left'));

	$format_row_even_left =& $workbook->addFormat();
	$format_row_even_left =& $workbook->addFormat(
		array(	'FgColor'	=> '1',
				'Color'		=> 'black',
				'Align'		=> 'left',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

	$format_row_even_center =& $workbook->addFormat();
	$format_row_even_center =& $workbook->addFormat(
		array(	'FgColor'	=> '1',
				'Color'		=> 'black',
				'Align'		=> 'center',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));
	
	$format_row_odd_left =& $workbook->addFormat();
	$format_row_odd_left =& $workbook->addFormat(
		array(	'FgColor'	=> LIGHT_GRAY,
				'Color'		=> 'black',
				'Align'		=> 'left',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

	$format_row_odd_center =& $workbook->addFormat();
	$format_row_odd_center =& $workbook->addFormat(
		array(	'FgColor'	=> LIGHT_GRAY,
				'Color'		=> 'black',
				'Align'		=> 'center',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));
	$worksheet =& $workbook->addWorksheet('PrePostList');
		// set column widths
	$worksheet->setColumn(0,0,8); // User ID
	$worksheet->setColumn(1,1,50); // Last
	$worksheet->setColumn(1,1,50); // First
	$worksheet->setColumn(2,2,30); // Role
	$worksheet->setColumn(3,3,30); // Email
	$worksheet->setColumn(4,4,5); // State
	$worksheet->setColumn(4,4,5); // Prescore
	$worksheet->setColumn(4,4,15); // Predate
	$worksheet->setColumn(4,4,5); // PostScore
	$worksheet->setColumn(4,4,15); // PostDate
	//Get data from post
	if (isset($_GET['module_list_data']) && is_numeric($_GET['module_list_data'])){
    $module_id = $_GET['module_list_data'];
	}
	$roster_group = $_GET['pre_post_group_rosters'];
	$state = $_GET['pre_post_state'];
	$type_test = $_GET['pre_post_type'];
	$start_date = $_GET['start_date'];
	$end_date = $_GET['end_date'];
	$bpv1endDate ="2016-09-05";
	$tav1endDate ="2016-12-28";
	//user selects to download report for all rosters
	if ($roster_group <> 'All'){
	$users = $wpdb->get_results($wpdb->prepare("SELECT object_id, term_order 
        FROM wp_term_relationships r, wp_term_taxonomy x 
        WHERE
		r.term_taxonomy_id = x.term_taxonomy_id 
		AND 
		term_order in (3,4) AND x.term_id = %d
		ORDER BY term_order",$roster_group), OBJECT);
		
		foreach($users as $user){
		$s_users .= $user->object_id.",";
		}
		$s_users = substr($s_users, 0, -1);
	
		$s_user_where = " AND user_id in(".$s_users.") ";
	}
	//user selects the data range
	if ($start_date <> ""  && $end_date <> ""){
	$dateWhere = " AND quiz_completed_date BETWEEN '". $start_date ."' AND  '". $end_date ."' " ;
	}
	//user selects a specific state for report
	if ($state <> 'All'){
	$stateWhere =" and user_id in (select user_id from wp_usermeta where meta_key='state' and meta_value='". $state."') ";
    }
	$prev_quizid =0;
	
	//get the quiz title from the course id
	$course_title= $wpdb->get_var("Select course_title from wp_wpcw_courses where course_id =". $module_id);
	//get the quiz_ids for pre
	if ($type_test =='pre'){
       if ($module_id == 4 && ($end_date <= $bpv1endDate)) {$quiz_ids='1';}
	     else if ($module_id == 10 && ($end_date <= $tav1endDate)) {$quiz_ids='23';}
	   else{
		 $quiz_ids=$wpdb->get_var("Select quiz_id from wp_wpcw_quizzes where parent_course_id =" . $module_id . " and quiz_title like '%pre%'");  
	   }
	}
	else{
	//get the quiz ids for both pre and post
        if ($module_id == 4 && ($end_date <= $bpv1endDate)) {$quiz_ids='1,3';}	
		   else if ($module_id == 10 && ($end_date <= $tav1endDate)) {$quiz_ids='23,24';}		   
		else{
			$quiz_id_rows=$wpdb->get_results("Select quiz_id from wp_wpcw_quizzes where parent_course_id = ".$module_id  ." and (quiz_title like '%pre%' || quiz_title like '%post%')", OBJECT);
			foreach ($quiz_id_rows as $item){
				$quiz_ids.=$item->quiz_id .",";
			}
			$quiz_ids =substr_replace($quiz_ids, "", -1);

		}
	}
//get main rows about pre and post test based on form instructions
	$quiz_details = $wpdb->get_results("select distinct(quiz_id), user_id, quiz_data, quiz_correct_questions, quiz_question_total, quiz_completed_date as completed_date from wp_wpcw_user_progress_quizzes where quiz_id in (". $quiz_ids.")
    ". $s_user_where . $dateWhere .$stateWhere . " ORDER BY user_id, quiz_id", OBJECT);
	  $numrows = $wpdb->num_rows;
	if ($numrows == 0){
	die( "there were no records");
	}
	else{ 
    $post_test_id=$wpdb->get_var("select quiz_id from wp_wpcw_quizzes where parent_course_id = ". $module_id  ."  and quiz_title like '%post%' ");
	$pre_test_id=$wpdb->get_var("select quiz_id from wp_wpcw_quizzes where parent_course_id = ". $module_id  ."  and quiz_title like '%pre%' ");
	$numquestions = $wpdb->get_var("select count(*) from wp_wpcw_quizzes_questions where parent_quiz_id =".$pre_test_id );
	$col = 0;
	$worksheet->write(1, $col++, "User ID", $format_header_left);
	$worksheet->write(1, $col++, "Last Name", $format_header_left);
	$worksheet->write(1, $col++, "First name", $format_header_left);
	$worksheet->write(1, $col++, "TC Role", $format_header_left);
	$worksheet->write(1, $col++, "Email", $format_header_left);
	$worksheet->write(1, $col++, "State", $format_header_left);
	$worksheet->write(1, $col++, "Pre score (" . $numquestions .")", $format_header_left);	
	$worksheet->write(1, $col++, "Pre Date", $format_header_left);
	$worksheet->write(1, $col++, "Post score (". $numquestions.")", $format_header_left);	
	$worksheet->write(1, $col++, "Post Date", $format_header_left);
    //user and questions are now in the headings
	$col=0;
	$row=2;	
	$prev_quiz_id=0;
	foreach($quiz_details as $q_id){
	$current_user_id = $q_id->user_id;
	$current_quiz_id = $q_id->quiz_id;
	if ($type_test <> "pre"){
	//check on user id this is the post test if they match, so add the pretest stuff to the post test
	if ($prev_user_id == $current_user_id){
			//echo "<br>quiz should be post " . $current_quiz_id . " and user is " . $current_user_id ." <b>now we would write a record</b>" .$pretestdate ." and score is ".$pretestcorrect ;
			$user_meta = get_user_meta($q_id->user_id);
			$user_data = get_userdata($q_id->user_id);
			//now write a row with post test plus pre test
			$worksheet->write($row, $col++, $q_id->user_id, $format_row_left);
			$worksheet->write($row, $col++, $user_meta['last_name'][0], $format_row_left);
			$worksheet->write($row, $col++,  $user_meta['first_name'][0], $format_row_left);
			$worksheet->write($row, $col++, $user_meta['transition_profile_role'][0], $format_row_left);
			$worksheet->write($row, $col++, $user_data->user_email, $format_row_left);
			$worksheet->write($row, $col++, $user_meta['state'][0], $format_row_left);
			$worksheet->write($row, $col++, $pretestcorrect, $format_row_left);	
			$worksheet->write($row, $col++, $pretestdate, $format_row_left);
			$worksheet->write($row, $col++, $q_id->quiz_correct_questions, $format_row_left);	
			$worksheet->write($row, $col++, $q_id->completed_date, $format_row_left);
			$col=0;
			$row++;	
			//move on to the next one
			$prev_quiz_id = $current_quiz_id;
	        $prev_user_id = $current_user_id;
		
	}
	elseif ($prev_user_id <> $current_user_id && $current_quiz_id = $pre_test_id){
	    $pretestdate = $q_id->completed_date;
		$pretestcorrect = $q_id->quiz_correct_questions;
		//move on to the next one
		$prev_quiz_id = $current_quiz_id;
	    $prev_user_id = $current_user_id;
	}
	//move on to the next one these are post tests with no pre tesst
	$prev_quiz_id = $current_quiz_id;
	$prev_user_id = $current_user_id;
	}
	//process records differently for pre test only
	else{
			$user_meta = get_user_meta($q_id->user_id);
			$user_data = get_userdata($q_id->user_id);
			//now write a row with post test plus pre test
			$worksheet->write($row, $col++, $q_id->user_id, $format_row_left);
			$worksheet->write($row, $col++, $user_meta['last_name'][0], $format_row_left);
			$worksheet->write($row, $col++,  $user_meta['first_name'][0], $format_row_left);
			$worksheet->write($row, $col++, $user_meta['transition_profile_role'][0], $format_row_left);
			$worksheet->write($row, $col++, $user_data->user_email, $format_row_left);
			$worksheet->write($row, $col++, $user_meta['state'][0], $format_row_left);
			$worksheet->write($row, $col++,$q_id->quiz_correct_questions, $format_row_left);	
			$worksheet->write($row, $col++, $q_id->completed_date, $format_row_left);
			$worksheet->write($row, $col++,'N/A', $format_row_left);	
			$worksheet->write($row, $col++, 'N/A', $format_row_left);
			$col=0;
			$row++;	
			//move on to the next one
			$prev_quiz_id = $current_quiz_id;
	        $prev_user_id = $current_user_id;
			
	}
}//end foreach record
}//end else we have records
//create file to download
$workbook->send("pptest_".$state."_".$course_title."_".date("m-d-Y-hiA").".xls");
$workbook->close();
}//end it is the quick version

else if ($_GET['action'] =='tc_report_captured_clicks'){
global $wpdb;
$stateWhere ="";
$dateWhere="";
$state = $_GET['pre_post_state'];
$category = $_GET['cat'];
if ($category > 0){
$inCategoryWhere =" AND ID in (Select object_id from wp_term_relationships where term_taxonomy_id = (select term_taxonomy_id from wp_term_taxonomy where term_id = ". $category ."))";
$inPostParentWhere = " AND post_parent in (Select object_id from wp_term_relationships where term_taxonomy_id = (select term_taxonomy_id from wp_term_taxonomy where term_id = ". $category ."))";
}
if ($state <> 'All'){$stateWhere = " and user_state='". $state ."'";}
$start_date=$_GET['start_date'];
$end_date=$_GET['end_date'];
//user selects the data range
if ($start_date <> ""  && $end_date <> ""){
$dateWhere = " AND (clicked_on_date >='". $start_date ."' AND clicked_on_date <= '". $end_date ."') " ;
}
set_include_path(get_include_path() . PATH_SEPARATOR . '/home/transcoalition/pear/php');
require_once('Spreadsheet/Excel/Writer.php');
$workbook = new Spreadsheet_Excel_Writer();
    define('CUSTOM_GRAY', 40);
	define('LIGHT_GRAY', 41);
	$workbook->setCustomColor(CUSTOM_GRAY, 152, 152, 152);
	$workbook->setCustomColor(LIGHT_GRAY, 221, 221, 221);

	$format_header_left =& $workbook->addFormat();
	$format_header_left =& $workbook->addFormat(
		array(	'Bold'		=> 1,
				'Border'	=> 1,
				'Align'		=> 'left'));

	$format_row_even_left =& $workbook->addFormat();
	$format_row_even_left =& $workbook->addFormat(
		array(	'FgColor'	=> '1',
				'Color'		=> 'black',
				'Align'		=> 'left',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

	$format_row_even_center =& $workbook->addFormat();
	$format_row_even_center =& $workbook->addFormat(
		array(	'FgColor'	=> '1',
				'Color'		=> 'black',
				'Align'		=> 'center',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));
	
	$format_row_odd_left =& $workbook->addFormat();
	$format_row_odd_left =& $workbook->addFormat(
		array(	'FgColor'	=> LIGHT_GRAY,
				'Color'		=> 'black',
				'Align'		=> 'left',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

	$format_row_odd_center =& $workbook->addFormat();
	$format_row_odd_center =& $workbook->addFormat(
		array(	'FgColor'	=> LIGHT_GRAY,
				'Color'		=> 'black',
				'Align'		=> 'center',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

$worksheet =& $workbook->addWorksheet('Captured Links Worksheet');
		// set column widths
	$worksheet->setColumn(0,0,20); // title
	$worksheet->setColumn(1,1,50); // url to file
	$worksheet->setColumn(1,1,50); // Source Page
	$worksheet->setColumn(1,1,5); // State
	$worksheet->setColumn(2,2,30); // Role
	$worksheet->setColumn(3,3,10); // Email
	$worksheet->setColumn(4,4,15); // Clicked on Date


$col = 0;
	$worksheet->write(1, $col++, "Resource Title", $format_header_left);
	$worksheet->write(1, $col++, "Link to file", $format_header_left);
	$worksheet->write(1, $col++, "Source Page", $format_header_left);
	$worksheet->write(1, $col++, "State", $format_header_left);
	$worksheet->write(1, $col++, "Role", $format_header_left);
	$worksheet->write(1, $col++, "Email", $format_header_left);
	$worksheet->write(1, $col++, "Clicked on Date", $format_header_left);

//do the query and get the content into the cells

$row = 2;
$col = 0;
$rows = $wpdb->get_results("select distinct(post_title) as theTitle, ID, from_page, user_id, user_email,user_role, user_state, clicked_on_date from wp_posts p, wp_captured_links l
where p.ID = simple_link_id ". $dateWhere . $stateWhere . $inCategoryWhere . "  GROUP BY post_title, user_email order by clicked_link_id", OBJECT);


foreach ($rows as $item){
//get the web address if we have one 	
$address=$wpdb->get_var("Select meta_value from wp_postmeta where meta_key='web_address' and  post_id = " . $item->ID);
if ($address == ""){
	$address="Not a simple link, nothing to look up.";
}
        $fromPage = str_replace('http://transitioncoalition.org/','',$item->from_page);
		$worksheet->write($row, $col++, $item->theTitle, $format_row_left);
		$worksheet->write($row, $col++, $address, $format_row_left);
		$worksheet->write($row, $col++, $fromPage, $format_row_left);
		$worksheet->write($row, $col++, $item->user_state, $format_row_left);
		$worksheet->write($row, $col++, $item->user_role, $format_row_left);
		$worksheet->write($row, $col++, $item->user_email, $format_row_left);
		$worksheet->write($row, $col++, $item->clicked_on_date, $format_row_left);	
$row++;	
$col=0;
}

//now look up posts that are categorised and get the simple links that go with them
if ($inCategoryWhere <> "" ){
$rows2 = $wpdb->get_results("select distinct(post_title) as theTitle, guid, ID, from_page, user_id, user_email,user_role, user_state, clicked_on_date 
      from wp_posts p, wp_captured_links l  
      where 
      p.ID = l.simple_link_id
      and 
      post_parent > 0 
      and 
      post_type='attachment' ". $dateWhere . $stateWhere . $inPostParentWhere .  " GROUP BY post_title, user_email order by clicked_link_id", OBJECT);
    foreach ($rows2 as $item){
	//get the web address if we have one 
			$fromPage = str_replace('http://transitioncoalition.org/','',$item->from_page);
			$worksheet->write($row, $col++, $item->theTitle, $format_row_left);
			$worksheet->write($row, $col++, $item->guid, $format_row_left);
			$worksheet->write($row, $col++, $fromPage, $format_row_left);
			$worksheet->write($row, $col++, $item->user_state, $format_row_left);
			$worksheet->write($row, $col++, $item->user_role, $format_row_left);
			$worksheet->write($row, $col++, $item->user_email, $format_row_left);
			$worksheet->write($row, $col++, $item->clicked_on_date, $format_row_left);
	$row++;	
	$col=0;	
	}
}
$workbook->send('clickedOnData_'.date(YmdHis).'.xls');
$workbook->close();	
} //end that it is a downloads counter
else if ($_GET['action'] == 'tc_report_lern_enrollment'){
global $wpdb;
$lerntopic=$_GET['lern_topic'];
set_include_path(get_include_path() . PATH_SEPARATOR . '/home/transcoalition/pear/php');
require_once('Spreadsheet/Excel/Writer.php');
$workbook = new Spreadsheet_Excel_Writer();
    define('CUSTOM_GRAY', 40);
	define('LIGHT_GRAY', 41);
	$workbook->setCustomColor(CUSTOM_GRAY, 152, 152, 152);
	$workbook->setCustomColor(LIGHT_GRAY, 221, 221, 221);

	$format_header_left =&$workbook->addFormat();
	$format_header_left =&$workbook->addFormat(
		array(	'Bold'		=> 1,
				'Border'	=> 1,
				'Align'		=> 'left'));

	$format_row_even_left =&$workbook->addFormat();
	$format_row_even_left =&$workbook->addFormat(
		array(	'FgColor'	=> '1',
				'Color'		=> 'black',
				'Align'		=> 'left',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

	$format_row_even_center =&$workbook->addFormat();
	$format_row_even_center =&$workbook->addFormat(
		array(	'FgColor'	=> '1',
				'Color'		=> 'black',
				'Align'		=> 'center',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));
	
	$format_row_odd_left =&$workbook->addFormat();
	$format_row_odd_left =&$workbook->addFormat(
		array(	'FgColor'	=> LIGHT_GRAY,
				'Color'		=> 'black',
				'Align'		=> 'left',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

	$format_row_odd_center =&$workbook->addFormat();
	$format_row_odd_center =&$workbook->addFormat(
		array(	'FgColor'	=> LIGHT_GRAY,
				'Color'		=> 'black',
				'Align'		=> 'center',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

$worksheet =&$workbook->addWorksheet('Lern Enrollment');
		// set column widths
	$worksheet->setColumn(0,0,50); // first name
	$worksheet->setColumn(1,1,50); // last name
	$worksheet->setColumn(2,2,50); // email
	$worksheet->setColumn(3,3,5); //  course progress
	$worksheet->setColumn(4,4,30); // on wait list y/n
$col = 0;
	$worksheet->write(1, $col++, "First Name", $format_header_left);
	$worksheet->write(1, $col++, "Last Name", $format_header_left);
	$worksheet->write(1, $col++, "Email", $format_header_left);
	$worksheet->write(1, $col++, "Course % Complete", $format_header_left);
	$worksheet->write(1, $col++, "Waitlist User", $format_header_left);

//do the query and get the content into the cells

$row = 2;
$col = 0;
$lernusers = $wpdb->get_results("Select user_id,course_progress from wp_wpcw_user_courses where course_id =" . $lerntopic, OBJECT);
foreach ($lernusers as $lernuser){
$waitlist_user='N';
//get the user email, first name last name and % done
$user_id = $lernuser->user_id;
$progress= $lernuser->course_progress ."%";
$user_info= get_userdata($user_id);
$useremail=$user_info->user_email;
$userfirstname=$user_info->first_name;
$userlastname=$user_info->last_name;
//check email against the email addresses on the waitlist table
$exists = $wpdb->get_var("select waitlist_item_id from wp_wpcw_waitinglist where user_email ='" .$useremail."' and course_id = ". $lerntopic );
if ($exists > 0){$waitlist_user= 'Y';}
		$worksheet->write($row, $col++, $userfirstname, $format_row_left);
		$worksheet->write($row, $col++, $userlastname, $format_row_left);
		$worksheet->write($row, $col++, $useremail, $format_row_left);
		$worksheet->write($row, $col++, $progress, $format_row_left);
		$worksheet->write($row, $col++, $waitlist_user, $format_row_left);
$row++;	
$col=0;
}
$workbook->send('lernEnrollment_'.date(YmdHis).'.xls');
$workbook->close();	
}
else if ($_GET['action'] == 'tc_report_lern_waitlist'){
global $wpdb;
$lerntopic=$_GET['lern_topic'];
set_include_path(get_include_path() . PATH_SEPARATOR . '/home/transcoalition/pear/php');
require_once('Spreadsheet/Excel/Writer.php');
$workbook = new Spreadsheet_Excel_Writer();
    define('CUSTOM_GRAY', 40);
	define('LIGHT_GRAY', 41);
	$workbook->setCustomColor(CUSTOM_GRAY, 152, 152, 152);
	$workbook->setCustomColor(LIGHT_GRAY, 221, 221, 221);

	$format_header_left =&$workbook->addFormat();
	$format_header_left =&$workbook->addFormat(
		array(	'Bold'		=> 1,
				'Border'	=> 1,
				'Align'		=> 'left'));

	$format_row_even_left =&$workbook->addFormat();
	$format_row_even_left =&$workbook->addFormat(
		array(	'FgColor'	=> '1',
				'Color'		=> 'black',
				'Align'		=> 'left',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

	$format_row_even_center =&$workbook->addFormat();
	$format_row_even_center =&$workbook->addFormat(
		array(	'FgColor'	=> '1',
				'Color'		=> 'black',
				'Align'		=> 'center',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));
	
	$format_row_odd_left =&$workbook->addFormat();
	$format_row_odd_left =&$workbook->addFormat(
		array(	'FgColor'	=> LIGHT_GRAY,
				'Color'		=> 'black',
				'Align'		=> 'left',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

	$format_row_odd_center =&$workbook->addFormat();
	$format_row_odd_center =&$workbook->addFormat(
		array(	'FgColor'	=> LIGHT_GRAY,
				'Color'		=> 'black',
				'Align'		=> 'center',
				'Border'	=> 1,
				'BorderColor' => CUSTOM_GRAY));

$worksheet =&$workbook->addWorksheet('Lern Enrollment');
		// set column widths
	$worksheet->setColumn(0,0,50); // first name
	$worksheet->setColumn(1,1,50); // last name
	$worksheet->setColumn(2,2,50); // email
	$worksheet->setColumn(3,3,5); //  course progress
	$worksheet->setColumn(4,4,30); // on wait list y/n
$col = 0;
	$worksheet->write(1, $col++, "Name", $format_header_left);
	$worksheet->write(1, $col++, "Email", $format_header_left);
	$worksheet->write(1, $col++, "Date added", $format_header_left);
	$worksheet->write(1, $col++, "Enrolled", $format_header_left);

//do the query and get the content into the cells

$row = 2;
$col = 0;
$lernwaiters = $wpdb->get_results("Select * from wp_wpcw_waitinglist where course_id  =" . $lerntopic, OBJECT);
foreach ($lernwaiters as $lernwaiter){
//get the waitlist information
$enrolled='N';
$user_email = $lernwaiter->user_email;
$name = $lernwaiter->user_name;
$dateadded=$lernwaiter->entry_date;
//check email against the email addresses on the waitlist table
$exists = $wpdb->get_var("select user_id from wp_wpcw_user_courses where course_id = ". $lerntopic." and user_id in (select ID from wp_users where user_email='".$user_email."')" );
if ($exists > 0){$enrolled= 'Y';}
		$worksheet->write($row, $col++, $name, $format_row_left);
		$worksheet->write($row, $col++, $user_email, $format_row_left);
		$worksheet->write($row, $col++, $dateadded, $format_row_left);
		$worksheet->write($row, $col++, $enrolled, $format_row_left);
$row++;	
$col=0;
}
$workbook->send('lernEnrollment_'.date(YmdHis).'.xls');
$workbook->close();	
}


?>