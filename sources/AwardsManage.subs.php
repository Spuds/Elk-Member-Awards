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
 * Loads all the awards for the members in the list
 *
 * @param int[] $new_loaded_ids
 */
function AwardsLoad($new_loaded_ids)
{
	global $user_profile, $modSettings;

	$db = database();

	$group_awards = array();
	$group_awards_details = array();

	// Build our database request to load all existing member awards for this group of members, including group awards
	$request = $db->query('', '
		SELECT
			am.id_member, am.active, am.id_group,
			aw.id_award, aw.award_name, aw.award_function, aw.description, aw.minifile, aw.award_trigger,
			aw.award_type, aw.award_param, aw.award_location
		FROM {db_prefix}awards_members AS am
			INNER JOIN {db_prefix}awards AS aw ON (aw.id_award = am.id_award)
		WHERE (am.id_member IN({array_int:members}) OR am.id_member < 0)
			AND am.active = {int:active}
		ORDER BY am.favorite DESC, am.date_received DESC',
		array(
			'members' => $new_loaded_ids,
			'active' => 1
		)
	);
	// Fetch the award info just once
	while ($row = $db->fetch_assoc($request))
	{
		// Prepare the data
		$temp = array(
			'id' => $row['id_award'],
			'id_group' => $row['id_group'],
			'award_name' => $row['award_name'],
			'award_function' => $row['award_function'],
			'parameters' => unserialize($row['award_param']),
			'description' => parse_bbc($row['description']),
			'more' => '?action=profile;area=membersAwards;a_id=' . $row['id_award'],
			'href' => '?action=profile;area=showAwards;u=' . $row['id_member'],
			'minifile' => $row['minifile'],
			'img' => '/' . (empty($modSettings['awards_dir']) ? '' : $modSettings['awards_dir'] . '/') . $row['minifile'],
			'trigger' => $row['award_trigger'],
			'award_type' => $row['award_type'],
			'location' => $row['award_location'],
			'active' => $row['active']
		);

		// Track "group" awards separately
		if ($row['id_member'] < 0)
		{
			$group_awards[] = $row['id_group'];
			$group_awards_details[$row['id_group']] = $temp;
			$group_awards_details[$row['id_group']]['title'] = strip_tags($group_awards_details[$row['id_group']]['description']);
		}
		else
		{
			$user_profile[$row['id_member']]['awards'][$row['id_award']] = $temp;
			$user_profile[$row['id_member']]['awards'][$row['id_award']]['title'] = strip_tags($user_profile[$row['id_member']]['awards'][$row['id_award']]['description']);

			// Keep an array of just active awards for this member to make life easier
			if (!empty($row['active']))
			{
				$user_profile[$row['id_member']]['awardlist'][] = $row['id_award'];
			}
		}
	}
	$db->free_result($request);

	// Are any group awards?
	if (!empty($group_awards))
	{
		AwardsLoadGroupAwards($new_loaded_ids, $group_awards, $group_awards_details);
	}
}

/**
 * Assign group awards to members in those groups
 *
 *  - checks to see if the award was individually assigned as well to
 * avoid double awards
 *
 * @param int[] $new_loaded_ids
 * @param int[] $group_awards
 * @param array[] $group_awards_details
 */
function AwardsLoadGroupAwards($new_loaded_ids, $group_awards, $group_awards_details)
{
	global $user_profile;

	// check each member to see if they are a member of a group that has a group awards
	foreach ($new_loaded_ids as $member_id)
	{
		// Make an array of this users groups
		$user_profile[$member_id]['groups'] = array($user_profile[$member_id]['id_group'], $user_profile[$member_id]['id_post_group']);
		if (!empty($user_profile[$member_id]['additional_groups']))
		{
			$user_profile[$member_id]['groups'] = array_merge($user_profile[$member_id]['groups'], explode(',', $user_profile[$member_id]['additional_groups']));
		}

		// See if any of this members groups match a group award
		$give_group_awards = array_intersect($user_profile[$member_id]['groups'], $group_awards);
		if (!empty($give_group_awards))
		{
			// Woohoo ... a group award for you *IF* it was not assigned individually, you only get it once ;)
			foreach ($give_group_awards as $groupaward_id)
			{
				if (!isset($user_profile[$member_id]['awards'][$group_awards_details[$groupaward_id]['id']]))
				{
					$user_profile[$member_id]['awards'][$groupaward_id] = $group_awards_details[$groupaward_id];
				}
			}
		}
	}
}

