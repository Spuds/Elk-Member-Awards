<?php

/**
 * @name      Member Awards
 * @copyright Spuds
 * @license   MPL 1.1 http://mozilla.org/MPL/1.1/
 *
 * @version 1.0
 *
 */

// If we have found SSI.php and we are outside of ELK, then we are running standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('ELK'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('ELK'))
	die('<b>Error:</b> Cannot install - please verify you put this file in the same place as ELK\'s SSI.php.');

if ((ELK == 'SSI') && !$user_info['is_admin'])
	die('Admin priveleges required.');

$db = database();

global $db_prefix, $modSettings;

// Settings to create new mod settings...
$mod_settings = array(
	'awards_dir' => 'awards',
	'awards_favorites' =>  1,
	'awards_in_post' => 1,
	'awards_avatar_format' => 1,
	'awards_signature_format' => 1,
	'awards_belowavatar_title' => 'awards',
	'awards_aboveavatar_title' => 'awards',
	'awards_signature_title' => 'awards',
	'awards_requests' => 0,
);

// Define our tables
$tables = array();
$tables[] = array(
	'table_name' => 'awards_members',
	'columns' => array(
		array(
			'name' => 'uniq_id',
			'type' => 'mediumint',
			'size' => 8,
			'null' => false,
			'auto' => true
		),
		array(
			'name' => 'id_award',
			'type' => 'mediumint',
			'size' => 8,
			'null' => false,
			'default' => 0
		),
		array(
			'name' => 'id_member',
			'type' => 'mediumint',
			'size' => 8,
			'null' => false,
			'default' => 0
		),
		array(
			'name' => 'id_group',
			'type' => 'int',
			'size' => 8,
			'null' => false,
			'default' => 0
		),
		array(
			'name' => 'date_received',
			'type' => 'date',
			'null' => false,
			'default' => '0001-01-01'
		),
		array(
			'name' => 'favorite',
			'type' => 'tinyint',
			'size' => 1,
			'null' => false,
			'default' => 0
		),
		array(
			'name' => 'award_type',
			'type' => 'tinyint',
			'size' =>  2,
			'null' => false,
			'default' => 0
		),
		array(
			'name' => 'active',
			'type' => 'tinyint',
			'size' => 1,
			'null' => false,
			'default' => 0
		),
		array(
			'name' => 'comments',
			'type' => 'text',
			'null' => false
		)
	),
	'indexes' => array(
		array(
			'type' => 'unique',
			'columns' => array( 'id_member', 'id_award')
		),
		array(
			'type' => 'index',
			'columns' => array('id_member')
		),
		array(
			'type' => 'primary',
			'columns' => array('uniq_id')
		),
	),
	'if_exists' => 'ignore',
	'error' => 'fatal',
	'parameters' => array(),
);

$tables[] = array(
	'table_name' => 'awards',
	'columns' => array(
		array(
			'name' => 'id_award',
			'type' => 'mediumint',
			'size' => 5,
			'null' => false,
			'auto' => true
		),
		array(
			'name' => 'award_name',
			'type' => 'varchar',
			'size' => 80,
			'null' => false
		),
		array(
			'name' => 'award_function',
			'type' => 'varchar',
			'size' => 256,
			'null' => false
		),
		array(
			'name' => 'description',
			'type' => 'varchar',
			'size' => 256,
			'null' => false
		),
		array(
			'name' => 'time_added',
			'type' => 'int',
			'size' => 10,
			'null' => false,
			'default' => 0
		),
		array(
			'name' => 'filename',
			'type' => 'tinytext',
			'null' => false
		),
		array(
			'name' => 'minifile',
			'type' => 'tinytext',
			'null' => false
		),
		array(
			'name' => 'award_trigger',
			'type' => 'mediumint',
			'size' => 5,
			'null' => false,
			'default' => 0
		),
		array(
			'name' => 'award_param',
			'type' => 'varchar',
			'size' => 256,
			'null' => false
		),
		array(
			'name' => 'award_type',
			'type' => 'tinyint',
			'size' =>  2,
			'null' => false,
			'default' => 0
		),
		array(
			'name' => 'award_location',
			'type' => 'tinyint',
			'size' =>  1,
			'null' => false,
			'default' => 0
		),
		array(
			'name' => 'id_category',
			'type' => 'tinyint',
			'size' => 4,
			'null' => false,
			'default' => 1
		),
		array(
			'name' => 'id_profile',
			'type' => 'tinyint',
			'size' => 4,
			'null' => false,
			'default' => 0
		),
		array(
			'name' => 'award_requestable',
			'type' => 'tinyint',
			'size' => 1,
			'null' => false,
			'default' => 0
		),
		array(
			'name' => 'award_assignable',
			'type' => 'tinyint',
			'size' => 1,
			'null' => false,
			'default' => 0
		),
	),
	'indexes' => array(
		array(
			'type' => 'primary',
			'columns' => array('id_award')
		),
		array(
			'type' => 'index',
			'columns' => array('award_type','award_trigger')
		),
		array(
			'type' => 'index',
			'columns' => array('id_category')
		)
	),
	'if_exists' => 'ignore',
	'error' => 'fatal',
	'parameters' => array(),
);

