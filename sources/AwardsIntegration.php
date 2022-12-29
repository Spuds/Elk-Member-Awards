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
 * @version   1.1.1
 *
 */

/**
 * Profile Menu Hook, integrate_profile_areas, called from Profile.controller.php
 *
 * Used to add menu items to the profile area
 * Adds show my & view award options
 *
 * @param array $profile_areas
 */
function ipa_member_awards(&$profile_areas)
{
	global $txt, $user_info;

	// No need to show these profile option to guests, perhaps a view_awards permissions should be added?
	if ($user_info['is_guest'])
	{
		return;
	}

	loadLanguage('AwardsManage');

	$profile_areas = elk_array_insert($profile_areas, 'info', array(
		'member_awards' => array(
			'title' => $txt['awards'],
			'areas' => array(
				'showAwards' => array(
					'label' => $txt['showAwards'],
					'file' => 'AwardsProfile.controller.php',
					'controller' => 'Awards_Controller',
					'function' => 'action_showAwards',
					'permission' => array(
						'own' => 'profile_view_own',
						'any' => 'profile_view_any',
					),
				),
				'membersAwards' => array(
					'file' => 'AwardsProfile.controller.php',
					'controller' => 'Awards_Controller',
					'function' => 'action_membersAwards',
					'hidden' => (isset($_GET['area']) && $_GET['area'] !== 'membersAwards'),
					'permission' => array(
						'own' => 'profile_view_own',
						'any' => 'profile_view_any',
					),
				),
				'listAwards' => array(
					'label' => $txt['listAwards'],
					'file' => 'AwardsProfile.controller.php',
					'controller' => 'Awards_Controller',
					'function' => 'action_listAwards',
					'permission' => array(
						'own' => 'profile_view_own',
						'any' => 'profile_view_any',
					),
				),
				'requestAwards' => array(
					'file' => 'AwardsProfile.controller.php',
					'controller' => 'Awards_Controller',
					'hidden' => true,
					'function' => 'action_requestAwards',
					'permission' => array(
						'own' => 'profile_view_own',
						'any' => 'profile_view_any',
					),
				)
			)
		)
	), 'after');
}

/**
 * User Info Hook, integrate_user_info, called from Load.php
 *
 * - Used to add items to the $user_info array
 */
function iui_member_awards()
{
	global $user_info;

	$user_info['awards'] = $user_settings['awards'] ?? array();
}

/**
 * Admin hook, integrate_admin_areas, called from Admin.php
 *
 * - Adds the admin menu and all award sub actions as a sub menu
 * - hidden to all but admin, accessible via manage_award permission
 *
 * @param array $admin_areas
 */
function iaa_member_awards(&$admin_areas)
{
	global $txt, $modSettings;

	loadLanguage('AwardsManage');

	// allow members with this permission to access the menu :P
	$admin_areas['members']['permission'][] = 'manage_awards';
	$admin_areas['members']['permission'][] = 'assign_awards';

	// Create or Modify
	$modify = isset($_REQUEST['a_id'], $_REQUEST['sa']) && $_REQUEST['sa'] === 'modify';

	// our main awards menu area, under the members tab
	$admin_areas['members']['areas']['awards'] = array(
		'label' => $txt['awards'],
		'file' => 'ManageAwards.controller.php',
		'controller' => 'Awards_Controller',
		'function' => 'action_index',
		'icon' => 'awards.png',
		'permission' => array('manage_awards', 'assign_awards'),
		'subsections' => array(
			'main' => array($txt['awards_main'], array('assign_awards', 'manage_awards')),
			'categories' => array($txt['awards_categories'], 'manage_awards'),
			'modify' => array($modify ? $txt['awards_modify'] : $txt['awards_add'], 'manage_awards'),
			'assign' => array($txt['awards_assign'], array('assign_awards', 'manage_awards')),
			'assigngroup' => array($txt['awards_assign_membergroup'], 'manage_awards'),
			'assignmass' => array($txt['awards_assign_mass'], 'manage_awards'),
			'requests' => array($txt['awards_requests'] . (empty($modSettings['awards_request']) ? '' : ' (<b>' . $modSettings['awards_request'] . '</b>)'), array('assign_awards', 'manage_awards')),
			'settings' => array($txt['awards_settings'], 'manage_awards'),
		)
	);
}

/**
 * Permissions hook, integrate_load_permissions, called from ManagePermissions.php
 *
 * - Used to add new permissions
 *
 * @param array $permissionGroups
 * @param array $permissionList
 * @param array $leftPermissionGroups
 * @param array $hiddenPermissions
 * @param array $relabelPermissions
 */
