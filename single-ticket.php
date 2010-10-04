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

  <div class="pageNav">
    <div class="prev"><?php previous_post_link('%link', '&laquo; Previous Post'); ?></div>
    <div class="next"><?php next_post_link('%link', 'Next Post &raquo;') ?></div>
  </div>


	<?php endwhile; else: ?>

		<p>Sorry, no posts matched your criteria.</p>

<?php endif; ?>

</div>


<?php get_footer(); ?>
