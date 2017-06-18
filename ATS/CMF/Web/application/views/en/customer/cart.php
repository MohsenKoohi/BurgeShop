<div class="main">

	<div class="container cart">
		<?php if($products){ ?>
			<h1>{products_text}</h1>
			<br>

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
							<img src='{images_url}/remove.png' title='{remove_text}' class='remove-product'/>
						</div>
						
					</div>
			<?php
				}
			?>

			<br>

			<div class='row'>
				<a href='' class='three columns anti-float button button-primary'>{pay_text}</a>
			</div>
		<?php } ?>
	</div>
</div>
