<?php
/*
Plugin Name: Plugin Rifa
Plugin URI: https://trevopremiado.com/
Description: Plugin para adminitração e configurações do script de RIFA
Author: Diogenes Junior
Author URI: http://www.diogenesjunior.com.br
*/


/**
*  ------------------------------------------------------------------------------------------------
*
*
*   REGISTERS
*
*
*  ------------------------------------------------------------------------------------------------
*/
add_theme_support( 'woocommerce' );

add_action( 'wp_enqueue_scripts', 'misha_register_and_enqueue' );
 
function misha_register_and_enqueue() {
   
	wp_register_script( 'scripts', get_option('home')."/wp-content/plugins/plugin-rifa/js/scripts.js" );
	wp_enqueue_script( 'scripts' );

	wp_localize_script( 
		'scripts',
		'ambiente', // it is the name of JavaScript variable (object)
		array(
			'homeUrl' => get_option('home')+"/"
		)
	);

	wp_enqueue_style( 'style', get_option('home')."/wp-content/plugins/plugin-rifa/css/style.css" );
	wp_enqueue_style( 'woocommerce-rifa', get_option('home')."/wp-content/plugins/plugin-rifa/css/woocommerce.css" );
 
}

// REDIRECIONAR PARA O CHECKOUT AO CLICAR
//add_filter( 'woocommerce_add_to_cart_redirect', 'bbloomer_redirect_checkout_add_cart' );
 
//function bbloomer_redirect_checkout_add_cart() {
//   return wc_get_checkout_url();
//}


/**
*  ------------------------------------------------------------------------------------------------
*
*
*   INSERSÕES NO HTML
*
*
*  ------------------------------------------------------------------------------------------------
*/
add_action('wp_footer', 'modalRifaRodape'); 

function modalRifaRodape() { 
    echo '
       <div id="modalRifa">
          <div class="coluna-1" id="colunaUm">
               <p>Carregando...</p>
          </div>
          <div class="coluna-2" id="colunaDois">
               <img src="'.get_option('home').'/wp-content/plugins/plugin-rifa/images/loading.gif" style="width:32px;height:auto;" />
          </div>
       </div>'; 
}


/**
*  ------------------------------------------------------------------------------------------------
*
*
*   SHORTCODES
*
*
*  ------------------------------------------------------------------------------------------------
*/
function funcao_cotasrifa( $atts ) { 


	function coloque_zero($input0){
        
        if($input0>=100) return $input0;
		
		if($input0<=9):
		
		    $input2 = "00".$input0;
		
		else:

		  if($input0>=10 && $input0 <= 99) $input2 = "0".$input0;

		endif;

		return $input2;

	}

	$rifa = $atts['rifa'];
    
    $html = '

         <input type="hidden" id="idDoProdutoInput" value="'.$rifa.'" />
         <div class="cotas-disponiveis">
             
    ';


    $args = array( 'post_type' => 'product', 
                         'p' => $rifa, 

    );

    $html_prova = "";

    $loop = new WP_Query( $args );
    while ( $loop->have_posts() ) : $loop->the_post(); 
                          
              $cotas = get_field("numero_de_cotas");

              $reservas = get_user_meta( 1, get_the_ID());

              $a = 0;
              while($a<count($reservas)):
                
                 $estado = explode(",",$reservas[$a]);

                 $b = 0;
                 while($b<count($estado)):

                 	$html_prova = $html_prova.$estado[$b]."#";

                 	$b++;
                 endwhile;

              	$a++;
              endwhile;

              $negado = "não";
              $l = 0;

              while($l<$cotas):

                            $a = $l + 1;

                             $d = 0;
			              while($d<count($reservas)):
			                
			                 $estado = explode(",",$reservas[$d]);

                              $b = 0;
				              while($b<count($estado)):

				                 	$html_prova = $html_prova.$estado[$b]."#";

				                 	if($a==$estado[$b]):
				                        
					                      $negado = "sim";  

				                 	endif;

				                 	$b++;
				              
				              endwhile;

				              $d++;
				             endwhile;

				              if($negado!="sim"):

		                            $html = $html . '

						                <div class="form-check">
						                  <input class="form-check-input" type="checkbox" name="cotas" onchange="selecionarCotaRifa(this.value)" value="'.coloque_zero($a).'" id="cota'.coloque_zero($a).'">
						                  <label class="form-check-label label-um" for="cota'.$a.'">
						                    '.coloque_zero($a).'
						                  </label>
						                </div>

						            ';

				              endif;

				              $negado = "não";

              $l++;
              endwhile;

    endwhile; 
    wp_reset_query(); 
	

	$html = $html . '</div>';

    
    return $html;
		
}

