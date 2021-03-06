<?php
	global $gateway, $pmpro_review, $skip_account_fields, $pmpro_paypal_token, $wpdb, $current_user, $pmpro_msg, $pmpro_msgt, $pmpro_requirebilling, $pmpro_level, $pmpro_levels, $tospage, $pmpro_show_discount_code, $pmpro_error_fields;
	global $discount_code, $username, $password, $password2, $bfirstname, $blastname, $baddress1, $baddress2, $bcity, $bstate, $bzipcode, $bcountry, $bphone, $bemail, $bconfirmemail, $CardType, $AccountNumber, $ExpirationMonth,$ExpirationYear;

	$pmpro_stripe_lite = apply_filters("pmpro_stripe_lite", !pmpro_getOption("stripe_billingaddress"));	//default is oposite of the stripe_billingaddress setting
?>

<?php   $site = $_GET['id'];
		$transfer = $_GET['transfer_id'];

	if($site) { ?>
		<h1 class="check-out-header">You have been invited to join BrandCards!</h1>
	<?php } elseif($transfer) { ?>
		<h1 class="check-out-header">Congrats! You've been invited to take ownership of this brand!</h1>
	<?php } else { ?>
		<h1 class="check-out-header">Great! Let's get started building better brands.</h1>
	<?php } ?>

<div class="row gutters">
<div class="col span_16 large-gutter registration-form">
<div class="registration-form-wrapper">

