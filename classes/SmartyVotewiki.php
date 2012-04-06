<?php

require_once SMARTY_DIR . 'Smarty.class.php';

class SmartyVotewiki extends Smarty
{
	public function __construct()
	{
		parent::__construct();

		$this->template_dir = VW_DIR . '/smarty/templates';
		$this->compile_dir  = VW_DIR . '/smarty/templates_c';
		$this->config_dir   = VW_DIR . '/smarty/configs';
		$this->cache_dir    = VW_DIR . '/smarty/cache';

		$this->clearAllAssign();
	}

	public function clearAllAssign()
	{
		global $locale;

		parent::clearAllAssign();
		$this->assign('locale', $locale);
	}
}

?>
