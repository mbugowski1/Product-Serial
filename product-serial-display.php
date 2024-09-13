<?php
if ( !class_exists( 'Product_Serial_Display' ) ) exit;
class Product_Serial_Display {

    public function __construct() {
        // Dodanie niestandardowej wiadomości do podglądu zamówienia
		add_action('woocommerce_admin_order_item_headers', array( $this, 'print_serial_number_column_name'));
		add_action('woocommerce_admin_order_item_values', array( $this, 'print_serial_number_column_value'), 10, 3);
    }
	// Add custom column headers here
	public function print_serial_number_column_name() {
		// set the column name
		$column_name = 'Numer seryjny';

		// display the column name
		echo '<th>' . $column_name . '</th>';
	}

	// Add custom column values here
	public function print_serial_number_column_value($_product, $item, $item_id = null) {
		// get the post meta value from the associated product
		if ( !($_product && is_a( $_product, 'WC_Product' ) ) ) return;
		//$serial_number = get_post_meta($_product->post->ID, '_serial_number', true);
		$serial_number = $item->get_meta('_serial_number');

		// display the value
		echo '<td>' . $serial_number . '</td>';
	}
}

// Inicjalizacja klasy
