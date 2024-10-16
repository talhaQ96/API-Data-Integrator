<?php
/**
 * Plugin Name: API Data Integrator
 * Plugin URI: https://strategy11.com/
 * Description: Integrates external data into your site via a custom REST API endpoint, with caching and dynamic data display.
 * Version: 1.0.0
 * Author: Talha Qureshi
 * Author URI: https://github.com/talhaQ96
 */

# Exit if files accessed directly.
defined('ABSPATH') || die();

# Define constants for the plugin's root path and URL.
define('PLUGIN_ROOT_PATH', plugin_dir_path(__FILE__));
define('PLUGIN_ROOT_URL', plugin_dir_url(__FILE__));

class APIDataIntegrator {
    public $cache_key = 'api_data_cache';
    public $api_url = 'https://api.strategy11.com/wp-json/challenge/v1/1';

    /**
     * Constructor
     */
    public function __construct() {
        # Enqueue Admin Scripts
        add_action('admin_enqueue_scripts', array($this, 'adi_enqueue_admin_scripts'));

        # Enqueue Frontend Scripts
        add_action('wp_enqueue_scripts', array($this, 'adi_enqueue_frontend_scripts'));

        # Register REST API Endpoint
        add_action('rest_api_init', array($this, 'adi_register_rest_routes'));

        # Add Admin Menu Page
        add_action('admin_menu', array($this, 'adi_register_admin_menu'));

        # Register Shortcode
        add_shortcode('adi_api_data', array($this, 'adi_data_table_shortcode'));

        # Register AJAX Request to Refresh Data on Button Click: Admin Panel
        add_action('wp_ajax_refresh_data', array($this, 'adi_refresh_data_on_button_click'));

        # Register WP-CLI Command
        if (defined('WP_CLI') && WP_CLI) {
            WP_CLI::add_command('refresh data', array($this, 'adi_force_refresh'));
        }
    }

    /**
     * The function loads all required assets for Admin.
     */
    public function adi_enqueue_admin_scripts() {
        wp_enqueue_style('adi-admin-styles', esc_url(PLUGIN_ROOT_URL) . 'assets/css/admin.css');
        wp_enqueue_script('adi-admin-script', esc_url(PLUGIN_ROOT_URL) . 'assets/js/admin.js');

        $data = $this->adi_get_data();
        $data = $data->get_data();

        wp_localize_script(
            'adi-admin-script',
            'handle',
            array(
                'api_response' => $data,
                'api_url' => $this->api_url,
                'admin_url' => admin_url('admin-ajax.php')
            )
        );
    }

    /**
     * The function loads all required assets for Frontend.
     */
    public function adi_enqueue_frontend_scripts() {
        wp_enqueue_style('adi-frontend-styles', esc_url(PLUGIN_ROOT_URL) . 'assets/css/styles.css');
        wp_enqueue_script('adi-frontend-script', esc_url(PLUGIN_ROOT_URL) . 'assets/js/main.js');

        $data = $this->adi_get_data();
        $data = $data->get_data();

        wp_localize_script(
            'adi-frontend-script', 
            'handle',
            array(
                'api_response' => $data
            )
        );
    }

    /**
     * Registers a REST API route for retrieving data.
     */
    public function adi_register_rest_routes() {
        register_rest_route('challenge/v1', '/data', [
            'methods' => 'GET',
            'callback' => array($this, 'adi_get_data'),
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Retrieves data from cache or fetches from the API if not cached.
     * 
     * Used as a callback for the `register_rest_route`.
     */
    public function adi_get_data() {
        $data = get_transient($this->cache_key);

        if ($data === false) {
            $response = wp_remote_get(esc_url($this->api_url));
            if (is_wp_error($response)) {
                return new WP_Error('api_error', 'Failed to fetch data from external API');
            }

            $data = wp_remote_retrieve_body($response);
            set_transient($this->cache_key, $data, HOUR_IN_SECONDS);
        }

        return rest_ensure_response(json_decode($data));
    }

    /**
     * Registers a shortcode to display API endpoint data in HTML table format on the front end.
     */
    public function adi_data_table_shortcode() {
        echo '<div id="adi_data-output"></div>';
    }

    /**
     * Adds an admin menu page `API Data` to the WordPress dashboard.
     */
    public function adi_register_admin_menu() {
        add_menu_page(
            'API Data',
            'API Data',
            'manage_options',
            'api-data',
            array($this, 'adi_admin_page'),
            'dashicons-chart-line',
            2
        );
    }

    /**
     * Renders the admin menu page displaying API data.
     * 
     * Used as a callback for `add_menu_page`.
     */
    public function adi_admin_page() {
        $data = $this->adi_get_data();
        if (is_wp_error($data)) {
            return 'Failed to fetch data';
        }
        
        $data = $data->get_data();
?>
        <div class="adi_top-bar">
            <div class="left">
                <a class="logo" href="https://strategy11.com/">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 599.68 601.37" width="35" height="35">
                        <path fill="#f05a24" d="M289.6 384h140v76h-140z"></path>
                        <path fill="#4d4d4d" d="M400.2 147h-200c-17 0-30.6 12.2-30.6 29.3V218h260v-71zM397.9 264H169.6v196h75V340H398a32.2 32.2 0 0 0 30.1-21.4 24.3 24.3 0 0 0 1.7-8.7V264zM299.8 601.4A300.3 300.3 0 0 1 0 300.7a299.8 299.8 0 1 1 511.9 212.6 297.4 297.4 0 0 1-212 88zm0-563A262 262 0 0 0 38.3 300.7a261.6 261.6 0 1 0 446.5-185.5 259.5 259.5 0 0 0-185-76.8z"></path>
                    </svg>
                </a>
                <h1 class="title">API Data Integrator</h1>
            </div>
            <div class="right">
                <a id="refresh-data-button" class="button">Refresh Data</a>
            </div>
        </div>
        <div id="adi_data-output"></div>
<?php
    }

    /**
     * Clears the cached data by deleting the transient.
     */
    public function adi_refresh_data_on_button_click() {
        delete_transient($this->cache_key);

        $data = $this->adi_get_data();
        $data = $data->get_data();

        wp_send_json_success($data);
    }

    /**
     * Clears the cached data by deleting the transient.
     */
    public function adi_force_refresh() {
        delete_transient($this->cache_key);
        WP_CLI::success('API data cache cleared.');
    }
}

# Initialize the plugin
new APIDataIntegrator();
?>