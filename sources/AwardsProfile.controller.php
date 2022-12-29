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
 * @version   1.1
 *
 */

use BBC\ParserWrapper;

/**
 * This is the awards profile controller class.
 * This file handles the profile side of Awards.
 */
class Awards_Controller extends Action_Controller
{
	/**
	 * Entry point function for Member Awards profile sections, makes sure its on
	 */
	public function pre_dispatch()
	{
		global $modSettings, $user_info;

		// If Member Awards is disabled, we don't go any further
		if (empty($modSettings['awards_enabled']) && !$user_info['is_admin'])
		{
			throw new Elk_Exception('feature_disabled', 'fatal');
		}

		// Some things we will need
		loadLanguage('AwardsManage');
		loadTemplate('AwardsProfile');
		loadCSSFile('awards.css');

		require_once(SUBSDIR . '/Awards.subs.php');
	}

	public function action_index()
	{
		global $context;

		// Right now does nothing, but we need it :D
		$subActions = array(
			'showAwards' => array($this, 'action_showAwards', 'permission' => array('profile_view_own', 'profile_view_any')),
			'membersAwards' => array($this, 'action_membersAwards', 'permission' => array('profile_view_own', 'profile_view_any')),
			'listAwards' => array($this, 'action_listAwards', 'permission' => array('profile_view_own', 'profile_view_any')),
			'requestAwards' => array($this, 'action_requestAwards', 'permission' => array('profile_view_own', 'profile_view_any')),
		);

		// Start up the controller, if we ever get here
		$action = new Action();

		// Default to sub-action 'main'
		$subAction = $action->initialize($subActions, 'showAwards');
		$context['sub_action'] = $subAction;

		// Call the right function
		$action->dispatch($subAction);
	}

	/**
	 * Shows an individuals awards by category (awards album)
	 */
	public function action_showAwards()
	{
		global $context, $txt, $scripturl;

		$memID = isset($REQUEST['u']) ? (int) $REQUEST['u'] : currentMemberID();

		// Do they want to make a favorite?
		if (isset($_GET['makeFavorite']) && allowedTo(array('profile_extra_any', 'profile_extra_own')))
		{
			// Check session
			checkSession('get');

			// Clean
			$award_id = (int) $_GET['in'];
			$makefav = !empty($_GET['makeFavorite']) ? 1 : 0;

			// Make it a favorite
			AwardsSetFavorite($memID, $award_id, $makefav);

			// To make changes appear redirect back to that page
			redirectexit('action=profile;area=showAwards;u=' . $memID);
		}

		// Count the number of items in the database for create index
		$context['count_awards'] = AwardsCountMembersAwards($memID);

		// Calculate the number of results to pull up.
		$max_awards = 25;

		// Construct the page index
		$start = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;
		$context['page_index'] = constructPageIndex($scripturl . '?action=profile;area=showAwards;u=' . $memID, $start, $context['count_awards'], $max_awards);

		// Load the individual and group awards
		$context['categories'] = AwardsLoadMembersAwards($start, $max_awards, $memID);

		// And off to the template we go
		$context['page_title'] = $txt['profile'] . ' - ' . $txt['awards_title'];
		$context['sub_template'] = 'awards';
		$context['allowed_fav'] = ($context['user']['is_owner'] && allowedTo('profile_view_own')) || allowedTo('profile_extra_any');
	}

	/**
	 * Shows all members that have received an award
	 *
	 * - Action from profile when viewing available user awards
	 */
	public function action_membersAwards()
	{
		global $context, $scripturl, $txt;

		// Are they allowed to see the memberlist at all?
		isAllowedTo('view_mlist');

		// Load in our helper functions
		require_once(SUBSDIR . '/GenericList.class.php');

		// Load this awards details
		$id = (int) $_REQUEST['a_id'];
		$context['award'] = AwardsLoadAward($id);

		// Build the listoption array to display the data, in this case who has this award
		$listOptions = array(
			'id' => 'view_profile_assigned',
			'title' => $txt['awards_showmembers'] . ': ' . $context['award']['award_name'],
			'items_per_page' => 25,
			'no_items_label' => $txt['awards_no_assigned_members2'],
			'base_href' => $scripturl . '?action=profile;area=membersAwards;a_id=' . $id,
			'default_sort_col' => 'username',
			'get_items' => array(
				'function' => 'AwardsLoadMembers',
				'params' => array(
					$id,
				),
			),
			'get_count' => array(
				'function' => 'AwardsLoadMembersCount',
				'params' => array(
					$id,
				),
			),
			'columns' => array(
				'username' => array(
					'header' => array(
						'value' => $txt['username'],
					),
					'data' => array(
						'db' => 'real_name',
					),
					'sort' => array(
						'default' => 'm.real_name ',
						'reverse' => 'm.real_name DESC',
					),
				),
				'date' => array(
					'header' => array(
						'value' => $txt['awards_date'],
					),
					'data' => array(
						'db' => 'date_received',
					),
					'sort' => array(
						'default' => 'a.date_received DESC',
						'reverse' => 'a.date_received',
					),
				),
			),
			'additional_rows' => array(
				array(
					'position' => 'top_of_list',
					'value' => '<br class="clear" />',
				),
			),
		);

		// Set the context values
		$context['page_title'] = $txt['awards_title'] . ' - ' . $context['award']['award_name'];
		$context['sub_template'] = 'awards_members';

		// Create the list.
		createList($listOptions);
	}

