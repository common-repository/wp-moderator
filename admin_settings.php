<?php
class ModeratorAdminClass {
	/*************************************************
	Admin Settings For voter plugin menu function
	*************************************************/
	function aheadzen_admin_menu()
	{
		add_submenu_page('options-general.php', 'Moderator Options', 'Moderator', 'manage_options', 'moderator',array('ModeratorAdminClass','aheadzen_settings_page'));
	}

	/*************************************************
	Admin Settings For voter plugin
	*************************************************/
	function aheadzen_settings_page()
	{
		global $bp,$post;
		if($_GET['moderator']=='allposts')
		{
			$moderated_posts = ModeratorPluginClass::aheadzen_all_data_moderator();
			$moderated_posts_count = count($moderated_posts);
			echo '<script>window.location.href="'.admin_url().'options-general.php?page=moderator&msg=allpostsuccess&mc='.$moderated_posts_count.'";</script>';
			exit;
		}else
		if($_POST)
		{
			update_option('aheadzen_moderator_post_moderation_kw',$_POST['post_moderation_kw']);
			update_option('aheadzen_moderator_post_blacklist_kw',$_POST['post_blacklist_kw']);
			update_option('aheadzen_moderator_post_moderation_href',$_POST['tp_queue_chk']);
			update_option('aheadzen_moderator_post_moderation_href_num',$_POST['tp_queue']);
			update_option('aheadzen_moderator_old_user_chk',$_POST['old_user_chk']);
			update_option('aheadzen_moderator_old_user_days',$_POST['old_user_days']);
			update_option('aheadzen_moderator_max_posts',$_POST['max_posts']);
			update_option('aheadzen_moderator_question_title_words',$_POST['question_title_words']);
			update_option('aheadzen_moderator_question_title_chars',$_POST['question_title_chars']);
			update_option('aheadzen_moderator_question_desc_words',$_POST['question_desc_words']);
			update_option('aheadzen_moderator_question_desc_chars',$_POST['question_desc_chars']);
			update_option('aheadzen_moderator_moderation_notify',$_POST['moderation_notify']);
			update_option('aheadzen_moderator_moderation_notify_email',$_POST['moderation_notify_email']);
			
			update_option('email_moderation_outgoint_emails',$_POST['email_moderation_outgoint_emails']);
			update_option('email_moderation_outgoint_email_interval',$_POST['email_moderation_outgoint_email_interval']);			
			update_option('email_moderation_outgoint_email_modules',$_POST['module']);
			update_option('is_email_blacklist_with_kw',$_POST['is_email_blacklist_with_kw']);
			echo '<script>window.location.href="'.admin_url().'options-general.php?page=moderator&msg=success";</script>';
			exit;
		}

		?>
		<h2><?php _e('Moderator Settings','aheadzen');?></h2>
		<?php
		if($_GET['msg']=='success'){
		echo '<p class="success">'.__('Your settings updated successfully.','aheadzen').'</p>';
		}elseif($_GET['msg']=='allpostsuccess'){
		echo '<p class="success">'.sprintf(__('Moderation for all posts applied successfully. Total of %s posts updated.','aheadzen'),$_GET['mc']).'</p>';
		}
		$moderation_kw = get_option('aheadzen_moderator_post_moderation_kw');
		$post_blacklist_kw = get_option('aheadzen_moderator_post_blacklist_kw');
		$moderation_href = get_option('aheadzen_moderator_post_moderation_href');
		$moderation_href_num = get_option('aheadzen_moderator_post_moderation_href_num');
		$old_user_chk = get_option('aheadzen_moderator_old_user_chk');
		$old_user_days = get_option('aheadzen_moderator_old_user_days');
		$max_posts = get_option('aheadzen_moderator_max_posts');
		$question_title_words = get_option('aheadzen_moderator_question_title_words');
		$question_title_chars = get_option('aheadzen_moderator_question_title_chars');
		$question_desc_words = get_option('aheadzen_moderator_question_desc_words');
		$question_desc_chars = get_option('aheadzen_moderator_question_desc_chars');
		$moderation_notify = get_option('aheadzen_moderator_moderation_notify');
		$moderation_notify_email = get_option('aheadzen_moderator_moderation_notify_email');
		$email_moderation_outgoint_emails = get_option('email_moderation_outgoint_emails');
		$email_moderation_outgoint_email_interval = get_option('email_moderation_outgoint_email_interval');
		if(!$email_moderation_outgoint_email_interval){$email_moderation_outgoint_email_interval=1;}
		$is_email_blacklist_with_kw = get_option('is_email_blacklist_with_kw');
		$outgoint_email_modules = get_option('email_moderation_outgoint_email_modules');
		?>
		<style>.success{padding:10px; border:solid 1px green; width:70%; color:green;font-weight:bold;}
		.textarea{width:400px; height:200px;}
		</style>
		<form method="post" action="<?php echo admin_url();?>options-general.php?page=moderator">
			<table class="form-table">
				<tr valign="top">
					<td>
					<label for="post_moderation">
					<p><?php _e('Posts Moderation Keywords','aheadzen');?> ::<br />
					<textarea class="textarea" name="post_moderation_kw"><?php echo $moderation_kw;?></textarea>
					<br /><small><?php _e('when a posts contains any of these words in the content, it will be held for moderation queue (post pending status).<br/> Enter keywords in new line by enter key.','aheadzen');?></small>
					</p>
					</label>
					</td>
				</tr>
				
				<tr valign="top">
					<td>
					<label for="post_moderation">
					<p><?php _e('Posts Black List Keywords','aheadzen');?> ::<br />
					<textarea class="textarea" name="post_blacklist_kw"><?php echo $post_blacklist_kw;?></textarea>
					<br /><small><?php _e('when a posts contains any of these words in the content, it will be deleted permanently.<br/> Enter keywords in new line by enter key.','aheadzen');?></small>
					</p>
					</label>
					</td>
				</tr>
				
				<tr valign="top">
					<td>
					<label for="comments_notify">
						<input type="checkbox" <?php if($moderation_href){echo 'checked';} ?> value="1" id="tp_queue_chk" name="tp_queue_chk">
						<?php _e('Hold a Posts in the queue if it contains','aheadzen');?> 
						<input type="number" class="small-text" value="<?php echo $moderation_href_num;?>" id="tp_queue" step="1" min="0" name="tp_queue"> 
						<?php _e('or more links','aheadzen');?> 
					</label>
					</td>
				</tr>
				
				<tr valign="top">
					<td>
				<label for="default_comment_status">
				<input type="checkbox" value="open" <?php if($old_user_chk){echo 'checked';} ?> id="tt_old_user_chk" name="old_user_chk">
				Treat <input type="number" class="small-text" value="<?php echo $old_user_days;?>" id="old_user_days" step="1" min="0" name="old_user_days"> days old as new users.</label>
				</td>
				</tr>
				
				<tr valign="top"><td>
				Limit to not more than
				<input id="max_posts" type="number" value="<?php echo $max_posts;?>" name="max_posts" style="width:100px">
				posts per day from new users.
				</td></tr>
				
				<tr valign="top"><td>
				<label for="page_width">Post Title Length</label>
				<input id="question_title_words" type="number" value="<?php echo $question_title_words;?>" name="question_title_words" style="width:100px">
				Word <strong>OR</strong> <input id="question_title_chars" type="number" value="<?php echo $question_title_chars;?>" name="question_title_chars" style="width:100px"> Character
				</td></tr>
				
				<tr valign="top"><td>
				<label for="page_width">Post Description Length</label>
				<input id="question_desc_words" type="number" value="<?php echo $question_desc_words;?>" name="question_desc_words" style="width:100px">
				Word <strong>OR</strong> <input id="question_desc_chars" type="number" value="<?php echo $question_desc_chars;?>" name="question_desc_chars" style="width:100px"> Character
				</td></tr>
				<tr><td><hr /></td></tr>
				<tr valign="top"><td>
				<h3>Email Options</h3>
				<label for="email_moderation_notify">
						<input type="checkbox" <?php if($moderation_notify){echo 'checked';} ?> value="1" id="email_moderation_notify" name="moderation_notify">
						<?php _e('Email to admin in case of moderated any post.','aheadzen');?> 
						
					</label>
				</td></tr>
				<tr valign="top"><td>
				<label for="page_width">Send email to Email ID :: </label>
					<input type="text" value="<?php echo $moderation_notify_email;?>" id="moderation_notify_email" name="moderation_notify_email">
						<br /><small><?php echo __('Enter email address. default is :','aheadzen').get_option('admin_email');?> </small>
				</td></tr>
				<tr><td><hr /></td></tr>
				<tr valign="top"><td>
				<label for="is_email_blacklist_with_kw">
						<input type="checkbox" <?php if($is_email_blacklist_with_kw){echo 'checked';} ?> value="1" id="is_email_blacklist_with_kw" name="is_email_blacklist_with_kw">
						<?php _e('Use blacklisted keywords to filter buddy press messages.','aheadzen');?> 
						
					</label>
				</td></tr>
				<tr><td><hr /></td></tr>
				<tr valign="top"><td>				
				<label for="email_moderation_outgoint_emails">
						<input type="checkbox" <?php if($email_moderation_outgoint_emails){echo 'checked';} ?> value="1" id="email_moderation_outgoint_emails" name="email_moderation_outgoint_emails">
						<?php _e('Moderate Outgoing Emails.','aheadzen');?> 
						
					</label>
				</td></tr>
				<tr valign="top"><td>
				<label for="email_moderation_outgoint_email_interval">
				Do not send repeat emails to any user before 
				<input style="width:50px;" type="text" value="<?php echo $email_moderation_outgoint_email_interval;?>" id="email_moderation_outgoint_email_interval" name="email_moderation_outgoint_email_interval">
				hours
				</label><br />
				<small>Default is 1 hour</small>
				</td></tr>
				<tr valign="top"><td>				
				<label><?php _e('Select Modules (default for all):: ','aheadzen');?> </label>					
					<?php
					global $wpdb;
					if(class_exists('My_Log_Entry')){
						$componentArr = array();
						$res = $wpdb->get_results("SELECT DISTINCT `component`,`type` FROM `".My_Log_Entry::LOG_TABLE."` order by component ASC");
						if($res){
							foreach($res as $resObj){							
								$componentArr[$resObj->component][]=$resObj->type;
							}
							foreach($componentArr as $key=>$val){
							?>
							<h4><?php echo $key;?></h4>
							<ul>
							<?php
								for($t=0;$t<count($val);$t++){ 
							?>
								<li><input type="checkbox" <?php if($outgoint_email_modules && $outgoint_email_modules[$key][$val[$t]]){echo 'checked';}?> name="module[<?php echo $key;?>][<?php echo $val[$t];?>]" value="1"> <?php echo $val[$t];?></li>	
							<?php }?>
							</ul>
							<?php }
						}
					}
					?>
				</td></tr>				
				<tr><td><hr /></td></tr>
				<tr valign="top"><td>
				<h3>Manage Moderator for Older posts</h3>
				
				<label for="email_moderation_notify">
				<a href="<?php echo admin_url('options-general.php?page=moderator&moderator=allposts');?>">Apply Moderator Condition for All Posts</a>
				</label>
				</td></tr>
				
				<tr valign="top">
					<td>
						<input type="hidden" name="page_options" value="<?php echo $value;?>" />
						<input type="hidden" name="action" value="update" />
						<input type="submit" value="Save settings" class="button-primary"/>
					</td>
				</tr>					
			</table>
		</form>
		<?php
		// Check that the user is allowed to update options  
		if (!current_user_can('manage_options'))
		{
			wp_die('You do not have sufficient permissions to access this page.');
		}
	}
}