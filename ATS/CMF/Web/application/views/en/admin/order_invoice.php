<div class='container' style='width:100%;font-family:tahoma;display:inline-block'>
	<h1>{invoice_text} <?php echo $order_id;?></h1>

	<div class='row' style='width:100%'>
		<div class="row even-odd-bg dont-magnify" >
			<div style="width:25%;float:left">{name_text}</div>
			<div style="width:75%;float:left">
				<a href="<?php echo get_admin_customer_details_link($order_info['order_customer_id']);?>" >
					<?php echo $order_info['customer_name']; ?>
				</a>
			</div>
		</div>
		<div class="row even-odd-bg dont-magnify" >
			<div style="width:25%;float:left">{date_text}</div>
			<div style="width:75%;float:left"><span class='date'><?php echo $order_info['order_date']; ?></span></div>
		</div>
		<div class="row even-odd-bg dont-magnify" >
			<div style="width:25%;float:left">{total_text}</div>
			<div style="width:75%;float:left">
				<span class='date'><?php echo price_separator($order_info['order_total']); ?></span>
				 {currency_text}
			</div>
		</div>
		<div class="row even-odd-bg dont-magnify" >
			<div style="width:25%;float:left">{status_text}</div>
			<div style="width:75%;float:left">
				<?php 
					$status_name='order_status_'.$order_info['order_status'].'_text';
					if(isset($$status_name))
						echo $$status_name;
					else
						echo $order_info['order_status'];
				?>
			</div>
		</div>
	</div>

	<div class="tab" id="products" style="">
		<div class='row title-row even-odd-bg' style='width:100%;border:1px solid #aaa;display:inline-block'>
			<div style='width:33%;float:left; text-align:center'>{product_name_text}</div>
			<div style='width:16%;float:left; text-align:center'>{quantity_text}</div>
			<div style='width:25%;float:left; text-align:center'>{unit_price_text}</div>
			<div style='width:25%;float:left; text-align:center'>{total_price_text}</div>
		</div>

		<?php foreach($cart_info as $p){ ?>
			<div class='row even-odd-bg' style='width:100%;border:1px solid #aaa;display:inline-block'>
				<div style='width:33%;float:left;'>
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
				</div>
				<div style='width:16%;float:left; text-align:center'>
					<?php echo $p['quantity'];?>
				</div>
				<div style='width:25%;float:left; text-align:center'>
					<?php echo price_separator($p['price']);?>
				</div>
				<div style='width:25%;float:left; text-align:center'>
					<?php echo price_separator($p['quantity']*$p['price']);?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>