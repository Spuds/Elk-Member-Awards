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
{
	die('No access...');
}

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
	 *
	 * @var array
	 */
	public $remaining_ids = array();

	/**
	 * TTL in seconds for a cache entry
	 *
	 * @var int
	 */
	public $ttl = 300;

	/**
	 * Holds all award profiles in the system
	 *
	 * @var array
	 */
	public $profiles = array();

	/**
	 * Holds all awards of the specific type
	 *
	 * @var array
	 */
	public $awards = array();

	/**
	 * members that have been determined to have achieved this award
	 *
	 * @var array
	 */
	public $members = array();

	/**
	 * Holds the sorted awards by trigger and profile
	 *
	 * @var array
	 */
	public $profile_group = array();

	/**
	 * Holds the sorted awardid's by trigger and profile
	 *
	 * @var array
	 */
	public $profile_award_ids = array();

	/**
	 * Database object
	 *
	 * @var object
	 */
	protected $_db = null;

	/**
	 * Award parameters
	 *
	 * @var array
	 */
	protected $award_parameters = array();

	/**
	 * Class constructor, makes db available
	 *
	 * @param Database|null $db
	 * @param array[]|null $awardids array of award information containing all of a specific type
	 * @param array[]|null $autoawardsprofiles array of profiles in the system
	 */
	public function __construct($db = null, $awardids, $autoawardsprofiles)
	{
		// Load data for abstract class use
		$this->_db = $db;
		$this->awards = $awardids === null ? array() : $awardids;
		$this->profiles = $autoawardsprofiles === null ? array() : $autoawardsprofiles;
	}

	/**
	 * Does the actual award calculations and determinations
	 *
	 * @param int[] $new_loaded_ids id's of members to check
	 */
	abstract public function process($new_loaded_ids);

	/**
	 * Does the database work of setting an auto award to a member
	 *
	 * - Makes sure each member only has 1 of each award
	 * - handles the adding of new / update cache info
	 * - handles the cache maintenance
	 *
	 * @param string $award_type
	 * @param int[] $awardids
	 */
	public function assign($award_type, $awardids)
	{
		global $user_profile;

		if (empty($this->members))
		{
			return;
		}

		// init
		$values = array();
		$users = array();
		$remove = array();

		// Set a date
		$date_received = date('Y') . '-' . date('m') . '-' . date('d');

		// Prepare the database values.
		foreach ($this->members as $member => $memberaward)
		{
			$values[] = array((int) $memberaward, (int) $member, $date_received, (int) $award_type, 1);
			$users[] = $member;

			// These are all the awardids, for this award type, that this user should no longer have
			$remove[$member] = array_diff($awardids, array($memberaward));

			// And this will contain just the specific award_ids that he should no longer have
			$remove[$member] = array_intersect($user_profile[$member]['awardlist'], $remove[$member]);
		}

		// First the removals ... Members can only have one active award of each auto 'type'
		foreach ($this->members as $member => $dummy)
		{
			if (!empty($remove[$member]))
			{
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
	 * @return string[]|boolean
	 */
	public function one_to_n()
	{
		return false;
	}

	/**
	 * Checks if the award is an automatically assigned award
	 *
	 * @return string[]|boolean
	 */
	public function manually_assigned()
	{
		return $this->award_type() !== $this->award_type();
	}

	/**
	 * Returns the type of award, by default they are assumed automatically determined
	 * set to GROUP or MANUAL in the award class files to override
	 *
	 * @return int
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
				{
					$this->_prepare_parameters($type, $name);
				}
			}
		}
		else
		{
			$_POST['parameters']['trigger'] = 0;
		}

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
		if ($type === 'boards' || $type === 'board_select')
		{
			$_POST['parameters'][$name] = is_array($_POST['parameters'][$name]) ? implode('|', $_POST['parameters'][$name]) : $_POST['parameters'][$name];
		}
		elseif ($type === 'int' || $type === 'select')
		{
			$_POST['parameters'][$name] = (int) $_POST['parameters'][$name];
		}
		elseif ($type === 'text' || $type === 'textarea' || is_array($type))
		{
			$_POST['parameters'][$name] = Util::htmlspecialchars($_POST['parameters'][$name], ENT_QUOTES);
		}
		elseif ($type === 'check')
		{
			$_POST['parameters'][$name] = !empty($_POST['parameters'][$name]) ? 1 : 0;
		}
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
	 * @param bool $nth fetch as a 1 to N award
	 */
	public function award_cache_fetch($new_loaded_ids, $key, $nth = false)
	{
		global $user_profile;

		$request = $this->_db->query('', '
			SELECT
				id_member, area_key, time, value
			FROM {db_prefix}awards_cache
			WHERE area_key = {string:area_key}' . ($nth ? '' : '
				AND id_member IN ({array_int:members})') . '
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
			$found[$row['id_member']] = $row['value'];
			$user_profile[$row['id_member']][$key] = $row['value'];
		}
		$this->_db->free_result($request);

		// Members that have no data or no current data in the cache
		$this->remaining_ids = $nth ? $found : array_diff($new_loaded_ids, array_keys($found));
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
		{
			return;
		}

		$data = array();
		$time = time();

		// Build the data to add
		foreach ($new_loaded_ids as $member)
		{
			if (isset($user_profile[$member][$key]))
			{
				$data[] = array($member, $key, $time, $user_profile[$member][$key]);
			}
		}

		// And add it
		$this->_db->insert('replace',
			'{db_prefix}awards_cache',
			array('id_member' => 'int', 'area_key' => 'string', 'time' => 'int', 'value' => 'string'),
			$data,
			array('area_key')
		);
	}

	/**
	 * Expands the serialized award parameters and places it back for usage
	 *  - Groups like profile awards together
	 *  - Requires that the query returns awards sorted by trigger value
	 */
	protected function _prep_and_group()
	{
		// For all the awards of this type / function
		foreach ($this->awards as $key => $award)
		{
			// Expand out the award params
			if (!is_array($this->awards[$key]['award_param']))
			{
				$this->awards[$key]['award_param'] = unserialize($award['award_param']);
			}

			// The triggers arrive in high to low order, but we need to "sort/group" by profiles as well
			$this->profile_group[$this->awards[$key]['id_profile']][] = $this->awards[$key];
			$this->profile_award_ids[$this->awards[$key]['id_profile']][] = $this->awards[$key]['id_award'];
		}
	}

	/**
	 * Loops on a array of member id's and determines if they have reached the award trigger level
	 *
	 * @param array[] $profile_group profile group containing the awards we are comparing against
	 * @param string $area unique award_profile key like topic_count_0
	 */
	protected function _check_members($profile_group, $area)
	{
		global $user_profile;

		foreach ($this->remaining_ids as $member_id)
		{
			// Check each award level (trigger) for that group, they are sorted high to low
			foreach ($profile_group as $award)
			{
				// Standard limit based awards
				if (!$this->one_to_n() && isset($user_profile[$member_id][$area]) && ($user_profile[$member_id][$area] >= $award['award_trigger']))
				{
					if (!in_array($award['id_award'], $user_profile[$member_id]['awardlist']))
					{
						$this->members[$member_id] = (int) $award['id_award'];
					}
					break;
				}
				// 1 to N award then, the trigger is the number to give out
				elseif (isset($user_profile[$member_id][$area]) && ($user_profile[$member_id][$area] <= $award['award_trigger']))
				{
					if (isset($user_profile[$member_id]['awardlist']) && !in_array($award['id_award'], $user_profile[$member_id]['awardlist']))
					{
						$this->members[$member_id] = (int) $award['id_award'];
					}
					break;
				}
			}
		}
	}
}