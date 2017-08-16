<?php
/*
Plugin Name: TC reports
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Version: 1.0
Description: Plugin for Transition Coalition Research Learning Team for reports on user activity. The plugin is designed 
to allow admin user to import reports into an excel document. The reports are the QI Survey and Pre/Post test for member users that are a part of 
Transition Coalition. The reports are used by the Transition Coalition coordinators in order to review accomplishments for member users. 
Author: Greg Carlson
*/
//define the location of the plugin directory
define( 'transcoal-reports', dirname(__FILE__).'/' );  

//include the files for plugin
include "transcoal-qi-survey.php";
include "transcoal-prepost-test.php";
include "transcoal-prepost-test-quick.php";
include "transcoal-qi-survey-list.php";
include "transcoal-satisfaction-survey.php";
include "transcoal-mo-satisfaction-survey.php";
include "transcoal-capture_clicks.php";
include "transcoal-file-attachments.php";
include "transcoal-lern.php";
include "transcoal-lern-waitlist.php";
include "transcoal-lern-duplicate.php";
if(is_admin())
{
    new TC_Reports_table();
}

class TC_Reports_table{
	
	
		/**
     * Constructor will create the menu item
     */
    public function __construct()
    {
		add_action( 'admin_init', array($this, 'my_plugin_admin_init') );
		add_action('admin_menu',  array($this, 'tc_reports_menu' ));	
    }
	
	public function my_plugin_admin_init(){
		wp_register_script( 'tc-reports-plugin-script',  plugins_url('/js/tc_reports.js',__FILE__) );
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
	}

		public function tc_reports_menu(){
			add_menu_page( 'TC Reports', 'TC Reports', 'manage_options', 'tc-reports', '', '', 16 );
			//add_submenu_page( 'tc-reports', 'QI survey', 'QI survey', 'manage_options', 'tc-reports', 'add_qi_survey_page' );
			add_submenu_page( 'tc-reports', 'QI Survey', 'QI Survey', 'manage_options', 'tc-qi-report', 'add_qi_survey_list' );	
			add_submenu_page( 'tc-reports', 'Satisfaction Survey', 'Satisfaction Survey', 'manage_options', 'tc-satisfaction-report', 'add_satisfaction_survey_list' );	
			add_submenu_page( 'tc-reports', 'Mo Satisfaction Survey', 'Mo Satisfaction Survey', 'manage_options', 'tc-mo-satisfaction-report', 'add_mo_satisfaction_survey_list' );	
			add_submenu_page( 'tc-reports', 'Pre/Post test', 'Pre/Post test', 'manage_options', 'tc-test-report', 'add_pre_post_test' );
			add_submenu_page( 'tc-reports', 'Quick Pre/Post test', 'Quick Pre/Post test', 'manage_options', 'tc-test-report-quick', 'add_pre_post_test_quick' );
			add_submenu_page( 'tc-reports', 'Captured Clicks', 'Captured Clicks', 'manage_options', 'tc-captured-clicks', 'add_tc_captured_clicks' );
            add_submenu_page( 'tc-reports', 'File Attachments', 'File Attachments', 'manage_options', 'tc-file-attachments', 'add_tc_file_attachments' );	
			add_submenu_page( 'tc-reports', 'LERN Enrollment', 'LERN Enrollment', 'manage_options', 'tc-lern-enrollment', 'tc_lern_enrollment' );
			add_submenu_page( 'tc-reports', 'LERN Waiting List', 'LERN Waiting List', 'manage_options', 'tc-lern-waitlist', 'tc_lern_waitlist' );
			add_submenu_page( 'tc-reports', 'Duplicate a LERN', 'Duplicate a LERN', 'manage_options', 'tc-lern-duplicate', 'tc_lern_duplicate' );			
			add_action('admin_init', array($this,'my_plugin_admin_scripts'));
		}
		
	public function my_plugin_admin_scripts() {
        /* Link already registered script to a page */
        wp_enqueue_script( 'tc-reports-plugin-script' );
		
    }
		
		

}
?>