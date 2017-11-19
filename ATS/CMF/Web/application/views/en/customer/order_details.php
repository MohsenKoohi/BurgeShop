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
					?>
				</div>
			</div>

			<div class="row even-odd-bg dont-magnify">
				<div class="three columns">{payments_text}</div>
				<div class="eight	columns">
					<?php
						foreach($order_payment_sections as $ops)
						{
							$status_name='order_payment_section_status_'.$ops['ops_status'].'_text';
							if(isset($$status_name))
								$ops_status=$$status_name;
							else
								$ops_status=$ops['ops_status'];

							echo "<div class='row'>";
							echo "<div class='three columns'>$level_text ".$ops['ops_number']."</div>";
							echo "<div class='three columns'><span class='date'>".price_separator($ops['ops_total'])."</span>$currency_text</div>";
							echo "<div class='three columns'>$ops_status</div>";
							if($ops['ops_status'] == 'not_payed')
							{
								$link=get_customer_order_section_payment_link($order_id, $ops['ops_number']);
								echo "<div class='three columns'><a class='full-width button button-primary sub-primary' href='$link'>$pay_text</a></div>";
							}


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

		<br><br>

		<?php if($message_info){ ?>
			<div class="container separated">
				<style type="text/css">
					.even-odd-bg .even-odd-bg
					{
						margin-bottom:-8px;
					}

					.even-odd-bg.row div.content
					{
						padding:10px;
						border:1px solid #ddd;
						border-radius: 10px;
						max-height: 200px;
						overflow: auto;
						min-height: 50px;
					}
				</style>
				<h1>{message_text} {message_id}
					<?php 
						if($message_info) 
							echo $comma_text." ".$message_info['mi_subject'];
					?>
				</h1>		
				
				<div class="container">
				
					<div style="font-size:1.3em">
						<div class="row">
							<div class="two columns">
								{sender_from_text}:
							</div>
							<div class="ten columns">
								<?php 
									$type=$message_info['mi_sender_type'];;
									if($type === "department")
										$sender=$department_text." ".${"department_".$departments[$message_info['mi_sender_id']]."_text"};
									if($type === "customer")
										$sender=$customer_text." ".$message_info['mi_sender_id']." - ".$message_info['scn'];
									echo $sender;
								?>
							</div>
						</div>

						<div class="row">
							<div class="two columns">
								{receiver_to_text}:
							</div>
							<div class="ten columns">
								<?php 
									$type=$message_info['mi_receiver_type'];
									if($type === "department")
										$receiver=$department_text." ".${"department_".$departments[$message_info['mi_receiver_id']]."_text"};
									if($type === "customer")
										$receiver=$customer_text." ".$message_info['mi_receiver_id']." - ".$message_info['rcn'];
									echo $receiver;
								?>
							</div>
						</div>

						<div class="row">
							<div class="two columns">
								{subject_text}:
							</div>
							<div class="ten columns">
								<?php echo $message_info['mi_subject'];?>
							</div>
						</div>
					</div>			
					<div></div>
					<?php 
						$i=1;
						foreach($threads as $thread)
						{ 
					?>
						<div class="row even-odd-bg dont-magnify">
							<div class="one columns counter" title="<?php echo $thread['mt_thread_id']; ?>">
								# <?php echo $i++;?>
							</div>								
							<div class="three columns">
								<?php 
									$type=$thread['mt_sender_type'];;
									if($type === "department")
										$sender=$department_text." ".${"department_".$departments[$thread['mt_sender_id']]."_text"};
									if($type === "customer")
										$sender=$customer_text." ".$thread['mt_sender_id']." - ".$thread['scn'];
									echo $sender;
								?>
							</div>

							<div class="three columns">
								<span style="direction:ltr;display:inline-block">
									<?php echo str_replace("-","/",$thread['mt_timestamp']); ?>
								</span>
							</div>

							<?php
								if(preg_match("/[ابپتثجچحخدذرز]/",$thread['mt_content']))
									$lang="fa";
								else
									$lang="en";
							?>
							<div class="content eleven columns lang-<?php echo $lang;?>">
								<span>
									<?php echo nl2br($thread['mt_content']);?>
								</span>
							</div>
							<?php 
								if($thread['mt_attachment'])
								{ 
									$link=get_message_thread_attachment_url($thread['mt_message_id'],$thread['mt_thread_id'],$thread['mt_attachment']);
							?>
									<div class="three columns">
										<a href="<?php echo $link;?>" target="_blank">
											<img class='clips' src="{images_url}/clips.png"/>
											<span>
												<b>{attachment_text}</b>
											</span>
										</a>
									</div>	
							<?php 
								} 
							?>			
						</div>
					<?php 
							}
					?>
				</div>
				
				<div class="separated">
					<h2>{reply_text}</h2>
					<?php echo form_open_multipart('',array("onsubmit"=>"return confirm('{are_you_sure_to_send_text}')")); ?>
					<input type="hidden" name="post_type" value="add_reply" />			
						<?php if(sizeof($all_langs)>1){ ?>
							<div class="row response-type">
								<div class="three columns">
									<label>{language_text}</label>
									<select name="language" class="full-width" onchange="langChanged(this);">
										<?php
											foreach($all_langs as $key => $val)
											{
												$sel="";
												if($key===$selected_lang)
													$sel="selected";

												echo "<option $sel value='$key'>$val</option>";
											}
										?>
										<script type="text/javascript">
											var langSelectVal;

											function langChanged(el)
											{
												if(langSelectVal)
													$("#content-ta").toggleClass(langSelectVal);

												langSelectVal="lang-"+""+$(el).val();
												
												$("#content-ta").toggleClass(langSelectVal);
											}

											$(function()
											{
												$("select[name='language']").trigger("change");
											});
										</script>
									</select>
								</div>
							</div>	
						<?php } ?>
						<br><br>
						<div class="row">
							<div class="twelve columns">
								<textarea id="content-ta" name="content" class="full-width" rows="7"></textarea>
							</div>
						</div>
						<div class="row">
							<div class="three columns">
								<span>{attachment_text}</span>
							</div>
							<div class="three columns">
								<input type="file" name="attachment" class="full-width" />
							</div>
						</div>
						
						<br><br>
						<div class="row">
							<div class="four columns">&nbsp;</div>
							<input type="submit" class=" button-primary four columns" value="{send_text}"/>
						</div>
					</form>
				</div>
			</div>
		<?php } ?>			
			
	</div>
</div>