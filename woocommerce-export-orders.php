<?php 
/*
Plugin Name: WooCommerce Export Orders
Plugin URI: http://www.imaginate-solutions.com/
Description: This plugin lets store owners to export orders
Version: 0.1
Author: Dhruvin Shah
Author URI: http://www.imaginate-solutions.com/
*/

{
	/**
	 * Localisation
	 **/
	load_plugin_textdomain('woo-export-order', false, dirname( plugin_basename( __FILE__ ) ) . '/');

	/**
	 * woo_export class
	 **/
	if (!class_exists('woo_export')) {

		class woo_export {
				
			public function __construct() {
				
				// WordPress Administration Menu
				add_action('admin_menu', array(&$this, 'woo_export_orders_menu'));
				
				add_action( 'admin_enqueue_scripts', array(&$this, 'export_enqueue_scripts_css' ));
				add_action( 'admin_enqueue_scripts', array(&$this, 'export_enqueue_scripts_js' ));
			}
			
			/**
			 * Functions
			 */
			
			function export_enqueue_scripts_css() {
					
				if ( isset($_GET['page']) && $_GET['page'] == 'export_orders_page' )
				{
					wp_enqueue_style( 'woocommerce_admin_styles', plugins_url() . '/woocommerce/assets/css/admin.css' );
			
					wp_enqueue_style( 'dataTable', plugins_url('/css/data.table.css', __FILE__ ) , '', '', false);
			
					wp_enqueue_style( 'TableTools', plugins_url('/TableTools/media/css/TableTools.css', __FILE__ ) , '', '', false);
				}
			}
			
			function export_enqueue_scripts_js(){
				
				if (isset($_GET['page']) && $_GET['page'] == 'export_orders_page')
				{
					wp_register_script( 'dataTable', plugins_url().'/woocommerce-export-orders/js/jquery.dataTables.js');
					wp_enqueue_script( 'dataTable' );
						
					wp_register_script( 'TableTools', plugins_url().'/woocommerce-export-orders/TableTools/media/js/TableTools.js');
					wp_enqueue_script( 'TableTools' );
				
					wp_register_script( 'ZeroClip', plugins_url().'/woocommerce-export-orders/TableTools/media/js/ZeroClipboard.js');
					wp_enqueue_script( 'ZeroClip' );
				}
			}
			
			function woo_export_orders_menu(){
				
				add_menu_page( 'Export Orders','Export Orders','manage_woocommerce', 'export_orders_page');
				add_submenu_page('export_orders_page.php', __( 'Export Orders Settings', 'woo-export-order' ), __( 'Export Orders Settings', 'woo-export-order' ), 'manage_woocommerce', 'export_orders_page', array(&$this, 'export_orders_page' ));
				//remove_submenu_page('export_settings','exports_settings');
			}
			
			function export_orders_page(){
				
				global $wpdb;
				
				?>
				
					<br>
					<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
					<a href="admin.php?page=export_orders_page" class="nav-tab nav-tab-active"> <?php _e( 'Export Orders', 'woo-export-order' );?> </a>
					</h2>
				
				<?php 
				$query_order = "SELECT DISTINCT order_id FROM `" . $wpdb->prefix . "woocommerce_order_items`  ";
				$order_results = $wpdb->get_results( $query_order );
				
				$var = $today_checkin_var = $today_checkout_var = $booking_time = "";
				foreach ( $order_results as $id_key => $id_value )
				{
					$order = new WC_Order( $id_value->order_id );
					
					if ( $order->post_status == 'wc-completed' )
					{
					$order_items = $order->get_items();
					
					$my_order_meta = get_post_custom( $id_value->order_id );

					$c = 0;
					foreach ($order_items as $items_key => $items_value )
					{
						$var .= "<tr>
						<td>".$id_value->order_id."</td>
						<td>".$my_order_meta[_billing_first_name][0]." ".$my_order_meta[_billing_last_name][0]."</td>
						<td>".$items_value['name']."</td>
						<td>".$items_value['line_total']."</td>
						<td>".$order->completed_date."</td>
						<td><a href=\"post.php?post=". $id_value->order_id."&action=edit\">View Order</a></td>
						</tr>";
							
						$c++;
					}
					}
				}
				
				$swf_path = plugins_url()."/woocommerce-export-orders/TableTools/media/swf/copy_csv_xls.swf";
				?>

				<script>
					
					jQuery(document).ready(function() {
					 	var oTable = jQuery('.datatable').dataTable( {
								"bJQueryUI": true,
								"sScrollX": "",
								"bSortClasses": false,
								"aaSorting": [[0,'desc']],
								"bAutoWidth": true,
								"bInfo": true,
								"sScrollY": "100%",	
								"sScrollX": "100%",
								"bScrollCollapse": true,
								"sPaginationType": "full_numbers",
								"bRetrieve": true,
								"oLanguage": {
												"sSearch": "Search:",
												"sInfo": "Showing _START_ to _END_ of _TOTAL_ Orders",
												"sInfoEmpty": "Showing 0 to 0 of 0 entries",
												"sZeroRecords": "No Orders Completed yet",
												"sInfoFiltered": "(filtered from _MAX_total entries)",
												"sEmptyTable": "No Orders available in table",
												"sLengthMenu": "Number of Orders to show: _MENU_",
												"oPaginate": {
																"sFirst":    "First",
																"sPrevious": "Previous",
																"sNext":     "Next",
																"sLast":     "Last"
															  }
											 },
								 "sDom": 'T<"clear"><"H"lfr>t<"F"ip>',
						         "oTableTools": {
										            "sSwfPath": "<?php echo plugins_url(); ?>/woocommerce-export-orders/TableTools/media/swf/copy_csv_xls_pdf.swf"
										        }
								 
					} );
				} );
					
					       
					</script>



			<!-- <div style="float: left;">
				<h2>
					<strong>All Orders</strong>
				</h2>
			</div> -->
			<div>
				<table id="order_history" class="display datatable">
					<thead>
						<tr>
							<th><?php _e( 'Order ID' , 'woo-export-order' ); ?></th>
							<th><?php _e( 'Customer Name' , 'woo-export-order' ); ?></th>
							<th><?php _e( 'Product Name' , 'woo-export-order' ); ?></th>
							<th><?php _e( 'Amount' , 'woo-export-order' ); ?></th>
							<th><?php _e( 'Order Date' , 'woo-export-order' ); ?></th>
							<th><?php _e( 'Action' , 'woo-export-order' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php echo $var;?>
					</tbody>
				</table>
			</div>
			
			<?php 
								
			}
		}
	}
	
	$woo_export = new woo_export();
}
?>