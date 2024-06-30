<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Easy_Dash_Function')) {
	class Easy_Dash_Function {

		public function calculate_cogs_for_order($order_id) {
			$order = wc_get_order($order_id);

			if (!$order) {
				return array(
					'success' => false,
					'message' => 'Order not found',
				);
			}

			$items = $order->get_items();

			if (empty($items)) {
				return array(
					'success' => false,
					'message' => 'Order has no items',
				);
			}

			$total_cogs = 0;

			foreach ($items as $item_id => $item) {
				$product_id = $item->get_product_id();
				$quantity = $item->get_quantity();
				$cogs = get_post_meta($product_id, '_cogs', true);

				if ($cogs !== '') {
					$item_cogs = floatval($cogs) * $quantity;
					$total_cogs += $item_cogs;
				}
			}

			return array(
				'success' => true,
				'message' => 'COGS calculated successfully',
				'result'  => $total_cogs
			);
		}


	}
}

?>