add_shortcode('exibir_cotas', 'funcao_cotasrifa');




/**
*  ------------------------------------------------------------------------------------------------
*
*
*   SALVAR NO CARRINHO DE COMPRAS
*
*
*  ------------------------------------------------------------------------------------------------
*/
function salvar_carrinho() {
  
  $produto_id = intval( $_POST['id'] );
  $quantidade = intval( $_POST['qtd'] );
  
  global $woocommerce;
  
  // PRIMEIRO LIMPAMOS O CARRINHO
  $woocommerce->cart->empty_cart();

  // DEPOIS ADICIONADOS O PRODUTO E A QUANTIDADE NO CARRINHO
  $woocommerce->cart->add_to_cart($produto_id,$quantidade);
  
  // DEPOIS RETORNAMOS O TOTAL DO CARRINHO
  echo WC()->cart->total;

  wp_die();

}

add_action('wp_ajax_salvar_carrinho', 'salvar_carrinho');
add_action( 'wp_ajax_nopriv_salvar_carrinho', 'salvar_carrinho' );





/**
*  ------------------------------------------------------------------------------------------------
*
*
*   EXIBIR OS PRODUTOS COMPRADOS NAS COLUNAS DO ADMIN
*
*
*  ------------------------------------------------------------------------------------------------
*/
add_filter('manage_edit-shop_order_columns', 'misha_order_items_column' );
function misha_order_items_column( $order_columns ) {
    $order_columns['order_products'] = "Rifa";
    return $order_columns;
}
 
add_action( 'manage_shop_order_posts_custom_column' , 'misha_order_items_column_cnt' );
function misha_order_items_column_cnt( $colname ) {
	global $the_order; // the global order object
 
 	if( $colname == 'order_products' ) {
 
		// get items from the order global object
		$order_items = $the_order->get_items();
 
		if ( !is_wp_error( $order_items ) ) {
			foreach( $order_items as $order_item ) {
 
 				echo $order_item['quantity'] .' × <a href="' . admin_url('post.php?post=' . $order_item['product_id'] . '&action=edit' ) . '">'. $order_item['name'] .'</a><br />';
				// you can also use $order_item->variation_id parameter
				// by the way, $order_item['name'] will display variation name too
				$cotas = get_post_meta( $the_order->get_order_number(), 'billing_cotasescolhidas', true );
				echo "<br>Cotas: ".$cotas;
 
			}
		}
 
	}
 
}

/**
*  ------------------------------------------------------------------------------------------------
*
*
*   SALVAR AS RESERVAS DO USUARIO NO BANCO DE DADOS (DEIXA-LAS INDISPONIVEIS PARA OUTROAS COMPRAS)
*
*
*  ------------------------------------------------------------------------------------------------
*/
add_action( 'woocommerce_order_details_before_order_table', 'trusted_shops_thankyou', 15, 1 );
function trusted_shops_thankyou( $order ) {

   $id_comprador = $order->user_id;
   $items = $order->get_items(); 
	foreach ( $items as $item ) {

	        $product_name = $item->get_name();
	        $product_id = $item->get_product_id();
	        $qtd = $qtd + $item->get_quantity();
	        //echo "<br>CHAVE: ".$product_id;
	}

	$reservas = get_post_meta( $order->get_order_number(), 'billing_cotasescolhidas', true );

    // ID DO PRODUTO, ID DO USUARIO, RESERVAS (SALVAR AS RESERVAS DE UM CLIENTE)
	add_user_meta( $product_id, "diogenesjunior.ti@gmail.com", $reservas);
    
    // ID FAKE, ID DO PRODUTO, RESERVAS (SALVAR DISPONIBILDIADES)
	add_user_meta( "1", $product_id, $reservas);


}


