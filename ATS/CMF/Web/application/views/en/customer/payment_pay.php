<div class="main">

	<div class="container payment-methods">
		<h1>{payment_of_order_text} {order_id} </h1>
		<br>
			
		<h4>{total_text}: <?php echo price_separator($order_total);?> {currency_text}</h4>
		<?php if(isset($coupon_discount)){ ?>
			<h4>{coupon_discount_text}: <?php echo price_separator($coupon_discount);?> {currency_text}</h4>
			<h4>{to_be_payed_text}: <?php echo price_separator($order_total- $coupon_discount);?> {currency_text}</h4>
		<?php } ?>

		<?php if(!isset($coupon_discount)){ echo form_open("");?>
			<input type='hidden' name='post_type' value="coupon">
			<div class='row separated'>
				<div class='three columns'><span>{coupon_text}</span></div>
				<div class='six columns half-col-maring'>
					<input type='text' name='code' class='full-width lang-en'/>
				</div>
				<div class='two columns half-col-margin'>
					<input type="submit" class=" button sub-primary button-type1 full-width" value="{submit_text}"/>
				</div>
			</div>
		<?php echo form_close(); } ?>
		<br><br>
		<b>{please_select_the_payment_method_text}</b>
		<br><br>
		<div class='row'>
			<?php foreach($payment_methods as $p){ ?>
				<div class='row even-odd-bg '>
					<div class='nine columns'>
						<a href="<?php echo $p['link'];?>">
							<img src="<?php echo $p['image'];?>"/>
							<b class='payment-method-name'><?php echo $p['name'];?></b>
						</a>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
</div>
