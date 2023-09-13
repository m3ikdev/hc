<?php


if (!defined('ABSPATH')) {
    exit;
}


class ecoumene_woocommerce_hc
{
    
    public function __construct()
    {
        
        add_action('init', array($this, 'init'));
        add_action('wp_head', array($this, 'head'));
        add_filter('woof_before_term_name', array($this, 'woof_before_term_name'), 10, 2);
        
        add_filter('woocommerce_product_tabs', array($this, 'woo_product_tabs'), 98);
        
        add_filter('manage_product_posts_columns', array($this, 'define_columns'), 98, 1);
        add_action('manage_product_posts_custom_column', array($this, 'render_columns'), 10, 2);
        
        add_action('wp_ajax_woocommerce_coeur_product', array($this, 'coeur_product'), 10, 2);
        add_action('wp_ajax_woocommerce_promo_product', array($this, 'promo_product'), 10, 2);
        
        
        add_action('woocommerce_product_query', array($this, 'woocommerce_product_query'), 1);
        add_action('woocommerce_before_shop_loop', array($this, 'filtre_tag'), 19);
        
        // add_action('woocommerce_single_product_summary',  array($this, 'woocommerce_format'), 25);
        
        
        add_action('woocommerce_before_add_to_cart_form', array($this, 'woocommerce_variation_btns'), 10);
        add_filter('woocommerce_related_products', array($this, 'woocommerce_related_products'), 10, 3);
        add_action('elementor/query/product_article', array($this, 'product_article'));
        add_action('elementor/query/article_produit', array($this, 'article_produit'));
        add_action('elementor/query/product_upsells', array($this, 'product_upsells'));
        add_action('elementor/query/product_onsales', array($this, 'product_onsales'));
        
        
        add_filter('woocommerce_account_menu_items', array($this, 'woocommerce_account_menu_items'), 10, 2);
        
        add_filter('elementor/theme/get_location_templates/template_id', array($this, 'get_location_templates'), 10, 1);
        
        add_action('woocommerce_shortcode_current_query_loop_no_results', array($this, 'render_no_results'));
        
        add_filter('woocommerce_package_rates', array($this, 'woocommerce_package_rates'), 10, 2);
        
        
        add_filter('woocommerce_get_price_html', array($this, 'bbloomer_price_free_zero_empty'), 100, 2);
        
        add_action('woocommerce_admin_order_data_after_order_details', array($this, 'action_woocommerce_order_item_add_action_buttons'), 10, 1);
        add_action('init', array($this, 'woocommerce_disable_init'));
        
        add_action('woocommerce_cart_loaded_from_session', array($this, 'bbloomer_sort_cart_items_alphabetically'));
        add_filter('woocommerce_ship_to_different_address_checked', '__return_false');
        add_action('manage_shop_order_posts_custom_column', array($this, 'woo_render_shipping_address_column'), 99, 2);
        
        
        add_action('admin_menu', array($this, 'rapport_tax_page_menu_page'));
        
        add_action('admin_init', array($this, 'download_export_rapport'), 1);
        
        
        add_filter('woocommerce_login_redirect', array($this, 'iconic_login_redirect'), 10, 2);
        
        
        add_filter('woocommerce_lost_password_message', array($this, 'woocommerce_lost_password_message'), 10, 1);
        
        add_shortcode('role', array($this, 'sc_userrole'));
        
        
        add_action('wpo_wcpdf_before_order_details', array($this, 'wpo_wcpdf_before_order_details'), 10, 2);
        
        add_filter("get_previous_post_join", array($this, 'get_nextprevious_post_join'), 10, 5);
        add_filter("get_next_post_join", array($this, 'get_nextprevious_post_join'), 10, 5);
        
        add_filter("get_previous_post_where", array($this, 'get_nextprevious_post_where'), 10, 5);
        add_filter("get_next_post_where", array($this, 'get_nextprevious_post_where'), 10, 5);
        
        add_action( 'woocommerce_product_options_inventory_product_data',array($this, 'product_settings_fields'), 1 );
        add_action( 'woocommerce_product_after_variable_attributes',array($this, 'variation_settings_fields'), 1, 3 );
        
        // Save Variation Settings
        add_action( 'woocommerce_process_product_meta', array($this,'woocommerce_process_product_meta'), 10, 1 );
        add_action( 'woocommerce_save_product_variation', array($this,'save_variation_settings_fields'), 10, 2 );
        
        
    add_filter( 'woocommerce_get_availability_text',  array($this,'filter_product_availability_text'), 10, 2);
    
    
  //  add_action( 'woocommerce_order_status_failed_to_processing',  array($this,'woocommerce_order_status_failed_encours'), 1, 2);
    
         add_action( 'init', array($this, 'register_custom_order_status') );
        add_filter( 'wc_order_statuses',  array($this, 'add_custom_to_order_statuses'),10,1 );
        
        
           add_filter( 'bulk_actions-edit-shop_order', array($this,'misha_register_bulk_action') );

    add_action( 'admin_action_mark_en-traitement', array($this,'misha_bulk_process_en_traitement_status' ));
    add_action( 'admin_action_mark_expedie-en-partie', array($this,'misha_bulk_process_expedie_en_partie_status' ));
    add_action( 'admin_action_mark_cueillette-a-leco', array($this,'misha_bulk_process_cueillette_a_leco_status' ));
    
    //add_action('admin_notices', 'misha_custom_order_status_notices');
    
    
    
    add_action( 'woocommerce_before_checkout_billing_form',array($this,'substituts_field'),1,1 );
    
    add_action( 'woocommerce_checkout_update_order_meta', array($this,'substituts_checkout_field_update_order_meta') );
    //woocommerce_thankyou
    
    add_action( 'woocommerce_thankyou', array($this,'substituts_woocommerce_thankyou'),11,1 );
    add_action('woocommerce_checkout_process', array($this,'substituts_checkout_field_process'));
    add_action( 'woocommerce_admin_order_data_after_billing_address',array($this, 'substituts_checkout_field_display_admin_order_meta'), 10, 1 );
    add_action( 'woocommerce_email_customer_details',array($this,'email_acceptez_substituts'),23,3);
    
    
    add_action( 'wpo_wcpdf_after_order_data', array($this,'substituts_wcpdf_after_order_data'),11,2 );
    //wpo_wcpdf_after_order_data
    
}

function substituts_wcpdf_after_order_data($type, $order){
    ?>
                    <tr class="payment-method">
                    <th><?php _e('J\'accepte les substituts :', 'woocommerce-pdf-invoices-packing-slips'); ?></th>
                   <td><?php echo ((get_post_meta( $order->id, 'substituts', true ) == 1) ? "Oui" : "Non")  ?></td>
                </tr>
    <?php
}

function substituts_woocommerce_thankyou($order_id){
    echo '<p><strong>'.__('Acceptez-vous les substituts').' :</strong><br />' . ((get_post_meta( $order_id, 'substituts', true ) == 1) ? "Oui" : "Non")  . '</p>';
}

function substituts_field( $checkout ) {
    echo '<div class="acceptez-substituts">';
    woocommerce_form_field( 'substituts', array(
        'type'          => 'radio',
		'class'         => array('input-radio'),
        'label'         => "<span class='text'>Acceptez-vous les substitutions en cas de rupture de stock ?</span>",
		'checked'       => true,
		'required' => true,
		'options' => array(1=>"Oui",0=>"Non"),
        ),  $checkout->get_value( 'substituts' ));

    echo '</div>';

}


function substituts_checkout_field_process() {
    // Check if set, if its not set add an error.
   
    if ( !isset($_POST['substituts']) )
        wc_add_notice( __( '<b>Acceptez-vous les substituts?</b> est obligatoire!' ), 'error' );
}

function substituts_checkout_field_update_order_meta( $order_id ) {
    if ( ! empty( $_POST['substituts'] ) ) {
        update_post_meta( $order_id, 'substituts', sanitize_text_field( $_POST['substituts'] ) );
    }
}

function substituts_checkout_field_display_admin_order_meta($order){

    echo '<p><strong>'.__('Acceptez-vous les substituts').':</strong><br />' . ((get_post_meta( $order->id, 'substituts', true ) == 1) ? "Oui" : "Non")  . '</p>';

}
function email_acceptez_substituts( $order, $sent_to_admin, $plain_text){
    echo '<br /><p><b>Acceptez-vous les substituts?</b> ' .   ((get_post_meta( $order->id, 'substituts', true )) ? "Oui" : "Non") ."</p>";

}
    
    
    function register_custom_order_status() {
        
        register_post_status( 'wc-en-traitement', array(
            'label'                     => 'En traitement',
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'En traitement (%s)', 'En traitement (%s)' )
        ) );
        
