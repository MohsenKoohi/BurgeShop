<?php

// Order manager is responsible for shipping and billing addresses, 
// coupons, and shipping method
// In this version, we don't have any of these and thus we just submit order
// and transfer to payment manger

class Order_manager_model extends CI_Model
{
	private $order_table_name="order";
	private $order_history_table_name="order_history";
	private $order_statuses=array(
		"submitted","payed","verified","processing","completed","canceled"
	);
	
	public function __construct()
	{
		parent::__construct();

      return;
   }

	public function install()
	{
		$tbl_name=$this->db->dbprefix($this->order_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl_name (
				`order_id` INT AUTO_INCREMENT
				,`order_customer_id` INT NOT NULL
				,`order_date`	CHAR(19)
				,`order_total` DOUBLE 
				,`order_status` VARCHAR(63)
				,PRIMARY KEY (order_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$tbl_name=$this->db->dbprefix($this->order_history_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl_name (
				`oh_id` INT AUTO_INCREMENT
				,`oh_order_id` INT NOT NULL
				,`oh_date`	CHAR(19)
				,`oh_status` VARCHAR(63)
				,`oh_comment` VARCHAR(511) NOT NULL
				,PRIMARY KEY (oh_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$this->load->model("module_manager_model");

		$this->module_manager_model->add_module("order","order_manager");
		$this->module_manager_model->add_module_names_from_lang_file("order");
		
		return;
	}

	public function uninstall()
	{
		return;
	}

	public function get_order_statuses()
	{
		return $this->order_statuses;
	}

	public function get_dashboard_info()
	{
		$CI=& get_instance();
		$lang=$CI->language->get();
		$CI->lang->load('ae_general',$lang);		
		
		$data=array();
		$counts=$this->get_orders_count();
		$order_counts=array();
		foreach($counts as $c)
			$order_counts[]=array(
				"name"	=> $CI->lang->line("order_status_".$c['order_status'])
				,"count"	=> $c['count']
			);
		$data['orders_count']=$order_counts;
		
		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("order_dashboard"),$data,TRUE);
		
		return $ret;		
	}


	private function get_orders_count()
	{
		return $this->db
			->select("order_status, COUNT(*) as count")
			->from($this->order_table_name)
			->group_by("order_status")
			->get()
			->result_array();
	}

	public function submit_order()
	{
		$this->load->model(array(
			"cart_manager_model"
			,"customer_manager_model"
		));
		$cart=$this->cart_manager_model->get_cart($this->selected_lang);
		
		$customer_id = $this->customer_manager_model->get_logged_customer_id();
		$props=array(
			"order_customer_id"	=> $customer_id
			,"order_date"			=> get_current_time()
			,"order_total"			=> $cart['total_price']
		);

		$this->db->insert($this->order_table_name,$props);
		$order_id=$this->db->insert_id();
		$props['order_id']=$order_id;
		$this->log_manager_model->info("ORDER_SUBMIT",$props);	

		$this->cart_manager_model->save_order_cart($order_id);

		$this->add_history($order_id,"submitted");
		$this->customer_manager_model->add_customer_log($customer_id,'ORDER_SUBMIT',$props);

		return $order_id;
	}

	public function add_history($order_id, $status, $comment='')
	{
		$this->db
			->set("order_status", $status)
			->where("order_id", $order_id)
			->update($this->order_table_name);

		$props=array(
			"oh_order_id"	=> $order_id
			,"oh_status"	=> $status
			,"oh_date"		=> get_current_time()
			,"oh_comment"	=> trim(preg_replace('/[\\r\\n]+/', "\n", $comment))
		);
		$this->db->insert($this->order_history_table_name,$props);
		$oh_id=$this->db->insert_id();
		$props['oh_id']=$oh_id;

		$this->log_manager_model->info("ORDER_ADD_HISTORY",$props);	

		$order=$this->db->get_where($this->order_table_name,array("order_id"=> $order_id))->row_array();
		if($order)
		{
			$customer_id=$order['order_customer_id'];
			$this->customer_manager_model->add_customer_log($customer_id,'ORDER_ADD_HISTORY',$props);
		}

		return;
	}

	public function get_order_history($order_id)
	{
		return $this->db
			->select("*")
			->from($this->order_history_table_name)
			->where("oh_order_id",$order_id)
			->order_by("oh_id DESC")
			->get()
			->result_array();
	}

	public function get_orders($filter)
	{
		$this->db
			->select("o.*,  customer_name, customer_email")
			->from($this->order_table_name." o")
			->join("customer","order_customer_id = customer_id","left");

		$this->set_query_filters($filter);
			
		return $this->db
			->get()
			->result_array();
	}

	public function get_total_orders($filter)
	{
		$this->db
			->select("COUNT(*) as count")
			->from($this->order_table_name)
			->join("customer","order_customer_id = customer_id","left");
		
		$this->set_query_filters($filter);

		$row=$this->db
			->get()
			->row_array();

		return $row['count'];
	}

	private function set_query_filters($filter)
	{
		if(isset($filter['order_id']))
		{
			$ids=explode(" ",preg_replace("/\s+/"," ", $filter['order_id']));
			if(sizeof($ids)>1)
				$this->db->where_in("order_id",$ids);
			else
				$this->db->where("order_id",$filter['order_id']);
		}

		if(isset($filter['status']))
			$this->db->where("order_status",$filter['status']);

		if(isset($filter['start_date']))
			$this->db->where("order_date >=",$filter['start_date']." 00:00:00");

		if(isset($filter['end_date']))
			$this->db->where("order_date <=",$filter['end_date']." 23:59:59");

		if(isset($filter['customer_id']))
			$this->db->where("order_customer_id",(int)$filter['customer_id']);

		if(isset($filter['name']))
			$this->db->where("customer_name LIKE '%".str_replace(' ', '%', $filter['name'])."%'");

		if(isset($filter['email']))
			$this->db->where("LOWER(customer_email) LIKE '%".str_replace(' ', '%', strtolower($filter['email']))."%'");

		if(isset($filter['order_by']))
			$this->db->order_by($filter['order_by']);

		if(isset($filter['start']) && isset($filter['length']))
			$this->db->limit((int)$filter['length'],(int)$filter['start']);

		return;
	}	
}