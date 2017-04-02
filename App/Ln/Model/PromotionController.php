<?php

namespace Ln\Model;

use Ln\Controller\ComController;
class PromototionController extends ComController{
	
	//@×¢²áÏêÏ¸ÈÕÖ¾
	public function (){
		
		$data =M('Ln.mc_members','ims_') ->field('ims_mc_members.*')
			->join('qw_member m on ims_mc_members.soneid = m.uid','LEFT')
			->join('qw_auth_group_access on m.uid=qw_auth_group_access.uid','LEFT')
			->where('qw_auth_group_access.group_id =2')
			->select()->co;
		
		
	}
	
	
	
}