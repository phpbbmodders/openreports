<?php
/**
 *
 * Open Reports extension for the phpBB Forum Software package
 *
 * @copyright (c) 2024, phpBB Modders, https://www.phpbbmodders.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbmodders\openreports\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Open Reports event listener
 */
class main_listener implements EventSubscriberInterface
{
	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth                   $auth
	 * @param \phpbb\db\driver\driver_interface  $db
	 * @param \phpbb\language\language           $language
	 * @param \phpbb\template\template           $template
	 * @param string                             $root_path
	 * @param string                             $php_ext
	 * @param string                             $table_prefix
	 */
	public function __construct(protected \phpbb\auth\auth $auth, protected \phpbb\db\driver\driver_interface $db, protected \phpbb\language\language $language, protected \phpbb\template\template $template, protected $root_path, protected $php_ext, protected $table_prefix)
	{
		$this->auth = $auth;
		$this->db = $db;
		$this->language = $language;
		$this->template = $template;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->table_prefix = $table_prefix;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup'	=> 'user_setup',
			'core.page_header'	=> 'page_header',
		];
	}

	/**
	 * Load common language files
	 */
	public function user_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'phpbbmodders/openreports',
			'lang_set' => 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Add a link to the controller in the forum navbar
	 */
	public function page_header()
	{
		if ($this->auth->acl_get('m_report'))
		{
			$sql = 'SELECT *
				FROM ' . REPORTS_TABLE . '
				WHERE report_closed = 0';
			$result = $this->db->sql_query($sql);
			$post_reports = $pm_reports = 0;
			while ($row = $this->db->sql_fetchrow($result))
			{
				if ($row['pm_id'] == 0)
				{
					$post_reports++;
				}
				else
				{
					$pm_reports++;
				}
			}
			$this->db->sql_freeresult($result);

			$this->template->assign_vars([
				'U_PM_REPORTS'		=> $pm_reports > 0 ? append_sid($this->root_path . 'mcp.' . $this->php_ext, "i=mcp_pm_reports&amp;mode=pm_reports") : '',
				'U_POST_REPORTS'	=> $post_reports > 0 ? append_sid($this->root_path . 'mcp.' . $this->php_ext, "i=reports") : '',

				'PM_REPORTS'	=> $pm_reports > 0 ? $this->language->lang('PM_REPORTS', $pm_reports) : '',
				'POST_REPORTS'	=> $post_reports > 0 ? $this->language->lang('POST_REPORTS', $post_reports) : '',
			]);
		}
	}
}
