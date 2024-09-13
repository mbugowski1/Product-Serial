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
}
new Product_Serial();
if ( !class_exists( 'Product_Serial_Display' ) ) exit;
class Product_Serial_Display {

    public function __construct() {
        // Dodanie niestandardowej wiadomości do podglądu zamówienia
        add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'display_serial_number' ) );
    }

    // Funkcja wyświetlająca wiadomość
    public function display_serial_number( $order ) {
        // Możesz dodać tutaj dowolną wiadomość lub dynamiczne dane, np. metadane zamówienia
        echo '<div class="custom-order-message">';
        echo '<h3>' . __( 'Niestandardowa wiadomość', 'woocommerce' ) . '</h3>';
        echo '<p>' . __( 'To jest niestandardowa wiadomość wyświetlana w szczegółach zamówienia.', 'woocommerce' ) . '</p>';
        echo '</div>';
    }
}

// Inicjalizacja klasy
new Product_Serial_Display();