<div id="pmpro_level-<?php echo $pmpro_level->id; ?>">
<form id="pmpro_form" class="pmpro_form" action="<?php if(!empty($_REQUEST['review'])) echo pmpro_url("checkout", "?level=" . $pmpro_level->id); ?>" method="post">

	<input type="hidden" id="level" name="level" value="<?php echo esc_attr($pmpro_level->id) ?>" />
	<input type="hidden" id="checkjavascript" name="checkjavascript" value="1" />

	<?php if($pmpro_msg)
		{
	?>
		<div id="pmpro_message" class="pmpro_message <?php echo $pmpro_msgt?>"><?php echo $pmpro_msg?></div>
	<?php
		}
		else
		{
	?>
		<div id="pmpro_message" class="pmpro_message" style="display: none;"></div>
	<?php
		}
	?>

	<?php if($pmpro_review) { ?>
		<p><?php _e('Almost done. Review the membership information and pricing below then <strong>click the "Complete Payment" button</strong> to finish your order.', 'pmpro');?></p>
	<?php } ?>

	<div id="pmpro_pricing_fields" class="pmpro_checkout" width="100%" cellpadding="0" cellspacing="0" border="0">

				<p>
					<?php printf(__('You are signing up for a <strong>%s</strong> membership.', 'pmpro'), $pmpro_level->name);?>
				</p>

				<div class="level-explanation">
					<?php
						if(!empty($pmpro_level->description))
							echo 'You will be able to manage ' . $pmpro_level->description . ' with this plan.';
					?>
				</div>

				<?php do_action("pmpro_checkout_after_level_cost"); ?>

				<?php if($pmpro_show_discount_code) { ?>

					<?php if($discount_code && !$pmpro_review) { ?>
						<p id="other_discount_code_p" class="pmpro_small"><a id="other_discount_code_a" href="#discount_code"><?php _e('Click here to change your discount code', 'pmpro');?></a>.</p>
					<?php } elseif(!$pmpro_review) { ?>
						<p id="other_discount_code_p" class="pmpro_small"><?php _e('Do you have a discount code?', 'pmpro');?> <a id="other_discount_code_a" href="#discount_code"><?php _e('Click here to enter your discount code', 'pmpro');?></a>.</p>
					<?php } elseif($pmpro_review && $discount_code) { ?>
						<p><strong><?php _e('Discount Code', 'pmpro');?>:</strong> <?php echo $discount_code?></p>
					<?php } ?>

				<?php } ?>

		<?php if($pmpro_show_discount_code) { ?>
		<div id="other_discount_code_tr" style="display: none;">
				<div>
					<label for="other_discount_code"><?php _e('Discount Code', 'pmpro');?></label>
					<input id="other_discount_code" name="other_discount_code" type="text" class="input <?php echo pmpro_getClassForField("other_discount_code");?>" size="20" value="<?php echo esc_attr($discount_code)?>" />
					<input type="button" name="other_discount_code_button" id="other_discount_code_button" value="<?php _e('Apply', 'pmpro');?>" />
				</div>
		</div>
		<?php } ?>
	</div>

	<?php if($pmpro_show_discount_code) { ?>
	<script>
		//update discount code link to show field at top of form
		jQuery('#other_discount_code_a').attr('href', 'javascript:void(0);');
		jQuery('#other_discount_code_a').click(function() {
			jQuery('#other_discount_code_tr').show();
			jQuery('#other_discount_code_p').hide();
			jQuery('#other_discount_code').focus();
		});

		//update real discount code field as the other discount code field is updated
		jQuery('#other_discount_code').keyup(function() {
			jQuery('#discount_code').val(jQuery('#other_discount_code').val());
		});
		jQuery('#other_discount_code').blur(function() {
			jQuery('#discount_code').val(jQuery('#other_discount_code').val());
		});

		//update other discount code field as the real discount code field is updated
		jQuery('#discount_code').keyup(function() {
			jQuery('#other_discount_code').val(jQuery('#discount_code').val());
		});
		jQuery('#discount_code').blur(function() {
			jQuery('#other_discount_code').val(jQuery('#discount_code').val());
		});

		//applying a discount code
		jQuery('#other_discount_code_button').click(function() {
			var code = jQuery('#other_discount_code').val();
			var level_id = jQuery('#level').val();

			if(code)
			{
				//hide any previous message
				jQuery('.pmpro_discount_code_msg').hide();

				//disable the apply button
				jQuery('#other_discount_code_button').attr('disabled', 'disabled');

				jQuery.ajax({
					url: '<?php echo admin_url('admin-ajax.php')?>',type:'GET',timeout:<?php echo apply_filters("pmpro_ajax_timeout", 5000, "applydiscountcode");?>,
					dataType: 'html',
					data: "action=applydiscountcode&code=" + code + "&level=" + level_id + "&msgfield=pmpro_message",
					error: function(xml){
						alert('Error applying discount code [1]');

						//enable apply button
						jQuery('#other_discount_code_button').removeAttr('disabled');
					},
					success: function(responseHTML){
						if (responseHTML == 'error')
						{
							alert('Error applying discount code [2]');
						}
						else
						{
							jQuery('#pmpro_message').html(responseHTML);
						}

						//enable invite button
						jQuery('#other_discount_code_button').removeAttr('disabled');
					}
				});
			}
		});
	</script>
	<?php } ?>

	<?php
		do_action('pmpro_checkout_after_pricing_fields');
	?>

	<?php if(!$skip_account_fields && !$pmpro_review) { ?>
	<div id="pmpro_user_fields" class="pmpro_checkout" width="100%" cellpadding="0" cellspacing="0" border="0">


				<div>
					<label class="hidden" for="username"><?php _e('Username', 'pmpro');?></label>
					<input id="username" name="username" type="text" class="hidden input " size="30" value="<?php echo esc_attr($username)?>" />
				</div>

				<?php
					do_action('pmpro_checkout_after_username');
				?>

				<div>
					<label for="password"><?php _e('Password', 'pmpro');?></label>
					<input id="password" name="password" type="password" class="input <?php echo pmpro_getClassForField("password");?>" size="30" value="<?php echo esc_attr($password)?>" />
				</div>
				<?php
					$pmpro_checkout_confirm_password = apply_filters("pmpro_checkout_confirm_password", true);
					if($pmpro_checkout_confirm_password)
					{
					?>
					<div>
						<label for="password2"><?php _e('Confirm Password', 'pmpro');?></label>
						<input id="password2" name="password2" type="password" class="input <?php echo pmpro_getClassForField("password2");?>" size="30" value="<?php echo esc_attr($password2)?>" />
					</div>
					<?php
					}
					else
					{
					?>
					<input type="hidden" name="password2_copy" value="1" />
					<?php
					}
				?>

				<?php
					do_action('pmpro_checkout_after_password');
				?>

				<div>
					<label for="bemail"><?php _e('E-mail Address', 'pmpro');?></label>
					<input id="bemail" name="bemail" type="text" class="input <?php echo pmpro_getClassForField("bemail");?>" size="30" value="<?php echo esc_attr($bemail)?>" />
				</div>
				<?php
					$pmpro_checkout_confirm_email = apply_filters("pmpro_checkout_confirm_email", true);
					if($pmpro_checkout_confirm_email)
					{
					?>
					<div>
						<label for="bconfirmemail"><?php _e('Confirm E-mail Address', 'pmpro');?></label>
						<input id="bconfirmemail" name="bconfirmemail" type="text" class="input <?php echo pmpro_getClassForField("bconfirmemail");?>" size="30" value="<?php echo esc_attr($bconfirmemail)?>" />

					</div>
					<?php
					}
					else
					{
					?>
					<input type="hidden" name="bconfirmemail_copy" value="1" />
					<?php
					}
				?>

				<?php
					do_action('pmpro_checkout_after_email');
				?>

				<div class="pmpro_hidden">
					<label for="fullname"><?php _e('Full Name', 'pmpro');?></label>
					<input id="fullname" name="fullname" type="text" class="input <?php echo pmpro_getClassForField("fullname");?>" size="30" value="" /> <strong><?php _e('LEAVE THIS BLANK', 'pmpro');?></strong>
				</div>

				<div class="pmpro_captcha">
				<?php
					global $recaptcha, $recaptcha_publickey;
					if($recaptcha == 2 || ($recaptcha == 1 && pmpro_isLevelFree($pmpro_level)))
					{
						echo recaptcha_get_html($recaptcha_publickey, NULL, true);
					}
				?>
				</div>

				<?php
					do_action('pmpro_checkout_after_captcha');
				?>

	</div>
	<?php } elseif($current_user->ID && !$pmpro_review) { ?>

		<p id="pmpro_account_loggedin">
			<?php printf(__('You are logged in as <strong>%s</strong>. If you would like to use a different account for this membership, <a href="%s">log out now</a>.', 'pmpro'), $current_user->user_login, wp_logout_url($_SERVER['REQUEST_URI'])); ?>
		</p>
	<?php } ?>

	<?php
		do_action('pmpro_checkout_after_user_fields');
	?>

	<?php
		do_action('pmpro_checkout_boxes');
	?>

	<?php if(pmpro_getOption("gateway", true) == "paypal" && empty($pmpro_review)) { ?>
		<div id="pmpro_payment_method" class="pmpro_checkout top1em" width="100%" cellpadding="0" cellspacing="0" border="0" <?php if(!$pmpro_requirebilling) { ?>style="display: none;"<?php } ?>>

				<div><?php _e('Choose your Payment Method', 'pmpro');?></div>

					<div>
						<input type="radio" name="gateway" value="paypal" <?php if(!$gateway || $gateway == "paypal") { ?>checked="checked"<?php } ?> />
							<a href="javascript:void(0);" class="pmpro_radio"><?php _e('Check Out with a Credit Card Here', 'pmpro');?></a> &nbsp;
						<input type="radio" name="gateway" value="paypalexpress" <?php if($gateway == "paypalexpress") { ?>checked="checked"<?php } ?> />
							<a href="javascript:void(0);" class="pmpro_radio"><?php _e('Check Out with PayPal', 'pmpro');?></a> &nbsp;
					</div>
		</tbody>
		</div>
	<?php } ?>

	<?php if(empty($pmpro_stripe_lite) || $gateway != "stripe") { ?>
	<div id="pmpro_billing_address_fields" class="pmpro_checkout top1em" width="100%" cellpadding="0" cellspacing="0" border="0" <?php if(!$pmpro_requirebilling || $gateway == "paypalexpress" || $gateway == "paypalstandard" || $gateway == "twocheckout") { ?>style="display: none;"<?php } ?>>
			<div><?php _e('Billing Address', 'pmpro');?></div>

				<div class="first_name_field">
					<label for="bfirstname"><?php _e('First Name', 'pmpro');?></label>
					<input id="bfirstname" name="bfirstname" type="text" class="input <?php echo pmpro_getClassForField("bfirstname");?>" size="30" value="<?php echo esc_attr($bfirstname)?>" />
				</div>
				<div class="last_name_field">
					<label for="blastname"><?php _e('Last Name', 'pmpro');?></label>
					<input id="blastname" name="blastname" type="text" class="input <?php echo pmpro_getClassForField("blastname");?>" size="30" value="<?php echo esc_attr($blastname)?>" />
				</div>
				<div>
					<label for="baddress1"><?php _e('Address 1', 'pmpro');?></label>
					<input id="baddress1" name="baddress1" type="text" class="input <?php echo pmpro_getClassForField("baddress1");?>" size="30" value="<?php echo esc_attr($baddress1)?>" />
				</div>
				<div>
					<label for="baddress2"><?php _e('Address 2', 'pmpro');?></label>
					<input id="baddress2" name="baddress2" type="text" class="input <?php echo pmpro_getClassForField("baddress2");?>" size="30" value="<?php echo esc_attr($baddress2)?>" />
				</div>

				<?php
					$longform_address = apply_filters("pmpro_longform_address", true);
					if($longform_address)
					{
				?>
					<div class="city_field">
						<label for="bcity"><?php _e('City', 'pmpro');?></label>
						<input id="bcity" name="bcity" type="text" class="input <?php echo pmpro_getClassForField("bcity");?>" size="30" value="<?php echo esc_attr($bcity)?>" />
					</div>
					<div class="state_field">
						<label for="bstate"><?php _e('State', 'pmpro');?></label>
						<input id="bstate" name="bstate" type="text" class="input <?php echo pmpro_getClassForField("bstate");?>" size="30" value="<?php echo esc_attr($bstate)?>" />
					</div>
					<div class="zip_field">
						<label for="bzipcode"><?php _e('Postal Code', 'pmpro');?></label>
						<input id="bzipcode" name="bzipcode" type="text" class="input <?php echo pmpro_getClassForField("bzipcode");?>" size="30" value="<?php echo esc_attr($bzipcode)?>" />
					</div>
				<?php
					}
					else
					{
					?>
					<div>
						<label for="bcity_state_zip"><?php _e('City, State Zip', 'pmpro');?></label>
						<input id="bcity" name="bcity" type="text" class="input <?php echo pmpro_getClassForField("bcity");?>" size="14" value="<?php echo esc_attr($bcity)?>" />,
						<?php
							$state_dropdowns = apply_filters("pmpro_state_dropdowns", false);
							if($state_dropdowns === true || $state_dropdowns == "names")
							{
								global $pmpro_states;
							?>
							<select name="bstate" class=" <?php echo pmpro_getClassForField("bstate");?>">
								<option value="">--</option>
								<?php
									foreach($pmpro_states as $ab => $st)
									{
								?>
									<option value="<?php echo esc_attr($ab);?>" <?php if($ab == $bstate) { ?>selected="selected"<?php } ?>><?php echo $st;?></option>
								<?php } ?>
							</select>
							<?php
							}
							elseif($state_dropdowns == "abbreviations")
							{
								global $pmpro_states_abbreviations;
							?>
								<select name="bstate" class=" <?php echo pmpro_getClassForField("bstate");?>">
									<option value="">--</option>
									<?php
										foreach($pmpro_states_abbreviations as $ab)
										{
									?>
										<option value="<?php echo esc_attr($ab);?>" <?php if($ab == $bstate) { ?>selected="selected"<?php } ?>><?php echo $ab;?></option>
									<?php } ?>
								</select>
							<?php
							}
							else
							{
							?>
							<input id="bstate" name="bstate" type="text" class="input <?php echo pmpro_getClassForField("bstate");?>" size="2" value="<?php echo esc_attr($bstate)?>" />
							<?php
							}
						?>
						<input id="bzipcode" name="bzipcode" type="text" class="input <?php echo pmpro_getClassForField("bzipcode");?>" size="5" value="<?php echo esc_attr($bzipcode)?>" />
					</div>
					<?php
					}
				?>

				<?php
					$show_country = apply_filters("pmpro_international_addresses", true);
					if($show_country)
					{
				?>
				<div>
					<label for="bcountry"><?php _e('Country', 'pmpro');?></label>
					<select name="bcountry" class=" <?php echo pmpro_getClassForField("bcountry");?>">
						<?php
							global $pmpro_countries, $pmpro_default_country;
							if(!$bcountry)
								$bcountry = $pmpro_default_country;
							foreach($pmpro_countries as $abbr => $country)
							{
							?>
							<option value="<?php echo $abbr?>" <?php if($abbr == $bcountry) { ?>selected="selected"<?php } ?>><?php echo $country?></option>
							<?php
							}
						?>
					</select>
				</div>
				<?php
					}
					else
					{
					?>
						<input type="hidden" name="bcountry" value="US" />
					<?php
					}
				?>
				<div>
					<label for="bphone"><?php _e('Phone', 'pmpro');?></label>
					<input id="bphone" name="bphone" type="text" class="input <?php echo pmpro_getClassForField("bphone");?>" size="30" value="<?php echo esc_attr(formatPhone($bphone))?>" />
				</div>
				<?php if($skip_account_fields) { ?>
				<?php
					if($current_user->ID)
					{
						if(!$bemail && $current_user->user_email)
							$bemail = $current_user->user_email;
						if(!$bconfirmemail && $current_user->user_email)
							$bconfirmemail = $current_user->user_email;
					}
				?>
				<div>
					<label for="bemail"><?php _e('E-mail Address', 'pmpro');?></label>
					<input id="bemail" name="bemail" type="text" class="input <?php echo pmpro_getClassForField("bemail");?>" size="30" value="<?php echo esc_attr($bemail)?>" />
				</div>
				<?php
					$pmpro_checkout_confirm_email = apply_filters("pmpro_checkout_confirm_email", true);
					if($pmpro_checkout_confirm_email)
					{
					?>
					<div>
						<label for="bconfirmemail"><?php _e('Confirm E-mail', 'pmpro');?></label>
						<input id="bconfirmemail" name="bconfirmemail" type="text" class="input <?php echo pmpro_getClassForField("bconfirmemail");?>" size="30" value="<?php echo esc_attr($bconfirmemail)?>" />

					</div>
					<?php
						}
						else
						{
					?>
					<input type="hidden" name="bconfirmemail_copy" value="1" />
					<?php
						}
					?>
				<?php } ?>

	</div>
	<?php } ?>

	<?php do_action("pmpro_checkout_after_billing_fields"); ?>

	<?php
		$pmpro_accepted_credit_cards = pmpro_getOption("accepted_credit_cards");
		$pmpro_accepted_credit_cards = explode(",", $pmpro_accepted_credit_cards);
		$pmpro_accepted_credit_cards_string = pmpro_implodeToEnglish($pmpro_accepted_credit_cards);
	?>

	<div id="pmpro_payment_information_fields" class="pmpro_checkout top1em " width="100%" cellpadding="0" cellspacing="0" border="0" <?php if(!$pmpro_requirebilling || $gateway == "paypalexpress" || $gateway == "paypalstandard" || $gateway == "twocheckout") { ?>style="display: none;"<?php } ?>>
			<div class="cards-accepted"><span class="pmpro_thead-msg"><?php printf(__('We Accept %s', 'pmpro'), $pmpro_accepted_credit_cards_string);?></span><?php _e('', 'pmpro');?></div>
			<div class="clear"></div>
			<div>
				<?php
					$sslseal = pmpro_getOption("sslseal");
					if($sslseal)
					{
					?>
						<div class="pmpro_sslseal"><?php echo stripslashes($sslseal)?></div>
					<?php
					}
				?>
				<input type="hidden" id="CardType" name="CardType" value="<?php echo esc_attr($CardType);?>" />
				<script>
					jQuery(document).ready(function() {
							jQuery('#AccountNumber').validateCreditCard(function(result) {
								var cardtypenames = {
									"amex":"American Express",
									"diners_club_carte_blanche":"Diners Club Carte Blanche",
									"diners_club_international":"Diners Club International",
									"discover":"Discover",
									"jcb":"JCB",
									"laser":"Laser",
									"maestro":"Maestro",
									"mastercard":"Mastercard",
									"visa":"Visa",
									"visa_electron":"Visa Electron"
								}

								if(result.card_type)
									jQuery('#CardType').val(cardtypenames[result.card_type.name]);
								else
									jQuery('#CardType').val('Unknown Card Type');
							});
					});
				</script>

		<div class="credit-card-wrapper">
			<div class="row">
				<h3>Credit Card Details</h3>
			</div>

			<div class="row">
				<div class="pmpro_payment-account-number">
					<label for="AccountNumber"><?php _e('Card Number', 'pmpro');?></label>
					<input id="AccountNumber" <?php if($gateway != "stripe" && $gateway != "braintree") { ?>name="AccountNumber"<?php } ?> class="input <?php echo pmpro_getClassForField("AccountNumber");?>" type="text" size="25" value="<?php echo esc_attr($AccountNumber)?>" <?php if($gateway == "braintree") { ?>data-encrypted-name="number"<?php } ?> autocomplete="off" />
				</div>
			</div>



			<div class="pmpro_payment-expiration">
				<div class="row">
					<label for="ExpirationMonth"><?php _e('Expiration Date', 'pmpro');?></label>
				</div>

				<div class="row">
					<div class="col span_6">
						<select id="ExpirationMonth" <?php if($gateway != "stripe") { ?>name="ExpirationMonth"<?php } ?> class=" <?php echo pmpro_getClassForField("ExpirationMonth");?>">
							<option value="01" <?php if($ExpirationMonth == "01") { ?>selected="selected"<?php } ?>>01</option>
							<option value="02" <?php if($ExpirationMonth == "02") { ?>selected="selected"<?php } ?>>02</option>
							<option value="03" <?php if($ExpirationMonth == "03") { ?>selected="selected"<?php } ?>>03</option>
							<option value="04" <?php if($ExpirationMonth == "04") { ?>selected="selected"<?php } ?>>04</option>
							<option value="05" <?php if($ExpirationMonth == "05") { ?>selected="selected"<?php } ?>>05</option>
							<option value="06" <?php if($ExpirationMonth == "06") { ?>selected="selected"<?php } ?>>06</option>
							<option value="07" <?php if($ExpirationMonth == "07") { ?>selected="selected"<?php } ?>>07</option>
							<option value="08" <?php if($ExpirationMonth == "08") { ?>selected="selected"<?php } ?>>08</option>
							<option value="09" <?php if($ExpirationMonth == "09") { ?>selected="selected"<?php } ?>>09</option>
							<option value="10" <?php if($ExpirationMonth == "10") { ?>selected="selected"<?php } ?>>10</option>
							<option value="11" <?php if($ExpirationMonth == "11") { ?>selected="selected"<?php } ?>>11</option>
							<option value="12" <?php if($ExpirationMonth == "12") { ?>selected="selected"<?php } ?>>12</option>
						</select>
					</div>
					<div class="col span_6">
						<select id="ExpirationYear" <?php if($gateway != "stripe") { ?>name="ExpirationYear"<?php } ?> class=" <?php echo pmpro_getClassForField("ExpirationYear");?>">
							<?php
								for($i = date("Y"); $i < date("Y") + 10; $i++)
								{
							?>
								<option value="<?php echo $i?>" <?php if($ExpirationYear == $i) { ?>selected="selected"<?php } ?>><?php echo $i?></option>
							<?php
								}
							?>
						</select>
					</div>

					<?php
						$pmpro_show_cvv = apply_filters("pmpro_show_cvv", true);
						if($pmpro_show_cvv)
						{
					?>
					<div class="col span_12 cvv-col">
						<div class="pmpro_payment-cvv">
							<label for="CVV"><?php _ex('CVV', 'Credit card security code, CVV/CCV/CVV2', 'pmpro');?></label>
							<input id="CVV" <?php if($gateway != "stripe" && $gateway != "braintree") { ?>name="CVV"<?php } ?> type="text" size="4" value="<?php if(!empty($_REQUEST['CVV'])) { echo esc_attr($_REQUEST['CVV']); }?>" class="input <?php echo pmpro_getClassForField("CVV");?>" <?php if($gateway == "braintree") { ?>data-encrypted-name="cvv"<?php } ?> />  <small><a href="javascript:void(0);" onclick="javascript:window.open('<?php echo pmpro_https_filter(PMPRO_URL)?>/pages/popup-cvv.html','cvv','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=600, height=475');"><?php _ex("?", 'link to CVV help', 'pmpro');?></a></small>
						</div>
					</div>
				</div>
			</div>

				<?php
					}
				?>
			</div>

				<?php if($pmpro_show_discount_code) { ?>
				<div class="pmpro_payment-discount-code">
					<label for="discount_code"><?php _e('Discount Code', 'pmpro');?></label>
					<input class="input <?php echo pmpro_getClassForField("discount_code");?>" id="discount_code" name="discount_code" type="text" size="20" value="<?php echo esc_attr($discount_code)?>" />
					<input type="button" id="discount_code_button" name="discount_code_button" value="<?php _e('Apply', 'pmpro');?>" />
					<p id="discount_code_message" class="pmpro_message" style="display: none;"></p>
				</div>
				<?php } ?>

			</div>
	</div>
	<script>
		//checking a discount code
		jQuery('#discount_code_button').click(function() {
			var code = jQuery('#discount_code').val();
			var level_id = jQuery('#level').val();

			if(code)
			{
				//hide any previous message
				jQuery('.pmpro_discount_code_msg').hide();

				//disable the apply button
				jQuery('#discount_code_button').attr('disabled', 'disabled');

				jQuery.ajax({
					url: '<?php echo admin_url('admin-ajax.php')?>',type:'GET',timeout:<?php echo apply_filters("pmpro_ajax_timeout", 5000, "applydiscountcode");?>,
					dataType: 'html',
					data: "action=applydiscountcode&code=" + code + "&level=" + level_id + "&msgfield=discount_code_message",
					error: function(xml){
						alert('Error applying discount code [1]');

						//enable apply button
						jQuery('#discount_code_button').removeAttr('disabled');
					},
					success: function(responseHTML){
						if (responseHTML == 'error')
						{
							alert('Error applying discount code [2]');
						}
						else
						{
							jQuery('#discount_code_message').html(responseHTML);
						}

						//enable invite button
						jQuery('#discount_code_button').removeAttr('disabled');
					}
				});
			}
		});
	</script>

	<?php
		if($gateway == "check")
		{
			$instructions = pmpro_getOption("instructions");
			echo '<div class="pmpro_check_instructions">' . wpautop($instructions) . '</div>';
		}
	?>

	<?php if($gateway == "braintree") { ?>
		<input type='hidden' data-encrypted-name='expiration_date' id='credit_card_exp' />
		<input type='hidden' name='AccountNumber' id='BraintreeAccountNumber' />
		<script type="text/javascript" src="https://js.braintreegateway.com/v1/braintree.js"></script>
		<script type="text/javascript">
			//setup braintree encryption
			var braintree = Braintree.create('<?php echo pmpro_getOption("braintree_encryptionkey"); ?>');
			braintree.onSubmitEncryptForm('pmpro_form');

			//pass expiration dates in original format
			function pmpro_updateBraintreeCardExp()
			{
				jQuery('#credit_card_exp').val(jQuery('#ExpirationMonth').val() + "/" + jQuery('#ExpirationYear').val());
			}
			jQuery('#ExpirationMonth, #ExpirationYear').change(function() {
				pmpro_updateBraintreeCardExp();
			});
			pmpro_updateBraintreeCardExp();

			//pass last 4 of credit card
			function pmpro_updateBraintreeAccountNumber()
			{
				jQuery('#BraintreeAccountNumber').val('XXXXXXXXXXXXX' + jQuery('#AccountNumber').val().substr(jQuery('#AccountNumber').val().length - 4));
			}
			jQuery('#AccountNumber').change(function() {
				pmpro_updateBraintreeAccountNumber();
			});
			pmpro_updateBraintreeAccountNumber();
		</script>
	<?php } ?>

	<?php
		if($tospage && !$pmpro_review)
		{
		?>
		<div id="pmpro_tos_fields" class="pmpro_checkout top1em" width="100%" cellpadding="0" cellspacing="0" border="0">

			<div><?php echo $tospage->post_title?></div>

					<div id="pmpro_license">
<?php echo wpautop($tospage->post_content)?>
					</div>
					<input type="checkbox" name="tos" value="1" id="tos" /> <label class="pmpro_normal pmpro_clickable" for="tos"><?php printf(__('I agree to the %s', 'pmpro'), $tospage->post_title);?></label>

		</div>
		<?php
		}
	?>

	<?php do_action("pmpro_checkout_after_tos_fields"); ?>

	<?php do_action("pmpro_checkout_before_submit_button"); ?>

	<div class="pmpro_submit">
		<?php if($pmpro_review) { ?>

			<span id="pmpro_submit_span">
				<input type="hidden" name="confirm" value="1" />
				<input type="hidden" name="token" value="<?php echo esc_attr($pmpro_paypal_token)?>" />
				<input type="hidden" name="gateway" value="<?php echo esc_attr($gateway); ?>" />
				<input type="submit" class="pmpro_btn pmpro_btn-submit-checkout" value="<?php _e('Complete Payment', 'pmpro');?> &raquo;" />
			</span>

		<?php } else { ?>

			<?php if($gateway == "paypal" || $gateway == "paypalexpress" || $gateway == "paypalstandard") { ?>
			<span id="pmpro_paypalexpress_checkout" <?php if(($gateway != "paypalexpress" && $gateway != "paypalstandard") || !$pmpro_requirebilling) { ?>style="display: none;"<?php } ?>>
				<input type="hidden" name="submit-checkout" value="1" />
				<input type="image" value="<?php _e('Check Out with PayPal', 'pmpro');?> &raquo;" src="<?php echo apply_filters("pmpro_paypal_button_image", "https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif");?>" />
			</span>
			<?php } ?>

			<span id="pmpro_submit_span" <?php if(($gateway == "paypalexpress" || $gateway == "paypalstandard") && $pmpro_requirebilling) { ?>style="display: none;"<?php } ?>>
				<input type="hidden" name="submit-checkout" value="1" />
				<input type="submit" class="pmpro_btn pmpro_btn-submit-checkout" value="<?php if($pmpro_requirebilling) { if($gateway == "twocheckout") { _e('Submit and Pay with 2CheckOut', 'pmpro'); } else { _e('Submit and Check Out', 'pmpro'); } } else { _e('Register', 'pmpro');}?>" />
			</span>
		<?php } ?>

		<span id="pmpro_processing_message" style="visibility: hidden;">
			<img src="<?php network_site_url(); ?>/wp-content/themes/brandcards/images/ajax-loader.gif" id="loading-animation" />
			<?php
				$processing_message = apply_filters("pmpro_processing_message", __("Processing...", "pmpro"));
				echo $processing_message;
			?>
		</span>
	</div>

