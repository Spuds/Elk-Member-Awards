<?php

function template_main()
{
	global $context;

	for ($i = 0; $i < $context['count']; $i++)
	{
		template_show_list('awards_cat_list_' . $i);
		echo '<br /><br />';
	}
}

function template_modify()
{
	global $context, $txt, $scripturl;

	echo '
				<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=awards;sa=modify" method="post" name="award" accept-charset="UTF-8" enctype="multipart/form-data">';

	if (isset($_GET['saved']))
		echo'
					<div class="infobox">',
						$txt['awards_saved_award'], '
					</div>';

	echo '
					<h3 class="category_header hdicon cat_img_award_add ">
						', ($context['editing'] === true ? $txt['awards_edit_award'] . ' - ' . $context['award']['award_name'] : $txt['awards_add_award']), '
					</h3>';

	echo '
					<div class="windowbg2">
						<div class="content">
							<fieldset>
								<legend>', $txt['awards_add_name'], '</legend>
								<dl class="settings">
									<dt>
										<label for="award_name">', $txt['awards_badge_name'], '</label>
									</dt>
									<dd>
										<input type="text" name="award_name" id="award_name" value="', $context['award']['award_name'], '" size="30" />
									</dd>

									<dt>
										<label for="description">', $txt['awards_edit_description'], '</label>
									</dt>
									<dd>
										<input type="text" name="description" id="description" value="', $context['award']['description'], '" size="30" />
									</dd>

									<dt>
										<label for="id_category">', $txt['awards_category'], '</label>:
									</dt>
									<dd>
										<select name="id_category" id="id_category">';

	foreach ($context['categories'] as $category)
		echo '
											<option value="', $category['id'], '"', ($category['id'] == $context['award']['category']) ? ' selected="selected"' : '', '>', $category['name'], '</option>';

	echo '
										</select>
									</dd>
								</dl>
							</fieldset>

							<fieldset>
								<legend>', $txt['awards_add_type'], '</legend>
								<dl class="settings">
									<dt>
										<label for="id_type">', $txt['awards_type'], '</label>:
									</dt>
									<dd>
										<select name="id_type" id="id_type">';

	// our awards type list selection
	foreach ($context['award_types'] as $type)
		echo '
											<option value="', $type['id'], '"', (isset($context['award']['type']) && $type['id'] == $context['award']['type']) ? ' selected="selected"' : '', '>', $type['name'], '</option>';

	echo '
										</select>
									</dd>

									<dt>
										<label for="awardTrigger">', $txt['awards_trigger'], '</label>:
										<br />
										<span id="awardTrigger_desc" class="smalltext" ></span>';

	// The descriptions for them, hidden and used by javascript to fill in the awardTrigger_desc span
	foreach ($context['award_types'] as $desc)
		echo '
										<span id="trigger_desc_', $desc['id'], '" style="display:none">', $desc['desc'], '</span>';

	echo '
									</dt>
									<dd>
										<input type="text" name="awardTrigger" id="awardTrigger" value="', $context['award']['trigger'], '" size="30" class="input_text"/>
									</dd>
								</dl>
							</fieldset>

							<fieldset>
								<legend>', $txt['awards_add_image'], '</legend>
								<dl class="settings">
									<dt>
										&nbsp;
									</dt>
									<dd>',
										!empty($context['award']['img']) ? '<img id="awardsfull" src="' . $context['award']['img'] . '" align="middle" alt="" />' : '&nbsp;', '
									</dd>
									<dt>
										<label for="awardFile">', $txt['awards_badge_upload'], '</label>:
									</dt>
									<dd>
										<input type="file" name="awardFile" id="awardFile" size="40" />
									</dd>
								</dl>';

	if (!empty($context['award']['img']))
		echo '
								<br class="clear" />';

	echo '
								<dl class="settings">
									<dt>
										&nbsp;
									</dt>
									<dd>',
										!empty($context['award']['small']) ? '<img id="awardssmall" src="' . $context['award']['small'] . '" align="middle" alt="" />' : '&nbsp;', '
									</dd>
									<dt>
										<label for="awardFileMini">', $txt['awards_badge_upload_mini'], '</label>
									</dt>
									<dd>
										<input type="file" name="awardFileMini" id="awardFileMini" size="40" />
									</dd>
								</dl>
								<dl class="settings">
									<dt>
										<label for="award_location">', $txt['awards_image_placement'], '</label>:
									</dt>
									<dd>
										<select name="award_location" id="award_location">';

	// our awards type list selection
	foreach ($context['award_placements'] as $type)
		echo '
											<option value="', $type['id'], '"', (isset($context['award']['location']) && $type['id'] == $context['award']['location']) ? ' selected="selected"' : '', '>', $type['name'], '</option>';

	echo '
										</select>
									</dd>
								</dl>
							</fieldset>

							<fieldset>
								<legend>', $txt['awards_extras'], '</legend>
								<dl class="settings">
									<dt>
										<label for="award_requestable">', $txt['awards_requestable'], '</label>:<br />
										<span class="smalltext">', $txt['awards_requestable_desc'], '</span>
									</dt>
									<dd>
										<input type="checkbox" name="award_requestable" id="award_requestable" ', empty($context['award']['requestable']) ? '' : 'checked="checked"', ' />
									</dd>
									<dt>
										<label for="award_assignable">', $txt['awards_assignable'], '</label>:<br />
										<span class="smalltext">', $txt['awards_assignable_desc'], '</span>
									</dt>
									<dd>
										<input type="checkbox" name="award_assignable" id="award_assignable" ', empty($context['award']['assignable']) ? '' : 'checked="checked"', ' />
									</dd>
								</dl>
							</fieldset>

							<div class="submitbutton">
								<input type="hidden" name="a_id" value="', $context['award']['id'], '" />
								<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
								<input type="submit" class="button_submit" name="award_save" value="', $context['editing'] ? $txt['save'] : $txt['awards_submit'], '" accesskey="s" />
							</div>
						</div>
					</div>
				</form>';
}

