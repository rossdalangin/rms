<?php
/**
 * Template Name: LuxeResort Full Width Canvas
 */
get_header();
?>
<div id="primary" class="content-area resort-full-width-template">
	<main id="main" class="site-main">
		<?php
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
		?>
	</main>
</div>
<style>
.resort-full-width-template {
    max-width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
}
.resort-full-width-template .resort-booking-container {
    max-width: 1200px;
    margin: 60px auto;
    padding: 60px;
    background: #fff;
    box-shadow: 0 20px 40px rgba(0,0,0,0.05);
    border-radius: 20px;
}
</style>
<?php
get_footer();