function ilp_member_awards(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	global $context;

	// Permissions hook, integrate_load_permissions, called from ManagePermissions.php
	// used to add new permisssions ...
	$permissionList['membergroup']['manage_awards'] = array(false, 'member_admin', 'administrate');
	$permissionList['membergroup']['assign_awards'] = array(false, 'member_admin', 'administrate');

	$context['non_guest_permissions'] = array_merge($context['non_guest_permissions'], array('manage_awards', 'assign_awards'));
}

/**
 * Menu Button hook, integrate_menu_buttons, called from subs.php
 *
 * - Used to add top menu buttons
 *
 * @param array $buttons
 */
function imb_member_awards(&$buttons)
{
	global $txt, $scripturl;

	// Bit of a cheat but known to happen
	if (empty($txt['awards']))
	{
		$txt['awards'] = 'Awards';
	}

	// allows members with manage_awards permission to see a menu item since the admin menu is hidden for them
	$buttons['mlist']['sub_buttons']['awards'] = array(
		'title' => $txt['awards'],
		'href' => $scripturl . '?action=admin;area=awards;sa=main',
		'show' => (allowedTo('manage_awards') || allowedTo('assign_awards')),
	);
}

/**
 * Load Member Data hook, integrate_load_member_data, Called from load.php
 *
 * - Used to add columns / tables to the query so additional data can be loaded for a set
 *
 * @param int[] $new_loaded_ids
 * @param string $set
 */
function iamd_member_awards($new_loaded_ids, $set)
{
	global $user_profile, $modSettings;

	// Give them all nothing to start
	// @todo is this needed outside of minimal ?
	foreach ($new_loaded_ids as $id)
	{
		$user_profile[$id]['awards'] = array();
		$user_profile[$id]['awardlist'] = array();
	}

	// I'm sorry, but I've got to stick this award somewhere ...
	if ($modSettings['awards_in_post'] > 0 && $set !== 'minimal' && !empty($modSettings['awards_enabled']))
	{
		require_once(SUBSDIR . '/AwardsManage.subs.php');
		loadCSSFile('awards.css');
		AwardsLoad($new_loaded_ids);
		AwardsAutoCheck($new_loaded_ids);
	}
}

/**
 * Load data to Member Context, integrate_member_context
 *
 * Called from load.php
 * Used to add items to the $memberContext array
 *
 * @param int $user
 */
function imc_member_awards($user)
{
	global $memberContext, $user_profile, $context;

	// @todo reference needed here?, like &$user_profile[$user]['awards']
	if ($context['loadMemberContext_set'] !== 'minimal')
	{
		$memberContext[$user]['awards'] = $user_profile[$user]['awards'];
	}
}

/**
 * Whos online hook
 *
 * integrate_whos_online_allowed
 *
 * - Used to add action view permissions checks to the who's online listing
 *
 * @param array $allowedActions
 */
function iwoa_member_awards(&$allowedActions)
{
	$allowedActions['awards'] = array('manage_awards');
}

/**
 * Used to place the awards in to the users post profile
 *
 * - Directly injects the changes to the generic template which affects the
 * user poster area in post/pm/etc.
 * - Builds the signature area, used in the display template, just for posts
 *
 * @param string $poster_div
 * @param string $message
 */
