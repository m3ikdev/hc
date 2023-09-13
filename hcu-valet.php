<?php
/*
Plugin Name: Cumulus Valet - Hébergement Géré
Plugin URI: https://ca.horizon-cumulus.com
Description: Horizon-Cumulus a développé une extension destinée à tous ses clients ayant souscris à un service d'hébergement géré. L'extension Cumulus Valet offre des fonctionnalités qui permettent de bonifier la gestion de votre site Internet. De plus, Valet contribue à épurer l'interface d'administration et nous permet d'assurer une meilleure veille afin de détecter les éventuels problèmes sur votre site.
Version: 1.1.15
Author: Horizon-Cumulus
Author URI: http://ca.horizon-cumulus.com

------------------------------------------------------------------------
Copyright 2014 Horizon-cumulus Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc.
*/

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('FS_METHOD'))
    define('FS_METHOD', 'direct');

if (!defined('RECOVERY_MODE_EMAIL'))
    define('RECOVERY_MODE_EMAIL', 'g1a9f4k5v0r7v2c5@horizon-cumulus.slack.com');

if (!defined('DISALLOW_FILE_EDIT'))
    define('DISALLOW_FILE_EDIT', true);


class hc_maintenance
{
    
    const VERSION = '1.1.15';
    
    protected $disable_comments;
    protected $cuws;
    protected $HCU_Taxonomy_Order;
    private $active_disable_comments = false;
    private $active_cuws = false;
    private $hc_debug = false;
    private $active_duplicate_post = false;
    private $active_mainwp_reports = false;
    
    
    public function __construct()
    {
        
        $hcu_options = get_option("hcu-options");
        
        $active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
        if (!(isset($_GET["plugin"]) && isset($_GET["action"]) && $_GET["action"] == "activate")) {
            
            if (!in_array('wp-fail2ban/wp-fail2ban.php', $active_plugins)) {
                require_once(__DIR__ . '/fail2ban/fail2ban.php');
            }
            
            if (!in_array('disable-comments/disable-comments.php', $active_plugins)) {
                require_once(__DIR__ . '/disable-comments/disable-comments.php');
                $this->disable_comments = new  Disable_Comments_hcu();
                $this->active_disable_comments = true;
            }
            
            /* Disable
            if (!in_array('so-clean-up-wp-seo/so-clean-up-wp-seo.php', $active_plugins)) {
                require_once(__DIR__ . '/hide-seo-bloat/so-clean-up-wp-seo.php');
                $this->cuws = CUWS::instance(__FILE__, '3.14.3');
                $this->active_cuws = true;
           }
            */
//var_dump($active_plugins);
            if (!in_array('duplicate-post/duplicate-post.php', $active_plugins)) {
                require_once(__DIR__ . '/duplicate-post/duplicate-post.php');
                $this->active_duplicate_post = true;
            }
            
            if (!isset($hcu_options["disable-tax-order"]) || !$hcu_options["disable-tax-order"]) {
                require_once(__DIR__ . '/tax-order/tax-order.php');
                $this->HCU_Taxonomy_Order = HCU_Taxonomy_Order::get_instance();
            }
            
        }
        
        
        if (in_array('mainwp-child-reports/mainwp-child-reports.php', $active_plugins)) {
            $this->active_mainwp_reports = true;
        }
        
        if (!isset($hcu_options["allow-iframe-origin"]) || !$hcu_options["allow-iframe-origin"]) {
            add_action('send_headers', 'send_frame_options_header', 10, 0);
        }
        
        add_action('init', array(&$this, 'wphc_init_action'));
        
        add_action('wp_dashboard_setup', array(&$this, 'adjust_default_dashboard_widgets'), 99);
        
        
        add_action('wp_enqueue_scripts', array(&$this, 'wphc_site_style'));
        
        add_action('admin_enqueue_scripts', array(&$this, 'wphc_admin_style'));
        
        add_filter('rocket_cache_reject_ua', array(&$this, 'rocket_cache_reject_ua'), 1, 1);
        
        register_activation_hook(__FILE__, array(&$this, 'hcu_wpdsa_plugin_activation'));
        register_deactivation_hook(__FILE__, array(&$this, 'hcu_wpdsa_plugin_deactivation'));
        
        add_filter('plugins_api', array(&$this, 'hcu_plugin_info'), 20, 3);
        
        add_filter('site_transient_update_plugins', array(&$this, 'hcu_push_update'));
    }
    
