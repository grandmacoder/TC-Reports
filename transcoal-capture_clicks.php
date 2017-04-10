<?php
function add_tc_captured_clicks() {

if(!current_user_can('manage_options')){
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}
?>
	<div class='wrap'>
                <div id='icon-users' class='icon32'></div>
                <h2>Captured Link Clicks Report</h2>
	<form name="CapturedLinkClicks" id="CapturedLinkClicks">
	<span style="font-size: 16px; padding-right: 68px;"> <b>State:</b> </span>
	<select name="pre_post_state" id="pre_post_state">
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
 <span style="font-size: 16px; padding-right: 29px;"> <b>Start Date:</b> </span>	
 <input type="text" class="custom_date" name="start_date" /><br>
 <span style="font-size: 16px; padding-right: 38px;"> <b>End Date:</b> </span>	
 <input type="text" class="custom_date" name="end_date" /><br>
 <?php
$select = wp_dropdown_categories('taxonomy=post_tag&show_option_none=Select tag&show_count=0&orderby=name&echo=0');
echo $select;
?>
<br>
 <INPUT TYPE="button" name="btnsubmitcaptureclicks" id="btnsubmitcaptureclicks" value="Download File"><div id="messageareaprepost" ></div>
 </form>
</div>
<?php
}
?>
