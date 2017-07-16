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
		if($this->input->post())
		{
			if($this->input->post('post_type')==="delete_order")
				return $this->delete_order();
		}
		
		$this->set_search_results();

		$this->data['message']=get_message();

		$this->data['raw_page_url']=get_link("admin_order");
		$this->data['lang_pages']=get_lang_pages(get_link("admin_order",TRUE));
		$this->data['header_title']=$this->lang->line("orders");
		
		$this->send_admin_output("order");

		return;	 
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
		));

		$this->data['order_id']=$order_id;
		$orders_info=$this->order_manager_model->get_orders(array("order_id"=>$order_id));
		if(!$orders_info)
			return redirect(get_link('admin_order'));

		$this->data['order_info']=$orders_info[0];

		$this->data['cart_info']=$this->cart_manager_model->get_order_cart($order_id, $this->selected_lang);
		
		$this->data['payments_info']=$this->payment_manager_model->get_order_payments($order_id);

		$this->data['lang_pages']=get_lang_pages(get_admin_order_details_link($order_id,TRUE));
		$this->data['header_title']=$this->lang->line("order_details")." ".$order_id;
		
		$this->send_admin_output("order_details");

		return;
	}
}