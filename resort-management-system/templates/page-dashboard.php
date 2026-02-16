<?php
/**
 * Template Name: LuxeResort Dashboard Template
 */
get_header();
?>
<div id="primary" class="content-area resort-dashboard-page-template">
	<main id="main" class="site-main">
        <div class="resort-dashboard-header" style="background:var(--resort-primary); padding: 80px 20px; text-align:center; color:#fff; margin-bottom: -40px;">
            <h1 style="color:#fff; margin:0;"><?php the_title(); ?></h1>
            <p style="opacity:0.8; margin-top:10px;"><?php _e('Your elite getaway management portal.', 'resort-manager'); ?></p>
        </div>
		<div class="resort-booking-container" style="background:#fff; border-radius:20px; box-shadow:0 10px 30px rgba(0,0,0,0.05); position:relative; z-index:10;">
            <?php
            while ( have_posts() ) :
                the_post();
                the_content();
            endwhile;
            ?>
        </div>
	</main>
</div>
<style>
.resort-dashboard-page-template .resort-guest-dashboard {
    border: 0 !important;
    padding: 0 !important;
}
</style>
<?php
get_footer();
