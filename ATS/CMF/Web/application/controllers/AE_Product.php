<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_Product extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->lang->load('ae_product',$this->selected_lang);
		$this->load->model("product_manager_model");

	}

	public function index()
	{
		if($this->input->post("product_type")==="add_product")
			return $this->add_product();

		$this->set_products_info();

		//we may have some messages that our product has been deleted successfully.
		$this->data['message']=get_message();

		$this->load->model("product_category_manager_model");
		$this->data['categories']=$this->product_category_manager_model->get_all();

		$this->data['raw_page_url']=get_link("admin_product");
		$this->data['lang_pages']=get_lang_pages(get_link("admin_product",TRUE));
		$this->data['header_title']=$this->lang->line("products");

		$this->send_admin_output("product");

		return;	 
	}	

	private function set_products_info()
	{
		$filters=array();

		$this->initialize_filters($filters);

		$total=$this->product_manager_model->get_total($filters);
		if($total)
		{
			$per_page=20;
			$page=1;
			if($this->input->get("page"))
				$page=(int)$this->input->get("page");

			$start=($page-1)*$per_page;

			$filters['group_by']="product_id";
			$filters['start']=$start;
			$filters['count']=$per_page;
			
			$this->data['products_info']=$this->product_manager_model->get_products($filters);
			
			$end=$start+sizeof($this->data['products_info'])-1;

			unset($filters['start']);
			unset($filters['count']);
			unset($filters['group_by']);

			$this->data['products_current_page']=$page;
			$this->data['products_total_pages']=ceil($total/$per_page);
			$this->data['products_total']=$total;
			$this->data['products_start']=$start+1;
			$this->data['products_end']=$end+1;		
		}
		else
		{
			$this->data['products_current_page']=0;
			$this->data['products_total_pages']=0;
			$this->data['products_total']=$total;
			$this->data['products_start']=0;
			$this->data['products_end']=0;
		}

		unset($filters['lang']);
			
		$this->data['filter']=$filters;

		return;
	}

	private function initialize_filters(&$filters)
	{
		$filters['lang']=$this->language->get();

		if($this->input->get("title"))
			$filters['title']=$this->input->get("title");

		if($this->input->get("category_id")!==NULL)
			$filters['category_id']=(int)$this->input->get("category_id");

		persian_normalize($filters);

		return;
	}

	private function add_product()
	{
		$product_id=$this->product_manager_model->add_product();

		return redirect(get_admin_product_details_link($product_id));
	}

	public function details($product_id)
	{
		if($this->input->post("product_type")==="edit_product")
			return $this->edit_product($product_id);

		if($this->input->post("product_type")==="delete_product")
			return $this->delete_product($product_id);

		$this->data['product_id']=$product_id;
		$product_info=$this->product_manager_model->get_product($product_id);

		$this->data['langs']=$this->language->get_languages();

		$this->data['product_contents']=array();
		$product_title="";
		foreach($this->data['langs'] as $lang => $val)
			foreach($product_info as $pi)
			{
				if($pi['pc_lang_id'] === $this->selected_lang)
					$product_title=$pi['pc_title'];

				if($pi['pc_lang_id'] === $lang)
				{
					$this->data['product_contents'][$lang]=$pi;
					break;
				}
			}

		if($product_info)
		{
			$this->data['product_info']=array(
				"product_date"=>str_replace("-","/",$product_info[0]['product_date'])
				,"product_price"=>$product_info[0]['product_price']
				,"product_allow_comment"=>$product_info[0]['product_allow_comment']
				,"product_active"=>$product_info[0]['product_active']
				,"user_name"=>$product_info[0]['user_name']
				,"user_id"=>$product_info[0]['user_id']
				,"categories"=>$product_info[0]['categories']
				,"product_title"=>$this->data['product_contents'][$this->selected_lang]['pc_title']
			);
			$this->data['customer_link']=get_customer_product_details_link($product_id,$product_title);
		}
		else
		{
			$this->data['product_info']=array();
			$this->data['customer_link']="";
		}
		
		$this->data['current_time']=get_current_time();
		$this->load->model("product_category_manager_model");
		$this->data['categories']=$this->product_category_manager_model->get_hierarchy("checkbox",$this->selected_lang);

		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_admin_product_details_link($product_id,TRUE));
		$this->data['header_title']=$this->lang->line("product_details")." ".$product_id;

		$this->send_admin_output("product_details");

		return;
	}

	private function delete_product($product_id)
	{
		$props=$this->product_manager_model->get_product($product_id);
		foreach($props as $p)
		{
			$gallery=$p['pc_gallery']['images'];
			foreach($gallery as $i)
				unlink(get_product_gallery_image_path($i['image']));
		}
		
		$this->product_manager_model->delete_product($product_id);

		set_message($this->lang->line('product_deleted_successfully'));

		return redirect(get_link("admin_product"));
	}

	private function edit_product($product_id)
	{
		$product_props=array();
		$product_props['categories']=$this->input->post("categories");

		$product_props['product_date']=$this->input->post('product_date');
		persian_normalize($product_props['product_date']);
		if( DATE_FUNCTION === 'jdate')
			validate_persian_date_time($product_props['product_date']);
		
		$product_props['product_price']=(double)persian_normalize($this->input->post('product_price'));
		$product_props['product_active']=(int)($this->input->post('product_active') === "on");
		$product_props['product_allow_comment']=(int)($this->input->post('product_allow_comment') === "on");
		
		$product_content_props=array();
		foreach($this->language->get_languages() as $lang=>$name)
		{
			$product_content=$this->input->post($lang);
			$product_content['pc_content']=$_POST[$lang]['pc_content'];
			$product_content['pc_lang_id']=$lang;

			if(isset($product_content['pc_active']))
				$product_content['pc_active']=(int)($product_content['pc_active']=== "on");
			else
				$product_content['pc_active']=0;

			$product_content['pc_gallery']=$this->get_product_gallery($product_id,$lang);

			$product_content_props[$lang]=$product_content;
		}

		foreach($this->language->get_languages() as $lang=>$name)
		{
			$copy_from=$this->input->post($lang."[copy]");
			if(!$copy_from)
				continue;

			$product_content_props[$lang]=$product_content_props[$copy_from];
			$product_content_props[$lang]['pc_lang_id']=$lang;
		}


		$this->product_manager_model->set_product_props($product_id,$product_props,$product_content_props);
		
		set_message($this->lang->line("changes_saved_successfully"));

		redirect(get_admin_product_details_link($product_id));

		return;
	}

	private function get_product_gallery($product_id, $lang)
	{
		$pp=$this->input->post($lang);
		$pp=$pp['pc_gallery'];
		//bprint_r($pp);

		$gallery=array();
		$gallery['last_index']=0;
		$gallery['images']=array();

		$last_index=&$gallery['last_index'];

		if(isset($pp['old_images']))
			foreach($pp['old_images'] as $index)
			{
				$img=$pp['old_image_image'][$index];
				$delete=isset($pp['old_image_delete'][$index]);
				if($delete)
				{
					unlink(get_product_gallery_image_path($img));
					continue;
				}

				$text=$pp['old_image_text'][$index];
				$gallery['images'][$index]=array(
					"image"	=> $img
					,"text"	=> $text
				);

				$last_index=max(1+$index,$last_index);
			}
		
		if(isset($pp['new_images']))
			foreach($pp['new_images'] as $index)
			{
				$file_names=$_FILES[$lang]['name']['pc_gallery']['new_image'][$index];
				$file_tmp_names=$_FILES[$lang]['tmp_name']['pc_gallery']['new_image'][$index];
				$file_errors=$_FILES[$lang]['error']['pc_gallery']['new_image'][$index];
				$file_sizes=$_FILES[$lang]['size']['pc_gallery']['new_image'][$index];
				$text=$pp['new_text'][$index];
				$watermark=isset($pp['new_image_watermark'][$index]);

				foreach($file_names as $findex => $file_name)
				{
					if($file_errors[$findex])
						continue;

					$extension=pathinfo($file_names[$findex], PATHINFO_EXTENSION);

					if($watermark)
						burge_cmf_watermark($file_tmp_names[$findex]);

					$img_name=$product_id."_".$lang."_".$last_index."_".get_random_word(5).".".$extension;
					$file_dest=get_product_gallery_image_path($img_name);
					move_uploaded_file($file_tmp_names[$findex], $file_dest);

					$gallery['images'][$last_index++]=array(
						"image"	=> $img_name
						,"text"	=> $text
						);
					//echo "***<br>".$file_name."<br>".$file_sizes[$findex]."<br>".$text."<br>watermark:".$watermark."<br>###<br>";
				}			
			}
		
		//bprint_r($gallery);

		//we need in some positions to check if pc_gallery is null
		if(!sizeof($gallery['images']))
			return NULL;

		return $gallery;
	}
}