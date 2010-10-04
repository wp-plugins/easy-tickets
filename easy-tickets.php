<?php
/*
 Plugin Name: Easy Tickets
 Plugin URI: www.google.com
 Description: Enables a simple support ticket system.
 Author: Paxman
 Version: 1.0
 Author URI: http://paxman.blog.siol.net
 */

//globals
$meta_fields = array("ticket_type","ticket_status","ticket_priority");
$fields_to_name = array("ticket_type"=>"Type","ticket_status"=>"Status","ticket_priority"=>"Priority");

$ticket_statuses = array("Open","Assigned","Will not fix","Closed");
$ticket_types = array("Bug","Enhancement","Future request","Patch");
$ticket_priorities = array("Low","Medium","High");

// Initiate the plugin
add_action("init", "et_register_ticket_type");

//column view
add_filter("manage_edit-ticket_columns", "et_edit_columns");
add_action("manage_posts_custom_column", "et_edit_custom_columns");

//save ticket
add_action("save_post", "et_save_post", 10, 2);

//ajax
//addd
add_action("wp_ajax_et_add_ticket", "et_ajax_add_ticket");
add_action('wp_ajax_nopriv_et_add_ticket', 'et_ajax_add_ticket');
//update
add_action("wp_ajax_et_update_ticket", "et_ajax_update_ticket");
add_action('wp_ajax_nopriv_et_update_ticket', 'et_ajax_update_ticket');

//quick edit options box
add_action('quick_edit_custom_box', 'et_quick_edit_box');
add_filter('editable_slug','et_quick_edit_fields');
add_filter('manage_posts_columns', 'et_quick_edit_column', 10, 1);
add_action( "admin_head","et_quick_edit_css" );

//register scripts 
add_action('init', 'et_register_js');

//page/ticket forms
//template_redirect
add_action('wp', 'et_form_forms');

function et_register_ticket_type()
{
	// Register custom post types
	register_post_type('ticket', array(
			'label' => __('Tickets'),
			'singular_label' => __('Tickets'),
			'public' => true,
			'show_ui' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => true,
			'_builtin' => false, // It's a custom post type, not built in
			'_edit_link' => 'post.php?post=%d',
			'capability_type' => 'post',
			'hierarchical' => false,
			'rewrite' => array("slug" => "ticket"), 
			'query_var' => "ticket",
			'register_meta_box_cb' => 'et_edit_meta_box',
			'supports' => array('title','editor','comments' /*,'custom-fields'*/) // Let's use custom fields for debugging purposes only
	));


	add_action( "admin_head", "et_edit_remove_media_buttons");
	
	//add_action( "admin_head", "et_edit_hide_commentsdiv");
	
	
}

function et_register_js() 
{
	wp_deregister_script( 'inline-edit-post' );
	
	wp_register_script( 'inline-edit-post', plugins_url('inline-edit-post.js',__FILE__),array('jquery'));
	wp_register_script('et-ajax-form', plugins_url("jquery.form.js",__FILE__),array('jquery'));
	wp_register_script( 'et-table-sorter-js', plugins_url('jquery.tablesorter.min.js',__FILE__),array('jquery'));
	wp_register_script( 'et-table-sorter-pager-js', plugins_url('jquery.tablesorter.pager.js',__FILE__),array('jquery'));
	
	wp_register_style('et-table-sorter-css', plugins_url("blue/style.css",__FILE__));
	wp_register_style('et-table-pager-css', plugins_url("jquery.tablesorter.pager.css",__FILE__));
}     

