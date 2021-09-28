<?php 
public function setQuotationByBA() {
   	$lead_id = base64_decode($this->uri->segment(4, 0));
	$lead_com_id = base64_decode($this->uri->segment(5));
	
	//   echo  $this->create_attachment_pdf_by_mng($lead_id);die;
   
	$get_case_data1 = $this->leadM->getCaseLead($lead_id);

	if($get_case_data1['created_by'] == '1'){
		$created_by = $get_case_data1['added_by'];
	} else {
		$created_by = $get_case_data1['created_by'];
	}

	$data['lead_id'] = $lead_id;

	$data['bank_data'] = $this->db->select('users.*,user_details.cug_mobile')
						    ->from('users')
						    ->join('user_details', 'user_details.user_id = users.id')
						    ->where(array('users.id'=>$get_case_data1['added_by']))
						    ->get()->row_array(); 

	$data['created_by_name'] = $this->db->select('name')
						     ->from('users')
						     ->where(array('id'=>$created_by))
						     ->get()->row_array();

	$get_all_quo = $this->leadM->getAllQuotation($lead_id); 
	$get_view_data = $this->w_user->getLeadsData($lead_id);
	$data['res']   = $get_view_data;

	$pid = base64_decode($this->input->get('pid'));
	$data['pid'] = $pid;

	$user_data = $this->session->userdata('adminData');
	if ($user_data['user_type'] == 4) {
		$sel_by = 'Admin';
	} else {
		$sel_by_name = $this->session->userdata('adminData')['Name'];
		$sel_by = ucwords($sel_by_name);
	} 

	// Get case report preparation detail
	$data['report_preparation_data'] = $this->db->select('*')
						     ->from('report_prepare_master')
						     ->where(array('case_id'=>$lead_com_id,'user_role_type'=>3,'status'=>3))
						     ->get()->row_array();
						     
	/***********************************/
	
	
	$desig_id = $this->session->userdata('designation_id');
	if ($desig_id == 69) { // designation id = 69 for DGM  designation (48 hrs.)
    	$sel_by = 'DGM';
	} else if ($desig_id == 68) { // designation id = 68 for avp designation  (48 hrs.)
		$sel_by = 'AVP';
	}

	if ($this->input->post()) 
	{ 

		$userId = $this->session->userdata('adminData')['userId'];
		$userTypeId = $this->session->userdata('adminData')['user_type']; 
		$userDep = '';
		if($userTypeId == 1){
			$userDepartmentDet = $this->db->select('*')->from('employee_details')->where(array('user_id' => $userId))->get()->row_array();
		    $userDep =  $userDepartmentDet['department'];
		}else if($userTypeId == 1){
			$userDepartmentDet = $this->db->select('*')->from('associate_details')->where(array('user_id' => $userId))->get()->row_array();
		    $userDep =  $userDepartmentDet['department_id'];
		}
		
		$quotation 	= trim($this->input->post('quotation_val'));
		
		$percent 	= $this->input->post('percent'); 
		$due_quotation_val 	= trim($this->input->post('due_quotation_val'));
		$quotation_remark 	= trim($this->input->post('quotation_remark'));
		$discount 			= $this->input->post('discount');
		$account_type 		= $this->input->post('account_type');
		$pay_payable_by 	= $this->input->post('pay_payable_by');
		
		$page_id 			= $this->input->post('page_id');

		$success_message = 'Your quotation value has been successfully submitted. Please check your mail for accept quotation.';
		
		if($get_view_data['added_by'] != '') {
            
            $get_cr_data = $this->w_user->getLeadUser($get_view_data['added_by']);
            $get_created_by_data = $this->w_user->getLeadUser($get_cr_data['id']);
            $created_by = $get_created_by_data['name'];
        }else {
            if($get_view_data['created_by'] != '1'){ 
                $get_user_data = $this->w_user->getLeadUser($get_view_data['created_by']);
                $get_created_by_data = $this->w_user->getLeadUser($get_user_data['id']);
                $created_by = $get_created_by_data['name'];
            } else{ 
             	$get_user_data = $this->w_user->getLeadUser($get_view_data['added_by']);
                $get_created_by_data = $this->w_user->getLeadUser($get_user_data['id']);
                $created_by = $get_created_by_data['name'];
            }
        }
        // B A detais
        $ba_id = $get_view_data['assign_lead']; 
        $ba_Det = $this->db->select('*')->from('users')->where(array('id'=>$ba_id))->get()->row_array();
        $ba_mobile = $this->w_user->getBAMobile($ba_id);
        $ba_name = ucwords($ba_Det['name']);
        $ba_email = ucwords($ba_Det['email']);
        //bankid
        $bank_id = $get_view_data['added_by'];
        //get bank details
		$bank_Det = $this->db->select('*')->from('users')->where(array('id'=>$bank_id))->get()->row_array();
			    
		$bank_email = $bank_Det['email'];
		
		$all_assets = $this->db->select('*')->from('lead_component')->where(array('lead_id'=>$lead_id,'status' => 1))->get()->result_array(); 
						     
		$all_assets_count = count($all_assets); 
		
		if($all_assets_count == 1){
			$asset_type_val = $this->db->select('*')->from('assets_type')->where(array('id' => $all_assets[0]['type_of_assets']))->get()->row_array();
			$cat_type_val = $this->db->select('*')->from('category_of_assets')->where(array('id' => $all_assets[0]['category_assets']))->get()->row_array();
			$asset_type_txt = $asset_type_val['property_name'];
			$cat_type_txt = $cat_type_val['name'];
			
			$asset_address = '';
            $asset_address .= $all_assets[0]['property_no'].', '.$all_assets[0]['colony'].', ';
            $table_name1 = 'master_states';
            
            $arr1 = array('status' => '1', 'id' => $all_assets[0]['state']);
            $state_name = $this->GetAnyData($table_name1, $arr1, $all_assets[0]['state']);
            
            $table_name2 = 'master_district';
            
            $arr2 = array('status' => '1', 'id' => $all_assets[0]['district']);
            $district_name = $this->GetAnyData($table_name2, $arr2, $all_assets[0]['district']);
            
            if(!empty($all_assets[0]['city_type'])){
                    if($all_assets[0]['city_type'] == '1'){
                        $table_name3 = 'master_cities';
                    } else if($all_assets[0]['city_type'] == '2'){
                        $table_name3 = 'master_tehsil';
                    } else if($all_assets[0]['city_type'] == '3'){
                        $table_name3 = 'master_villages';
                    } 

                $arr3 = array('status' => '1', 'id' => $all_assets[0]['city_val']);
                $city_name = $this->GetAnyData($table_name3, $arr3, $all_assets[0]['city_val']);
                    $asset_address .= $city_name['name'].', '.$district_name['name'].', '.$state_name['name'].', '.$all_assets[0]['pincode'];
                }
            $situated_at_txt = $asset_address;

		} else {
			$asset_type_txt = ''; $cat_type_txt = ''; $nature_type_txt = ''; $situated_at_txt = '';
			$count = 1; 
			foreach ($all_assets as $key => $value) {
				$asset_type_val = $this->db->select('*')->from('assets_type')->where(array('id' => $value['type_of_assets']))->get()->row_array();
				$cat_type_val = $this->db->select('*')->from('category_of_assets')->where(array('id' => $value['category_assets']))->get()->row_array();
				// $nature_type_val = $this->db->select('*')->from('nature_of_asset')->where(array('id' => $value['nature_assets']))->get()->row_array();
				$asset_type_txt .= $asset_type_val['property_name'].' | ';
				$cat_type_txt .= $cat_type_val['name'].' | ';
				// $nature_type_txt .= $count.'. '.$nature_type_val['name'].' ';
				

				$asset_address = '';
                $asset_address .= $value['property_no'].', '.$value['colony'].', ';
                $table_name1 = 'master_states';
            
                $arr1 = array('status' => '1', 'id' => $value['state']);
                $state_name = $this->GetAnyData($table_name1, $arr1, $value['state']);
        
                $table_name2 = 'master_district';
            
                $arr2 = array('status' => '1', 'id' => $value['district']);
                $district_name = $this->GetAnyData($table_name2, $arr2, $value['district']);
                    
                    if(!empty($value['city_type'])){
                        if($value['city_type'] == '1'){
                            $table_name3 = 'master_cities';
                        } else if($value['city_type'] == '2'){
                            $table_name3 = 'master_tehsil';
                        } else if($value['city_type'] == '3'){
                            $table_name3 = 'master_villages';
                        } 

                    $arr3 = array('status' => '1', 'id' => $value['city_val']);
                    $city_name = $this->GetAnyData($table_name3, $arr3, $value['city_val']);
                        $asset_address .= $city_name['name'].', '.$district_name['name'].', '.$state_name['name'].', '.$value['pincode'];
                    }
                $situated_at_txt .= $asset_address.' | ';


				$count++;
			}
		}

		if (!empty($quotation)) 
		{
			if($page_id == 5){
			    
				$get_leads_data = $this->db->select('*')->from('leads')->where(array('id' => $lead_id))->get()->row_array();
				$head_e_id = $get_case_data1['customer_email_id'];
				$head_e_id2 = $get_case_data1['co_persone_email'];
				
				$reporting_services = $get_case_data1['reporting_services'];
				$ass_purpose 		= $get_case_data1['purpose_of_assignment'];
				$rep_ser 			= $this->businessM->repSer($reporting_services, $ass_purpose);
				$reporting_ser 		= $rep_ser['rep_ser']['name'];
				$auto_popu 			= $rep_ser['ass_pur']['name'];
				
				$quotation_type 	= $this->input->post('quotation_type');
				$no_advance_status 	= $this->input->post('no_advance_status');
				
				$no_advance_pay_term 		= $this->input->post('no_advance_pay_term');
				//$no_advance_pay_term_mng 	= $this->input->post('no_advance_pay_term_mng');
				// if(empty($no_advance_pay_term))
				// {
				// 	$no_advance_pay_term=$no_advance_pay_term_mng;
				// }

				$no_advance_quo_purpose 	= $this->input->post('no_advance_quo_purpose');
				$comment 					= $this->input->post('comment');
				
				if(empty($percent)){
					$percent = $this->input->post('prev_quotation_percentage');
				}
				
				$arr = array(
							'set_quotation' 		=> $quotation, 
							'no_advance_status'		=> $no_advance_status,
							'no_advance_pay_term'		=> $no_advance_pay_term,
							'no_advance_quo_purpose'=> $no_advance_quo_purpose,
							'no_advance_status_comment'			=> $comment,
							'assign_approval_for_no_advance'  	=> 2,
							'quotation_percentage' 	=> $percent, 
							'due_quotation' 		=> $due_quotation_val, 
							'remarks' 				=> $quotation_remark, 
							'updated_at' 			=> date('Y-m-d H:i:s'),
							// 'discount' 				=> $discount
							);
				$condi = array('id' => $lead_id);
				
				$save_quotation = $this->businessM->saveQuotation($condi, $arr);
				
				$lead_set_status = 4;
				
				$assignment_purpose = $this->db->select('*')->from('assignment_purpose')->where(array('id'=>$get_case_data1['purpose_of_assignment']))->get()->row_array();
						    
				$bank_id = $get_case_data1['added_by'];
				
				$bank_Det = $this->db->select('u.*, bd.bank_name')->from('users as u')->join('user_details as ud', 'ud.user_id = u.id')
				->join('bank_branch_master as bm', 'ud.bank_id = bm.id')->join('bank_details as bd', 'bm.bank_id = bd.id')->where(array('u.id' => $bank_id ))->get()->row_array(); 
			                          
				$bank_email = $bank_Det['email'];
				
				$ba_id = $get_case_data1['assign_lead'];
                $ba_Det = $this->db->select('*')->from('users')->where(array('id'=>$ba_id))->get()->row_array();
                $ba_mobile = $this->w_user->getBAMobile($ba_id);
                $ba_name = ucwords($ba_Det['name']);
                $ba_email = $ba_Det['email'];
                $ba_official_mobile = $this->w_user->getBAOfficialMobile($ba_id);
                
                if(empty($ba_name)){
                    $ba_name = 'Business Coordinator';
                }
                
                if(empty($ba_mobile)){
                    $ba_mobile = '0120-4110117/432647';
                }
                
                if(empty($ba_email)){
                    $ba_official_email = 'admin@visindia.org';
                } else{
                    $ba_official_email = $ba_email;
                }
                
                $vis_case_id = $get_case_data1['lead_ids'];

                if(!empty($no_advance_pay_term)) {

                	$payment_terms=array('1'=>'before site inspection','2'=>'after site inspection','3'=>'after submission of draft report','4'=>'within 7 days after submission of final report','5'=>'before delivery of the report');
                	$no_advance_pay_term_text=$payment_terms[$no_advance_pay_term];
                    // if($no_advance_pay_term == 1) {
                    //     $no_advance_pay_term_text = 'After Survey';
                    // } else if($no_advance_pay_term == 2) {
                    //     $no_advance_pay_term_text = 'After Submission of Draft Report';
                    // } else {
                    //     $no_advance_pay_term_text = 'After Submission of Final Report Within 7 Days';
                    // }
                }
                
				if($quotation_type == 1){ // for custom quotation
					if($no_advance_status == 2){ // approved
						if($no_advance_quo_purpose == 1){ // notify only
							$lead_set_status = 6;
							$arr = array('status' => 6 );
							$this->db->where(array('id' => $lead_id))->update('leads', $arr);
							$arr2= array('case_activity_status' => 6 );
							$this->db->where(array('lead_id' => $lead_id, 'status' => 1))->update('lead_component', $arr2);
							
							$get_cases = $this->db->select('*')->from('lead_component')->where(array('lead_id' => $lead_id))->get()->result_array();
							
							$click_here = base_url('admin/compL/approveLead?lead_id='.base64_encode($lead_id));
							
							$asset_attachment = $this->createPdf($lead_id);
							
							$attched_file = $_SERVER["DOCUMENT_ROOT"].'/'.$asset_attachment;
							
							$update_array = array('approval_link_expire' => 1);
							$this->db->where('id', $lead_id)->update('leads', $update_array);
							
							$cond_1 = array('definition' => '56', 'status' => '1' );
							$getEmail = $this->w_user->getEmail('notification_setting_master', $cond_1);
							
							$vars = array(
								'[$Customer_Name]'  => ucwords($get_case_data1['customer_name']),
								'[$CASE_ID]'  			=> $vis_case_id,
								'[$Coordinating_Person]'	=> ucwords($get_case_data1['co_persone_name']),
	                          	'[$Relationship]'       	=> $get_case_data1['relation_with_owner'],
                              	'[$Assignment_Purpose]'     => $assignment_purpose['name'],
                              	'[$Assets_Number]'       	=> $get_case_data1['assets_number'],
                              	'[$Bank_Name]'			=> ucwords($bank_Det['bank_name']),
              					'[$Manager_Name]'		=> ucwords($bank_Det['name']),
                              	'[$AMOUNT]'		=> $quotation,
                              	'[$USER_NAME]' 	=> $ba_name,
                				'[$USER_Official_PHONE]' => $ba_mobile,
                				'[$PAYMENT_TERM]'=>$no_advance_pay_term_text,
                              	'[$URL]'  		=> $click_here,
                              	'[$OFFICE_CONTACT]'=>OFFICE_CONTACT
                        	); 

							$msg = strtr($getEmail['body'], $vars);
							$sign = $msg;
							$sign .= $getEmail['content'];

							$vars1 = array(
                              '[$CASE_ID]'  	=> $vis_case_id,
                              '[$Customer_Name]' =>ucwords($get_case_data1['customer_name'])
                        	); 

							$sub = strtr($getEmail['subject'], $vars1);

							$get_leads_data = $this->db->select('*')->from('leads')->where(array('id' => $lead_id))->get()->row_array();
							$head_e_id = $get_case_data1['customer_email_id'];
							$head_e_id2 = $get_case_data1['co_persone_email'];
							
							$this->email->cc($bank_email.','.$ba_email.','.$this->backupEmail);
							// this code by mangal 02-06-2020 start
						 	$vis_attachment = $this->create_attachment_pdf_by_mng($lead_id);
						 	 $attched_vis = $_SERVER["DOCUMENT_ROOT"].'/'.$vis_attachment;
							$this->email->attach($attched_vis);
							// this code by mangal 02-06-2020 end
							
							$this->sendEmailWithAttachment($head_e_id, $sign, $sub, $getEmail['send_to'], $attched_file);
							if ($head_e_id != $head_e_id2) {
								$this->sendEmailWithAttachment($head_e_id2, $sign, $sub, $getEmail['send_to'], $attched_file);
							}


							$cond_2 = array('definition' => '59', 'status' => '1' );
							$getEmail2 = $this->w_user->getEmail('notification_setting_master', $cond_2);

							$vars2 = array(
                              '[$CASE_ID]'  	=> $vis_case_id,
                              '[$Customer_Name]'  => ucwords($get_case_data1['customer_name'])
                        	); 

							$sub2 = strtr($getEmail2['subject'], $vars2);

							$vars2 = array(
								'[$Customer_Name]'  => ucwords($get_case_data1['customer_name']),
								'[$CASE_ID]'  			=> $vis_case_id,
								'[$Coordinating_Person]'	=> ucwords($get_case_data1['co_persone_name']),
	                          	'[$Relationship]'       	=> $get_case_data1['relation_with_owner'],
                              	'[$Assignment_Purpose]'     => $assignment_purpose['name'],
                              	'[$Assets_Number]'       	=> $get_case_data1['assets_number'],
                              	'[$Bank_Name]'			=> ucwords($bank_Det['bank_name']),
              					'[$Manager_Name]'		=> ucwords($bank_Det['name']),
                              	'[$AMOUNT]'		=> $quotation,
                              	'[$CONDITION]'		=> $no_advance_pay_term_text,
                              	'[$PAYMENT_TERM]'=>$no_advance_pay_term_text,
                              	'[$USER_NAME]' 	=> $ba_name,
                				'[$USER_PHONE]' => $ba_mobile,
                              	'[$URL]'  		=> $click_here,
                              	'[$OFFICE_CONTACT]'=>OFFICE_CONTACT
                        	); 

							$msg2 = strtr($getEmail2['body'], $vars2);
							$sign2 = $msg2;
							$sign2 .= $getEmail2['content'];
							
                            // this code by mangal 02-06-2020 start
							 $vis_attachment = $this->create_attachment_pdf_by_mng($lead_id);
							 $attched_vis = $_SERVER["DOCUMENT_ROOT"].'/'.$vis_attachment;
							 $this->email->attach($attched_vis);
							// this code by mangal 02-06-2020 end
							
							$this->email->cc($bank_email.','.$ba_email.','.$this->backupEmail);
							$this->sendEmailWithAttachment($head_e_id, $sign2, $sub2, $getEmail2['send_to'], $attched_file);
							if ($head_e_id != $head_e_id2) {
								$this->sendEmailWithAttachment($head_e_id2, $sign2, $sub2, $getEmail2['send_to'], $attched_file);
							}
								
							
						} else {
							$click_here = base_url('admin/compL/approveLead?lead_id='.base64_encode($lead_id));

							$update_array = array('approval_link_expire' => 1);
							$this->db->where('id', $lead_id)->update('leads', $update_array);

							$click_here = base_url('admin/compL/approveLead?lead_id='.base64_encode($lead_id));

							$asset_attachment = $this->createPdf($lead_id);
							$attched_file = $_SERVER["DOCUMENT_ROOT"].'/'.$asset_attachment;

							$cond_2 = array('definition' => '60', 'status' => '1' );
							$getEmail2 = $this->w_user->getEmail('notification_setting_master', $cond_2);

							$vars2 = array(
                              '[$CASE_ID]'  	=> $vis_case_id,
                              '[$Customer_Name]'  	=> ucwords($get_case_data1['customer_name'])
                        	); 

							$sub2 = strtr($getEmail2['subject'], $vars2);

							$vars2 = array(
								'[$Customer_Name]'  	=> ucwords($get_case_data1['customer_name']),
								'[$CASE_ID]'  			=> $vis_case_id,
								'[$Coordinating_Person]'	=> ucwords($get_case_data1['co_persone_name']),
	                          	'[$Relationship]'       	=> $get_case_data1['relation_with_owner'],
                              	'[$Assignment_Purpose]'     => $assignment_purpose['name'],
                              	'[$Assets_Number]'       	=> $get_case_data1['assets_number'],
                              	'[$Bank_Name]'			=> ucwords($bank_Det['bank_name']),
              					'[$Manager_Name]'		=> ucwords($bank_Det['name']),
                              	'[$AMOUNT]'		=> $quotation,
                              	'[$CONDITION]'		=> $no_advance_pay_term_text,
                              	'[$PAYMENT_TERM]'=>$no_advance_pay_term_text,
                              	'[$USER_NAME]' 	=> $ba_name,
                				'[$USER_PHONE]' => $ba_mobile,
                              	'[$URL]'  		=> $click_here,
                              	'[$OFFICE_CONTACT]'=>OFFICE_CONTACT
                        	); 

							$msg2 = strtr($getEmail2['body'], $vars2);
							$sign2 = $msg2;
							$sign2 .= $getEmail2['content'];
                            // this code by mangal 02-06-2020 start
							 $vis_attachment = $this->create_attachment_pdf_by_mng($lead_id);
							 $attched_vis = $_SERVER["DOCUMENT_ROOT"].'/'.$vis_attachment;
							 $this->email->attach($attched_vis);
							// this code by mangal 02-06-2020 end
							$this->email->cc($bank_email.','.$ba_email.','.$this->backupEmail);
							$this->sendEmailWithAttachment($head_e_id, $sign2, $sub2, $getEmail2['send_to'],$attched_file);
							if ($head_e_id != $head_e_id2) {
								$this->sendEmailWithAttachment($head_e_id2, $sign2, $sub2, $getEmail2['send_to'],$attched_file);
							}
						} 
					} else {
						$click_here = site_url('admin/compL/setTransaction?lead_id='.base64_encode($lead_id));

						$asset_attachment = $this->createPdf($lead_id);

						$attched_file = $_SERVER["DOCUMENT_ROOT"].'/'.$asset_attachment;

						$cond_1 = array('definition' => '58', 'status' => '1' );
						$getEmail = $this->w_user->getEmail('notification_setting_master', $cond_1);

						$vars = array(
							'[$Customer_Name]'  	=> ucwords($get_case_data1['customer_name']),
							'[$CASE_ID]'  			=> $vis_case_id,
							'[$Coordinating_Person]'	=> ucwords($get_case_data1['co_persone_name']),
                          	'[$Relationship]'       	=> $get_case_data1['relation_with_owner'],
                          	'[$Assignment_Purpose]'     => $assignment_purpose['name'],
                          	'[$Assets_Number]'       	=> $get_case_data1['assets_number'],
                          	'[$Bank_Name]'			=> ucwords($bank_Det['bank_name']),
          					'[$Manager_Name]'		=> ucwords($bank_Det['name']),
                          	'[$AMOUNT]'		=> $due_quotation_val,
                          	'[$QUOTATION]'		=> $quotation,
                          	'[$USER_NAME]' 	=> $ba_name,
                          	'[$PAYMENT_TERM]'=>$no_advance_pay_term_text,
            				'[$USER_Official_PHONE]' => $ba_mobile,
                          	'[$URL]'  		=> $click_here,
                          	'[$OFFICE_CONTACT]'=>OFFICE_CONTACT
                    	); 

						$msg = strtr($getEmail['body'], $vars);
						$sign = $msg;
						$sign .= $getEmail['content'];

						$vars1 = array(
                          '[$CASE_ID]'  	=> $vis_case_id,
                          '[$Customer_Name]'  	=> ucwords($get_case_data1['customer_name'])
                    	); 

						$sub = strtr($getEmail['subject'], $vars1);

						$get_leads_data = $this->db->select('*')->from('leads')->where(array('id' => $lead_id))->get()->row_array();
						$head_e_id = $get_case_data1['customer_email_id'];
						$head_e_id2 = $get_case_data1['co_persone_email'];
						
						// this code by mangal 02-06-2020 start
						 $vis_attachment = $this->create_attachment_pdf_by_mng($lead_id);
						 $attched_vis = $_SERVER["DOCUMENT_ROOT"].'/'.$vis_attachment;
						 $this->email->attach($attched_vis);
						// this code by mangal 02-06-2020 end
						
						$this->email->cc($bank_email.','.$ba_email.','.$this->backupEmail);
						$this->sendEmailWithAttachment($head_e_id, $sign, $sub, $getEmail['send_to'], $attched_file);
						if ($head_e_id != $head_e_id2) {
							$this->sendEmailWithAttachment($head_e_id2, $sign, $sub, $getEmail['send_to'], $attched_file);
						}
					}

				} else { // for as per bank fee
					if($no_advance_status == 2){ //approved
						$lead_set_status = 6;
						$arr = array('status' => 6 );
						$this->db->where(array('id' => $lead_id))->update('leads', $arr);
						$arr2= array('case_activity_status' => 6 );
						$this->db->where(array('lead_id' => $lead_id, 'status' => 1))->update('lead_component', $arr2);

						$get_cases = $this->db->select('*')->from('lead_component')->where(array('lead_id' => $lead_id))->get()->result_array();
						$click_here = base_url('admin/compL/approveLead?lead_id='.base64_encode($lead_id));

						$asset_attachment = $this->createPdf($lead_id);

						$attched_file = $_SERVER["DOCUMENT_ROOT"].'/'.$asset_attachment;

						$update_array = array('approval_link_expire' => 1);
						$this->db->where('id', $lead_id)->update('leads', $update_array);

						$cond_1 = array('definition' => '56', 'status' => '1' );
						$getEmail = $this->w_user->getEmail('notification_setting_master', $cond_1);

						$vars = array(
							'[$Customer_Name]'  	=> ucwords($get_case_data1['customer_name']),
							'[$CASE_ID]'  			=> $vis_case_id,
							'[$Coordinating_Person]'	=> ucwords($get_case_data1['co_persone_name']),
                          	'[$Relationship]'       	=> $get_case_data1['relation_with_owner'],
                          	'[$Assignment_Purpose]'     => $assignment_purpose['name'],
                          	'[$Assets_Number]'       	=> $get_case_data1['assets_number'],
                          	'[$Bank_Name]'			=> ucwords($bank_Det['bank_name']),
          					'[$Manager_Name]'		=> ucwords($bank_Det['name']),
                          	'[$AMOUNT]'		=> $quotation,
                          	'[$USER_NAME]' 	=> $ba_name,
            				'[$USER_Official_PHONE]' => $ba_mobile,
            				'[$PAYMENT_TERM]'=>$no_advance_pay_term_text,
                          	'[$URL]'  		=> $click_here,
                          	'[$OFFICE_CONTACT]'=>OFFICE_CONTACT
                    	); 

						$msg = strtr($getEmail['body'], $vars);
						$sign = $msg;
						$sign .= $getEmail['content'];

						$vars1 = array(
                          '[$CASE_ID]'  	=> $vis_case_id,
                          '[$Customer_Name]'  	=> ucwords($get_case_data1['customer_name'])
                    	); 

						$sub = strtr($getEmail['subject'], $vars1);

						$get_leads_data = $this->db->select('*')->from('leads')->where(array('id' => $lead_id))->get()->row_array();
						$head_e_id = $get_case_data1['customer_email_id'];
						$head_e_id2 = $get_case_data1['co_persone_email'];
						
						// this code by mangal 02-06-2020 start
						 $vis_attachment = $this->create_attachment_pdf_by_mng($lead_id);
						 $attched_vis = $_SERVER["DOCUMENT_ROOT"].'/'.$vis_attachment;
						 $this->email->attach($attched_vis);
						// this code by mangal 02-06-2020 end
							
						$this->email->cc($bank_email.','.$ba_email.','.$this->backupEmail);
						$this->sendEmailWithAttachment($head_e_id, $sign, $sub, $getEmail['send_to'], $attched_file);
						if ($head_e_id != $head_e_id2) {
							$this->sendEmailWithAttachment($head_e_id2, $sign, $sub, $getEmail['send_to'], $attched_file);
						}
					} else {
						$click_here = site_url('admin/compL/setTransaction?lead_id='.base64_encode($lead_id));

						$asset_attachment = $this->createPdf($lead_id);

						$attched_file = $_SERVER["DOCUMENT_ROOT"].'/'.$asset_attachment;

						$cond_1 = array('definition' => '57', 'status' => '1' );
						$getEmail = $this->w_user->getEmail('notification_setting_master', $cond_1);

						$vars = array(
							'[$Customer_Name]'  	=> ucwords($get_case_data1['customer_name']),
							'[$CASE_ID]'  			=> $vis_case_id,
							'[$Coordinating_Person]'	=> ucwords($get_case_data1['co_persone_name']),
                          	'[$Relationship]'       	=> $get_case_data1['relation_with_owner'],
                          	'[$Assignment_Purpose]'     => $assignment_purpose['name'],
                          	'[$Assets_Number]'       	=> $get_case_data1['assets_number'],
                          	'[$Bank_Name]'			=> ucwords($bank_Det['bank_name']),
          					'[$Manager_Name]'		=> ucwords($bank_Det['name']),
                          	'[$AMOUNT]'		=> $due_quotation_val,
                          	'[$QUOTATION]'		=> $quotation,
                          	'[$USER_NAME]' 	=> $ba_name,
            				'[$USER_Official_PHONE]' => $ba_mobile,
            				'[$PAYMENT_TERM]'=>$no_advance_pay_term_text,
                          	'[$URL]'  		=> $click_here,
                          	'[$OFFICE_CONTACT]'=>OFFICE_CONTACT
                    	); 

						$msg = strtr($getEmail['body'], $vars);
						$sign = $msg;
						$sign .= $getEmail['content'];

						$vars1 = array(
                          '[$CASE_ID]'  	=> $vis_case_id,
                          '[$Customer_Name]'  	=> ucwords($get_case_data1['customer_name'])
                    	); 

						$sub = strtr($getEmail['subject'], $vars1);

						$get_leads_data = $this->db->select('*')->from('leads')->where(array('id' => $lead_id))->get()->row_array();
						$head_e_id = $get_case_data1['customer_email_id'];
						$head_e_id2 = $get_case_data1['co_persone_email'];
						
						// this code by mangal 02-06-2020 start
						 $vis_attachment = $this->create_attachment_pdf_by_mng($lead_id);
						 $attched_vis = $_SERVER["DOCUMENT_ROOT"].'/'.$vis_attachment;
						 $this->email->attach($attched_vis);
						// this code by mangal 02-06-2020 end
						
						$this->email->cc($bank_email.','.$ba_email.','.$this->backupEmail);
						$this->sendEmailWithAttachment($head_e_id, $sign, $sub, $getEmail['send_to'], $attched_file);
						if ($head_e_id != $head_e_id2) {
							$this->sendEmailWithAttachment($head_e_id2, $sign, $sub, $getEmail['send_to'], $attched_file);
						}
					}
				}

				$update_arr = array('status' => $lead_set_status);
				$this->db->where('id', $lead_id)->update('leads', $update_arr);



				if(!empty($no_advance_pay_term)){
					$fee_structure_text = 'and Payment Terms: ';
					if($no_advance_pay_term == 1){
						$fee_structure_text .= 'After Survey';
						$payment_term = 'After Survey';
					} else if($no_advance_pay_term == 1){
						$fee_structure_text .= 'After Submission of Draft Report';
						$payment_term = 'After Submission of Draft Report';
					} else {
						$fee_structure_text .= 'After Submission of Final Report within 7 days';
						$payment_term = 'After Submission of Final Report within 7 days';
					}

					$fee_structure_text .= ' and Quotation for the purpose of: ';
					if($no_advance_quo_purpose == 1){
						$fee_structure_text .= 'Notify only.';
					} else {
						$fee_structure_text .= 'Ask for the Quotation Approval.';
					}

				} else {
					$fee_structure_text = '.';
				}

				

				if($no_advance_status == 2){
					$quotation_remark1 = 'No Advance Quotation has been approved by DGM with Comment: '.$comment.' '.$fee_structure_text;
				} else if($no_advance_status == 3) {
					$quotation_remark1 = 'No Advance Quotation has been rejected by DGM with Comment: '.$comment.' '.$fee_structure_text;
				}


				/* set log history */
				$log_arr = array(
									'lead_id'			=> $lead_id,
									'quotation_amount' 	=> $quotation,
									'amount' 			=> $due_quotation_val,
									//'employee_id'		=> $user_data['userId'],
									'case_status'		=> 7,
									'reasion_id'		=> '',
									'created_at'		=> date('Y-m-d H:i:s'),
									'remark'			=> $quotation_remark1,
									'status'			=> 1
								);
				$this->db->insert('lead_history', $log_arr);
				if($quotation_type == 1){ // for custom quotation
					// clear DGM VIS Notification
					$userId = $this->session->userdata('adminData')['userId'];
					$n_arr = array(	
	            					'status' 		=> '2',
	            					'updated_at'	=> date('Y-m-d H:i:s')
	            				);
					$n_cond = array(
				    				'status'	=> 1,
				    				'emp_id'	=> $userId,
				    				'res_id'	=> $lead_id,
				    				'notification_type'	=> 8
				    				);
        			$this->db->where($n_cond)->update('user_notification', $n_arr);
				} else { // for as per bank fee
					// clear DGM VIS Notification
					$userId = $this->session->userdata('adminData')['userId'];
					$n_arr = array(	
	            					'status' 		=> '2',
	            					'updated_at'	=> date('Y-m-d H:i:s')
	            				);
					$n_cond = array(
				    				'status'	=> 1,
				    				// 'emp_id'	=> $userId,
				    				'res_id'	=> $lead_id,
				    				'notification_type'	=> 8
				    				);
					$this->db->where($n_cond)->update('user_notification', $n_arr);
		        }
	            $message = '<div class="success_msg" id="secc_msg"><div class="col-xs-12 set_div_msg">Your quotation value has been successfully submitted. Please check your mail for accept quotation. <span class="set_cross""><i class="fa fa-times" aria-hidden="true"></i></span></div></div>';
    			$this->session->set_flashdata('message', $message);   
        		redirect(site_url('admin/approvalM/noAdvanceLead'), 'refresh');
			} else {

				$get_leads_data = $this->db->select('*')->from('leads')->where(array('id' => $lead_id))->get()->row_array();
				$head_e_id = $get_case_data1['customer_email_id'];
				$head_e_id2 = $get_case_data1['co_persone_email'];

				$quotation_type 	= $this->input->post('quotation_type');

				$no_advance_reason 	= trim($this->input->post('no_advance_reason'));
				$bank_manager_name 	= trim($this->input->post('bank_manager_name'));
				$bank_manager_mobile 		= $this->input->post('bank_manager_mobile');
				$bank_manager_email 		= $this->input->post('bank_manager_email');
				$reason_text 				= $this->input->post('reason_text');

				$no_advance_quo_purpose 	= $this->input->post('no_advance_quo_purpose');

				$arr = array(
								'set_quotation' 		=> $quotation, 
								'quotation_percentage' 	=> $percent, 
								'due_quotation' 		=> $due_quotation_val, 
								'no_advance_reason'		=> $no_advance_reason,
								'bank_manager_name'		=> $bank_manager_name,
								'bank_manager_mobile'		=> $bank_manager_mobile,
								'bank_manager_email'		=> $bank_manager_email,
								'reason_text'				=> $reason_text,
								'no_advance_quo_purpose'	=> $no_advance_quo_purpose,
								'remarks' 				=> $quotation_remark, 
								'updated_at' 			=> date('Y-m-d H:i:s'),
								'discount' 				=> $discount
							); 
				//by mangal 29-06-2020
    			$time_number=$this->input->post('time_number');
    			if(!empty($time_number))
    			{
    			    $arr['time_number']=$time_number;
    			}
    			$time_duration=$this->input->post('time_duration');
    			if(!empty($time_duration))
    			{
    			    $arr['time_duration']=$time_duration;
    			}
    			$pocket_expenses=$this->input->post('pocket_expenses');
    			if(!empty($pocket_expenses))
    			{
    			    $arr['pocket_expenses']=$pocket_expenses;
    			}
    			$fixed_value=$this->input->post('fixed_value');
    			if(!empty($fixed_value))
    			{
    			    $arr['fixed_value']=$fixed_value;
    			}
    			$no_advance_pay_term_mng=$this->input->post('no_advance_pay_term_mng');
    			if(!empty($no_advance_pay_term_mng))
    			{
    			    $arr['no_advance_pay_term']=$no_advance_pay_term_mng;
    			}

				$no_advance_pay_term=$no_advance_pay_term_mng;
				//end by mangal 29-06-2020
				//by mangal 13-07-2020
				$termsQuotation=$this->input->post('termsQuotation');
				if(!empty($termsQuotation))
				{
				    $arr['terms_quotation']=implode(',',$termsQuotation);
				}
				$termsService=$this->input->post('termsService');
				if(!empty($termsService))
				{
				   $arr['terms_service']=implode(',',$termsService); 
				}
				
				//end by mangal 13-07-2020
				
				// new_17-11-2020 
				$extra_terms_quotation_id=$this->input->post('extra_terms_quotation_id');
				$extra_terms_quotation=$this->input->post('extra_terms_quotation');
				if(!empty($extra_terms_quotation))
				{
					foreach($extra_terms_quotation as $key=> $terms_quotation)
					{
					    if(!empty($terms_quotation))
					    {
						    $term_data['contantes']=$terms_quotation;
						    $term_data['extra_terms_type']=1;
						    $term_data['lead_id']=$lead_id;
						   if(!empty($extra_terms_quotation_id[$key]))
						   {
						       $this->db->update('tbl_quickquotation_extra_term_qs',$term_data,array('id'=>$extra_terms_quotation_id[$key]));
						   }else{
						       $this->db->insert('tbl_quickquotation_extra_term_qs',$term_data);
						   }
					    }
					    
					}
				}
				
				$extra_terms_service_id=$this->input->post('extra_terms_service_id');
				$extra_terms_service=$this->input->post('extra_terms_service');
				if(!empty($extra_terms_service))
				{
					foreach($extra_terms_service as $key=> $terms_service)
					{  
					    if(!empty($terms_service))
					    {
						    $service_data['contantes']=$terms_service;
						    $service_data['extra_terms_type']=2;
						    $service_data['lead_id']=$lead_id;
						    if(!empty($extra_terms_service_id[$key]))
						    {
						       $this->db->update('tbl_quickquotation_extra_term_qs',$service_data,array('id'=>$extra_terms_service_id[$key]));  
						    }else{
						       $this->db->insert('tbl_quickquotation_extra_term_qs',$service_data); 
						    }
					    }
					    
					}
				}
				// new_17-11-2020
				
							
				$condi = array('id' => $lead_id);
				$save_quotation = $this->businessM->saveQuotation($condi, $arr);
				$quotation_remark1 = 'Quotation has been giving by the Admin on the Enquiry';
				$assignment_purpose = $this->db->select('*')->from('assignment_purpose')->where(array('id'=>$get_case_data1['purpose_of_assignment']))->get()->row_array();
				$bank_id = $get_case_data1['added_by'];
				$bank_Det = $this->db->select('u.*, bd.bank_name')->from('users as u')->join('user_details as ud', 'ud.user_id = u.id')->join('bank_branch_master as bm', 'ud.bank_id = bm.id')->join('bank_details as bd', 'bm.bank_id = bd.id')->where(array('u.id' => $bank_id ))->get()->row_array(); 
				$bank_email = $bank_Det['email'];
				$ba_id = $get_case_data1['assign_lead'];
                $ba_Det = $this->db->select('*') ->from('users')->where(array('id'=>$ba_id))->get()->row_array();
                $ba_mobile = $this->w_user->getBAMobile($ba_id);
                $ba_name = ucwords($ba_Det['name']);
                $ba_email = $ba_Det['email'];
                $ba_official_mobile = $this->w_user->getBAOfficialMobile($ba_id);

                if(empty($ba_name)){
                	$ba_name = 'Business Coordinator';
                }

                if(empty($ba_mobile)){
                	$ba_mobile = '0120-4110117/432647';
                }

                if(empty($ba_email)){
                	$ba_official_email = 'admin@visindia.org';
                } else{
                	$ba_official_email = $ba_email;
                }

                $vis_case_id = $get_case_data1['lead_ids'];
                //echo 'hello 1';
                //echo $no_advance_pay_term;die;
                if(!empty($no_advance_pay_term)) 
                { 
                	$payment_terms=array('1'=>'before site inspection','2'=>'after site inspection','3'=>'after submission of draft report','4'=>'within 7 days after submission of final report','5'=>'before delivery of the report');
                	$no_advance_pay_term_text=$payment_terms[$no_advance_pay_term];
	                // if($no_advance_pay_term == 1) {
	                // 	$no_advance_pay_term_text = 'After Survey';
	                // } else if($no_advance_pay_term == 2) {
	                // 	$no_advance_pay_term_text = 'After Submission of Draft Report';
	                // } else {
	                // 	$no_advance_pay_term_text = 'After Submission of Final Report Within 7 Days';
	                // }
	            }
					
					if($due_quotation_val != '0.00'){
				$lead_status = 28;
				}else{
				$lead_status = 4;
				}


				$lead_status = 4;
				if(($pay_payable_by == 1 && $percent == 6)) 
				{
					$success_message = 'The Quotation has been submitted to DGM - VIS for Approval since no advance required was selected.';

					$arrr = array('assign_approval_for_no_advance'=> 1);
					$condii = array('id' => $lead_id);
					//	print_r($arr);die;
					$save_quotation = $this->businessM->saveQuotation($condii, $arrr);
					//$lead_status = 3;
					$lead_status = 29;
					//$dgm_emp = $this->businessM->getUserByDes(69); 
					$dgm_emp=$this->db->query("SELECT * FROM `users` INNER join employee_details on `users`.id = `employee_details`.user_id JOIN `user_types` ON `user_types`.id= `employee_details`.designation  WHERE `user_types`.user_function_id = 10 OR `user_types`.user_function_id = 9 and `users`.resource_status = 2")->result_array();

					foreach ($dgm_emp as $key => $value) 
					{
						// send dashboard notification to DGM VIS employees
						//$n_url = base_url().'admin/approvalM/noAdvanceLead';
						$n_url= base_url('admin/webU/editCase/'.base64_encode($lead_id).'?pid='.base64_encode(5));
				        $text = '<a class="user_notification" href="'.$n_url.'"><b>VIS0'.$lead_id.'</b>,is waiting for your approval.</a>';

				        $n_arr = array(	'emp_id' 		=> $value['user_id'],
				            			'res_id' 		=> $lead_id,  // lead id
				            			'notification_type'	=> 8,  // for approval for no advance
		            					'text' 			=> $text,
		            					'status' 		=> '1',
		            					'created_at' 	=> date('Y-m-d H:i:s'),
		            					'updated_at'	=> date('Y-m-d H:i:s')
				            		);

				        $this->db->insert('user_notification', $n_arr);

				        // send email

				        $dgm_emp_detail = $this->db->select('*')
						    ->from('users')
						    ->where(array('id'=>$value['user_id']))
						    ->get()->row_array();

						$ba_id = $get_view_data['assign_lead'];

						$ba_Det = $this->db->select('*')
							    ->from('users')
							    ->where(array('id'=>$ba_id))
							    ->get()->row_array();
		                
		                $ba_name = ucwords($ba_Det['name']);
		                $ba_email = ucwords($ba_Det['email']);

						$cond_1 = array('definition' => '55', 'status' => '1' );
						$getEmail = $this->w_user->getEmail('notification_setting_master', $cond_1);

						$vars1 = array(
                           '[$CASE_ID]'  	=> $get_view_data['lead_ids']
                        ); 

	        			$sub = strtr($getEmail['subject'], $vars1);

	        			if($no_advance_reason == 1){
	        				$reason = 'Reliable Old Customer';
	        			} else if($no_advance_reason == 2){
	        				$reason = 'Bank Assurance - Bank Manager Name: '.$bank_manager_name.', Bank Manager Mobile Number: '.$bank_manager_mobile.' & Bank Manager Email ID: '.$bank_manager_email;
	        			} else {
	        				$reason = $reason_text;
	        			}

	        			$vars = array(
                              	'[$DGM_VIS_NAME]'  	=> ucwords($dgm_emp_detail['name']),
                              	'[$CASE_ID]'		=> $get_view_data['lead_ids'],
	                            '[$Business_Associates_Name]' 	=> $ba_name,
	                            '[$URL]' => $n_url,
	                            '[$Reason]' 	=> $reason,
	                            '[$OFFICE_CONTACT]'=>OFFICE_CONTACT
                        ); 

		        		$msg = strtr($getEmail['body'], $vars);

		        		$sign = $msg;
						$sign .= $getEmail['content'];

						$this->email->cc($ba_email.','.$this->backupEmail);
				
						$this->sendEmail($dgm_emp_detail['email'], $sign, $sub, $getEmail['send_to']);
						///
						//echo 'asdasd';die;
					}
				} else if($account_type == 1 && $pay_payable_by == 2 && $due_quotation_val == '0.00'){
					if($quotation_type == 1){ // custom quotation
						if($no_advance_quo_purpose == 1){ // notify only
							$lead_status = 6;
							$arr = array('status' => 6 );
							$this->db->where(array('id' => $lead_id))->update('leads', $arr);
							$arr2= array('case_activity_status' => 6 );
							$this->db->where(array('lead_id' => $lead_id, 'status' => 1))->update('lead_component', $arr2);

							$get_cases = $this->db->select('*')->from('lead_component')->where(array('lead_id' => $lead_id))->get()->result_array();

							$asset_attachment = $this->createPdf($lead_id);

							$attched_file = $_SERVER["DOCUMENT_ROOT"].'/'.$asset_attachment;

							$cond_2 = array('definition' => '59', 'status' => '1' );
							$getEmail2 = $this->w_user->getEmail('notification_setting_master', $cond_2);

							$vars2 = array(
                              '[$CASE_ID]'  	=> $vis_case_id,
                              '[$Customer_Name]'  => ucwords($get_case_data1['customer_name'])
                        	); 

							$sub2 = strtr($getEmail2['subject'], $vars2);
							$click_here = base_url('admin/compL/approveLead?lead_id='.base64_encode($lead_id));

							$vars2 = array(
								'[$Customer_Name]'  => ucwords($get_case_data1['customer_name']),
								'[$CASE_ID]'  			=> $vis_case_id,
								'[$Coordinating_Person]'	=> ucwords($get_case_data1['co_persone_name']),
	                          	'[$Relationship]'       	=> $get_case_data1['relation_with_owner'],
                              	'[$Assignment_Purpose]'     => $assignment_purpose['name'],
                              	'[$Assets_Number]'       	=> $get_case_data1['assets_number'],
                              	'[$Bank_Name]'			=> ucwords($bank_Det['bank_name']),
              					'[$Manager_Name]'		=> ucwords($bank_Det['name']),
                              	'[$AMOUNT]'		=> $quotation,
                              	'[$CONDITION]'		=> $no_advance_pay_term_text,
                              	'[$PAYMENT_TERM]'=>$no_advance_pay_term_text,
                              	'[$USER_NAME]' 	=> $ba_name,
                				'[$USER_PHONE]' => $ba_mobile,
                              	'[$URL]'  		=> $click_here,
                              	'[$OFFICE_CONTACT]'=>OFFICE_CONTACT
                        	); 

							$msg2 = strtr($getEmail2['body'], $vars2);
							$sign2 = $msg2;
							$sign2 .= $getEmail2['content'];
							
                            // this code by mangal 02-06-2020 start
							 $vis_attachment = $this->create_attachment_pdf_by_mng($lead_id);
							 $attched_vis = $_SERVER["DOCUMENT_ROOT"].'/'.$vis_attachment;
							 $this->email->attach($attched_vis);
							// this code by mangal 02-06-2020 end
							
							$this->email->cc($bank_email.','.$ba_email.','.$this->backupEmail);
							$this->sendEmailWithAttachment($head_e_id, $sign2, $sub2, $getEmail2['send_to'], $attched_file);
							if ($head_e_id != $head_e_id2) {
								$this->sendEmailWithAttachment($head_e_id2, $sign2, $sub2, $getEmail2['send_to'], $attched_file);
							}
							
						} else { // Ask for Quotation Approval from Customer
							$update_array = array('approval_link_expire' => 1);
							$this->db->where('id', $lead_id)->update('leads', $update_array);

							$click_here = base_url('admin/compL/approveLead?lead_id='.base64_encode($lead_id));

							$asset_attachment = $this->createPdf($lead_id);
							$attched_file = $_SERVER["DOCUMENT_ROOT"].'/'.$asset_attachment;

							$cond_2 = array('definition' => '60', 'status' => '1' );
							$getEmail2 = $this->w_user->getEmail('notification_setting_master', $cond_2);
							$click_here = base_url('admin/compL/approveLead?lead_id='.base64_encode($lead_id));

							$vars2 = array(
                              '[$CASE_ID]'  	=> $vis_case_id,
                              '[$Customer_Name]'  	=> ucwords($get_case_data1['customer_name'])
                        	); 

							$sub2 = strtr($getEmail2['subject'], $vars2);

							$vars2 = array(
								'[$Customer_Name]'  => ucwords($get_case_data1['customer_name']),
								'[$CASE_ID]'  		=> $vis_case_id,
								'[$Coordinating_Person]'=> ucwords($get_case_data1['co_persone_name']),
	                          	'[$Relationship]'       	=> $get_case_data1['relation_with_owner'],
                              	'[$Assignment_Purpose]'     => $assignment_purpose['name'],
                              	'[$Assets_Number]'       	=> $get_case_data1['assets_number'],
                              	'[$Bank_Name]'			=> ucwords($bank_Det['bank_name']),
              					'[$Manager_Name]'		=> ucwords($bank_Det['name']),
                              	'[$AMOUNT]'		=> $quotation,
                              	'[$CONDITION]'		=> $no_advance_pay_term_text,
                              	'[$PAYMENT_TERM]' =>$no_advance_pay_term_text,
                              	'[$USER_NAME]' 	=> $ba_name,
                				'[$USER_PHONE]' => $ba_mobile,
                              	'[$URL]'  		=> $click_here,
                              	'[$OFFICE_CONTACT]'=>OFFICE_CONTACT
                        	); 

							//print_r($vars2);die;
							$msg2 = strtr($getEmail2['body'], $vars2);
							$sign2 = $msg2;
							$sign2 .= $getEmail2['content'];
                            // this code by mangal 02-06-2020 start
							 $vis_attachment = $this->create_attachment_pdf_by_mng($lead_id);
							 $attched_vis = $_SERVER["DOCUMENT_ROOT"].'/'.$vis_attachment;
							 $this->email->attach($attched_vis);
							// this code by mangal 02-06-2020 end
							$this->email->cc($bank_email.','.$ba_email.','.$this->backupEmail);
							$this->sendEmailWithAttachment($head_e_id, $sign2, $sub2, $getEmail2['send_to'], $attched_file);
							if ($head_e_id != $head_e_id2) {
								$this->sendEmailWithAttachment($head_e_id2, $sign2, $sub2, $getEmail2['send_to'], $attched_file);
							}
						}

					} else { // as per bank fee
						$lead_status = 6;
						$arr = array('status' => 6 );
						$this->db->where(array('id' => $lead_id))->update('leads', $arr);
						$arr2= array('case_activity_status' => 6 );
						$this->db->where(array('lead_id' => $lead_id, 'status' => 1))->update('lead_component', $arr2);

						$get_cases = $this->db->select('*')->from('lead_component')->where(array('lead_id' => $lead_id))->get()->result_array();
						
					}
				}


				/* set log history */
				$log_arr = array(
									'lead_id'			=> $lead_id,
									'quotation_amount' 	=> $quotation,
									'amount' 			=> $due_quotation_val,
									//'employee_id'		=> $user_data['userId'],
									'case_status'		=> 7,
									'reasion_id'		=> '',
									'created_at'		=> date('Y-m-d H:i:s'),
									'remark'			=> $quotation_remark1,
									'status'			=> 1
								);
				$this->db->insert('lead_history', $log_arr);
		
				$reporting_services = $get_case_data1['reporting_services'];
				$ass_purpose 		= $get_case_data1['purpose_of_assignment'];
				$rep_ser 			= $this->businessM->repSer($reporting_services, $ass_purpose);
				$reporting_ser 		= $rep_ser['rep_ser']['name'];
				$auto_popu 			= $rep_ser['ass_pur']['name'];
				//$assets_data = $this->w_user->getAssetsData($case_ids);
				$arr1 = array();
				$arr2 = array();
				$arr3 = array();
				$update_arr = array('status' => $lead_status);
				$this->db->where('id', $lead_id)->update('leads', $update_arr);
				$cat_assets = '';
				$type_assets = '';
				$sit_address = '';
				
				//$confirmUrl = site_url('admin/compL/setDocuments?case_id='.base64_encode($case_ids));
				$confirmUrl = site_url('admin/compL/setTransaction?lead_id='.base64_encode($lead_id));
				$mobile_no = $get_case_data1['customer_contact_n']; // customer mobile number
				$cuso_name = $get_case_data1['customer_name']; 		// customer name
				$short_code = $this->get_tiny_url($confirmUrl);     // get short url
				// clear BA Notification
				$userId = $this->session->userdata('adminData')['userId'];
				if($userId == 1){
					$n_arr = array(	'status'=> '2','updated_at'	=> date('Y-m-d H:i:s'));
					$n_cond = array('status'=> 1,'res_id'=> $lead_id,'notification_type'=> 6);
				} else {
					$n_arr = array(	'status'=> '2','updated_at'	=> date('Y-m-d H:i:s'));
					$n_cond = array('status'=> 1,'emp_id'=> $userId,'res_id'=> $lead_id,'notification_type'	=> 6);
				}
	        	$this->db->where($n_cond)->update('user_notification', $n_arr);
				// sms message 
				$cond_1 = array('definition' => '19', 'status' => '1' );
				$getSMS = $this->w_user->getEmail('notification_setting_master', $cond_1);
				$vars_sms = array(
                          '[$CUSTOMER_NAME]'  	=> ucwords($cuso_name),
                          '[$URL]'				=> $short_code
                    );
				$sms_smg = strtr($getSMS['body'], $vars_sms);
				// $sms_smg = 'Hi '.ucwords($cuso_name).' Kindly follow this link '.$short_code.', to approve the quotation.';
				$this->SendOtpSMS($mobile_no, $sms_smg);			// send sms to the customer
				// $sms_smg = 'Hi '.ucwords($cuso_name).' Kindly follow this link '.$short_code.', to approve the quotation.';
				// $this->SendOtpSMS($mobile_no, $sms_smg);			// send sms to the customer
            	if($due_quotation_val != '0.00' && $quotation_type == 1){ 
            		$click_here = site_url('admin/compL/setTransaction?lead_id='.base64_encode($lead_id));
            			$asset_attachment = $this->createPdf($lead_id);
						$attched_file = $_SERVER["DOCUMENT_ROOT"].'/'.$asset_attachment;
						$cond_1 = array('definition' => '58', 'status' => '1' );
						$getEmail = $this->w_user->getEmail('notification_setting_master', $cond_1);
						$vars = array(
							'[$Customer_Name]'  	=> ucwords($get_case_data1['customer_name']),
							'[$CASE_ID]'  			=> $vis_case_id,
							'[$Coordinating_Person]'	=> ucwords($get_case_data1['co_persone_name']),
                          	'[$Relationship]'       	=> $get_case_data1['relation_with_owner'],
                          	'[$Assignment_Purpose]'     => $assignment_purpose['name'],
                          	'[$Assets_Number]'       	=> $get_case_data1['assets_number'],
                          	'[$Bank_Name]'			=> ucwords($bank_Det['bank_name']),
          					'[$Manager_Name]'		=> ucwords($bank_Det['name']),
                          	'[$AMOUNT]'		=> $due_quotation_val,
                          	'[$QUOTATION]'		=> $quotation,
                          	'[$USER_NAME]' 	=> $ba_name,
                          	'[$PAYMENT_TERM]'=>$no_advance_pay_term_text,
            				'[$USER_Official_PHONE]' => $ba_mobile,
                          	'[$URL]'  		=> $click_here,
                          	'[$OFFICE_CONTACT]'=>OFFICE_CONTACT
                    	); 

						$msg = strtr($getEmail['body'], $vars);
						$sign = $msg;
						$sign .= $getEmail['content'];

						$vars1 = array(
                          '[$CASE_ID]'  	=> $vis_case_id,
                          '[$Customer_Name]'  	=> ucwords($get_case_data1['customer_name'])
                    	); 

						$sub = strtr($getEmail['subject'], $vars1);

						$get_leads_data = $this->db->select('*')->from('leads')->where(array('id' => $lead_id))->get()->row_array();
						$head_e_id = $get_case_data1['customer_email_id'];
						$head_e_id2 = $get_case_data1['co_persone_email'];
						
						// this code by mangal 02-06-2020 start
						 $vis_attachment = $this->create_attachment_pdf_by_mng($lead_id);
						 $attched_vis = $_SERVER["DOCUMENT_ROOT"].'/'.$vis_attachment;
						 $this->email->attach($attched_vis);
						// this code by mangal 02-06-2020 end
						
						$this->email->cc($bank_email.','.$ba_email.','.$this->backupEmail);
						$this->sendEmailWithAttachment($head_e_id, $sign, $sub, $getEmail['send_to'], $attched_file);
						if ($head_e_id != $head_e_id2) {
							$this->sendEmailWithAttachment($head_e_id2, $sign, $sub, $getEmail['send_to'], $attched_file);
						}

            	} else if($due_quotation_val != '0.00' && $quotation_type == 2){
            		$click_here = site_url('admin/compL/setTransaction?lead_id='.base64_encode($lead_id));

            		$asset_attachment = $this->createPdf($lead_id);

					$attched_file = $_SERVER["DOCUMENT_ROOT"].'/'.$asset_attachment;

						$cond_1 = array('definition' => '57', 'status' => '1' );
						$getEmail = $this->w_user->getEmail('notification_setting_master', $cond_1);

						$vars = array(
							'[$Customer_Name]'  	=> ucwords($get_case_data1['customer_name']),
							'[$CASE_ID]'  			=> $vis_case_id,
							'[$Coordinating_Person]'	=> ucwords($get_case_data1['co_persone_name']),
                          	'[$Relationship]'       	=> $get_case_data1['relation_with_owner'],
                          	'[$Assignment_Purpose]'     => $assignment_purpose['name'],
                          	'[$Assets_Number]'       	=> $get_case_data1['assets_number'],
                          	'[$Bank_Name]'			=> ucwords($bank_Det['bank_name']),
          					'[$Manager_Name]'		=> ucwords($bank_Det['name']),
                          	'[$AMOUNT]'		=> $due_quotation_val,
                          	'[$QUOTATION]'		=> $quotation,
                          	'[$USER_NAME]' 	=> $ba_name,
            				'[$USER_Official_PHONE]' => $ba_mobile,
            				'[$PAYMENT_TERM]'=>$no_advance_pay_term_text,
                          	'[$URL]'  		=> $click_here,
                          	'[$OFFICE_CONTACT]'=>OFFICE_CONTACT
                    	); 

						$msg = strtr($getEmail['body'], $vars);
						$sign = $msg;
						$sign .= $getEmail['content'];

						$vars1 = array(
                          '[$CASE_ID]'  	=> $vis_case_id,
                          '[$Customer_Name]'  	=> ucwords($get_case_data1['customer_name'])
                    	); 

						$sub = strtr($getEmail['subject'], $vars1);

						$get_leads_data = $this->db->select('*')->from('leads')->where(array('id' => $lead_id))->get()->row_array();
						$head_e_id = $get_case_data1['customer_email_id'];
						$head_e_id2 = $get_case_data1['co_persone_email'];
						
						// this code by mangal 02-06-2020 start
						 $vis_attachment = $this->create_attachment_pdf_by_mng($lead_id);
						 $attched_vis = $_SERVER["DOCUMENT_ROOT"].'/'.$vis_attachment;
						 $this->email->attach($attched_vis);
						// this code by mangal 02-06-2020 end
						
						$this->email->cc($bank_email.','.$ba_email.','.$this->backupEmail);
						$this->sendEmailWithAttachment($head_e_id, $sign, $sub, $getEmail['send_to'], $attched_file);
						if ($head_e_id != $head_e_id2) {
							$this->sendEmailWithAttachment($head_e_id2, $sign, $sub, $getEmail['send_to'], $attched_file);
						}
            	}
                
                $message = '<div class="success_msg" id="secc_msg"><div class="col-xs-12 set_div_msg">'.$success_message.' <span class="set_cross""><i class="fa fa-times" aria-hidden="true"></i></span></div></div>';
    			$this->session->set_flashdata('message', $message);   
    			$rURL = base_url().'admin/businessM/setQuotationByBA/'.base64_encode($lead_id).'?pid='.base64_encode($page_id);
        		redirect($rURL, 'refresh');
        	}
		} else {
			$message = '<div class="unsuccess_msg" id="unsecc_msg"><div class="col-xs-12 set_div_msg">Quotation value could not be blank. <span class="set_cross""><i class="fa fa-times" aria-hidden="true"></i></span></div></div>';
			$this->session->set_flashdata('message', $message);   
    		redirect(site_url('admin/businessM/setQuotationByBA/'.base64_encode($lead_id).'?pid='.base64_encode($page_id)), 'refresh');
		}
	} else {
		if (!empty($lead_com_id)) {
			$data['title'] = 'VIS | Manage Case | Set Quotation Details';
			$data['page_title'] = 'View Case Assets';
			$data['view_list'] = 'Manage Case';
			$data['change_case_to'] = 'Set Quotation Detail Form';
		} else {
			$data['title'] = 'VIS | Manage Lead | Set Quotation Details';
			$data['page_title'] = 'View Lead Assets';
			$data['view_list'] = 'Manage Lead';
			$data['change_case_to'] = 'Set Quotation Detail Form';
		}
		$titles = 'VIS | Manage Leads | Set Quotation Details';
		if (!empty($lead_com_id)) {
			$titles = 'VIS | Manage Cases | Set Quotation Details';
		}
		$data['title'] = $titles;

		$get_percent = $this->db->select('*')->from('payment_value')->where('status', '1')->get()->result_array();
		$per_id = $get_case_data1['quotation_percentage'];
		$per_html = '';
		$data['per_id_status'] = '0';
		if (!empty($per_id)) { 
			$get_percent_name = $this->db->select('*')
									->from('payment_value')
									->where(array('status' => '1', 'id' => $per_id))
									->get()->row_array();
			$per_html .= '<optgroup label="Selected by '.$sel_by.'">';
			$per_html .= '<option value="'.$per_id.'" selected>'.$get_percent_name['advance_percentage'].'</option>';
			$per_html .= '</optgroup>';
			$data['per_id_status'] = '1';
		}

		$per_html .= '<optgroup label="Chooose any one">';
			$per_html .= '<option value="0">Select percentage</option>';
			foreach ($get_percent as $key => $value) {
				$per_html .= '<option value="'.$value['id'].'">'.$value['advance_percentage'].'</option>';
			}
		$per_html .= '</optgroup>'; 

		if (!empty($lead_com_id)) {
			$data['data'] = $this->db->select('*')
							     ->from('lead_component')
							     ->where('id', $lead_com_id)
							     ->get()->row_array();
		} else {
			$data['data']['case_activity_status'] = '';
		}
		
		$data['per_html'] = $per_html;
		$data['quotation'] = $get_all_quo;
		$data['lead_data'] = $get_case_data1;
		$data['case_id'] = $lead_id;
		//start by mangal singh yadav date 13-07-2020 
		$data['termsService']=$this->db->order_by('order_by','asc')->get_where('tbl_quotation_terms',array('quotation_term_type'=>2,'status'=>1))->result();
		$data['termsQuotation']=$this->db->order_by('order_by','asc')->get_where('tbl_quotation_terms',array('quotation_term_type'=>1,'status'=>1))->result();
		//end by mangal singh yadav date 13-07-2020 
		// new_17_11_2020
		$data['quickquotation_ex_term_qut']=$this->db->order_by('id','asc')->get_where('tbl_quickquotation_extra_term_qs',array('extra_terms_type'=>1,'lead_id'=>$lead_id))->result();
		$data['quickquotation_ex_term_ser']=$this->db->order_by('id','asc')->get_where('tbl_quickquotation_extra_term_qs',array('extra_terms_type'=>2,'lead_id'=>$lead_id))->result();
		// new_17_11_2020
		
    	$this->load->view('admin/include/header', $data);
    	$this->load->view('admin/business/set_quotation', $data);
    	$this->load->view('admin/include/footer');
	}
}
?>