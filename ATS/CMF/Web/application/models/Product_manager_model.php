<?php
class Product_manager_model extends CI_Model
{
	private $product_table_name="product";
	private $product_content_table_name="product_content";
	private $product_category_table_name="product_category";
	private $product_writable_props=array(
		"product_price","product_date","product_active","product_allow_comment"
	);
	private $product_content_writable_props=array(
		"pc_active","pc_image","pc_keywords","pc_description","pc_title","pc_content","pc_gallery"
	);

	public function __construct()
	{
		parent::__construct();

		return;
	}

	public function install()
	{
		$product_table=$this->db->dbprefix($this->product_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $product_table (
				`product_id` INT  NOT NULL AUTO_INCREMENT
				,`product_price` DOUBLE NOT NULL DEFAULT 0
				,`product_date` DATETIME  
				,`product_creator_uid` INT NOT NULL DEFAULT 0
				,`product_active` TINYINT NOT NULL DEFAULT 0
				,`product_allow_comment` TINYINT NOT NULL DEFAULT 0
				,`product_comment_count` INT NOT NULL DEFAULT 0
				,PRIMARY KEY (product_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$product_content_table=$this->db->dbprefix($this->product_content_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $product_content_table (
				`pc_product_id` INT  NOT NULL
				,`pc_lang_id` CHAR(2) NOT NULL
				,`pc_active` TINYINT NOT NULL DEFAULT 1
				,`pc_image` VARCHAR(1024) NULL
				,`pc_content` MEDIUMTEXT
				,`pc_title`	 TEXT
				,`pc_keywords` TEXT
				,`pc_description` TEXT
				,`pc_gallery` TEXT
				,PRIMARY KEY (pc_product_id, pc_lang_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$product_category_table=$this->db->dbprefix($this->product_category_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $product_category_table (
				`pcat_product_id` INT  NOT NULL
				,`pcat_category_id` INT NOT NULL
				,PRIMARY KEY (pcat_product_id, pcat_category_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$this->load->model("module_manager_model");

		$this->module_manager_model->add_module("product","product_manager");
		$this->module_manager_model->add_module_names_from_lang_file("product");
		
		return;
	}

	public function uninstall()
	{
		return;
	}
	
	public function get_dashboard_info()
	{
		$CI=& get_instance();
		$lang=$CI->language->get();

		$CI->lang->load('ae_product',$lang);
			
		$data=$this->get_statistics();

		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("product_dashboard"),$data,TRUE);
		
		return $ret;		
	}

	private function get_statistics()
	{
		$tb=$this->db->dbprefix($this->product_table_name);

		return $this->db->query("
			SELECT 
				(SELECT COUNT(*) FROM $tb) as total, 
				(SELECT COUNT(*) FROM $tb WHERE product_active) as active
			")->row_array();
	}

	public function add_product()
	{
		$user=$this->user_manager_model->get_user_info();

		$props=array(
			"product_date"=>get_current_time()
			,"product_creator_uid"=>$user->get_id()
		);

		$this->db->insert($this->product_table_name,$props);
		
		$new_product_id=$this->db->insert_id();
		$props['product_id']=$new_product_id;

		$this->log_manager_model->info("product_ADD",$props);	

		$product_contents=array();
		foreach($this->language->get_languages() as $index=>$lang)
			$product_contents[]=array(
				"pc_product_id"=>$new_product_id
				,"pc_lang_id"=>$index
			);
		$this->db->insert_batch($this->product_content_table_name,$product_contents);

		return $new_product_id;
	}

	public function get_products($filter)
	{
		$this->db->from($this->product_table_name);
		$this->db->join($this->product_content_table_name,"product_id = pc_product_id","left");
		$this->db->join($this->product_category_table_name,"product_id = pcat_product_id","left");
		
		$this->set_product_query_filter($filter);

		$results=$this->db->get();

		$rows=$results->result_array();
		foreach($rows as &$row)
			$row['pc_gallery']=json_decode($row['pc_gallery'],TRUE);
		
		return $rows;
	}

	public function get_total($filter)
	{
		$this->db->select("COUNT( DISTINCT product_id ) as count");
		$this->db->from($this->product_table_name);
		$this->db->join($this->product_content_table_name,"product_id = pc_product_id","left");
		$this->db->join($this->product_category_table_name,"product_id = pcat_product_id","left");
		
		$this->set_product_query_filter($filter);
		
		$row=$this->db->get()->row_array();

		return $row['count'];
	}

	private function set_product_query_filter($filter)
	{
		if(isset($filter['lang']))
			$this->db->where("pc_lang_id",$filter['lang']);

		if(isset($filter['category_id']))
			$this->db->where("pcat_category_id",$filter['category_id']);

		if(isset($filter['title']))
		{
			$title=trim($filter['title']);
			$title="%".str_replace(" ","%",$title)."%";
			$this->db->where("( `pc_title` LIKE '$title')");
		}

		if(isset($filter['active']))
			$this->db->where(array(
				"product_active"=>$filter['active']
				,"pc_active"=>$filter['active']
			));

		if(isset($filter['product_date_le']))
			$this->db->where("product_date <=",str_replace("/","-",$filter['product_date_le']));

		if(isset($filter['product_date_ge']))
			$this->db->where("product_date >=",str_replace("/","-",$filter['product_date_ge']));

		if(isset($filter['order_by']))
		{
			if($filter['order_by']==="random")
				$this->db->order_by("product_id","random");
			else
				$this->db->order_by($filter['order_by']);
		}
		else
			$this->db->order_by("product_id DESC");	

		if(isset($filter['start']))
			$this->db->limit($filter['count'],$filter['start']);

		if(isset($filter['group_by']))
			$this->db->group_by($filter['group_by']);
	
		return;
	}

	public function get_product($product_id,$filter=array())
	{
		$cat_query=$this->db
			->select("GROUP_CONCAT(pcat_category_id)")
			->from($this->product_category_table_name)
			->where("pcat_product_id",$product_id)
			->get_compiled_select();

		$this->db
			->select("product.* , product_content.* , user_id, user_name")
			->select("(".$cat_query.") as categories")
			->from("product")
			->join("user","product_creator_uid = user_id","left")
			->join("product_content","product_id = pc_product_id","left")
			->where("product_id",$product_id);

		$this->set_product_query_filter($filter);

		$results=$this->db
			->get()
			->result_array();

		$this->set_galleries($results);

		return $results;
	}

	private function set_galleries(&$products)
	{
		foreach($products as &$product)
		{
			$gallery=array(
				'last_index'	=> 0
				,'images'		=> array()
			);

			if($product['pc_gallery'])
				$gallery=json_decode($product['pc_gallery'],TRUE);

			$product['pc_gallery']=$gallery;
		}

		return;
	}

	public function set_product_props($product_id, $props, $product_contents)
	{	
		$this->db
			->where("pcat_product_id",$product_id)
			->delete($this->product_category_table_name);
		
		$props_categories=$props['categories'];
		
		if($props_categories!=NULL)
		{
			$categories=explode(",",$props_categories);
			$ins=array();
			foreach($categories as $category_id)
				$ins[]=array("pcat_product_id"=>$product_id,"pcat_category_id"=>(int)$category_id);

			if($ins)
				$this->db->insert_batch($this->product_category_table_name,$ins);
		}

		unset($props['categories']);

		$props=select_allowed_elements($props,$this->product_writable_props);

		if($props)
		{
			foreach ($props as $prop => $value)
				$this->db->set($prop,$value);

			$this->db
				->where("product_id",$product_id)
				->update($this->product_table_name);
		}

		$props['categories']=$props_categories;

		foreach($product_contents as $content)
		{
			$lang=$content['pc_lang_id'];

			$content['pc_gallery']=json_encode($content['pc_gallery']);
			$content=select_allowed_elements($content,$this->product_content_writable_props);
			if(!$content)
				continue;

			foreach($content as $prop => $value)
			{
				$this->db->set($prop,$value);
				$props[$lang."_".$prop]=$value;
			}

			$this->db
				->where("pc_product_id",$product_id)
				->where("pc_lang_id",$lang)
				->update($this->product_content_table_name);
		}
		
		$this->log_manager_model->info("product_CHANGE",$props);	

		return;
	}

	public function change_category($old_category_id,$new_category_id)
	{
		$rows=$this->db
			->where("pcat_category_id",$old_category_id)
			->or_where("pcat_category_id",$new_category_id)
			->group_by("pcat_product_id")
			->get($this->product_category_table_name)
			->result_array();

		$product_ids=array();
		foreach($rows as $row)
			$product_ids[]=$row['pcat_product_id'];

		if(!$product_ids)
			return;

		$this->db
			->where("pcat_category_id",$old_category_id)
			->or_where("pcat_category_id",$new_category_id)
			->delete($this->product_category_table_name);

		$ins=array();
		foreach($product_ids as $product_id)
			$ins[]=array("pcat_category_id"=>$new_category_id,"pcat_product_id"=>$product_id);

		$this->db->insert_batch($this->product_category_table_name,$ins);

		return;
	}

	public function delete_product($product_id)
	{
		$props=array("product_id"=>$product_id);

		$this->db
			->where("product_id",$product_id)
			->delete($this->product_table_name);

		$this->db
			->where("pc_product_id",$product_id)
			->delete($this->product_content_table_name);

		$this->db
			->where("pcat_product_id",$product_id)
			->delete($this->product_category_table_name);
		
		$this->log_manager_model->info("product_DELETE",$props);	

		return;

	}
}
