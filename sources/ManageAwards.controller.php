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

use BBC\ParserWrapper;

/**
 * This is the awards' administration controller class.
 *
 * - This file handles the admin side of Awards.
 */
class Awards_Controller extends Action_Controller
{
	/**
	 * Entry point function for Member Awards, permission checks, makes sure it is on
	 */
	public function pre_dispatch()
	{
		global $context, $txt, $modSettings, $user_info;

		// If Member Awards is disabled, we don't go any further unless you are the admin
		if (empty($modSettings['awards_enabled']) && !$user_info['is_admin'])
		{
			throw new Elk_Exception('feature_disabled', 'fatal');
		}

		// It's on, but are we allowed to manage or assign?
		isAllowedTo(array('manage_awards', 'assign_awards'));

		// Some things we will need, template, language, css
		loadLanguage('AwardsManage');
		loadTemplate('AwardsManage');
		loadCSSFile('awards.css');

		// And some files we need
		require_once(SUBSDIR . '/Awards.subs.php');

		// Our award types array, do not mess with these
		$context['award_types'] = array(
			array('id' => 1, 'name' => $txt['awards_manual'], 'desc' => $txt['awards_manual_desc']),
			array('id' => 2, 'name' => $txt['awards_post_count'], 'desc' => $txt['awards_post_count_desc']),
			array('id' => 3, 'name' => $txt['awards_top_posters'], 'desc' => $txt['awards_top_posters_desc']),
			array('id' => 4, 'name' => $txt['awards_topic_count'], 'desc' => $txt['awards_topic_count_desc']),
			array('id' => 5, 'name' => $txt['awards_top_topic_starters'], 'desc' => $txt['awards_top_topic_starters_desc']),
			array('id' => 6, 'name' => $txt['awards_time_online'], 'desc' => $txt['awards_time_online_desc']),
			array('id' => 7, 'name' => $txt['awards_member_since'], 'desc' => $txt['awards_member_since_desc']),
			array('id' => 8, 'name' => $txt['awards_karma_level'], 'desc' => $txt['awards_karma_level_desc']),
		);

		// Our allowed placement array
		$context['award_placements'] = array(
			array('id' => 1, 'name' => $txt['awards_image_placement_below']),
			array('id' => 2, 'name' => $txt['awards_image_placement_above']),
			array('id' => 3, 'name' => $txt['awards_image_placement_sig']),
			array('id' => 4, 'name' => $txt['awards_image_placement_off'])
		);

		// And our placement format array
		$context['award_formats'] = array(
			array('id' => 1, 'name' => $txt['awards_format_full_frame']),
			array('id' => 2, 'name' => $txt['awards_format_heading']),
			array('id' => 3, 'name' => $txt['awards_format_no_frame']),
		);
	}

	/**
	 * Default action method.
	 *
	 * If a specific method wasn't directly called, forwards to main.
	 */
	public function action_index()
	{
		global $context, $txt, $modSettings;

		$subActions = array(
			'main' => array($this, 'action_awards_main', 'permission' => array('assign_awards', 'manage_awards')),
			'categories' => array($this, 'action_list_categories', 'permission' => 'manage_awards'),
			'modify' => array($this, 'action_modify', 'permission' => 'manage_awards'),
			'assign' => array($this, 'action_assign', 'permission' => array('assign_awards', 'manage_awards')),
			'assigngroup' => array($this, 'action_assign_member_group', 'permission' => 'manage_awards'),
			'assignmass' => array($this, 'action_assign_mass', 'permission' => 'manage_awards'),
			'requests' => array($this, 'action_awards_requests', 'permission' => array('assign_awards', 'manage_awards')),
			'settings' => array($this, 'action_settings', 'permission' => 'manage_awards'),
			'delete' => array($this, 'action_delete', 'permission' => 'manage_awards'),
			'viewcategory' => array($this, 'action_view_category', 'permission' => 'manage_awards'),
			'editcategory' => array($this, 'action_edit_category', 'permission' => 'manage_awards'),
			'deletecategory' => array($this, 'action_delete_category', 'permission' => 'manage_awards'),
			'viewassigned' => array($this, 'action_view_assigned', 'permission' => 'manage_awards'),
		);

		// You way will end here if you don't have permission.
		$action = new Action();

		// Default to sub-action 'main'
		$subAction = $action->initialize($subActions, 'main');
		$context['sub_action'] = $subAction;

		// Set up the admin tabs (see iaa_member_awards() as well)
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['awards'],
			'help' => 'awards_help',
			'description' => $txt['awards_description_main'],
			'tabs' => array(
				'main' => array(
					'label' => $txt['awards_main'],
					'permission' => array('assign_awards', 'manage_awards')
				),
				'categories' => array(
					'label' => $txt['awards_categories'],
					'permission' => 'manage_awards'
				),
				'modify' => array(
					'label' => isset($_REQUEST['a_id']) && $subAction === 'modify' ? $txt['awards_modify'] : $txt['awards_add'],
					'permission' => 'manage_awards'
				),
				'assign' => array(
					'label' => $txt['awards_assign'],
					'permission' => array('assign_awards', 'manage_awards')
				),
				'assigngroup' => array(
					'label' => $txt['awards_assign_membergroup'],
					'permission' => 'manage_awards'
				),
				'assignmass' => array(
					'label' => $txt['awards_assign_mass'],
					'permission' => 'manage_awards'
				),
				'requests' => array(
					'label' => $txt['awards_requests'] . (empty($modSettings['awards_request']) ? '' : ' (<b>' . $modSettings['awards_request'] . '</b>)'),
					'permission' => array('assign_awards', 'manage_awards')
				),
				'settings' => array(
					'label' => $txt['awards_settings'],
					'permission' => 'manage_awards'
				),
			),
		);

