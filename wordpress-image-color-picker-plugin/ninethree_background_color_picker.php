<?php
/*
Plugin Name: Nine Three Limited Background Image Color Picker
Plugin URI: https://www.davidmillington.net
Description: Uses the Vibrant.js library to pick optimal colors from the selected image suitable for CSS styling
Version: 1.0
Author: David Millington
Author URI: https://www.davidmillington.net
License: GPL2

Copyright 2018  David Millington  (email : david.millington@ninethree.co.uk)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
if (!class_exists('NineThree_Background_Color_Picker')) {
    class NineThree_Background_Color_Picker
    {
        private static $instance = null;
        private $color_picker_types = ["DarkMuted" => "Dark Muted", "DarkVibrant" => "Dark Vibrant", "LightMuted" => "Light Muted", "LightVibrant" => "Light Vibrant", "Muted" => "Muted", "Vibrant" => "Vibrant"];
        private $color_style_types = [ "NavigationBackgroundColor" => "Navigation Background Color",
                                       "NavigationLogoColor" => "Navigation Logo Color",
                                       "NavigationLinkColor" => "Navigation Link Color",
                                       "NavigationLinkHoverColor" => "Navigation Link Hover Color",
                                       "NavigationLinkActiveColor" => "Navigation Link Active Color",
                                       "CardBackgroundColor" => "Card Background Color",
                                       "CardHeaderColor" => "Card Header Color",
                                       "CardBorderColor" => "Card Border Color",
                                       "TextColor" => "Text Color",
                                       "TitleColor" => "Title Color",
                                       "ButtonColor" => "Button Color",
                                       "ButtonHoverColor" => "Button Hover Color",
                                       "ButtonActiveColor" => "Button Active Color",
                                       "LinkColor" => "Link Color",
                                       "LinkHoverColor" => "Link Hover Color",
                                       "LinkActiveColor" => "Link Active Color"];
        public $options;

        public static function get_instance()
        {
            if (null == self::$instance) {
                self::$instance = new self;
            }

            return self::$instance;
        }

        /**
         * Initializes the plugin by setting localization, filters, and administration functions.
         */
        private function __construct()
        {
            // Add the page to the admin menu
            add_action('admin_menu', array(&$this, 'add_page'));

            // Register page options
            add_action('admin_init', array(&$this, 'register_page_options'));

            // Register javascript
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

            // Register REST api path
            add_action('rest_api_init', array($this, 'add_routes'));

            // Get registered option
            $this->options = get_option('background_stylist_options');
        }

        /**
         * Function that will add javascript file for Color Piker.
         */
        public function enqueue_admin_assets()
        {
            // CSS for Color Picker
            wp_enqueue_style('wp-color-picker');

            // CSS for Background Stylist
            wp_enqueue_style('wp-background-stylist', plugins_url('css/ninethree.background.stylist.css', __FILE__));

            // Enqueue the media browser so images can be selected
            wp_enqueue_media();

            // Make sure to add the wp-color-picker dependecy to js file
            // Color picker with alpha channel: https://github.com/kallookoo/wp-color-picker-alpha
            wp_enqueue_script('vibrant_js', plugins_url('js/vibrant.min.js', __FILE__), array('jquery'), '', true);
            wp_enqueue_script('wp-color-picker-alpha', plugins_url('js/wp-color-picker-alpha.min.js', __FILE__), array('jquery', 'wp-color-picker'), true);
            wp_enqueue_script('background_stylist_js', plugins_url('js/jquery.ninethree.background.stylist.js', __FILE__), array('jquery', 'vibrant_js', 'wp-color-picker-alpha'), '', true);

            wp_localize_script('background_stylist_js', 'BackgroundStylist', array(
                'api' => array(
                    'url' => esc_url_raw(rest_url('background-stylist-api/v2/settings')),
                    'nonce' => wp_create_nonce('wp_rest'),
                ),
            ));
        }

        /**
         * Function that will add the options page under Media Menu.
         */
        public function add_page()
        {
            add_submenu_page('upload.php', 'Background Stylist', 'Background Stylist', 'manage_options', __FILE__, array($this, 'display_page'));
        }

        /**
         * Function that will display the options page.
         */
        public function display_page()
        {
            echo '<div class="wrap"><h2>Background Stylist</h2><form method="post" action="options.php">';

            settings_fields(__FILE__);
            do_settings_sections(__FILE__);
            submit_button('Save Changes', 'primary', 'save', false);
            submit_button('Delete Colors', 'delete', 'delete', false);

            echo '</form></div>';
        }

        /**
         * Function that will register admin page options.
         */
        public function register_page_options()
        {
            // Add Section for option fields
            add_settings_section('background_stylist_section', '', array($this, 'display_section'), __FILE__); // id, title, display cb, page

            // Add Background Image Field
            add_settings_field('background_stylist_background_image_field', 'Background Image', array($this, 'background_image_settings_field'), __FILE__, 'background_stylist_section'); // id, title, display cb, page, section

            // Add Color Pick Field (button to force re-coloring)
            add_settings_field('background_stylist_background_image_picker_field', 'Auto Pick Colors', array($this, 'background_image_picker_settings_field'), __FILE__, 'background_stylist_section'); // id, title, display cb, page, section

            add_settings_section('background_stylist_section_picker_colors', '', array($this, 'display_section'), __FILE__); // id, title, display cb, page

            // Add Color Picker Fields (auto populated by color picker)
            foreach ($this->color_picker_types as $key => $value) {
                add_settings_field('background_stylist_' . $key . '_color_field', $value, array($this, 'color_picker_field'), __FILE__, 'background_stylist_section_picker_colors', $key); // id, title, display cb, page, section, color_title
            }

            add_settings_section('background_stylist_section_style_colors', '', array($this, 'display_section'), __FILE__); // id, title, display cb, page

            // Add Color Style Fields (picked by user and used in front end stylesheet)
            foreach ($this->color_style_types as $key => $value) {
                add_settings_field('background_stylist_' . $key . '_color_field', $value, array($this, 'color_setting_field'), __FILE__, 'background_stylist_section_style_colors', $key); // id, title, display cb, page, section, color_title
            }

            // Register Settings
            register_setting(__FILE__, 'background_stylist_options', array($this, 'validate_options')); // option group, option name, sanitize cb
        }

        /**
         * Functions that display the fields.
         */
        public function background_image_settings_field()
        {
            if (isset($this->options['background_image_id'])) {
                echo '<input type="hidden" name="background_stylist_options[background_image_id]" id="background_image_id" value="' . $this->options['background_image_id'] . '" />';
                echo '<input type="hidden" id="background_image_url" value="' . wp_get_attachment_image_src($this->options['background_image_id'], 'full')[0] . '" />';
            } else {
                echo '<input type="hidden" name="background_stylist_options[background_image_id]" id="background_image_id" value="" />';
                echo '<input type="hidden" id="background_image_url" value="" />';
            }

            echo '<button class="select_background_image button">Select Background Image</button>';
        }

        public function background_image_picker_settings_field()
        {
            echo '<button id="auto-color-picker" class="button">Pick Colors</button>';
        }

        public function color_picker_field($color_title)
        {
            echo '<button id="' . $color_title . '" class="color-button"></button>';
            echo '<button id="' . $color_title . '_BodyText" class="color-button"></button>';
            echo '<button id="' . $color_title . '_TitleText" class="color-button"></button>';
        }

        public function color_setting_field($color_title)
        {
            $val = (isset($this->options[$color_title])) ? $this->options[$color_title] : '';
            echo '<input type="text" name="background_stylist_options[' . $color_title . ']" id="' . $color_title . '" value="' . $val . '" class="color-picker" data-alpha="true" />';
        }

        public function break_field()
        {
            echo '<br />';
        }

        /**
         * Function that will validate all fields.
         */
        public function validate_options($fields)
        {
            $valid_fields = array();

            // Validate Background Image ID
            $valid_fields['background_image_id'] = strip_tags(stripslashes(trim($fields['background_image_id'])));

            // Validate Colors
            foreach ($this->color_style_types as $key => $value) {
                $color = strip_tags(stripslashes(trim($fields[$key])));
                if ($this->check_color($color) === false) {
                    // Set the error message
                    add_settings_error('background_stylist_options', 'background_stylist_bg_error', 'Please insert a valid ' . $value . ' style color', 'error');
                    // Get the previous valid value
                    $valid_fields[$key] = $this->options[$key];
                } else {
                    $valid_fields[$key] = $color;
                }
            }

            // Save two copies of the data, the update_option() version is the one for future use if this image is loaded again
            // The apply_filters() version is the currently loaded version
            if (isset($_POST['delete'])) {
                delete_option('background_stylist_options_' . $valid_fields['background_image_id']);
                return apply_filters('validate_options', get_option('background_stylist_options'), $fields);
            }

            update_option('background_stylist_options_' . $valid_fields['background_image_id'], $valid_fields);
            return apply_filters('validate_options', $valid_fields, $fields);
        }

        /**
         * Function that will check if value is a valid color.
         * Regex is from lemnis' comment on Aug 12, 2017: https://gist.github.com/olmokramer/82ccce673f86db7cda5e
         */
        public function check_color($value)
        {
            if (preg_match('/^(#[0-9a-f]{3}|#(?:[0-9a-f]{2}){2,4}|(rgb|hsl)a?\((-?\d+%?[,\s]+){2,3}\s*[\d\.]+%?\))$/i', $value)) {
                return true;
            }
            return false;
        }

        /**
         * Callback function for settings section
         */
        public function display_section()
        { /* Leave blank */}

        public function add_routes()
        {
            register_rest_route('background-stylist-api/v2', '/settings/(?P<id>\d+)',
                array(
                    'methods' => 'GET',
                    'callback' => array($this, 'get_settings'),
                    'args' => array(
                    ),
                    'permissions_callback' => array($this, 'permissions'),
                )
            );
        }

        public function permissions()
        {
            return current_user_can('manage_options');
        }

        public function get_settings(WP_REST_Request $request)
        {
            return rest_ensure_response(get_option('background_stylist_options_' . $request['id']));
        }
    }

    NineThree_Background_Color_Picker::get_instance();
}