function et_form_ticket_js()
{
	?>
	<script type="text/javascript">
	jQuery.noConflict();
	jQuery(document).ready(function()
	{
	
		jQuery('#et_ticket_edit_send').removeAttr('name');
		
		//prepare Options Object 
		var options = { 
					data: { 'action':'et_update_ticket',
							'et_update_ticket_nonce': "<?php echo wp_create_nonce('et_update_ticket_nonce'); ?>"
							},
					type: "POST",
					dataType: 'script',
				    url:       "<?php echo admin_url('admin-ajax.php') ?>",
				    success:   function(responseText, statusText, xhr, $form) 
				    { 
				    	 jQuery('#result').html(responseText);
				    } 
				}; 
		
		jQuery('#et_ticket_edit_form').submit(function() 
		{ 
		    jQuery(this).ajaxSubmit(options); 
		    return false; 
		}); 
	
	});
	</script>
	<?php
}

function et_form_page_js()
{
	?>
	<script type="text/javascript">
	jQuery.noConflict();
	jQuery(document).ready(function()
	{
	
		jQuery('#et_page_add_send').removeAttr('name');
		
		//prepare Options Object 
		var options = { 
					data: { 'action':'et_add_ticket',
							'et_add_ticket_nonce': "<?php echo wp_create_nonce('et_add_ticket_nonce'); ?>"
							},
					type: "POST",
					dataType: 'script',
				    url:       "<?php echo admin_url('admin-ajax.php') ?>",
				    success:   function(responseText, statusText, xhr, $form) 
				    { 
				    	 jQuery('#result').html(responseText);
				    } 
				}; 
		
		jQuery('#et_page_add_form').submit(function() 
		{ 
		    jQuery(this).ajaxSubmit(options); 
		    return false; 
		}); 

    	jQuery("#tickets").tablesorter().tablesorterPager({container: jQuery("#ticket_pager")}); ; 
		
	});
	</script>
	<?php
}


function et_quick_edit_fields($postname) 
{
	global $post;
	
	//if($post->post_type == 'ticket')
	{
		$custom = get_post_custom($post->ID);
		$ticket_type = $custom["ticket_type"][0];
		$ticket_status = $custom["ticket_status"][0];
		$ticket_priority = $custom["ticket_priority"][0];
		
		return $postname.'</div><div class="ticket_status_text">'.$ticket_status.
						'</div><div class="ticket_type_text">'.$ticket_type.
						'</div><div class="ticket_priority_text">'.$ticket_priority;
	}
}


function et_quick_edit_css () 
{ 
	global $post;
	
	if ($post->post_type == 'ticket')
	{
		?>
		<style>
			#wpbody-content .inline-edit-row-page .inline-edit-col-right, #owpbody-content .bulk-edit-row-post .inline-edit-col-right
			{
				margin-top:0;
			}
		</style>
		<?php
	} 
}
	

function et_quick_edit_box($column_name)
{
	global $post;
	
	if ($post->post_type == 'ticket')
	{
		echo '<fieldset class="inline-edit-col-right ticket-options"><div class="inline-edit-col">
		<div class="inline-edit-group">';
		et_edit_ticket_options();
		echo '</label></div></div></fieldset>';
	}
}


function et_quick_edit_column($posts_columns) 
{
    $posts_columns['sth'] = '';
    return $posts_columns;
}

function et_form_forms()
{
	global $post;
	
	//forms javascript
	if ($post->post_type == 'ticket')
	{
		add_filter('comment_id_fields', 'et_form_ticket_id_fields');
		add_filter('comment_form_defaults', 'et_form_ticket_defaults');
		add_action('wp_head', 'et_form_ticket_js');
		wp_enqueue_script( 'et-ajax-form');
	}
	else
	if (is_page_template('easy-tickets-page.php'))
	{
		add_filter('comment_id_fields', 'et_form_page_id_fields');
		add_filter('comment_form_defaults', 'et_form_page_defaults');
			
		wp_enqueue_style('et-table-sorter-css');
		wp_enqueue_style('et-table-pager-css');
		
		wp_enqueue_script( 'et-table-sorter-js');
		wp_enqueue_script( 'et-table-sorter-pager-js');
	
		wp_enqueue_script( 'et-ajax-form');
		
		add_action('wp_head', 'et_form_page_js');
		
	}
}

