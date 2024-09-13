<?php
/**
* Plugin Name: product-serial
* Plugin URI: https://www.your-site.com/
* Description: Product with serial numbers for woocommerce
* Version: 0.1
* Author: Vaisor
* Author URI: https://www.your-site.com/
**/
 
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'Product_Serial' ) ) exit;

class Product_Serial {
	public function __construct() {
		add_filter('woocommerce_product_data_tabs', array($this, 'product_data_tabs'));
		add_filter( 'woocommerce_product_data_panels', array( $this, 'product_data_panel' ) );
		add_action( 'woocommerce_admin_process_product_object', array( $this, 'product_data_save'), 10, 1 );
	}
	public function product_data_tabs($tabs) {
		$tabs['serial'] = array(
			'label' => 'Numery seryjne',
			'target' => 'product-serial-panel',
		);
		return $tabs;
	}
	public function product_data_panel() {
		global $post;
		?>
		<div id="product-serial-panel" class="panel woocommerce-product-serial-panel woocommerce_options_panel">
			<?php
			woocommerce_wp_text_input(
				array(
					'id' => '_serial_number',
					'type' => 'text',
					'placeholder' => 'Numery seryjne',
					'label' => 'Numer seryjny',
					'desc_tip' => 'true',
					'description' => 'Wpisz numery seryjne oddzielone |',
				)
			);
			?>
		</div>


		<?php
	}
	public function product_data_save( $product ) {
		if ( isset( $_POST['_serial_number'] ) ) {        
			$product->update_meta_data( '_serial_number', sanitize_text_field( $_POST['_serial_number'] ) );
		}
	}
}
new Product_Serial();
if ( !class_exists( 'Product_Serial_Display' ) ) exit;
class Product_Serial_Display {

    public function __construct() {
        // Dodanie niestandardowej wiadomości do podglądu zamówienia
		add_action('woocommerce_admin_order_item_headers', array( $this, 'my_woocommerce_admin_order_item_headers'));
		add_action('woocommerce_admin_order_item_values', array( $this, 'my_woocommerce_admin_order_item_values'), 10, 3);
    }
	// Add custom column headers here
	public function my_woocommerce_admin_order_item_headers() {
		// set the column name
		$column_name = 'Test Column';

		// display the column name
		echo '<th>' . $column_name . '</th>';
	}

	// Add custom column values here
	public function my_woocommerce_admin_order_item_values($_product, $item, $item_id = null) {
		// get the post meta value from the associated product
		$serial_number = get_post_meta($_product->post->ID, '_serial_number', true);
		//$serial_number = $item->get_meta('_serial_number');

		// display the value
		echo '<td>' . $serial_number . '</td>';
	}
}

// Inicjalizacja klasy
new Product_Serial_Display();