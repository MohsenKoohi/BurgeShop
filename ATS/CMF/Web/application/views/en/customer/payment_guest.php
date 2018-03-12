<div class="main">

	<div class="container payment-methods">
		<h1>{payment_text}</h1>
		<br>

		<?php echo form_open($page_link);?>
			<input type='hidden' name='post_type' value="guest_payment">
			<div class='row'>
				<label class='three columns'><b>{name_text}</b></label>
				<div class='six columns half-col-maring'>
					<input type='text' name='name' class='full-width'/>
				</div>
			</div>

			<div class='row'>
				<div class='three columns'><b>{email_text}</b></div>
				<div class='six columns half-col-maring'>
					<input type='text' name='email' class='full-width'/>
				</div>
			</div>

			<div class='row'>
				<div class='three columns'><b>{mobile_text}</b></div>
				<div class='six columns half-col-maring'>
					<input type='text' name='mobile' class='full-width'/>
				</div>
			</div>

			<div class='row'>
				<div class='three columns'><b>{total_text}</b></div>
				<div class='six columns half-col-maring'>
					<input type='text' name='total' value="<?php if($total) echo $total;?>" class='ltr ten columns'/> &nbsp; {currency_text}
				</div>
			</div>

			<br><br>
			<b>{please_select_the_payment_method_text}</b>
			<br><br>
			<div class='row'>
				<?php foreach($payment_methods as $p){ ?>
					<div class='row even-odd-bg '>
						<div class='nine columns'>
							<input type='radio' name='payment_method' value='<?php echo $p['code'];?>'/>
							<img src="<?php echo $p['image'];?>"/>
							<b class='payment-method-name'><?php echo $p['name'];?></b>
						</div>
					</div>
				<?php } ?>
			</div>

			<br><br>
			<div class='row'>
				<div class='four columns'>&nbsp;</div>
				<div class='four columns'>
					<input type="submit" class=" button sub-primary button-type1 full-width" value="{submit_text}"/>
				</div>
			</div>
		<?php echo form_close();?>
	</div>
</div>
