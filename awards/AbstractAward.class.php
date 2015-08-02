<?php

/**
 * @name      Awards Addon
 * @license   Mozilla Public License version 1.1 http://www.mozilla.org/MPL/1.1/.
 *
 * This software is a derived product, based on:
 * Original Software by:           Juan "JayBachatero" Hernandez
 * Copyright (c) 2006-2009:        YodaOfDarkness (Fustrate)
 * Copyright (c) 2010:             Jason "JBlaze" Clemons
 *
 * @version   1.0.1
 *
 */

if (!defined('ELK'))
	die('No access...');

/**
 * Abstract Award
 *
 * - Implements Abstract_Award
 * - Sets base functionality for use in awards
 */
abstract class Abstract_Award
{
	/**
	 * The award type keys
	 */
	const AUTOMATIC = 2;
	const MANUAL = 1;
	const GROUP = 0;

	/**
	 * ID's that were not found in the award cache
	 * @var array
	 */
	public $remaining_ids = array();

	/**
	 * TTL in seconds for a cache entry
	 * @var int
	 */
	public $ttl = 300;

	/**
	 * Database object
	 * @var object
	 */
	protected $_db = null;

	/**
	 * Award parameters
	 * @var array
	 */
	protected $award_parameters = array();

	/**
	 * Class constructor, makes db available
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
		$this->_db = $db;
	}

	/**
	 * Does the actual award calculations and determinations
	 *
	 * @param array[] $awardids trigger sorted rows of a specific award type
	 * @param int[] $new_loaded_ids id's of members to check
	 */
	abstract public function process($awardids, $new_loaded_ids);

	/**
	 * Does the database work of setting an autoaward to a member
	 *
	 * - Makes sure each member only has 1 of each award
	 * - handles the adding of new / update cache info
	 * - handles the cache maintenance
	 *
	 * @param int[] $members
	 * @param string $award_type
	 * @param int[] $awardids
	 */
	public function assign($members, $award_type, $awardids)
	{
		global $user_profile;

		// init
		$values = array();
		$users = array();
		$remove = array();

		// Set a date
		$date_received = date('Y') . '-' . date('m') . '-' . date('d');

		// Prepare the database values.
		foreach ($members as $member => $memberaward)
		{
			$values[] = array((int) $memberaward, (int) $member, $date_received, (int) $award_type, 1);
			$users[] = $member;

			// These are all the awardids, for this award type, that this user should no longer have
			$remove[$member] = array_diff($awardids, array($memberaward));

			// And this will contain just the specific award_ids that he should no longer have
			$remove[$member] = array_intersect($user_profile[$member]['awardlist'], $remove[$member]);
		}

		// First the removals ... Members can only have one active award of each auto 'type'
		foreach ($members as $member => $dummy)
		{
			if (!empty($remove[$member]))
				$this->_db->query('', '
					DELETE FROM {db_prefix}awards_members
					WHERE id_award IN ({array_int:award_list})
						AND id_member = {int:id_member}',
					array(
						'id_member' => $member,
						'award_list' => $remove[$member],
					)
				);
		}

		// Now the adds, Insert the award data
		$this->_db->insert('replace', '
			{db_prefix}awards_members',
			array('id_award' => 'int', 'id_member' => 'int', 'date_received' => 'string', 'award_type' => 'int', 'active' => 'int'),
			$values,
			array('id_member', 'id_award')
		);
	}

	/**
	 * Checks if the award is a 1ton (top X) award
	 *
	 * @return string[]
	 */
	public function one_to_n()
	{
		return false;
	}

	/**
	 * Checks if the award is an automatically assigned award
	 *
	 * @return string[]
	 */
	public function manually_assigned()
	{
		return $this->award_type() !== self::AUTOMATIC;
	}

	/**
	 * Returns the type of award, by default they are assumed automatically determined
	 * set to GROUP or MANUAL in the award class files to override
	 *
	 * @return string[]
	 */
	public function award_type()
	{
		return self::AUTOMATIC;
	}

