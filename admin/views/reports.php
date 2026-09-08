<?php
/**
 * Reports admin view.
 *
 * @package Dispensary_WP
 */

use Dispensary_WP\Modules\Reports\Reports;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'dispensary_view_reports' ) ) {
	wp_die(
		esc_html__(
			'You do not have permission to view reports.',
			'dispensary-wp'
		)
	);
}

$reports = new Reports();

$tab = isset( $_GET['tab'] )
	? sanitize_key( wp_unslash( $_GET['tab'] ) )
	: 'sales';

$allowed_tabs = array(
	'sales',
	'inventory',
	'customers',
	'staff',
	'products',
	'financial',
);

if ( ! in_array( $tab, $allowed_tabs, true ) ) {
	$tab = 'sales';
}

/*
 * Date filters.
 */
$from_date = isset( $_GET['from_date'] )
	? sanitize_text_field( wp_unslash( $_GET['from_date'] ) )
	: gmdate( 'Y-m-01' );

$to_date = isset( $_GET['to_date'] )
	? sanitize_text_field( wp_unslash( $_GET['to_date'] ) )
	: gmdate( 'Y-m-d' );

/*
 * Load report data.
 */
$sales_summary     = array();
$daily_sales       = array();
$payment_status    = array();
$top_products      = array();
$inventory_summary = array();
$low_stock         = array();
$expiring_batches  = array();
$customer_summary  = array();
$top_customers     = array();
$staff_summary     = array();
$attendance        = array();
$product_sales     = array();
$revenue           = array();
$payments          = array();
$refunds           = array();

if ( 'sales' === $tab ) {
	$sales_summary  = $reports->sales_summary( $from_date, $to_date );
	$daily_sales    = $reports->daily_sales( $from_date, $to_date );
	$payment_status = $reports->sales_by_payment_status();
	$top_products   = $reports->top_products( 10 );
}

if ( 'inventory' === $tab ) {
	$inventory_summary = $reports->inventory_summary();
	$low_stock         = $reports->low_stock( 10 );
	$expiring_batches  = $reports->expiring_batches( 30 );
}

if ( 'customers' === $tab ) {
	$customer_summary = $reports->customer_summary();
	$top_customers    = $reports->top_customers( 10 );
}

if ( 'staff' === $tab ) {
	$staff_summary = $reports->staff_summary();
	$attendance    = $reports->staff_attendance( $from_date, $to_date );
}

if ( 'products' === $tab ) {
	$product_sales = $reports->product_sales( 50 );
}

if ( 'financial' === $tab ) {
	$revenue  = $reports->revenue( $from_date, $to_date );
	$payments = $reports->payments();
	$refunds  = $reports->refunds();
}

/*
 * Helper to safely get array/object values.
 */
$get_value = static function ( $data, $key, $default = 0 ) {

	if ( is_array( $data ) && isset( $data[ $key ] ) ) {
		return $data[ $key ];
	}

	if ( is_object( $data ) && isset( $data->{$key} ) ) {
		return $data->{$key};
	}

	return $default;
};
?>