function et_edit_hide_commentsdiv ()
{
	?>
<style>
#commentstatusdiv {
	display: none;
}
</style>
	<?php
}

function et_edit_remove_media_buttons() 
{
	global $post_type; 
	
	if ($post_type == 'ticket')
	{
		remove_action( 'media_buttons', 'media_buttons' );
	}
}

function et_edit_columns($columns)
{
	$columns = array(
			"cb" => "<input type=\"checkbox\" />",
			"title" => "Title",
			"ticket_description" => "Description",
			"ticket_type" => "Type",
			"ticket_priority" => "Priority",
			"ticket_status" => "Status",
	);

	return $columns;
}

function et_edit_custom_columns($column)
{
	global $post;

	switch ($column)
	{
		case "title":
			the_title();
			break;
		case "ticket_description":
			the_excerpt();
			break;
		case "ticket_type":
			echo get_post_meta($post->ID,'ticket_type',true);
			break;
		case "ticket_priority":
			echo get_post_meta($post->ID,'ticket_priority',true);
			break;
		case "ticket_status":
			echo get_post_meta($post->ID,'ticket_status',true);
			break;
	}
}

// When a post is inserted or updated
function et_save_post($post_id, $post = null)
{	 
	if(!current_user_can('edit_posts'))
		wp_die('You don\'t have the permission to do this');
	
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
		return $post_id;	

	if ($post->post_type == "ticket")
	{
		global $meta_fields,$fields_to_name;
		$user = wp_get_current_user();
		
		$meta_changed = '';
		
		$prev_meta = get_post_custom($post_id);
		$cur_meta;
		
		// Loop through the POST data
		foreach ($meta_fields as $key)
		{
			if(isset($_POST[$key]))
			{
				$cur_meta[$key] = $_POST[$key];
				
				$prev = $prev_meta[$key][0];	
				$cur = $_POST[$key];
				
				// If value is a string it should be unique
				if (!is_array($value))
				{
					// Update meta
					if (!update_post_meta($post_id, $key, $cur))
					{
						// Or add the meta data
						add_post_meta($post_id, $key, $cur, true);
					}
					
					if($prev != $cur)
					{
						$meta_changed .= $fields_to_name[$key].": <em>".$prev." -> ".$cur."</em>\n";
					}
				}
			}
		}

		$message = '';
		
		if($meta_changed != '')
		{
			$message .= $meta_changed;
		}	
		
		if(!empty($_POST["ticket_comment"]) && stristr($_POST["ticket_comment"],"Enter comment") == FALSE && $_POST["ticket_comment"] != "")
		{
			$message .= $wpdb->escape($_POST["ticket_comment"]);
		}
		
		if($message != '')
		{
			global $wpdb;

			$time = current_time('mysql');
				
			$data = array(
				    'comment_post_ID' => $post_id,
				    'comment_author' => $user->display_name,
				    'comment_author_email' => $user->user_email,
				    'comment_author_url' => site_url(),
				    'comment_content' =>$message,
				    'user_id' => $user->ID,
				 	'comment_type' => '',
				    'comment_author_IP' => '127.0.0.1',
				    'comment_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
				    'comment_date' => $time,
				 	'comment_approved' => 1,
			);

			wp_insert_comment($data);
		}
		
		//close comments if status changed to closed
		if($prev_meta['ticket_status'][0] != 'Closed' && $cur_meta['ticket_status'] == 'Closed')
		{
			global $wpdb;
			$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET comment_status = %s WHERE ID = %d", 'closed', $post_id ) );	
		}
		//or open it
		else if($prev_meta['ticket_status'][0] == 'Closed' && $cur_meta['ticket_status'] != 'Closed')
		{
			global $wpdb;
			$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET comment_status = %s WHERE ID = %d", 'open', $post_id ) );	
		}
		
	}
	else
	return $post_id;
}