	/**
	 * Validate / Sanitize posted parameters to be in compliance with award_parameters
	 */
	public function clean()
	{
		// Load the parameters for the award
		$type_parameters = $this->parameters();

		if (!empty($_POST['parameters']) && is_array($_POST['parameters']) && !empty($type_parameters))
		{
			// Sanitise the passed parameters
			foreach ($type_parameters as $name => $type)
			{
				if (isset($_POST['parameters'][$name]))
					$this->_prepare_parameters($type, $name);
			}
		}
		else
			$_POST['parameters']['trigger'] = 0;

		return $_POST['parameters'];
	}

	/**
	 * Returns optional award parameters
	 *
	 * @return mixed[]
	 */
	public function parameters()
	{
		return $this->award_parameters;
	}

	/**
	 * Helper method for clean()
	 *
	 * @param string $type type of field
	 * @param string $name name of field
	 */
	private function _prepare_parameters($type, $name)
	{
		if ($type == 'boards' || $type == 'board_select')
			$_POST['parameters'][$name] = is_array($_POST['parameters'][$name]) ? implode('|', $_POST['parameters'][$name]) : $_POST['parameters'][$name];
		elseif ($type == 'int' || $type == 'select')
			$_POST['parameters'][$name] = (int) $_POST['parameters'][$name];
		elseif ($type == 'text' || $type == 'textarea' || is_array($type))
			$_POST['parameters'][$name] = Util::htmlspecialchars($_POST['parameters'][$name], ENT_QUOTES);
		elseif ($type == 'check')
			$_POST['parameters'][$name] = !empty($_POST['parameters'][$name]) ? 1 : 0;
	}

	/**
	 * Fetch interim auto award values
	 *
	 * - Load auto award values for members
	 * - Value must have a recent TTL to be valid
	 * - Fetched by award key value
	 * - Generally a lite weight query to save running larger more complex querys that are
	 * often required for award calculations.
	 *
	 * @param int[] $new_loaded_ids member list to load
	 * @param string $key the award key to load
	 */
	public function award_cache_fetch($new_loaded_ids, $key)
	{
		global $user_profile;

		$request = $this->_db->query('', '
			SELECT
				area_key, time, value, id_member
			FROM {db_prefix}awards_cache
			WHERE id_member IN ({array_int:members})
				AND area_key = {string:area_key}
				AND time > {int:time}',
			array(
				'members' => $new_loaded_ids,
				'area_key' => $key,
				'time' => time() - $this->ttl
			)
		);
		$found = array();
		while ($row = $this->_db->fetch_assoc($request))
		{
			// Keep track of who we loaded
			$found[$row['id_member']] = 1;
			$user_profile[$row['id_member']][$key] = $row['value'];
		}

		// Members that have no data or no current data in the cache
		$this->remaining_ids = array_diff($new_loaded_ids, array_keys($found));
	}

	/**
	 * Removes all cache keys that are older than ttl
	 *
	 * @param string $key
	 */
	public function award_cache_purge($key)
	{
		$this->_db->query('', '
			DELETE FROM {db_prefix}awards_cache
			WHERE time < {int:time}
				AND area_key LIKE {string:area_key}',
			array(
				'time' => time() - $this->ttl,
				'area_key' => $key . '%',
			)
		);
	}

	/**
	 * Add / Update cached entries for a user / key / data
	 *
	 * @param $new_loaded_ids
	 * @param $key
	 */
	public function award_cache_save($new_loaded_ids, $key)
	{
		global $user_profile;

		if (empty($new_loaded_ids))
			return;

		$data = array();
		$time = time();

		// Build the data to add
		foreach ($new_loaded_ids as $member)
		{
			if (isset($user_profile[$member][$key]))
				$data[] = array($member, $key, $time, $user_profile[$member][$key]);
		}

		// And add it
		$this->_db->insert('replace',
			'{db_prefix}awards_cache',
			array('id_member' => 'int', 'area_key' => 'string', 'time' => 'int', 'value' => 'int',),
			$data,
			array('area_key')
		);
	}
}