    function hcu_plugin_info($res, $action, $args)
    {
        
        // do nothing if this is not about getting plugin information
        if ('plugin_information' !== $action) {
            return $res;
        }
        // do nothing if it is not our plugin
        if (plugin_basename(__DIR__) !== $args->slug) {
            return $res;
        }
        
        // info.json is the file with the actual plugin information on your server
        $remote = wp_remote_get(
            'https://gestion-wordpress.com/valet/info.json',
            array(
                'timeout' => 10,
                'headers' => array(
                    'Accept' => 'application/json'
                )
            )
        );
        
        // do nothing if we don't get the correct response from the server
        if (
            is_wp_error($remote)
            || 200 !== wp_remote_retrieve_response_code($remote)
            || empty(wp_remote_retrieve_body($remote)
            )) {
            return $res;
        }
        
        $remote = json_decode(wp_remote_retrieve_body($remote));
        
        $res = new stdClass();
        $res->name = $remote->name;
        $res->slug = $remote->slug;
        $res->author = $remote->author;
        $res->author_profile = $remote->author_profile;
        $res->version = $remote->version;
        $res->tested = $remote->tested;
        $res->requires = $remote->requires;
        $res->requires_php = $remote->requires_php;
        $res->download_link = $remote->download_url;
        $res->trunk = $remote->download_url;
        $res->last_updated = $remote->last_updated;
        $res->sections = array(
            'description' => $remote->sections->description,
            'installation' => $remote->sections->installation,
            'changelog' => $remote->sections->changelog
            // you can add your custom sections (tabs) here
        );
        // in case you want the screenshots tab, use the following HTML format for its content:
        // <ol><li><a href="IMG_URL" target="_blank"><img src="IMG_URL" alt="CAPTION" /></a><p>CAPTION</p></li></ol>
        if (!empty($remote->sections->screenshots)) {
            $res->sections['screenshots'] = $remote->sections->screenshots;
        }
        
        return $res;
        
    }
    
    
    function hcu_push_update($transient)
    {
        
        if (empty($transient->checked)) {
            return $transient;
        }
        
        $remote = wp_remote_get(
            'https://gestion-wordpress.com/valet/info.json',
            array(
                'timeout' => 10,
                'headers' => array(
                    'Accept' => 'application/json'
                )
            )
        );
        
        if (
            is_wp_error($remote)
            || 200 !== wp_remote_retrieve_response_code($remote)
            || empty(wp_remote_retrieve_body($remote)
            )) {
            return $transient;
        }
        
        $remote = json_decode(wp_remote_retrieve_body($remote));
        
        // your installed plugin version should be on the line below! You can obtain it dynamically of course
        if (
            $remote
            && version_compare(self::VERSION, $remote->version, '<')
            && version_compare($remote->requires, get_bloginfo('version'), '<')
            && version_compare($remote->requires_php, PHP_VERSION, '<')
        ) {
            
            $res = new stdClass();
            $res->slug = $remote->slug;
            $res->plugin = plugin_basename(__FILE__); // it could be just YOUR_PLUGIN_SLUG.php if your plugin doesn't have its own directory
            $res->new_version = $remote->version;
            $res->tested = $remote->tested;
            $res->package = $remote->download_url;
            $transient->response[$res->plugin] = $res;
            
            //$transient->checked[$res->plugin] = $remote->version;
        }
        
        return $transient;
        
    }
    
    
    private function hcu_update_db_elementor()
    {
        if (is_plugin_active('elementor/elementor.php')) {
            $manager = new \Elementor\Core\Upgrade\Manager();
            
            $updater = $manager->get_task_runner();
            if (!$manager->should_upgrade()) {
                //  \WP_CLI::success('The DB is already updated!');
                return;
            }
            $callbacks = $manager->get_upgrade_callbacks();
            $did_tasks = false;
            
            if (!empty($callbacks)) {
                \Elementor\Plugin::$instance->logger->get_logger()->info('Update DB has been started', [
                    'meta' => [
                        'plugin' => $manager->get_plugin_label(),
                        'from' => $manager->get_current_version(),
                        'to' => $manager->get_new_version(),
                    ],
                ]);
                
                $updater->handle_immediately($callbacks);
                $did_tasks = true;
            }
            
            $manager->on_runner_complete($did_tasks);
        }
    }
    
    private function hcu_update_db_elementor_pro()
    {
        if (is_plugin_active('elementor-pro/elementor-pro.php')) {
            $manager = new \ElementorPro\Core\Upgrade\Manager();
            
            $updater = $manager->get_task_runner();
            if (!$manager->should_upgrade()) {
                //  \WP_CLI::success('The DB is already updated!');
                return;
            }
            $callbacks = $manager->get_upgrade_callbacks();
            $did_tasks = false;
            
            if (!empty($callbacks)) {
                \Elementor\Plugin::$instance->logger->get_logger()->info('Update DB has been started', [
                    'meta' => [
                        'plugin' => $manager->get_plugin_label(),
                        'from' => $manager->get_current_version(),
                        'to' => $manager->get_new_version(),
                    ],
                ]);
                
                $updater->handle_immediately($callbacks);
                $did_tasks = true;
            }
            
            $manager->on_runner_complete($did_tasks);
        }
    }
    
    
    private function hcu_update_db_wc()
    {
        if (is_plugin_active('woocommerce/woocommerce.php')) {
            if ( WC_Install::needs_db_update() ) {
                WC_Install::run_manual_database_update();
            }
        }
    }
    