$tables[] = array(
	'table_name' => 'awards_categories',
	'columns' => array(
		array(
			'name' => 'id_category',
			'type' => 'mediumint',
			'size' => 8,
			'null' => false,
			'auto' => true
		),
		array(
			'name' => 'category_name',
			'type' => 'varchar',
			'size' => 255,
			'null' => false
		),
	),
	'indexes' => array(
		array(
			'type' => 'primary',
			'columns' => array('id_category')
		),
		array(
			'type' => 'index',
			'columns' => array('category_name')
		)
	),
	'if_exists' => 'ignore',
	'error' => 'fatal',
	'parameters' => array(),
);

$tables[] = array(
	'table_name' => 'awards_profiles',
	'columns' => array(
		array(
			'name' => 'id_profile',
			'type' => 'mediumint',
			'size' => 8,
			'null' => false,
			'auto' => true
		),
		array(
			'name' => 'type',
			'type' => 'tinyint',
			'size' => 4,
			'null' => false
		),
		array(
			'name' => 'name',
			'type' => 'tinytext',
			'null' => false
		),
		array(
			'name' => 'parameters',
			'type' => 'text',
			'null' => false
		),
	),
	'indexes' => array(
		array(
			'type' => 'primary',
			'columns' => array('id_profile')
		),
	),
	'if_exists' => 'ignore',
	'error' => 'fatal',
	'parameters' => array(),
);

// Settings to create new columns in existing tables
$columns = array();

// Now on to the tables ... if they don't exist create them and if they do exist update them if required.
$dbtbl = db_table();
$current_tables = $db->db_list_tables(false, '%awards_%');
$real_prefix = preg_match('~^(`?)(.+?)\\1\\.(.*?)$~', $db_prefix, $match) === 1 ? $match[3] : $db_prefix;

// Loop through each defined table and do whats needed, update existing or add as new
foreach ($tables as $table)
{
	// Does the table exist?
	if (in_array($real_prefix . $table['table_name'], array_map('strtolower', $current_tables)))
	{
		foreach ($table['columns'] as $column)
			$dbtbl->db_add_column($db_prefix . $table['table_name'], $column);

		foreach ($table['indexes'] as $index)
			$dbtbl->db_add_index($db_prefix . $table['table_name'], $index, array(), 'ignore');
	}
	else
		$dbtbl->db_create_table($db_prefix . $table['table_name'], $table['columns'], $table['indexes'], $table['parameters'], $table['if_exists'], $table['error']);
}

// And for good measure, lets add a default category and profile
$rows = array();
$rows[] = array(
	'method' => 'ignore',
	'table_name' => '{db_prefix}awards_categories',
	'columns' => array(
		'category_name' => 'string',
		'id_category' => 'int'
	),
	'data' => array(
		'Uncategorized',
		1
	),
	'keys' => array(
		'id_category'
	)
);
$rows[] = array(
	'method' => 'ignore',
	'table_name' => '{db_prefix}awards_profiles',
	'columns' => array(
		'name' => 'string',
		'id_profile' => 'int',
		'type' => 'int',
		'parameters' => 'string'
	),
	'data' => array(
		'Default',
		0,
		0,
		''
	),
	'keys' => array(
		'id_profile'
	)
);

// Add rows to any existing tables
foreach ($rows as $row)
	$db->insert($row['method'], $row['table_name'], $row['columns'], $row['data'], $row['keys']);

// Update/add mod settings if applicable
foreach ($mod_settings as $new_setting => $new_value)
{
	if (!isset($modSettings[$new_setting]))
		updateSettings(array($new_setting => $new_value));
}

// Done
if(ELK === 'SSI')
	echo 'Database changes are complete!';