		// Call the right function
		$action->dispatch($subAction);
	}

	/**
	 * Main page for the admin panel
	 *
	 * - Loads all the awards and categories that have been added to the system
	 */
	public function action_awards_main()
	{
		global $context, $scripturl, $txt;

		// Load dependencies
		require_once(SUBSDIR . '/GenericList.class.php');

		// Load all the categories.
		$categories = AwardsLoadCategories();

		// Build an award list for *each* category
		$count = 0;
		foreach ($categories as $name => $cat)
		{
			$listOptions = array(
				// ID per category
				'id' => 'awards_cat_list_' . $count,
				'title' => $name,
				'items_per_page' => 25,
				'default_sort_col' => 'award_name',
				'no_items_label' => $txt['awards_error_no_awards'],
				'base_href' => $scripturl . '?action=admin;area=awards' . (isset($_REQUEST['sort' . $count]) ? ';sort' . $count . '=' . urlencode($_REQUEST['sort' . $count]) : ''),
				'request_vars' => array(
					'sort' => 'sort' . $count,
					'desc' => 'desc' . $count,
				),
				'get_items' => array(
					'file' => 'Awards.subs.php',
					'function' => 'AwardsLoadCategoryAwards',
					'params' => array(
						$cat,
					),
				),
				'get_count' => array(
					'file' => 'Awards.subs.php',
					'function' => 'AwardsCountCategoryAwards',
					'params' => array(
						$cat,
					),
				),
				'columns' => array(
					'img' => array(
						'header' => array(
							'value' => $txt['awards_image'],
							'class' => 'grid8 nowrap centertext',
						),
						'data' => array(
							'sprintf' => array(
								'format' => '<img class="award_regular_image" src="%1$s" alt="%2$s" />',
								'params' => array(
									'img' => false,
									'award_name' => false,
								),
							),
							'class' => 'centertext',
						),
					),
					'small' => array(
						'header' => array(
							'value' => $txt['awards_mini'],
							'class' => 'grid8 nowrap centertext',
						),
						'data' => array(
							'sprintf' => array(
								'format' => '<img class="award_mini_image" src="%1$s" alt="%2$s" />',
								'params' => array(
									'small' => false,
									'award_name' => false,
								),
							),
							'class' => 'centertext',
						),
					),
					'award_name' => array(
						'header' => array(
							'value' => $txt['awards_name'],
						),
						'data' => array(
							'db' => 'award_name',
							'class' => 'grid25',
						),
						'sort' => array(
							'default' => 'award_name',
							'reverse' => 'award_name DESC',
						),
					),
					'description' => array(
						'header' => array(
							'value' => $txt['awards_desc'],
						),
						'data' => array(
							'db' => 'description',
						),
						'sort' => array(
							'default' => 'description',
							'reverse' => 'description DESC',
						),
					),
					'action' => array(
						'header' => array(
							'value' => $txt['awards_actions'],
							'class' => 'centertext',
						),
						'data' => array(
							'function' => function ($row) use ($txt) {
								$result = (allowedTo('manage_awards') ? '
									<a href="' . $row['edit'] . '" title="' . $txt['awards_button_edit'] . '">
										<i class="icon i-modify"></i>
									</a>
									<a href="' . $row['delete'] . '" onclick="return confirm(' . $txt['awards_confirm_delete_award'] . ')" title="' . $txt['awards_button_delete'] . '">
										<i class="icon i-delete"></i>
									</a>
									<br />' : '');

								if (($row['award_type'] <= 1) && (allowedTo('manage_awards') || (allowedTo('assign_awards') && !empty($row['assignable']))))
								{
									$result .= '
										<a href="' . $row['assign'] . '" title="' . $txt['awards_button_assign'] . '">
											<i class="icon i-user-plus"></i>
										</a>';
								}

								$result .= '
										<a href="' . $row['view_assigned'] . '" title="' . $txt['awards_button_members'] . '">
											<i class="icon i-users"></i>
										</a>';

								return $result;
							},
							'class' => 'nowrap centertext',
						),
					),
				),
			);

			createList($listOptions);
			$count++;
		}

		// Set up for the template display
		$context['count'] = $count;
		$context['page_title'] = $txt['awards_title'] . ' - ' . $txt['awards_main'];
		$context['sub_template'] = 'main';

		// And the old admin tabs
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['awards'],
			'help' => $txt['awards_help'],
			'description' => $txt['awards_description_main'],
		);
	}

	/**
	 * Sets up the $context['award'] array for the add/edit page.
	 *
	 * - If it's a new award, inserts a new row if not it updates an existing one.
	 * - Uses AwardsUpload for files upload.
	 * - If a new image is uploaded for an existing award, deletes the old images.
	 */
	public function action_modify()
	{
		global $context, $txt, $modSettings;

		// Load in our helper functions
		require_once(SUBSDIR . '/Awards.subs.php');

		// Check if they are saving the changes
		if (isset($_POST['award_save']))
		{
			checkSession();

			// Check if any of the key values where left empty, and if so tell them
			if (empty($_POST['award_name']))
			{
				throw new Elk_Exception('awards_error_empty_award_name', 'general');
			}

			if (empty($_FILES['awardFile']['name']) && $_POST['a_id'] == 0)
			{
				throw new Elk_Exception('awards_error_no_file', 'general');
			}

			// Clean and cast the values
			$id = (int) $_POST['a_id'];
			$award_name = strtr(Util::htmlspecialchars($_POST['award_name'], ENT_QUOTES), array("\r" => '', "\n" => '', "\t" => ''));
			$description = strtr(Util::htmlspecialchars($_POST['description'], ENT_QUOTES), array("\r" => '', "\n" => '', "\t" => ''));
			$category = (int) $_POST['id_category'];
			$time_added = time();
			$award_type = empty($_POST['id_type']) ? 0 : $_POST['id_type'];
			$trigger = empty($_POST['awardTrigger']) ? 0 : (int) $_POST['awardTrigger'];
			$award_location = (int) $_POST['award_location'];
			$award_requestable = (isset($_POST['award_requestable']) ? 1 : 0);
			$award_assignable = (isset($_POST['award_assignable']) ? 1 : 0);

			// New award?
			if ($id < 1)
			{
				// Add in a new award and get the id
				$id = AwardsAddAward($award_name, $description, $time_added, $category, $award_type, $trigger, $award_location, $award_requestable, $award_assignable);

				// Now upload the file(s) associated with the award
				AwardsUpload($id);
			}
			else
			{
				// Not a new award so lets edit an existing one
				$trigger = empty($_POST['awardTrigger']) ? 0 : (int) $_POST['awardTrigger'];

				// Load the existing award info and see if they changed the trigger value
				$context['award'] = AwardsLoadAward($id);

				// Trigger value changed on an auto award, this invalidates all (auto) awards earned with this award ID
				if (($context['award']['type'] > 1) && ($context['award']['trigger'] != $trigger))
				{
					AwardsRemoveMembers($id);
				}

				// Make the updates to the award
				$editAward = AwardsUpdateAward($id, $award_name, $description, $category, $award_type, $trigger, $award_location, $award_requestable, $award_assignable);

				// Are we uploading new images for this award?
				if ($editAward && ((isset($_FILES['awardFile']) && $_FILES['awardFile']['error'] == 0) || (isset($_FILES['awardFileMini']) && $_FILES['awardFileMini']['error'] == 0)))
				{
					// Let's make sure that we delete the file that we are supposed to and not something harmful
					list ($filename, $minifile) = AwardLoadFiles($id);

					// Delete the old file(s) first.
					if (($_FILES['awardFile']['error'] == 0) && file_exists(BOARDDIR . '/' . (empty($modSettings['awards_dir']) ? '' : $modSettings['awards_dir'] . '/') . $filename))
					{
						@unlink(BOARDDIR . '/' . (empty($modSettings['awards_dir']) ? '' : $modSettings['awards_dir'] . '/') . $filename);
					}

					if (file_exists(BOARDDIR . '/' . (empty($modSettings['awards_dir']) ? '' : $modSettings['awards_dir'] . '/') . $minifile))
					{
						@unlink(BOARDDIR . '/' . (empty($modSettings['awards_dir']) ? '' : $modSettings['awards_dir'] . '/') . $minifile);
					}

					// Now add the new one.
					AwardsUpload($id);
				}
			}

			// Awards were changed, flush the cache
			cache_put_data('awards:autoawards', null, 60);
			cache_put_data('awards:autoawardsid', null, 60);

			// Back to the admin panel
			redirectexit('action=admin;area=awards;sa=modify;saved=1;a_id=' . $id);
		}

		// Not saving so we must be adding or modifying
		$context['categories'] = AwardsLoadCategories('ASC', true);

		// Some javascript to disable the trigger text box if the first option e.g. regular is selected
		addInlineJavascript('
			let award_type = document.getElementById(\'id_type\');

			mod_addEvent(award_type, \'change\', toggleAwardTrigger);
			toggleAwardTrigger();

			function mod_addEvent(control, ev, fn)
			{
				if (control.addEventListener)
					control.addEventListener(ev, fn, false);
				else if (control.attachEvent)
					control.attachEvent(\'on\'+ev, fn);
			}

			function toggleAwardTrigger()
			{
				let select_elem = document.getElementById(\'awardTrigger\');

				select_elem.disabled = award_type.value == 1;

				var desc = document.getElementById(\'trigger_desc_\' + award_type.value + \'\').firstChild.data;
				document.getElementById(\'awardTrigger_desc\').innerHTML = desc;
			}', true);

		// Load the data for editing/viewing an existing award
		if (isset($_REQUEST['a_id']))
		{
			// Check that awards id is clean.
			$id = (int) $_REQUEST['a_id'];

			// Load a single award in for editing.
			$context['award'] = AwardsLoadAward($id);
			$context['editing'] = true;

			// Set the page title
			$context['page_title'] = $txt['awards_title'] . ' - ' . $txt['awards_edit_award'];
		}
		else
		{
			// Setup some default blank values as we are adding a new award
			$context['editing'] = false;
			$context['award'] = array(
				'id' => 0,
				'award_name' => '',
				'description' => '',
				'category' => 1,
				'trigger' => '',
				'award_type' => 1,
				'award_location' => 1,
				'assignable' => 0,
				'requestable' => 0
			);

			// Set the title
			$context['page_title'] = $txt['awards_title'] . ' - ' . $txt['awards_manage_awards'];
		}

		$context['sub_template'] = 'modify';
		$context['tabindex'] = 1;
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['awards'],
			'help' => $txt['awards_help'],
			'description' => $txt['awards_description_modify'],
		);
	}

	/**
	 * This function handles deleting an award
	 *
	 * - If the image exists delete it then deletes the row from the database
	 * - Deletes any trace of the award from the awards_members table.
	 */
	public function action_delete()
	{
		global $modSettings;

		// Check the session
		checkSession('get');

		$id = (int) $_GET['a_id'];

		// Select the file name to delete
		list ($filename, $minifile) = AwardLoadFiles($id);

		// Now delete the award from the server
		@unlink(BOARDDIR . '/' . (empty($modSettings['awards_dir']) ? '' : $modSettings['awards_dir'] . '/') . $filename);
		@unlink(BOARDDIR . '/' . (empty($modSettings['awards_dir']) ? '' : $modSettings['awards_dir'] . '/') . $minifile);

		// Now delete the entry from the database and remove it from the members
		AwardsDeleteAward($id);
		AwardsRemoveMembers($id);

		// Redirect the exit
		redirectexit('action=admin;area=awards');
	}

	/**
	 * This is where you assign awards to members.
	 *
	 * Step 1
	 *   - Select the award that you want to assign
	 *
	 * - Step 2
	 *   - Select the members that you want to give this award to.
	 *   - Enter the date that the award was given.
	 */
	public function action_assign()
	{
		global $context, $txt, $user_info, $modSettings, $scripturl;

		// Load in our helper functions
		require_once(SUBSDIR . '/Awards.subs.php');

		// First step, select the awards that can be assigned by this member
		if (!isset($_GET['step']) || $_GET['step'] == 1)
		{
			// Select all the non auto awards to populate the menu.
			$context['awards'] = AwardsLoadAssignableAwards();
			$context['awardsjavasciptarray'] = json_encode($context['awards']);

			// Quick check for mischievous users, you can't just enter any a_id ;)
			if (isset($_REQUEST['a_id']) && !allowedTo('manage_awards') && empty($context['awards'][$_REQUEST['a_id']]['assignable']))
			{
				throw new Elk_Exception('awards_error_hack_error');
			}

			// Set the current step.
			$context['step'] = 1;

			// Set the title
			$context['page_title'] = $txt['awards_title'] . ' - ' . $txt['awards_select_badge'];
		}
		// Ah step '2', they selected some bum(s) to get an award :)
		elseif ($_GET['step'] == 2)
		{
			// Check session.
			checkSession();

			// Well we need this
			$members = array();

			// Make sure that they picked an award and members to assign it to...
			// but not themselves, that would be wrong
			if (!empty($_POST['recipient_to']))
			{
				foreach ($_POST['recipient_to'] as $recipient)
				{
					if ($recipient != $user_info['id'] || $user_info['is_admin'])
					{
						$members[] = (int) $recipient;
					}
				}
			}

			if (empty($members) || empty($_POST['award']))
			{
				throw new Elk_Exception('awards_error_no_members', 'general');
			}

			// Set a valid date, award.
			$date_received = (int) $_POST['year'] . '-' . (int) $_POST['month'] . '-' . (int) $_POST['day'];
			$award_id = (int) $_POST['award'];

			// Prepare the values and add them
			$values = array();
			foreach ($members as $member)
			{
				$values[] = array($award_id, $member, $date_received, 1);
			}

			AwardsAddMembers($values);

			// Redirect to show the members with this award.
			redirectexit('action=admin;area=awards;sa=viewassigned;a_id=' . $_POST['award']);
		}

		$context['sub_template'] = 'assign';
		$context['tabindex'] = 1;
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['awards'],
			'help' => $txt['awards_help'],
			'description' => $txt['awards_description_assign'],
		);

		// Some JS for the UI
		loadJavascriptFile(array('awards.js', 'suggest.js'), array('defer' => true));
		addInlineJavascript('
			var oAwardSend = new elk_AwardSend({
				sSelf: \'oAwardSend\',
				sSessionId: elk_session_id,
				sSessionVar: elk_session_var,
				sTextDeleteItem: \'' . $txt['autosuggest_delete_item'] . '\',
				sToControlId: \'to_control\',
				aToRecipients: [
				]
			});

			function showaward()
			{
				awards = ' . $context['awardsjavasciptarray'] . '
				document.getElementById(\'awards\').src = \'' . dirname($scripturl) . '/' . $modSettings['awards_dir'] . '/\' + awards[document.forms.assign.award.value][\'filename\'];
				document.getElementById(\'miniawards\').src = \'' . dirname($scripturl) . '/' . $modSettings['awards_dir'] . '/\' + awards[document.forms.assign.award.value][\'minifile\'];
			}', true);
	}

	/**
	 * This is where you add an award to a membergroup
	 *
	 * Step 1
	 *   - Select the award that you want to assign
	 *     - Select the groups that you want to assign them to
	 *
	 * - Step 2
	 *   - Preps and checks the data
	 *   - Enter the date that the award was given.
	 */
	public function action_assign_member_group()
	{
		global $context, $txt, $modSettings, $scripturl;

		// Load in our helper functions
		require_once(SUBSDIR . '/Awards.subs.php');

		// First step, select the membergroups and awards
		if (!isset($_REQUEST['step']) || (int) $_REQUEST['step'] == 1)
		{
			// Load all the member groups
			$context['groups'] = AwardsLoadGroups();

			// Done with groups, now on to selecting the non auto awards to populate the menu.
			$context['awards'] = AwardsLoadAssignableAwards();
			$context['awardsjavasciptarray'] = json_encode($context['awards']);

			// Set the template details
			$context['step'] = 1;
			$context['page_title'] = $txt['awards_title'] . ' - ' . $txt['awards_mem_group'];
		}
		// Ah step 'duo', they selected some ungrateful group(s) to get an award :P
		elseif ((int) $_REQUEST['step'] == 2)
		{
			// Make sure that they picked an award and group to assign it to...
			if (isset($_POST['who']))
			{
				foreach ($_POST['who'] as $group)
				{
					$membergroups[] = (int) $group;
				}
			}

			if (empty($membergroups) || empty($_POST['award']))
			{
				throw new Elk_Exception('awards_error_no_groups', 'general');
			}

			// Set the award date
			$date_received = (int) $_POST['year'] . '-' . (int) $_POST['month'] . '-' . (int) $_POST['day'];
			$award_id = (int) $_POST['award'];

			// Prepare and insert the values.
			$values = array();
			foreach ($membergroups as $group)
			{
				$values[] = array($award_id, -$group, $group, $date_received, 1);
			}

			AwardsAddMembers($values, true);

			// Redirect to show the members with this award.
			redirectexit('action=admin;area=awards;sa=viewassigned;a_id=' . $_POST['award']);
		}

		// Set up for the template
		$context['sub_template'] = 'assign_group';
		$context['tabindex'] = 1;
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['awards'],
			'help' => $txt['awards_help'],
			'description' => $txt['awards_description_assigngroup'],
		);

		// Add some JS for the UI experience
		addInlineJavascript('
			function showaward()
			{
				awards = ' . $context['awardsjavasciptarray'] . '
				document.getElementById(\'awards\').src = \'' . dirname($scripturl) . '/' . $modSettings['awards_dir'] . '/\' + awards[document.forms.assigngroup.award.value][\'filename\'];
				document.getElementById(\'miniawards\').src = \'' . dirname($scripturl) . '/' . $modSettings['awards_dir'] . '/\' + awards[document.forms.assigngroup.award.value][\'minifile\'];
			}', true);
	}

	/**
	 * This is where you add an award to all or some of the members of a membergroup
	 *
	 * Step 1
	 *   - Select the membergroups to generate a list of members to assign
	 *
	 * Step 2
	 *     - Select the award and members to assign the award
	 *   - The memberlist is created from the chosen group and all memebers are pre-selected
	 *
	 * - Step 3
	 *   - Preps and checks the data
	 *   - Enter the date that the award was given.
	 */
	public function action_assign_mass()
	{
		global $context, $txt, $scripturl, $modSettings;

		// Load in our helper functions
		require_once(SUBSDIR . '/Awards.subs.php');

		// First step, select the membergroups and awards
		if (!isset($_REQUEST['step']) || (int) $_REQUEST['step'] < 3)
		{
			// Load all the member groups
			$context['groups'] = AwardsLoadGroups();

			// Done with groups, now on to selecting the non auto assignable awards to populate the menu.
			$context['awards'] = AwardsLoadAssignableAwards();
			$context['awardsjavasciptarray'] = json_encode($context['awards']);

			// Set the template details
			$context['step'] = 1;
			$context['page_title'] = $txt['awards_title'] . ' - ' . $txt['awards_select_group'];

			// Something to check to make sure a false group_id is not passed back
			$_SESSION['allowed_groups'] = array_keys($context['groups']);

			// Good old number 2 ... they have selected some groups, we need to load the members for them
			if (isset($_REQUEST['step']) && (int) $_REQUEST['step'] === 2)
			{
				// Make sure that they checked some groups, so we can load them
				if (!empty($_POST['who']))
				{
					$context['members'] = AwardsLoadGroupMembers();

					// Set the template details
					$context['step'] = 3;
					$context['page_title'] = $txt['awards_title'] . ' - ' . $txt['awards_select_member'];
				}
				else
				{
					// They made a mistake, back to step 1 they go!
					$context['step'] = 1;
					$context['page_title'] = $txt['awards_title'] . ' - ' . $txt['awards_select_group'];
				}
			}
		}
		// Ah step 3, they selected mass quantities of members to get a special award
		elseif ((int) $_REQUEST['step'] == 3)
		{
			checkSession();

			// No members no awards
			if (empty($_POST['member']) || empty($_POST['award']))
			{
				throw new Elk_Exception('awards_error_no_members', 'general');
			}

			// Make sure that they picked an award and group to assign it to...
			foreach ($_POST['member'] as $member)
			{
				$members[] = (int) $member;
			}

			// Set a valid date and award
			$date_received = (int) $_POST['year'] . '-' . (int) $_POST['month'] . '-' . (int) $_POST['day'];
			$award_id = (int) $_POST['award'];

			// Prepare the values.
			$values = array();
			foreach ($members as $member)
			{
				$values[] = array($award_id, $member, $date_received, 1);
			}

			AwardsAddMembers($values);

			// Redirect to show the members with this award.
			redirectexit('action=admin;area=awards;sa=viewassigned;a_id=' . $_POST['award']);
		}

		addInlineJavascript('
		function showaward()
		{
			awards = ' . $context['awardsjavasciptarray'] . '
			document.getElementById(\'awards\').src = \'' . dirname($scripturl) . '/' . $modSettings['awards_dir'] . '/\' + awards[document.forms.assigngroup2.award.value][\'filename\'];
			document.getElementById(\'miniawards\').src = \'' . dirname($scripturl) . '/' . $modSettings['awards_dir'] . '/\' + awards[document.forms.assigngroup2.award.value][\'minifile\'];
		};
		');

		$context['sub_template'] = 'assign_mass';
		$context['tabindex'] = 1;
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['awards'],
			'help' => $txt['awards_help'],
			'description' => $txt['awards_description_assignmass'],
		);
	}

	/**
	 * This is where you see the members that have been assigned a certain award.
	 *
	 * - Can unassign the award for selected members.
	 */
	public function action_view_assigned()
	{
		global $context, $scripturl, $txt, $modSettings;

		// An award must be selected.
		$id = (int) $_REQUEST['a_id'];
		if (empty($id) || $id <= 0)
		{
			throw new Elk_Exception('awards_error_no_award', 'general');
		}

		// Load in our helper functions
		require_once(SUBSDIR . '/Awards.subs.php');

		// Removing the award from some members?
		if (isset($_POST['unassign']))
		{
			checkSession();

			// Get all the id's selected in the form
			$ids = array();
			foreach ($_POST['member'] as $remove_id => $dummy)
			{
				$ids[] = (int) $remove_id;
			}

			// Delete the rows from the database for the ids selected.
			AwardsRemoveMembers($id, $ids);

			// Redirect to the awards
			redirectexit('action=admin;area=awards;sa=viewassigned;a_id=' . $id);
		}

		// Load the awards info for this award
		$context['award'] = AwardsLoadAward($id);
		$parser = ParserWrapper::instance();
		$context['award']['description'] = $parser->parseMessage($context['award']['description'], true);

		// Build the list option array to display the data
		$listOptions = array(
			'id' => 'view_assigned',
			'title' => $txt['awards_showmembers'] . ': ' . $context['award']['award_name'],
			'items_per_page' => $modSettings['defaultMaxMessages'],
			'no_items_label' => $txt['awards_no_assigned_members2'],
			'base_href' => $scripturl . '?action=admin;area=awards;sa=viewassigned;a_id=' . $id,
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
				'members' => array(
					'header' => array(
						'value' => $txt['members'],
					),
					'data' => array(
						'function' => function ($rowData) use ($scripturl) {
							if ($rowData['id_member'] > 0)
							{
								return '<a class="linkbutton" href="' . strtr($scripturl, array('%' => '%%')) . '?action=profile;u=' . $rowData['id_member'] . '">' . $rowData['member_name'] . '</a>';
							}

							return $rowData['member_name'];
						},
					),
					'sort' => array(
						'default' => 'm.member_name ',
						'reverse' => 'm.member_name DESC',
					),
				),
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
				'check' => array(
					'header' => array(
						'value' => '<input type="checkbox" id="checkAllMembers" onclick="invertAll(this, this.form);" class="input_check" />',
						'class' => 'centertext'
					),
					'data' => array(
						'sprintf' => array(
							'format' => '<input type="checkbox" name="member[%1$d]" id="member%1$d" class="input_check" />',
							'params' => array(
								'uniq_id' => false,
							),
						),
						'class' => 'centertext',
					),
				),
			),
			'form' => array(
				'href' => $scripturl . '?action=admin;area=awards;sa=viewassigned;a_id=' . $context['award']['id'],
				'include_sort' => true,
				'include_start' => true,
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '<input type="submit" name="unassign" class="right_submit" value="' . $txt['awards_unassign'] . '" accesskey="u" onclick="return confirm(\'' . $txt['awards_removemember_yn'] . '\');" />',
				),
			),
		);

		// Set the context values for the template
		$context['page_title'] = $txt['awards_title'] . ' - ' . $context['award']['award_name'];
		$context['sub_template'] = 'view_assigned';
		$context['tabindex'] = 1;
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['awards'],
			'help' => $txt['awards_help'],
			'description' => $txt['awards_description_viewassigned'],
		);

		// Create the list.
		require_once(SUBSDIR . '/GenericList.class.php');
		createList($listOptions);
	}

	/**
	 * This is where you handle the settings for the Addon
	 *
	 * - awardsDir is the directory in which the awards/badges are saved.
	 */
	public function action_settings()
	{
		global $context, $txt;

		$context['sub_template'] = 'settings';
		$context['page_title'] = $txt['awards_title'] . ' - ' . $txt['awards_settings'];

		// Save the settings
		if (isset($_POST['save_settings']))
		{
			// Check the session
			checkSession();

			// Strip any slashes from the awards dir
			$_POST['awards_dir'] = str_replace(array('\\', '/'), '', $_POST['awards_dir']);

			// Try to create a new dir if it doesn't exist.
			if (!is_dir(BOARDDIR . '/' . $_POST['awards_dir']) && trim($_POST['awards_dir']) != '')
			{
				if (!mkdir($concurrentDirectory = BOARDDIR . '/' . $_POST['awards_dir'], 0755) && !is_dir($concurrentDirectory))
				{
					$context['awards_mkdir_fail'] = true;
				}
			}

			// Now save these in the modSettings array
			updateSettings(
				array(
					'awards_enabled' => isset($_POST['awards_enabled']) ? 1 : 0,
					'awards_dir' => Util::htmlspecialchars($_POST['awards_dir'], ENT_QUOTES),
					'awards_favorites' => isset($_POST['awards_favorites']) ? 1 : 0,
					'awards_in_post' => isset($_POST['awards_in_post']) ? (int) $_POST['awards_in_post'] : 5,
					'awards_aboveavatar_format' => isset($_POST['awards_aboveavatar_format']) ? (int) $_POST['awards_aboveavatar_format'] : 0,
					'awards_aboveavatar_title' => isset($_POST['awards_aboveavatar_title']) ? trim(Util::htmlspecialchars($_POST['awards_aboveavatar_title'], ENT_QUOTES)) : $txt['awards_title'],
					'awards_belowavatar_format' => isset($_POST['awards_belowavatar_format']) ? (int) $_POST['awards_belowavatar_format'] : 0,
					'awards_belowavatar_title' => isset($_POST['awards_belowavatar_title']) ? trim(Util::htmlspecialchars($_POST['awards_belowavatar_title'], ENT_QUOTES)) : $txt['awards_title'],
					'awards_signature_format' => isset($_POST['awards_signature_format']) ? (int) $_POST['awards_signature_format'] : 0,
					'awards_signature_title' => isset($_POST['awards_signature_title']) ? trim(Util::htmlspecialchars($_POST['awards_signature_title'], ENT_QUOTES)) : $txt['awards_title'],
				)
			);
		}
	}

	/**
	 * Edits existing categories or adds new ones
	 */
	public function action_edit_category()
	{
		global $context, $txt;

		// Editing
		if (isset($_REQUEST['a_id']))
		{
			$id = (int) $_REQUEST['a_id'];

			// Needs to be an int!
			if (empty($id) || $id <= 0)
			{
				throw new Elk_Exception('awards_error_no_id_category', 'general');
			}

			// Load single category for editing.
			$context['category'] = AwardsLoadCategory($id);

			$context['editing'] = true;
			$context['page_title'] = $txt['awards_title'] . ' - ' . $txt['awards_edit_category'];
		}
		// otherwise adding a new one
		else
		{
			// Setup place holders.
			$context['editing'] = false;
			$context['category'] = array(
				'id' => 0,
				'name' => '',
			);

			$context['page_title'] = $txt['awards_title'] . ' - ' . $txt['awards_manage_categories'];
		}

		// Check if they are saving the changes
		if (isset($_POST['category_save']))
		{
			checkSession();

			$name = trim(strtr(Util::htmlspecialchars($_REQUEST['category_name'], ENT_QUOTES), array("\r" => '', "\n" => '', "\t" => '')));

			// Check if any of the values were left empty
			if (empty($name))
			{
				throw new Elk_Exception('awards_error_empty_category_name', 'general');
			}

			// Add a new or Update and existing
			if ($_POST['id_category'] == 0)
			{
				AwardsSaveCategory($name);
			}
			else
			{
				$id_category = (int) $_POST['id_category'];
				AwardsSaveCategory($name, $id_category);
			}

			// Redirect back to the mod.
			redirectexit('action=admin;area=awards;sa=editcategory;saved=1');
		}

		$context['sub_template'] = 'edit_category';
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['awards_category'],
			'help' => $txt['awards_category_help'],
			'description' => isset($_REQUEST['a_id']) ? $txt['awards_description_editcategory'] : $txt['awards_description_addcategory'],
		);
	}

	/**
	 * List all the categories
	 *
	 * - Provides option to edit or delete them
	 */
	public function action_list_categories()
	{
		global $context, $txt;

		// Define $categories
		$context['categories'] = AwardsLoadAllCategories();

		// Count the number of awards in each category
		$counts = AwardsInCategories();

		foreach ($counts as $id => $count)
		{
			$context['categories'][$id]['awards'] = $count['awards'];
		}

		// Set the context values
		$context['page_title'] = $txt['awards_title'] . ' - ' . $txt['awards_list_categories'];
		$context['sub_template'] = 'list_categories';
	}

	/**
	 * List all the categories
	 *
	 * Provides option to edit or delete them
	 */
	public function action_delete_category()
	{
		// Must have manage not just assign permission for this action
		isAllowedTo('manage_awards');

		// Before doing anything check the session
		checkSession('get');

		$id = (int) $_REQUEST['a_id'];

		if ($id === 1)
		{
			throw new Elk_Exception('awards_error_delete_main_category', 'general');
		}

		AwardsDeleteCategory($id);

		// Redirect back to the mod.
		redirectexit('action=admin;area=awards;sa=categories');
	}

	/**
	 * Shows all the awards within a category
	 */
	public function action_view_category()
	{
		global $context, $scripturl, $txt;

		// Must have manage not just assign permission for this action
		isAllowedTo('manage_awards');

		// Clean up!
		$id_category = (int) $_REQUEST['a_id'];
		$max_awards = 15;
		$start = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

		// Count the number of awards in this cat for create index
		$count_awards = (int) AwardsInCategories($id_category);

		// And find the category name
		$category = AwardsLoadCategory($id_category);
		$context['category'] = $category['name'];

		// Grab all qualifying awards
		$context['awards'] = AwardsLoadCategoryAwards($start, $max_awards, 'award_name DESC', $id_category);

		$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=awards;sa=viewcategory', $context['start'], $count_awards, $max_awards);
		$context['page_title'] = $txt['awards_title'] . ' - ' . $txt['awards_viewing_category'];
		$context['sub_template'] = 'view_category';

		// And the admin tabs
		$context[$context['admin_menu_name']]['tab_data'] += array(
			'title' => $txt['awards'],
			'help' => $txt['awards_help'],
			'description' => $txt['awards_description_viewcategory'],
		);

		// A cheat to show categories as selected when in view category
		$context['menu_data_1']['current_subsection'] = 'categories';
	}

	/**
	 * Shows all the awards that members have requested
	 *
	 * - Groups the requests by category
	 * - Calls request_award template
	 */
	public function action_awards_requests()
	{
		global $context, $txt;

		// Load just the members awaiting approval, so we can reject them >:D
		$awards = AwardsLoadRequestedAwards();

		// Place them in context for the template
		$context['awards'] = $awards;
		$context['sub_template'] = 'request_award';
		$context['page_title'] = $txt['awards_requests'];
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['awards'],
			'help' => $txt['awards_help'],
			'description' => $txt['awards_description_requests'],
		);

		// Denied or approved ... the choice is yours
		if (isset($_POST['reject_selected']) || isset($_POST['approve_selected']))
		{
			$this->action_awards_requests2();
		}
	}

	/**
	 * Does the actual approval or deny of the request
	 *
	 * - If approved flips the active bit
	 * - If rejected removes the request
	 */
	private function action_awards_requests2()
	{
		global $modSettings;

		// Check session.
		checkSession();

		// Start the counter.
		$requests_count = 0;

		// Let's sanitize these up.
		$awards = array();
		foreach ($_POST['requests'] as $id_award => $members)
		{
			foreach ($members as $member => $id_member)
			{
				$requests_count++;
				$awards[$id_award][] = (int) $id_member;
			}
		}

		// Accept the request
		if (isset($_POST['approve_selected']))
		{
			AwardsApproveDenyRequests($awards);
		}
		// Or the more fun, deny em!
		elseif (isset($_POST['reject_selected']))
		{
			AwardsApproveDenyRequests($awards, false);
		}

		// We need to update the requests amount.
		updateSettings(array(
			'awards_request' => (($modSettings['awards_request'] - $requests_count) <= 0 ? 0 : $modSettings['awards_request'] - $requests_count),
		));

		// Redirect.
		redirectexit('action=admin;area=awards;sa=requests');
	}
}
