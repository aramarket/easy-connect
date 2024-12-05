<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('EW_After_Review_Functions')) {
    class EW_After_Review_Functions {
        public $es_wa_phone_number_id;
        public $es_wa_whatsapp_api_version;
        public $es_wa_user_access_token;

        public $es_wa_after_review_coupon;
        public $es_wa_after_review_template;

        // Property to hold the Easy_WhatsApp_Function instance
        private $ew_function;

        public function __construct() {
            $this->es_wa_phone_number_id = esc_attr(get_option('phone_number_id'));
            $this->es_wa_whatsapp_api_version = esc_attr(get_option('whatsapp_api_version'));
            $this->es_wa_user_access_token = esc_attr(get_option('user_access_token'));

            $this->es_wa_after_review_coupon = esc_attr(get_option('coupon_code'));
            $this->es_wa_after_review_template = esc_attr(get_option('coupon_code_template'));

            // Instantiate the Easy_WhatsApp_Function class
            $this->ew_function = new Easy_WhatsApp_Function();

        }

        public function run() {
            add_action('comment_post', array($this, 'es_whatsapp_add_review_function'), 10, 3);
        }

        public function es_whatsapp_add_review_function($comment_id, $comment_approved, $commentdata) {
            if ($comment_approved && $commentdata['comment_type'] === 'review') {
                // Get the user email from comment data
                $user_email = $commentdata['comment_author_email'];
                // Check if the user exists
                if ($user_email) {
                    $response = $this->send_after_review_message($user_email);
                    // Uncomment below if you want to add a note to a specific order (e.g., order ID 25391)
                    // $order = wc_get_order(25391);
                    // $order->add_order_note('Reviewed Order ' . $response['message']);
                }
            }
        }

        public function send_after_review_message($user_email) {
			
            $user_info = get_user_by('email', $user_email);
            if (!$user_info) {
                return array(
                    'success' => false,
                    'message' => "User not found",
                );
            }

            $user_id = $user_info->ID;
            $user_name = $user_info->display_name;
            
            // Get the user's billing phone
            $billing_phone = get_user_meta($user_id, 'billing_phone', true);
            
            $phone_number = $this->ew_function->es_wa_extract_phone_number($billing_phone);
            if (!$phone_number['success']) {
                return array(
                    'success' => false,
                    'message' => $phone_number['message'],
                );
            }

            $coupon_code = $this->es_wa_after_review_coupon;
            $template_name = $this->es_wa_after_review_template;
            $recipient_phone_number = $phone_number['result'];
			
			// Get the coupon object
			$coupon = new WC_Coupon($coupon_code);
			
			// Check if the coupon is valid
			if (!$coupon->get_id()) {
				return array(
					'success' => false,
					'message' => "Invalid coupon code",
				);
			}

			// Retrieve the discount type and amount
			$discount_type = $coupon->get_discount_type();
			$discount_value = $coupon->get_amount();

			// Determine the discount message based on the type and value
			if ($discount_type == 'percent') {
				$discount_message = $discount_value . '%';
			} elseif ($discount_type == 'fixed_cart' || $discount_type == 'fixed_product') {
				$discount_message = 'Flat ' . $discount_value;
			} else {
				$discount_message = $coupon_code;
			}
			
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
									"link": "https://aramarket.in/wp-content/uploads/order-review.png"
									}
								}
								]
							},
							{
								"type": "body",
								"parameters": [
									{
										"type": "text",
										"text": "' . $user_name . '"
									},
									{
										"type": "text",
										"text": "' . $coupon_code . '"
									},
									{
										"type": "text",
										"text": "' . $discount_message . '"
									}
								]
							},
							{
								"type": "button",
								"sub_type": "COPY_CODE",
								"index": 0,
								"parameters": [
									{
										"type": "coupon_code",
										"coupon_code": "' . $coupon_code . '"
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
    }
}

?>