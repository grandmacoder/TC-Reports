<?php


function add_qi_survey_page(){

if(!current_user_can('manage_options')){
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}
	$tcReportsList = new TC_Reports_List_Table();
    $tcReportsList->prepare_items();
	echo $filterState."<br>";
  
        ?> <div class='wrap'>
                <div id='icon-users' class='icon32'></div>
                <h2>QI Survey Page</h2>
				<div>
			<select name="qi_table_state" id="qi_table_state">
									<option value="">Select a state</option>
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
							</select>
							</div>
           <?php  $tcReportsList->display(); ?>
            </div>
<?php
}
// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class TC_Reports_List_Table extends WP_List_Table
{
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array    array('Name'=>$firstname." ".$lastname, 'Email'=>$email, 'Date'=>$qi_date, 'State'=>$state, 'District'=>$district, 'Role'=>$role);
     */
    public function get_columns()
    {
        $columns = array(
            'Name'          => 'NAME',  
            'Date'		=> 'DATE',
			'Email'       => 'EMAIL',
            'State'        => 'STATE',
            'District'    => 'DISTRICT',
            'Role'      => 'ROLE'
        );
        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array('Name' => array('Name', false), 'Email' => array('Email', false), 'Date' => array('Date', false), 'State' => array('State', false));
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {
					
		global $wpdb;
		$filterState = $_GET['filterState'];
		
		$qi_users = $wpdb->get_results($wpdb->prepare("SELECT r.statistic_ref_id, r.user_id, u.user_email, FROM_UNIXTIME(create_time) as qi_date 
										FROM wp_wp_pro_quiz_statistic_ref as r, wp_users as u 
										WHERE r.user_id = u.ID AND quiz_id = %d", 5), OBJECT);
		$qi_array = array();
		foreach($qi_users as $qi_user){
			$firstname = get_user_meta($qi_user->user_id, 'first_name', true);
			$lastname = get_user_meta($qi_user->user_id, 'last_name', true);
			$email = $qi_user->user_email;
			$qi_date = $qi_user->qi_date;
			$state = get_user_meta($qi_user->user_id, 'state', true);
			$role = get_user_meta($qi_user->user_id, 'transition_profile_role', true);
			$school_district = get_user_meta($qi_user->user_id, 'school_district', true);
			if($school_district==""){
				$school_district = "No District";
			}
			//load up the array of with arrays
			if($filterState <> ""){
				if($filterState == $state){
				$qi_array[] = array('Name'=>$firstname." ".$lastname."<br> <a href='/qi-results/?surveyref=".$qi_user->statistic_ref_id."&qi_user=".$qi_user->user_id."' target=blank>QI survey</a>", 'Email'=>$email, 'Date'=>$qi_date, 'State'=>$state, 'District'=>$school_district, 'Role'=>$role);				
				}
			}else{
			$qi_array[] = array('Name'=>$firstname." ".$lastname."<br> <a href='/qi-results/?surveyref=".$qi_user->statistic_ref_id."&qi_user=".$qi_user->user_id."' target=blank>QI survey</a>", 'Email'=>$email, 'Date'=>$qi_date, 'State'=>$state, 'District'=>$school_district, 'Role'=>$role);
			}
		}
		
		return $qi_array;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'Name':
            case 'Email':
            case 'Date':
            case 'State':
            case 'District':
            case 'Role':
                return $item[ $column_name ];

            default:
                return print_r( $item, true ) ;
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'title';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }


        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }
}
?>