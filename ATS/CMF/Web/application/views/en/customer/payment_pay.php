<div class="main">

	<div class="container payment-methods">
		<h1>{payment_of_order_text} {order_id} </h1>
		<br>
			
		<h2>{total_text}: {order_total} {currency_text}</h2>
		<br>

		<?php foreach($payment_methods as $p){ ?>
			<div class='row bg-even-odd '>
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
