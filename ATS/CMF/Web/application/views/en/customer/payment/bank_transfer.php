<div class="main">

	<div class="container payment-methods">
		<h1>{payment_method_bank_transfer_text}</h1>
		<br>
			
		<h2>{total_text}: <?php echo price_separtor($order_total);?> {currency_text}</h2>
		<br>

		<b>{bank_name_text}: {new_bank_text}</b><br>
		<b>{bank_account_number_text}: 11112222333</b><br>
		<b>{bank_cart_number_text}: 11112222333</b><br>
		<br>
		<b>{after_transferring_money_through_the_bank_submit_this_form_text}:</b>

		<br><br>
		<?php echo form_open("",array("onsubmit"=>"return checkForm();","id"=>"submit_form")); ?>
			<input type='hidden' name='post_type' value='submit_payment'/>
			<div class='row even-odd-bg'>
				<div class="two columns">
					{payer_name_text}:
				</div>
				<div class="six columns">
					<input class='full-width' type='text'  name='name' />
				</div>
			</div>

			<div class='row even-odd-bg'>
				<div class="two columns">
					{payment_date_text}:
				</div>
				<div class="six columns">
					<input class='full-width date' type='text' name='date' />
				</div>
			</div>

			<div class='row even-odd-bg'>
				<div class="two columns">
					{payment_bank_text}:
				</div>
				<div class="six columns">
					<input class='full-width' type='text'  name='bank' />
				</div>
			</div>

			<div class='row even-odd-bg'>
				<div class="two columns">
					{reference_code_text}:
				</div>
				<div class="six columns">
					<input class='full-width date' type='text' name='reference_code' />
				</div>
			</div>

			<br><br>

			<div class='row'>
				<input type='submit' class='three columns anti-float button button-primary' value='{submit_text}'/>
			</div>

			<script type="text/javascript">
				function checkForm()
				{
					var ret=true;
					$("input[type=text]",$("#submit_form")).each(function(index,el)
					{
						if(!$(el).val())
							ret=false;
					});

					if(!ret)
						alert("{please_fill_all_fields_text}");

					return ret;
				}
			</script>
		<?php echo form_close(); ?>
	</div>
</div>
