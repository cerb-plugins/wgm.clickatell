<?php
if(class_exists('Extension_PageMenuItem')):
class WgmClickatell_SetupPluginsMenuItem extends Extension_PageMenuItem {
	const POINT = 'wgmclickatell.setup.menu.plugins.clickatell';
	
	function render() {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('extension', $this);
		$tpl->display('devblocks:wgm.clickatell::setup/menu_item.tpl');
	}
};
endif;

if(class_exists('Extension_PageSection')):
class WgmClickatell_SetupSection extends Extension_PageSection {
	const ID = 'wgmclickatell.setup.clickatell';
	
	function render() {
		$tpl = DevblocksPlatform::getTemplateService();
		$active_worker = CerberusApplication::getActiveWorker();
		
		if(!$active_worker->is_superuser)
			return;

		$visit = CerberusApplication::getVisit();
		$visit->set(ChConfigurationPage::ID, 'clickatell');
		
		$credentials = DevblocksPlatform::getPluginSetting('wgm.clickatell', 'credentials', '', true, true);
		$tpl->assign('credentials', $credentials);
		
		$tpl->display('devblocks:wgm.clickatell::setup/index.tpl');
	}
	
	function saveJsonAction() {
		$active_worker = CerberusApplication::getActiveWorker();
		
		if(!$active_worker->is_superuser)
			return;
		
		try {
			@$api_user = DevblocksPlatform::importGPC($_REQUEST['api_user'],'string','');
			@$api_pass = DevblocksPlatform::importGPC($_REQUEST['api_pass'],'string','');
			@$api_id = DevblocksPlatform::importGPC($_REQUEST['api_id'],'string','');
			
			if(empty($api_user) || empty($api_pass) || empty($api_id))
				throw new Exception("All API fields are required.");
			
			$credentials = [
				'id' => $api_id,
				'user' => $api_user,
				'pass' => $api_pass,
			];
			
			DevblocksPlatform::setPluginSetting('wgm.clickatell', 'credentials', $credentials, true, true);
			
			echo json_encode(array('status'=>true,'message'=>'Saved!'));
			return;
			
		} catch (Exception $e) {
			echo json_encode(array('status'=>false,'error'=>$e->getMessage()));
			return;
			
		}
	}
};
endif;

class WgmClickatell_API {
	static $_instance = null;
	private $_api_user = null;
	private $_api_pass = null;
	private $_api_id = null;
	
	private function __construct() {
		$credentials = DevblocksPlatform::getPluginSetting('wgm.clickatell', 'credentials', [], true, true);
		
		$this->_api_user = @$credentials['user'];
		$this->_api_pass = $credentials['pass'];
		$this->_api_id = $credentials['id'];
	}
	
	/**
	 * @return WgmClickatell_API
	 */
	static public function getInstance() {
		if(null == self::$_instance) {
			self::$_instance = new WgmClickatell_API();
		}

		return self::$_instance;
	}
	
	/**
	 * 
	 * @param string $rel_path
	 * @param string $method
	 * @param array $vars
	 * @return 
	 */
	public function sendmsg($phone, $text) {
		$phone = DevblocksPlatform::strAlphaNum($phone);
		
		$url = sprintf("https://api.clickatell.com/http/sendmsg?user=%s&password=%s&api_id=%s&to=%s&text=%s",
			urlencode($this->_api_user),
			urlencode($this->_api_pass),
			urlencode($this->_api_id),
			urlencode($phone),
			urlencode($text)
		);
		
		$ch = DevblocksPlatform::curlInit();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$out = DevblocksPlatform::curlExec($ch);
		curl_close($ch);
		
		$result = (0==strcasecmp("ID:",substr($out,0,3)));
		return $result;
	}
};

if(class_exists('Extension_DevblocksEventAction')):
class WgmClickatell_EventActionSendSms extends Extension_DevblocksEventAction {
	function render(Extension_DevblocksEvent $event, Model_TriggerEvent $trigger, $params=array(), $seq=null) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('params', $params);
		
		if(!is_null($seq))
			$tpl->assign('namePrefix', 'action'.$seq);
		
		$tpl->display('devblocks:wgm.clickatell::events/action_send_sms_clickatell.tpl');
	}
	
	function run($token, Model_TriggerEvent $trigger, $params, DevblocksDictionaryDelegate $dict) {
		$clickatell = WgmClickatell_API::getInstance();
		
		// Translate message tokens
		$tpl_builder = DevblocksPlatform::getTemplateBuilder();
		$content = $tpl_builder->build($params['content'], $dict);
		
		$result = $clickatell->sendmsg($params['phone'], $content);
	}
};
endif;