function injectProfileAwards(&$poster_div, $message)
{
	global $txt, $scripturl, $modSettings, $context, $settings;

	// Showing member awards in all the wrong places
	if (!empty($message['member']['awards']) && $modSettings['awards_in_post'] > 0)
	{
		// Show their profile awards, maybe for badges and the like
		$awards_link = array();

		// Load all this members award areas
		foreach ($message['member']['awards'] as $award)
		{
			// Above
			if ($award['location'] == 2)
			{
				$awards_link[2][] = '
					<a href="' . $scripturl . $award['more'] . '">
						<img src="' . dirname($scripturl) . $award['img'] . '" alt="' . $award['award_name'] . '" title="' . $award['description'] . '" />
					</a> ';
			}
			// Below
			elseif ($award['location'] == 1)
			{
				$awards_link[1][] = '
					<a href="' . $scripturl . $award['more'] . '">
						<img src="' . dirname($scripturl) . $award['img'] . '" alt="' . $award['award_name'] . '" title="' . $award['description'] . '" />
					</a> ';
			}
			// Signature
			elseif ($award['location'] == 3)
			{
				$awards_link[3][] = '
					<a href="' . $scripturl . $award['more'] . '">
						<img src="' . dirname($scripturl) . $award['img'] . '" alt="' . $award['award_name'] . '" title="' . $award['description'] . '" />
					</a> ';
			}
		}

		// Above profile awards ...
		if (!empty($awards_link[2]))
		{
			// Only allow the number the admin set
			array_splice($awards_link[2], $modSettings['awards_in_post']);

			// Specific style class chosen?
			$style = (empty($modSettings['awards_aboveavatar_format']) || $modSettings['awards_aboveavatar_format'] == 1)
				? 'award_top award_poster_1'
				: ($modSettings['awards_aboveavatar_format'] == 2
					? 'award_top award_poster_2"' : 'award_top award_poster_3');

			$award_output = '
				<li class="listlevel1">
					<fieldset class="' . $style . '">';

			// Title for the above awards "box"
			if (!empty($modSettings['awards_aboveavatar_title']))
			{
				$award_output .= '
						<legend>
							<a href="' . $scripturl . '?action=profile;area=showAwards;u=' . $message['member']['id'] . '" title="' . $txt['awards'] . '">' . $modSettings['awards_aboveavatar_title'] . '</a>
						</legend>';
			}

			$award_output .= implode('', $awards_link[2]) . '
					</fieldset>
				</li>';

			// Insert the award output in the appropriate spot, right above the dropdown
			$find = '<li class="listlevel1 poster_avatar">';
			$replace = $award_output . $find;
			$poster_div = awards_str_replace_once($find, $replace, $poster_div);
		}

		// Below profile awards ...
		if (!empty($awards_link[1]))
		{
			// Only allow the number the admin set
			array_splice($awards_link[1], $modSettings['awards_in_post']);

			// Style for this area?
			$style = (empty($modSettings['awards_belowavatar_format']) || $modSettings['awards_belowavatar_format'] == 1)
				? 'award_bottom award_poster_1'
				: ($modSettings['awards_belowavatar_format'] == 2
					? 'award_bottom award_poster_2"' : 'award_bottom award_poster_3');

			$award_output = '
				<li>
					<fieldset class="' . $style . '">';

			if (!empty($modSettings['awards_belowavatar_title']))
			{
				$award_output .= '
						<legend>
							<a href="' . $scripturl . '?action=profile;area=showAwards;u=' . $message['member']['id'] . '" title="' . $txt['awards'] . '">' . $modSettings['awards_belowavatar_title'] . '</a>
						</legend>';
			}

			$award_output .= implode('', $awards_link[1]) . '
					</fieldset>
				</li>';

			// Insert the award output in the appropriate spot, at the end of the poster div sounds good
			$poster_div .= $award_output;
		}

		// Show their signature awards?
		if (!empty($awards_link[3]))
		{
			// Only allow the number set
			array_splice($awards_link[3], $modSettings['awards_in_post']);

			// Style for the sigs?
			$style = (empty($modSettings['awards_signature_format']) || $modSettings['awards_signature_format'] == 1)
				? 'award_signature_1'
				: ($modSettings['awards_signature_format'] == 2
					? 'award_signature_2"' : 'award_signature_3');

			$award_output = '
					<div class="signature">
						<fieldset class="' . $style . '">';

			// Title for the signature area?
			if (!empty($modSettings['awards_signature_title']))
			{
				$award_output .= '
							<legend>
								<a href="' . $scripturl . '?action=profile;area=showAwards;u=' . $message['member']['id'] . '" title="' . $txt['awards'] . '">' . $modSettings['awards_signature_title'] . '</a>
							</legend>';
			}

			$award_output .= implode('', $awards_link[3]) . '
						</fieldset>
					</div>';

			// Just make it available to the template, can't inject this one from here
			$context['award']['signature'] = $award_output;

		}

		// Give them a link to see all the awards
		$find = '<ol>';
		$replace = $find . '<li><a href="' . $scripturl . '?action=profile;area=showAwards;u=' . $message['member']['id'] . '" title="' . $txt['awards'] . '"><img src="' . $settings['images_url'] . '/award.png" alt="' . $txt['awards'] . '" title="' . $txt['awards'] . '" /></a></li>';
		$poster_div = awards_str_replace_once($find, $replace, $poster_div);
	}
}

/**
 * Helper function for string replacement
 *
 * @param string $needle
 * @param string $replace
 * @param string $haystack
 */
function awards_str_replace_once($needle, $replace, $haystack)
{
	// Looks for the first occurrence of $needle in $haystack and replaces it with $replace
	$pos = strpos($haystack, $needle);
	if ($pos === false)
	{
		return $haystack;
	}

	return substr_replace($haystack, $replace, $pos, strlen($needle));
}