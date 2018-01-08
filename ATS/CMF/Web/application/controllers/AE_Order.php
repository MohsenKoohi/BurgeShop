<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_Order extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->load->model(array(
			"order_manager_model"
			,"customer_manager_model"
		));

		$this->lang->load('ae_order',$this->selected_lang);

		return;
	}

	public function index()
	{	
		$this->set_search_results();

		$this->data['message']=get_message();

		$this->data['raw_page_url']=get_link("admin_order");
		$this->data['lang_pages']=get_lang_pages(get_link("admin_order",TRUE));
		$this->data['header_title']=$this->lang->line("orders");
		
		$this->send_admin_output("order");

		return;	 
	}

	private function set_search_results()
	{
		$filter=$this->get_search_filters();
		
		$this->data['filter']=$filter;
		
		$items_per_page=10;
		$page=1;
		if($this->input->get("page"))
			$page=(int)$this->input->get("page");

		$total=$this->order_manager_model->get_total_orders($filter);
		$this->data['total']=$total;
		$this->data['total_pages']=ceil($total/$items_per_page);
		if($total)
		{
			if($page > $this->data['total_pages'])
				$page=$this->data['total_pages'];
			if($page<1)
				$page=1;
			$this->data['current_page']=$page;
			
			$start=($page-1)*$items_per_page;
			$filter['start']=$start;
			$filter['length']=$items_per_page;

			$end=$start+$items_per_page-1;
			if($end>($total-1))
				$end=$total-1;
			$this->data['start']=$start+1;
			$this->data['end']=$end+1;		
	
			$filter['order_by']="order_id DESC";

			$this->data['orders_info']=$this->order_manager_model->get_orders($filter);

			unset($filter['start'],$filter['length'],$filter['order_by']);
		}
		else
		{
			$this->data['start']=0;
			$this->data['end']=0;
			$this->data['orders_info']=array();
		}

		return;
	}

	private function get_search_filters()
	{
		$filter=array();

		$pfnames=array("order_id","start_date","end_date","name","email");
		foreach($pfnames as $pfname)
		{
			if($this->input->get($pfname))
				$filter[$pfname]=$this->input->get($pfname);	

			if(("start_date" === $pfname) || ("end_date"===$pfname))
				if(!validate_persian_date($filter[$pfname]))
					unset($filter[$pfname]);
		}

		return $filter;
	}

	public function details($order_id)
	{
		$order_id=(int)$order_id;

		$this->load->model(array(
			"cart_manager_model"
			,"payment_manager_model"
			,"message_manager_model"
		));

		$this->data['order_id']=$order_id;
		$orders_info=$this->order_manager_model->get_orders(array("order_id"=>$order_id));
		if(!$orders_info)
			return redirect(get_link('admin_order'));

		if($this->input->post("post_type") == 'submit_status')
			return $this->submit_status($order_id);

		if($this->input->post())
		{
			if($this->input->post('post_type')==="set_ops_status")
				return $this->set_ops_status($order_id);

			if($this->input->post('post_type')==="add_new_payment_section")
				return $this->add_new_payment_section($order_id);

			if($this->input->post('post_type')==="delete_order")
				return $this->delete_order();
		}

		$this->data['order_info']=$orders_info[0];
		
		$this->data['cart_info']=$this->cart_manager_model->get_order_cart($order_id, $this->selected_lang);
		
		$this->data['order_payment_sections']=$this->payment_manager_model->get_order_payment_sections($order_id);

		$this->data['order_history']=$this->order_manager_model->get_order_history($order_id);

		$this->load->model("coupon_manager_model");
		$this->data['coupons']=$this->coupon_manager_model->get_order_coupons($order_id);
		$payments_coupons=array();
		foreach($this->data['coupons'] as $c)	
			$payments_coupons[$c['cp_payment_id']]=$c;
		$this->data['payments_coupons']=$payments_coupons;
		
		$this->data['order_statuses']=$this->order_manager_model->get_order_statuses();
		$this->data['order_payment_section_statuses']=$this->order_manager_model->get_order_payment_section_statuses();

		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_admin_order_details_link($order_id,TRUE));
		$this->data['header_title']=$this->lang->line("order_details")." ".$order_id;

		$message_id=(int)$this->data['order_info']['order_message_id'];
		$message_info=$this->message_manager_model->get_admin_message($message_id);

		$this->lang->load('ae_message',$this->selected_lang);

		if($message_info)
		{
			if($this->input->post("post_type") === "add_message_reply")
				return $this->add_message_reply($order_id, $message_id, $message_info);

			$this->data['message_access']=$message_info['access'];
			$this->data['message_info']=$message_info['message'];
			$this->data['message_threads']=$message_info['threads'];

			$this->data['departments']=$this->message_manager_model->get_departments();			
		}
		else
		{
			$this->data['message_info']=NULL;	
		}
		
		$this->send_admin_output("order_details");

		return;
	}	

	private function add_new_payment_section($order_id)
	{
		$amount=$this->input->post("amount");
		persian_normalize($amount);

		$this->order_manager_model->add_order_payment_section($order_id, $amount);

		set_message($this->lang->line("new_payment_section_added_successfully"));

		return redirect(get_admin_order_details_link($order_id)."#payment");
	}

	private function set_ops_status($order_id)
	{
		$ops_number=$this->input->post("ops_number");
		$status=$this->input->post("ops_status");

		$this->order_manager_model->set_order_payment_section_status($order_id, $ops_number, $status);

		set_message($this->lang->line("new_payment_section_status_saved_successfully"));

		return redirect(get_admin_order_details_link($order_id)."#payment");
	}

	private function delete_order()
	{
		$order_id=(int)$this->input->post("order_id");
		$customer_id=(int)$this->input->post("customer_id");

		$result=$this->order_manager_model->delete_order($order_id,$customer_id);
		if($result)
			set_message($this->lang->line("deleted_successfully"));
		else
			set_message($this->lang->line("cant_be_deleted"));

		return redirect(get_link("admin_order"));
	}

	private function add_message_reply($order_id, $message_id, $mess)
	{	
		$attachment=NULL;
		$error="";
		$this->get_attachment_file($attachment,$error);

		if($error)
		{
			set_message($error);
			return redirect(get_admin_order_details_link($order_id)."#message");
		}
		
		$thread_props=array(
			"content"		=> $this->input->post("content")
			,"attachment"	=> $attachment
		);

		$user_id=$this->user_manager_model->get_user_info()->get_id();

		$st=$mess['message']['mi_sender_type'];
		$rt=$mess['message']['mi_receiver_type'];

		if( (($st==="customer") && ($rt==="department")) ||
			(($st==="department") && ($rt==="customer")) )
		{
			$thread_props['sender_type']="department";
			if($st==="department")
				$thread_props['sender_id']=$mess['message']['mi_sender_id'];
			else
				$thread_props['sender_id']=$mess['message']['mi_receiver_id'];

			$thread_props['verifier_id']=$user_id;
		}

		$this->message_manager_model->add_reply($message_id,array(),$thread_props);

		set_message($this->lang->line("your_reply_added_successfully"));

		return redirect(get_admin_order_details_link($order_id)."#message");
	}

	private function get_attachment_file(&$attachment,&$error)
	{
		$attachment=NULL;
		$error="";

		$file_name=$_FILES['attachment']['name'];
		$file_tmp_name=$_FILES['attachment']['tmp_name'];
		$file_error=$_FILES['attachment']['error'];
		$file_size=$_FILES['attachment']['size'];

		if($file_error ==  UPLOAD_ERR_NO_FILE)
			return;
	
		if($file_error)
		{
			$error=$this->lang->line("the_file_is_erroneous");
			return;
		}

		if($file_size >  10*1024*1024 )
		{
			$error = $this->lang->line("the_file_size_is_larger_than");
			return;
		}

		$extension=strtolower(pathinfo($file_name, PATHINFO_EXTENSION));		
		$attachment=array(
			"temp_name"		=> $file_tmp_name
			,"extension"	=> $extension
		);

		return;		
	}


	private function submit_status($order_id)
	{
		$new_status=$this->input->post("status");
		$comment=preg_replace("/[\\n\\r]+/", "\n", $this->input->post("comment"));

		$this->order_manager_model->add_history($order_id, $new_status, $comment);

		if($this->input->post("email_invoice")=="on")
			$this->order_manager_model->email_invoice($order_id);

		if($this->input->post("email_status")=="on")
			$this->order_manager_model->email_status($order_id);

		if($this->input->post("sms_status")=="on")
			$this->order_manager_model->sms_status($order_id);

		set_message($this->lang->line("new_status_submitted_successfully"));

		redirect(get_admin_order_details_link($order_id)."#status");

		return;
	}
}