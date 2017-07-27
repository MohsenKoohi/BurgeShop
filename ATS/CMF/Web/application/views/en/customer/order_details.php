<div class="main">
	<div class="container cart">
		<h1>{order_details_text} <?php echo $order_id;?></h1>
		<div class='row order-details'>
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

		<?php foreach($cart_info as $p){ ?>
			<div class='row even-odd-bg item-row'>
				<div class='four columns product'>
					<label>{product_text}</label>
					<span class='align-right'>
						<a href="<?php echo get_customer_product_details_link($p['product_id'],$p['name']);?>"
							target='_blank'
						>
							<b><?php echo $p['name'];?></b>
						</a>
						<br>
						<ul class='dash-ul'>
							<?php 
								foreach($p['options'] as $o)
								{
									$type=$o['type'];
									$value=$o['value'];
									$ttype='product_option_'.$type.'_text';
									if(isset($$ttype))
										$ttype=$$ttype;
									else
										$ttype=$type;									

									if($type!='file')
										echo "<li>".$ttype.": <span>".$value."</span></li>";
									else
									{
										$link=get_order_item_file_url($value);
										echo "<li>".$ttype.": <span><a target='_blank' href='$link'>{download_text}</a></span></li>";	
									}
								} 
							?>
						</ul>
					</span>
				</div>
				<div class='two columns'>
					<label>{quantity_text}</label>
					<span><?php echo $p['quantity'];?></span>
				</div>
				<div class='three columns'>
					<label>{unit_price_text}</label>
					<span><?php echo price_separator($p['price']);?></span>
				</div>
				<div class='three columns'>
					<label>{total_price_text}</label>
					<span><?php echo price_separator($p['quantity']*$p['price']);?></span>
				</div>
			</div>
		<?php } ?>
								
			
	</div>
</div>