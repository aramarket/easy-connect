<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Easy_WhatsApp_Testing_Page')) {
    class Easy_WhatsApp_Testing_Page {
		// Property to hold the Easy_WhatsApp_Function instance
		private $ew_function;
		private $ew_order_status_function;
		private $ew_after_review_function;

		public function __construct() {
			// Instantiate the Easy_WhatsApp_Function class
			$this->ew_function = new Easy_WhatsApp_Function();
			$this->ew_order_status_function = new EW_Order_Status_Functions();
			$this->ew_after_review_function = new EW_After_Review_Functions();
		}
		public function test_massages_page(){
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				if(isset($_POST['test_send_whatsapp_message'])){
					// Check if the form is submitted
					$test_phone = sanitize_text_field($_POST['test_phone_number']);
					$test_template = sanitize_text_field($_POST['test_phone_template']);

					$response = $this->ew_order_status_function->send_test_whatsapp_message($test_phone, $test_template);
					if ($response['success']) {
						echo '<div class="notice notice-success is-dismissible"><p>Test message sent successfully - ' . $response['result']. ' </p></div>';
					} else {
						echo '<div class="notice notice-error is-dismissible"><p>Error: ' . $response['message'] . '</p></div>';
					}
				} elseif (isset($_POST['test_after_review_message'])) {
					// Check if the form is submitted
					$email = sanitize_text_field($_POST['test_after_review_email']);

					$response = $this->ew_after_review_function->send_after_review_message($email);
					
					if ($response['success']) {
						echo '<div class="notice notice-success is-dismissible"><p>Test message sent successfully - ' . $response['result']. ' </p></div>';
					} else {
						echo '<div class="notice notice-error is-dismissible"><p>Error: ' . $response['message'] . '</p></div>';
					}
				} elseif (isset($_POST['test_product_status_message'])) {
					// Check if the form is submitted
					$order_id = sanitize_text_field($_POST['test_order_id']);
					$get_order_status = sanitize_text_field($_POST['order_status']);
					$order_status = $this->ew_function->es_wa_simplify_order_status($get_order_status);

					$order = wc_get_order($order_id);
					if($order){
						$response = $this->ew_order_status_function->handle_order_statuss_message($order_status, $order);
						if ($response['success']) {
							echo '<div class="notice notice-success is-dismissible"><p>Test message sent successfully - ' . $response['result']. ' </p></div>';
						} else {
							echo '<div class="notice notice-error is-dismissible"><p> Error: ' . $response['message'] . '</p></div>';
						}
					}else{
						echo '<div class="notice notice-error is-dismissible"><p> Error: Order Not Found</p></div>';
					}
				}
			}
			?>
            <div class="wrap">
                <h1>Test WhatsApp API Massages</h1>

                <!-- form for setting -->
                <form method="post" action="">
                    <table class="form-table">
						
						<!-- Test phone number -->
						<tr valign="top">
							<th scope="row">Test WhatsApp API Connection</th>
							<td><input type="text" name="test_phone_number" placeholder="Enter test phone" value="" />
								<input type="text" name="test_phone_template" placeholder="hello_world" value="hello_world" />
								<input type="submit" name="test_send_whatsapp_message" class="button-primary" value="Test Send Message">
							</td>
						</tr>
						
						<!-- Test After Review Massage -->
						<tr valign="top">
							<th scope="row">Test After Review Message</th>
							<td><input type="text" name="test_after_review_email" placeholder="Enter customer email" />
								<input type="submit" name="test_after_review_message" class="button-primary" value="Test After Review Massage">
							</td>
						</tr>
						
						<!-- Test After Review Massage -->
						<tr valign="top">
							<th scope="row">Test Product Status Message</th>
							<td><input type="text" name="test_order_id" placeholder="Enter order id"/>
								<select name="order_status">
									<option value="">-- Select Coupon Code --</option>
									<option value="<?php echo esc_attr(get_option('order_received_status')); ?>">Order placed</option>
									<option value="<?php echo esc_attr(get_option('order_shipped_status')); ?>">Order shipped</option>
									<option value="<?php echo esc_attr(get_option('order_delivered_status')); ?>">Order delivered</option>
									<option value="<?php echo esc_attr(get_option('order_cancel_status')); ?>">Order cancel</option>
								</select>
								<input type="submit" name="test_product_status_message" class="button-primary" value="Test Product Status Message">
							</td>
						</tr>
					</table>
				</form>
			</div>
		<?php
		}
    }
}