<div class="wrap dispensary-wp-admin">

	<h1><?php esc_html_e( 'Reports', 'dispensary-wp' ); ?></h1>

	<form method="get" style="margin:15px 0;">
		<input type="hidden" name="page" value="dispensary-reports">
		<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>">

		<label for="from_date">
			<strong><?php esc_html_e( 'From', 'dispensary-wp' ); ?></strong>
		</label>

		<input
			type="date"
			id="from_date"
			name="from_date"
			value="<?php echo esc_attr( $from_date ); ?>"
		>

		<label for="to_date">
			<strong><?php esc_html_e( 'To', 'dispensary-wp' ); ?></strong>
		</label>

		<input
			type="date"
			id="to_date"
			name="to_date"
			value="<?php echo esc_attr( $to_date ); ?>"
		>

		<?php submit_button( __( 'Generate Report', 'dispensary-wp' ), 'secondary', 'submit', false ); ?>
	</form>

	<nav class="nav-tab-wrapper">

		<?php foreach ( $allowed_tabs as $report_tab ) : ?>

			<a
				href="<?php echo esc_url( admin_url( 'admin.php?page=dispensary-reports&tab=' . $report_tab ) ); ?>"
				class="nav-tab <?php echo $tab === $report_tab ? 'nav-tab-active' : ''; ?>"
			>
				<?php echo esc_html( ucfirst( $report_tab ) ); ?>
			</a>

		<?php endforeach; ?>

	</nav>

	<?php if ( 'sales' === $tab ) : ?>

		<div class="dispensary-wp-cards">

			<div class="dispensary-wp-card">
				<h3><?php esc_html_e( 'Orders', 'dispensary-wp' ); ?></h3>
				<strong>
					<?php echo esc_html( $get_value( $sales_summary, 'orders', 0 ) ); ?>
				</strong>
			</div>

			<div class="dispensary-wp-card">
				<h3><?php esc_html_e( 'Sales', 'dispensary-wp' ); ?></h3>
				<strong>
					<?php echo esc_html( $get_value( $sales_summary, 'sales', $get_value( $sales_summary, 'total', 0 ) ) ); ?>
				</strong>
			</div>

			<div class="dispensary-wp-card">
				<h3><?php esc_html_e( 'Tax', 'dispensary-wp' ); ?></h3>
				<strong>
					<?php echo esc_html( $get_value( $sales_summary, 'tax', 0 ) ); ?>
				</strong>
			</div>

			<div class="dispensary-wp-card">
				<h3><?php esc_html_e( 'Average Order', 'dispensary-wp' ); ?></h3>
				<strong>
					<?php echo esc_html( $get_value( $sales_summary, 'average_order', 0 ) ); ?>
				</strong>
			</div>

		</div>

		<div class="dispensary-wp-panel">

			<h2><?php esc_html_e( 'Daily Sales', 'dispensary-wp' ); ?></h2>

			<table class="widefat striped">

				<thead>
					<tr>
						<th><?php esc_html_e( 'Date', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'Orders', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'Sales', 'dispensary-wp' ); ?></th>
					</tr>
				</thead>

				<tbody>

					<?php if ( empty( $daily_sales ) ) : ?>

						<tr>
							<td colspan="3">
								<?php esc_html_e( 'No sales data found.', 'dispensary-wp' ); ?>
							</td>
						</tr>

					<?php else : ?>

						<?php foreach ( $daily_sales as $row ) : ?>

							<tr>
								<td>
									<?php echo esc_html( $get_value( $row, 'date', '' ) ); ?>
								</td>

								<td>
									<?php echo esc_html( $get_value( $row, 'orders', 0 ) ); ?>
								</td>

								<td>
									<?php echo esc_html( $get_value( $row, 'sales', $get_value( $row, 'total', 0 ) ) ); ?>
								</td>
							</tr>

						<?php endforeach; ?>

					<?php endif; ?>

				</tbody>

			</table>

		</div>

		<div class="dispensary-wp-panel">

			<h2><?php esc_html_e( 'Top Products', 'dispensary-wp' ); ?></h2>

			<table class="widefat striped">

				<thead>
					<tr>
						<th><?php esc_html_e( 'Product', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'Quantity', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'Sales', 'dispensary-wp' ); ?></th>
					</tr>
				</thead>

				<tbody>

					<?php foreach ( $top_products as $row ) : ?>

						<tr>
							<td>
								<?php
								echo esc_html(
									$get_value(
										$row,
										'name',
										$get_value( $row, 'product_name', '' )
									)
								);
								?>
							</td>

							<td>
								<?php echo esc_html( $get_value( $row, 'quantity', 0 ) ); ?>
							</td>

							<td>
								<?php echo esc_html( $get_value( $row, 'sales', $get_value( $row, 'total', 0 ) ) ); ?>
							</td>
						</tr>

					<?php endforeach; ?>

				</tbody>

			</table>

		</div>

	<?php elseif ( 'inventory' === $tab ) : ?>

		<div class="dispensary-wp-cards">

			<div class="dispensary-wp-card">
				<h3><?php esc_html_e( 'Products', 'dispensary-wp' ); ?></h3>
				<strong><?php echo esc_html( $get_value( $inventory_summary, 'products', 0 ) ); ?></strong>
			</div>

			<div class="dispensary-wp-card">
				<h3><?php esc_html_e( 'Stock Units', 'dispensary-wp' ); ?></h3>
				<strong><?php echo esc_html( $get_value( $inventory_summary, 'stock', $get_value( $inventory_summary, 'quantity', 0 ) ) ); ?></strong>
			</div>

			<div class="dispensary-wp-card">
				<h3><?php esc_html_e( 'Low Stock', 'dispensary-wp' ); ?></h3>
				<strong><?php echo esc_html( count( $low_stock ) ); ?></strong>
			</div>

			<div class="dispensary-wp-card">
				<h3><?php esc_html_e( 'Expiring Batches', 'dispensary-wp' ); ?></h3>
				<strong><?php echo esc_html( count( $expiring_batches ) ); ?></strong>
			</div>

		</div>

		<div class="dispensary-wp-panel">

			<h2><?php esc_html_e( 'Low Stock', 'dispensary-wp' ); ?></h2>

			<table class="widefat striped">

				<thead>
					<tr>
						<th>ID</th>
						<th><?php esc_html_e( 'Product', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'Stock', 'dispensary-wp' ); ?></th>
					</tr>
				</thead>

				<tbody>

					<?php foreach ( $low_stock as $row ) : ?>

						<tr>
							<td><?php echo esc_html( $get_value( $row, 'product_id', 0 ) ); ?></td>
							<td><?php echo esc_html( $get_value( $row, 'name', $get_value( $row, 'product_name', '' ) ) ); ?></td>
							<td><?php echo esc_html( $get_value( $row, 'stock', $get_value( $row, 'quantity', 0 ) ) ); ?></td>
						</tr>

					<?php endforeach; ?>

				</tbody>

			</table>

		</div>

	<?php elseif ( 'customers' === $tab ) : ?>

		<div class="dispensary-wp-cards">

			<div class="dispensary-wp-card">
				<h3><?php esc_html_e( 'Total Customers', 'dispensary-wp' ); ?></h3>
				<strong><?php echo esc_html( $get_value( $customer_summary, 'total', 0 ) ); ?></strong>
			</div>

			<div class="dispensary-wp-card">
				<h3><?php esc_html_e( 'Active Customers', 'dispensary-wp' ); ?></h3>
				<strong><?php echo esc_html( $get_value( $customer_summary, 'active', 0 ) ); ?></strong>
			</div>

		</div>

		<div class="dispensary-wp-panel">

			<h2><?php esc_html_e( 'Top Customers', 'dispensary-wp' ); ?></h2>

			<table class="widefat striped">

				<thead>
					<tr>
						<th><?php esc_html_e( 'Customer', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'Orders', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'Spent', 'dispensary-wp' ); ?></th>
					</tr>
				</thead>

				<tbody>

					<?php foreach ( $top_customers as $row ) : ?>

						<tr>
							<td>
								<?php
								echo esc_html(
									$get_value(
										$row,
										'name',
										$get_value( $row, 'customer_name', '' )
									)
								);
								?>
							</td>

							<td><?php echo esc_html( $get_value( $row, 'orders', 0 ) ); ?></td>

							<td><?php echo esc_html( $get_value( $row, 'spent', $get_value( $row, 'total', 0 ) ) ); ?></td>
						</tr>

					<?php endforeach; ?>

				</tbody>

			</table>

		</div>

	<?php elseif ( 'staff' === $tab ) : ?>

		<div class="dispensary-wp-cards">

			<div class="dispensary-wp-card">
				<h3><?php esc_html_e( 'Total Staff', 'dispensary-wp' ); ?></h3>
				<strong><?php echo esc_html( $get_value( $staff_summary, 'total', 0 ) ); ?></strong>
			</div>

			<div class="dispensary-wp-card">
				<h3><?php esc_html_e( 'Active Staff', 'dispensary-wp' ); ?></h3>
				<strong><?php echo esc_html( $get_value( $staff_summary, 'active', 0 ) ); ?></strong>
			</div>

		</div>

		<div class="dispensary-wp-panel">

			<h2><?php esc_html_e( 'Staff Attendance', 'dispensary-wp' ); ?></h2>

			<table class="widefat striped">

				<thead>
					<tr>
						<th><?php esc_html_e( 'Staff', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'Present', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'Absent', 'dispensary-wp' ); ?></th>
					</tr>
				</thead>

				<tbody>

					<?php foreach ( $attendance as $row ) : ?>

						<tr>
							<td><?php echo esc_html( $get_value( $row, 'name', $get_value( $row, 'staff_name', '' ) ) ); ?></td>
							<td><?php echo esc_html( $get_value( $row, 'present', 0 ) ); ?></td>
							<td><?php echo esc_html( $get_value( $row, 'absent', 0 ) ); ?></td>
						</tr>

					<?php endforeach; ?>

				</tbody>

			</table>

		</div>

	<?php elseif ( 'products' === $tab ) : ?>

		<div class="dispensary-wp-panel">

			<h2><?php esc_html_e( 'Product Sales Report', 'dispensary-wp' ); ?></h2>

			<table class="widefat striped">

				<thead>
					<tr>
						<th>ID</th>
						<th><?php esc_html_e( 'Product', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'Quantity', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'Sales', 'dispensary-wp' ); ?></th>
					</tr>
				</thead>

				<tbody>

					<?php foreach ( $product_sales as $row ) : ?>

						<tr>
							<td><?php echo esc_html( $get_value( $row, 'product_id', 0 ) ); ?></td>
							<td><?php echo esc_html( $get_value( $row, 'name', $get_value( $row, 'product_name', '' ) ) ); ?></td>
							<td><?php echo esc_html( $get_value( $row, 'quantity', 0 ) ); ?></td>
							<td><?php echo esc_html( $get_value( $row, 'sales', $get_value( $row, 'total', 0 ) ) ); ?></td>
						</tr>

					<?php endforeach; ?>

				</tbody>

			</table>

		</div>

	<?php elseif ( 'financial' === $tab ) : ?>

		<div class="dispensary-wp-cards">

			<div class="dispensary-wp-card">
				<h3><?php esc_html_e( 'Revenue', 'dispensary-wp' ); ?></h3>
				<strong><?php echo esc_html( $get_value( $revenue, 'revenue', $get_value( $revenue, 'total', 0 ) ) ); ?></strong>
			</div>

			<div class="dispensary-wp-card">
				<h3><?php esc_html_e( 'Payments', 'dispensary-wp' ); ?></h3>
				<strong><?php echo esc_html( count( $payments ) ); ?></strong>
			</div>

			<div class="dispensary-wp-card">
				<h3><?php esc_html_e( 'Refunds', 'dispensary-wp' ); ?></h3>
				<strong><?php echo esc_html( count( $refunds ) ); ?></strong>
			</div>

		</div>

		<div class="dispensary-wp-panel">

			<h2><?php esc_html_e( 'Payment Report', 'dispensary-wp' ); ?></h2>

			<table class="widefat striped">

				<thead>
					<tr>
						<th><?php esc_html_e( 'Payment Method', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'Amount', 'dispensary-wp' ); ?></th>
					</tr>
				</thead>

				<tbody>

					<?php foreach ( $payments as $row ) : ?>

						<tr>
							<td><?php echo esc_html( $get_value( $row, 'payment_method', $get_value( $row, 'method', '' ) ) ); ?></td>
							<td><?php echo esc_html( $get_value( $row, 'amount', $get_value( $row, 'total', 0 ) ) ); ?></td>
						</tr>

					<?php endforeach; ?>

				</tbody>

			</table>

		</div>

	<?php endif; ?>

</div>
