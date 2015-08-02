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
 * Post Count based awards
 */
class Post_Count_Liked_Award extends Abstract_Award
{
	const FUNC = 'Post_Count_Liked';

	protected $members = array();
	protected $awardids = array();
	protected $board_group = array();
	protected $board_award_ids = array();
	protected $like_threshold = 2;
	public $ttl = 300;

	/**
	 * Constructor, used to define award parameters
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
		$this->award_parameters = array(
			'trigger' => 'int',
			'points' => 'int',
			'points_per' => 'int',
			'boards' => 'boards',
		);

		parent::__construct($db);
	}

	/**
	 * Main processing function for checking award worthiness
	 *
	 * @param array[] $awardids
	 * @param int[] $new_loaded_ids
	 *
	 * @return array
	 */
	public function process($awardids, $new_loaded_ids)
	{
		global $user_profile;

		// Load and prepare
		$this->awardids = $awardids;
		$this->_prep_and_group();

		// For each grouping of boards
		foreach ($this->board_group as $key => $board_group)
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
			foreach ($this->remaining_ids as $member_id)
			{
				// Check each award level (trigger) for that group, they are sorted high to low
				foreach ($board_group as $award)
				{
					if (isset($user_profile[$member_id][$area]) && ($user_profile[$member_id][$area] >= $award['award_trigger']))
					{
						// Give this member a cupcake, if they don't already have it, and stop looking for more
						if (!in_array($award['id_award'], $user_profile[$member_id]['awardlist']))
							$this->members[$member_id] = (int) $award['id_award'];

						break;
					}
				}
			}

			// Anyone earn this funckey award?
			if (!empty($this->members))
				$this->assign($this->members, $area, $this->board_award_ids[$award['award_param']['boards']]);
		}

		// Maintain the cache
		$this->award_cache_purge(self::FUNC);
	}

	/**
	 * Determines the number of posts of a member that have >= $like_threshold likes
	 *
	 * - Only posts from specific boards are considered.
	 *
	 * @param string $key | separated list of boards
	 */
	private function post_count_liked_board_group($key)
	{
		global $user_profile;

		if (empty($this->remaining_ids))
			return;

		// Total likes, per message, for these members for posts on these boards
		$request = $this->_db->query('', '
			SELECT
				COUNT(*) AS likes, m.id_member
			FROM {db_prefix}messages as m
				INNER JOIN {db_prefix}message_likes AS lk ON lk.id_msg = m.id_msg
			WHERE id_board IN ({array_int:board_list})
				AND id_poster IN ({array_int:members})
			GROUP BY lk.id_poster, lk.id_msg
			HAVING COUNT(*) > {int:like_threshold}',
			array(
				'board_list' => explode('|', $key),
				'members' => $this->remaining_ids,
				'like_threshold' => $this->like_threshold - 1
			)
		);
		$members = array();
		// The query returns, in descending order, the like count total per post
		while ($row = $this->_db->fetch_assoc($request))
		{
			if (isset($members[$row['id_member']]))
				$members[$row['id_member']]++;
			else
				$members[$row['id_member']] = 1;
		}
		$this->_db->free_result($request);

		// Finally load the total found in to the appropriate user_profiles
		foreach ($this->remaining_ids as $id)
		{
			if (isset($members[$id]))
				$user_profile[$id][self::FUNC . '_' . $key] = $members[$id];
			else
				$user_profile[$id][self::FUNC . '_' . $key] = 0;
		}
	}

	/**
	 * Expands the serialized award parameters and places it back in place
	 *
	 * - Groups equal "include board" awards together
	 * - Requires that the query returns awards sorted by trigger value
	 */
	private function _prep_and_group()
	{
		// For all the awards of this type
		foreach ($this->awardids as $key => $award)
		{
			// Expand out the award params
			$this->awardids[$key]['award_param'] = unserialize(($award['award_param']));

			// The triggers arrive in high to low order, but we need to "sort" by board groups as well
			if (empty($this->awardids[$key]['award_param']['boards']))
				$this->awardids[$key]['award_param']['boards'] = 'all';

			// Group the awards by board groups like all or 5|1|7
			$this->board_group[$this->awardids[$key]['award_param']['boards']][] = $this->awardids[$key];
			$this->board_award_ids[$this->awardids[$key]['award_param']['boards']][] = $this->awardids[$key]['id_award'];
		}
	}
}