       register_post_status( 'wc-expedie-en-partie', array(
            'label'                     => 'Expédiée en partie',
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Expédiée en partie (%s)', 'Expédiée en partie (%s)' )
        ) );
        
        register_post_status( 'wc-cueillette-a-leco', array(
            'label'                     => 'Cueillette à écoumène',
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Cueillette à écoumène (%s)', 'Cueillette à écoumène (%s)' )
        ) );
    }
    
    function add_custom_to_order_statuses( $order_statuses ) {
     

                $order_statuses['wc-en-traitement'] =  'En traitement';
  
                $order_statuses['wc-expedie-en-partie'] = 'Expédiée en partie';

                $order_statuses['wc-cueillette-a-leco'] = 'Cueillette à écoumène';

        return $order_statuses;
    }
   
    
    function misha_register_bulk_action( $bulk_actions ) {
     
    	$bulk_actions['mark_en-traitement'] = 'Marquer En traitement';
    	$bulk_actions['mark_expedie-en-partie'] = 'Marquer Expédiée en partie';
    	$bulk_actions['mark_cueillette-a-leco'] = 'Marquer Cueillette à écoumène';
    	return $bulk_actions;
     
    }
    
    /*
     * Bulk action handler
     * Make sure that "action name" in the hook is the same like the option value from the above function
     */
     
    function misha_bulk_process_en_traitement_status() {
        $this->bulk_process_custom_status("en-traitement");
    }
        function misha_bulk_process_expedie_en_partie_status() {
        $this->bulk_process_custom_status("expedie-en-partie");
    }
        function misha_bulk_process_cueillette_a_leco_status() {
        $this->bulk_process_custom_status("cueillette-a-leco");
    }
    function bulk_process_custom_status($status){
     
    	// if an array with order IDs is not presented, exit the function
    	if( !isset( $_REQUEST['post'] ) && !is_array( $_REQUEST['post'] ) )
    		return;
     
    	foreach( $_REQUEST['post'] as $order_id ) {
     
    		$order = new WC_Order( $order_id );
    		$order_note = 'Modification de masse : ';
    		$order->update_status( $status, $order_note, true ); // "misha-shipment" is the order status name (do not use wc-misha-shipment)
     
    	}
     
    	// of course using add_query_arg() is not required, you can build your URL inline
    	$location = add_query_arg( array(
        		'post_type' => 'shop_order',
    		'marked_awaiting_shipment' => 1, // markED_awaiting_shipment=1 is just the $_GET variable for notices
    		'changed' => count( $_REQUEST['post'] ), // number of changed orders
    		'ids' => join( $_REQUEST['post'], ',' ),
    		'post_status' => 'all'
    	), 'edit.php' );
     
    	wp_redirect( admin_url( $location ) );
    	exit;
    }
    

    
    function misha_custom_order_status_notices() {
     
    	global $pagenow, $typenow;
     
    	if( $typenow == 'shop_order'
    	 && $pagenow == 'edit.php'
    	 && isset( $_REQUEST['marked_awaiting_shipment'] )
    	 && $_REQUEST['marked_awaiting_shipment'] == 1
    	 && isset( $_REQUEST['changed'] ) ) {
     
    		$message = sprintf( _n( 'Order status changed.', '%s order statuses changed.', $_REQUEST['changed'] ), number_format_i18n( $_REQUEST['changed'] ) );
    		echo "<div class=\"updated\"><p>{$message}</p></div>";
     
    	}
     
    }
    
    function woocommerce_order_status_failed_encours($order_id, $order){
         
         wp_mail("ecoumene-aaaaa7dycuarara77injqgmrhm@horizon-cumulus.slack.com","Ecoumene Commande " .$order_id. " - Erreur => En cours",wp_debug_backtrace_summary());
        // $order->update_status("failed","Forcer le status à échoué - ", true);
        // remove_all_actions("woocommerce_order_status_failed_to_processing_notification",10);
    }

    function filter_product_availability_text( $availability, $product ) {
        $date_of_availability =  get_post_meta( $product->get_id(), '_restock_date', true );

        if(  $product->get_parent_id() && empty($date_of_availability) ){
          
            $date_of_availability =  get_post_meta( $product->get_parent_id(), '_restock_date', true );
        }
        if ( ! $product->is_in_stock() && ! empty($date_of_availability) && $date_of_availability != "-" ) {
    
            if(strlen($date_of_availability) == 4){
                if(date("Y") <= $date_of_availability){
                    $availability .= '<span> - De retour en ' . $date_of_availability. '</span>';
                }
            }else{
                if(date("Y-m") <= $date_of_availability){
                    $availability .= '<span> - De retour en ' . date_i18n("F Y",strtotime($date_of_availability)). '</span>';
                }
            }
        }
        return $availability;
    }
    
     function product_settings_fields( ) {
        $options = array();
        $options[""] = "Ne pas afficher";
        $cyear = date("Y");
        $cmonth = date("n");
        for ($year = $cyear; $year < $cyear +2; $year++){
            $options[$year] = $year;
            
            for ($month = $cmonth; $month <= 12; $month++){
                $options[date("Y-m",strtotime($year. "-". $month))] = date_i18n("F Y",strtotime($year. "-". $month));
            }
            $cmonth=1;
        }
        $options[$cyear+2] = $cyear+2;
        
    	// Select
    	woocommerce_wp_select(
    	array(
    		'id'          => '_restock_date[' .get_the_ID(). ']',
    		'label'       => __( 'De retour en ', 'woocommerce' ),
    		//'description' => __( 'Choose a value.', 'woocommerce' ),
    		'value'       => get_post_meta( get_the_ID(), '_restock_date', true ),
    		'options' => $options
    		)
    	);
    
    }
    function variation_settings_fields( $loop, $variation_data, $variation ) {
     
        $options = array();
        $options[""] = "Identique au parent";
        $options["-"] = "Ne pas afficher";
        $cyear = date("Y");
        $cmonth = date("n");
        for ($year = $cyear; $year < $cyear +2; $year++){
            $options[$year] = $year;
            
            for ($month = $cmonth; $month <= 12; $month++){
                $options[date("Y-m",strtotime($year. "-". $month))] = date_i18n("F Y",strtotime($year. "-". $month));
            }
            $cmonth=1;
        }
        $options[$cyear+2] = $cyear+2;
        
    	// Select
    	woocommerce_wp_select(
    	array(
    		'id'          => '_restock_date[' . $variation->ID . ']',
    		'label'       => __( 'De retour en ', 'woocommerce' ),
    		//'description' => __( 'Choose a value.', 'woocommerce' ),
    		'value'       => get_post_meta( $variation->ID, '_restock_date', true ),
    		'options' => $options
    		)
    	);
    
    }
    
    function save_variation_settings_fields( $post_id ) {

    	// Select
    	$select = $_POST['_restock_date'][ $post_id ];
    	update_post_meta( $post_id, '_restock_date', esc_attr( $select ) );
    	
    }
    
    
    function woocommerce_process_product_meta($post_id) {

    	$select = $_POST['_restock_date'][ $post_id ];
        update_post_meta( $post_id, '_restock_date', esc_attr( $select ) );
    	
    }
    
    function get_nextprevious_post_join($join, $in_same_term, $excluded_terms, $taxonomy, $post)
    {
        global $wpdb;
        
        if ($post->post_type == "product" && !$in_same_term) {
            $join .= " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
        }
        return $join;
    }
    
    function get_nextprevious_post_where($where, $in_same_term, $excluded_terms, $taxonomy, $post)
    {
        global $wpdb;
        
        if ($post->post_type == "product" && !$in_same_term) {
            
            $taxonomy = "product_cat";
            $term_array = wp_get_object_terms($post->ID, $taxonomy, array('fields' => 'ids'));
            
            // Remove any exclusions from the term array to include.
            $term_array = array_diff($term_array, (array)$excluded_terms);
            $term_array = array_map('intval', $term_array);
            
            if (!$term_array || is_wp_error($term_array)) {
                return $where;
            }
            $terms = array();
            if ($term_array) {
                foreach ($term_array as $term_id) {
                    
                    
                    $term = get_term($term_id, $taxonomy);
                    if ($term->parent > 0) {
                        $terms[] = $term_id;
                    }
                    
                }
            }
            
            if ($terms) {
                $where .= $wpdb->prepare('AND tt.taxonomy = %s', $taxonomy);
                $where .= ' AND tt.term_id IN (' . implode(',', $terms) . ')';
            }
        }
        return $where;
    }
    
    
    function sc_userrole()
    {
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            return empty($user) ? "" : $user->roles[0];
        }
        
        return "";
    }
    
    function wpo_wcpdf_before_order_details($type, $order)
    {
        if ($order->customer_id) {
            $user = get_userdata($order->customer_id);
            if ($user) {
                if (in_array('detaillant30', $user->roles)) {
                    if ($message = get_field("detaillant30_facture_message", "options")) {
                        ?>
                        <p style="margin-bottom: 20px; padding: 10px;  background-color: #ededed; border: 1px solid #cccc;"><?php echo $message ?></p>
                        <?php
                    }
                }
                
                if (in_array('detaillant34', $user->roles)) {
                    if ($message = get_field("detaillant34_facture_message", "options")) {
                        ?>
                        <p style="margin-bottom: 20px; padding: 10px;  background-color: #ededed; border: 1px solid #cccc;"><?php echo $message ?></p>
                        <?php
                    }
                }
                
                if (in_array('detaillant40', $user->roles)) {
                    if ($message = get_field("detaillant40_facture_message", "options")) {
                        ?>
                        <p style="margin-bottom: 20px; padding: 10px;  background-color: #ededed; border: 1px solid #cccc;"><?php echo $message ?></p>
                        <?php
                    }
                }
            }
        }
    }
    
    function woocommerce_lost_password_message($str)
    {
        return "Mot de passe perdu ? Veuillez saisir votre identifiant ou votre adresse e-mail. <br> <br>
        Vous recevrez un lien par e-mail pour créer un nouveau mot de passe. Si vous ne recevez pas le courriel dans les minutes qui suivent, assurez-vous de vérifier dans la boîte courriel des indésirables (spam, pourriel).";
    }
    
    function woo_render_shipping_address_column($column, $post_id)
    {
        if ('shipping_address' === $column) {
            $order = new WC_Order($post_id);
            echo "<br />";
            $this->action_woocommerce_order_item_add_action_buttons($order, true);
        }
    }
    
    
    function bbloomer_sort_cart_items_alphabetically()
    {
        
        global $woocommerce;
        
        // READ CART ITEMS
        $products_in_cart = array();
        foreach ($woocommerce->cart->cart_contents as $key => $item) {
            $products_in_cart[$key] = remove_accents($item['data']->get_title());
        }
        
        // SORT CART ITEMS
        natsort($products_in_cart);
        
        // ASSIGN SORTED ITEMS TO CART
        $cart_contents = array();
        foreach ($products_in_cart as $cart_key => $product_title) {
            $cart_contents[$cart_key] = $woocommerce->cart->cart_contents[$cart_key];
        }
        $woocommerce->cart->cart_contents = $cart_contents;
        
    }
    
    function action_woocommerce_order_item_add_action_buttons($order, $liste = false)
    {
        
        ?>
        <button type="button" id="etiqette-dymo-<?php echo $order->ID ?>" class="button generate-items"
                style="display: none; <?php if (!$liste) { ?>position: absolute;
                        right: 15px;
                        bottom: 15px; <?php } ?>">Étiquette Dymo
        </button>
        <script type="text/javascript"
                src="<?php echo plugins_url("/js/DYMO.Label.Framework.2.0.js", __DIR__) ?>"></script>
        <script type="text/javascript"
                src="<?php echo plugins_url("/js/qrcode.js", __DIR__) ?>"></script>
        <script>
            jQuery(function ($) {

                // stores loaded label info
                var barcodeLabel;
                var printersSelect;
                $("#etiqette-dymo-<?php echo $order->ID ?>").on("click", function (e) {

                    // Run's Dymo Javascript..
                    dymo.label.framework.init(onload);
                    
                    <?php if($order->data["shipping"]["address_1"] ){ ?>
                    var label_text = '<?php if($order->data["shipping"]["company"]){ echo $order->data["shipping"]["company"] ?>' + String.fromCharCode(13) + String.fromCharCode(13) + '<?php } ?><?php echo addslashes($order->data["shipping"]["first_name"] . " " . $order->data["shipping"]["last_name"]) ?> ' + String.fromCharCode(13) + String.fromCharCode(13) + '<?php echo addslashes($order->data["shipping"]["address_1"]) ?> ' + String.fromCharCode(13) <?php if($order->data["shipping"]["address_2"]){ ?> + '<?php echo addslashes($order->data["shipping"]["address_2"]) ?> ' + String.fromCharCode(13) <?php } ?> + '<?php echo addslashes($order->data["shipping"]["city"] . ", " . $order->data["shipping"]["state"])  ?>' + String.fromCharCode(13) + '<?php echo addslashes($order->data["shipping"]["postcode"])  ?>';
                    <?php }else { ?>
                    var label_text = '<?php if($order->data["billing"]["company"]){ echo $order->data["billing"]["company"] ?>' + String.fromCharCode(13) + String.fromCharCode(13) + '<?php } ?><?php echo addslashes($order->data["billing"]["first_name"] . " " . $order->data["billing"]["last_name"]) ?> ' + String.fromCharCode(13) + String.fromCharCode(13) + '<?php echo addslashes($order->data["billing"]["address_1"]) ?> ' + String.fromCharCode(13) <?php if($order->data["billing"]["address_2"]){ ?> + '<?php echo addslashes($order->data["billing"]["address_2"]) ?> ' + String.fromCharCode(13) <?php } ?> + '<?php echo addslashes($order->data["billing"]["city"] . ", " . $order->data["billing"]["state"]) ?>' + String.fromCharCode(13) + '<?php echo addslashes($order->data["billing"]["postcode"])  ?>';
                    <?php } ?>
                    barcodeLabel.setObjectText('Barcode', label_text);

                    // Should Be Printer Name, Dymo 450 Turbo..
                    console.log("print: ", printersSelect);

                    barcodeLabel.print(printersSelect);
                });

                var printers = dymo.label.framework.getLabelWriterPrinters();
                if (printers.length > 0) {
                    $("#etiqette-dymo-<?php echo $order->ID ?>").show();
                } else {
                    //$("#etiqette-dymo-<?php echo $order->ID ?>").show();
                }

                // called when the document loaded
                function onload() {

                    // loads all supported printers into a combo box
                    function loadPrinters() {
                        var printers = dymo.label.framework.getLabelWriterPrinters();
                        if (printers.length == 0) {
                            alert("No DYMO printers are installed. Install DYMO printers.");
                            return;
                        }
                        console.log("got here: ", printers);

                        for (var i = 0; i < printers.length; i++) {
                            var printer = printers[i];

                            printersSelect = printer.name;
                        }
                    }


                    function loadLabelFromWeb() {
                        barcodeLabel = dymo.label.framework.openLabelXml(getBarcodeLabelXml());
                    }

                    // Load Labels
                    loadLabelFromWeb();

                    // load printers list on startup
                    loadPrinters();
                };
            });

        </script>
        
        
        <?php
        
    }
    
    function woocommerce_disable_init()
    {
        if (!isset($_SESSION)) session_start();
        if (isset($_SESSION["disable_wc"]) && $_SESSION["disable_wc"]) {
            remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
            remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
            remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
            add_filter( 'woocommerce_available_payment_gateways','__return_false');
            
        }
    }
    
    function woocommerce_package_rates($rates, $package)
    {
        if (in_array($package["destination"]["country"], array("CA"))) {
            $tarifs = array(
                "cheque-cadeau" => array(
                    "type" => "fixe",
                    "min" => 5,
                    "frais_supp" => 0,
                    "max" => 5,
                    "qte" => 0,
                    "montant" => 0
                ),
                "standard-leger" => array(
                    "type" => "fixe",
                    "min" => 12.75,
                    "frais_supp" => 0.5,
                    "max" => 150.75,
                    "qte" => 0,
                    "montant" => 0
                ),
                "standard" => array(
                    "type" => "fixe",
                    "min" => 12.75,
                    "frais_supp" => 1,
                    "max" => 150.75,
                    "qte" => 0,
                    "montant" => 0
                ),
                "intermediaire" => array(
                    "type" => "fixe",
                    "min" => 16.75,
                    "frais_supp" => 2,
                    "max" => 150.75,
                    "qte" => 0,
                    "montant" => 0
                ),
                "non-standard" => array(
                    "type" => "fixe",
                    "min" => 25.75,
                    "frais_supp" => 2,
                    "max" => 150.75,
                    "qte" => 0,
                    "montant" => 0),
                "non-standard-b" => array(
                    "type" => "fixe",
                    "min" => 25.75,
                    "frais_supp" => 25.75,
                    "max" => 150.75,
                    "qte" => 0,
                    "montant" => 0),
                "colis-surdimensionnes" => array(
                    "type" => "fixe",
                    "min" => 49.75,
                    "frais_supp" => 49.75,
                    "max" => 150.75,
                    "qte" => 0,
                    "montant" => 0),
                "semence-sachet" => array(
                    "type" => "montant",
                    "min" => 5,
                    "frais_supp" => array(27 => 5),
                    "max" => 5,
                    "qte" => 0,
                    "montant" => 0),
                "saisonnier" => array(
                    "type" => "fixe",
                    "min" => 16.75,
                    "frais_supp" => 2,
                    "max" => 150.75,
                    "qte" => 0,
                    "montant" => 0),
                "petite-saisonniere" => array(
                    "type" => "fixe",
                    "min" => 12.95,
                    "frais_supp" => 0.5,
                    "max" => 150.75,
                    "qte" => 0,
                    "montant" => 0),
                "petit-colis" => array(
                    "type" => "fixe",
                    "min" => 6.95,
                    "frais_supp" => 1,
                    "max" => 150.75,
                    "qte" => 0,
                    "montant" => 0
                ),
            );
        } elseif (in_array($package["destination"]["country"], array("US"))) {
            $tarifs = array(
                "cheque-cadeau" => array("type" => "fixe", "min" => 0, "qte" => 0, "montant" => 0),
                "standard-leger" => array("type" => "fixe", "min" => 0, "qte" => 0, "montant" => 0),
                "standard" => array("type" => "fixe", "min" => 0, "qte" => 0, "montant" => 0),
                "intermediaire" => array("type" => "fixe", "min" => 0, "qte" => 0, "montant" => 0),
                "non-standard" => array("type" => "fixe", "min" => 0, "qte" => 0, "montant" => 0),
                "non-standard-b" => array("type" => "fixe", "min" => 0, "qte" => 0, "montant" => 0),
                "colis-surdimensionnes" => array("type" => "fixe", "min" => 0, "qte" => 0, "montant" => 0),
                "saisonnier" => array("type" => "fixe", "min" => 0, "qte" => 0, "montant" => 0),
                "petite-saisonniere" => array("type" => "fixe", "min" => 0, "qte" => 0, "montant" => 0),
                  "petit-colis" => array("type" => "fixe", "min" => 0, "qte" => 0, "montant" => 0),
                "semence-sachet" => array(
                    "type" => "montant",
                    "min" => 19.95,
                    "frais_supp" => array(60 => 29.95, 100 => 39.95),
                    "max" => 39.95,
                    "qte" => 0,
                    "montant" => 0),
            );
        } else {
            $tarifs = array(
                "cheque-cadeau" => array("type" => "fixe", "min" => 0, "qte" => 0, "montant" => 0),
                "standard-leger" => array("type" => "fixe", "min" => 0, "qte" => 0, "montant" => 0),
                "standard" => array("type" => "fixe", "min" => 0, "qte" => 0, "montant" => 0),
                "intermediaire" => array("type" => "fixe", "min" => 0, "qte" => 0, "montant" => 0),
                "non-standard" => array("type" => "fixe", "min" => 0, "qte" => 0, "montant" => 0),
                "non-standard-b" => array("type" => "fixe", "min" => 0, "qte" => 0, "montant" => 0),
                "colis-surdimensionnes" => array("type" => "fixe", "min" => 0, "qte" => 0, "montant" => 0),
                "saisonnier" => array("type" => "fixe", "min" => 0, "qte" => 0, "montant" => 0),
                "petite-saisonniere" => array("type" => "fixe", "min" => 0, "qte" => 0, "montant" => 0),
                  "petit-colis" => array("type" => "fixe", "min" => 0, "qte" => 0, "montant" => 0),
                "semence-sachet" => array(
                    "type" => "montant",
                    "min" => 16.95,
                    "frais_supp" => array(30 => 20.95, 50 => 29.95, 100 => 39.95),
                    "max" => 39.95,
                    "qte" => 0,
                    "montant" => 0),
            );
        }
        
        $debug = array();
        
        foreach ($package["contents"] as $p) {
            if ($p) {
                $shipping_class = $p['data']->get_shipping_class();
                if (isset($tarifs[$shipping_class])) {
                    $tarifs[$shipping_class]["qte"] += $p["quantity"];
                    $tarifs[$shipping_class]["montant"] += $p["line_total"];
                }
            }
        }
        
        $sup_max = 0;
        $min_class = "";
        $min = 0;
        $montant = 0;
        $montant_semence = 0;
        $montant_saisonnier= 0;
        $montant_petitsaisonnier = 0;
        $max = 0;
        $max_class = "";
        
        $semence_only = true;
        $cheque_cadeau = false;
        $saisonnier = false;
        $petitsaisonnier = false;
        $nonstandard = false;
        $nonstandard_b = false;
        $surdimensionnes = false;
        
        foreach ($tarifs as $class => $tarif) {
            switch ($tarif["type"]) {
                case "fixe":
                    if ($tarif["qte"]) {
                        $debug["stock"][$class] = $tarif["qte"];
                        
                        switch ($class) {
                           /*
                            case "non-standard":
                                $semence_only = false;
                                if (!$nonstandard) {
                                    $debug["min"][$class] = $tarif["min"];
                                    $montant += $tarif["min"];
                                    $nonstandard = true;
                                }
                                
                                break;*/
                            case "non-standard-b":
                                $semence_only = false;
                                if (!$nonstandard_b) {
                                    $debug["min"][$class] = $tarif["min"];
                                    $montant += $tarif["min"];
                                    $nonstandard_b = true;
                                }
                                
                                break;
                            case "colis-surdimensionnes":
                                
                                $semence_only = false;
                                if (!$surdimensionnes) {
                                    $debug["min"][$class] = $tarif["min"];
                                    $montant += $tarif["min"];
                                    $surdimensionnes = true;
                                }
                                
                                break;
                            case "saisonnier":
                                
                                $semence_only = false;
                                if (!$saisonnier) {
                                    $debug["min"][$class] = $tarif["min"];
                                    $montant_saisonnier += $tarif["min"];
                                    $saisonnier = true;
                                }
                                
                                break;
                            case "petite-saisonniere":
                                
                                $semence_only = false;
                                if (!$saisonnier) {
                                    $debug["min"][$class] = $tarif["min"];
                                    $montant_petitsaisonnier += $tarif["min"];
                                    $petitsaisonnier = true;
                                }
                                
                                break;
                           case "cheque-cadeau":
                                $cheque_cadeau = true;
                                 if ($min < $tarif["min"]) {
                                    $min_class = $class;
                                    $min = $tarif["min"];
                                }
                                
                                break;
                            default:
                                $semence_only = false;
                                if ($min < $tarif["min"]) {
                                    $min_class = $class;
                                    $min = $tarif["min"];
                                }
                                break;
                        }
                        
                        //if ($tarif["qte"] > 1) {
                            $debug[$class] = array("Qte" => $tarif["qte"], "frais supplémentaire" => $tarif["frais_supp"], "frais" => $tarif["qte"]  * $tarif["frais_supp"]);
                            $montant += $tarif["qte"] * $tarif["frais_supp"];
                       // }
                         if($sup_max < $tarif["frais_supp"]){
                                $sup_max =  $tarif["frais_supp"];
                            }
                        if ($max < $tarif["max"]) {
                            $max = $tarif["max"];
                            $max_class = $class;
                        }
                    }
                    break;
                case "montant";
                    if ($tarif["qte"]) {
                        $debug["stock"][$class] = $tarif["qte"];
                        
                        $frais_max = $tarif["min"];
                        foreach ($tarif["frais_supp"] as $m => $frais) {
                            if ($tarif["montant"] > $m && $frais_max < $frais) {
                                $frais_max = $frais;
                            }
                        }
                        
                        $montant_semence += $frais_max;
                    }
                    break;
            }
            
        }
         if($sup_max){
            $montant -= $sup_max;
            $debug["sup_max"] = array($sup_max);
        }
        $debug["min"][$min_class] = $min;
        $montant = $min + $montant;
        if ($montant > $max) {
            $montant = $max;
            $debug["max"] = array($max_class => $max);
        }
        unset($_SESSION["disable_wc"]);
        if ($semence_only) {
            if ($saisonnier || $petitsaisonnier || $nonstandard || $nonstandard_b || $surdimensionnes) {
                $montant += $montant_semence;
            } else {
                if (in_array($package["destination"]["country"], array("CA"))) {
                    if ($tarifs["semence-sachet"]["montant"]  > 25) { //TODO Montant minimumpour livraison gratuite
                        $montant = 0;
                        
                        $debug["semence-sachet"] = array("Gratuit" => $tarifs["semence-sachet"]["montant"]);
                    } else {
                        $debug["semence-sachet"] = array("Montant" => $tarifs["semence-sachet"]["montant"], "total" => $montant_semence);
                        if(!$cheque_cadeau || $montant < $montant_semence){
                            $montant = $montant_semence;
                        }
                        
                    }
                } else {
                     if(!$cheque_cadeau || $montant < $montant_semence){
                            $montant = $montant_semence;
                        }
                }
            }
            
        } else {
             if ($saisonnier || $petitsaisonnier) {
                $montant += $montant_semence + $montant_petitsaisonnier + $montant_saisonnier;
            }
            
            if (!in_array($package["destination"]["country"], array("CA"))) {
                unset($rates["flat_rate:17"]);
                $_SESSION["disable_wc"] = true;
                remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
                remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
                remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
                add_filter( 'woocommerce_available_payment_gateways','__return_false');
            }
        }
        $debug["total"] = $montant;
        if (is_cart() && (get_option("woocommerce_shipping_debug_mode") == "yes" || isset($_GET["debug"]))) {
            echo "<pre>";
            print_r($debug);
            
            // print_r($rates);
            echo "</pre>";
        }
        
        $methods = WC()->shipping()->get_shipping_methods();
        
        if (in_array($package["destination"]["country"], array("CA"))) {
            if ($montant) {
                if (isset($rates["flat_rate:13"])) {
                    $rates["flat_rate:13"]->cost = $montant;
                    
                    if ($method = $methods[$rates["flat_rate:13"]->get_method_id()]) {
                        $taxes = WC_Tax::calc_shipping_tax($rates["flat_rate:13"]->cost, WC_Tax::get_shipping_tax_rates());
                        $rates["flat_rate:13"]->taxes = $taxes;
                        // $rates["flat_rate:13"]->set_taxes($method->is_taxable() ? $taxes : array());
                    }
                    // $rates["flat_rate:13"]->taxes = $taxes;
                    
                    unset($rates["free_shipping:14"]);
                }
            } else {
                if (isset($rates["free_shipping:14"])) {
                    unset($rates["flat_rate:13"]);
                }
            }
        } else {
            if (isset($rates["flat_rate:17"])) {
                $rates["flat_rate:17"]->cost = $montant;
                if ($method = $methods[$rates["flat_rate:17"]->get_method_id()]) {
                    $taxes = WC_Tax::calc_shipping_tax($rates["flat_rate:17"]->cost, WC_Tax::get_shipping_tax_rates());
                    $rates["flat_rate:17"]->taxes = $taxes;
                }
                //  $rates["flat_rate:17"]->taxes = $taxes;
                unset($rates["local_pickup:18"]);
            }
        }
        
        return $rates;
    }
    
    
    public function render_no_results()
    {
        
        if (isset($_REQUEST["recherche"])) {
            $this->filtre_tag();
            echo '<div class="elementor-nothing-found elementor-products-nothing-found">' . "Il semble que nous ne trouvions pas ce que vous cherchez." . '</div>';
        }
    }
    
    function get_location_templates($theme_template_id)
    {
        //var_dump($theme_template_id);
         if (get_query_var('paged')) {
            $paged = get_query_var('paged');
        } elseif (get_query_var('page')) { // 'page' is used instead of 'paged' on Static Front Page
            $paged = get_query_var('page');
        } else {
            $paged = 1;
        }
        if ( ($paged  > 1 || isset($_REQUEST["recherche"])) && in_array($theme_template_id, array(4102, 4059, 3929, 3905))) {
            return 3951;
        }
        
        return $theme_template_id;
    }
    
    function woocommerce_account_menu_items($items, $endpoints)
    {
        
        /*unset($items['downloads']);*/
        unset($items['payment-methods']);
        return $items;
    }
    
    
    function woocommerce_related_products($related_posts, $product_id, $options)
    {
        global $product;
        if ($product) {
            $upsells = $product->get_upsells();
            $limit_rel = $options["limit"] - count($upsells);
            if ($limit_rel > 0) {
                $related_posts = array_slice($related_posts, 0, $limit_rel);
            } else {
                $related_posts = array();
            }
            
        }
        return array_merge($related_posts, $upsells);
    }
    
    function init()
    {
        add_shortcode('meta_arbo', array($this, 'sc_meta_arbo'));
        add_shortcode('meta_tags', array($this, 'sc_meta_tags'));
        
    }
    
    function head()
    {
        
        if (get_field("pepiniere_seulement")) {
            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
            remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
        }
    }
    
    function filtre_tag()
    {
        $prod_attribute = null;
        if (isset($_POST["attribute"])) {
            $prod_attribute = $_POST["attribute"];
        }
        
        if($prod_attribute){
            $terms = get_terms($prod_attribute);
            $attribute_obj = get_taxonomy($prod_attribute);?>
            <h2 class="attribute-selection-title"><?=str_replace('Produit ', '',$attribute_obj->label)?> :</h2>
            <form action="" method="post" id="form_woo_attributes">
                <input type="hidden" name="attribute" value="<?=$prod_attribute?>">
            <?php foreach ($terms as $t) {
                if ($image = get_field("_thumbnail_id", $t->taxonomy . "_" . $t->term_id)) {
                    ?>
                        <div class="woo_filtres">
                            <div class="woo_filtre woo_filtre_img"
                                    data-tooltips="<?php echo $t->name ?>"><input
                                        type="checkbox"
                                        id="filtre_<?php echo $t->taxonomy ?>_<?php echo $t->slug ?>"
                                        name="tax[<?php echo $t->taxonomy ?>][]"
                                        value="<?php echo $t->slug ?>"
                                    <?php if (isset($_POST["tax"][$t->taxonomy]) && in_array($t->slug, $_POST["tax"][$t->taxonomy])) echo "CHECKED" ?> />
                                <label for="filtre_<?php echo $t->taxonomy ?>_<?php echo $t->slug ?>">
                                    <img src="<?php echo $image["sizes"]["thumbnail"] ?>"/>
                                </label>
                            </div>
                        </div>
                <?php } ?>
                
            <?php }?>
            </form>
            <?php
        }

        $filtres = array();
        if (isset($_POST["tax"])) {
            foreach ($_POST["tax"] as $tax => $terms) {
                if (!in_array($tax, array("product_cat"))) {
                    foreach ($terms as $term) {
                        $filtres[] = array("tax" => $tax, "slug" => $term, "term" => get_term_by("slug", $term, $tax));
                    }
                }
            }
        }
        
        if ($filtres) {
            ?>
            <ul class="filtre_tags">
                <?php foreach ($filtres as $term) { ?>
                    <li class="filtre_tag">
                        <?php
                        switch ($term["slug"]) {
                            case "promo":
                                echo "Promotion";
                                break;
                            case "featured":
                                echo "Nouveautés";
                                break;
                            case "coeur":
                                echo "Meilleurs vendeurs";
                                break;
                            default:
                                echo $term["term"]->name;
                                break;
                        }
                        ?>
                        <button type="button" data-term="<?php echo $term["tax"] . "_" . $term["slug"]; ?>">X</button>
                    </li>
                <?php } ?>
                <li class="filtre_tag filtre_reinit">
                    <button type="reset">Réinitialisation</button>
                </li>
            </ul>

            <?php
        }
    }
    
    function woof_before_term_name($term, $tax)
    {
        if ($image = get_field("_thumbnail_id", $term["taxonomy"] . "_" . $term["term_id"])) {
            return '<img src="' . $image["sizes"]["thumbnail"] . '" />';
        } else {
            return $term["name"];
        }
    }
    
    function woo_product_tabs($tabs)
    {
      // if(isset($_GET["dev"])){ var_dump($tabs); }
  
        if (isset($tabs["description"])) {
            $tabs["description"]["priority"] = 23;
        }
        if (isset($tabs["additional_information"])) {
            $tabs["additional_information"]["priority"] = 24;
        }
        $field_tabs = get_field("tab", "options");
        
        // $culture_attributs = get_field("culture_attributs", "options");
        foreach ($field_tabs as $k => $tab) {
            $has_tab = false;
            foreach ($tab["sections"] as $section) {
                if ($has_tab) continue;
                foreach ($section["section"] as $attribute) {
                    if ($has_tab) continue;
                    $terms = get_the_terms(get_the_ID(), "pa_" . $attribute["attribut"]);
                    if ($terms && !$terms->errors) {
                        $has_tab = true;
                        continue;
                    }
                }
            }
            if ($has_tab) {
                $tabs["tab_" . $k] = array("title" => $tab["titre"], "priority" => $k + 1, "callback" => array($this, "woocommerce_picto_tab"));
            }
        }
        return $tabs;
    }
    
    function woocommerce_picto_tab($tab_id)
    {
        
        $field_tabs = get_field("tab", "options");
        foreach ($field_tabs as $k => $tab) {
            if ($tab_id == "tab_" . $k) {
                // $culture_attributs = get_field("culture_attributs", "options");
                //ob_start();
                ?>
                <div>
                    <?php
                    foreach ($tab["sections"] as $section) {
                        $show_titre = false;
                        $has_item = false;
                        foreach ($section["section"] as $a=> $attribute) {
                            switch ($attribute["type"]) {
                                case "icon":
                                    $terms = get_the_terms(get_the_ID(), "pa_" . $attribute["attribut"]);
                                    if ($terms) {
                                        $has_item = true;
                                        if (!$show_titre) {
                                            $show_titre = true;
                                            ?>
                                            <div class="element">
                                            <h4><?php echo $section["nom"] ?></h4>
                                            <?php
                                        }
                                        foreach ($terms as $term) {
                                            if ($image = get_field("_thumbnail_id", $term->taxonomy . "_" . $term->term_id)) {
                                                
                                                $attr_id = wc_attribute_taxonomy_id_by_name($term->taxonomy);
                                                $attr_taxonomy = wc_get_attribute($attr_id);
                                                ?>
                                                <div style="display: inline-block; padding: 0 3px; "
                                                     data-tooltips="<?php echo $attr_taxonomy->name ?> : <?php echo $term->name ?>">
                                                    <?php if ($attr_taxonomy->has_archives) { ?>
                                                        <a href="<?php echo get_term_link($term, $term->taxonomy) ?>"><img
                                                                    src="<?php echo $image["sizes"]["thumbnail"] ?>"/></a>
                                                    <?php } else { ?>
                                                        <img src="<?php echo $image["sizes"]["thumbnail"] ?>"/>
                                                    <?php } ?>
                                                </div>
                                                <?php
                                            }
                                        }
                                    }
                                    break;
                                case "liste":
                                    $has_item2 = false;
                                
                                    foreach ($attribute["liste"] as $k => $liste) {
                                        $terms = get_the_terms(get_the_ID(), "pa_" . $liste["attribut"]);
                                        if ($terms) {
                                            $has_item = true;
                                            $has_item2 = true;
                                            $attribute["liste"][$k]["terms"] = $terms;
                                        }
                                    }
                                    
                                    if ($has_item) {
                                        if (!$show_titre) {
                                            $show_titre = true;
                                          ?>
                                              <div class="element element-type-liste">
                                              <h4><?php echo $section["nom"] ?></h4>
                                              <?php
                                        }
                                        
                                        if ($has_item2) {
                                            ?>
                                            <style>
                                                #element-liste-<?php echo $a ?> { border-left-color: <?php echo $attribute["couleur"] ?>33; }
                                                #element-liste-<?php echo $a ?> .element-list-content .element-list-lien { color: <?php echo $attribute["couleur"] ?>; }
                                                #element-liste-<?php echo $a ?> .element-list-content .element-list-lien:hover { border-color: <?php echo $attribute["couleur"] ?>; }
                                            </style>

                                        <div id="element-liste-<?php echo $a ?>" class="element-liste" >
                                            <div class="element-list-titre">
                                                <h3><?php echo $attribute["titre"] ?></h3>
                                            </div>

                                            <div class="element-list-content">
                                                <?php
                                                foreach ($attribute["liste"] as $liste) {
                                                    if($liste["terms"]){
                                                    
                                                    if ($attribute["separer"]) {
                                                        ?>
                                                        <div class="element-list-section">
                                                        <div class="element-list-section-titre"><?php echo $liste["titre"] ?> :</div><div class="element-list-section-lien"><?php
                                                       
                                                            foreach ($liste["terms"] as $term) {
                                                                ?><a href="<?php echo get_term_link($term, $term->taxonomy) ?>"
                                                                   class="element-list-lien"><?php echo $term->name; ?></a><?php
                                                            } ?></div>
                                                        </div>
                                                        <?php
                                                    } else {
                                                        foreach ($liste["terms"] as $term) {
                                                            ?><a href="<?php echo get_term_link($term, $term->taxonomy) ?>"
                                                               class="element-list-lien"><?php echo $term->name; ?></a><?
                                                        }
                                                    }
                                                }
                                                    }
                                                ?>
                                            </div>
                                            </div><?php
                                        }
                                    }
                                    
                                    
                                    break;
                                
                            }
                            
                            
                        }
                        if ($has_item) {
                            ?></div><?php
                        }
                    }
                    ?>
                </div>
                <?php
            }
        }
        // return ob_get_clean();
    }
    
    function woocommerce_format()
    {
        global $product;
        if (is_a($product, "WC_Product_Variable")) {
            $formats = $product->get_children(true);
            ?>

            <div class="formats">
                <div class="list_formats">
                    <?php
                    if ($formats) {
                        foreach ($formats as $format) {
                            $term_slug = get_field("attribute_pa_format", $format);
                            $active = "";
                            if ($product->default_attributes["pa_format"] == $term_slug) {
                                $active = "active";
                            }
                            $term = get_term_by("slug", $term_slug, "pa_format");
                            
                            echo '<a class="item-format ' . $active . '" href="javascript:void(0);" title="' . $term->name . '"  >' . $term->name . '</a>';
                        }
                    }
                    ?>
                </div>

            </div>
            <script>
                jQuery(function ($) {
                    $(".variations_form.cart .variations").hide();
                    $(".item-swatch").bind("click", function () {
                        $("#pa_produit_couleur").val($(this).attr("data-slug"));
                        $("#pa_produit_couleur").trigger("change")
                        $(".item-swatch").not(this).removeClass("active");
                        $(this).addClass("active");


                        if ($(".woocommerce-variation-availability .stock").hasClass("out-of-stock")) {

                            $("#showstock").html("");
                            $("#msg-out-of-stock").html($(".woocommerce-variation-availability .stock").html());
                        } else {

                            $("#showstock").html($(".woocommerce-variation-availability").html());
                            $("#msg-out-of-stock").html("");
                        }

                        $(".woocommerce-variation-availability").hide();
                        $("#swatch_name").html($(this).attr("title"));
                        $("#sku-description").html($(this).attr("title-sku"));
                    });

                    setTimeout(function () {
                        $(".item-swatch.active").click();
                    }, 200);

                });
            </script>
            <?php
        }
    }
    
    public function define_columns($columns)
    {
        //var_dump($columns);
        $columns["coeur"] = '<span class="wc-coeur parent-tips" data-tip="' . esc_attr__('Meilleurs vendeurs', 'ecoumene-hc') . '">' . __('Meilleurs vendeurs', 'ecoumene-hc') . '</span>';
        $columns["promo"] = '<span class="wc-promo parent-tips" data-tip="' . esc_attr__('Promo', 'ecoumene-hc') . '">' . __('Promo', 'ecoumene-hc') . '</span>';
        
        return $columns;
    }
    
    
    public function render_columns($column, $post_id)
    {
        switch ($column) {
            case "coeur":
                $this->render_coeur_column($post_id);
                break;
            case "promo":
                $this->render_promo_column($post_id);
                break;
        }
    }
    
    protected function render_coeur_column($post_id)
    {
        $product = wc_get_product($post_id);
        $url = wp_nonce_url(admin_url('admin-ajax.php?action=woocommerce_coeur_product&product_id=' . $post_id), 'woocommerce-coeur-product');
        echo '<a href="' . esc_url($url) . '" aria-label="' . esc_attr__('Toggle featured', 'woocommerce') . '">';
        //var_dump($value);
        if (has_term("coeur", "product_visibility", $post_id)) {
            echo '<span class="wc-coeur tips" data-tip="' . esc_attr__('Yes', 'woocommerce') . '">' . esc_html__('Yes', 'woocommerce') . '</span>';
        } else {
            echo '<span class="wc-coeur not-coeur tips" data-tip="' . esc_attr__('No', 'woocommerce') . '">' . esc_html__('No', 'woocommerce') . '</span>';
        }
        echo '</a>';
    }
    
    protected function render_promo_column($post_id)
    {
        $product = wc_get_product($post_id);
        $url = wp_nonce_url(admin_url('admin-ajax.php?action=woocommerce_promo_product&product_id=' . $post_id), 'woocommerce-promo-product');
        echo '<a href="' . esc_url($url) . '" aria-label="' . esc_attr__('Toggle featured', 'woocommerce') . '">';
        //var_dump($value);
        if (has_term("promo", "product_visibility", $post_id)) {
            echo '<span class="wc-promo tips" data-tip="' . esc_attr__('Yes', 'woocommerce') . '">' . esc_html__('Yes', 'woocommerce') . '</span>';
        } else {
            echo '<span class="wc-promo not-promo tips" data-tip="' . esc_attr__('No', 'woocommerce') . '">' . esc_html__('No', 'woocommerce') . '</span>';
        }
        echo '</a>';
    }
    
    public static function coeur_product()
    {
        if (current_user_can('edit_products') && check_admin_referer('woocommerce-coeur-product')) {
            
            if (has_term("coeur", "product_visibility", absint($_GET['product_id']))) {
                wp_remove_object_terms(absint($_GET['product_id']), "coeur", "product_visibility");
            } else {
                wp_set_object_terms(absint($_GET['product_id']), "coeur", "product_visibility", true);
            }
            
        }
        
        wp_safe_redirect(wp_get_referer() ? remove_query_arg(array('trashed', 'untrashed', 'deleted', 'ids'), wp_get_referer()) : admin_url('edit.php?post_type=product'));
        exit;
    }
    
    
    public static function promo_product()
    {
        if (current_user_can('edit_products') && check_admin_referer('woocommerce-promo-product')) {
            
            if (has_term("promo", "product_visibility", absint($_GET['product_id']))) {
                wp_remove_object_terms(absint($_GET['product_id']), "promo", "product_visibility");
            } else {
                wp_set_object_terms(absint($_GET['product_id']), "promo", "product_visibility", true);
            }
            
        }
        
        wp_safe_redirect(wp_get_referer() ? remove_query_arg(array('trashed', 'untrashed', 'deleted', 'ids'), wp_get_referer()) : admin_url('edit.php?post_type=product'));
        exit;
    }
    
    function woocommerce_product_query($q)
    {
        if (get_query_var('paged')) {
            $paged = get_query_var('paged');
        } elseif (get_query_var('page')) { // 'page' is used instead of 'paged' on Static Front Page
            $paged = get_query_var('page');
        } else {
            $paged = 1;
        }
        
        if (isset($_GET["v"])) {
            if ($_GET["v"] == "nouveaute") {
                $_POST["tax"]["product_visibility"][] = "featured";
            }
            
            if ($_GET["v"] == "coeur") {
                $_POST["tax"]["product_visibility"][] = "coeur";
            }
            
            if ($_GET["v"] == "promo") {
                $_POST["tax"]["product_visibility"][] = "promo";
            }
        }
        
        if (isset($_POST["tax"])) {
            $_SESSION["filtre_tax"] = $_POST["tax"];
            if (is_paged()) {
                $paged = 1;
            }
        } else {
            if (isset($_SESSION["filtre_tax"])) {
                if (is_paged()) {
                    $_POST["tax"] = $_SESSION["filtre_tax"];
                    
                } else {
                    $_SESSION["filtre_tax"] = null;
                    unset($_SESSION["filtre_tax"]);
                }
            }
        }
        
        $post__in = $q->get('post__in');
        if (isset($_POST["tax"]["product_visibility"]) && in_array("promo", $_POST["tax"]["product_visibility"])) {
            $post__in = wc_get_product_ids_on_sale();
        }
        
        $tax_query = $q->get('tax_query');
        if (isset($_POST["tax"]) && $_POST["tax"]) {
            $tax_query['relation'] = 'AND';
            foreach ($_POST["tax"] as $taxs => $terms) {
                if ($taxs == "product_visibility") {
                    $terms = array_diff($terms, array("promo"));
                }
                if ($terms) {
                    foreach ($terms as $term) {
                        $tax_query[] = array(
                            "taxonomy" => $taxs,
                            "terms" => $term,
                            "field" => 'slug',
                        );
                    }
                }
            }
        }

        //print_r($tax_query);
        
        if ($paged > 1) {
            $q->set('paged', $paged);
        }
        $q->set('post__in', $post__in);
        $q->set('tax_query', $tax_query);
    }
    
    function sc_meta_arbo()
    {
        extract(shortcode_atts(array(
            'id' => get_the_ID(),
        ), $atts));
        
        global $product;
        ob_start();
        if ($product && (has_term(array("semences","produits-de-la-foret"), "product_cat", $product->get_id()))) {
            ?>
            <p class="meta_arbo">
                <?php
                if (has_term(null, "pa_famille", $product->get_id())) { ?>
                    <span class="famille"><?php echo get_the_term_list($product->get_id(), 'pa_famille', '', ', ', ''); ?></span>
                    <i class="fal fa-chevron-right"></i>
                <?php } ?>
                <?php if ($nom_latin = get_field("nom_latin")) { ?>
                    <span class="nom_latin"><?php echo get_field("nom_latin") ?></span>
                    <?php if (!has_term("selections", "product_cat", $product->get_id())) { ?>
                        <i class="fal fa-chevron-right"></i>
                    <?php } ?>
                <?php } ?>
                <span class="cycle"><?php echo get_the_term_list($product->get_id(), 'pa_cycle-de-vie', '', ', ', ''); ?></span>
            </p>
            <?php
        }
        return ob_get_clean();
    }
    
    function sc_meta_tags()
    {
        extract(shortcode_atts(array(
            'id' => get_the_ID(),
        ), $atts));
        
        global $product;
        ob_start();
        ?>
        <div class="elementor-widget-wrap">
            <?php
            if ($product && $product->is_on_sale()) { ?>
                <div class="elementor-element elementor-element-0e65eb5 elementor-widget__width-auto elementor-widget elementor-widget-button"
                     data-id="0e65eb5" data-element_type="widget" data-widget_type="button.default">
                    <div class="elementor-widget-container">
                        <div class="elementor-button-wrapper">
                            <a href="#" class="elementor-button-link elementor-button elementor-size-xs"
                               role="button">
        						<span class="elementor-button-content-wrapper">
        						<span class="elementor-button-text">PROMO</span>
        		</span>
                            </a>
                        </div>
                    </div>
                </div>
            
            <?php } ?>
            <?php
            if (has_term("coeur", "product_visibility", get_the_ID())) { ?>
                <div class="elementor-element elementor-element-f179d2b elementor-widget__width-auto elementor-widget elementor-widget-button"
                     data-id="f179d2b" data-element_type="widget" data-widget_type="button.default">
                    <div class="elementor-widget-container">
                        <div class="elementor-button-wrapper">
                            <a href="#" class="elementor-button-link elementor-button elementor-size-xs"
                               role="button">
        						<span class="elementor-button-content-wrapper">
        						<span class="elementor-button-icon elementor-align-icon-left">
        				<i class="fa fa-heart" aria-hidden="true"></i>
        			</span>
        						<span class="elementor-button-text">Meilleurs vendeurs</span>
        		</span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <?php
            if (has_term("featured", "product_visibility", get_the_ID())) { ?>
                <div class="elementor-element elementor-element-412332a elementor-widget__width-auto elementor-widget elementor-widget-button"
                     data-id="412332a" data-element_type="widget" data-widget_type="button.default">
                    <div class="elementor-widget-container">
                        <div class="elementor-button-wrapper">
                            <a href="#" class="elementor-button-link elementor-button elementor-size-xs"
                               role="button">
        						<span class="elementor-button-content-wrapper">
        						<span class="elementor-button-text">NOUVEAUTÉ</span>
        		</span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>

        </div>
        <?php
        return ob_get_clean();
    }
    
    
    function product_article($query)
    {
        if ($article = get_post_meta(get_the_ID(), "articles", true)) {
            //var_dump($article);
            $query->set('post__in', $article);
        } else {
            $query->set('post__in', [0]);
        }
    }
    
    function article_produit($query)
    {
        if ($produits = get_post_meta(get_the_ID(), "produits", true)) {
            //var_dump($article);
            $query->set('post__in', $produits);
        } else {
            $query->set('post__in', [0]);
        }
    }
    
    
    function product_upsells($query)
    {
        $related_products = array();
        
        $related_products = wc_get_related_products(get_the_ID(), 6);
        //var_dump($related_products);
        $query->set('post__in', $related_products);
    }
    
    function product_onsales($query)
    {
        $post__in = wc_get_product_ids_on_sale();
        $query->set('post__in', $post__in);
    }
    
    
    function bbloomer_price_free_zero_empty($price, $product)
    {
        
        if ('' === $product->get_price() || 0 == $product->get_price()) {
            $price = '<span class="woocommerce-Price-amount amount">Gratuit</span>';
        }
        
        return $price;
    }
    
    function woocommerce_variation_btns()
    {
        global $product;
        
        
        $variation_view_type = get_field("variation_view_type");
        if ($product->is_type('variable') && (!$variation_view_type || $variation_view_type == "btns")) {
            ?>

            <div class="variation_btns" style="display: none">
                <?php
                
                $attributes = $product->get_variation_attributes();
                $only1attr = (count($attributes) == 1);
                $variations_attr = array();
                $variations = $product->get_available_variations();
                
                         //     if(isset($_GET["dev"])){ var_dump($variations); }
                
                foreach ($variations as $variation){
                    if($variation["attributes"]){
                        foreach ($variation["attributes"] as $attribute_name_slug => $attr){
                            if(!isset($variations_attr[str_replace("attribute_","",$attribute_name_slug)])){
                                $variations_attr[str_replace("attribute_","",$attribute_name_slug)] = array();
                            }
                            $variations_attr[str_replace("attribute_","",$attribute_name_slug)][] = $attr;
                        }
                    }
                }
                
                
                foreach ($variations_attr as $attribute_name => $options) {
                    $attribute_name_slug = strtolower(sanitize_title(remove_accents($attribute_name)));
               
                    $attribute_label_name = wc_attribute_label($attribute_name);
                    
                    echo "<div class='list_variation_btns'><label>" . $attribute_label_name . " :</label>";
                    $attr = $product->get_variation_attributes();
                    $active = "";
                    $selected = "";
                    $disable = "";
                    
                    if ($attribute_name && $product instanceof WC_Product) {
                        $selected_key = 'attribute_' . sanitize_title($attribute_name);
                        $selected = isset($_REQUEST[$selected_key]) ? wc_clean(wp_unslash($_REQUEST[$selected_key])) : $product->get_variation_default_attribute($attribute_name); // WPCS: input var ok, CSRF ok, sanitization ok.
                    }
                    
                    if (!empty($options)) {
                        if ($product && taxonomy_exists($attribute_name)) {
                            // Get terms if this is a taxonomy - ordered. We need the names too.
                            $terms = wc_get_product_terms($product->get_id(), $attribute_name, array(
                                'fields' => 'all',
                            ));
                            
                            foreach ($terms as $term) {
                                if (in_array($term->slug, $options, true)) {
                                    if ($selected == $term->slug) {
                                        $active = "active";
                                    }
                                    if ($only1attr) {
                                        foreach ($variations as $variation) {
                                            if (isset($variation["attributes"]["attribute_" . $attribute_name]) && $variation["attributes"]["attribute_" . $attribute_name] == $term->slug) {
                                                //var_dump($variation["is_in_stock"]);
                                                if (!$variation["is_in_stock"]) {
                                                    $disable = "disable";
                                                }
                                                continue;
                                            }
                                        }
                                        
                                    }
                                    echo '<a class="btn-item-variation tax_' . $attribute_name_slug . ' ' . $active . ' ' . $disable . '" href="javascript:void(0);" title="' . esc_html($term->name) . '" data-slug="' . esc_html($term->slug) . '" >' . esc_html(apply_filters('woocommerce_variation_option_name', $term->name)) . '</a>';
                                }
                            }
                        } else {
                            foreach ($options as $option) {
                                if ($selected == $option) {
                                    $active = "active";
                                }
                                if ($only1attr) {
                                    if ($product->get_manage_stock() && !$product->get_stock_quantity()) {
                                        $disable = "disable";
                                    }
                                }
                                echo '<a class="btn-item-variation tax_' . $attribute_name_slug . ' ' . $active . ' ' . $disable . '" href="javascript:void(0);" title="' . esc_html($option) . '" data-slug="' . esc_html($option) . '" >' . esc_html(apply_filters('woocommerce_variation_option_name', $option)) . '</a>';
                            }
                        }
                        
                    }
                    echo "</div>";
                }
                ?>
            </div>

            <script>
                jQuery(function ($) {
                    $(".variations_form.cart .variations").hide();
                    $(".variation_btns").show();
                    
                    <?php
                    foreach ($attributes as $attribute_name => $options) {
                    $attribute_name_slug = strtolower(sanitize_title(remove_accents($attribute_name)));
                    ?>
                    console.log(<?php echo json_encode($attribute_name_slug) ?>)
                    $(".btn-item-variation.tax_<?php echo $attribute_name_slug ?>").bind("click", function () {
                        $("select#<?php echo $attribute_name_slug ?>").val($(this).attr("data-slug"));
                        $("select#<?php echo $attribute_name_slug  ?>").trigger("change");

                        $(".btn-item-variation.tax_<?php echo $attribute_name_slug ?>").not(this).removeClass("active");
                        $(this).addClass("active");
                    });

                    setTimeout(function () {
                        if ($(".btn-item-variation.tax_<?php echo $attribute_name_slug ?>.active").length) {
                            $(".btn-item-variation.tax_<?php echo $attribute_name_slug ?>.active").first().click();
                        } else {
                            $(".btn-item-variation.tax_<?php echo $attribute_name_slug ?>").first().addClass("active").click();
                        }
                    }, 200);
                    
                    <?php } ?>
                });
            </script>
            <?php
        } else {
            ?>
            <script>
                jQuery(function ($) {
                    if ($(".voucher-fields-wrapper-variation").length > 0) {
                        $(".woocommerce-variation.single_variation").after("<div id='voucher-fields-wrapper-variation'></div>");
                        $(".voucher-fields-wrapper-variation").appendTo("#voucher-fields-wrapper-variation");
                    }
                });
            </script>
            
            <?php
        }
    }
    
        function rapport_tax_page_menu_page()
    {
        add_menu_page(
            'Rapport',
            'Rapport',
            'manage_options',
            'rapport',
            array($this, 'rapport_tax_page_menu_page_content')
        );
    }
    
    
    function rapport_tax_page_menu_page_content()
    {

        ?>
        <h1>Rapport de comptabilité</h1>
        <hr/>
        <form action="" method="post">
            <p><label>Entre le </label>
                <input type="date" name="debut" value="<?php echo date("Y-m-d", strtotime("-1 month")) ?>">
                et le <input type="date" name="fin" value="<?php echo date("Y-m-d") ?>">
            </p>
            <button name="download_export_rapport_comptable" value="1" type="submit">Télécharger</button>
        </form>

        <br/>
        <br/>
        <br/>
        <br/>
        <br/>
        <br/>
        <h1>Rapport de semences</h1>
        <hr/>
        <form action="" method="post">
            <button name="download_export_rapport_semence" value="1" type="submit">Télécharger</button>
        </form>

        <br/>
        <br/>
        <br/>
        <br/>
        <br/>
        <br/>
        <h1>Rapport d'inventaire</h1>
        <hr/>
        <form action="" method="post">
            <button name="download_export_rapport_inventaire" value="1" type="submit">Télécharger</button>
        </form>


        <br/>
        <br/>
        <br/>
        <br/>
        <br/>
        <br/>

        <h1>Rapport d'anomalie (Stock/OutofStock)</h1>
        <hr/>
        <form action="" method="post" target="_blank">
            <button name="download_export_rapport_anomalie" value="1" type="submit">Afficher</button>
        </form>
        <?php
    }
    
    function formattext($text)
    {
        return $text;
    }
    
    function download_export_rapport()
    {
        
        if (isset($_POST["download_export_rapport_comptable"]) && $_POST["download_export_rapport_comptable"]) {
            include("download_export_rapport_comptable.php");
        }
        if (isset($_POST["download_export_rapport_semence"]) && $_POST["download_export_rapport_semence"]) {
            include("download_export_rapport_semence.php");
        }
        
        if (isset($_POST["download_export_rapport_inventaire"]) && $_POST["download_export_rapport_inventaire"]) {
            include("download_export_rapport_inventaire.php");
        }
        
        if (isset($_POST["download_export_rapport_anomalie"]) && $_POST["download_export_rapport_anomalie"]) {
            include("download_export_rapport_anomalie.php");
        }
    }
    
    function get_post_id_by_slug($slug, $post_type = "post")
    {
        $query = new WP_Query(
            array(
                'name' => $slug,
                'post_type' => $post_type,
                'numberposts' => 1,
                'fields' => 'ids',
            ));
        $posts = $query->get_posts();
        return array_shift($posts);
    }
    
    
    function iconic_login_redirect($redirect, $user)
    {
        return '/mon-compte/';
    }
    
}

$ecoumene_woocommerce_hc = new ecoumene_woocommerce_hc();
