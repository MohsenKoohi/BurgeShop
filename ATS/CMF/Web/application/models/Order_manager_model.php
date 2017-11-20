<?php

// Order manager is responsible for shipping and billing addresses, 
// coupons, and shipping method
// In this version, we don't have any of these and thus we just submit order
// and transfer to payment manger

class Order_manager_model extends CI_Model
{
	private $order_table_name="order";
	private $order_history_table_name="order_history";
	private $order_payment_section_table_name="order_payment_section";
	private $order_statuses=array(
		"submitted","payed","verified","processing","completed","canceled"
	);

	private $ops_statuses=array(
		"not_payed","payed","verified"
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
				,`order_message_id` BIGINT DEFAULT 0
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
				,`oh_user_id` INT DEFAULT 0
				,`oh_comment` VARCHAR(511) NOT NULL
				,PRIMARY KEY (oh_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$tbl_name=$this->db->dbprefix($this->order_payment_section_table_name); 
		$ops_statuses="'".implode("','",$this->ops_statuses)."'";
		$ops_status_default=$this->ops_statuses[0];

		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl_name (
				`ops_order_id` INT AUTO_INCREMENT
				,`ops_number` INT NOT NULL
				,`ops_total` DOUBLE
				,`ops_status` ENUM ($ops_statuses) DEFAULT '$ops_status_default'
				,PRIMARY KEY (ops_order_id, ops_number)	
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

	public function get_order_payment_section_statuses()
	{
		return $this->ops_statuses;
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

	public function delete_order($order_id, $customer_id)
	{
		return FALSE;

		$this->db
			->where("order_id", $order_id)
			->where("order_customer_id", $customer_id)
			->delete($this->order_table_name);

		$this->db
			->where("oh_order_id", $order_id)
			->delete($this->order_history_table_name);

		return TRUE;
	}

	public function get_email_subject_and_content($customer_id, $keyword)
	{
		list($type,$id)=explode("=", $keyword);

		if($type == 'order')
			return $this->get_order_invoice($id);

		if($type == 'order_status')
			return $this->get_email_status($id);
	}

	public function get_sms_content($customer_id, $keyword)
	{
		list($type,$id)=explode("=", $keyword);

		if($type == 'order_sms')
			return $this->get_sms_status($id);
	}

	public function email_invoice($order_id)
	{
		$orders=$this->get_orders(array("order_id"=>$order_id));
		if(!$orders)
			return;
		$order=$orders[0];

		list($subject, $content)=$this->get_order_invoice($order_id, $order);

		$customer_id=$order['order_customer_id'];
		$this->load->model("customer_manager_model");
		$customer_info=$this->customer_manager_model->get_customer_info($customer_id);
		$email=$customer_info['customer_email'];
		if(!$email)
			return;

		$this->load->model("es_manager_model");
		$this->es_manager_model->send_email_now($customer_id, "order", "order=$order_id", $email, $subject, $content);

		$props=array(
			"customer_id"	=> $customer_id
			,"order_id"		=> $order_id
		);

		$this->log_manager_model->info("ORDER_EMAIL_INVOICE",$props);	
		$this->customer_manager_model->add_customer_log($customer_id,'ORDER_EMAIL_INVOICE',$props);

		return;
	}

	private function get_order_invoice($order_id, $order=NULL)
	{
		if(!$order)
		{
			$orders=$this->get_orders(array("order_id"=>$order_id));
			if(!$orders)
				return;
			$order=$orders[0];
		}

		$CI=& get_instance();

		$CI->load->model("cart_manager_model");
		$data=array();
		$data['order_id']=$order_id;
		$data['order_info']=$order;
		$data['cart_info']=$CI->cart_manager_model->get_order_cart($order_id, $CI->selected_lang);
		$data['styles_url']=get_link("styles_url");
		
		$CI->lang->load('ae_order',$CI->selected_lang);
		$CI->lang->load('ae_general',$CI->selected_lang);
		$CI->load->library('parser');
		$words=array(
			"order_number","name","date","total","status","currency","status","product_name"
			,"quantity","unit_price","total_price","invoice");
		foreach($this->order_statuses as $s)
			$words[]='order_status_'.$s;

		foreach($words as $w)
			$data[$w."_text"]=$CI->lang->line($w);
		
		$content=$CI->parser->parse($CI->get_admin_view_file("order_invoice"),$data,TRUE);

		$subject=$CI->lang->line("order")." ".$order_id;

		return array($subject, $content);
	}

	public function email_status($order_id)
	{
		$orders=$this->get_orders(array("order_id"=>$order_id));
		if(!$orders)
			return;
		$order=$orders[0];

		list($subject, $content)=$this->get_email_status($order_id, $order);
		
		$customer_id=$order['order_customer_id'];
		$this->load->model("customer_manager_model");
		$customer_info=$this->customer_manager_model->get_customer_info($customer_id);
		$email=$customer_info['customer_email'];
		if(!$email)
			return;

		$this->load->model("es_manager_model");
		$this->es_manager_model->send_email_now($customer_id, "order", "order_status=$order_id", $email, $subject, $content);

		return;
	}

	private function get_email_status($order_id, $order=NULL)
	{
		if(!$order)
		{
			$orders=$this->get_orders(array("order_id"=>$order_id));
			if(!$orders)
				return;
			$order=$orders[0];
		}

		$CI=& get_instance();

		$CI->lang->load('ae_general',$CI->selected_lang);
		$CI->lang->load('ae_order',$CI->selected_lang);

		$subject=$CI->lang->line("order")." ".$order_id;
		$content=str_replace(
			"STATUS"
			,$CI->lang->line("order_status_".$order['order_status'])
			,$CI->lang->line("order_status_changed")
		)."<span style='color:white'>AA</span>";

		return array($subject, $content);
	}

	public function sms_status($order_id)
	{
		$orders=$this->get_orders(array("order_id"=>$order_id));
		if(!$orders)
			return;
		$order=$orders[0];

		$content=$this->get_sms_status($order_id, $order);
		
		$customer_id=$order['order_customer_id'];
		$this->load->model("customer_manager_model");
		$customer_info=$this->customer_manager_model->get_customer_info($customer_id);
		$mobile=$customer_info['customer_mobile'];
		if(!$mobile)
			return;

		$this->load->model("es_manager_model");
		$this->es_manager_model->send_sms_now($customer_id, "order", "order_sms=$order_id", $mobile, $content);

		return;
	}

	private function get_sms_status($order_id, $order=NULL)
	{
		if(!$order)
		{
			$orders=$this->get_orders(array("order_id"=>$order_id));
			if(!$orders)
				return;
			$order=$orders[0];
		}

		$CI=& get_instance();

		$CI->lang->load('ae_general',$CI->selected_lang);
		$CI->lang->load('ae_order',$CI->selected_lang);

		$content=str_replace(
			array("STATUS","ID")
			,array($CI->lang->line("order_status_".$order['order_status']), $order_id)
			,$CI->lang->line("order_status_changed_sms")
		);

		return $content;
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
			,"message_manager_model"
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
		$this->customer_manager_model->add_customer_log($customer_id,'ORDER_SUBMIT',$props);

		$this->cart_manager_model->save_order_cart($order_id);

		$this->add_history($order_id,"submitted");

		$this->add_order_payment_section($order_id, $cart['total_price']);

		$this->lang->load('ce_order',$this->selected_lang);
		$subject=$this->lang->line("order")." ".$order_id;
		$content=$this->lang->line("order_first_message_content");
		$mids=$this->message_manager_model->add_d2c_message(array(
			"sender_id"			=> 4
			,"subject"			=> $subject
			,"receiver_ids"	=>array($customer_id)
			,"verifier_id"		=> 1
			,"content"			=> $content
			,"attachment"		=> NULL
		));

		$mid=$mids[0];
		$this->db
			->set("order_message_id", $mid)
			->where("order_id", $order_id)
			->update($this->order_table_name);

		return $order_id;
	}

	public function get_order_payment_section($order_id, $ops_number)
	{
		return $this->db
			->select("*")
			->from($this->order_payment_section_table_name)
			->where("ops_order_id",$order_id)
			->where("ops_number", $ops_number)
			->get()
			->row_array();
	}

	public function add_order_payment_section($order_id, $total)
	{
		if(!$total)
			return 0;

		$ops_number=1;
		$count_row=$this->db
			->select("COUNT(*) as count")
			->from($this->order_payment_section_table_name)
			->where("ops_order_id",$order_id)
			->get()
			->row_array();
		if($count_row)
			$ops_number=1+$count_row['count'];

		$props=array(
			"ops_order_id"	=> $order_id
			,"ops_number"	=> $ops_number
			,"ops_total"	=>	$total
		);

		$this->db->insert($this->order_payment_section_table_name,$props);
		$this->log_manager_model->info("ORDER_PAYMENT_SECTION_ADD",$props);	

		$customer_id=$this->get_customer_of_order($order_id);
		$this->customer_manager_model->add_customer_log($customer_id,'ORDER_PAYMENT_SECTION_ADD',$props);

		return $count_row;
	}

	public function set_order_payment_section_status($order_id, $ops_number, $status)
	{
		$props=array(
			"ops_order_id"	=> $order_id
			,"ops_number"	=> $ops_number
		);

		$this->db
			->set("ops_status", $status)
			->where($props)
			->update($this->order_payment_section_table_name);
		
		$props["ops_status"]=$status;
		$this->log_manager_model->info("ORDER_PAYMENT_SECTION_STATUS_CHANGE", $props);	

		$customer_id=$this->get_customer_of_order($order_id);
		$this->customer_manager_model->add_customer_log($customer_id, 'ORDER_PAYMENT_SECTION_STATUS_CHANGE', $props);

		return;
	}

	public function add_history($order_id, $status, $comment='')
	{
		$this->db
			->set("order_status", $status)
			->where("order_id", $order_id)
			->update($this->order_table_name);

		$this->load->model("user_manager_model");
		$user_info=$this->user_manager_model->get_user_info();
		if($user_info)
			$user_id=$user_info->get_id();
		else
			$user_id=0;

		$props=array(
			"oh_order_id"	=> $order_id
			,"oh_status"	=> $status
			,"oh_date"		=> get_current_time()
			,"oh_user_id"	=> $user_id
			,"oh_comment"	=> trim(preg_replace('/[\\r\\n]+/', "\n", $comment))
		);
		$this->db->insert($this->order_history_table_name,$props);
		$oh_id=$this->db->insert_id();
		$props['oh_id']=$oh_id;

		$this->log_manager_model->info("ORDER_ADD_HISTORY",$props);	

		$customer_id=$this->get_customer_of_order($order_id);
		$this->customer_manager_model->add_customer_log($customer_id,'ORDER_ADD_HISTORY',$props);

		return;
	}

	private function get_customer_of_order($order_id)
	{
		$order=$this->db
			->from($this->order_table_name)
			->where("order_id", $order_id)
			->get()
			->row_array();

		if($order)
			return $order['order_customer_id'];

		return 0;
	}

	public function get_order_history($order_id)
	{
		return $this->db
			->select("oh.* , user_name, user_code")
			->from($this->order_history_table_name. " oh")
			->join("user","user_id = oh_user_id ","LEFT")
			->where("oh_order_id",$order_id)
			->order_by("oh_id ASC")
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