function et_edit_meta_box()
{
	// Custom meta boxes for the edit podcast screen
	add_meta_box("ticket_status", "Ticket options", "et_edit_ticket_options", "ticket", "side", "high");
	add_meta_box("ticket_comment", "Ticket comment", "et_edit_ticket_comment", "ticket", "normal", "high");
}

function et_edit_ticket_comment()
{
	global $post;

	?>
<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>"
	class="postarea"><textarea id="" tabindex="2" name="ticket_comment"
	cols="40" class="theEditor" rows="10">Enter comment
</textarea></div>

	<?php
}

function et_ticket_option($name,$globals,$echo = false)
{
	global $post;

	$custom = get_post_custom($post->ID);
	$value = $custom[$name][0];

	$option = '<select name="'.$name.'" class="'.$name.'">';

	foreach($globals as $global)
	{
		$selected = ($global == $value) ? "selected='selected'" : "";
		$option .= "<option value='$global' $selected>$global</option>";
	}

	$option .= '</select>';

	if($echo)
		echo $option;
	else
		return $option;
}

// Admin post meta contents
function et_edit_ticket_options()
{
	global $post;
	$custom = get_post_custom($post->ID);
	$ticket_type = $custom["ticket_type"][0];
	$ticket_status = $custom["ticket_status"][0];
	$ticket_priority = $custom["ticket_priority"][0];

	global $ticket_statuses;
	global $ticket_types;
	global $ticket_priorities;
	
	?>
		<span class="title">Status:</span>
		<select name='ticket_status' class='ticket_status'>
		<?php
		foreach($ticket_statuses as $status)
		{
			$selected = ($status == $ticket_status) ? "selected='selected'" : "";
			echo "<option value='$status' $selected>$status</option>";
		}
		?>
		</select>
		<br/>
		<span class="title">Type:</span>
		<select name='ticket_type' class='ticket_type'>
		<?php
		foreach($ticket_types as $type)
		{
			$selected = ($type == $ticket_type) ? "selected='selected'" : "";
			echo "<option value='$type' $selected>$type</option>";
		}
		?>
		</select>
		<br/>
		<span class="title">Priority:</span>
		<select name='ticket_priority' class='ticket_priority'>
		<?php
		foreach($ticket_priorities as $priority)
		{
			$selected = ($priority == $ticket_priority) ? "selected='selected'" : "";
			echo "<option value='$priority' $selected>$priority</option>";
		}
		?>
		</select>
	
<?php
}