/**
 * Used to assign a member group to an award
 */
function template_assign_group()
{
	global $context, $scripturl, $txt, $modSettings, $settings;

	echo '
		<div id="awardpage">
			<div class="infobox">',
				$txt['awards_assigngroup_intro'], '
			</div>

			<div id="awardassign">
				<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=awards;sa=assigngroup;step=2" method="post" name="assigngroup" id="assigngroup" accept-charset="UTF-8">
					<div class="floatleft" style="width:22%">
						<h3 class="category_header">
							', $txt['awards_select_badge'], '
						</h3>
						<div class="windowbg">
							<div class="content">
								<dl id="awardselect" class="settings">
									<dt>
										<select name="award" onchange="showaward();" size="10">';


	// Loop and show the drop down.
	foreach ($context['awards'] as $key => $award)
		echo '
											<option title="', $award['description'], '" value="', $key, '" ', isset($_REQUEST['a_id']) && $_REQUEST['a_id'] == $key ? 'selected="selected"' : '', '>', $award['award_name'], '</option>';

	echo '
										</select>
									</dt>
								</dl>
							</div>
						</div>
					</div>

					<div class="floatright" style="width:75%">
						<h3 class="category_header hdicon cat_img_plus">
							', $txt['awards_assign_badge'], '
						</h3>
						<div class="windowbg">
							<div class="content">
								<dl class="settings">
									<dt>
										<label for="awards"><b>', $txt['awards_image'], ':</b></label>
									</dt>
									<dd>
										<img id="awards" src="', isset($_REQUEST['a_id']) ? dirname($scripturl) . '/' . $modSettings['awards_dir'] . '/' . $context['awards'][$_REQUEST['a_id']]['filename'] : '', '" align="middle"  alt=""/>
									</dd>
									<dt>
										<label for="miniawards"><b>', $txt['awards_mini'], ':</b></label>
									</dt>
									<dd>
										<img id="miniawards" src="', isset($_REQUEST['a_id']) ? dirname($scripturl) . '/' . $modSettings['awards_dir'] . '/' . $context['awards'][$_REQUEST['a_id']]['minifile'] : '', '" align="middle"  alt=""/>
									</dd>
									<dt>
										<label for="date_received"><b>', $txt['awards_date'], '</b></label>:
									</dt>
									<dd id="date_received">';

	// The month... and day... and year...
	echo '
										<select name="month" tabindex="', $context['tabindex']++, '">';

	foreach ($txt['months'] as $key => $month)
		echo '
											<option value="', $key, '" ', date('F') == $month ? 'selected="selected"' : '', '>', $month, ' </option>';

	echo '
										</select>
										<select name="day" tabindex="', $context['tabindex']++, '">';

	for ($i = 1; $i <= 31; $i++)
		echo '
											<option value="', $i, '" ', date('j') == $i ? 'selected="selected"' : '', '>', $i, ' </option>';

	echo '
										</select>
										<select name="year" tabindex="', $context['tabindex']++, '">';

	for ($i = date('Y') + 5; $i >= date('Y') - 5; $i--)
		echo '
											<option value="', $i, '" ', date('Y') == $i ? 'selected="selected"' : '', '>', $i, ' </option>';

	echo '
										</select>
									</dd>
								</dl>
								<h3 class="secondary_header">
									<img class="icon" src="' . $settings['images_url'] . '/awards/group.png" alt="" />', $txt['awards_mem_group'], '
								</h3>
								<div class="windowbg">
									<dl class="settings">
										<dt>';

	foreach ($context['groups'] as $group)
		echo '
											<input type="checkbox" name="who[', $group['id'], ']" id="who', $group['id'], '" value="', $group['id'], '" class="input_check" /> ', $group['name'], ' <em>(', $group['member_count'], ')</em><br />';

	echo '
											<br class="clear" />
											<input type="checkbox" id="checkAllGroups" onclick="invertAll(this, this.form, \'who\');" class="input_check" /> <em>', $txt['check_all'], '</em>
										</dt>
										<dd>' . $txt['awards_mem_group_desc'] . '
										</dd>
									</dl>
									<hr class="hrcolor" />
									<div class="submitbutton">
										<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
										<input type="submit" class="button_submit" value="', $txt['awards_button_assign'], '" tabindex="', $context['tabindex']++, '" />
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>';
}

