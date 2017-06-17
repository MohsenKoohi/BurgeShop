<?php

class Cart_manager_model extends CI_Model
{
	private $cart_product_table_name="cart_product";
	private $cart_product_option_table_name="cart_product_option";
	
	public function __construct()
	{
		parent::__construct();

      return;
   }

	public function install()
	{
		$tbl_name=$this->db->dbprefix($this->cart_product_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl_name (
				`cp_id` INT AUTO_INCREMENT
				,`cp_customer_id` INT NOT NULL
				,`cp_order_id` INT DEFAULT 0
				,`cp_product_id` INT NOT NULL
				,`cp_quantity`	INT DEFAULT 1
				,PRIMARY KEY (cp_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$tbl_name=$this->db->dbprefix($this->cart_product_option_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl_name (
				`cpo_id` INT AUTO_INCREMENT
				,`cpo_cp_id` INT 
				,`cpo_type` VARCHAR(255)
				,`cpo_value` VARCHAR(1023)
				,PRIMARY KEY (cpo_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$this->load->model("module_manager_model");

		$this->module_manager_model->add_module("cart","cart_manager");
		$this->module_manager_model->add_module_names_from_lang_file("cart");
		
		return;
	}

	public function uninstall()
	{
		return;
	}

	public function get_dashboard_info()
	{
		return "";
		$CI=& get_instance();
		//$lang=$CI->language->get();
		//$CI->lang->load('ae_log',$lang);		
		
		$data=array();
		$row=$this->db
			->select("COUNT(*) as count")
			->from($this->cart_product_table_name)
			->get()
			->row_array();
		$data['total']=$row['count'];
		
		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("cart_dashboard"),$data,TRUE);
		
		return $ret;		
	}

	public function add_product($product_id,$options,$quantity,$price)
	{
		if(!$this->session->userdata("cart"))
			$this->session->set_userdata("cart",array("last_id"=>0,"products"=>array()));

		$cart=$this->session->userdata("cart");
		$last_id=$cart['last_id'];
		$products=$cart['products'];

		$products[$last_id]=array(
			'cart_index'	=> $last_id
			,'product_id'	=> $product_id
			,'options'		=> $options
			,'quantity'		=> $quantity
			,'price'			=> $price
		);

		$last_id++;

		$this->session->set_userdata("cart",array(
			"last_id"	=> $last_id
			,"products"	=> $products
		));

		$this->update_cart();

		return;
	}

	//if customer has been logged in, 
	//it stores cart's products in database for future use  
	private function update_cart()
	{

	}

	public function get_cart($lang_id)
	{
		$cart=$this->session->userdata("cart");
		$pids=array();
		foreach($cart['products'] as $product)
			if(!in_array( $product['product_id'], $pids))
				$pids[]=$product['product_id'];

		$this->load->model("product_manager_model");
		$products=$this->product_manager_model->get_products(array(
			"lang"				=> $lang_id
			,"product_ids"		=> $pids
			,"post_date_le"	=> get_current_time()
			,"active"			=> 1
			,"group_by"			=> "product_id"

		));

		$aproducts=array();
		foreach($products as $product)
			$aproducts[$product['product_id']]=$product;

		$ret=array();
		foreach($cart['products'] as $product)
		{
			$p=$product;
			$p['product_name']=$aproducts[$p['product_id']]['pc_title'];
			$ret[]=$p;
		}

		return $ret;
	}

	
}