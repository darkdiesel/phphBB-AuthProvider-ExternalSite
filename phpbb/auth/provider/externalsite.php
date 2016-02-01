<?php
/**
 *
 * This file is written by Igor Peshkov for external site user authentication.
 *
 * @package phpBB Extension - External Site Auth Provider
 * @copyright (c) Igor Peshkov (dark_diesel) <https://plus.google.com/+IgorPeshkov>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, contact with developer
 *
 */

namespace darkdiesel\authproviderexternalsite\phpbb\auth\provider;

/**
 * Database authentication provider for phpBB3
 * This is for authentication via the integrated external site
 */
class externalsite extends \phpbb\auth\provider\base
{
    /**
     * phpBB passwords manager
     *
     * @var \phpbb\passwords\manager
     */
    protected $passwords_manager;

    /**
     * DI container
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $phpbb_container;

    /**
     * Database Authentication Constructor
     *
     * @param	\phpbb\db\driver\driver_interface		$db
     * @param	\phpbb\config\config 		$config
     * @param	\phpbb\passwords\manager	$passwords_manager
     * @param	\phpbb\request\request		$request
     * @param	\phpbb\user			$user
     * @param	\Symfony\Component\DependencyInjection\ContainerInterface $phpbb_container DI container
     * @param	string				$phpbb_root_path
     * @param	string				$php_ext
     */
    public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\passwords\manager $passwords_manager, \phpbb\request\request $request, \phpbb\user $user, \Symfony\Component\DependencyInjection\ContainerInterface $phpbb_container, $phpbb_root_path, $php_ext)
    {
        $this->db = $db;
        $this->config = $config;
        $this->passwords_manager = $passwords_manager;
        $this->request = $request;
        $this->user = $user;
        $this->phpbb_root_path = $phpbb_root_path;
        $this->php_ext = $php_ext;
        $this->phpbb_container = $phpbb_container;
    }

    /**
     * {@inheritdoc}
     */
    public function login($username, $password)
    {
        // Auth plugins get the password untrimmed.
        // For compatibility we trim() here.
        $password = trim($password);

        // do not allow empty password
        if (!$password)
        {
            return array(
                'status'	=> LOGIN_ERROR_PASSWORD,
                'error_msg'	=> 'NO_PASSWORD_SUPPLIED',
                'user_row'	=> array('user_id' => ANONYMOUS),
            );
        }

        if (!$username)
        {
            return array(
                'status'	=> LOGIN_ERROR_USERNAME,
                'error_msg'	=> 'LOGIN_ERROR_USERNAME',
                'user_row'	=> array('user_id' => ANONYMOUS),
            );
        }

        $username_clean = utf8_clean_string($username);

        // get external site user
        $sql = 'SELECT *
			FROM ' . $this->config['auth_externalsite_user_table'] . "
			WHERE " . $this->config['auth_externalsite_user_name_fld']. " = '" . $this->db->sql_escape($username_clean) . "'";
        $result = $this->db->sql_query($sql);
        $external_site_user = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        // get forum user
        $sql = 'SELECT *
			FROM ' . USERS_TABLE . "
			WHERE username_clean = '" . $this->db->sql_escape($username_clean) . "'";
        $result = $this->db->sql_query($sql);
        $forum_user = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        // check count of authorisation
        if (($this->user->ip && !$this->config['ip_login_limit_use_forwarded']) ||
            ($this->user->forwarded_for && $this->config['ip_login_limit_use_forwarded']))
        {
            $sql = 'SELECT COUNT(*) AS attempts
				FROM ' . LOGIN_ATTEMPT_TABLE . '
				WHERE attempt_time > ' . (time() - (int) $this->config['ip_login_limit_time']);
            if ($this->config['ip_login_limit_use_forwarded'])
            {
                $sql .= " AND attempt_forwarded_for = '" . $this->db->sql_escape($this->user->forwarded_for) . "'";
            }
            else
            {
                $sql .= " AND attempt_ip = '" . $this->db->sql_escape($this->user->ip) . "' ";
            }

            $result = $this->db->sql_query($sql);
            $attempts = (int) $this->db->sql_fetchfield('attempts');
            $this->db->sql_freeresult($result);

            $attempt_data = array(
                'attempt_ip'			=> $this->user->ip,
                'attempt_browser'		=> trim(substr($this->user->browser, 0, 149)),
                'attempt_forwarded_for'	=> $this->user->forwarded_for,
                'attempt_time'			=> time(),
                'user_id'				=> ($forum_user) ? (int) $forum_user['user_id'] : 0,
                'username'				=> $username,
                'username_clean'		=> $username_clean,
            );
            $sql = 'INSERT INTO ' . LOGIN_ATTEMPT_TABLE . $this->db->sql_build_array('INSERT', $attempt_data);
            $this->db->sql_query($sql);
        }
        else
        {
            $attempts = 0;
        }

        if (!$external_site_user)
        {
            if ($this->config['ip_login_limit_max'] && $attempts >= $this->config['ip_login_limit_max'])
            {
                return array(
                    'status'		=> LOGIN_ERROR_ATTEMPTS,
                    'error_msg'		=> 'LOGIN_ERROR_ATTEMPTS',
                    'user_row'		=> array('user_id' => ANONYMOUS),
                );
            }

            return array(
                'status'	=> LOGIN_ERROR_USERNAME,
                'error_msg'	=> 'LOGIN_ERROR_USERNAME',
                'user_row'	=> array('user_id' => ANONYMOUS),
            );
        } elseif ($external_site_user && !$forum_user){
            if ($this->config['ip_login_limit_max'] && $attempts >= $this->config['ip_login_limit_max'])
            {
                return array(
                    'status'		=> LOGIN_ERROR_ATTEMPTS,
                    'error_msg'		=> 'LOGIN_ERROR_ATTEMPTS',
                    'user_row'		=> array('user_id' => ANONYMOUS),
                );
            }

            if ($external_site_user[$this->config['auth_externalsite_user_password_fld']] == sha1($password)) {
                return array(
                    'status' => LOGIN_SUCCESS_CREATE_PROFILE,
                    'error_msg' => false,
                    'user_row' => array(
                        "username" => $external_site_user[$this->config['auth_externalsite_user_name_fld']],
                        "user_password" => $this->passwords_manager->hash($password),
                        "user_email" => $external_site_user[$this->config['auth_externalsite_user_email_fld']],
                        "user_type" => USER_NORMAL,
                        "group_id" => 2
                    ),
                );
            } else {
                return array(
                    'status' => LOGIN_ERROR_PASSWORD,
                    'error_msg' => 'LOGIN_ERROR_PASSWORD',
                    'user_row' => array('user_id' => ANONYMOUS),
                );
            }
        }

        $show_captcha = ($this->config['max_login_attempts'] && $forum_user['user_login_attempts'] >= $this->config['max_login_attempts']) ||
            ($this->config['ip_login_limit_max'] && $attempts >= $this->config['ip_login_limit_max']);

        // If there are too many login attempts, we need to check for a confirm image
        // Every auth module is able to define what to do by itself...
        if ($show_captcha)
        {
            $captcha_factory = $this->phpbb_container->get('captcha.factory');
            $captcha = $captcha_factory->get_instance($this->config['captcha_plugin']);
            $captcha->init(CONFIRM_LOGIN);
            $vc_response = $captcha->validate($forum_user);
            if ($vc_response)
            {
                return array(
                    'status'		=> LOGIN_ERROR_ATTEMPTS,
                    'error_msg'		=> 'LOGIN_ERROR_ATTEMPTS',
                    'user_row'		=> $forum_user,
                );
            }
            else
            {
                $captcha->reset();
            }
        }

        // Check password ...
        //if ($this->passwords_manager->check($password, $forum_user['user_password'], $forum_user))
        if ($external_site_user[$this->config['auth_externalsite_user_password_fld']] == sha1($password))
        {
            // Check for old password hash...
            if ($this->passwords_manager->convert_flag || strlen($forum_user['user_password']) == 32)
            {
                $hash = $this->passwords_manager->hash($password);

                // Update the password in the users table to the new format
                $sql = 'UPDATE ' . USERS_TABLE . "
					SET user_password = '" . $this->db->sql_escape($hash) . "'
					WHERE user_id = {$forum_user['user_id']}";
                $this->db->sql_query($sql);

                $forum_user['user_password'] = $hash;
            }

            $sql = 'DELETE FROM ' . LOGIN_ATTEMPT_TABLE . '
				WHERE user_id = ' . $forum_user['user_id'];
            $this->db->sql_query($sql);

            if ($forum_user['user_login_attempts'] != 0)
            {
                // Successful, reset login attempts (the user passed all stages)
                $sql = 'UPDATE ' . USERS_TABLE . '
					SET user_login_attempts = 0
					WHERE user_id = ' . $forum_user['user_id'];
                $this->db->sql_query($sql);
            }

            // User inactive...
            if ($forum_user['user_type'] == USER_INACTIVE || $forum_user['user_type'] == USER_IGNORE)
            {
                return array(
                    'status'		=> LOGIN_ERROR_ACTIVE,
                    'error_msg'		=> 'ACTIVE_ERROR',
                    'user_row'		=> $forum_user,
                );
            }

            // Successful login... set user_login_attempts to zero...
            return array(
                'status'		=> LOGIN_SUCCESS,
                'error_msg'		=> false,
                'user_row'		=> $forum_user,
            );
        }

        // Password incorrect - increase login attempts
        $sql = 'UPDATE ' . USERS_TABLE . '
			SET user_login_attempts = user_login_attempts + 1
			WHERE user_id = ' . (int) $forum_user['user_id'] . '
				AND user_login_attempts < ' . LOGIN_ATTEMPTS_MAX;
        $this->db->sql_query($sql);

        // Give status about wrong password...
        return array(
            'status'		=> ($show_captcha) ? LOGIN_ERROR_ATTEMPTS : LOGIN_ERROR_PASSWORD,
            'error_msg'		=> 'LOGIN_ERROR_PASSWORD',
            'user_row'		=> $forum_user,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function acp()
    {
        // These are fields required in the config table
        return array(
            'auth_externalsite_user_table', 'auth_externalsite_user_name_fld', 'auth_externalsite_user_password_fld', 'auth_externalsite_user_email_fld',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function get_acp_template($new_config)
    {
        return array(
            'TEMPLATE_FILE'	=> './../../ext/darkdiesel/authproviderexternalsite/adm/style/auth_provider_externalsite.html',
            'TEMPLATE_VARS'	=> array(
                'AUTH_EXTERNALSITE_USER_TABLE'		=> $new_config['auth_externalsite_user_table'],
                'AUTH_EXTERNALSITE_USER_NAME_FLD'		=> $new_config['auth_externalsite_user_name_fld'],
                'AUTH_EXTERNALSITE_USER_PASSWORD_FLD'		=> $new_config['auth_externalsite_user_password_fld'],
                'AUTH_EXTERNALSITE_USER_EMAIL_FLD'		=> $new_config['auth_externalsite_user_email_fld'],
            ),
        );
    }
}
