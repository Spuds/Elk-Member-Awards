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
 * Post Count based awards
 */
class Post_Count_Award extends Abstract_Award
{
	/**
	 * UNIQUE functional name for this award
	 */
	const FUNC = 'Post_Count';

	/**
	 * Constructor, used to define award parameters then pass over to the abstract common
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
			'points_per' => 'int',
		);

		parent::__construct($db, $awardids, $autoawardsprofiles);
	}

	/**
	 * Main processing function for checking award worthiness
	 *
	 * @param int[] $new_loaded_ids
	 */
	public function process($new_loaded_ids)
	{
		// Load and prepare
		$this->_prep_and_group();

		// For each grouping of profiles
		foreach ($this->profile_group as $key => $profile_group)
		{
			// Set the area key, allowing for multiple award_type via profile groups .. post_count_0 post_count_1 etc
			$area = self::FUNC . '_' . $key;

			// See what we can fulfill from the cache
			$this->award_cache_fetch($new_loaded_ids, $area);

			// Get the post count for the remaining ids
			$this->_post_count_profile_group($key);

			// Save this for a while
			$this->award_cache_save($this->remaining_ids, $area);

			// See if any members have earned it
			$this->members = array();
			$this->_check_members($profile_group, $area);

			// Anyone earn this area award
			$this->assign($this->award_type(), $this->profile_award_ids[$key]);
		}

		// Maintain the cache
		$this->award_cache_purge(self::FUNC);
	}

	/**
	 * Expands the serialized award parameters and places it back for usage
	 *  - Groups like profile awards together
	 *  - Requires that the query returns awards sorted by trigger value
	 */
	protected function _prep_and_group()
	{
		// Nothing special here, so use the abstract method
		parent::_prep_and_group();
	}

	/**
	 * Returns the post count for the specified users from the specified boards
	 *
	 * @param int $key key of the profile to use for parameters
	 */
	private function _post_count_profile_group($key)
	{
		global $user_profile;

		if (empty($this->remaining_ids))
		{
			return;
		}

		$boards = $this->profiles[$key]['parameters']['boards'];

		// All boards is just their current post count
		if ($boards == 'all')
		{
			foreach ($this->remaining_ids as $member_id)
			{
				$user_profile[$member_id][self::FUNC . '_' . $key] = $user_profile[$member_id]['posts'];
			}
		}
		// Some boards means we have some work
		else
		{
			$request = $this->_db->query('', '
				SELECT
					COUNT(*) AS posts, id_member
				FROM {db_prefix}messages
				WHERE id_board IN ({array_int:board_list})
					AND id_member IN ({array_int:members})
				GROUP BY id_member',
				array(
					'board_list' => explode('|', $boards),
					'members' => $this->remaining_ids
				)
			);
			while ($row = $this->_db->fetch_assoc($request))
			{
				$user_profile[$row['id_member']][self::FUNC . '_' . $key] = $row['posts'];
			}

			$this->_db->free_result($request);
		}
	}

	/**
	 * Loops on a set of member id's and checks if they have reached the award trigger level
	 *
	 * @param array[] $profile_group profile group containing the awards we are comparing against
	 * @param string $area unique award_profile key like topic_count_0
	 */
	protected function _check_members($profile_group, $area)
	{
		// Nothing special here, so use the abstract method
		parent::_check_members($profile_group, $area);
	}
}