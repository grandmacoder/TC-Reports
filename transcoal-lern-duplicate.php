<?php
function tc_lern_duplicate(){

if(!current_user_can('manage_options')){
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}
    global $wpdb;
    ?>
<div class='wrap'>
    <div id='icon-users' class='icon32'></div>
    <h2>LERN copier</h2>
	<form name="LernDuplicate" id="LernDuplicate">
	<span style="font-size: 16px; padding-right: 68px;"> <b>Choose the LERN topic:</b> </span>
	<select name="lern_topic" id="lern_topic">
<?php
$topics = $wpdb->get_results("select course_title, c.course_id from wp_wpcw_courses c, wp_wpcw_course_extras e where e.course_id = c.course_id and course_type ='LERN'");
foreach ($topics as $topic){
echo "<option value=". $topic->course_id.">". $topic->course_title."</option>";
}
?>
</select><br>
<br>
 <INPUT TYPE="button" name="btnsubmitlernduplicate" id="btnsubmitlernduplicate" value="Make a Copy"><div id="messageareaduplicate" ></div>
 </form>
</div>
<?php
}
?>