    private function hcu_wpdsa_plugin_version()
    {
        
        // IMPORTANT: BUMP VERSION NUMBER ON EACH RELEASE
        $wpdsa_version = self::VERSION;
        
        $wpdsa_current_version = get_option('wpdsa_version');
        if (false === $wpdsa_current_version) {
            update_option('wpdsa_version', $wpdsa_version);
        } else {
            if (version_compare($wpdsa_current_version, $wpdsa_version, '<')) {
                $filesystem = $this->hcu_wpdsa_get_filesystem();
                // Remove existing file
                $filename = WPMU_PLUGIN_DIR . '/hcu-wp-down-slack-alert-worker.php';
                if ($filesystem->exists($filename)) {
                    $filesystem->delete($filename);
                }
                // Add new file
                $contents = $filesystem->get_contents(plugin_dir_path(__FILE__) . 'mu-plugin-template/hcu-wp-down-slack-alert-worker.php');
                if (!$filesystem->exists(WPMU_PLUGIN_DIR)) {
                    $filesystem->mkdir(WPMU_PLUGIN_DIR);
                }
                if (!$filesystem->exists(WPMU_PLUGIN_DIR)) {
                    return;
                }
                $filesystem->put_contents($filename, $contents);
                update_option('wpdsa_version', $wpdsa_version);
            }
        }
        // If the mu-plugin was removed, recreate it.
        $filesystem = $this->hcu_wpdsa_get_filesystem();
        $filename = WPMU_PLUGIN_DIR . '/hcu-wp-down-slack-alert-worker.php';
        if (!$filesystem->exists($filename)) {
            $contents = $filesystem->get_contents(plugin_dir_path(__FILE__) . 'mu-plugin-template/hcu-wp-down-slack-alert-worker.php');
            if (!$filesystem->exists(WPMU_PLUGIN_DIR)) {
                $filesystem->mkdir(WPMU_PLUGIN_DIR);
            }
            if (!$filesystem->exists(WPMU_PLUGIN_DIR)) {
                return;
            }
            $filesystem->put_contents($filename, $contents);
        }
    }
    
    function hcu_wpdsa_plugin_activation()
    {
        $filesystem = $this->hcu_wpdsa_get_filesystem();
        $filename = WPMU_PLUGIN_DIR . '/hcu-wp-down-slack-alert-worker.php';
        if ($filesystem->exists($filename)) {
            return;
        }
        $contents = $filesystem->get_contents(plugin_dir_path(__FILE__) . 'mu-plugin-template/hcu-wp-down-slack-alert-worker.php');
        if (!$filesystem->exists(WPMU_PLUGIN_DIR)) {
            $filesystem->mkdir(WPMU_PLUGIN_DIR);
        }
        if (!$filesystem->exists(WPMU_PLUGIN_DIR)) {
            return;
        }
        $filesystem->put_contents($filename, $contents);
    }
    
    function hcu_wpdsa_plugin_deactivation()
    {
        $filesystem = $this->hcu_wpdsa_get_filesystem();
        
        // Remove existing file
        $filename = WPMU_PLUGIN_DIR . '/hcu-wp-down-slack-alert-worker.php';
        if ($filesystem->exists($filename)) {
            $filesystem->delete($filename);
        }
    }
    
    private function hcu_wpdsa_get_filesystem()
    {
        static $filesystem;
        if ($filesystem) {
            return $filesystem;
        }
        require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
        require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');
        
        $filesystem = new WP_Filesystem_Direct(new StdClass());
        if (!defined('FS_CHMOD_DIR')) {
            define('FS_CHMOD_DIR', (@fileperms(ABSPATH) & 0777 | 0755));
        }
        if (!defined('FS_CHMOD_FILE')) {
            define('FS_CHMOD_FILE', (@fileperms(ABSPATH . 'index.php') & 0777 | 0644));
        }
        return $filesystem;
    }
    
    static function htaccess_png_404_to_403()
    {
        return "# BEGIN REDIRECTION PNG 404 TO 403" . PHP_EOL
            . "<IfModule mod_rewrite.c> " . PHP_EOL
            . " RewriteEngine On " . PHP_EOL
            . "RewriteCond %{REQUEST_FILENAME} !-f " . PHP_EOL
            . "RewriteCond %{REQUEST_FILENAME} !-d " . PHP_EOL
            //. "RewriteCond %{REQUEST_URI} !(robots\.txt|sitemap\.xml(\.gz)?) " . PHP_EOL
            . "RewriteCond %{REQUEST_FILENAME} \.(gif|jpg|jpeg|png)$ [NC] " . PHP_EOL
            . "RewriteRule .* - [L] </IfModule>" . PHP_EOL
            . "# END REDIRECTION PNG 404 TO 403";
    }
    
