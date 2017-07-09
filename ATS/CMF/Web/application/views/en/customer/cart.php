<div class="main">

	<div class="container cart">
		<h1>{cart_text}</h1>
		<br>
		<?php if($products){ ?>
			<div class='row even-odd-bg'>
				<div class='four columns align-center'><b>{product_text}</b></div>
				<div class='two columns align-center'><b>{price_text} ({currency_text})<b></div>
				<div class='two columns align-center'><b>{quantity_text}</b></div>
				<div class='two columns align-center'><b>{total_price_text} ({currency_text})</b></div>
				<div class='two columns align-center'>&nbsp;</div>
			</div>
			<?php
				foreach($products as $product)
				{
			?>
					<div class="row even-odd-bg">
						<div class="four columns">
							<a href="<?php echo get_customer_product_details_link($product['product_id'],$product['product_name']);?>" >
								<b><?php echo $product['product_name'];?></b>
								<ul class='dash-ul'>
									<?php 
										foreach($product['options'] as $type => $value)
											echo "<li>".$type.": ".$value."</li>";
									?>
								</ul>
							</a>
						</div>

						<div class='two columns align-center'>
							<?php echo price_separator($product['price']);?>
						</div>

						<div class='two columns align-center'>
							<?php echo $product['quantity'];?>
						</div>

						<div class='two columns align-center'>
							<?php echo price_separator($product['quantity'] * $product['price']);?>
						</div>

						<div class='two columns align-center'>
							<img src='{images_url}/remove.png' title='{remove_text}' class='remove-product' 
								onclick='removeItem(<?php echo $product['cart_index'];?>);'
							/>
						</div>
						
					</div>
			<?php
				}
			?>
			<br>
			<div class='row'>
				<a href='' class='three columns anti-float button button-primary'>{submit_order_text}</a>
			</div>

			<?php echo form_open(get_link("customer_cart"),array("id"=>"remove-item")); ?>
				<input type='hidden' name='post_type' value='remove_item'/>
				<input type='hidden' name='item_index' value=''/>
			<?php echo form_close();?>

			<script type="text/javascript">
				function removeItem(cartIndex)
				{
					$("form#remove-item input[name=item_index]").val(cartIndex);
					$("form#remove-item").submit();

					return;
				}
			</script>

		<?php }  else { ?>
			<h2>{your_cart_is_empty_text}</h2>
		<?php } ?>
	</div>
</div>