/**
 * Template used to assign awards to specific members
 */
function template_assign()
{
	global $context, $scripturl, $txt, $modSettings, $settings;

	echo '
		<div id="awardpage">
			<div class="infobox">',
				$txt['awards_assign_intro'], '
			</div>
			<div id="awardassign">
				<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=awards;sa=assign;step=2" method="post" name="assign" id="assign" accept-charset="UTF-8">
					<div class="floatleft" style="width:22%">
						<h3 class="category_header">
							', $txt['awards_select_badge'], '
						</h3>
						<div class="windowbg">
							<div class="content">
								<dl id="awardselect" class="settings">
									<dt>
										<select name="award" onchange="showaward();" size="10">';

	// Loop and show the award selection drop down.
	foreach ($context['awards'] as $key => $award)
		echo '
											<option title="', $award['description'], '" value="', $key, '" ', isset($_REQUEST['a_id']) && $_REQUEST['a_id'] == $key ? 'selected="selected"' : '', '>', $award['award_name'], '</option>';

	echo '
										</select>
									</dt>
								</dl>
							</div>
						</div>
					</div>

					<div class="floatright" style="width:75%">
						<h3 class="category_header hdicon cat_img_plus">
							', $txt['awards_assign_badge'], '
						</h3>
						<div class="windowbg">
							<div class="content">
								<dl class="settings">
									<dt>
										<label for="awards"><b>', $txt['awards_image'], ':</b></label>
									</dt>
									<dd>
										<img id="awards" src="', isset($_REQUEST['a_id']) ? dirname($scripturl) . '/' . $modSettings['awards_dir'] . '/' . $context['awards'][$_REQUEST['a_id']]['filename'] : '', '" align="middle"  alt=""/>
									</dd>
									<dt>
										<label for="miniawards"><b>', $txt['awards_mini'], ':</b></label>
									</dt>
									<dd>
										<img id="miniawards" src="', isset($_REQUEST['a_id']) ? dirname($scripturl) . '/' . $modSettings['awards_dir'] . '/' . $context['awards'][$_REQUEST['a_id']]['minifile'] : '', '" align="middle"  alt=""/>
									</dd>
									<dt>
										<label for="date_received"><b>', $txt['awards_date'], '</b></label>:
									</dt>
									<dd id="date_received">';

	// The month... and day... and year...
	echo '
										<select name="month" tabindex="', $context['tabindex']++, '">';

	foreach ($txt['months'] as $key => $month)
		echo '
											<option value="', $key, '" ', date('F') == $month ? 'selected="selected"' : '', '>', $month, ' </option>';

	echo '
										</select>
										<select name="day" tabindex="', $context['tabindex']++, '">';
	for ($i = 1; $i <= 31; $i++)
		echo '
											<option value="', $i, '" ', date('j') == $i ? 'selected="selected"' : '', '>', $i, ' </option>';

	echo '
										</select>
										<select name="year" tabindex="', $context['tabindex']++, '">';

	for ($i = date('Y') + 5; $i >= date('Y') - 5; $i--)
		echo '
											<option value="', $i, '" ', date('Y') == $i ? 'selected="selected"' : '', '>', $i, ' </option>';

	echo '
										</select>
									</dd>
								</dl>
								<div class="title_bar">
									<h3 class="secondary_header">
										<img class="icon" src="' . $settings['images_url'] . '/awards/user.png" alt="" />', $txt['awards_select_member'], '
									</h3>
								</div>
								<div class="windowbg">
									<dl class="settings">
										<dt>
											<label for="to_control"><b>', $txt['awards_member_name'], ':</b></label><br />
											<input class="smalltext" type="text" name="to" id="to_control" tabindex="', $context['tabindex']++, '" size="40" style="width: 130px;" />
										</dt>
										<dd>
											<label for="to_control"><b>', $txt['awards_member_selected'], ':</b></label><br />
											<div id="to_item_list_container"></div>
										</dd>
									</dl>
								</div>
								<hr class="hrcolor" />
								<div class="submitbutton">
									<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
									<input type="submit" class="button_submit" value="', $txt['awards_button_assign'], '" tabindex="', $context['tabindex']++, '" />
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>';
}