function et_form_ticket_defaults($result)
{
	global $post,$ticket_types,$ticket_statuses,$ticket_priorities;

	$result['fields'] =  array(
		'result' => '<div id="result"></div><br/>',
		);
		
	if(current_user_can('manage_options'))
	{
		$result['fields']['type'] = '<p class="comment-form-description"><label for="description">' . _x( 'Type', 'noun' ) . 
				'</label> <span class="required">*</span> : '. et_ticket_option('ticket_type',$ticket_types, false).'</p>';
		$result['fields']['status'] ='<p class="comment-form-description"><label for="description">' . _x( 'Status', 'noun' ) . 
				'</label> <span class="required">*</span> : '. et_ticket_option('ticket_status',$ticket_statuses, false).'</p>';
		$result['fields']['priority'] ='<p class="comment-form-description"><label for="description">' . _x( 'Priority', 'noun' ) . 
				'</label> <span class="required">*</span> : '. et_ticket_option('ticket_priority',$ticket_priorities, false).'</p>';
	
		$result['fields']['summary'] ='<p class="comment-form-title"><label for="email">' . __( 'Summary' ) . '</label> <span class="required">*</span> :'.
                            '<input id=title" name="ticket_summary" type="text" value="'.$post->post_title.'" size="30"' . $aria_req . ' /></p>';
		$result['fields']['description'] ='<p class="comment-form-description"><label for="description">' . _x( 'Description', 'noun' ) . 
				'</label> <span class="required">*</span> : <textarea id="description" name="ticket_description" cols="45" rows="8">'.$post->post_content.'</textarea></p>';
	}
		
	$result['fields']['comment'] = '<p class="comment-form-description"><label for="description">' . _x( 'Comment on ticket', 'noun' ) . 
				'</label> <span class="required">*</span> : <textarea id="description" name="ticket_description" cols="45" rows="8"></textarea></p>';
	
	$captcha = '';
	
	//that easy?
	//captcha
	if(has_action('comment_form', 'recaptcha_comment_form'))
	{
		if(!is_user_logged_in())
		{
			remove_action('comment_form', 'recaptcha_comment_form');
			add_action('comment_form_after_fields','recaptcha_comment_form');
		}
	}
	else
	{
		//use really simple captcha,if present
		//$captcha = captcha form
	}
		
	/*if(function_exists('recaptcha_get_html'))
	{
		if($captcha = get_option('recaptcha'));
		{
			if($captcha['pubkey'] != '')
			{
				$captcha = recaptcha_get_html($captcha['pubkey']);
			}
		}
	}*/
	
	if(is_user_logged_in())
	{
		$result['comment_field'] = implode($result['fields']);
				
		if(!current_user_can('manage_options'))
			$result['comment_field'] .= $captcha;
	}
	else
	{
		$result['comment_field'] = '';
		$result['fields']['captcha'] = $captcha;
	}
		
	$result['comment_notes_before'] = '<p class="comment-notes" style="margin-bottom:0;">Required fields are marked with *</p>';
	$result['title_reply'] = "If you'd like to comment on or change the ticket, please fill in the form below.";
	$result['comment_notes_after'] = '';
	$result['id_form'] = 'et_ticket_edit_form';
	$result['id_submit'] = 'et_ticket_edit_send';

	$result['label_submit'] = 'Send';

	return $result;
}

function et_form_page_defaults($result)
{
	$result['fields'] =  array(
		'result' => '<div id="result"></div><br/>',
		'summary' =>'<p class="comment-form-title"><label for="email">' . __( 'Problem summary' ) . '</label> <span class="required">*</span> :'.
                            '<input id=title" name="ticket_summary" type="text" value="" size="30"' . $aria_req . ' /></p>',
		'description' =>'<p class="comment-form-description"><label for="description">' . _x( 'Problem description', 'noun' ) . '</label> <span class="required">*</span> :'.
						'<textarea id="description" name="ticket_description" cols="45" rows="8"></textarea></p>'		
	);
		
	$captcha = '';
	
	//that easy?
	//captcha
	if(has_action('comment_form', 'recaptcha_comment_form'))
	{
		if(!is_user_logged_in())
		{
			remove_action('comment_form', 'recaptcha_comment_form');
			add_action('comment_form_after_fields','recaptcha_comment_form');
		}
	}
	else
	{
		//use really simple captcha,if present
		//$captcha = captcha form ...
	}
	
	/*
	if(function_exists('recaptcha_get_html'))
	{
		if($captcha = get_option('recaptcha'));
		{
			if($captcha['pubkey'] != '')
			{
				$captcha = '';//recaptcha_get_html($captcha['pubkey']);
			}
		}
	}*/
	
	if(is_user_logged_in())
	{
		$result['comment_field'] = implode($result['fields']);
				
		if(!current_user_can('manage_options'))
			$result['comment_field'] .= $captcha;
	}
	else
	{
		$result['comment_field'] = '';
		$result['fields']['captcha'] = $captcha;
	}
		
	$result['comment_notes_before'] = '<p class="comment-notes" style="margin-bottom:0;">Required fields are marked with *</p>';
	$result['title_reply'] = "<h3>If you'd like to report a new ticket, please fill in the form below.</h3>";
	$result['comment_notes_after'] = '';
	$result['id_form'] = 'et_page_add_form';
	$result['id_submit'] = 'et_page_add_send';

	$result['label_submit'] = 'Report';

	return $result;
}

