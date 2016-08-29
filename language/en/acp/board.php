<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @package phpBB Extension - External Site Auth Provider
 * @copyright (c) Igor Peshkov (dark_diesel) <https://plus.google.com/+IgorPeshkov>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, contact with developer
 *
 */

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB')) {
  exit;
}

if (empty($lang) || !is_array($lang)) {
  $lang = array();
}

$lang = array_merge($lang, array(
  'AUTH_EXTERNALSITE_DB_CONNECTION'  => 'DB Connection Settings for External Site',
  'AUTH_EXTERNALSITE_TABLE_SETTINGS' => 'User Data & Password Hash Settings',

  'AUTH_EXTERNALSITE_DBMS'        => 'External Site DataBase Management System (DBMS)',
  'AUTH_EXTERNALSITE_DBMS_MYSQL'  => 'MySql',
  'AUTH_EXTERNALSITE_DBMS_MYSQLI' => 'MySql(i)',

  'AUTH_EXTERNALSITE_IS_USERS_SAME_DB' => 'Is User Table Stored in the same DB with phpbb?',
  'AUTH_EXTERNALSITE_IN_SAME_DB'          => 'Same db',
  'AUTH_EXTERNALSITE_IN_REMOTE_DB'        => 'Remote db',

  'AUTH_EXTERNALSITE_DB_HOST'         => 'External Site db host',
  'AUTH_EXTERNALSITE_DB_HOST_EXPLAIN' => 'Example: 127.0.0.1',
  'AUTH_EXTERNALSITE_DB_USER'         => 'External Site db user',
  'AUTH_EXTERNALSITE_DB_USER_EXPLAIN' => '',
  'AUTH_EXTERNALSITE_DB_PASS'         => 'External Site db password',
  'AUTH_EXTERNALSITE_DB_PASS_EXPLAIN' => '',
  'AUTH_EXTERNALSITE_DB_PORT'         => 'External Site db port',
  'AUTH_EXTERNALSITE_DB_PORT_EXPLAIN' => 'Port for remote db. Default: 3306',
  'AUTH_EXTERNALSITE_DB_NAME'         => 'External Site db name',
  'AUTH_EXTERNALSITE_DB_NAME_EXPLAIN' => '',

  'AUTH_EXTERNALSITE_TYPE'         => 'External Site Type',
  'AUTH_EXTERNALSITE_TYPE_EXPLAIN' => '',
  'AUTH_EXTERNALSITE_TYPE_WP4X'    => 'WordPress 4.x',
  'AUTH_EXTERNALSITE_TYPE_CUSTOM'  => 'Custom Site',

  'AUTH_EXTERNALSITE_WP4X_DB_PREFIX' => 'WordPress 4.x DB prefix',
  'AUTH_EXTERNALSITE_WP4X_DB_PREFIX_EXPLAIN' => 'DB prefix, stored in wp-config.php as $table_prefix variable. <br/> <strong>Example:</strong> <samp>wp_</samp>',

  'AUTH_EXTERNALSITE_CUSTOM_HASH'         => 'Custom Password Hash',
  'AUTH_EXTERNALSITE_CUSTOM_HASH_EXPLAIN' => '',
  'AUTH_EXTERNALSITE_CUSTOM_HASH_SHA1'    => 'Sha1',
  'AUTH_EXTERNALSITE_CUSTOM_HASH_MD5'     => 'MD5',
  'AUTH_EXTERNALSITE_CUSTOM_HASH_CUSTOM'  => 'Custom Hash',

  'AUTH_EXTERNALSITE_USER_TABLE'                => 'External Site user table',
  'AUTH_EXTERNALSITE_USER_TABLE_EXPLAIN'        => 'Table of database that contains users from external site. <br/> <strong>Example:</strong> <samp>user</samp>',
  'AUTH_EXTERNALSITE_USER_NAME_FLD'             => 'User Name column at user table',
  'AUTH_EXTERNALSITE_USER_NAME_FLD_EXPLAIN'     => 'Set column name at external site user table that contains unique user name<br/> <strong>Example:</strong> <samp>NickName</samp>',
  'AUTH_EXTERNALSITE_USER_PASSWORD_FLD'         => 'User Password column at user table',
  'AUTH_EXTERNALSITE_USER_PASSWORD_FLD_EXPLAIN' => 'Set column name at external site user table that contains user password<br/> <strong>Example:</strong> <samp>Password</samp>',
  'AUTH_EXTERNALSITE_USER_EMAIL_FLD'            => 'User Email column at user table',
  'AUTH_EXTERNALSITE_USER_EMAIL_FLD_EXPLAIN'    => 'Set column name at external site user table that contains user email<br/> <strong>Example:</strong> <samp>Email</samp>',
));
