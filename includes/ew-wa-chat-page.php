<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Easy_WhatsApp_Chat_Page')) {
    class Easy_WhatsApp_Chat_Page {

		public function __construct() {
			add_action('init', [$this, 'register_settings']);
			add_action('admin_menu', array($this, 'register_menu_page'));
			add_action('wp_footer', array($this, 'add_whatsapp_chat_icon'));
		}
		
		public function register_settings() {
            // Shiprocket settings
            register_setting('easy-connect-wci-group', 'wci_enable');
            register_setting('easy-connect-wci-group', 'wci_number');
        }
		
		public function register_menu_page(){
			add_submenu_page('easy-connect-main','WhatsApp Chat Icon','WhatsApp Chat Icon','manage_options','whatsapp-chat-icon', array($this, 'whatsapp_chat_icon_page') );
        }
		
		public function whatsapp_chat_icon_page() {
			?>
				<div class="wci-settings-wrap">
                <h1>Whatsapp Chat Icon Settings</h1>
                <form method="post" action="options.php">
                    <?php settings_fields('easy-connect-wci-group'); ?>
                    <?php do_settings_sections('easy-connect-wci-group'); ?>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">WhatsApp chat icon Enable/Disable</th>
                            <td>
                                <input type="checkbox" name="wci_enable" value="1" <?php checked(get_option('wci_enable'), 1); ?> /> Enable WhatsApp chat icon on website right corner
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Phone number*</th>
                            <td>
                          <input type="text" name="wci_number" placeholder="+918265xxxxxx" value="<?php echo esc_attr(get_option('wci_number')); ?>" />
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>
			<?php
		}

		// Hook to enqueue styles and scripts
		public function add_whatsapp_chat_icon() {
			if(get_option('wci_enable')) {
				$phone = get_option('wci_number');
// 				$phone = '+919368994493';
				$whatsappLogo = EASY_CONNECT_URL . 'EasyConnect/assets/img/whatsapp-icon.png';
				?>
				<a href="https://wa.me/<?php echo $phone ?>" class="whatsapp-chat" target="_blank">
					<img src="<?php echo $whatsappLogo ?>" alt="WhatsApp Chat" />
				</a>
				<style>
					.whatsapp-chat {
						position: fixed;
						bottom: 50px;
						right: 20px;
						z-index: 1000;
					}
					.whatsapp-chat img {
						width: 60px;
						height: 60px;
	/* 					border-radius: 50%;
						box-shadow: 0 2px 5px rgba(0,0,0,0.2); */
					}
				</style>
				<?php
			}
		}
    }
}