</form>
</div> <!-- end pmpro_level-ID -->
<?php if($gateway == "paypal" || $gateway == "paypalexpress") { ?>
<script>
	//choosing payment method
	jQuery('input[name=gateway]').click(function() {
		if(jQuery(this).val() == 'paypal')
		{
			jQuery('#pmpro_paypalexpress_checkout').hide();
			jQuery('#pmpro_billing_address_fields').show();
			jQuery('#pmpro_payment_information_fields').show();
			jQuery('#pmpro_submit_span').show();
		}
		else
		{
			jQuery('#pmpro_billing_address_fields').hide();
			jQuery('#pmpro_payment_information_fields').hide();
			jQuery('#pmpro_submit_span').hide();
			jQuery('#pmpro_paypalexpress_checkout').show();
		}
	});

	//select the radio button if the label is clicked on
	jQuery('a.pmpro_radio').click(function() {
		jQuery(this).prev().click();
	});
</script>
<?php } ?>

<script>
<!--
	// Find ALL <form> tags on your page
	jQuery('form').submit(function(){
		// On submit disable its submit button
		jQuery('input[type=submit]', this).attr('disabled', 'disabled');
		jQuery('input[type=image]', this).attr('disabled', 'disabled');
		jQuery('#pmpro_processing_message').css('visibility', 'visible');
	});

	//add required to required fields
	jQuery('.pmpro_required').after('<span class="pmpro_asterisk"> *</span>');

	//unhighlight error fields when the user edits them
	jQuery('.pmpro_error').bind("change keyup input", function() {
		jQuery(this).removeClass('pmpro_error');
	});

	//click apply button on enter in discount code box
	jQuery('#discount_code').keydown(function (e){
	    if(e.keyCode == 13){
		   e.preventDefault();
		   jQuery('#discount_code_button').click();
	    }
	});

	//hide apply button if a discount code was passed in
	<?php if(!empty($_REQUEST['discount_code'])) {?>
		jQuery('#discount_code_button').hide();
		jQuery('#discount_code').bind('change keyup', function() {
			jQuery('#discount_code_button').show();
		});
	<?php } ?>

	//click apply button on enter in *other* discount code box
	jQuery('#other_discount_code').keydown(function (e){
	    if(e.keyCode == 13){
		   e.preventDefault();
		   jQuery('#other_discount_code_button').click();
	    }
	});