/**
*  ------------------------------------------------------------------------------------------------
*
*
*   FUNÇÕES GERAIS
*
*
*  ------------------------------------------------------------------------------------------------
*/
function funcao_buscar_reservas( $atts ) { 

	$id_produto = $atts['produto'];
    
    $html = '
       
       <form method="post" action="">
           <input type="email" name="emailConsultaBuscarReservas" placeholder="Digite seu e-mail" required style="border:1px solid #ccc !important;height:38px;box-shadow:none;" />
       </form>


    '; 

    if($_POST["emailConsultaBuscarReservas"]):
          
          $usuarios = get_user_by( 'email', $_POST["emailConsultaBuscarReservas"] );

          if($usuarios!=""):

		          $id = $usuarios->ID;

		          $html = $html.'
		             
		             <p style="padding-top:12px;">Usuário: '.$_POST["emailConsultaBuscarReservas"].'</p>
		             <p><b>Suas cotas:</b></p><div class="cotas-disponiveis">

		          ';

		          $reservas = get_user_meta( $id, $id_produto);

          endif;

          $a = 0;

       
        

          while($a<count($reservas)):

          	$temp = explode(",",$reservas[$a]);
          	$c = 0;

          	while($c<count($temp)):

          		                if($temp[$c]!=""):

          		                    $html = $html . '

						                <div class="form-check">
						                  <input class="form-check-input" type="checkbox" name="cotas" value="'.$temp[$c].'" id="cota'.$temp[$c].'">
						                  <label style="background:#666 !important;color:#fff !important;" class="form-check-label label-um" for="cota'.$temp[$c].'">
						                    '.$temp[$c].'
						                  </label>
						                </div>

						            ';

						        endif;

          		$c++;
          	endwhile;

          	

          	$a++;
          endwhile;

          $html = $html . '</div>';

    endif;
    
    //
    //add_user_meta( "1111", "diogenesjunior.ti@gmail.com", "12,13,14,15" );
    //$html = get_user_meta( "1111", "diogenesjunior.ti@gmail.com" );

    //return print_r($html);



    return $html;
		
}

add_shortcode('buscar_reservas', 'funcao_buscar_reservas');



/**
*  ------------------------------------------------------------------------------------------------
*
*
*   PAGE TEMPLATES
*
*
*  ------------------------------------------------------------------------------------------------
*/
class PageTemplater {

	/**
	 * A reference to an instance of this class.
	 */
	private static $instance;

	/**
	 * The array of templates that this plugin tracks.
	 */
	protected $templates;

	/**
	 * Returns an instance of this class.
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new PageTemplater();
		}

		return self::$instance;

	}

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	private function __construct() {

		$this->templates = array();


		// Add a filter to the attributes metabox to inject template into the cache.
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {

			// 4.6 and older
			add_filter(
				'page_attributes_dropdown_pages_args',
				array( $this, 'register_project_templates' )
			);

		} else {

			// Add a filter to the wp 4.7 version attributes metabox
			add_filter(
				'theme_page_templates', array( $this, 'add_new_template' )
			);

		}

		// Add a filter to the save post to inject out template into the page cache
		add_filter(
			'wp_insert_post_data',
			array( $this, 'register_project_templates' )
		);


		// Add a filter to the template include to determine if the page has our
		// template assigned and return it's path
		add_filter(
			'template_include',
			array( $this, 'view_project_template')
		);


		// Add your templates to this array.
		$this->templates = array(
			'templates/teste.php' => 'Página em branco',
		);

	}

	/**
	 * Adds our template to the page dropdown for v4.7+
	 *
	 */
	public function add_new_template( $posts_templates ) {
		$posts_templates = array_merge( $posts_templates, $this->templates );
		return $posts_templates;
	}

	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 */
	public function register_project_templates( $atts ) {

		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list.
		// If it doesn't exist, or it's empty prepare an array
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		}

		// New cache, therefore remove the old one
		wp_cache_delete( $cache_key , 'themes');

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;

	}

	/**
	 * Checks if the template is assigned to the page
	 */
	public function view_project_template( $template ) {
		// Return the search template if we're searching (instead of the template for the first result)
		if ( is_search() ) {
			return $template;
		}

		// Get global post
		global $post;

		// Return template if post is empty
		if ( ! $post ) {
			return $template;
		}

		// Return default template if we don't have a custom one defined
		if ( ! isset( $this->templates[get_post_meta(
			$post->ID, '_wp_page_template', true
		)] ) ) {
			return $template;
		}

		// Allows filtering of file path
		$filepath = apply_filters( 'page_templater_plugin_dir_path', plugin_dir_path( __FILE__ ) );

		$file =  $filepath . get_post_meta(
			$post->ID, '_wp_page_template', true
		);

		// Just to be safe, we check if the file exist first
		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo $file;
		}

		// Return template
		return $template;

	}

}
add_action( 'plugins_loaded', array( 'PageTemplater', 'get_instance' ) );

		



?>