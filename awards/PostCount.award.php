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
class Post_Count_Award extends Abstract_Award
{
	const FUNC = 'Post_Count';

	protected $members = array();
	protected $awardids = array();
	protected $board_group = array();
	protected $board_award_ids = array();

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
	 * @param int[] $awardids
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
			// Set the funckey :P Allows for multiple award_type via board groups .. post_count_all post_count_1|5 etc
			$area = self::FUNC . '_' . $key;

			// See what we can fulfill from the cache
			$this->award_cache_fetch($new_loaded_ids, $area);

			// Get the post count for the remaining
			$this->_post_count_board_group($this->remaining_ids, $key);

			// Save this for a while
			$this->award_cache_save($this->remaining_ids, $area);

			// See if any members have earned it
			$this->members = array();
			foreach ($new_loaded_ids as $member_id)
			{
				// Check each award level (trigger) for that group, they are sorted high to low
				foreach ($board_group as $award)
				{
					if (isset($user_profile[$member_id][$area]) && ($user_profile[$member_id][$area] >= $award['award_trigger']))
					{
						// Give this member a cupcake, if they don't already have it, and stop looking for them
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
	 * Expands the serialized award parameters and places it back for usage
	 * Groups like "include board" awards together
	 * Requires that the query returns awards sorted by trigger value
	 */
	private function _prep_and_group()
	{
		// For all the awards of this type
		foreach ($this->awardids as $key => $award)
		{
			// Expand out the award params
			$this->awardids[$key]['award_param'] = unserialize(($award['award_param']));

			// The triggers arrive in high to low order, but we need to "sort/group" by board groups as well
			if (empty($this->awardids[$key]['award_param']['boards']))
				$this->awardids[$key]['award_param']['boards'] = 'all';

			// Group the awards by board groups like all or 5|1|7
			$this->board_group[$this->awardids[$key]['award_param']['boards']][] = $this->awardids[$key];
			$this->board_award_ids[$this->awardids[$key]['award_param']['boards']][] = $this->awardids[$key]['id_award'];
		}
	}

	/**
	 * Returns the post count for the specified users from the specified boards
	 *
	 * @param int[] $new_loaded_ids
	 * @param string $key string of boards to counts posts from X|X\X
	 */
	private function _post_count_board_group($new_loaded_ids, $key)
	{
		global $user_profile;

		if (empty($new_loaded_ids))
			return;

		// All boards is just their current post count
		if ($key == 'all')
		{
			foreach ($new_loaded_ids as $member_id)
				$user_profile[$member_id][self::FUNC . '_' . $key] = $user_profile[$member_id]['posts'];
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
					'board_list' => explode('|', $key),
					'members' => $new_loaded_ids
				)
			);
			while ($row = $this->_db->fetch_assoc($request))
				$user_profile[$row['id_member']][self::FUNC . '_' . $key] = $row['posts'];

			$this->_db->free_result($request);
		}
	}
}