-->
</script>
<script>
    //add javascriptok hidden field to checkout
    jQuery("input[name=submit-checkout]").after('<input type="hidden" name="javascriptok" value="1" />');
</script>

</div>

<span class="pmpro_thead-msg already-account"><?php _e('Already have an account?', 'pmpro');?> <a href="<?php echo wp_login_url(pmpro_url("checkout", "?level=" . $pmpro_level->id))?>"><?php _e('Log in here', 'pmpro');?></a>.</span>

</div>



<div class="col span_6 registration-sidebar">
	<?php   $site = $_GET['id'];
			$role = "subscriber";
	if($site) { ?>
		<h3>You've been invited to:</h3>
		<?php invite_brand_cover($site, $role); ?>
	<?php } ?>

	<?php   $transfer = $_GET['transfer_id'];
			$role = "subscriber";
	if($transfer) { ?>
		<h3>You're taking ownnership of:</h3>
		<?php invite_brand_cover($transfer, $role); ?>
	<?php } ?>

	<div class="cost-sidebar">
		<?php $level_cost = array(2, 3);
		if (in_array($pmpro_level->id, $level_cost)) { ?>
			<h5>Free For The First 30 Days!</h5>
		<?php } ?>
		<?php if($discount_code && pmpro_checkDiscountCode($discount_code)) { ?>
			<?php printf(__('<p class="pmpro_level_discount_applied">The <strong>%s</strong> code has been applied to your order.</p>', 'pmpro'), $discount_code);?>
		<?php } ?>
		<?php
		$level_cost = array(2, 3);
		if (in_array($pmpro_level->id, $level_cost)) { ?>
			<div class="price">
				<?php echo wpautop(pmpro_getLevelCost($pmpro_level)); ?>
				<?php echo wpautop(pmpro_getLevelExpiration($pmpro_level)); ?></div>
				<p class="secondary">This will be billed monthly until you cancel.</p>

				<a href="/membership-account/membership-levels/" class="button button-secondary button-block">Choose A Different Plan</a>
			</div>


			<hr>
		<?php } ?>



		<h4>BrandCards allows you to:</h4>
		<ul>
			<li>Manage all of your brand's assets</li>
			<li>Download logos, colors, & fonts</li>
			<li>Track changes to your brands</li>
			<li><a href="#" class="learn-more">Learn More</a>
		</ul>


		<hr>


	</div>



</div>
</div>