function et_form_ticket_id_fields($result)
{
	global $post;
    $result = "<input type='hidden' name='ticket_id' id='ticket_id' value='".$post->ID."' />\n";
	//$result ='';
	return $result;
}

function et_form_page_id_fields($result)
{
	$result ='';
	return $result;
}

function et_ajax_update_ticket()
{
	if(!check_ajax_referer('et_update_ticket_nonce','et_update_ticket_nonce',false))
	{
		et_message('red','Bad boy!');
		exit();
	}
	
	if(!(isset($_POST['ticket_summary']) && $_POST['ticket_summary'] != "") ||
	!(isset($_POST['ticket_description']) && $_POST['ticket_description'] != "") ||
	!(isset($_POST['ticket_comment']) && $_POST['ticket_comment'] != "") ||
	!(isset($_POST['ticket_type']) && $_POST['ticket_type'] != "")||
	!(isset($_POST['ticket_status']) && $_POST['ticket_status'] != "")||
	!(isset($_POST["ticket_priority"])&& $_POST['ticket_priority'] != ""))
	{
		et_message('red','Some of the required fields were not set. Please set them.');
		exit();
	}

	//set, clean input
	if(!et_check_fields($_POST['ticket_type'],$_POST['ticket_status'],$_POST["ticket_priority"]))
	{
		et_message('red','Ticket type, status or priority is wrong. Please check them.');
		exit();
	}	
	
	$summary = sanitize_text_field($_POST['ticket_summary']);
	$description = wp_filter_nohtml_kses($_POST['ticket_description']);
	$comment = wp_filter_nohtml_kses($_POST['ticket_comment']);
	
	//reflect reCaptcha settings
	$result = et_check_recaptcha();
	if(!$result)
	{
		et_message('red','Wrong reCaptcha or reCaptcha error. Please refresh the page and try again.');
		exit();
	}
	
	//if not administrator
	if (!current_user_can('manage_options'))
	{
		//captcha - after it gets fixxed :)
		/*
		if ( class_exists('ReallySimpleCaptcha') )
		{
			$wps_comment_captcha = new ReallySimpleCaptcha();
			$wps_comment_captcha_prefix = ($_POST['comment_captcha_prefix']);
			$wps_comment_captcha_code = sanitize_text_field($_POST['comment_captcha_code']);
			$wps_comment_captcha_correct = false;
			$wps_comment_captcha_check = $wps_comment_captcha->check( $wps_comment_captcha_prefix, $wps_comment_captcha_code );
			$wps_comment_captcha_correct = $wps_comment_captcha_check;
			$wps_comment_captcha->remove($wps_comment_captcha_prefix);
			$wps_comment_captcha->cleanup();
			
			if ( ! $wps_comment_captcha_correct )
			{
				echo '<div style="padding: 20px;  border: 1px solid red;;">You have entered an incorrect CAPTCHA value. Refresh page, and try again.</div>';
				exit();
			}
		}*/

		//akismet
		global $akismet_api_host, $akismet_api_port;

		//if akismet installed/activated and have API key
		if ( function_exists( 'akismet_http_post' ) &&
		( get_option( 'wordpress_api_key' ) || $wpcom_api_key ) )
		{

			$c['blog'] = get_option( 'home' );
			$c['user_ip'] = preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );
			$c['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
			$c['referrer'] = $_SERVER['HTTP_REFERER'];
			$c['comment_type'] = 'ticket';

			//hrmphf
			//$c['permalink'] = $permalink;

			//maybe
			//$c['comment_author_email'] = $showcase_email;

			$c['comment_author'] = $summary;
			//$c['comment_author_url'] = $showcase_url;
			$c['comment_content'] = $description;

			$ignore = array( 'http_cookie',"ticket_summary","ticket_description");

			foreach ( $_SERVER as $key => $value )
			if ( ! in_array( $key, (array) $ignore ) )
			$c["$key"] = $value;

			$query_string = '';
			foreach ( $c as $key => $data )
			$query_string .= $key . '=' . urlencode( stripslashes( (string) $data ) ) . '&';

			$response = akismet_http_post( $query_string, $akismet_api_host,'/1.1/comment-check', $akismet_api_port );

			if ( 'true' == $response[1] )
			{
				et_message('red','Akismet said you\'re spammer. If you think you are not one, change something in the entry fields and try again.');
				exit();
			}
		}
	}

	//is ticket id valid
	$ticket = get_post($_POST('ticket_id'),ARRAY_A);
	
	if($ticket == null || $ticket['post_type'] != 'ticket')
	{
		et_message('red','Ticket ID is not valid.');
		exit();
	}
	
	//change ticket/add comment
	if(current_user_can('manage_options'))
	{
		//update ticket
		$post = array(
		   'ID' => $ticket['ID'],
		  'post_content' =>  $description,
		  'post_title' => $summary,
		);
	
		$post_id = wp_insert_post($post);
	
		if($post_id == 0)
		{
			et_message('red','Couldn\'t update ticket. Please, try later.');
			exit();
		}		

		update_post_meta($ticket['ID'], 'ticket_status', $_POST['ticket_status']);
		update_post_meta($ticket['ID'], 'ticket_type', $_POST['ticket_type']);
		update_post_meta($ticket['ID'], 'ticket_priority', $_POST["ticket_priority"]);
	}
	
	if(!empty($_POST["ticket_comment"]) && stristr($_POST["ticket_comment"],"Enter comment") == FALSE && $_POST["ticket_comment"] != "")
	{
		global $user_ID;
		get_currentuserinfo();

		$time = current_time('mysql');

		$data = array(
				    'comment_post_ID' => $ticket['ID'],
				    'comment_author' => $user->display_name,
				    'comment_author_email' => $user->user_email,
				    'comment_author_url' => site_url(),
				    'comment_content' =>$comment,
				    'user_id' => $user->ID,
				 	'comment_type' => '',
				    'comment_author_IP' => '127.0.0.1',
				    'comment_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
				    'comment_date' => $time,
				 	'comment_approved' => 1,
		);

		wp_insert_comment($data);
	}
	
	et_message('green','Ticket updated successfully.');	
	exit();
}

