<?php
/*
 * Plugin Name: TwitterRSS Stats
 * Version: 1.0
 * Plugin URI: http://webdesignergeeks.com/
 * Description: Twitter & RSS Social Stats widget  <a href="http://webdesignergeeks.com/">tutorial</a>.
 * Author: Ajay Patel
 * Author URI: http://ajayy.com/
 */
?>
<?php
function addHeaderCode() {
	echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/twitter-rss-social-stats/css/style.css" />' . "\n";
}
function my_plugin_create_table()
{
	global $wpdb; 
	if($wpdb->get_var("show tables like TRR_Stats") != 'TRR_Stats') 
	{
		$sql = "CREATE TABLE TRR_Stats (
		id mediumint(9) NOT NULL,
		rss_email tinytext NOT NULL,
		twitter tinytext NOT NULL,
		rss tinytext NOT NULL,
		UNIQUE KEY id (id)
		);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}
register_activation_hook( __FILE__, 'my_plugin_create_table' );
class TRRWidget extends WP_Widget
{
	function TRRWidget(){
		$widget_ops = array('classname' => 'widget_hello_world', 'description' => __( "Twitter & RSS Social Stats") );
		$control_ops = array('width' => 300, 'height' => 300);
		$this->WP_Widget('helloworld', __('Twitter & RSS Social Stats'), $widget_ops, $control_ops);
	}	
	function widget($args, $instance){
		extract($args);
		$title = apply_filters('widget_title', $instance['title'] );
		$rss_email = empty($instance['rss_email']) ? 'webdesignergeeks' : $instance['rss_email'];
		$twitter = empty($instance['twitter']) ? 'webdesignergeek' : $instance['twitter'];
		$rss = empty($instance['rss']) ? 'webdesignergeeks' : $instance['rss'];
		
		/* Before widget (defined by themes). */
		echo $before_widget;
		/* Title of widget (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;
		
		global $wpdb;
		$item_info = $wpdb->get_row("SELECT * FROM TRR_Stats WHERE id=1;");
		$rss_email_f = $item_info->rss_email;
		/*Solve RSS Date Prob*/
		$Today=date('Y-m-d');
		$NewDate=Date('Y-m-d', strtotime("-2 days"));//minus 2 days to date
				
		$url = file_get_contents('https://feedburner.google.com/api/awareness/1.0/GetFeedData?uri='.$rss_email.'&dates='.$NewDate);
		preg_match( '/circulation="(\d+)"/', $url, $matches );
		if ( $matches[1] )
		$rss_f = $matches[1] . "+ Subscribers";
		else
		$rss_f = "0";
		
		$twit = file_get_contents('http://twitter.com/users/show/'.$twitter.'.xml');
		preg_match( '/\<followers_count\>(\d+)\<\/followers_count\>/', $twit, $matches );
		if ( $matches[1] )
		$twitter_f = $matches[1] . "+ Followers";
		else
		$twitter_f = "0";
		
		echo '
			<div class="sidebarContainer" id="sidebarSubscribe">
			<a target="_blank" href="http://twitter.com/'.$twitter.'" class="subscribeSidebarBox" id="followTwitter">
            	<span class="icon"><img src="'.get_bloginfo('url').'/wp-content/plugins/twitter-rss-social-stats/img/twitter.png" alt="Twitter" /></span>
                <span class="title">Follow Us on Twitter</span>
                <span class="count">'.$twitter_f.'</span>
            </a>
        	<a target="_blank" href="http://feeds.feedburner.com/'.$rss.'" class="subscribeSidebarBox" id="subscribeRSS">
            	<span class="icon"><img src="'.get_bloginfo('url').'/wp-content/plugins/twitter-rss-social-stats/img/rss_feed.png" alt="RSS"/></span>
                <span class="title">Subscribe to our RSS feed</span>
                <span class="count">'.$rss_f.'</span>
            </a>
            <a target="_blank" href="http://feedburner.google.com/fb/a/mailverify?uri='.$rss_email_f.'" class="subscribeSidebarBox" id="subscribeEmail">
            	<span class="icon"><img src="'.get_bloginfo('url').'/wp-content/plugins/twitter-rss-social-stats/img/rss_email.png" alt="rss_email" /></span>
                <span class="title">Subscribe for updates via</span>
                <span class="count">EMAIL</span>
            </a>
        </div>';		
		echo $after_widget;
	}
	/*Plugin Update */
	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['rss_email'] = strip_tags(stripslashes($new_instance['rss_email']));
		$instance['twitter'] = strip_tags(stripslashes($new_instance['twitter']));
		$instance['rss'] = strip_tags(stripslashes($new_instance['rss']));		
		global $wpdb;
			$wpdb->insert( 'TRR_Stats', array(
			'id'	=> 1,
			'rss_email' => $instance['rss_email'], 
			'twitter' => $instance['twitter'],
			'rss' => $instance['rss']
			) 
		);		
		global $wpdb;
			$wpdb->update( 'TRR_Stats', 
			array( 
				'rss_email' => $instance['rss_email'], 
				'twitter' => $instance['twitter'],
				'rss' => $instance['rss']
			),
			array(
				'id' => 1
			) 
		);	
		return $instance;
	}	
	function form($instance){
		$instance = wp_parse_args( (array) $instance, array('rss_email'=>'webdesignergeeks', 'twitter'=>'webdesignergeek', 'rss'=>'webdesignergeeks') );		
		$rss_email = htmlspecialchars($instance['rss_email']);
		$twitter = htmlspecialchars($instance['twitter']);
		$rss = htmlspecialchars($instance['rss']);	
		?>
			<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
			</p>
		<?php
		echo '<p><label for="' . $this->get_field_name('twitter') . '">' . ('Twitter:') . ' <input id="' . $this->get_field_id('twitter') . '" name="' . $this->get_field_name('twitter') . '" type="text" value="' . $twitter . '" style="width:100%;"/></label></p>';
		echo '<p>i.e: webdesignergeeks</p>';
		echo '<p><label for="' . $this->get_field_name('rss') . '">' . __('Rss:') . ' <input style="width:100%;" id="' . $this->get_field_id('rss') . '" name="' . $this->get_field_name('rss') . '" type="text" value="' . $rss . '" /></label></p>';
		echo '<p>i.e: webdesignergeeks</p>';
		echo '<p><label for="' . $this->get_field_name('rss_email') . '">' . ('Rss Email:') . ' <input style="width:100%;" id="' . $this->get_field_id('rss_email') . '" name="' . $this->get_field_name('rss_email') . '" type="text" value="' . $rss_email . '" /></label></p>';
		echo '<p>i.e: webdesignergeeks</p>';	
	}
}
function TRR_Widget() {
	register_widget('TRRWidget');
}	
add_action('widgets_init', 'TRR_Widget');
add_action('wp_head', 'addHeaderCode');
?>