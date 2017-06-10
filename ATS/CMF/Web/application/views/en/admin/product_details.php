<div class="main">
	<div class="container">
		<h1>{product_details_text} {product_id}
			<?php 
			if($product_info && $product_info['product_title']) 
				echo $comma_text." ".$product_info['product_title'];
			?>
		</h1>		
		<?php 
			if(!$product_info) {
		?>
			<h4>{not_found_text}</h4>
		<?php 
			}else{ 
		?>
			<script src="{scripts_url}/tinymce/tinymce.min.js"></script>
			<div class="container">
				<div class="row general-buttons">
					<a href="{customer_link}" class="two columns " target="_blank">
						<div class="full-width button sub-primary button-type1">
							{customer_link_text}
						</div>
					</a>
				</div>
				<div class="row general-buttons">
					<a  class="two columns"  onclick="deleteproduct()">
						<div class="full-width button sub-primary button-type2">
							{delete_product_text}
						</div>
					</a>
				</div>
				<br>
				<?php echo form_open_multipart(get_admin_product_details_link($product_id),array("onsubmit"=>"return formSubmit();")); ?>
					<input type="hidden" name="product_type" value="edit_product" />
					<div class="row even-odd-bg" >
						<div class="three columns">
							<span>{date_text}</span>
						</div>
						<div class="six columns">
							<input type="text" class="full-width ltr" name="product_date" value="<?php echo $product_info['product_date'];?>" />
						</div>
						<div class="three columns">
							<img 
								class="icon update" src="{images_url}/update.png" 
								onclick="$('[name=product_date]').val('{current_time}');"
							/>
						</div>
					</div>
					<div class="row even-odd-bg" >
						<div class="three columns">
							<span>{creator_user_text}</span>
						</div>
						<div class="six columns">
							<span>
								<?php echo $code_text." ".$product_info['user_id']." - ".$product_info['user_name'];?>
							</span>							
						</div>
					</div>
					<div class="row even-odd-bg" >
						<div class="three columns">
							<span>{active_text}</span>
						</div>
						<div class="six columns">
							<input type="checkbox" class="graphical" name="product_active"
								<?php if($product_info['product_active']) echo "checked"; ?>
							/>
						</div>
					</div>
					<div class="row even-odd-bg" >
						<div class="three columns">
							<span>{allow_comment_text}</span>
						</div>
						<div class="six columns">
							<input type="checkbox" class="graphical" name="product_allow_comment"
								<?php if($product_info['product_allow_comment']) echo "checked"; ?>
							/>
						</div>
					</div>
					<div class="row even-odd-bg" >
						<div class="three columns">
							<span>{categories_text}</span>
						</div>
						<input type="hidden" name="categories"/>
						<div id="category" class="nine columns category-div" style="max-height:500px;overflow:auto;">
							<?php echo $categories;?>
						</div>
						<script type="text/javascript">
							$(function()
							{
								var categories=[<?php echo $product_info['categories']?>];
								for(i=0;i<categories.length;i++)
									$("#category input[data-id="+categories[i]+"]").prop("checked","checked");
								
								$("input[name=categories]").val(categories);

								$("#category span").click(
									function()
									{
										var id=$(this).data("id");
										var el=$("#category input[data-id="+id+"]");
										el.trigger("click");
									}
								);

								$("#category input").change(function()
								{
									var ids=[];
									$("#category input:checked").each(function(index,el)
									{
										ids[ids.length]=$(el).data("id");
									});

									$("input[name=categories]").val(ids.join(","));
								});
							});						
							
						</script>
					</div>
					<div class="tab-container">
						<ul class="tabs">
							<?php foreach($product_contents as $pc) { ?>
								<li>
									<a href="#pc_<?php echo $pc['pc_lang_id'];?>">
										<?php echo $langs[$pc['pc_lang_id']];?>
									</a>
								</li>
							<?php } ?>
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
						<?php foreach($product_contents as $lang=>$pc) {?>
							<div class="tab" id="pc_<?php echo $pc['pc_lang_id'];?>">
								<div class="container">
									<div class="row even-odd-bg" >
										<div class="three columns">
											<span>{active_text}</span>
										</div>
										<div class="six columns">
											<input type="checkbox" class="graphical" 
												name="<?php echo $lang;?>[pc_active]" 
												<?php if($pc['pc_active']) echo "checked"; ?>
											/>
										</div>
									</div>
									<?php if(sizeof($all_langs)>1) { ?>
										<div class="row even-odd-bg">
											<div class="three columns">
												<span>{copy_from_text}</span>
											</div>
											<div class="six columns">
												<select class="full-width" name="<?php echo $lang;?>[copy]">
													<option value="">{please_select_text}</option>
													<?php 
														foreach($all_langs as $slang=>$value)
														{
															if($slang === $lang)
																continue;

															echo "<option value='$slang'>".${"lang_".$slang."_name_text"}."</option>";
														}
													?>
												</select>
											</div>
										</div>
									<?php } ?>
									<div class="row even-odd-bg" >
										<div class="three columns">
											<span>{title_text}</span>
										</div>
										<div class="nine columns">
											<input type="text" class="full-width" 
												name="<?php echo $lang;?>[pc_title]" 
												value="<?php echo $pc['pc_title']; ?>"
												onchange="trimTitle(this);"
											/>
										</div>
									</div>

									<div class="row even-odd-bg" >
										<div class="three columns">
											<span>{image_text}</span>
										</div>
										<div class="nine columns ">
											<div class="two columns ">
												<span>{delete_image_text}</span>
											</div>
											<div class="three columns ">
												<input id="del-img-<?php echo $lang; ?>" type="checkbox" class="graphical" onclick="deleteImage('<?php echo $lang;?>');"/>
											</div>
											<br><br>
											<div class="tweleve columns">
												<input type="hidden" name="<?php echo $lang;?>[pc_image]"
												value="<?php echo $pc['pc_image']; ?>" />
												<?php
													$image=$no_image_url;
													if($pc['pc_image'])
														$image=$pc['pc_image'];
												?>
												<img 
													id="img-<?php echo $lang; ?>"
													src="<?php echo $image; ?>"  
													style="cursor:pointer;max-height:200px;background-color:white"
													onclick="selectImage('<?php echo $lang; ?>');"
												/>
											</div>
										</div>
									</div>
									<div class="row even-odd-bg dont-magnify" >
										<div class="three columns">
											<span>{content_text}</span>
										</div>
										<div class="twelve columns ">
											<textarea class="full-width" rows="15"
												name="<?php echo $lang;?>[pc_content]"
											><?php echo $pc['pc_content']; ?></textarea>
										</div>
									</div>
									<div class="row even-odd-bg" >
										<div class="three columns">
											<span>{meta_keywords_text}</span>
										</div>
										<div class="nine columns">
											<input type="text" class="full-width" 
												name="<?php echo $lang;?>[pc_keywords]" 
												value="<?php echo $pc['pc_keywords']; ?>"
											/>
										</div>
									</div>
									<div class="row even-odd-bg" >
										<div class="three columns">
											<span>{meta_description_text}</span>
										</div>
										<div class="nine columns">
											<input type="text" class="full-width" 
												name="<?php echo $lang;?>[pc_description]" 
												value="<?php echo $pc['pc_description']; ?>"
											/>
										</div>
									</div>
									<div class="row even-odd-bg" >
										<div class="three columns">
											<span>{gallery_text}</span>
										</div>
										<div class="nine columns">
											<style type="text/css">
												.gallery-row img
												{
													max-width: 100%;
													max-height: 300px;
												}
											</style>
											<?php 
												$gallery=$pc['pc_gallery']; 
												if($gallery)
													foreach($gallery['images'] as $index=>$gim)
													{
														$img_link=get_product_gallery_image_url($gim['image']);
											?>
													<div class="row gallery-row separated">
														<input type='hidden' name='<?php echo $lang;?>[pc_gallery][old_images][]' value='<?php echo $index;?>'/>
														<div class="five columns">
															<a href="<?php echo $img_link; ?>" target="_blank">
																<img class="lazy-load" data-ll-type="src"
																	data-ll-url="<?php echo $img_link; ?>"/>
															</a>
														</div>
														<div class="six columns half-col-margin">
															<input type="text"  class="full-width" 
																name='<?php echo $lang;?>[pc_gallery][old_image_text][<?php echo $index?>]'
																value="<?php echo $gim['text']; ?>"
															/>
															<input type="hidden"  
																name='<?php echo $lang;?>[pc_gallery][old_image_image][<?php echo $index?>]'
																value="<?php echo $gim['image']; ?>"
															/>
															<br><br>
															{delete_text}
															<input type="checkbox" class="graphical"
																name='<?php echo $lang;?>[pc_gallery][old_image_delete][<?php echo $index?>]'
															/>
														</div>
													</div>
											<?php
													}
											?>
											<input type="hidden"  
												name="<?php echo $lang;?>[pc_gallery][count]"  
												value="0"
											/>
											<div class="row separated">
												<label>&nbsp;</label>
												<div class="five columns button button-type1" style="font-size:1.3em"
													onclick="addGalleryRow('<?php echo $lang;?>',this);" >
													{add_image_text}
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
					<br><br>
					<div class="row">
							<div class="four columns">&nbsp;</div>
							<input type="submit" class="button-primary four columns" value="{submit_text}"/>
					</div>				
				</form>

				<div style="display:none">
					<?php echo form_open(get_admin_product_details_link($product_id),array("id"=>"delete")); ?>
						<input type="hidden" name="product_type" value="delete_product"/>
						<input type="hidden" name="product_id" value="{product_id}"/>
					</form>

					<script type="text/javascript">

					function trimTitle(el)
					{
						el=$(el);
						var newTitle=el.val().trim().replace(/\s+/g," ");
						el.val(newTitle);
					}

					//gallery operations
					function addGalleryRow(lang,el)
					{
						var counter=$("input[name='"+lang+"[pc_gallery][count]']");
						var index=parseInt(counter.val());
						counter.val(index+1);
						
						var html=
							"<div class='row separated'>"
							+	"<input type='hidden' name='"+lang+"[pc_gallery][new_images][]' value='"+index+"'/>"
							+	"<div class='four columns'>"
							+		"<label>{image_text}</label>"
							+		"<input type='file' multiple name='"+lang+"[pc_gallery][new_image]["+index+"][]'/>"
							+	"</div>"
							+	"<div class='two columns'>"
							+		"<label>{watermark_text}</label>"
							+		"<input type='checkbox' checked name='"+lang+"[pc_gallery][new_image_watermark]["+index+"]'/>"
							+	"</div>"
							+	"<div class='six columns'>"
							+		"<label>{description_text}</label>"
							+		"<input type='text' class='full-width' name='"+lang+"[pc_gallery][new_text]["+index+"]'/>"
							+	"</div>"
							+"</div>";

						html=$(html);
						setCheckBoxGraphical($("input[type=checkbox]",html)[0]);

						$(el).parent().before(html);
					}
					//end of gallery operations

					//operations for product_content iamges
					var activeLang;

					function selectImage(lang)
					{
						var fileMan=$(".burgeFileMan");
						if(!fileMan.length)
							createFileMan();

						fileMan.css("display","block");
						setTimeout(function()
						{
							$(".burgeFileMan iframe")[0].focus();
						},1000);

						activeLang=lang;
					}

					function createFileMan()
					{
						var src="<?php echo get_link('admin_file_inline');?>";
						src+="?parent_function=fileSelected";
						$(document.body).append($(
							"<div class='burgeFileMan' onkeypress='checkExit(event);' tabindex='1' >"
								+"<div class='bmain'>"
								+	"<div class='bheader'>File Manager"
								+		"<button class='close' onclick='closeFileMan()'>×</button>"
								+ "</div>"
								+	"<iframe src='"+src+"'></iframe>"
								+"</div>"
							+"</div>"
						));
					}

					function checkExit(event)
					{
						if(event.keyCode == 27)
							closeFileMan();
					}

					function closeFileMan()
					{
						var fileMan=$(".burgeFileMan");
						
						fileMan.css("display","none");	
					}

					function fileSelected(path)
					{
						$("#img-"+activeLang).prop("src",path);
						$("input[name='"+activeLang+"[pc_image]']").val(path);
						$("#del-img-"+activeLang).prop("checked",false);
						lastImages[activeLang]="";
						closeFileMan();
					}

					var lastImages=[];

					function deleteImage(lang)
					{
						if(typeof(lastImages[lang])==="undefined" || lastImages[lang]=="")
						{
							lastImages[lang]=$("#img-"+lang).prop("src");
							$("input[name='"+lang+"[pc_image]']").val("");
							$("#img-"+lang).prop("src","{no_image_url}");
						}
						else
						{
							$("input[name='"+lang+"[pc_image]']").val(lastImages[lang]);
							$("#img-"+lang).prop("src",lastImages[lang]);	
							lastImages[lang]="";
						}
					}
					//end of operations for product_content images	

					$(window).load(initializeTextAreas);
					var tmTextAreas=[];
					<?php
						foreach($langs as $lang => $value)
							echo "\n".'tmTextAreas.push("textarea[name=\''.$lang.'[pc_content]\']");';
					?>
					var tineMCEFontFamilies=
						"Mitra= b mitra, mitra;Yagut= b yagut, yagut; Titr= b titr, titr; Zar= b zar, zar; Koodak= b koodak, koodak;"+
						+"Andale Mono=andale mono,times;"
						+"Arial=arial,helvetica,sans-serif;"
						+"Arial Black=arial black,avant garde;"
						+"Book Antiqua=book antiqua,palatino;"
						+"Comic Sans MS=comic sans ms,sans-serif;"
						+"Courier New=courier new,courier;"
						+"Georgia=georgia,palatino;"
						+"Helvetica=helvetica;"
						+"Impact=impact,chicago;"
						+"Symbol=symbol;"
						+"Tahoma=tahoma,arial,helvetica,sans-serif;"
						+"Terminal=terminal,monaco;"
						+"Times New Roman=times new roman,times;"
						+"Trebuchet MS=trebuchet ms,geneva;"
						+"Verdana=verdana,geneva;"
						+"Webdings=webdings;"
						+"Wingdings=wingdings,zapf dingbats";
					var tinyMCEPlugins="directionality textcolor link image hr emoticons2 lineheight colorpicker media table code";
					var tinyMCEToolbar=[
					   "link image media hr bold italic underline strikethrough alignleft aligncenter alignright alignjustify styleselect formatselect fontselect fontsizeselect  emoticons2",
					   "cut copy paste bullist numlist outdent indent forecolor backcolor removeformat  ltr rtl lineheightselect table code"
					];

					function formSubmit()
					{
						if(!confirm("{are_you_sure_to_submit_text}"))
							return false;

						return true;
					}

					function RoxyFileBrowser(field_name, url, type, win)
					{
						var roxyFileman ="<?php echo get_link('admin_file_inline');?>";

						if (roxyFileman.indexOf("?") < 0) {     
						 roxyFileman += "?type=" + type;   
						}
						else {
						 roxyFileman += "&type=" + type;
						}
						roxyFileman += '&input=' + field_name + '&value=' + win.document.getElementById(field_name).value;
						if(tinyMCE.activeEditor.settings.language){
						 roxyFileman += '&langCode=' + tinyMCE.activeEditor.settings.language;
						}
						tinyMCE.activeEditor.windowManager.open({
						  file: roxyFileman,
						  title: 'Roxy Fileman',
						  width: 850, 
						  height: 650,
						  resizable: "yes",
						  plugins: "media",
						  inline: "yes",
						  close_previous: "no"  
						}, {     window: win,     input: field_name    });
					
						return false; 
					}

					function initializeTextAreas()
					{
						for(i in tmTextAreas)
		               tinymce.init({
								selector: tmTextAreas[i]
								,plugins: tinyMCEPlugins
								,file_browser_callback: RoxyFileBrowser
								//,width:"600"
								,height:"600"
								,convert_urls:false
								,toolbar: tinyMCEToolbar
								,font_formats:tineMCEFontFamilies
								,media_live_embeds: true
	               	});
              	}

              	function deleteproduct()
					{
						if(!confirm("{are_you_sure_to_delete_this_product_text}"))
							return;

						$("form#delete").submit();
					}
					</script>
				</div>
			</div>
		<?php 
			}
		?>
		
		



	</div>
</div>