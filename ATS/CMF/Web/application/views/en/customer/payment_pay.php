<div class="main">

	<div class="container payment-methods">
		<h1>{payment_of_order_text} {order_id} </h1>
		<br>
			
		<h2>{total_text}: {order_total} {currency_text}</h2>
		<br>

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