/**
 * Master auto award function, runs the show
 *
 * - Loads all of the defined auto awards and groups them
 * - Uses the cache when it can
 * - Determines if any members in the list have earned any of the auto awards
 *
 * @param int[] $new_loaded_ids
 */
function AwardsAutoCheck($new_loaded_ids)
{
	global $modSettings;

	$db = database();

	// See if we already have the available auto awards in the cache
	$autoawards = cache_get_data('awards:autoawards', 4 * 3600);
	$autoawardsid = cache_get_data('awards:autoawardsid', 4 * 3600);
	$autoawardsprofiles = cache_get_data('awards:autoawardsprofiles', 4 * 3600);

	// Like will need these
	require_once(SUBSDIR . '/awards/AbstractAward.class.php');
	require_once(SUBSDIR . '/Awards.subs.php');

	if ($autoawards === null || $autoawardsid === null || $autoawardsprofiles === null)
	{
		// Init
		$autoawards = array();
		$autoawardsid = array();

		// Load all the defined auto awards
		// The key is the trigger desc sort, this allows us to use 1 query for that auto award 'type',
		// all others will be a subset of that
		$request = $db->query('', '
			SELECT
				id_award, award_name, award_function, award_trigger, award_param, award_type, id_profile
			FROM {db_prefix}awards
			WHERE award_type = {int:type}
			ORDER BY award_type DESC, award_trigger DESC',
			array(
				'type' => 2,
			)
		);
		// Build up the auto awards array
		while ($row = $db->fetch_assoc($request))
		{
			// holds all the awards information for each award type
			$autoawards[$row['award_function']][] = $row;

			// holds all the possible award id's for a given award type.
			require_once(SUBSDIR . '/Awards.subs.php');
			$autoawardsid[$row['award_function']][] = (int) $row['id_award'];
		}
		$db->free_result($request);

		// And the profiles
		$autoawardsprofiles = AwardsLoadProfiles();

		// Save it for 4 hours, really could be longer since it only changes when a new auto award is added / edited.
		if (!empty($modSettings['cache_enable']))
		{
			cache_put_data('awards:autoawards', $autoawards, 4 * 3600);
			cache_put_data('awards:autoawardsid', $autoawardsid, 4 * 3600);
			cache_put_data('awards:autoawardsprofiles', $autoawardsprofiles, 4 * 3600);
		}
	}

	// Now lets do something with each award type
	foreach ($autoawards as $award_type => $awardids)
	{
		// Start an instance of this award_type class
		$award = instantiate_award($award_type, $awardids, $autoawardsprofiles);

		// Call its main processing function
		$award->process($new_loaded_ids);
	}
	/*
		switch ($award_type)
		{
			case 2:

			case 3:
				// Top posters 1-N
				AwardsTopPosters_1_N($awardids[0]['award_trigger']);
				$members = AwardsAutoAssignMembers($awardids, $new_loaded_ids, 'top_posters', true);

				// If we found new awards to assign, do so
				if (!empty($members))
					AwardsAutoAssign($members, $award_type, $autoawardsid[$award_type]);
				break;
			case 4:
				// Topic count based awards
				AwardsTopicsStarted($new_loaded_ids);
				$members = AwardsAutoAssignMembers($awardids, $new_loaded_ids, 'num_topics');

				// If we found new awards to assign, do so
				if (!empty($members))
					AwardsAutoAssign($members, $award_type, $autoawardsid[$award_type]);
				break;
			case 5:
				// Top topic starters 1-N
				AwardsTopTopicStarter_1_N($awardids[0]['award_trigger']);
				$members = AwardsAutoAssignMembers($awardids, $new_loaded_ids, 'top_topics', true);

				// If we found new awards to assign, do so
				if (!empty($members))
					AwardsAutoAssign($members, $award_type, $autoawardsid[$award_type]);
				break;
			case 6:
				// Most time wasted on the site 1-N,
				AwardsTopTimeon_1_N($awardids[0]['award_trigger']);
				$members = AwardsAutoAssignMembers($awardids, $new_loaded_ids, 'top_time', true);

				// If we found new awards to assign, do so
				if (!empty($members))
					AwardsAutoAssign($members, $award_type, $autoawardsid[$award_type]);
				break;
			case 7:
				// Member join date seniority
				AwardsSeniority($new_loaded_ids);
				$members = AwardsAutoAssignMembers($awardids, $new_loaded_ids, 'join_length');

				// If we found new awards to assign, do so
				if (!empty($members))
					AwardsAutoAssign($members, $award_type, $autoawardsid[$award_type]);
				break;
			case 8:
				// People like me dammit!
				AwardsPopularity($new_loaded_ids);
				$members = AwardsAutoAssignMembers($awardids, $new_loaded_ids, 'popularity');

				// If we found new awards to assign, do so
				if (!empty($members))
					AwardsAutoAssign($members, $award_type, $autoawardsid[$award_type]);
				break;
		}
	}*/
}

