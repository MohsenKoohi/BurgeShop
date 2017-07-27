<div class="main">
	<div class="container">
		<h1>{order_details_text} <?php echo $order_id;?></h1>
		<div class='row'>
			<div class="row even-odd-bg dont-magnify" >
				<div class="three columns">{order_number_text}</div>
				<div class="eight columns"><?php echo $order_id; ?></div>
			</div>
			<div class="row even-odd-bg dont-magnify" >
				<div class="three columns">{name_text}</div>
				<div class="eight columns">
					<?php echo $order_info['customer_name']; ?>
				</div>
			</div>
			<div class="row even-odd-bg dont-magnify" >
				<div class="three columns">{date_text}</div>
				<div class="eight columns"><span class='date'><?php echo $order_info['order_date']; ?></span></div>
			</div>
			<div class="row even-odd-bg dont-magnify" >
				<div class="three columns">{total_text}</div>
				<div class="eight columns">
					<span class='date'><?php echo price_separator($order_info['order_total']); ?></span>
					 {currency_text}
				</div>
			</div>
			<div class="row even-odd-bg dont-magnify" >
				<div class="three columns">{status_text}</div>
				<div class="eight columns">
					<?php 
						$status_name='order_status_'.$order_info['order_status'].'_text';
						if(isset($$status_name))
							echo $$status_name;
						else
							echo $order_info['order_status'];

						if(isset($payment_link))
						{
					?>
							<a target="_blank" 
								href="<?php echo $payment_link; ?>"
								class="button button-primary sub-primary four columns anti-float "
							>
								{pay_text}
							</a>
					<?php 
						}
					?>
				</div>
			</div>
		</div>

		<br><br>
				
		<div class='row title-row even-odd-bg'>
			<div class='four columns'>{product_name_text}</div>
			<div class='two columns'>{quantity_text}</div>
			<div class='three columns'>{unit_price_text}</div>
			<div class='three columns'>{total_price_text}</div>
		</div>

		<?php foreach($cart_info as $p){ ?>
			<div class='row even-odd-bg'>
				<div class='four columns'>
					<a href="<?php echo get_customer_product_details_link($p['product_id'],$p['name']);?>"
						target='_blank'
					>
						<b><?php echo $p['name'];?></b>
						<br>
						<ul class='dash-ul'>
							<?php 
								foreach($p['options'] as $o)
								{
									$type=$o['type'];
									$value=$o['value'];
									echo "<li>$type: $value</li>";
								} 
							?>
						</ul>
					</a>
				</div>
				<div class='two columns align-center'>
					<?php echo $p['quantity'];?>
				</div>
				<div class='three columns align-center'>
					<?php echo price_separator($p['price']);?>
				</div>
				<div class='three columns align-center'>
					<?php echo price_separator($p['quantity']*$p['price']);?>
				</div>
			</div>
		<?php } ?>
			
								
			
	</div>
</div>