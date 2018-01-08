<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_Coupon extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->lang->load('ae_coupon',$this->selected_lang);
		$this->load->model("coupon_manager_model");

	}

	public function index()
	{
		if($this->input->post("post_type") == 'add_new_coupon')
			return $this->add_new_coupon();

		$this->data['coupons']=$this->coupon_manager_model->get_all();
		
		$this->data['message']=get_message();
		$this->data['page_link']=get_link("admin_coupon");
		$this->data['lang_pages']=get_lang_pages(get_link("admin_coupon",TRUE));
		$this->data['header_title']=$this->lang->line("coupons");

		$this->send_admin_output("coupon");

		return;	 
	}	

	private function add_new_coupon()
	{
		$coupon_id=$this->coupon_manager_model->add_new();
		
		set_message($this->lang->line("the_new_coupon_addedd_successfully"));

		return redirect(get_admin_coupon_details_link($coupon_id));
	}

	public function details($coupon_id)
	{
		$coupon_id = (int)$coupon_id;
		if($this->input->post("post_type")==="edit_coupon")
			return $this->edit_coupon($coupon_id);

		if($this->input->post("post_type")==="delete_coupon")
			return $this->delete_coupon($coupon_id);

		$this->data['coupon_id']=$coupon_id;
		list($coupon_info, $coupon_customers)=$this->coupon_manager_model->get_coupon($coupon_id);
		$this->data['info']=$coupon_info;
		$this->data['customers']=$coupon_customers;

		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_admin_coupon_details_link($coupon_id,TRUE));
		$this->data['header_title']=$this->lang->line("coupon")." ".$coupon_id.$this->lang->line("header_separator").$this->lang->line("coupons");

		$this->send_admin_output("coupon_details");

		return;
	}

	private function delete_message($coupon_id)
	{
		$res=$this->contact_us_manager_model->delete($message_id);

		if($res)
			set_message($this->lang->line('message_deleted_successfully'));
		else
			set_message($this->lang->line('message_cant_be_deleted'));

		return redirect(get_link("admin_contact_us"));
	}

	private function send_response($message_id)
	{
		$subject=$this->input->post("subject");
		$response=trim($this->input->post("content"));
		$lang=$this->input->post("language");

		$info=$this->contact_us_manager_model->get_messages(
			array("message_id"=>$message_id)
		);
		if(isset($info[0]))
			$info=$info[0];
		else
			return redirect(get_link("admin_contact_us"));

		if($response)
		{
			$response=persian_normalize($response);
			$subject=persian_normalize($subject);

			$this->lang->load('ae_general_lang',$lang);
			$this->lang->load('email_lang',$lang);	

			$subject.=$this->lang->line("header_separator").$info['cu_ref_id'].$this->lang->line("header_separator").$this->lang->line("main_name");

			$mo_response=$subject."\n".$response;
			$this->contact_us_manager_model->set_response($message_id,$mo_response);
			
			$response_to=$this->lang->line("response_to")."<br>".nl2br($info['cu_message_content']);

			$message=str_replace(
				array('$content','$slogan','$response_to'),
				array(nl2br($response),$this->lang->line("slogan"),$response_to)
				,$this->lang->line("email_template")
			);

			burge_cmf_send_mail($info['cu_sender_email'],$subject,$message);

			set_message($this->lang->line("response_sent_successfully"));
		}
		else
			set_message($this->lang->line("response_content_is_empty"));

		return redirect(get_admin_contact_us_message_details_link($message_id));
	}

	public function send_new()
	{
		if($this->input->post("post_type")==="send_message")
		{
			$receivers=$this->input->post("receivers");
			$subject=$this->input->post("subject");
			$content=nl2br($this->input->post("content"));
			$lang=$this->input->post("language");

			if($receivers && $subject && $content)
			{
				$receivers=preg_replace("/\s*[\n]+\s*/", ";", $receivers);
				$receivers=explode(";", $receivers);
				
				$this->lang->load('ae_general_lang',$lang);
				$this->lang->load('email_lang',$lang);	

				$subject.=$this->lang->line("header_separator").$this->lang->line("main_name");

				$message=str_replace(
					array('$content','$slogan','$response_to'),
					array($content,$this->lang->line("slogan"),"")
					,$this->lang->line("email_template")
				);

				$this->log_manager_model->info("CONTACT_US_NEW_MESSAGE",array(
					"receivers"=>implode(";", $receivers)
					,"subject"=>$subject
					,"message"=>$content
				));

				burge_cmf_send_mail($receivers,$subject,$message);

				set_message($this->lang->line("message_sent_successfully"));
				redirect(get_link("admin_contact_us_send_new"));
				return;
			}
			else
				set_message($this->lang->line("fill_all_fields"));

			
		}

		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_link("admin_contact_us_send_new",TRUE));
		$this->data['header_title']=$this->lang->line("send_new_message");

		$this->send_admin_output("contact_us_send_new");

	}
}