function et_ajax_add_ticket()
{
	if(!check_ajax_referer('et_add_ticket_nonce','et_add_ticket_nonce', false))
	{
		et_message('red','Bad boy!');
		exit();
	}
	
	if(!(isset($_POST['ticket_summary']) && $_POST['ticket_summary'] != "") ||
	!(isset($_POST['ticket_description']) && $_POST['ticket_description'] != ""))
	{
		et_message('red','Some of the required fields were not set. Please set them.');
		exit();
	}

	//set, clean input
	$summary = sanitize_text_field($_POST['ticket_summary']);
	$description = wp_filter_nohtml_kses($_POST['ticket_description']);

	//reflect reCaptcha settings
	$result = et_check_recaptcha();
	if(!$result)
	{
		et_message('red','Wrong reCaptcha or reCaptcha error. Please refresh the page and try again.');
		exit();
	}

	//else for everybody but administrators, check with akismet
	if (!current_user_can('manage_options'))
	{	
		//captcha - after it gets fixxed :)
		/*
		if ( class_exists('ReallySimpleCaptcha') )
		{
			$wps_comment_captcha = new ReallySimpleCaptcha();
			$wps_comment_captcha_prefix = ($_POST['comment_captcha_prefix']);
			$wps_comment_captcha_code = sanitize_text_field($_POST['comment_captcha_code']);
			$wps_comment_captcha_correct = false;
			$wps_comment_captcha_check = $wps_comment_captcha->check( $wps_comment_captcha_prefix, $wps_comment_captcha_code );
			$wps_comment_captcha_correct = $wps_comment_captcha_check;
			$wps_comment_captcha->remove($wps_comment_captcha_prefix);
			$wps_comment_captcha->cleanup();
			
			if ( ! $wps_comment_captcha_correct )
			{
				echo '<div style="padding: 20px;  border: 1px solid red;;">You have entered an incorrect CAPTCHA value. Refresh page, and try again.</div>';
				exit();
			}
		}*/

		//akismet
		global $akismet_api_host, $akismet_api_port;

		//if akismet installed/activated and have API key
		if ( function_exists( 'akismet_http_post' ) &&
		( get_option( 'wordpress_api_key' ) || $wpcom_api_key ) )
		{

			$c['blog'] = get_option( 'home' );
			$c['user_ip'] = preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );
			$c['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
			$c['referrer'] = $_SERVER['HTTP_REFERER'];
			$c['comment_type'] = 'ticket';

			//hrmphf
			//$c['permalink'] = $permalink;

			//maybe
			//$c['comment_author_email'] = $showcase_email;

			$c['comment_author'] = $summary;
			//$c['comment_author_url'] = $showcase_url;
			$c['comment_content'] = $description;

			$ignore = array( 'http_cookie',"ticket_summary","ticket_description");

			foreach ( $_SERVER as $key => $value )
			if ( ! in_array( $key, (array) $ignore ) )
			$c["$key"] = $value;

			$query_string = '';
			foreach ( $c as $key => $data )
			$query_string .= $key . '=' . urlencode( stripslashes( (string) $data ) ) . '&';

			$response = akismet_http_post( $query_string, $akismet_api_host,'/1.1/comment-check', $akismet_api_port );

			if ( 'true' == $response[1] )
			{
				pws_message('red','Akismet said you\'re spammer. If you think you are not one, change something in the entry fields and try again.');
				exit();
			}
		}
	}

	//add new ticket
	global $user_ID;
	get_currentuserinfo();

	//add new post
	$post = array(
	  'post_content' =>  $description,
	  'post_status' => 'pending',
	  'post_title' => $summary,
	  'post_type' => 'ticket', 
	  'post_author' => $user_ID
	);

	$post_id = wp_insert_post($post);

	if($post_id == 0)
	{
		pws_message('red','Couldn\'t add new ticket. Please, try later.');
		exit();
	}
	
	pws_message('green','New ticket added successfully!');	
	exit();
}

