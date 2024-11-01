<?php
/*
Plugin Name: Moderator Plugin
Plugin URI: http://aheadzen.com/
Description: The plugin is like post content checker. Plugin will moderate the front end added posts content mean post added by users not from wp-admin. <br />Get the plugin <a href="options-general.php?page=moderator" target="_blank"><b>Moderator Settings >></b></a>
Author: Aheadzen Team
Version: 1.0.5
Author URI: http://aheadzen.com/

Copyright: © 2014-2015 ASK-ORACLE.COM
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/
if(is_admin()){
	include(dirname(__FILE__).'/admin_settings.php');
}

add_action('admin_menu',array('ModeratorAdminClass','aheadzen_admin_menu'));
add_action('init', array( 'ModeratorPluginClass', 'aheadzen_init' ));
if(!is_admin()){
	add_action('save_post',array('ModeratorPluginClass','aheadzen_check_post_kw_moderator'),1,2);
	add_action('bb_insert_post',array('ModeratorPluginClass','aheadzen_check_bp_post_kw_moderator'),9,2);
	add_action('bb_insert_topic',array('ModeratorPluginClass','aheadzen_check_bp_topic_kw_moderator'),9,2);
	add_action('aheadzen_moderator_notification_email',array('ModeratorPluginClass','aheadzen_moderator_notification_send_email'),9);
	
	add_filter( 'group_forum_topic_title_before_save', array('ModeratorPluginClass','group_forum_topic_blacklist') );
	add_filter( 'group_forum_topic_text_before_save',  array('ModeratorPluginClass','group_forum_topic_blacklist') );
	add_action( 'phpmailer_init',array('ModeratorPluginClass', 'az_wpse_53612_fakemailer'));
	
}
//do_action( 'save_post', $post->ID, $post, true );
//do_action( 'wp_insert_post', $post->ID, $post, true );
//do_action( 'bb_insert_post', $post_id, $args, compact( array_keys($args) ) );
class ModeratorPluginClass {
	
	public function __construct(){
		
	}
	
	function group_forum_topic_blacklist($content)
	{
		$post_text = $content;
		$args = array('id'=>$post_id,'content'=>$post_text,'title'=>'');
		$moderator_blacklist_arr = self::aheadzen_is_kw_moderator_blacklist($args);
		if($moderator_blacklist_arr['is_blacklist']){
			$content = '';
		}
		return $content;
	}
	
	/*************************************************
	Voter plugin init function
	*************************************************/
	function aheadzen_init()
	{
		load_plugin_textdomain('aheadzen', false, basename( dirname( __FILE__ ) ) . '/languages');
	}
	
	/*****************************
	Check both moderator & blacklist conditions
	*****************************/
	function aheadzen_check_post_moderator_check($args)
	{
		$title = trim($args['title']);
		$content = trim($args['content']);
		$type = $args['type'];
		if($type=='blacklist')
		{
			$mod_keys = trim(get_option('aheadzen_moderator_post_blacklist_kw'));
		}else{ //moderator
			$mod_keys = trim(get_option('aheadzen_moderator_post_moderation_kw'));
		}
		if(!$mod_keys){return;}
		$mod_keys_arr = explode("\n", $mod_keys );
		$is_moderator = 0;
		for($i=0;$i<count($mod_keys_arr);$i++)
		{
			$mod_word = trim($mod_keys_arr[$i]);
			if($content && strstr($content,$mod_word)){	$is_moderator = 1; break;}
			if($title && strstr($title,$mod_word)){	$is_moderator = 1; break;}
		}
		return $is_moderator;
	}
	
	/*****************************
	Moderator Function
	*****************************/
	function aheadzen_check_post_moderator($args)
	{
		$args['type'] = 'moderator';
		$is_moderator = self::aheadzen_check_post_moderator_check($args);
		return $is_moderator;
	}
	
	/*****************************
	Blacklist Function
	*****************************/
	function aheadzen_check_post_blacklist($args)
	{
		$args['type'] = 'blacklist';
		$is_blacklist = self::aheadzen_check_post_moderator_check($args);
		return $is_blacklist;
	}
	
	/*****************************
	Moderator Hyperlink Check
	*****************************/
	function aheadzen_check_post_moderator_href($content)
	{
		$moderation_href = trim(get_option('aheadzen_moderator_post_moderation_href'));
		$moderation_href_num = trim(get_option('aheadzen_moderator_post_moderation_href_num'));
		$is_moderator=0;
		if($moderation_href && $moderation_href_num > 0)
		{
			$num_links = preg_match_all( '/<a [^>]*href/i', $content, $out );
			if($num_links && $moderation_href_num<=$num_links)
			{
				$is_moderator=1;
			}			
		}
		return $is_moderator;
	}
	
	/*****************************
	Check Moderator & Black List
	*****************************/
	function aheadzen_is_kw_moderator_blacklist($args)
	{
		$is_moderator = self::aheadzen_check_post_moderator($args);
		if(!$is_moderator){ $is_moderator = self::aheadzen_check_post_moderator_href($args['content']); }
		$is_blacklist = self::aheadzen_check_post_blacklist($args);
		return array('is_moderator'=>$is_moderator,'is_blacklist'=>$is_blacklist);
	}
	
	function aheadzen_get_length($string,$type='words')
	{
		$string = trim($string);
		if($string){
			/* 'words' OR 'charts' */
			if($type=='words'){ //word count
				$string_arr = explode(' ',$string);
				return count($string_arr);
			}else{ //character count
				return strlen($string);
			}
		}
	}
	function aheadzen_is_user_can_add_post()
	{
		if(self::aheadzen_is_new_user()) //check is new user
		{
			global $current_user;
			$uid = $current_user->ID;
			$user_posts = self::aheadzen_get_user_posts($uid);
			$max_posts = get_option('aheadzen_moderator_max_posts');
			if($max_posts && $max_posts<$user_posts){return 0;}
		}		
		return 1;
	}
	
	function aheadzen_get_user_posts($uid)
	{
		global $wpdb;
		$today = date('Y-m-d');
		$res = $wpdb->get_row("select count(ID) as idcount from $wpdb->posts where post_author=\"$uid\" and DATE_FORMAT(post_date,'%Y-%m-%d')=\"$today\" and post_status in ('publish','draft','pending')");
		return $res->idcount;		
	}
	
	function aheadzen_is_new_user()
	{
		global $current_user;
		$uid = $current_user->ID;
		$is_new_user = 0;
		if($uid){
			$old_user_days = get_option('aheadzen_moderator_old_user_days');
			$max_posts = get_option('aheadzen_moderator_max_posts');
			$user_registered = $current_user->data->user_registered;
			
			$diff = time() - strtotime($user_registered);
			//$years = floor($diff / (365*60*60*24));
			//$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
			//$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
			$total_days = floor($diff/(60*60*24));
			//printf("%d total Days -- %d years, %d months, %d days\n", $all_days,$years, $months, $days);
			if($total_days<=$old_user_days){
				$is_new_user = 1;
			}
		}
		return $is_new_user;
	}
	
	function aheadzen_check_title_content_length($args)
	{
		$is_content_len = $is_title_len = 1;
		$title = trim($args['title']);
		$content = trim($args['content']);
		$title_words = get_option('aheadzen_moderator_question_title_words');
		$title_chars = get_option('aheadzen_moderator_question_title_chars');
		$desc_words = get_option('aheadzen_moderator_question_desc_words');
		$desc_chars = get_option('aheadzen_moderator_question_desc_chars');
		if($title && $title_words){
			$w_count=self::aheadzen_get_length($title,'words');
			if($title_words>=$w_count){$is_title_len = 0;}
		}
		if($title && $title_chars){
			$c_count=self::aheadzen_get_length($title,'charts');
			if($title_chars>=$c_count){$is_title_len = 0;}
		}
		if($content && $desc_words){
			$w_count=self::aheadzen_get_length($content,'words');
			if($desc_words>=$w_count){$is_content_len = 0;}
		}
		if($content && $desc_chars){
			$c_count=self::aheadzen_get_length($content,'charts');
			if($desc_chars>=$c_count){$is_content_len = 0;}
		}
		return array($is_title_len,$is_content_len);
	}
	
	/*****************************
	Get Error Message
	*****************************/
	function aheadzen_get_error_msg($type='title')
	{
		$title_words = get_option('aheadzen_moderator_question_title_words');
		$title_chars = get_option('aheadzen_moderator_question_title_chars');
		$desc_words = get_option('aheadzen_moderator_question_desc_words');
		$desc_chars = get_option('aheadzen_moderator_question_desc_chars');
		
		if($type=='title'){
			return "Title should be minimum $title_words words OR $title_chars characters long";
		}elseif($type=='content'){
			return "Description should be minimum $desc_words words OR $desc_chars characters long";
		}
	}
	
	/*****************************
	WP Posts Moderator 
	*****************************/
	function aheadzen_check_post_kw_moderator($post_id,$post)
	{
		if(!is_admin()){
			if(!self::aheadzen_is_user_can_add_post()){
				wp_delete_post($post_id);
				return;
			}
		}
			
		if($post->post_status=='pending'){return;}
		$args = array('id'=>$post->ID,'content'=>$post->post_content,'title'=>$post->post_title);
		$moderator_blacklist_arr = self::aheadzen_is_kw_moderator_blacklist($args);
		if($moderator_blacklist_arr['is_blacklist']){
			wp_delete_post($post_id);	//delete the post
			return $post_id;
		}elseif($moderator_blacklist_arr['is_moderator']){
			$the_post = array(
			  'ID'           => $post_id,
			  'post_status' => 'pending'
			);
			wp_update_post( $the_post ); // update the post status to pending review
			if(!is_admin()){
				do_action('aheadzen_moderator_notification_email',$args); //send email if modarated the post
			}
			return $post_id;
		}
		
		global $error;
		$emsg = '';
		$error = new WP_Error();
		$tclenght_arr = self::aheadzen_check_title_content_length($args);
		if($tclenght_arr && !$tclenght_arr[0]){ //checking for title length error
			$emsg = self::aheadzen_get_error_msg('title');
			$error->add( 'title_error',$emsg);
		}
		if($tclenght_arr && !$tclenght_arr[1]){ //checking for content length error.
			$emsg = self::aheadzen_get_error_msg('content');
			$error->add( 'content_error',$emsg);
		}
		if($emsg){return;}
	}
	
	/*****************************
	Buddypress Posts Moderator 
	*****************************/
	function aheadzen_check_bp_post_kw_moderator($post_id,$args) 
	{
		$post_text = $args['post_text'];
		$args = array('id'=>$post_id,'content'=>$post_text,'title'=>'');
		$moderator_blacklist_arr = self::aheadzen_is_kw_moderator_blacklist($args);
		if($moderator_blacklist_arr['is_blacklist']){
			bb_delete_post($post_id, 1);	//delete the post	
		}elseif($moderator_blacklist_arr['is_moderator']){
			global $wpdb,$bbdb;
			$bbdb->query("update $bbdb->posts set post_status=1 where post_id=\"$post_id\""); // update the status to pending review
			do_action('aheadzen_moderator_notification_email',$args); //send email if modarated the post
		}
		
		$emsg = '';
		$tclenght_arr = self::aheadzen_check_title_content_length($args);
		if($tclenght_arr && !$tclenght_arr[0]){
			$emsg = self::aheadzen_get_error_msg('title');
			bp_core_add_message( $emsg, 'title_error' );
		}
		if($tclenght_arr && !$tclenght_arr[1]){
			$emsg = self::aheadzen_get_error_msg('content');
			bp_core_add_message( $emsg, 'content_error' );
		}
		if($emsg){return;}
		
	}
	
	/*****************************
	Buddypress Topics Moderator 
	*****************************/
	function aheadzen_check_bp_topic_kw_moderator($topic_id,$args) 
	{
		$topic_title = $args['topic_title'];
		//$post_text = $args['post_text'];
		$args = array('id'=>$topic_id,'content'=>$topic_title,'title'=>'');
		$moderator_blacklist_arr = self::aheadzen_is_kw_moderator_blacklist($args);
		if($moderator_blacklist_arr['is_blacklist']){
			bb_delete_topic($topic_id, 1);	//delete the post		
		}elseif($moderator_blacklist_arr['is_moderator']){
			global $wpdb,$bbdb;
			$bbdb->query("update $bbdb->topics set topic_status=1 where topic_id=\"$topic_id\""); // update the status to pending review
			do_action('aheadzen_moderator_notification_email',$args); //send email if modarated the post
		}
		
		$emsg = '';
		$tclenght_arr = self::aheadzen_check_title_content_length($args);
		if($tclenght_arr && !$tclenght_arr[0]){
			$emsg = self::aheadzen_get_error_msg('title');
			bp_core_add_message( $emsg, 'title_error' );
		}
		if($tclenght_arr && !$tclenght_arr[1]){
			$emsg = self::aheadzen_get_error_msg('content');
			bp_core_add_message( $emsg, 'content_error' );
		}
		if($emsg){return true;}
	}
	
	function aheadzen_moderator_notification_send_email($args)
	{
		global $bp;
		$from_name =  get_option('blogname');
		$from_email = get_option('admin_email');
		
		$to_email = get_option('aheadzen_moderator_moderation_notify_email');
		if(trim($to_email)==''){$to_email=get_option('admin_email');}
		
		$subject = "Post moderation notification";
		$message = "Hi site manager, <br /> One of the post  has been moderated of ID:#".$args['id']."<br /> with title of '".$args['title']."' <br /><br />Thank You.";		
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type: text/html; charset=".get_bloginfo('charset')."" . "\r\n";
		$headers .= "From: $from_name <$from_email>" . "\r\n";
		//echo "to : $to_email<br />, SUBJECT: $subject<br />, Message: $message<br />,Header : $headers";exit;
		wp_mail($to_email, $subject, $message, $headers);
	}
	
	function aheadzen_all_data_moderator()
	{
		global $wpdb;
		$moderated_posts = array();
		$res = $wpdb->get_results("select ID,post_title,post_content from $wpdb->posts where post_status in ('publish')");
		foreach($res as $resobj)
		{
			$pid = self::aheadzen_check_post_kw_moderator($resobj->ID,$resobj);
			if($pid){$moderated_posts[]=$pid;}
		}
		return $moderated_posts;
	}
	
	function az_wpse_53612_fakemailer( $phpmailer ) {
		
		$is_email_blacklist_with_kw = get_option('is_email_blacklist_with_kw');
		$mod_keys = trim(get_option('aheadzen_moderator_post_blacklist_kw'));
		if($phpmailer->Body && $is_email_blacklist_with_kw){
			$mod_keys = trim(get_option('aheadzen_moderator_post_blacklist_kw'));
			//$mod_keys = trim(get_option('aheadzen_moderator_post_moderation_kw'));
			$mod_keys_arr = explode("\n", $mod_keys );
			$is_moderator = 0;
			for($i=0;$i<count($mod_keys_arr);$i++)
			{
				$mod_word = trim($mod_keys_arr[$i]);
				$content = $phpmailer->Body;
				if($content && strstr($content,$mod_word)){
					$phpmailer->ClearAllRecipients();
				}
			}
		}
		
		$moderation_outgoint_emails = get_option('email_moderation_outgoint_emails');
		$moderation_outgoint_email_interval = get_option('email_moderation_outgoint_email_interval');
		if(!$moderation_outgoint_emails){return;}
		if(!class_exists('My_Log_Entry')){return;}
		if(!$moderation_outgoint_email_interval){$moderation_outgoint_email_interval=1;}
		$from = $phpmailer->From;
		$to = $phpmailer->getToAddresses();
		$to = $to[0][0];
		$log_table =  My_Log_Entry::LOG_TABLE;
		$time = date('Y-m-d h:i:s');
		if($from && $to && $log_table){
			$outgoint_email_modules = get_option('email_moderation_outgoint_email_modules');
			$componentArr = array();
			if($outgoint_email_modules){
				foreach($outgoint_email_modules as $component=>$typeArr){
					foreach($typeArr as $type=>$flag){
						$componentArr[] = "component=\"$component\" and type=\"$type\"";
					}
				}
			}
			global $wpdb;
			$sql = "select id from $log_table where from_email like \"$from\" and to_email like \"$to\" and TIMESTAMPDIFF(HOUR,date_recorded,'".$time."')<".$moderation_outgoint_email_interval;
			if($componentArr){
				$componentStr = "(".implode(") or (",$componentArr).")";
				$sql .= " and  ($componentStr) ";
			}
			$sql .= " limit 1";
			$log_table_id = $wpdb->get_var($sql);
			if($log_table_id){
				$phpmailer->ClearAllRecipients();
			}			
		}	
	}
}

