<?php
/*
Plugin Name: Simple Archive
Plugin URI: https://github.com/dywp/Simple-Archive-Wordpress-Shortcode-Plugin
Description: Adds a shortcode to display your archive by year and month and listing the last 30 posts below
Author: Dress Your Wordpress
Author URI: http://dressyourwp.com
Version: 0.1
License: GPLv2
*/

/*
This program is free software; you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by 
the Free Software Foundation; version 2 of the License.

This program is distributed in the hope that it will be useful, 
but WITHOUT ANY WARRANTY; without even the implied warranty of 
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
GNU General Public License for more details. 

You should have received a copy of the GNU General Public License 
along with this program; if not, write to the Free Software 
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
*/



if ( !function_exists( 'add_action' ) ) {
	echo "This page cannot be called directly.";
	exit;
}


class wcsinple_archive{
    public function __construct(){
        if(is_admin()){
	    add_action('admin_menu', array($this, 'add_plugin_page'));
	    add_action('admin_init', array($this, 'page_init'));
	}
    }
	
    public function add_plugin_page(){
        // This page will be under "Settings"
	add_options_page('Settings Admin', 'Sinple Archive Settings', 'manage_options', 'sinple_archive-setting-admin', array($this, 'create_admin_page'));
    }

    public function create_admin_page(){
        ?>
	<div class="wrap">
	    <?php screen_icon(); ?>
	    <h2>Settings</h2>			
	    <form method="post" action="options.php">
	        <?php
                    // This prints out all hidden setting fields
		    settings_fields('sinple_archive_option_group');	
		    do_settings_sections('sinple_archive-setting-admin');
		?>
	        <?php submit_button(); ?>
	    </form>
	</div>
	<?php
    }
	
    public function page_init(){		
	register_setting('sinple_archive_option_group', 'input_css_sinple_edit', array($this, 'check_ID'));
		
        add_settings_section(
	    'setting_section_id',
	    'Setting',
	    array($this, 'print_section_info'),
	    'sinple_archive-setting-admin'
	);	
		
	add_settings_field(
	    'css_code', 
	    'CSS', 
	    array($this, 'create_an_id_field'), 
	    'sinple_archive-setting-admin',
	    'setting_section_id'			
	);		
    }
	
    public function check_ID($input){
        
	    $mid = $input['css_code'];			
	    if(get_option('sinple_archive_css_code') === FALSE){
		add_option('sinple_archive_css_code', $mid);
	    }else{
		update_option('sinple_archive_css_code', $mid);
	   }
	return $mid;
    }
	
    public function print_section_info(){
    print <<< EOT
    <h2>Hi</h2>
    <p>To add the archive to a page use this shortcode:<br />
    
    [new_archive number_posts="2"]</p>
    <p>number_posts will determine how many latest post to show.</p>
    <p>You can edit the css below:</p>
    
EOT;
    
	
    }
	
    public function create_an_id_field(){
    
   $css = '/*archive page*/

.date_archive, .last_30 {list-style: none; margin: 0; padding: 0}
.date_archive li { margin: 10px 0}
.date_archive li:after {
    content: ".";
    display: block;
    height: 0;
    clear: both;
    visibility: hidden;
}
.date_archive li a {float: left; margin: 0 5px;}
.date_archive li span {font-weight: bold}
.aligncenter {display: block; margin: 0 auto; text-align: center}  
   ';
   $saved_option = get_option('sinple_archive_css_code');
  if ($saved_option != $css and !empty($saved_option)) { $css_code =  $saved_option ; } else { $css_code =  $css; }
        ?>
        <textarea   id="input_css_sinple_edit" class="large-text code" rows="15" name="input_css_sinple_edit[css_code]" ><?php echo $css_code ?></textarea>
      <?php
    }
}

$wcsinple_archive = new wcsinple_archive();


/* Add Csutom Short Code*/

function _rd_archive_shortcode($atts){
extract( shortcode_atts( array(
		'number_posts' => '30'
	), $atts ) );


	$content .= '<h2>'.__('By Month','text-domain').'</h2>';
	$content .= '<ul class="date_archive">';

global $wpdb;
$years = $wpdb->get_col("SELECT DISTINCT YEAR(post_date) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date  DESC");
foreach($years as $year) :

	$content .= '<li class="years"><span><a href="'. get_year_link($year).' ">'. $year.'</a></span>';

		
$months = $wpdb->get_col("SELECT DISTINCT MONTH(post_date) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post' AND YEAR(post_date) = '".$year."' ORDER BY post_date ASC ");
			foreach($months as $month) :
		$new_month = str_pad($month, 2, '0', STR_PAD_LEFT);
		$timestamp = date('M', mktime(0,0,0,$new_month,1));
			$content .= '<a href="'.get_month_link($year, $month).'">'.$timestamp .'</a>';
			 endforeach;
		$content .= '</li>';
endforeach; 
$content .= '</ul>';
	
	$content .= '<h2>'.sprintf( __( 'The latest %1$s Posts', 'text-domain' ), $number_posts).'</h2><ul class="last_30">';

	$recent_posts = wp_get_recent_posts('numberposts='.$number_posts);
	foreach( $recent_posts as $recent ){
		$content .= '<li><a href="' . get_permalink($recent["ID"]) . '" title="Look '.esc_attr($recent["post_title"]).'" >' .   $recent["post_title"].'</a> </li> ';
	}

$content .= '</ul>';

return $content;	
}

add_shortcode('new_archive' ,'_rd_archive_shortcode');


/*Add css*/




/**
     * Register with hook 'wp_enqueue_scripts', which can be used for front end CSS and JavaScript
     */
    add_action( 'wp_enqueue_scripts', 'simple_archive_css_add_my_stylesheet' );

    /**
     * Enqueue plugin style-file
     */
    function simple_archive_css_add_my_stylesheet() {
        // Respects SSL, Style.css is relative to the current file
        wp_register_style( 'simple_archive_css', plugins_url('css/simple_archive_css.php', __FILE__) );
        wp_enqueue_style( 'simple_archive_css' );
    }




?>