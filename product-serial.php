<?php
/**
* Plugin Name: Product Serial
* Plugin URI: https://github.com/mbugowski1/Product-Serial
* Description: Product with serial numbers for woocommerce
* Version: 0.1.2
* Author: vaisor
* Author URI: https://github.com/mbugowski1
**/
 
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

require_once(__DIR__ . '/product-serial-display.php');
if ( !class_exists( 'Product_Serial' ) ) exit;

class Product_Serial {
	public function __construct() {
		add_filter( 'woocommerce_product_data_tabs', array($this, 'product_data_tabs'));
		add_filter( 'woocommerce_product_data_panels', array( $this, 'product_data_panel' ) );
		add_action( 'woocommerce_admin_process_product_object', array( $this, 'product_data_save'), 10, 1 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'save_serial_number_to_order_item' ), 11, 4);
		add_action( 'woocommerce_before_save_order_item', array($this, 'update_serial_number_to_order_item'), 10, 1 );
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
	public function save_serial_number_to_order_item( $item, $cart_item_key, $values, $order ) {
		$product_id = $values['product_id'];
		$serial_number = get_post_meta($product_id, '_serial_number', true);
		if ( !empty( $serial_number ) ) {
			$order_item_from = $values['wcrp_rental_products_rent_from'];
			$order_item_to = $values['wcrp_rental_products_rent_to'];
			$return_threshold = $values['wcrp_rental_products_return_days_threshold'];
			
			$item->update_meta_data( '_serial_number', $this->available_serial_numbers($serial_number, $order_item_from, $order_item_to, $return_threshold )[0] );
		}
	}
	public function update_serial_number_to_order_item( $item ) {
		error_log("Wywolanie uwaga");
		if($item->meta_exists("_serial_number")) return;
		$product_id = $item->get_product_id();
		if( empty($product_id) ) return;
		$serial_number = get_post_meta($product_id, '_serial_number', true);
		if ( !empty( $serial_number ) ) {
			$order_item_from = $item->get_meta('wcrp_rental_products_rent_from');
			$order_item_to = $item->get_meta('wcrp_rental_products_rent_to');
			$return_threshold = $item->get_meta('wcrp_rental_products_return_days_threshold');
			
			$item->update_meta_data( '_serial_number', $this->available_serial_numbers($serial_number, $order_item_from, $order_item_to, $return_threshold )[0] );
		}
	}
	

	private function occupied_serial_numbers($order_from, $order_to, $return_threshold) {
		$args = array(
			'post_type'  => 'shop_order',
			'post_status' => 'wc-processing', // Status 'w trakcie realizacji'
			'posts_per_page' => -1, // Pobierz wszystkie zamówienia
		);
		$query = new WP_Query($args);

		$occupied = array();
		if ($query->have_posts()) {
			foreach ($query->posts as $order_post) {
				$order = wc_get_order($order_post->ID);
		
				foreach ($order->get_items() as $item_id => $item) {
					$used_serial_number = $item->get_meta('_serial_number');
					$order_item_from = wc_get_order_item_meta( $item_id, 'wcrp_rental_products_rent_from', true );
					$order_item_to = wc_get_order_item_meta( $item_id, 'wcrp_rental_products_rent_to', true );
					
					//Add return threshold
					$days_to_add = intval($return_threshold);
					$date = new DateTime($order_item_to);
					$date->add(new DateInterval('P' . $days_to_add . 'D'));
					$order_item_to = $date->format('Y-m-d');

					error_log('Od ' . $order_item_from . " Do " . $order_item_to);

					if (empty ($used_serial_number) || empty ($order_item_from) || empty ($order_item_to))
						continue;
					if(($order_item_from <= $order_to) && ($order_item_to >= $order_from))
						$occupied[] = $used_serial_number;
				}
			}
		}
		return $occupied;
	}
	private function available_serial_numbers($serial_number, $order_from, $order_to, $return_threshold) {
		$serials = str_replace(' ', '', $serial_number);
		$serials = explode('|', $serials);
		$occupied = $this->occupied_serial_numbers($order_from, $order_to, $return_threshold);
		$available = array_diff($serials, $occupied);
		$available = array_values($available);
		return $available;
	}
}
new Product_Serial();
//new Product_Serial_Display();