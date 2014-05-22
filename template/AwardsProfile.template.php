<?php

/**
 * @name      Member Awards Addon
 * @license   Mozilla Public License version 1.1 http://www.mozilla.org/MPL/1.1/.
 *
 * @version   1.0 Alpha
 *
 * Original Software by:           Juan "JayBachatero" Hernandez
 * Copyright (c) 2006-2009:        YodaOfDarkness (Fustrate)
 * Copyright (c) 2010:             Jason "JBlaze" Clemons
 *
 */

/**
 * This is the template for showing a members awards from the profile view
 */
function template_awards()
{
	global $context, $txt, $settings;

	echo '
					<h3 class="category_header hdicon cat_img_award_add">
						' ,$txt['awards'], '
					</h3>';

	// Show the amount of awards that a member has
	if (!empty($context['count_awards']))
		echo '
					<p class="description">',
						sprintf($txt['awards_count_badges'], $context['count_awards']), '
					</p>';

	// Check if this member has any awards
	if (empty($context['categories']))
		echo '
					<div class="infobox">',
						$txt['awards_no_badges_member'], '
					</div>';
	else
	{
		// There be awards !!, output them by category for viewing
		foreach($context['categories'] as $category)
		{
			echo '
						<h3 class="category_header hdicon cat_img_database">
							', $txt['awards_category'], ': ', $category['name'], '
						</h3>
						<table class="table_grid">
						<thead>
							<tr class="table_head">
								<th scope="col" class="grid17 centertext">', $txt['awards_image'], '</th>
								<th scope="col" class="grid17 centertext">', $txt['awards_mini'], '</th>
								<th scope="col" class="grid17">', $txt['awards_name'], '</th>
								<th scope="col" class="grid17">', $txt['awards_date'], '</th>
								<th scope="col">', $txt['awards_details'], '</th>
								<th scope="col" class"centertext" class="grid8">', $txt['awards_favorite2'], '</th>
							</tr>
						</thead>
						<tbody>';

			// Output the awards for this category
			foreach ($category['awards'] as $award)
			{
				echo '
							<tr class="windowbg">
								<td class="centertext">
									<a href="', $award['more'], '">
										<img src="', $award['img'], '" alt="', $award['award_name'], '" />
									</a>
								</td>
								<td class="centertext">
									<a href="', $award['more'], '">
										<img src="', $award['small'], '" alt="', $award['award_name'], '" />
									</a>
								</td>
								<td>
									', $award['award_name'], '
								</td>
								<td>
									', $txt['months'][$award['time'][1]], ' ', $award['time'][2], ', ', $award['time'][0], '
								</td>
								<td>',
									$award['description'], '
								</td>
								<td class="centertext">',
									$context['allowed_fav'] && $award['favorite']['allowed'] ? '<a href="' . $award['favorite']['href'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . $award['favorite']['img'] . '</a>' : '',
									$award['favorite']['fav'] == 1 ? '<img src="' . $settings['images_url'] . '/awards/star.png" alt="' . $txt['awards_favorite']. '" />' : '', '
								</td>
							</tr>';
			}

			echo '
						</tbody>
					</table>';
		}

		// Show the pages
		echo '
				<div class="floatleft">', template_pagesection(), '</div>';
	}
}

/**
 * Template for showing all members that have a certain award
 */
function template_awards_members()
{
	global $context, $txt;

	// Open the Div
	echo '
		<h3 class="category_header hdicon cat_img_award_add">
			', $txt['viewingAwards'] . ' ' . $context['award']['award_name'], '
		</h3>
		<div class="description centertext">
			<img src="', $context['award']['img'], '" alt="', $context['award']['award_name'], '" />
			<br />';

	if ($context['award']['img'] != $context['award']['small'])
		echo '
			<img style="vertical-align:middle" src="', $context['award']['small'], '" alt="', $context['award']['award_name'], '" /> ';

	echo '
			<strong>', $context['award']['award_name'], '</strong>
			<br />', $context['award']['description'], '
		</div>';

	// Show the list output
	template_show_list('view_profile_assigned');
}

/**
 * Template for showing the awards that a member has
 */
function template_awards_list()
{
	global $context, $txt, $settings;

	echo '
				<h3 class="category_header hdicon cat_img_award_add">
					', $txt['awards_title'], '
				</h3>';

	// Check if there are any awards
	if (empty($context['categories']))
		echo '
				<div class="infobox">',
					$txt['awards_error_no_badges'], '
				</div>';
	else
	{
		foreach($context['categories'] as $key => $category)
		{
			echo '
					<h3 class="secondary_header">
						<img class="icon" src="' . $settings['images_url'] . '/awards/category.png" alt="" />&nbsp;', '<a href="', $category['view'], '">', $txt['awards_category'], ': ', $category['name'], '</a>
					</h3>
					<table class="table_grid">
					<thead>
						<tr class="table_head">
							<th scope="col" class="centertext grid17">', $txt['awards_image'], '</th>
							<th scope="col" class="centertext grid17">', $txt['awards_mini'], '</th>
							<th scope="col" class="grid25">', $txt['awards_name'], '</th>
							<th scope="col">', $txt['awards_details'], '</th>
							<th scope="col" class="centertext grid8">', $txt['awards_actions'], '</th>
						</tr>
					</thead>
					<tbody>';

			foreach ($category['awards'] as $award)
			{
				echo '
						<tr class="windowbg">
							<td align="center">
								<img src="', $award['img'], '" alt="', $award['award_name'], '" />
							</td>
							<td align="center">
								<img src="', $award['small'], '" alt="', $award['award_name'], '" />
							</td>
							<td>', $award['award_name'], '</td>
							<td>', $award['description'], '</td>
							<td class="centertext">
								<a href="', $award['view_assigned'], '">
									<img src="', $settings['images_url'], '/awards/user.png" title="', $txt['awards_button_members'], '" alt="" />
								</a>';

				if (!empty($award['requestable']))
					echo '
								<a href="', $award['requestable_link'], '">
									<img src="', $settings['images_url'], '/awards/award_request.png" title="', $txt['awards_request_award'], '" alt="" />
								</a>';

				echo '
							</td>
						</tr>';
			}

			echo '
					</tbody>
					</table>';
		}

		// Show the pages
		echo '
				<div class="floatleft">', template_pagesection(), '</div>';
	}
}

/**
 * Template for showing a list of requestable awards
 */
function template_awards_request()
{
	global $context, $scripturl, $txt;

	// Open the Header
	echo '
		<h3 class="category_header hdicon cat_img_award_add">
			', $txt['awards_requesting_award'] . ' ' . $context['award']['award_name'], '
		</h3>

		<div class="description centertext">
			<img src="', $context['award']['img'], '" alt="', $context['award']['award_name'], '" />
			<br />';

	if ($context['award']['img'] != $context['award']['small'])
		echo '
			<img style="vertical-align:middle" src="', $context['award']['small'], '" alt="', $context['award']['award_name'], '" /> ';

	echo '
			<strong>', $context['award']['award_name'], '</strong>
			<br />', $context['award']['description'], '
		</div>';

	// Start with the form.
	echo '
		<form id="generic_form_wrapper" action="', $scripturl, '?action=profile;area=requestAwards;step=2" method="post" name="request" id="request" accept-charset="UTF-8" enctype="multipart/form-data">';

	// Enter a reason why you want this award.
	echo '
			<table width="100%" class="table_grid">
				<thead>
					<tr class="titlebg">
						<th scope="col" class="first_th smalltext" >', $txt['awards_request_comments'], '</th>
						<th scope="col" class="last_th smalltext" ></th>
					</tr>
				</thead>
				<tbody>
					<tr class="windowbg2">
						<td colspan="2" align="center">
							<div style="margin-bottom: 2ex;">
								<textarea cols="75" rows="7" style="', isBrowser('is_ie8') ? 'max-width: 100%; min-width: 100%' : 'width: 100%', '; height: 100px;" name="comments" tabindex="', $context['tabindex']++, '"></textarea><br />
							</div>
						</td>
					</tr>
				</tbody>
			</table>';

	// add in a submit button and close the form
	echo '
			<div class="submitbutton">
				<input class="button_submit" type="submit" name="request" value="', $txt['awards_request_award'], '" />
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				<input type="hidden" name="id_award" value="', $context['award']['id'], '" />
			</div>
		</form>';
}