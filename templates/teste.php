<?php get_header(); ?>
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
   
   <div class="container pagina-em-branco">
   	<div class="row">
   		<div class="col-12 text-center">
   			<h1>Apenas um teste</h1>
   		</div>
   	</div>
   </div>

<?php endwhile; endif; ?>
<?php get_footer(); ?>