/**
 * Given the award limits, the members to check and the area, does the comparison
 *
 * - uses the data set in $user_profile by the various award querys (topic, post, timeon, etc)
 * - Returns the member ids, from the supplied list, of any who have reached a threshold
 *
 * @param int[] $awardids
 * @param int[] $new_loaded_ids
 * @param string $area
 * @param boolean $one_to_n
 */
function AwardsAutoAssignMembers($awardids, $new_loaded_ids, $area, $one_to_n = false)
{
	global $user_profile;

	$members = array();

	// 1-n awards need to be ascending order, others use the default descending order
	if ($one_to_n)
	{
		$awardids = array_reverse($awardids);
	}

	// For all the members in this request
	foreach ($new_loaded_ids as $member_id)
	{
		// see if they have enough of '$areas' to hit one of the trigger levels
		foreach ($awardids as $award)
		{
			// normal value based awards
			if (!$one_to_n)
			{
				if (isset($user_profile[$member_id][$area]) && ($user_profile[$member_id][$area] >= $award['award_trigger']))
				{
					// Give this member a cupcake, if they don't already have it, and stop looking for more
					if (!in_array($award['id_award'], $user_profile[$member_id]['awardlist']))
					{
						$members[$member_id] = (int) $award['id_award'];
					}
					break;
				}
			}
			// 1 to n position based awards
			else
			{
				if (isset($user_profile[$member_id][$area]) && ($user_profile[$member_id][$area] <= $award['award_trigger']))
				{
					// Give this member a hoho, if they don't already have it, and stop looking for more
					if (!in_array($award['id_award'], $user_profile[$member_id]['awardlist']))
					{
						$members[$member_id] = (int) $award['id_award'];
					}
					break;
				}
			}
		}
	}

	return $members;
}

/**
 * Returns the number of topics started for each member in memberlist
 *
 * @param int[] $memberlist
 * @param int $ttl
 */
