<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Easy_WhatsApp_Setting')) {
    class Easy_WhatsApp_Setting {
		
		private $ew_testing_page;

        public function __construct() {
            add_action('init', array($this, 'register_settings'));
            add_action('admin_menu', array($this, 'register_menu_page'));

			$this->$ew_testing_page = new Easy_WhatsApp_Testing_Page();
        }

        public function register_settings() {
            
            // General settings
            $settings = [
                'user_access_token',
                'whatsapp_api_version',
                'phone_number_id',
				'coupon_code',
				'coupon_code_template',
                'order_cancel_status',
                'order_cancel_template',
                'order_received_status',
                'order_received_template',
                'order_shipped_status',
                'order_shipped_template',
                'order_delivered_status',
                'order_delivered_template'
            ];

            foreach ($settings as $setting) {
                register_setting('easy-whatsapp-settings-group', $setting);
            }
        }

        public function register_menu_page() {

            add_menu_page('EasyConnect Dashboard', 'EasyConnect', 'manage_options', 'easy-connect-main', array($this, 'dashboard_page'), 'dashicons-whatsapp', 7 );
			add_submenu_page('easy-connect-main','Test massages','Test Massages','manage_options','easy-connect-test-msg', array($this->$ew_testing_page, 'test_massages_page') );
        }
		
        public function dashboard_page() {
            ?>
            <div class="wrap">
                <h1>EasyConnect WhatsApp API Settings</h1>

                <!-- form for setting -->
                <form method="post" action="options.php">
                    <?php settings_fields('easy-whatsapp-settings-group'); ?>
                    <?php do_settings_sections('easy-whatsapp-settings-group'); ?>
                    <table class="form-table">	
						
						<!-- User access token -->
						<tr valign="top">
							<th scope="row">User access token</th>
							<td><input type="text" name="user_access_token" placeholder="EAAxxxxx" value="<?php echo esc_attr(get_option('user_access_token')); ?>" />
							</td>
						</tr>
						
						<!-- WhatsApp API version -->
						<tr valign="top">
							<th scope="row">WhatsApp API version</th>
							<td><input type="text" name="whatsapp_api_version" placeholder="v19.0" value="<?php echo esc_attr(get_option('whatsapp_api_version')); ?>" />
							</td>
						</tr>
						
						<!-- Phone number ID -->
						<tr valign="top">
							<th scope="row">Phone number ID</th>
							<td><input type="text" name="phone_number_id" placeholder="33xx2643xx93x44" value="<?php echo esc_attr(get_option('phone_number_id')); ?>" />
							</td>
						</tr>
						
						<!-- Dropdown for all coupon codes -->
						<?php
						$coupon_codes = get_posts(array(
							'post_type' => 'shop_coupon',
							'posts_per_page' => -1,
						));
						echo '<tr valign="top">
								<th scope="row">After Review Message</th>
								<td><select name="coupon_code">';
									echo '<option value="">-- Select Coupon Code --</option>';
									foreach ($coupon_codes as $coupon) {
										$selected = (get_option('coupon_code') == $coupon->post_title) ? 'selected' : '';
										echo '<option value="' . esc_attr($coupon->post_title) . '" ' . $selected . '>' . esc_html($coupon->post_title) . '</option>';
									}
									echo '</select>';

									// Coupon code template
									echo '<input type="text" name="coupon_code_template" placeholder="coupon_template" value="' . esc_attr(get_option('coupon_code_template')) . '" />
								</td>
							</tr>';
			
// 						order status and templat
                        $statuses = [
                            'order_received' => 'Order received status and template',
                            'order_shipped' => 'Order shipped status and template',
                            'order_delivered' => 'Order delivered status and template',
							'order_cancel' => 'Order cancel status and template',
                        ];

                        foreach ($statuses as $status => $label) {
                            echo '<tr valign="top">
                                    <th scope="row">' . esc_html($label) . '</th>
                                    <td>
                                        <select name="' . esc_attr($status) . '_status">
                                            <option value="">-- Select Status --</option>';
                                            if (function_exists('wc_get_order_statuses')) {
                                                $order_statuses = wc_get_order_statuses();
                                                $selected_status = get_option($status . '_status');
                                                foreach ($order_statuses as $order_status => $status_label) {
                                                    $selected = selected($order_status, $selected_status, false);
                                                    echo '<option value="' . esc_attr($order_status) . '" ' . $selected . '>' . esc_html($status_label) . '</option>';
                                                }
                                            } else {
                                                echo '<option value="" disabled>WooCommerce not active</option>';
                                            }
                            echo ' </select>
                                        <input type="text" name="' . esc_attr($status) . '_template" placeholder="hello_world" value="' . esc_attr(get_option($status . '_template')) . '" />
                            
                                    </td>
                                  </tr>';
                        }
                        ?>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>
            <?php
        }
    }
}

