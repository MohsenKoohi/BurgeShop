<div class="main">
	<div class="container">
		<h1>{order_details_text} <?php echo $order_id;?></h1>
		<div class='row'>
			<div class="row even-odd-bg dont-magnify" >
				<div class="three columns">{order_number_text}</div>
				<div class="eight columns"><b><?php echo $order_id; ?></b></div>
			</div>
			<div class="row even-odd-bg dont-magnify" >
				<div class="three columns">{name_text}</div>
				<div class="eight columns">
					<a href="<?php echo get_admin_customer_details_link($order_info['order_customer_id']);?>" >
						<b><?php echo $order_info['customer_name']; ?></b>
					</a>
				</div>
			</div>
			<div class="row even-odd-bg dont-magnify" >
				<div class="three columns">{date_text}</div>
				<div class="eight columns"><span class='date'><b><?php echo $order_info['order_date']; ?></b></span></div>
			</div>
			<div class="row even-odd-bg dont-magnify" >
				<div class="three columns">{total_text}</div>
				<div class="eight columns">
					<span class='date'><b><?php echo price_separator($order_info['order_total']); ?></b></span>
					 {currency_text}
				</div>
			</div>
			<div class="row even-odd-bg dont-magnify" >
				<div class="three columns">{status_text}</div>
				<div class="eight columns">
					<b>
						<?php 
							$status_name='order_status_'.$order_info['order_status'].'_text';
							if(isset($$status_name))
								echo $$status_name;
							else
								echo $order_info['order_status'];
						?>
					</b>
				</div>
			</div>
			<div class="row even-odd-bg dont-magnify" >
				<div class="three columns">{order_message_text}</div>
				<div class="eight columns">
					<a href="<?php echo get_admin_message_details_link($order_info['order_message_id']);?>" >
						<b>{message_text} <?php echo $order_info['order_message_id']; ?></b>
					</a>
				</div>
			</div>

			<div class="row">
				<div class="three columns">
					{last_message_text}
				</div>
				<div class="eight columns">
					<span style="direction:ltr;display:inline-block">
						<b><?php echo str_replace("-","/",$message_info['mi_last_activity']); ?></b>
					</span>
				</div>
			</div>
		</div>

		<br><br>
		<div class="tab-container">
			<ul class="tabs">
				<li><a href="#products">{products_text}</a></li>
				<li><a href="#payment">{payment_text}</a></li>
				<li><a href="#status">{status_text}</a></li>
				<li><a href="#message">{message_text}</a></li>
			</ul>
			<script type="text/javascript">
				$(function(){
				   $('ul.tabs').each(function(){
						var $active, $content, $links = $(this).find('a');
						$active = $($links.filter('[href="'+location.hash+'"]')[0] || $links[0]);
						$active.addClass('active');

						$content = $($active[0].hash);

						$links.not($active).each(function () {
						   $(this.hash).hide();
						});

						$(this).on('click', 'a', function(e){
						   $active.removeClass('active');
						   $content.hide();

						   $active = $(this);
						   $content = $(this.hash);

						   $active.addClass('active');

						   $content.show();						   	

						   e.preventDefault();
						});
					});
				});
			</script>

			<div class="tab" id="products" style="">
				
				<div class='row title-row even-odd-bg'>
					<div class='four columns'>{product_name_text}</div>
					<div class='two columns'>{quantity_text}</div>
					<div class='three columns'>{unit_price_text}</div>
					<div class='three columns'>{total_price_text}</div>
				</div>

				<?php foreach($cart_info as $p){ ?>
					<div class='row even-odd-bg'>
						<div class='four columns'>
							<a href="<?php echo get_admin_product_details_link($p['product_id']);?>">
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

			<div class="tab" id="payment" style="">

				<?php foreach($payments_info as $p){ ?>
					<div class='row even-odd-bg'>
						<div class='row'>
							<div class='three columns'>
								<label>{payment_method_text}</label>
								<span><?php echo ${"payment_method_".$p['method']."_text"};?></span>
							</div>

							<div class='two columns'>
								<label>{payment_id_text}</label>
								<span><?php echo $p['id'];?></span>
							</div>

							<div class='two columns'>
								<label>{date_text}</label>
								<span class='date'><?php echo $p['date'];?></span>
							</div>

							<div class='two columns'>
								<label>{status_text}</label>
								<span><?php echo ${'payment_status_'.$p['status'].'_text'};?></span>
							</div>

							<div class='three columns'>
								<label>{reference_code_text}</label>
								<span class='date'><?php echo $p['reference'];?></span>
							</div>
						</div>

						<br><br>
						<b style='font-size:1.2em'>{history_text}</b>
						<?php foreach($p['history'] as $h){ ?>
							<div class='row separated'>
								<div class='three columns'>
									<label>{date_text}</label>
									<span class='date' title='<?php echo $h['id'];?>'>
										<?php echo $h['date'];?>
									</span>
								</div>

								<div class='three columns'>
									<label>{status_text}</label>
									<span><?php echo ${'payment_status_'.$h['status'].'_text'};?></span>
								</div>

								<div class='six columns'>
									<label>{comment_text}</label>
									<span class=''>
										<?php 
											if($h['comment'])
												foreach($h['comment'] as $index => $value)
													echo ${'payment_comment_'.$index."_text"}.": ".$value."<br>";
										?>
									</span>
								</div>
							</div>
						<?php } ?>
					</div>
				<?php } ?>
			</div>

			<div class="tab" id="status" style="">
				<div class='row separated'>
					<h4>{history_text}</h4>
					<?php foreach($order_history as $h){ ?>
						<div class='row even-odd-bg'>
							<div class='two columns'>
								<label>{status_text}</label>
								<span><?php echo ${"order_status_".$h['oh_status']."_text"};?></span>
							</div>

							<div class='two columns'>
								<label>{date_text}</label>
								<span class='date'><?php echo $h['oh_date'];?></span>
							</div>

							<div class='two columns'>
								<label>{user_text}</label>
								<span class=''>
									<?php 
										if($h['oh_user_id'])
											echo $h['user_name']." (".$h['user_code'].")";
									?>
								</span>
							</div>

							<div class='six columns'>
								<label>{comment_text}</label>
								<span><?php echo nl2br($h['oh_comment']);?></span>
							</div>
						</div>
					<?php } ?>
				</div>

				<div class='row separated'>
					<h4>{submit_status_text}</h4>
					<?php echo form_open('',array('onsubmit'=>'return statusFormSubmitted()','id'=> 'status-from'));?>
						<input type='hidden' name='post_type' value='submit_status'/>
						<div class='row even-odd-bg'>
							<div class='three columns'>
								<span>{status_text}</span>
							</div>
							<div class='six columns'>
								<select name='status' class='full-width'>
									<?php 
										foreach($order_statuses as $s)
										{
											$sel='';
											if($s == $order_info['order_status'])
												$sel='selected';
											echo "<option value='$s' $sel >".${"order_status_".$s."_text"}."</option>";
										}
									?>
								</select>
							</div>
						</div>

						<div class='row even-odd-bg'>
							<div class='three columns'>
								<span>{email_invoice_text}</span>
							</div>
							<div class='nine columns'>
								<input type='checkbox' name='email_invoice' class='graphical' />
							</div>
						</div>

						<div class='row even-odd-bg'>
							<div class='three columns'>
								<span>{email_status_text}</span>
							</div>
							<div class='nine columns'>
								<input type='checkbox' name='email_status' class='graphical' /> 
							</div>
						</div>

						<div class='row even-odd-bg'>
							<div class='three columns'>
								<span>{sms_status_text}</span>
							</div>
							<div class='nine columns'>
								<input type='checkbox' name='sms_status' class='graphical' /> 
							</div>
						</div>

						<div class='row even-odd-bg'>
							<div class='three columns'>
								<span>{comment_text}</span>
							</div>
							<div class='nine columns'>
								<textarea name='comment' class='full-width' rows='5'></textarea>
							</div>
						</div>

						<br><br>
						<div class='row'>
							<input type='submit' value='{submit_text}' class='anti-float two columns button button-primary'/>
						</div>
					<?php echo form_close();?>

					<script type="text/javascript">
						function statusFormSubmitted()
						{
							if(!$('#status-from select').val())
							{
								alert("{please_select_the_new_status_text}");
								return false;
							}

							return true;
						}
					</script>
				</div>
			</div>

			<div class="tab" id="message" style="">
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
				<?php 
					if(!$message_info) {
				?>
					<h4>{not_found_text}</h4>
				<?php 
					}else{ 
				?>
					<div class="container">
						<?php 
							$i=1;
							$verification_status=array();	
							foreach($message_threads as $thread)
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
										{
											$sender=$department_text." ".${"department_".$departments[$thread['mt_sender_id']]."_text"};
											$sender.=" ( ".$user_text." ".$thread['vuc']." - ".$thread['vun']." ) ";
										}
										if($type === "user")
											$sender=$user_text." ".$thread['suc']." - ".$thread['sun'];
										if($type === "customer")
										{
											$link=get_admin_customer_details_link($thread['mt_sender_id']);
											$sender="<a target='_blank' href='$link'>"
												.$customer_text." ".$thread['mt_sender_id']." - ".$thread['scn']
												."</a>";
										}
										echo $sender;
									?>
								</div>

								<div class="three columns">
									<span style="direction:ltr;display:inline-block">
										<?php echo str_replace("-","/",$thread['mt_timestamp']); ?>
									</span>
								</div>

								<?php									
									if(($message_info['mi_sender_type'] === "customer") 
										&& ($message_info['mi_receiver_type'] === "customer")
										&& ($thread['mt_sender_type'] === "customer")
										)
									{
										echo '<div class="five columns">';
										
										$verification_status[$thread['mt_thread_id']]=(int)$thread['mt_verifier_id'];
										if($thread['mt_verifier_id'])
										{
											$verify="checked";
											echo $verified_text." ( ".$user_text." ".$thread['vuc']." - ".$thread['vun']." )";
										}
										else
										{
											$verify="";
											echo $not_verified_text;
										}
										$id=$thread['mt_thread_id'];
										if($access['verifier'])
											echo " - ".$verify_text.": <span>&nbsp;</span> <input type='checkbox' ".$verify." class='graphical' onchange='verifyMessage($id,$(this).prop(\"checked\"));'>";
										
										echo '</div>';
									}
								?>
								
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
												<span>
													<img class='clips' src="{images_url}/clips.png"/>
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

					
					<div class="separated">
						<h2>{reply_text}</h2>
						<?php echo form_open_multipart('',array()); ?>
							<input type="hidden" name="post_type" value="add_message_reply" />			
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
				<?php 
					}
				?>
			</div>
		</div>
								
			
	</div>
</div>