//true == skip check, is not shown, or valid
function et_check_recaptcha()
{
	global $recaptcha_opt;

	// set the minimum capability needed to skip the captcha if there is one
	if ($recaptcha_opt['re_bypass'] && $recaptcha_opt['re_bypasslevel'])
		$needed_capability = $recaptcha_opt['re_bypasslevel'];

	// skip the filtering if the minimum capability is met
	if (($needed_capability && current_user_can($needed_capability)) || !$recaptcha_opt['re_comments'])
		return true;
		
	if (recaptcha_wp_show_captcha_for_comment())
	{
		$challenge = $_POST['recaptcha_challenge_field'];
		$response = $_POST['recaptcha_response_field'];

		$recaptcha_response = recaptcha_check_answer ($recaptcha_opt ['privkey'], $_SERVER['REMOTE_ADDR'], $challenge, $response);

		if ($recaptcha_response->is_valid)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	return true;
}

function et_check_fields($type,$status,$priority) 
{
	global $ticket_statuses;
	global $ticket_types;
	global $ticket_priorities;

	if(in_array($type,$ticket_types) && in_array($status,$ticket_statuses) && in_array($priority,$ticket_priorities))
		return true;
	else 
		return false;	
}

function et_message($color,$message) 
{
	echo '<div style="padding: 10px; border: 1px solid '.$color.';"><h4>'.$message.'</h4></div>';
}
