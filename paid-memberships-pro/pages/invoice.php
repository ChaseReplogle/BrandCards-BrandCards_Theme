<?php
	global $wpdb, $pmpro_invoice, $pmpro_msg, $pmpro_msgt, $current_user;

	if($pmpro_msg)
	{
	?>
	<div class="pmpro_message <?php echo $pmpro_msgt?>"><?php echo $pmpro_msg?></div>
	<?php
	}
?>

<?php
	if($pmpro_invoice)
	{
		?>
		<?php
			$pmpro_invoice->getUser();
			$pmpro_invoice->getMembershipLevel();
		?>
		<div class="single-invoice-page">
			<div class="row invoice-pre-header no-print">
				<div class="col span_5">
					<a class="pmpro_a-back " href="/membership-account/membership-invoice">Back</a>
				</div>
				<div class="col span_19">
					<p class="secondary"><a class="" href="javascript:window.print()">Print This Invoice</a> Need a PDF? Choose "Save as PDF" from the Print dialog. </p>
				</div>
			</div>

			<div class="page-box invoice">

				<div class="row header">
					<div class="col span_24">
						<h2>Invoice</h2>
						<p class="secondary"><?php printf(__('Invoice #%s', 'pmpro'), $pmpro_invoice->code);?></p>
					</div>
				</div>

				<div class="row">
					<div class="col span_12">
					<?php
						//check instructions
						if($pmpro_invoice->gateway == "check" && !pmpro_isLevelFree($pmpro_invoice->membership_level))
							echo wpautop(pmpro_getOption("instructions"));
					?>

						<?php echo $pmpro_invoice->billing->name?><br />
						<?php echo $pmpro_invoice->billing->street?><br />
						<?php if($pmpro_invoice->billing->city && $pmpro_invoice->billing->state) { ?>
							<?php echo $pmpro_invoice->billing->city?>, <?php echo $pmpro_invoice->billing->state?> <?php echo $pmpro_invoice->billing->zip?> <?php echo $pmpro_invoice->billing->country?><br />
						<?php } ?>
						<?php echo formatPhone($pmpro_invoice->billing->phone)?>
					</div>
					<div class="col span_12">
						<div class="brandcards-address">
							<img src="<?php echo get_template_directory_uri(); ?>/images/logo.svg" width="180px" class="branding" /><br />
							2023 S Saratoga Ave<br />
							Springfield, MO 65804 USA<br />
						</div>
					</div>
				</div>

					<table id="pmpro_invoice_table" class="pmpro_single_invoice" width="100%" cellpadding="0" cellspacing="0" border="0">
						<thead>
							<tr>
								<th class="cell-left"><?php _e('Paid By', 'pmpro');?></th>
								<?php if(!empty($pmpro_invoice->billing->name)) { ?>
								<th><?php _e('Plan', 'pmpro');?></th>
								<?php } ?>
								<th><?php _e('Payment Date', 'pmpro');?></th>
								<th align="center"><?php _e('Total Billed', 'pmpro');?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="cell-left">
									<?php echo $pmpro_invoice->user->display_name?> (<?php echo $pmpro_invoice->user->user_email?>) <?php echo '<br />'; ?>

									<?php if($pmpro_invoice->accountnumber) { ?>
										<?php echo $pmpro_invoice->cardtype?> <?php echo __('ending in', 'pmpro');?> <?php echo last4($pmpro_invoice->accountnumber)?><br />
										<small><?php _e('Expiration', 'pmpro');?>: <?php echo $pmpro_invoice->expirationmonth?>/<?php echo $pmpro_invoice->expirationyear?></small>
									<?php } elseif($pmpro_invoice->payment_type) { ?>
										<?php echo $pmpro_invoice->payment_type?>
									<?php } ?>
								</td>
								<?php if(!empty($pmpro_invoice->billing->name)) { ?>
								<td>
									<?php echo $pmpro_invoice->membership_level->name?>
								</td>
								<?php } ?>
								<td><?php printf(__('%s', 'pmpro'), date_i18n(get_option('date_format'), $pmpro_invoice->timestamp));?></td>
								<td align="center">
									<?php if($pmpro_invoice->total != '0.00') { ?>
										<?php if(!empty($pmpro_invoice->tax)) { ?>
											<?php _e('Subtotal', 'pmpro');?>: <?php echo pmpro_formatPrice($pmpro_invoice->subtotal);?><br />
											<?php _e('Tax', 'pmpro');?>: <?php echo pmpro_formatPrice($pmpro_invoice->tax);?><br />
											<?php if(!empty($pmpro_invoice->couponamount)) { ?>
												<?php _e('Coupon', 'pmpro');?>: (<?php echo pmpro_formatPrice($pmpro_invoice->couponamount);?>)<br />
											<?php } ?>
											<strong><?php _e('Total', 'pmpro');?>: <?php echo pmpro_formatPrice($pmpro_invoice->total);?></strong>
										<?php } else { ?>
											<?php echo pmpro_formatPrice($pmpro_invoice->total);?>
										<?php } ?>
									<?php } else { ?>
										<small class="pmpro_grey"><?php echo pmpro_formatPrice(0);?></small>
									<?php } ?>
								</td>
							</tr>
						</tbody>
					</table>

				<div class="row thank-you">
					<h2>Thank You.</h2>
				</div>

				<div class="row footer">
					<p class="secondary">Questions about your account? Contact us at: brandcards.com/contact</p>
				</div>
			</div>
		</div>
		<?php
	}
	else
	{
		//Show all invoices for user if no invoice ID is passed
		$invoices = $wpdb->get_results("SELECT *, UNIX_TIMESTAMP(timestamp) as timestamp FROM $wpdb->pmpro_membership_orders WHERE user_id = '$current_user->ID' ORDER BY timestamp DESC");
		if($invoices)
		{
			?>
			<table id="pmpro_invoices_table" class="test pmpro_invoice" width="100%" cellpadding="0" cellspacing="0" border="0">
			<thead>
				<tr>
					<th><?php _e('Date', 'pmpro'); ?></th>
					<th><?php _e('Invoice #', 'pmpro'); ?></th>
					<th><?php _e('Total Billed', 'pmpro'); ?></th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
			<?php
				foreach($invoices as $invoice)
				{
					?>
					<tr>
						<td><?php echo date(get_option("date_format"), $invoice->timestamp)?></td>
						<td><a href="<?php echo pmpro_url("invoice", "?invoice=" . $invoice->code)?>"><?php echo $invoice->code; ?></a></td>
						<td><?php echo pmpro_formatPrice($invoice->total);?></td>
						<td><a href="<?php echo pmpro_url("invoice", "?invoice=" . $invoice->code)?>"><?php _e('View Invoice', 'pmpro'); ?></a></td>
					</tr>
					<?php
				}
			?>
			</tbody>
			</table>
			<?php
		}
		else
		{
			?>
			<p><?php _e('No invoices found.', 'pmpro');?></p>
			<?php
		}
	}
?>
<nav id="nav-below" class="navigation" role="navigation">
	<div class="nav-next alignright">
		<a href="<?php echo pmpro_url("account")?>"><?php _e('View Your Membership Account &rarr;', 'pmpro');?></a>
	</div>
	<?php if($pmpro_invoice) { ?>
		<div class="nav-prev alignleft">
			<a href="<?php echo pmpro_url("invoice")?>"><?php _e('&larr; View All Invoices', 'pmpro');?></a>
		</div>
	<?php } ?>
</nav>
