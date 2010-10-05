<?php get_header(); ?>

<div id="coreContent">

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

      <div class="post single hentry">
        <div class="postContent">
          <h3 class="entry-title"><?php the_title(); ?></h3>
          
        </div>
        <div class="postMeta">
          <?php
          $arc_year = get_the_time('Y');
          $arc_month = get_the_time('m');
          $arc_day = get_the_time('d');
          ?>
          
          <div class="postDate"><span>Description:</span> <?php the_content(); ?></div>
          <div class="categories"><span>Reported by:</span> <?php the_author(); ?></div>
          <div class="categories"><span>Reported on:</span> <?php the_time('F j, Y'); ?></div>
          <div class="categories"><span>Type:</span> <?php echo get_post_meta(get_the_ID(), 'ticket_type', true);?></div>
          <div class="categories"><span>Status:</span> <?php echo get_post_meta(get_the_ID(), 'ticket_status', true);?></div>
          <div class="categories"><span>Priority:</span> <?php echo get_post_meta(get_the_ID(), 'ticket_priority', true);?></div>

        </div>
      </div>
     
	<?php comment_form();?>
	
	<?php 
	/*if your theme uses custom comment form,
 * replace the comment_form() call in the page or comments teplate with something like the following
 * (beware of summary and description input field names and form, submit button and hidden ticket_id field ID's):
 */

/* start */
	global $ticket_types,$ticket_statuses,$ticket_priorities;
	
?>

<h3>If you'd like to change or comment on a ticket, please fill in the form below.</h3>
<div id="result"></div><br/>
<p class="comment-notes" style="margin-bottom:0;">Required fields are marked with *</p>
<form id="et_ticket_edit_form" method="post" action="">

<?php 
if(current_user_can('manage_options'))
{
	?>
	<p class="comment-form-description">
		<label for="description">Type </label> <span class="required">*</span> : 
		<?php et_ticket_option('ticket_type',$ticket_types, false)?>
	</p>
	<p class="comment-form-description">
		<label for="description">Status</label> <span class="required">*</span> : 
		<?php  et_ticket_option('ticket_status',$ticket_statuses, false)?> 
	</p>
	<p class="comment-form-description">
		<label for="description">Priority</label> <span class="required">*</span> : 
		<?php et_ticket_option('ticket_priority',$ticket_priorities, false)?>
	</p>
	
	<p class="comment-form-title">
		<label for="email">Ticket summary</label> <span class="required">*</span> :
		<input id=title" name="ticket_summary" type="text" value="<?php echo $post->post_title; ?>" size="30" />
	</p>
	
	<p class="comment-form-description">
		<label for="description">Ticket description</label> <span class="required">*</span>  :
		<textarea id="description" name="ticket_description" cols="45" rows="8"><?php echo $post->post_content; ?></textarea>
	</p>
	<?php 
}
?>		

<p class="comment-form-description">
	<label for="description">Comment on ticket</label> <span class="required">*</span> : 
	<textarea id="description" name="ticket_description" cols="45" rows="8"></textarea>
</p>
	
<?php
//for reCaptcha
do_action('comment_form');
?>

<p class="form-submit">
	<input type="submit" value="Send" id="et_ticket_edit_send"/>
	<input type="hidden" value="<?php the_ID();?>" id="ticket_id" name="ticket_id"/>	
</p>

</form>
<?php /* end */?>
	
  <div class="pageNav">
    <div class="prev"><?php previous_post_link('%link', '&laquo; Previous tickt'); ?></div>
    <div class="next"><?php next_post_link('%link', 'Next ticket &raquo;') ?></div>
  </div>


	<?php endwhile; else: ?>

		<p>Does not exist.</p>

<?php endif; ?>

</div>

<?php get_footer(); ?>
