<?php
function add_tc_file_attachments(){

if(!current_user_can('manage_options')){
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}
    global $wpdb;
    ?>
	<div class='wrap'>
    <div id='icon-users' class='icon32'></div>
    <h2>Get file attachments from forum replies</h2>
	<form name="TCFileAttachments" id="TCFileAttachments">
	<span style="font-size: 16px; padding-right: 49px;"> <b>Select Forum:</b> </span>
	<select name="forum_list" id="forum_list">
	<?php
    $query = new WP_Query(array(
    'post_type' => 'forum',
    'post_status' => 'publish',
	'posts_per_page' => -1,
));
     while ($query->have_posts()) {
      $query->the_post();
	  echo "<option value='".get_the_ID()."'>".get_the_title()."</option>";
   }
wp_reset_query();
?>
<input TYPE="button" name="btnsubmitgetattachments" id="btnsubmitgetattachments" value="Get Attachments">
 </form>

 <div id="fileAttachmentList"><p>File attachments will show up here</p></div>
 
 
 </div>
<?php
}
?>