	/**
	 * Shows all available awards that they can acheive / request
	 */
	public function action_listAwards()
	{
		global $context, $txt, $scripturl, $user_info, $user_profile;

		// Number of awards in the system
		$countAwards = AwardsCount();

		// Calculate the number of results to pull up.
		$maxAwards = 20;

		// Construct the page index
		$context['page_index'] = constructPageIndex($scripturl . '?action=profile;area=listAwards', $_REQUEST['start'], $countAwards, $maxAwards);
		$start = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

		// Array of their awards to prevent a request for something they have
		$awardcheck = array();
		$awards = $user_profile[$user_info['id']]['awards'] ?? array();
		foreach ($awards as $award)
		{
			$awardcheck[$award['id']] = 1;
		}

		// Select the awards and their categories.
		$context['categories'] = AwardsListAll($start, $maxAwards, $awardcheck);

		$context['page_title'] = $txt['profile'] . ' - ' . $txt['awards_title'];
		$context['sub_template'] = 'awards_list';
	}

	/**
	 * Allow a member to request an award and add it to the approval queue
	 */
	public function action_requestAwards()
	{
		global $context, $txt, $user_info, $user_profile, $modSettings;

		// First step, load the details of the requested award
		if (!isset($_GET['step']) || $_GET['step'] != 2)
		{
			// Load this awards details for the form
			$id = (int) $_REQUEST['a_id'];
			$context['award'] = AwardsLoadAward($id);

			// Not requestable, then how did we get here?
			if (empty($context['award']['requestable']))
			{
				throw new Elk_Exception('awards_error_not_requestable', 'general');
			}

			// Dude already has this one?
			foreach ($user_profile[$user_info['id']]['awards'] as $award)
			{
				if ($award['id'] == $id)
				{
					throw new Elk_Exception('awards_error_have_already', 'general');
				}
			}

			// Set the context values
			$parser = ParserWrapper::instance();
			$context['award']['description'] = $parser->parseMessage($context['award']['description'], true);
			$context['step'] = 1;
			$context['page_title'] = $txt['awards_request_award'] . ' - ' . $context['award']['award_name'];
			$context['sub_template'] = 'awards_request';
		}
		// step '2', they have actually demanded an award!
		elseif ((int) $_GET['step'] === 2)
		{
			// Check session.
			checkSession();

			// Clean those dirty pigs.
			$id = (int) $_POST['id_award'];
			$comments = strtr(Util::htmlspecialchars($_POST['comments'], ENT_QUOTES), array("\n" => '<br />', '"' => '&quot;', '<' => '&lt;', '>' => '&gt;', '  ' => ' &nbsp;'));
			censor($comments);
			$date = date('Y-m-d');

			// let's see if the award exists, silly hackers
			$context['award'] = AwardsLoadAward($id);

			// Not requestable? how did we get here?
			if (empty($context['award']['requestable']))
			{
				throw new Elk_Exception('awards_error_not_requestable', 'general');
			}

			// can't ask for what you have
			foreach ($user_profile[$user_info['id']]['awards'] as $award)
			{
				if ($award['id'] == $id)
				{
					throw new Elk_Exception('awards_error_have_already', 'general');
				}
			}

			// If we made it this far insert /replace such that it can be reviewed.
			AwardsMakeRequest($id, $date, $comments);

			updateSettings(array(
				'awards_request' => $modSettings['awards_request'],
			));

			// Redirect to their awards page.
			redirectexit('action=profile;area=showAwards;u=' . $user_info['id']);
		}
	}
}
