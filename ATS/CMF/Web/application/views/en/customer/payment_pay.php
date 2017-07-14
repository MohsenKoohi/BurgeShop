<div class="main">

	<div class="container cart">
		<h1>{payment_of_order_text} {order_id} </h1>
		<br>
					<div class='row'>
				<a href='<?php echo get_link('customer_order_submit');?>' class='three columns anti-float button button-primary'>{submit_order_text}</a>
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

		
	</div>
</div>