function AwardsTopicsStarted($memberlist, $ttl = 300)
{
	// Load up how many topics this list of users has started.
	global $modSettings, $user_profile;

	$db = database();

	// Init with all members in the query
	$temp = $memberlist;

	// Lets see if this is cached in our "cache in a cache"tm :P
	if (($awards_topic_started = cache_get_data('awards:topic_started', $ttl)) != null)
	{
		// Reset this since we have a cache, we will build it for only the members we need data on
		$temp = array();

		// We have *some* cache data, see what members we have data for, and if its not stale use it
		foreach ($memberlist as $member)
		{
			if (isset($awards_topic_started[$member]['update']))
			{
				// See if this member entry, found in the cache is still valid
				if ($awards_topic_started[$member]['update'] >= (time() - $ttl))
				{
					$user_profile[$member]['num_topics'] = $awards_topic_started[$member]['num_topics'];
				}
				else
				{
					// Its a stale entry in the cache, add it to our lookup and drop if from the cache array
					unset($awards_topic_started[$member]);
					$temp[] = $member;
				}
			}
			else
			{
				$temp[] = $member;
			}
		}
	}

	// If we did not find them all in the cache, or it was stale then do the query
	if (!empty($temp))
	{
		// Number of topics started.
		$request = $db->query('', '
			SELECT
				COUNT(*) AS num_topics, id_member_started
			FROM {db_prefix}topics
			WHERE id_member_started IN ({array_int:memberlist})' . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0
				? '
				AND id_board != {int:recycle_board}' : '') . '
			GROUP BY id_member_started',
			array(
				'memberlist' => $temp,
				'recycle_board' => $modSettings['recycle_board'],
			)
		);
		// Load them in to user_profile
		while ($row = $db->fetch_assoc($request))
		{
			$user_profile[$row['id_member_started']]['num_topics'] = $row['num_topics'];

			// add them to our existing cache array
			$awards_topic_started[$row['id_member_started']]['num_topics'] = $row['num_topics'];
			$awards_topic_started[$row['id_member_started']]['update'] = time();
		}
		$db->free_result($request);
	}

	// Put this back in the cache
	cache_put_data('awards:topic_started', $awards_topic_started, $ttl);
}

/**
 * Returns the top X posters in $user_profile
 *
 * @param int $limit
 */
function AwardsTopPosters_1_N($limit = 10)
{
	global $user_profile;

	$db = database();

	// Top Posters 1-N, basis from stats.php
	// Try to cache this part of the query since its generic and slow
	if (($members = cache_get_data('awards_top_posters', 360)) == null)
	{
		$request = $db->query('', '
			SELECT
				id_member, posts
			FROM {db_prefix}members
			WHERE posts > {int:no_posts}
			ORDER BY posts DESC
			LIMIT {int:limit}',
			array(
				'no_posts' => 0,
				'limit' => $limit
			)
		);
		$poster_number = 0;
		$members = array();
		while ($row = $db->fetch_assoc($request))
		{
			$poster_number++;
			$members[$row['id_member']] = $poster_number;
		}
		$db->free_result($request);

		// save this one for the next few mins ....
		cache_put_data('awards_top_posters', $members, 360);
	}

	if (empty($members))
	{
		$members = array(0 => 0);
	}

	// Load them up so we can see if the kids have won a new toy
	foreach ($members as $id_member => $poster_number)
	{
		$user_profile[$id_member]['top_posters'] = $poster_number;
	}
}

/**
 * Returns the top X topic starters in $user_profile
 *
 * @param int $limit
 */
