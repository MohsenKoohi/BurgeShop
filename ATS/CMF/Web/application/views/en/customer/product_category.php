<div class="main">
	<div class="container category">
		<h1><?php echo $category_info['pcd_name'];?></h1>
		<div class="row">
			<div class="twelve columns">
				<?php if($category_info['pcd_image']) { ?>
					<div class="post-img">
						<img class="lazy-load" data-ll-type="src"
					 		data-ll-url="<?php echo $category_info['pcd_image'];?>"
					 	/>
					</div>
				<?php } ?>
				<br>
				<div class="post-short-desc">
					<?php echo nl2br($category_info['pcd_description']); ?>
				</div>
			</div>
			<?php if($total_pages>1) { ?>
				&nbsp;<br>
				<div class="three columns results-page-select link-pagination">
					{pagination}
				</div>
			<?php }?>
		</div>
		<br>

		<?php
			foreach($products as $product)
			{
		?>
				<div class="row">
					<div class="twelve columns">
						<a href="<?php echo get_customer_product_details_link($product['product_id'],$product['pc_title']);?>" >
							<h2><?php echo $product['pc_title'];?></h2>
							<div class="post-date"><?php echo str_replace("-","/",$product['product_date']);?></div>
							<?php if($product['pc_image']) { ?>
								<div class="post-img">
									<img class="lazy-load" data-ll-type="src"
										data-ll-url="<?php echo $product['pc_image'];?>"
									/>
								</div>
							<?php } ?>
							<br>
							<div class="post-short-desc">
								<?php 
									$content=$product['pc_content'];
									$content=preg_replace("/\s*<br\s*\/?>\s*/","\n",$content);
									$content=str_replace("&nbsp;"," ", $content);
									$content=strip_tags($content);
									$content=mb_substr($content,0,100);								
									$content=preg_replace("/(\s*\n+\s*)+/", "<br/>", $content);
									
									echo $content."...";
								?>	
							</div>
							<br>
							<div class="read-more">{read_more_text}</div>
						</a>
					</div>
					
				</div>
		<?php
			}
		?>
	</div>
</div>