/**
 * Used to assign a lot of members to a specific award
 */
function template_assign_mass()
{
	global $context, $scripturl, $txt, $modSettings, $settings;

	echo '
		<div id="awardpage">
			<div class="infobox">',
				$txt['awards_assignmass_intro'], '
			</div>

			<div id="awardassign">
				<h3 class="category_header">
					', $txt['awards_mem_group'], '
				</h3>
				<div class="windowbg">
					<div class="content">
						<form action="', $scripturl, '?action=admin;area=awards;sa=assignmass;step=2" method="post" name="assigngroup" id="assigngroup" accept-charset="UTF-8">
							<dl class="select">
								<dt>';

	// Create the membergroup selection list
	foreach ($context['groups'] as $group)
		echo '
									<input type="checkbox" name="who[', $group['id'], ']" id="who', $group['id'], '" value="', $group['id'], '" class="input_check"' . ((isset($_POST['who'][$group['id']])) ? 'checked="checked"' : '') . ' /> ', $group['name'], ' <em>(', $group['member_count'], ')</em>';

	echo '
									<br class="clear" />
								</dt>
							</dl>
							<div class="submitbutton">
								<input type="submit" class="button_submit" value="', $txt['awards_mem_group'], '" tabindex="', $context['tabindex']++, '" />
								<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							</div>
						</form>
					</div>
				</div>

				<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=awards;sa=assignmass;step=3" method="post" name="assigngroup2" id="assigngroup2" accept-charset="UTF-8">
				<div class="floatleft" style="width:22%">
					<h3 class="category_header">
						', $txt['awards_select_badge'], '
					</h3>
					<div class="windowbg">
						<div class="content">
							<dl id="awardselect" class="settings">
								<dt>
									<select name="award" onchange="showaward();" size="10">';

	// Loop and show the drop down.
	foreach ($context['awards'] as $key => $award)
		echo '
										<option title="', $award['description'], '" value="', $key, '" ', isset($_REQUEST['a_id']) && $_REQUEST['a_id'] == $key ? 'selected="selected"' : '', '>', $award['award_name'], '</option>';

	echo '
									</select>
								</dt>
							</dl>
						</div>
					</div>
				</div>

				<div class="floatright" style="width:75%">
					<h3 class="category_header hdicon cat_img_plus">
						', $txt['awards_assign_badge'], '
					</h3>
					<div class="windowbg">
						<div class="content">
							<dl class="settings">
								<dt>
									<label for="awards"><b>', $txt['awards_image'], ':</b></label>
								</dt>
								<dd>
									<img id="awards" src="', isset($_REQUEST['a_id']) ? dirname($scripturl) . '/' . $modSettings['awards_dir'] . '/' . $context['awards'][$_REQUEST['a_id']]['filename'] : '', '" align="middle"  alt=""/>
								</dd>
								<dt>
									<label for="miniawards"><b>', $txt['awards_mini'], ':</b></label>
								</dt>
								<dd>
									<img id="miniawards" src="', isset($_REQUEST['a_id']) ? dirname($scripturl) . '/' . $modSettings['awards_dir'] . '/' . $context['awards'][$_REQUEST['a_id']]['minifile'] : '', '" align="middle"  alt=""/>
								</dd>
								<dt>
									<label for="date_received"><b>', $txt['awards_date'], '</b></label>:
								</dt>
								<dd id="date_received">';

	// The month... and day... and year...
	echo '
									<select name="month" tabindex="', $context['tabindex']++, '">';

	foreach ($txt['months'] as $key => $month)
		echo '
										<option value="', $key, '" ', date('F') == $month ? 'selected="selected"' : '', '>', $month, ' </option>';

	echo '
									</select>
									<select name="day" tabindex="', $context['tabindex']++, '">';

	for ($i = 1; $i <= 31; $i++)
		echo '
										<option value="', $i, '" ', date('j') == $i ? 'selected="selected"' : '', '>', $i, ' </option>';

	echo '
									</select>
									<select name="year" tabindex="', $context['tabindex']++, '">';

	for ($i = date('Y') + 5; $i >= date('Y') - 5; $i--)
		echo '
										<option value="', $i, '" ', date('Y') == $i ? 'selected="selected"' : '', '>', $i, ' </option>';

	echo '
									</select>
								</dd>
							</dl>

							<h3 class="secondary_header">
								<img class="icon" src="' . $settings['images_url'] . '/awards/multiple.png" alt="" />', $txt['awards_select_member'], '
							</h3>
							<div class="windowbg">';

	// Show the member selection boxes if they have chosen a member group.
	if (empty($context['members']))
	{
		echo '
								<div class="infobox">', $txt['awards_mem_mass_desc'], '</div>';
	}
	else
	{
		// Select the members to give a badge
		$columns = 5;
		$counter = 0;

		echo '
								<table width="100%" cellpadding="5" cellspacing="0" border="0" align="center" class="tborder">';

		foreach ($context['members'] as $key => $member)
		{
			// Open the tr
			if ($counter == 0)
				echo '
									<tr>';

			// The member
			echo '
										<td class="windowbg2">
											<label for="member', $key, '">
												<input type="checkbox" name="member[]" id="member', $key, '" value="', $key, '" checked="checked" class="check" /> ', $member, '
											</label>
										</td>';
			$counter++;

			// Close the tr
			if ($counter == $columns)
			{
				echo '
									</tr>';

				// Reset the counter
				$counter = 0;
			}
		}

		// Make sure the last one is closed
		if ($counter != 0)
		{
			if ($columns - $counter > 0)
			{
				for ($i = 0; $i < $columns - $counter; $i++)
					echo '
										<td class="windowbg2">&nbsp;</td>';
			}

			echo '
									</tr>';
		}

		// Close the table
		echo '
									<tr>
										<td  colspan="', $columns, '" class="windowbg2 righttext">
											<label for="checkAllGroups">
												<input type="checkbox" id="checkAllGroups" checked="checked" onclick="invertAll(this, this.form, \'member\');" class="check" /> <i>', $txt['check_all'], '</i>
											</label><br />
										</td>
									</tr>
								</table>';

		// show the submit box
		echo '
								<hr class="hrcolor" />
								<div class="submitbutton">
									<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
									<input type="submit" class="button_submit" value="', $txt['awards_button_assign'], '" tabindex="', $context['tabindex']++, '" />
								</div>';
	}

	// close this page up
	echo '
							</div>
						</div>
					</div>
				</div>
				</form>
			</div>
		</div>';

	// Create a javascript array from our php awards array so we can use it
	$script = "var awardValues = [";
	foreach ($context['awards'] as $key => $value)
	{
		if ($key < (count($context['awards']) - 1))
			$script = $script . implode(",", $value) . ',';
		else
			$script = $script . implode(",", $value) . "];\n";
	}
	$script = $script . "</script>";

	echo '
		<script type="text/javascript"><!-- // --><![CDATA[
			function showaward()
			{
				awards = ' . $context['awardsjavasciptarray'] . '
				document.getElementById(\'awards\').src = \'' . dirname($scripturl) . '/' . $modSettings['awards_dir'] . '/\' + awards[document.forms.assigngroup2.award.value][\'filename\'];
				document.getElementById(\'miniawards\').src = \'' . dirname($scripturl) . '/' . $modSettings['awards_dir'] . '/\' + awards[document.forms.assigngroup2.award.value][\'minifile\'];
			}
		// ]]></script>';
}

/**
 * View all members that have been assigned an award (admin panel view)
 */
function template_view_assigned()
{
	global $context;

	echo '
	<span class="upperframe"><span></span></span>
	<div class="roundframe">
		<div id="award">
			<img style="vertical-align:middle;padding:0 5px" src="', $context['award']['img'], '" alt="', $context['award']['award_name'], '" />
			<img style="vertical-align:middle;padding:0 5px" src="', $context['award']['small'], '" alt="', $context['award']['award_name'], '" />
			- <strong>', $context['award']['award_name'], '</strong> - ', $context['award']['description'], '
		</div>
	</div>
	<span class="lowerframe"><span></span></span>
	<br class="clear" />';

	template_show_list('view_assigned');

	echo '
	<br class="clear" />';
}

/**
 * Template for showing our settings to control the addon
 */
function template_settings()
{
	global $context, $txt, $scripturl, $modSettings;

	// Just saved, let them know
	if (isset($_GET['saved']))
		echo'
					<div class="infobox">',
						$txt['awards_saved_settings'], '
					</div>';

	// On to all the settings!
	echo '
					<h3 class="category_header hdicon cat_img_config">
						', $txt['awards_settings'], '
					</h3>

					<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=awards;sa=settings;saved=1" method="post" name="badge" id="badge" accept-charset="UTF-8" enctype="multipart/form-data" >
						<fieldset style="border-width: 1px 0px 0px 0px; padding: 5px 10xp;">
						<legend>', $txt['awards_basic_settings'], '</legend>
						<div class="roundframe">
						<dl class="settings">
							<dt>
								<label for="awards_enabled">', $txt['awards_enabled'], '</label>:<br />
								<span class="smalltext">', $txt['awards_enabled_desc'], '</span>
							</dt>
							<dd>
								<input type="checkbox" name="awards_enabled" id="awards_enabled" ', empty($modSettings['awards_enabled']) ? '' : 'checked="checked"', ' />
							</dd>

							<dt>
								<label for="awards_dir">', $txt['awards_badges_dir'], '</label>:<br />
								<span class="smalltext">', $txt['awards_badges_dir_desc'], '</span>
							</dt>
							<dd>
								<input type="text" name="awards_dir" id="awards_dir" value="', empty($modSettings['awards_dir']) ? '' : $modSettings['awards_dir'], '" size="30" />
							</dd>

							<dt>
								<label for="awards_favorites">', $txt['awards_favorite'], '</label>:<br />
								<span class="smalltext">', $txt['awards_favorite_desc'], '</span>
							</dt>
							<dd>
								<input type="checkbox" name="awards_favorites" id="awards_favorites" ', empty($modSettings['awards_favorites']) ? '' : 'checked="checked"', ' />
							</dd>

							<dt>
								<label for="awards_in_post">', $txt['awards_in_post'], '</label>:<br />
								<span class="smalltext">', $txt['awards_in_post_desc'], '</span>
							</dt>
							<dd>
								<input type="text" name="awards_in_post" id="awards_in_post" value="', empty($modSettings['awards_in_post']) ? '0' : $modSettings['awards_in_post'], '" size="3" />
							</dd>
						</dl>
						</div>
						</fieldset>

						<fieldset style="border-width: 1px 0px 0px 0px; padding: 5px 10xp;">
						<legend>', $txt['awards_aboveavatar_style'], '</legend>
						<div class="roundframe">
						<dl class="settings">
							<dt>
								<label for="awards_aboveavatar_title">', $txt['awards_aboveavatar_title'], '</label>:<br />
								<span class="smalltext">', $txt['awards_aboveavatar_title_desc'], '</span>
							</dt>
							<dd>
								<input type="text" name="awards_aboveavatar_title" id="awards_aboveavatar_title" value="', empty($modSettings['awards_aboveavatar_title']) ? '' : $modSettings['awards_aboveavatar_title'], '" size="30" />
							</dd>
							<dt>
								<label for="awards_aboveavatar_format">', $txt['awards_aboveavatar_format'], '</label>:<br />
								<span class="smalltext">', $txt['awards_aboveavatar_format_desc'], '</span>
							</dt>
							<dd>
								<select name="awards_aboveavatar_format" id="awards_aboveavatar_format">';

	$select = !empty($modSettings['awards_aboveavatar_format']) ? $modSettings['awards_aboveavatar_format'] : 0;
	foreach ($context['award_formats'] as $format)
		echo '
									<option value="', $format['id'], '"', ($format['id'] == $select) ? ' selected="selected"' : '', '>', $format['name'], '</option>';

	echo '
								</select>
							</dd>
						</dl>
						</div>
						</fieldset>

						<fieldset style="border-width: 1px 0px 0px 0px; padding: 5px 10xp;">
						<legend>', $txt['awards_belowavatar_style'], '</legend>
						<div class="roundframe">
						<dl class="settings">
							<dt>
								<label for="awards_belowavatar_title">', $txt['awards_belowavatar_title'], '</label>:<br />
								<span class="smalltext">', $txt['awards_belowavatar_title_desc'], '</span>
							</dt>
							<dd>
								<input type="text" name="awards_belowavatar_title" id="awards_belowavatar_title" value="', empty($modSettings['awards_belowavatar_title']) ? '' : $modSettings['awards_belowavatar_title'], '" size="30" />
							</dd>
							<dt>
								<label for="awards_belowavatar_format">', $txt['awards_belowavatar_format'], '</label>:<br />
								<span class="smalltext">', $txt['awards_belowavatar_format_desc'], '</span>
							</dt>
							<dd>
								<select name="awards_belowavatar_format" id="awards_belowavatar_format">';

	$select = !empty($modSettings['awards_belowavatar_format']) ? $modSettings['awards_belowavatar_format'] : 0;
	foreach ($context['award_formats'] as $format)
		echo '
									<option value="', $format['id'], '"', ($format['id'] == $select) ? ' selected="selected"' : '', '>', $format['name'], '</option>';

	echo '
								</select>
							</dd>
							</dl>
						</div>
						</fieldset>

						<fieldset style="border-width: 1px 0px 0px 0px; padding: 5px 10xp;">
						<legend>', $txt['awards_signature_style'], '</legend>
						<div class="roundframe">
						<dl class="settings">
							<dt>
								<label for="awards_signature_title">', $txt['awards_signature_title'], '</label>:<br />
								<span class="smalltext">', $txt['awards_signature_title_desc'], '</span>
							</dt>
							<dd>
								<input type="text" name="awards_signature_title" id="awards_signature_title" value="', empty($modSettings['awards_signature_title']) ? '' : $modSettings['awards_signature_title'], '" size="30" />
							</dd>
							<dt>
								<label for="awards_signature_format">', $txt['awards_signature_format'], '</label>:<br />
								<span class="smalltext">', $txt['awards_signature_format_desc'], '</span>
							</dt>
							<dd>
								<select name="awards_signature_format" id="awards_signature_format">';

	$select = !empty($modSettings['awards_signature_format']) ? $modSettings['awards_signature_format'] : 0;
	foreach ($context['award_formats'] as $format)
		echo '
									<option value="', $format['id'], '"', ($format['id'] == $select) ? ' selected="selected"' : '', '>', $format['name'], '</option>';

	echo '
								</select>
							</dd>
						</dl>
						</div>
						</fieldset>

						<hr class="hrcolor" />
						<div class="submitbutton">
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							<input type="submit" class="button_submit" name="save_settings" value="', $txt['save'], '" accesskey="s" />
						</div>
					</form>
				';
}

/**
 * Template for showing our category editing panel
 */
function template_edit_category()
{
	global $context, $txt, $scripturl;

	echo '
				<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=awards;sa=editcategory" method="post" name="category" id="category" accept-charset="UTF-8">
					<h3 class="category_header">
						', ((isset($_GET['saved']) && $_GET['saved'] == '1') ? $txt['awards_saved_category'] : ($context['editing'] == true ? $txt['awards_edit_category'] : $txt['awards_add_category'])), '
					</h3>
					<div class="windowbg">
						<div class="content">
							<dl class="settings">
								<dt>
									<label for="category_name">', $txt['awards_category_name'], '</label>:
								</dt>
								<dd>
									<input type="text" name="category_name" id="category_name" value="', $context['category']['name'], '" size="30" />
								</dd>
							</dl>
							<div class="submitbutton">
								<input type="hidden" name="id_category" value="', $context['category']['id'], '" />
								<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
								<input type="submit" class="button_submit" name="category_save" value="', $context['editing'] ? $txt['save'] : $txt['awards_add_category'], '" accesskey="s" />
							</div>
						</div>
					</div>
				</form>';
}

/**
 * Show all of the categorys in the system with modificaiton options
 */
function template_list_categories()
{
	global $context, $txt, $settings, $scripturl;

	echo '
			<form id="admin_form_wrapper" accept-charset="UTF-8" method="post" action="', $scripturl, '?action=admin;area=awards;sa=editcategory">
				<h3 class="category_header hdicon cat_img_database">
					', $txt['awards_list_categories'], '
				</h3>
				<table class="table_grid">
					<thead>
						<tr class="table_head">
							<th class="grid20" scope="col">', $txt['awards_actions'], '</th>
							<th scope="col">', $txt['awards_category_name'], '</th>
							<th class="grid20 centertext" scope="col">', $txt['awards_num_in_category'], '</th>
						</tr>
					</thead>
					<tbody>';

	// Check if there are any categories
	if (empty($context['categories']))
		echo '
						<tr class="windowbg2">
							<td colspan="3">', $txt['awards_error_no_categories'], '</td>
						</tr>';
	else
	{
		foreach ($context['categories'] as $cat)
		{
			echo '
						<tr class="windowbg">
							<td>
								<a href="', $cat['edit'], '" title="', $txt['awards_button_edit'], '">[', $txt['awards_button_edit'], ']&nbsp;<img class="icon" src="', $settings['images_url'], '/awards/modify.png" alt="" /></a> ', ($cat['id'] != 1) ? '
								<a href="' . $cat['delete'] . '" onclick="return confirm(\'' . $txt['awards_confirm_delete_category'] . '\');" title="' . $txt['awards_button_delete'] . '">
									[' . $txt['awards_button_delete'] . ']&nbsp;<img class="icon" src="' . $settings['images_url'] . '/awards/delete.png" alt="" />
								</a>' : '', '
							</td>
							<td>
								<a href="', $cat['view'], '" title="', $cat['name'], '">', $cat['name'], '</a>
							</td>
							<td class="centertext">
								', empty($cat['awards']) ? '0' : '<a href="' . $scripturl . '?action=admin;area=awards;sa=viewcategory;a_id=' . $cat['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $cat['awards'] . '</a>', '
							</td>
						</tr>';
		}
	}

	echo '
					</tbody>
				</table>
				<div class="submitbutton">
					<input id="add_category" class="button_submit" type="submit" value="', $txt['awards_add_category'], '" name="add_category" />
				</div>
			</form>';
}

/**
 * View a single category list with all its awards
 */
function template_view_category()
{
	global $context, $txt;

	if (empty($context['category']))
	{
		echo '
			<div class="roundframe">
				<div id="welcome">',
					$txt['awards_error_no_category'], '
				</div>
			</div>';
	}
	else
	{
		echo '
				<h3 class="category_header">
					', $context['category'], '
				</h3>
				<table class="table_grid">
					<thead>
						<tr class="table_head">
							<th class="centertext" scope="col">', $txt['awards_image'], '</th>
							<th class="centertext" scope="col">', $txt['awards_mini'], '</th>
							<th scope="col">', $txt['awards_name'], '</th>
							<th scope="col" class="centertext">', $txt['awards_description'], '</th>
						</tr>
					</thead>
					<tbody>';

		// Check if there are any awards
		if (empty($context['awards']))
			echo '
						<tr class="windowbg2">
							<td colspan="4">', $txt['awards_error_empty_category'], '</td>
						</tr>';
		else
		{
			foreach ($context['awards'] as $award)
			{
				echo '
						<tr class="windowbg">
							<td>
								<img src="', $award['img'], '" alt="', $award['award_name'], '" />
							</td>
							<td>
								<img src="', $award['small'], '" alt="', $award['award_name'], '" />
							</td>
							<td>
								<a href="', $award['edit'], '">', $award['award_name'], '</a>
							</td>
							<td>', $award['description'], '</td>
						</tr>';
			}
		}

		echo '
					</tbody>
				</table>';

		// Show the pages
		echo '
				<div class="floatleft pagesection">', $txt['pages'], ': ', $context['page_index'], '</div>';
	}
}

/**
 * Template for viewing the requested awards
 */
function template_request_award()
{
	global $context, $txt, $scripturl;

	// Nothing to approve at this time?
	if (empty($context['awards']))
	{
		echo '
			<div class="infobox">',
				$txt['awards_no_requests'], '
			</div>';
	}
	else
	{
		// There be requests woohoo!
		echo '
			<h3 class="category_header">
				', $txt['awards_requests'], '
			</h3>';

		// Start with the form.
		echo '
				<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=awards;sa=requests" method="post" name="requests" accept-charset="UTF-8" enctype="multipart/form-data">';

		// Loop through the awards
		foreach ($context['awards'] as $award)
		{
			// show this awards info in the header
			echo '
					<div class="description centertext">
						<img style="padding:0 0 5px 0" src="', $award['img'], '" alt="', $award['award_name'], '" /><br />';

			// Small image as well?
			if ($award['img'] != $award['small'])
				echo '
						<img style="vertical-align:middle" src="', $award['small'], '" alt="', $award['award_name'], '" /> ';

			echo '
						<strong>', $award['award_name'], '</strong><br />', $award['description'], '
					</div>

					<div class="windowbg">
						<div class="content">';

			// Now output the table of members who requested this award
			echo '
							<table class="table_grid">
								<thead>
									<tr class="table_head">
										<th scope="col" class="grid25">', $txt['who_member'], '</th>
										<th scope="col"', $txt['awards_comments'], '</th>
										<th scope="col" class="grid8 centertext">
											<input type="checkbox" id="checkAllMembers', $award['id'], '" checked="checked" onclick="invertAll(this, this.form, \'requests[', $award['id'], ']\');" class="check" />
										</th>
									</tr>
								</thead>
								<tbody>';

			foreach ($award['members'] as $id => $member)
			{
				echo '
									<tr>
										<td>
											', $member['link'], '<span class="floatright">
											', $member['pm'], '&nbsp;</span>
										</td>
										<td>
											', $member['comments'], '
										</td>
										<td class="centertext">
											<input type="checkbox" name="requests[', $award['id'], '][', $id, ']" value="', $id, '" checked="checked" class="check" />
										</td>
									</tr>';
			}

			echo '
								</tbody>
							</table>
						</div>
					</div>
					<hr class="hrcolor" />';
		}

		// Submit button
		echo '
					<div class="submitbutton">
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						<input type="submit" class="button_submit" name="reject_selected" value="', $txt['awards_reject_selected'], '" />
						<input type="submit" class="button_submit" name="approve_selected" value="', $txt['awards_approve_selected'], '" />
					</div>';

		// close this beast up
		echo '
				</form>';
	}
}