function AwardsTopTopicStarter_1_N($limit = 10)
{
	global $modSettings, $user_profile;

	$db = database();

	// Code basis from stats.php
	// Try to cache this part of the query when possible, because it's a bit of a pig :8
	if (($members = cache_get_data('awards_top_starters', 360)) == null)
	{
		$request = $db->query('', '
			SELECT
				id_member_started, COUNT(*) AS hits
			FROM {db_prefix}topics' . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? '
			WHERE id_board != {int:recycle_board}' : '') . '
			GROUP BY id_member_started
			ORDER BY hits DESC
			LIMIT {int:limit}',
			array(
				'recycle_board' => $modSettings['recycle_board'],
				'limit' => $limit
			)
		);
		$members = array();
		while ($row = $db->fetch_assoc($request))
		{
			$members[$row['id_member_started']] = $row['hits'];
		}
		$db->free_result($request);

		// save this one for the next few mins ....
		cache_put_data('awards_top_starters', $members, 360);
	}

	// Need to have something ....
	if (empty($members))
	{
		$members = array(0 => 0);
	}

	// And now get the top 1-N topic starter.
	$request = $db->query('top_topic_starters', '
		SELECT
			id_member
		FROM {db_prefix}members
		WHERE id_member IN ({array_int:member_list})
		ORDER BY FIND_IN_SET(id_member, {string:top_topic_posters})
		LIMIT {int:limit}',
		array(
			'member_list' => array_keys($members),
			'top_topic_posters' => implode(',', array_keys($members)),
			'limit' => $limit
		)
	);
	// Make them available for use in user_profile
	$topic_number = 0;
	while ($row = $db->fetch_assoc($request))
	{
		$topic_number++;
		$user_profile[$row['id_member']]['top_topics'] = $topic_number;
	}
	$db->free_result($request);
}

/**
 * Returns the top X time on line members in $user_profile
 *
 * @param int $limit
 */
function AwardsTopTimeon_1_N($limit = 10)
{
	global $user_profile;

	$db = database();

	// The time on line 1-N list will not change that often, so cache it for a bit
	$temp = cache_get_data('awards_total_time_members', 600);
	$request = $db->query('', '
		SELECT
			id_member, total_time_logged_in
		FROM {db_prefix}members' . (!empty($temp) ? '
		WHERE id_member IN ({array_int:member_list_cached})' : '') . '
		ORDER BY total_time_logged_in DESC
		LIMIT {int:limit}',
		array(
			'member_list_cached' => $temp,
			'limit' => $limit
		)
	);
	// Init
	$time_number = 0;
	$temp2 = array();
	// Make them available for use to use in user_profile
	while ($row_members = $db->fetch_assoc($request))
	{
		$temp2[] = (int) $row_members['id_member'];
		if ($time_number++ >= $limit)
		{
			continue;
		}

		$user_profile[$row_members['id_member']]['top_time'] = $time_number;
	}
	$db->free_result($request);

	// Cache the ones we found for a bit, just so we don't have to look again.
	if ($temp !== $temp2)
	{
		cache_put_data('awards_total_time_members', $temp2, 600);
	}
}

/**
 * Returns the top X join date based in $user_profile
 *
 * @param int[] $memberlist
 */
function AwardsSeniority($memberlist)
{
	global $user_profile;

	// Load up how long this member has been a member X.x years.months
	$now = time();

	foreach ($memberlist as $member)
	{
		$user_profile[$member]['join_length'] = AwardsDateDiff($user_profile[$member]['date_registered'], $now);
	}
}

/**
 * Returns the karma level for the given list of users
 *
 * @param int[] $memberlist
 */
function AwardsPopularity($memberlist)
{
	global $user_profile;

	// Get members total positive karma, the values are set via loadusersettings for us
	foreach ($memberlist as $member)
	{
		$kg = !empty($user_profile[$member]['karma_good']) ? $user_profile[$member]['karma_good'] : 0;
		$kb = !empty($user_profile[$member]['karma_bad']) ? $user_profile[$member]['karma_bad'] : 0;
		$user_profile[$member]['popularity'] = $kg - $kb;
	}
}

/**
 * Utility function to get the x.y years between to dates e.g. 1.5 is 1 year 6 months
 *
 * @param string $time1
 * @param string $time2
 */
function AwardsDateDiff($time1, $time2)
{
	$date1 = new DateTime(date('d-M-Y', $time1));
	$date2 = new DateTime(date('d-M-Y', $time2));
	$diff = $date1->diff($date2);

	return $diff->y + $diff->m;
}