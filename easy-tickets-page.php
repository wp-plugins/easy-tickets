<?php
/*
Template Name: Tickets
*/
?>

<?php get_header(); ?>

<div id="coreContent">
<?php 
	//save current page data - to be used with comment form below
    global $post;
 	$tmp_post = $post;

?>
<?php query_posts( array('post_type'=>'ticket','post_status' => 'publish','posts_per_page' => -1)); if (have_posts()) :?>

	<table id="tickets">
		<thead>
		<tr>
			<th>ID</th>
			<th>Submitted</th>
			<th>Summary</th>
			<th>Type</th>
			<th>Priority</th>
			<th>Status</th>
		</tr>
		
		</thead>
		<tbody id="tickets_body">
		
		 <?php while (have_posts()) : the_post(); ?>
		
		<tr>
			<td><?php the_ID();?></td>
			<td><?php the_date('F j, Y');?></td>
			<td><a href="<?php the_permalink() ?>"><?php the_title();?></a></td>
			<td ><?php echo get_post_meta(get_the_ID(), 'ticket_type', true);?></td>
			<td><?php echo get_post_meta(get_the_ID(), 'ticket_priority', true);?></td>
			<td><?php echo get_post_meta(get_the_ID(), 'ticket_status', true);?><td/>
		</tr>		
		
		<?php endwhile; ?>
		
		</tbody>
	</table>
	<?php $url = plugins_url("easy-tickets/icons/");?>
	<div class="pager" id="ticket_pager">
	<form>
		<img class="first" src="<?php echo $url;?>first.png"/>
		<img class="prev" src="<?php echo $url;?>prev.png"/>
		<span style="font-size: 1.6em">Go to:</span>
		<input type="text" class="pagedisplay" size="4"/>
		
		<span style="font-size: 1.6em">Per page:</span>
		<select class="pagesize">
			<option value="5" selected="selected">5</option>
			<option value="10">10</option>
			<option value="25">25</option>
			<option value="50">50</option>
		</select>
		
		<img class="next" src="<?php echo $url;?>next.png"/>
		<img class="last" src="<?php echo $url;?>last.png"/>
		
	</form>
	</div>
	
<div id="comments">

<?php 
//load temp page data
$post = $tmp_post; 

comment_form(); ?>
</div>	
	<?php else : ?>

		<h2>Good news!</h2>
		<p>No tickets have been reported yet.</p>

	<?php endif; ?>

  </div>
  
<?php get_footer(); ?>