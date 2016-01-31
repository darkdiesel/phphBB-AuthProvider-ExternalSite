<?php
/**
*
 * @package phpBB Extension - Other Site Auth Provider
 * @copyright (c) Igor Peshkov (dark_diesel) <https://plus.google.com/+IgorPeshkov>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, contact with developer
*
*/

namespace dark_diesel\AuthProviderOtherSite\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class main_listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'						=> 'load_language_on_setup',
		);
	}

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/**
	* Constructor
	*
	* @param \phpbb\controller\helper	$helper		Controller helper object
	* @param \phpbb\template			$template	Template object
	*/
	public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template)
	{
		$this->helper = $helper;
		$this->template = $template;
	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'dark_diesel/AuthProviderOtherSite',
			'lang_set' => 'acp/board',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}
}
