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
 * Top Poster Award
 *
 * @param mixed[] $parameters
 * @param int $id - not used in this award
 * @param boolean $return_parameters if true returns the configuration options for the award
 */
class Top_Poster_Award extends Abstract_Award
{
	/**
	 * UNIQUE functional name for this award
	 */
	const FUNC = 'Top_Poster';

	protected $n_members = array(0 => -1);

	/**
	 * Constructor, used to define award parameters
	 *
	 * @param Database|null $db
	 * @param array[]|null $awardids array of award information containing all of a specific type
	 * @param array[]|null $autoawardsprofiles array of profiles in the system
	 */
	public function __construct($db = null, $awardids = null, $autoawardsprofiles = null)
	{
		$this->award_parameters = array(
			'trigger' => 'int',
			'points' => 'int',
		);

		parent::__construct($db, $awardids, $autoawardsprofiles);
	}

	/**
	 * Initializes an award for use.
	 *
	 * @param int[] $new_loaded_ids id's of members to check
	 */
	public function process($new_loaded_ids)
	{
		// Load and prepare
		$this->_prep_and_group();

		// For each grouping of profiles
		foreach ($this->profile_group as $key => $profile_group)
		{
			// Set the function key Allows for multiple award_type via profile groups
			$area = self::FUNC . '_' . $key;

			// See what we can fulfill from the cache
			$this->award_cache_fetch($new_loaded_ids, $area, true);

			// Get the top posters for the remaining ids
			$this->_top_posters_1toN($key);

			// Save this for a while
			$this->award_cache_save($this->remaining_ids, $area);

			// See if any members have earned it
			$this->members = array();
			$this->_check_members($profile_group, $area);

			// Anyone earn this award
			$this->assign($this->award_type(), $this->profile_award_ids[$key]);
		}

		// Maintain the cache
		$this->award_cache_purge(self::FUNC);
	}

	/**
	 * Top Posters 1-N
	 */
	private function _top_posters_1toN($key)
	{
		global $user_profile;

		// For 1-n if this is empty, so was the cache
		if (empty($this->remaining_ids))
		{
			// Profile value shortcuts, we only use $boards (like_threshold and min_topic_replies are not honored here)
			$boards = $this->profiles[$key]['parameters']['boards'];

			// This is the best case, simple member table lookup
			if ($boards === 'all')
				$request = $this->_db->query('', '
				SELECT
					id_member, posts
				FROM {db_prefix}members
				WHERE posts > {int:no_posts}
				ORDER BY posts DESC
				LIMIT {int:limit}',
					array(
						'no_posts' => 0,
						'limit' => $this->awards[$key]['award_trigger'],
					)
				);
			// Otherwise its to the message table, and its not the nicest query
			else
				$request = $this->_db->query('', '
				SELECT
					COUNT(*) AS posts, id_member
				FROM {db_prefix}messages
				WHERE id_board IN ({array_int:board_list})
				GROUP BY id_member
				ORDER BY posts DESC
				LIMIT {int:limit}',
					array(
						'limit' => $this->awards[$key]['award_trigger'],
						'board_list' => $boards
					)
				);

			// Run the query to get the top posters
			$poster_number = 1;
			while ($row = $this->_db->fetch_assoc($request))
			{
				$this->remaining_ids[] = $row['id_member'];
				$user_profile[$row['id_member']][self::FUNC . '_' . $key] = $poster_number++;
			}
			$this->_db->free_result($request);
		}
	}

	/**
	 * Override default method to indicate this a 1 to N award
	 * @return bool
	 */
	public function one_to_n()
	{
		return true;
	}

	/**
	 * Expands the serialized award parameters and places it back for usage
	 *  - Groups like boards together
	 *  - Requires that the query returns awards sorted by trigger value
	 */
	protected function _prep_and_group()
	{
		// For all the awards of this type / function
		foreach ($this->awards as $key => $award)
		{
			// Expand out the award params
			if (!is_array($this->awards[$key]['award_param']))
				$this->awards[$key]['award_param'] = unserialize($award['award_param']);

			// The triggers arrive in high to low order, but we need to "sort/group" by profiles as well
			$this->profile_group[$this->awards[$key]['id_profile']][] = $this->awards[$key];
			$this->profile_award_ids[$this->awards[$key]['id_profile']][] = $this->awards[$key]['id_award'];
		}
	}
}