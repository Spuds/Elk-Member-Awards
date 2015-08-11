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
 * User info block, shows avatar, group, icons, posts, karma, etc
 *
 * @param mixed[] $parameters not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Topic_Count_Award extends Abstract_Award
{
	/**
	 * UNIQUE functional name for this award
	 */
	const FUNC = 'Topic_Count';

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
	 * Process an award for use.
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
			$this->award_cache_fetch($new_loaded_ids, $area);

			// Get the post count for the remaining ids
			$this->_topic_count_profile_group($key);

			// Save this for a while
			$this->award_cache_save($this->remaining_ids, $area);

			// See if any members have earned it
			$this->members = array();
			$this->_check_members($profile_group, $area);

			// Anyone earn this funckey award
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
	private function _topic_count_profile_group($key)
	{
		global $user_profile, $modSettings;

		if (empty($this->remaining_ids))
			return;

		// Profile value shortcuts
		$boards = $this->profiles[$key]['parameters']['boards'];
		$like_threshold = $this->profiles[$key]['parameters']['like_threshold'];
		$min_topic_replies = $this->profiles[$key]['parameters']['min_topic_replies'];

		// Number of topics started.
		$request = $this->_db->query('', '
			SELECT
				COUNT(*) AS num_topics, id_member_started
			FROM {db_prefix}topics
			WHERE id_member_started IN ({array_int:members})' . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? '
				AND id_board != {int:recycle_board}' : '') . ($boards === 'all' ? '' : '
				AND id_board IN ({array_int:board_list})') . ($min_topic_replies < 1 ? '' : '
				AND num_replies > {int:min_topic_replies}') . ($like_threshold < 1 ? '' : '
				AND num_likes > {int:$ike_threshold}') . '
			GROUP BY id_member_started',
			array(
				'recycle_board' => $modSettings['recycle_board'],
				'board_list' => explode('|', $boards),
				'members' => $this->remaining_ids,
				'min_topic_replies' => $min_topic_replies - 1,
				'like_threshold' => $like_threshold - 1,
			)
		);
		// Load them in to user_profile
		while ($row = $this->_db->fetch_assoc($request))
			$user_profile[$row['id_member_started']][self::FUNC . '_' . $key] = $row['num_topics'];
		$this->_db->free_result($request);
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