    static function get_markers_force_redirect_https($marker)
    {
        $redirection = '# BEGIN REDIRECTION HTTPS' . PHP_EOL;
        $redirection .= 'RewriteEngine On' . PHP_EOL;
        $redirection .= 'RewriteCond %{HTTPS} !on' . PHP_EOL;
        $redirection .= 'RewriteCond %{SERVER_PORT} !^443$' . PHP_EOL;
        $redirection .= 'RewriteCond %{HTTP:X-Forwarded-Proto} !https' . PHP_EOL;
        $redirection .= 'RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]' . PHP_EOL;
        $redirection .= '# END REDIRECTION HTTPS' . PHP_EOL . PHP_EOL;
        return $redirection . $marker;
    }
    
    function wphc_init_action()
    {
        
        if (isset($_GET["hc"])) {
            if ($_GET["hc"] == 1) {
                setcookie("hc-debug", '', time() - DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
                unset($_COOKIE["hc-debug"]);
            } else {
                setcookie("hc-debug", '1', time() + DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
                $_COOKIE["hc-debug"] = 1;
            }
        }
        
        if (isset($_COOKIE["hc-debug"])) {
            $this->hc_debug = true;
        }
        
        $hcu_options = get_option("hcu-options");
        
        add_filter('recovery_mode_email', array(&$this, 'wphc_recovery_mode_email'), 1, 1);
        
        //Désactivé XML RPC
        add_filter('xmlrpc_enabled', '__return_false');
        
        add_filter('rest_request_before_callbacks', array(&$this, 'hcu_rest_request_before_callbacks'), 10, 3);
        if (!isset($hcu_options["activer-rest"]) || !$hcu_options["activer-rest"]) {
            if (!in_array('contact-form-7/wp-contact-form-7.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                //Disable REST API link in HTTP headers
                remove_action('template_redirect', 'rest_output_link_header', 11);
                
                add_filter('rest_authentication_errors', array(&$this, 'disable_wp_rest_api'), 10, 1);
                
            }
        }
        //Disable REST API links in HTML <head>
        remove_action('wp_head', 'rest_output_link_wp_head', 10);
        remove_action('xmlrpc_rsd_apis', 'rest_output_rsd');
        
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_styles', 'print_emoji_styles');
        
        remove_action('wp_head', 'wlwmanifest_link');
        
        //Securité
        remove_action('rss2_head', 'the_generator');
        remove_action('rss_head', 'the_generator');
        remove_action('rdf_header', 'the_generator');
        remove_action('atom_head', 'the_generator');
        remove_action('commentsrss2_head', 'the_generator');
        remove_action('opml_head', 'the_generator');
        remove_action('app_head', 'the_generator');
        remove_action('comments_atom_head', 'the_generator');
        
        //Désactivé auto Updates
        add_filter('allow_minor_auto_core_updates', '__return_false');
        add_filter('allow_major_auto_core_updates', '__return_false');
        add_filter('auto_update_theme', '__return_false');
        add_filter('auto_update_plugin', '__return_false');
        add_filter('auto_update_translation', '__return_false');
        add_filter('auto_core_update_send_email', '__return_false');
        
        //désactivé notif update
        remove_action('admin_notices', 'update_nag', 3);
        remove_action('welcome_panel', 'wp_welcome_panel');
        
        
        if ((!isset($hcu_options["show-notice"]) || !$hcu_options["show-notice"]) && !$this->hc_debug) {
            add_action('in_admin_header', array(&$this, 'wphc_removenotice'), 1000);
        }
        
        add_action('wp_before_admin_bar_render', array(&$this, 'wphc_remove_admin_bar_updates_links'));
        
        add_filter('site_status_tests', array(&$this, 'wphc_remove_update_check'));
        add_filter('mainwp_child_extra_execution', array(&$this, 'mainwp_child_extra_execution'), 10, 2);
        add_filter('mainwp-site-sync-others-data', array(&$this, 'mainwp_site_sync_others_data'), 10, 2);
        
        
        add_action('admin_menu', array(&$this, 'register_wphc_menu_page'), 999);
        
        add_action('admin_footer', array(&$this, 'admin_footer_tawkto'));
        
        add_filter('sanitize_file_name', array(&$this, 'wphc_sanitize_file_name'), 10, 1);
        
        add_filter('before_rocket_htaccess_rules', array(&$this, 'htaccess_png_404_to_403'), 1);
        
        if (isset($hcu_options["force-https"]) && $hcu_options["force-https"]) {
            add_filter('before_rocket_htaccess_rules', array(&$this, 'get_markers_force_redirect_https'), 1, 1);
        }
        //add_action('template_redirect', array(&$this, 'wp_redirect_if_author_query'));
        //add_filter('author_link', array(&$this, 'wp_redirect_if_author_query'),100);
        if (!is_admin()) {
            // default URL format
            if (preg_match('/author=([0-9]*)/i', $_SERVER['QUERY_STRING'])) {
                wp_redirect(home_url(), 301);
                exit;
            }
            add_filter('redirect_canonical', array(&$this, 'shapeSpace_check_enum'), 10, 2);
            
            /*
            add_filter('rest_endpoints', function ($aryEndpoints) {
                if (isset($aryEndpoints['/wp/v2/users'])) {
                    unset($aryEndpoints['/wp/v2/users']);
                }
                if (isset($aryEndpoints['/wp/v2/users/(?P<id>[\d]+)'])) {
                    unset($aryEndpoints['/wp/v2/users/(?P<id>[\d]+)']);
                }
                return $aryEndpoints;
            });
            */
        }
        
        if (!$this->hc_debug) {
            add_action('pre_current_active_plugins', array(&$this, 'hide_plugin'));
        }
        
        //Ajout de fonction Ajax
        add_action('wp_ajax_info_errors_log', array(&$this, 'info_errors_log')); //pour les utilisateur connecté
        add_action('wp_ajax_nopriv_info_errors_log', array(&$this, 'info_errors_log')); //pour les utilisateurs anonyme
        
        if ($this->active_mainwp_reports) {
            add_action('after_rocket_clean_domain', array(&$this, 'valet_after_rocket_clean_domain'), 10, 3);
            //  add_action('after_rocket_clean_post', array(&$this, 'valet_after_rocket_clean_post'),10,3);
            //  add_action('after_rocket_clean_home', array(&$this, 'valet_after_rocket_clean_home'));
            // add_action('after_rocket_clean_file', array(&$this, 'valet_after_rocket_clean_file'),10,1);
        }
        
        if (function_exists("l_theplus_generator")) {
            remove_action('admin_bar_menu', [l_theplus_generator(), 'add_plus_clear_cache_admin_bar'], 300);
        }
        
        
        add_filter('admin_body_class', array(&$this, 'sitedev_addclass'));
        add_filter('body_class', array(&$this, 'sitedev_addclass'));
    }
    
    
    function sitedev_addclass($classes)
    {
        $url = get_site_url();
        $domain = parse_url($url, PHP_URL_HOST);
        if ((@substr_compare($domain, "enconstruction.website", -strlen("enconstruction.website")) == 0)) {
            if (is_array($classes)) {
                $classes[] = "site-enconstruction";
            } else {
                
                $classes .= " site-enconstruction";
            }
        }
        return $classes;
    }
    
    
    function info_errors_log()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        header('Content-type: application/json');
        $ALLOWED_IP = [
            '10.0.4.20',
            '204.80.3.96',
            '199.58.239.113',
        ];
        if (in_array($ip, $ALLOWED_IP)) {
            $since = time() - (3600 * 24);
            $data["error_log"] = $this->checkErrorsFile("../error_log", $since);
            $data["wp_admin_error_log"] = $this->checkErrorsFile("error_log", $since);
            $data["since"] = $since;
        } else {
            http_response_code(403);
            $data["error"] = 'Ip non permise : ' . $ip;
        }
//        echo "<pre>" . json_encode($data, 128);
        echo json_encode($data, 128);
        wp_die();
    }
    
    private function checkErrorsFile($file, $since)
    {
        $maximumLine = 5;
        $data = [];
        // $errors = file_get_contents($file);
        //  $arrErrors = explode("\n", $errors);
        $formattedErrors = [];
        $errorString = "";
        $errorNb = 0;
        $errorNbReturned = 0;
        /*
        foreach ($arrErrors as $error) {
            $match = preg_match('/\[\d+-.+\]/', $error, $datetime); // match un "[", 1 ou + chiffre, un "-" , n'importe quoi, un "]". [03-May-2022 15:48:49 UTC]
            if ($match) {
                //Nouvelle erreur
                $datetime = str_replace(['[', ']'], '', $datetime[0]);
                $timestamp = strtotime($datetime);
                if ($timestamp >= $since) {
                    if (strlen($errorString) == 0)
                        $errorString = $error;
                    if (!isset($formattedErrors[$timestamp]))
                        $errorNbReturned++;
                    $formattedErrors[$timestamp] = $errorString;
                }
                $errorNb++;
                $errorString = $error;
            } else {
                //Même erreur qu'avant, l'ajouté au string
                $errorString .= $error . PHP_EOL;
            }
        }
        */
        //$formattedErrors[0] = "[16-Dec-2019 20:29:57 UTC] PHP Fatal error:  Uncaught Error: Call to undefined function wp_add_dashboard_widget() in /home/bloguehc/public_html/wp-content/plugins/hc-maintenance/hc-maintenance.php:68";
        $data["error_size"] = filesize($file);
        $data["error_nb_line"] = 0; // count($arrErrors);
        $data["error_nb"] = $errorNb;
        $data["error_nb_returned"] = $errorNbReturned;
        $data["error_log"] = $formattedErrors;
        return $data;
    }
    
    function rocket_cache_reject_ua($ua)
    {
        $ua = \array_diff($ua, ["facebookexternalhit"]);
        $ua[] = "Site24x7";
        $ua[] = "(.*)NIXStatsbot";
        return $ua;
    }
    
    function wphc_sanitize_file_name($filename)
    {
        
        $sanitized_filename = remove_accents($filename); // Convert to ASCII
        
        // Standard replacements
        $invalid = array(
            ' ' => '-',
            '%20' => '-',
            '_' => '-',
        );
        $sanitized_filename = str_replace(array_keys($invalid), array_values($invalid), $sanitized_filename);
        
        $sanitized_filename = preg_replace('/[^A-Za-z0-9-\. ]/', '', $sanitized_filename); // Remove all non-alphanumeric except .
        $sanitized_filename = preg_replace('/\.(?=.*\.)/', '', $sanitized_filename); // Remove all but last .
        $sanitized_filename = preg_replace('/-+/', '-', $sanitized_filename); // Replace any more than one - in a row
        $sanitized_filename = str_replace('-.', '.', $sanitized_filename); // Remove last - if at the end
        $sanitized_filename = strtolower($sanitized_filename); // Lowercase
        
        return $sanitized_filename;
    }
    
    
    function shapeSpace_check_enum($redirect, $request)
    {
        // permalink URL format
        if (preg_match('/\?author=([0-9]*)(\/*)/i', $request)) return home_url();
        else return $redirect;
    }
    
    
    function wp_redirect_if_author_query()
    {
        $is_author_set = get_query_var('author', '');
        if ($is_author_set != '' && !is_admin()) {
            wp_redirect(home_url(), 301);     // send them somewhere else
            exit;
        }
    }
    
    function deactivate_plugin_conditional()
    {
        
        if (is_plugin_active('wp-fail2ban/wp-fail2ban.php')) {
            deactivate_plugins('wp-fail2ban/wp-fail2ban.php');
        }
        if (is_plugin_active('wp-security-audit-log/wp-security-audit-log.php')) {
            add_filter('wsal_filter_deactivation_email_delivery_address', '__return_false', 10, 1);
            deactivate_plugins('wp-security-audit-log/wp-security-audit-log.php');
            
            global $wpdb;
            $wpdb->query('DROP TABLE ' . $GLOBALS['wpdb']->base_prefix . 'wsal_options');
            $wpdb->query('DROP TABLE ' . $GLOBALS['wpdb']->base_prefix . 'wsal_occurrences');
            $wpdb->query('DROP TABLE ' . $GLOBALS['wpdb']->base_prefix . 'wsal_metadata');
            
            wp_clear_scheduled_hook('wsal_cleanup');
        }
    }
    
    
    function hide_plugin()
    {
        global $wp_list_table;
        $hidearr = array('hcu-valet/hcu-valet.php', 'mainwp-child/mainwp-child.php', 'mainwp-child-reports/mainwp-child-reports.php');
        $myplugins = $wp_list_table->items;
        foreach ($myplugins as $key => $val) {
            if (in_array($key, $hidearr)) {
                unset($wp_list_table->items[$key]);
            }
        }
    }
    
    function disable_wp_rest_api($access)
    {
        
        if (!is_user_logged_in()) {
            
            $message = apply_filters('disable_wp_rest_api_error', __('REST API restricted to authenticated users.', 'disable-wp-rest-api'));
            
            return new WP_Error('rest_login_required', $message, array('status' => rest_authorization_required_code()));
            
        }
        
        return $access;
        
    }
    
    
    function hcu_rest_request_before_callbacks($response, $handler, $request)
    {
        
        $route = $request->get_route();
        $method = $request->get_method();
        
        if ($method == "POST") {
            if ((preg_match("/^\/wp\/v2\/users/i", $route) === 1)
                || (preg_match("/^\/wp\/v2\/plugins/i", $route) === 1)) {
                
                $message = apply_filters('disable_wp_rest_api_error', __('REST API resquest disabled.', 'disable-wp-rest-api'));
                return new WP_Error('rest_login_required', $message, array('status' => rest_authorization_required_code()));
            }
            
        }
        
        
        return $response;
    }
    
    
    function mainwp_site_sync_others_data($information, $data)
    {
        
        if (isset($data["forfait"])) {
            update_option("hc-forfait", $data["forfait"]);
        }
        if (isset($data["revendeur"])) {
            update_option("hc-revendeur", $data["revendeur"]);
        }
        
        if (isset($data["infobox"])) {
            update_option("hc-infobox", $data["infobox"]);
        }
        
        if (isset($data["tawkto_hash"]) && $data["tawkto_hash"]) {
            update_option("tawkto_hash", $data["tawkto_hash"]);
        }
        
        
        if (is_plugin_active('wordpress-seo/wp-seo.php')) {
            $wpseo_option = get_option('wpseo');
            if (isset($wpseo_option) && isset($wpseo_option['ryte_indexability']) && $wpseo_option['ryte_indexability']) {
                $wpseo_option['ryte_indexability'] = false;
                update_option('wpseo', $wpseo_option);
            }
        }
        
        $this->hcu_wpdsa_plugin_version();
        
        $this->hcu_update_db_elementor();
        $this->hcu_update_db_elementor_pro();
        $this->hcu_update_db_wc();
        // $this->deactivate_plugin_conditional();
        
        return $information;
    }
    
    function mainwp_child_extra_execution($information, $post)
    {
        if (isset($post["action"]) && $post["action"] == "hc-get-admin-email") {
            
            $information["admin_email"] = get_option('admin_email');
        }
        
        return $information;
    }
    
    function wphc_remove_update_check($tests)
    {
        unset($tests['async']['background_updates']);
        unset($tests['direct']['php_version']);
        unset($tests['direct']['php_extensions']);
        return $tests;
    }
    
    function wphc_recovery_mode_email($email_data)
    {
        $email_data['to'] = RECOVERY_MODE_EMAIL;
        return $email_data;
    }
    
    
    function register_wphc_menu_page()
    {
        add_submenu_page('tools.php', 'Alertes et notifications', 'Alertes et notifications', 'manage_options', 'notice', array(&$this, 'wphc_admin_notice_page'), 0);
        add_submenu_page('options-general.php', 'Cumulus Valet', 'Cumulus Valet', 'manage_options', 'valet', array(&$this, 'wphc_admin_valet_page'), 999);
        
        $current_user = wp_get_current_user();
        if (!in_array($current_user->user_login, array("assistance", "admin")))
            remove_submenu_page('options-general.php', 'mainwp_child_tab');
    }
    
    
    function wphc_admin_notice_page()
    { ?><h1 class="wp-heading-inline">Alertes et notifications</h1> <?php }
    
    
    function wphc_site_style()
    {
        if (is_admin_bar_showing()) {
            wp_enqueue_style('wphc_style', plugins_url('css/style.css', __FILE__), array(), self::VERSION);
        }
    }
    
    function wphc_admin_style()
    {
        wp_enqueue_style('wphc_admin_style', plugins_url('css/admin.css', __FILE__), array(), self::VERSION);
    }
    
    function wphc_remove_admin_bar_updates_links()
    {
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu('updates');
    }
    
    function wphc_removenotice()
    {
        global $pagenow;
        if (($pagenow == "tools.php" && isset($_GET["page"]) && $_GET["page"] == "notice")) return;
        
        //Tab manager
        if (($pagenow == "edit.php" && isset($_GET["post_type"]) && $_GET["post_type"] == "wc_product_tab")) return;
        if (($pagenow == "post-new.php" && isset($_GET["post_type"]) && $_GET["post_type"] == "wc_product_tab")) return;
        if (($pagenow == "admin.php" && isset($_GET["page"]) && $_GET["page"] == "tab_manager")) return;
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
    }
    
    //Masqué les élément du Dashboard
    function adjust_default_dashboard_widgets()
    {
        global $wp_meta_boxes;
        //unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);    // Right Now Widget
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_site_health']);        // Activity Widget
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);        // Activity Widget
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']); // Comments Widget
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);  // Incoming Links Widget
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);         // Plugins Widget
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);    // Quick Press Widget
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);     // Recent Drafts Widget
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);           // WordPress News
        // remove plugin dashboard boxes
        unset($wp_meta_boxes['dashboard']['normal']['core']['rg_forms_dashboard']);        // Gravity Forms Plugin Widget
        unset($wp_meta_boxes['dashboard']['normal']['core']['pb_backupbuddy_stats']);      // Backup Buddy Plugin Widget
        unset($wp_meta_boxes['dashboard']['normal']['core']['wpseo-dashboard-overview']);      // YOAST SEO Plugin Widget
        unset($wp_meta_boxes['dashboard']['normal']['core']['e-dashboard-overview']);      // elementor Plugin Widget
        unset($wp_meta_boxes['dashboard']['normal']['core']['wsal']);      // Security audit log Plugin Widget
        
        //wp_add_dashboard_widget('admin_dashboard_box', 'Horizon-Cumulus - Hébergement géré', array(&$this,'hc_admin_dashboard_box'));
        add_meta_box('hc_dashboard_box', 'Hébergement géré', array(&$this, 'hc_admin_dashboard_box'), 'dashboard', 'side', 'high');
    }
    
    function hc_admin_dashboard_box()
    {
        $infobox = get_option("hc-infobox");
        
        if ($infobox) {
            echo urldecode($infobox);
            echo "<strong>Serveur : </strong>" . gethostname();
        } else {
            ?>
            <p>Votre site est entretenu par <a href="https://ca.horizon-cumulus.com"
                                               target="_blank">Horizon-Cumulus</a><br/>
                <?php
                $forfait = get_option("hc-forfait");
                if ($forfait) {
                    ?>
                    <strong>Votre forfait :</strong> <?php echo $forfait; ?><br/>
                    <?php
                }
                ?>
            </p>
            <h2 style="font-size: 18px;  font-weight: 600">Questions? Commentaires?</h2>
            <p><a href="https://ca.horizon-cumulus.com" target="_blank"><img
                            src="<?php echo plugins_url('img/logo.png', __FILE__); ?>" width="150px"></a></p>
            <p>
                <strong>En direct avec un agent :</strong> <a href="javascript:showTawk();"
                                                              id="tawtto_link"></a><br/>
                <strong>Ouvrir un billet d'assistance : </strong> <a
                        href="https://ca.horizon-cumulus.com/submitticket.php?step=2&deptid=1"
                        target="_blank">Cliquer ici</a><br/>
                <strong>Par courriel :</strong> <a
                        href="mailto:assistance@horizon-cumulus.ca">assistance@horizon-cumulus.ca</a><br/>
                <strong>Par téléphone :</strong> 1 877 966-4225
            </p>
            <?php
            
            echo "<strong>Serveur : </strong>" . gethostname();
        }
        
        
    }
    
    function admin_footer_tawkto()
    {
        
        global $pagenow;
        if ($pagenow == "index.php" || isset($_COOKIE["tawtto_active"])) {
            $admin_email = get_option('admin_email');
            $twakto_hash = get_option('tawkto_hash');
            $current_user = wp_get_current_user();
            
            $revendeur = get_option("hc-revendeur");
            
            if (!$revendeur) {
                ?>
                <!--Start of Tawk.to Script-->
                <script type="text/javascript">
                    function setCookie(cname, cvalue, exhours) {
                        var d = new Date();
                        d.setTime(d.getTime() + (exhours * 60 * 60 * 1000));
                        var expires = "expires=" + d.toUTCString();
                        document.cookie = cname + "=" + cvalue + ";path=/;" + expires;
                    }

                    var Tawk_API = Tawk_API || {};

                    Tawk_API.visitor = {
                        name: '<?php echo $current_user->display_name;  ?>',
                        email: '<?php echo $admin_email;  ?>',
                        hash: '<?php echo $twakto_hash; ?>'
                    };

                    Tawk_LoadStart = new Date();

                    Tawk_API.onLoad = function () {
                        <?php if(!isset($_COOKIE["tawtto_active"])){
                        ?>
                        Tawk_API.hideWidget();
                        <?php
                        } ?>

                        Tawk_API.addTags(['WordPress']);

                        var pageStatus = Tawk_API.getStatus();
                        if (pageStatus === 'online') {
                            jQuery("#tawtto_link").html('<span class="dot dot-green"></span>En ligne');
                        } else if (pageStatus === 'away') {
                            jQuery("#tawtto_link").html('<span class="dot dot-red"></span>Hors ligne');
                        } else {
                            jQuery("#tawtto_link").html('<span class="dot dot-red"></span>Hors ligne');
                        }
                    };

                    Tawk_API.onStatusChange = function (status) {
                        if (status === 'online') {
                            jQuery("#tawtto_link").html('<span class="dot dot-green"></span>En ligne');
                        } else if (status === 'away') {
                            jQuery("#tawtto_link").html('<span class="dot dot-red"></span>Hors ligne');
                        } else {
                            jQuery("#tawtto_link").html('<span class="dot dot-red"></span>Hors ligne');
                        }
                    };

                    Tawk_API.onChatStarted = function () {
                        setCookie("tawtto_active", 1, 2);
                    };

                    Tawk_API.onChatEnded = function () {
                        document.cookie = "tawtto_active=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                    };

                    function showTawk() {
                        Tawk_API.showWidget();
                        Tawk_API.maximize();
                    }

                    (function () {
                        var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
                        s1.async = true;
                        s1.src = 'https://embed.tawk.to/5e2f4188daaca76c6fd01deb/default';
                        s1.charset = 'UTF-8';
                        s1.setAttribute('crossorigin', '*');
                        s0.parentNode.insertBefore(s1, s0);
                    })();


                </script>
                <!--End of Tawk.to Script-->
                <?php
            }
        }
    }
    
    function valet_after_rocket_clean_domain($root, $lang, $url)
    {
        global $wp_mainwp_stream;
        if ($wp_mainwp_stream) {
            $wp_mainwp_stream->log->log("Cache", "Cache WP-Rocket Vider", array(), null, "WP-Rocket", "Clean domain");
        }
    }
    
    /* Ne sont pas utilisé conserver au besoin */
    function valet_after_rocket_clean_post($post, $purge_urls, $lang)
    {
        global $wp_mainwp_stream;
        
        if ($wp_mainwp_stream) {
            $wp_mainwp_stream->log->log("Cache", "valet_after_rocket_clean_post" . PHP_EOL . $post->post_title . PHP_EOL . $purge_urls, array(), $post->ID, "WP-Rocket", "Clean post");
        }
        
    }
    
    function valet_after_rocket_clean_home()
    {
        global $wp_mainwp_stream;
        if ($wp_mainwp_stream) {
            $wp_mainwp_stream->log->log("Cache", "valet_after_rocket_clean_home", array(), null, "WP-Rocket", "Clean home");
        }
    }
    
    function valet_after_rocket_clean_file($url)
    {
        global $wp_mainwp_stream;
        if ($wp_mainwp_stream) {
            $wp_mainwp_stream->log->log("Cache", "valet_after_rocket_clean_file" . PHP_EOL . $url, array(), null, "WP-Rocket", "Clean file");
        }
    }
    
    
    function wphc_admin_valet_page()
    {
        include("page-settings.php");
        
    }
    
}


$hc_maintenance = new hc_maintenance();