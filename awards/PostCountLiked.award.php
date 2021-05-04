<?php

/**
 * @package   Awards Addon
 * @license   Mozilla Public License version 1.1 http://www.mozilla.org/MPL/1.1/.
 *
 * This software is a derived product, based on:
 * Original Software by:           Juan "JayBachatero" Hernandez
 * Copyright (c) 2006-2009:        YodaOfDarkness (Fustrate)
 * Copyright (c) 2010:             Jason "JBlaze" Clemons
 *
 * @version 1.2
 *
 */

/**
 * Post Count based awards
 */
class Post_Count_Liked_Award extends Abstract_Award
{
	/**
	 * UNIQUE functional name for this award
	 */
	const FUNC = 'Post_Count_Liked';

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
	 *
	 */
	public function process($new_loaded_ids)
	{
		// Load and prepare
		$this->_prep_and_group();

		// For each grouping of boards
		foreach ($this->profile_group as $key => $profile_group)
		{
			// Set the funckey :P Allows for multiple of this "award_type" based on board groups
			$area = self::FUNC . '_' . $key;

			// See what we can fulfill from the cache
			$this->award_cache_fetch($new_loaded_ids, $area);

			// Get the totals for the remaining
			$this->post_count_liked_board_group($key);

			// Save this for a while
			$this->award_cache_save($this->remaining_ids, $area);

			// See if any members have earned it
			$this->members = array();
			$this->_check_members($profile_group, $area);

			// Assign it to anyone that has earn this funckey award
			$this->assign($this->award_type(), $this->profile_award_ids[$key]);
		}

		// Maintain the cache
		$this->award_cache_purge(self::FUNC);
	}

	/**
	 * Expands the serialized award parameters and places it back in place
	 * - Groups same profile awards together
	 * - Requires that the query returns awards sorted by trigger value
	 */
	protected function _prep_and_group()
	{
		// Nothing special here, so use the abstract method
		parent::_prep_and_group();
	}

	/**
	 * Determines the number of posts of a member that have >= min_post_likes as defined
	 * by a profile
	 *
	 * - Only posts from specific boards are considered, based on those in the profile
	 * - If the profile defines a min_topic_replies, only liked messages in topics with > x
	 * replies are considered
	 *
	 * @param string $key profile ID for this group of awards
	 */
	private function post_count_liked_board_group($key)
	{
		global $user_profile;

		if (empty($this->remaining_ids))
		{
			return;
		}

		// Profile value shortcuts
		$boards = $this->profiles[$key]['parameters']['boards'];
		$like_threshold = $this->profiles[$key]['parameters']['like_threshold'];
		$min_topic_replies = $this->profiles[$key]['parameters']['min_topic_replies'];

		// Total likes, per message, for these members for posts on these boards
		$request = $this->_db->query('', '
			SELECT
				COUNT(*) AS likes, m.id_member
			FROM {db_prefix}messages as m
				INNER JOIN {db_prefix}message_likes AS lk ON lk.id_msg = m.id_msg
				LEFT JOIN {db_prefix}topics AS t on t.id_topic = m.id_topic
			WHERE id_poster IN ({array_int:members})' . ($boards === 'all' ? '' : '
				AND m.id_board IN ({array_int:board_list})') . ($min_topic_replies < 1 ? '' : '
				AND t.num_replies > {int:min_topic_replies}') . '
			GROUP BY lk.id_poster, lk.id_msg
			HAVING COUNT(*) > {int:like_threshold}',
			array(
				'board_list' => explode('|', $boards),
				'members' => $this->remaining_ids,
				'like_threshold' => $like_threshold - 1,
				'min_topic_replies' => $min_topic_replies - 1,
			)
		);
		$members = array();
		// The query returns, in descending order, the like count total per post
		while ($row = $this->_db->fetch_assoc($request))
		{
			if (isset($members[$row['id_member']]))
			{
				$members[$row['id_member']]++;
			}
			else
			{
				$members[$row['id_member']] = 1;
			}
		}
		$this->_db->free_result($request);

		// Finally load the total found in to the appropriate user_profiles
		foreach ($this->remaining_ids as $id)
		{
			if (isset($members[$id]))
			{
				$user_profile[$id][self::FUNC . '_' . $key] = $members[$id];
			}
			else
			{
				$user_profile[$id][self::FUNC . '_' . $key] = 0;
			}
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