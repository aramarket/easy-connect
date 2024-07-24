<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Easy_WhatsApp_Function')) {
	class Easy_WhatsApp_Function {

		public function es_wa_simplify_order_status($status) {
			// Remove 'wc-' prefix if present
			if (strpos($status, 'wc-') === 0) {
				return substr($status, 3);
			}
			// If 'wc-' prefix is not present, return status as is
			return $status;
		}

		public function es_wa_extract_phone_number($input) {
			// Convert input to string if it is not already
			$input = strval($input);

			// Remove any non-numeric characters
			$cleaned_number = preg_replace('/[^0-9]/', '', $input);

			// If the cleaned number is more than 10 digits, get the last 10 digits
			if (strlen($cleaned_number) > 10) {
				$cleaned_number = substr($cleaned_number, -10);
			}

			// Check if the cleaned input has fewer than 10 digits
			if (strlen($cleaned_number) < 10) {
				return array(
					'success' => false,
					'message' => 'Invalid phone number'
				);
			}

			return array(
				'success' => true,
				'message' => 'Phone number cleaned successfully',
				'result'  => $cleaned_number
			);
		}
		
		public function remove_special_characters($input) {
			// Convert input to string if it is not already
			$input = strval($input);

			// Remove any character that is not a letter, number, or space
			$cleaned_input = preg_replace('/[^a-zA-Z0-9\s]/', '', $input);

			// Check if the cleaned input is empty
			if (strlen($cleaned_input) == 0) {
				return array(
					'success' => false,
					'message' => 'Input only contains special characters'
				);
			}

			return array(
				'success' => true,
				'message' => 'Special characters removed successfully',
				'result'  => $cleaned_input
			);
		}

		
		public function handleWhatsAppResponse($response) {
			// Decode the JSON response
			$data = json_decode($response, true);

			// Check if there's an error in the response
			if (isset($data['error'])) {
				$error_message = $data['error']['message'];
				return array(
					'success' => false,
					'message' => $error_message,
				);
			}

			// Check if the message status is accepted
			if (isset($data['messages'][0]['message_status']) && $data['messages'][0]['message_status'] === 'accepted') {
				$wa_id = $data['contacts'][0]['wa_id'];
				return array(
					'success' => true,
					'message' => 'WhatsApp message successfully Send',
					'result'  => $wa_id
				);
			}

			// Default return if no condition is met
			return array(
				'success' => false,
				'message' => 'Unknown response format.',
			);
		}
	}
}

?>