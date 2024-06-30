<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('EW_Order_Status_Functions')) {
	class EW_Order_Status_Functions {
		public $es_wa_phone_number_id;
		public $es_wa_whatsapp_api_version;
		public $es_wa_user_access_token;
		public $es_wa_cancel_status_name;
		public $es_wa_received_status_name;
		public $es_wa_shipped_status_name;
		public $es_wa_delivered_status_name;
		public $es_wa_cancel_template_name;
		public $es_wa_received_template_name;
		public $es_wa_shipped_template_name;
		public $es_wa_delivered_template_name;

		// Property to hold the Easy_WhatsApp_Function instance
		private $ew_function;

		public function __construct() {
			$this->es_wa_phone_number_id = esc_attr(get_option('phone_number_id'));
			$this->es_wa_whatsapp_api_version = esc_attr(get_option('whatsapp_api_version'));
			$this->es_wa_user_access_token = esc_attr(get_option('user_access_token'));

			$this->es_wa_cancel_status_name = esc_attr(get_option('order_cancel_status'));
			$this->es_wa_received_status_name = esc_attr(get_option('order_received_status'));
			$this->es_wa_shipped_status_name = esc_attr(get_option('order_shipped_status'));
			$this->es_wa_delivered_status_name = esc_attr(get_option('order_delivered_status'));

			$this->es_wa_cancel_template_name = esc_attr(get_option('order_cancel_template'));
			$this->es_wa_received_template_name = esc_attr(get_option('order_received_template'));
			$this->es_wa_shipped_template_name = esc_attr(get_option('order_shipped_template'));
			$this->es_wa_delivered_template_name = esc_attr(get_option('order_delivered_template'));

			// Instantiate the Easy_WhatsApp_Function class
			$this->ew_function = new Easy_WhatsApp_Function();
		}
		
        public function run() {
            add_action('woocommerce_order_status_changed', array($this, 'send_whatsapp_message_status_changed'), 10, 4);
        }
		
		public function send_whatsapp_message_status_changed($order_id, $old_status, $new_status, $order) {
			$response = $this->handle_order_statuss_message($new_status, $order);
			$order->add_order_note($response['message'] . ' for ' . $new_status);
		}
		
		public function handle_order_statuss_message($order_status, $order) {
			$order_received_status 	= $this->ew_function->es_wa_simplify_order_status($this->es_wa_received_status_name);
			$order_shipped_status 	= $this->ew_function->es_wa_simplify_order_status($this->es_wa_shipped_status_name);
			$order_delivered_status = $this->ew_function->es_wa_simplify_order_status($this->es_wa_delivered_status_name);
			$order_cancel_status 	= $this->ew_function->es_wa_simplify_order_status($this->es_wa_cancel_status_name);
			
			$response = [];
			
			// Switch statement to handle different statuses
			switch ($order_status) {
				case $order_received_status:
					if (!empty($order_received_status)) {
						$response = $this->order_placed_whatsapp_message($order);
					} else {
						$response = array(
							'success' => false,
							'message' => 'Please select order placed status',
						);
					}
					break;
				case $order_shipped_status:
					if (!empty($order_shipped_status)) {
						$response = $this->order_shipped_whatsapp_message($order);
					} else {
						$response = array(
							'success' => false,
							'message' => 'Please select order shipped status',
						);
					}
					break;
				case $order_delivered_status:
					if (!empty($order_delivered_status)) {
						$response = $this->order_delivered_whatsapp_message($order);
					} else {
						$response = array(
							'success' => false,
							'message' => 'Please select order delivered status',
						);
					}
					break;
				case $order_cancel_status:
					if (!empty($order_cancel_status)) {
						$response = $this->send_cancel_whatsapp_message($order);
					} else {
						$response = array(
							'success' => false,
							'message' => 'Please select order cancel status',
						);
					}
					break;
			}
			return $response;
		}
		
		public function send_text_whatsapp_message($phone_number) {
			// You can modify these variables with test data
			$phone_number_id = esc_attr(get_option('phone_number_id'));
			$version = esc_attr(get_option('whatsapp_api_version'));
			// $recipient_phone_number = esc_attr(get_option('test_phone_number'));
			$recipient_phone_number = $phone_number;
			$access_token = esc_attr(get_option('user_access_token'));

			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => 'https://graph.facebook.com/'.$version.'/'.$phone_number_id.'/messages',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'POST',
			  CURLOPT_POSTFIELDS =>'{
				"messaging_product": "whatsapp",
				"recipient_type": "individual",
				"to": "' . $recipient_phone_number . '",
				"type": "text",
				"text": {
					"preview_url": false,
					"body": "text-message-content"
				}
			}',
			  CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Authorization: Bearer ' . $access_token
			  ),
			));

			$response = curl_exec($curl);

			curl_close($curl);
			return $response; // This line is just for testing, you might want to handle the response differently

			// Log response or handle errors
		}

		public function send_test_whatsapp_message($test_phone, $test_template) {
			$phone_number = $this->ew_function->es_wa_extract_phone_number($test_phone);

			if (!$phone_number['success']) {
				return array(
					'success' => false,
					'message' => $phone_number['message'],
				);
			}

			$recipient_phone_number = $phone_number['result'];
			$template_name = $test_template;
			
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => 'https://graph.facebook.com/'.$this->es_wa_whatsapp_api_version.'/'.$this->es_wa_phone_number_id.'/messages',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'POST',
			  CURLOPT_POSTFIELDS =>'{
				"messaging_product": "whatsapp",
				"to": "' . $recipient_phone_number . '",
				"type": "template",
				"template": {
					"name": "' . $template_name . '",
					"language": {
						"code": "en_US"
					}
				}
			}',
			  CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Authorization: Bearer ' . $this->es_wa_user_access_token
			  ),
			));
			
			$response = curl_exec($curl);
			$error = curl_error($curl);
			curl_close($curl);

			if ($error) {
				return array(
					'success' => false,
					'message' => $error,
				);
			}
			
			return $this->ew_function->handleWhatsAppResponse($response);
		}

		public function send_cancel_whatsapp_message($order) {
			$phone_number = $this->ew_function->es_wa_extract_phone_number($order->get_billing_phone());

			if (!$phone_number['success']) {
				return array(
					'success' => false,
					'message' => $phone_number['message'],
				);
			}

			$recipient_phone_number = $phone_number['result'];
			$template_name = $this->es_wa_cancel_template_name;
			$product_title = implode(", ", array_map(function($item) { 
				return $this->ew_function->remove_special_characters( $item['name'] )['result']; }, $order->get_items()));
			
			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => 'https://graph.facebook.com/'.$this->es_wa_whatsapp_api_version.'/'.$this->es_wa_phone_number_id.'/messages',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS =>'{
					"messaging_product": "whatsapp",
					"to": "' . $recipient_phone_number . '",
					"type": "template",
					"template": {
							"name": "' . $template_name . '",
							"language": {
								"code": "en"
							},
							"components": [
								{
									"type": "header",
									"parameters": [
									{
										"type": "image",
										"image": {
										"link": "https://aramarket.in/wp-content/uploads/cancel-order-flat-illustration-design-600nw-2043932627.png"
										}
									}
									]
								},
								{
									"type": "body",
									"parameters": [
										{
											"type": "text",
											"text": "'. $order->get_billing_first_name() .' '.$order->get_billing_last_name() . '"
										},
										{
											"type": "text",
											"text": "' . $order->get_id() . '"
										},
										{
											"type": "text",
											"text": "' . $product_title . '"
										},
										{
											"type": "text",
											"text": "' . $order->get_total() . '"
										}
									]
								}
							]
						}
				}',
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json',
					'Authorization: Bearer ' . $this->es_wa_user_access_token
				),
			));

			$response = curl_exec($curl);
			$error = curl_error($curl);
			curl_close($curl);

			if ($error) {
				return array(
					'success' => false,
					'message' => $error,
				);
			} 
			
			return $this->ew_function->handleWhatsAppResponse($response);
		}
		
		public function order_placed_whatsapp_message($order) {
			$phone_number = $this->ew_function->es_wa_extract_phone_number($order->get_billing_phone());
			if (!$phone_number['success']) {
				return array(
					'success' => false,
					'message' => $phone_number['message'],
				);
			}

			$recipient_phone_number = $phone_number['result'];
			$template_name = $this->es_wa_received_template_name;
			$product_title = implode(", ", array_map(function($item) { 
				return $this->ew_function->remove_special_characters( $item['name'])['result']; }, $order->get_items()));
			
			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => 'https://graph.facebook.com/'.$this->es_wa_whatsapp_api_version.'/'.$this->es_wa_phone_number_id.'/messages',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS =>'{
					"messaging_product": "whatsapp",
					"to": "' . $recipient_phone_number . '",
					"type": "template",
					"template": {
						"name": "' . $template_name . '",
						"language": {
							"code": "en"
						},
						"components": [
							{
								"type": "header",
								"parameters": [
								{
									"type": "image",
									"image": {
									"link": "https://aramarket.in/wp-content/uploads/Untitled-design-22.png"
									}
								}
								]
							},
							{
								"type": "body",
								"parameters": [
									{
										"type": "text",
										"text": "' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . '"
									},
									{
										"type": "text",
										"text": "' . $order->get_id() . '"
									},
									{
										"type": "text",
										"text": "' . $product_title . '"
									},
									{
										"type": "text",
										"text": "' . $order->get_total() . '"
									}
								]
							},
						]
					}
				}',
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json',
					'Authorization: Bearer ' . $this->es_wa_user_access_token
				),
			));

			$response = curl_exec($curl);
			$error = curl_error($curl);
			curl_close($curl);

			if ($error) {
				return array(
					'success' => false,
					'message' => $error,
				);
			}
			
			return $this->ew_function->handleWhatsAppResponse($response);
		}
		
		public function order_shipped_whatsapp_message($order) {
			$phone_number = $this->ew_function->es_wa_extract_phone_number($order->get_billing_phone());
			if (!$phone_number['success']) {
				return array(
					'success' => false,
					'message' => $phone_number['message'],
				);
			}

			$recipient_phone_number = $phone_number['result'];
			$template_name = $this->es_wa_shipped_template_name;
			$product_title = implode(", ", array_map(function($item) { 
				return $this->ew_function->remove_special_characters( $item['name'])['result']; }, $order->get_items()));
			
			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => 'https://graph.facebook.com/'.$this->es_wa_whatsapp_api_version.'/'.$this->es_wa_phone_number_id.'/messages',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS =>'{
					"messaging_product": "whatsapp",
					"to": "' . $recipient_phone_number . '",
					"type": "template",
					"template": {
						"name": "' . $template_name . '",
						"language": {
							"code": "en"
						},
						"components": [
							{
								"type": "header",
								"parameters": [
								{
									"type": "image",
									"image": {
									"link": "https://aramarket.in/wp-content/uploads/order-shipped.png"
									}
								}
								]
							},
							{
								"type": "body",
								"parameters": [
									{
										"type": "text",
										"text": "' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . '"
									},
									{
										"type": "text",
										"text": "' . $order->get_id() . '"
									},
									{
										"type": "text",
										"text": "' . $product_title . '"
									},
									{
										"type": "text",
										"text": "' . $order->get_total() . '"
									}
								]
							},
							{
								"type": "button",
								"sub_type": "url",
								"index": 0, 
								"parameters": [
									{
										"type": "text",
										"text": "' . $order->get_id() . '"
									}
								]
							}
						]
					}
				}',
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json',
					'Authorization: Bearer ' . $this->es_wa_user_access_token
				),
			));

			$response = curl_exec($curl);
			$error = curl_error($curl);
			curl_close($curl);

			if ($error) {
				return array(
					'success' => false,
					'message' => $error,
				);
			} 
			
			return $this->ew_function->handleWhatsAppResponse($response);
		}
		
		public function order_delivered_whatsapp_message($order) {
			$phone_number = $this->ew_function->es_wa_extract_phone_number($order->get_billing_phone());
			if (!$phone_number['success']) {
				return array(
					'success' => false,
					'message' => $phone_number['message'],
				);
			}

			$recipient_phone_number = $phone_number['result'];
			$template_name = $this->es_wa_delivered_template_name;
			$product_title = implode(", ", array_map(function($item) { 
				return $this->ew_function->remove_special_characters( $item['name'])['result']; }, $order->get_items()));
			
			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => 'https://graph.facebook.com/'.$this->es_wa_whatsapp_api_version.'/'.$this->es_wa_phone_number_id.'/messages',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS =>'{
					"messaging_product": "whatsapp",
					"to": "' . $recipient_phone_number . '",
					"type": "template",
					"template": {
						"name": "' . $template_name . '",
						"language": {
							"code": "en"
						},
						"components": [
							{
								"type": "header",
								"parameters": [
								{
									"type": "image",
									"image": {
									"link": "https://aramarket.in/wp-content/uploads/order-delivered.png"
									}
								}
								]
							},
							{
								"type": "body",
								"parameters": [
									{
										"type": "text",
										"text": "' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . '"
									},
									{
										"type": "text",
										"text": "' . $order->get_id() . '"
									},
									{
										"type": "text",
										"text": "' . $product_title . '"
									}
								]
							},
						]
					}
				}',
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json',
					'Authorization: Bearer ' . $this->es_wa_user_access_token
				),
			));

			$response = curl_exec($curl);
			$error = curl_error($curl);
			curl_close($curl);

			if ($error) {
				return array(
					'success' => false,
					'message' => $error,
				);
			} 
			
			return $this->ew_function->handleWhatsAppResponse($response);
		}
	}
}

?>