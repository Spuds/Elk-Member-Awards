<?php

// Tabs
$txt['awards_title'] = 'Awards';
$txt['awards_description_main'] = 'This is the main panel for the awards system, from here you can add, edit, delete and assign awards.';
$txt['awards_description_assign'] = 'Select and award and then select members to assign the award.  Multiple members may be selected.';
$txt['awards_description_assigngroup'] = 'Select a membergroup to assign an award, all members of the group will receive the award.';
$txt['awards_description_assignmass'] = 'Use this function to assign an award to many members at once';
$txt['awards_description_modify'] = 'Add a new or modify an existing award';
$txt['awards_description_delete'] = 'Remove an award from the system, this includes removing the award from any members that have it';
$txt['awards_description_edit'] = 'This is where you add awards.';
$txt['awards_description_settings'] = 'Set the overall options for the awards system';
$txt['awards_description_viewassigned'] = 'View all members with an award';
$txt['awards_description_categories'] = 'Viewing all categories in the award system';
$txt['awards_description_editcategory'] = 'Edit an existing category name';
$txt['awards_description_addcategory'] = 'Add a new category name to the awards system';
$txt['awards_description_deletecategory'] = 'Remove a category from the system';
$txt['awards_description_viewcategory'] = 'Viewing a category and all awards in that category';


$txt['awards_description_profiles'] = 'Viewing all categories in the award system';
$txt['awards_description_editprofile'] = 'Edit an existing profile name';
$txt['awards_description_addprofile'] = 'Add a new profile name to the awards system.  Profiles can only be used with automatically assigned awards.';
$txt['awards_description_deleteprofile'] = 'Remove a profile from the system';
$txt['awards_description_viewprofile'] = 'Viewing a profile and all awards in that profile';
$txt['awards_profile_help'] = 'This is where you add, edit, delete award profiles.  Profiles can be used to group award
parameters so they are applied to specific awards.  Profiles can only used on automatically assigned awards. For example
if you wanted to have various levels of top count awards, but only wanted to include specific boards in that topic count.';
$txt['awards_description_requests'] = 'This is where you approve or deny member requests for awards';
$txt['awards_help'] = 'This is where you add, edit, delete and assign awards.';
$txt['awards_category_help'] = 'This is where you add, edit, delete award categories';
$txt['awards_main'] = 'Main';
$txt['awards_add'] = 'Create';
$txt['awards_edit'] = 'Edit';
$txt['awards_categories'] = 'Categories';
$txt['awards_profiles'] = 'Profiles';
$txt['awards_assign'] = 'Member Assign';
$txt['awards_assign_membergroup'] = 'Group Assign';
$txt['awards_assign_mass'] = 'Multi Assign';
$txt['awards_settings'] = 'Settings';
$txt['awards_delete'] = 'Delete';
$txt['awards_requests'] = 'Requested Awards';

// Errors
$txt['awards_error_wrong_extension'] = 'The file you are trying to upload is not allowed.  Please upload a valid file.';
$txt['awards_error_upload_size'] = 'The file you tried to upload exceeds the maximum upload size. Please try again with a smaller file.';
$txt['awards_error_upload_security_failed'] = 'The file could not be processed by the system (bad or incomplete file).';
$txt['awards_error_no_file'] = 'You must select a file to upload!';
$txt['awards_error_upload_failed'] = 'There was an error uploading the file.  Please check the file and try again.';
$txt['awards_error_no_id'] = 'You must select an award to edit.';
$txt['awards_error_no_id_category'] = 'You must select a category to edit.';
$txt['awards_error_no_id_profile'] = 'You must select a profile to edit.';
$txt['awards_error_no_award'] = 'The award that you entered does not exist.';
$txt['awards_error_no_category'] = 'The category that you entered does not exist.';
$txt['awards_error_no_profile'] = 'The profile that you entered does not exist.';

$txt['awards_error_empty_type'] = 'The award type was not specified';
$txt['awards_error_empty_award_name'] = 'You must enter a name for this category.';
$txt['awards_error_empty_category_name'] = 'You must enter a name for this award.';
$txt['awards_error_empty_category'] = 'That category doesn\'t have any awards.';
$txt['awards_error_empty_profile_name'] = 'You must enter a name for this profile';
$txt['awards_error_empty_profile'] = 'That profile doesn\'t have any awards.';

$txt['awards_error_empty_description'] = 'You must enter a description for this award!';
$txt['awards_error_cant_delete'] = 'You are not allowed to delete awards!';
$txt['awards_error_no_who'] = 'You must select the membergroups that you want to assign an award to.';
$txt['awards_error_no_members'] = 'You must select a member and/or award in order to continue!';
$txt['awards_error_no_group'] = 'You must select a group and/or award in order to continue!';
$txt['awards_error_no_awards'] = 'No Awards have been added';
$txt['awards_error_no_categories'] = 'You have not added any categories.';
$txt['awards_error_no_profiles'] = 'You have not added any profiles.';
$txt['awards_error_delete_main_category'] = 'You cannot delete category #1 because it is the default category.';
$txt['awards_error_delete_main_profile'] = 'You cannot delete profile #1 because it is the default profile.';

$txt['awards_error_upload_invalid'] = 'The file you tried to upload did not pass security checks. Please try again with another file.';
$txt['awards_error_hack_error'] = 'You are trying to access an area or do something you are not permitted to do .... Hacking Attempt';
$txt['awards_error_not_requestable'] = 'You have attempted to request an award which is NOT requestable';
$txt['awards_error_have_already'] = 'You can not request an award that you already have !';

// Used by: Awards Errors
$txt['cannot_manage_awards'] = 'You aren\'t allowed to manage awards.';

// Used by: Awards Help
$helptxt['awards'] = 'This area allows you to manage, assign, and categorize awards, as well as change a few settings.';

// Add/edit award
$txt['awards_add_name'] = 'Award Details';
$txt['awards_add_type'] = 'Award Type';
$txt['awards_add_image'] = 'Award Images';
$txt['awards_add_award'] = 'Add Award';
$txt['awards_edit_award'] = 'Edit Award';
$txt['awards_description'] = 'Award Description';
$txt['awards_badge_name'] = 'Award Name<br /><span class="smalltext">e.g. Most Posts, Top Topic Starter</span>';
$txt['awards_edit_description'] = 'Description<br /><span class="smalltext">Provide a description for this award to show when you hover over an award image</span>';
$txt['awards_badge_upload'] = 'Normal Image';
$txt['awards_badge_upload_mini'] = 'Miniature Image:<br /><span class="smalltext">Don\'t have a small image? Leave this empty, and it will use the normal one</span>';
$txt['awards_submit'] = 'Save Award';
$txt['awards_saved_award'] = 'Your changes have been <strong>Saved</strong>';
$txt['awards_manage_awards'] = 'Manage Awards';
$txt['awards_category'] = 'Category';
$txt['awards_image_placement'] = 'Show this award image';
$txt['awards_image_placement_above'] = 'Above Post Avatar';
$txt['awards_image_placement_below'] = 'Below Post Avatar';
$txt['awards_image_placement_sig'] = 'With Post Signature';
$txt['awards_image_placement_off'] = 'Don\'t show in Post Page';
$txt['awards_type'] = 'Type<br /><span class="smalltext">Type of award, regular and group awards are individually assigned, all others are automatically assigned based on trigger values</span>';
$txt['awards_group'] = 'Group';
$txt['awards_group_desc'] = 'Not applicable for group awards, these must be individually assigned to a group';


// Built in awards, follows award_ID where ID is set from the AwardsLoadType() function
$txt['awards_Regular'] = 'Regular Award';
$txt['awards_Regular_short'] = 'Create awards that are manually assignable to members';
$txt['awards_Regular_desc'] = 'Regular awards must be individually assigned to a member.  You can optionally define reputation points to give with the award';

$txt['awards_Group'] = 'Group Award';
$txt['awards_Group_short'] = 'Create awards that are manually assignable to groups';
$txt['awards_Group_desc'] = 'Group awards must be individually assigned to member groups.  You can optionally define reputation points to give with the award';

/*
$txt['awards_limit'] = 'Number of awards';
$txt['awards_trigger'] = 'Trigger Value';
$txt['awards_points'] = 'Points Earned';
$txt['awards_points_desc'] = 'The number of reputation points earned for this award.';
*/

$txt['awards_Post_Count'] = 'Post Count';
$txt['awards_Post_Count_desc'] = 'Define "post count" based awards.  You may optionally select just certain boards to consider in the post count totals.  You may define multiple post count awards with various levels, but only the highest will be awarded to a member.';
$txt['awards_Post_Count_short'] = 'Define automatically assigned Post Count based awards';
$txt['awards_Post_Count_boards'] = 'Board Restrictions';
$txt['awards_Post_Count_boards_desc'] = 'Only count posts made in these boards (comma separated list)';

$txt['awards_Top_Poster'] = 'Top Posters';
$txt['awards_Top_Poster_desc'] = 'Enter the number of members to receive this award in the limit field.  For example entering 10 will result in the top ten posters receiving the award.  Note: only the board restrictions are used from the chosen profile.';
$txt['awards_Top_Poster_short'] = 'Define automatically assigned awards for the (X) top posters';

$txt['awards_Post_Count_Liked'] = 'Liked Posts';
$txt['awards_Post_Count_Liked_desc'] = 'Enter the number of individual posts with likes that a member must have received to get this award.  The system uses a threshold value to set a minimum post like value.  For example setting a trigger of 10 and a threshold of 2, means the member must have 10 posts with at least 2 likes to get the award.';
$txt['awards_Post_Count_Liked_short'] = 'Define automatically assigned awards that are based on individual post "like" totals';

$txt['awards_Topic_Count'] = 'Topic Count';
$txt['awards_Topic_Count_desc'] = 'Enter the minimum number of topics that a member must have started to receive this award.  If you define multiple award levels only the highest will be awarded to the member.';
$txt['awards_Topic_Count_short'] = 'Enter automatically assigned Topic Started based awards';

// Profiles
$txt['awards_parameter_min_topic_replies'] = 'Only include topics that have received at least these many replies';
$txt['awards_parameter_min_post_likes'] = 'Only include posts with at least these many likes';
$txt['awards_parameter_profile_boards'] = 'Only count items started in these boards';

// Award parameters, generic values should specific ones not be defined by the award
$txt['awards_parameter_trigger'] = 'Trigger Value';
$txt['awards_parameter_trigger_desc'] = 'The minimum value at which this award will activate';
$txt['awards_parameter_points'] = 'Points Earned';
$txt['awards_parameter_points_desc'] = 'The number of reputation points earned for this award.';
$txt['awards_parameter_points_per'] = 'Points Earned Every:';
$txt['awards_parameter_points_per_desc'] = 'The points are earned every (X), ie 100 posts.  Leave blank to earn the point value when the award is achieved.';
$txt['awards_parameter_boards'] = 'Boards';
$txt['awards_parameter_boards_desc'] = 'Only items in these boards are considered';

$txt['awards_top_topic_starters'] = 'Top Topic Starters';
$txt['awards_top_topic_starters_desc'] = 'Enter the number of members to receive this award in the limit field.  For example entering 10 will result in the top ten topic starters receiving the award.  Only board restrictions are used from the chosen profile.';

$txt['awards_time_online'] = 'Most Time Online';
$txt['awards_time_online_desc'] = 'Enter the number of members to receive this award.  For example entering 10 will result in the top ten time online members receiving the award';

$txt['awards_member_since'] = 'Member Since';
$txt['awards_member_since_desc'] = 'Enter the number of years that someone must be a member to receive this award.';

$txt['awards_karma_level'] = 'Karma Level';
$txt['awards_karma_level_desc'] = 'Enter the minimum Karma Level a member must have to receive this award.  If you define multiple award levels only the highest will be awarded to the member.';

$txt['awards_parameter_likes'] = 'Liked threshold';
$txt['awards_parameter_likes_desc'] = 'Enter the minimum likes a post must have to be counted.  If you define multiple award levels only the highest will be awarded to the member.';

$txt['awards_requestable'] = 'Requestable Award';
$txt['awards_requestable_desc'] = 'Check this if you want to allow users to request this award.';
$txt['awards_assignable'] = 'Assignable Award';
$txt['awards_assignable_desc'] = 'Check this if you want to allow this award to be assignable by members who have the the assign_award permission. Note: Awards are always assignable with the manage_awards permission';
$txt['awards_extras'] = 'Optional Award Settings';

// Assign page
$txt['awards_mem_group'] = 'Select Membergroups';
$txt['awards_mem_group_desc'] = 'Select the membergroups that you would like to assign an award to.';
$txt['awards_mem_mass_desc'] = 'Please select the membergroup(s) to generate a member list to select from.';
$txt['awards_select_badge'] = 'Select Award to Assign';
$txt['awards_assign_badge'] = 'Assign Selected Award';
$txt['awards_select_member'] = 'Select Members';
$txt['awards_member_name'] = 'Member Name';
$txt['awards_member_selected'] = 'Selected Members';
$txt['awards_select_member_desc'] = 'Enter a name and then select them from the list that appears.  You may do this multiple times to assign the same award to several members';
$txt['awards_select_group'] = 'Select Membergroups';
$txt['awards_date'] = 'Date Received';
$txt['awards_date_given'] = 'Date Given';
$txt['awards_options'] = 'Options';
$txt['awards_assign_intro'] = 'From here, you can assign awards that you have created to your members.  Start by selecting one of the awards from the Select Award list.  Next select the date the award was given and then select the member.  You can select multiple members, begin by entering their user name in the select member box, a selection drop down list will appear as you type, select the correct user name from this drop down list.';
$txt['awards_assigngroup_intro'] = 'From here, you can assign awards to specific member groups.  Start by selecting one of the awards from the Select Award list.  Next select the date the award was given and then select the membergroup.  You can select multiple groups.  All members in those groups will get the award.  Removing  a member from the group removes the award while adding a new member to the group will automatically give them the award.';
$txt['awards_assignmass_intro'] = 'From here, you can quickly assign awards to large groups of members.  <strong>Start</strong> by selecting one or more of the membergroups to generate a list of all members in those groups. You can then choose all of those members or a subset of them to assign awards.';
$txt['awards_error_no_groups'] = 'No groups were selected, you must select a membergroup to assign the award to.';
$txt['awards_error_no_members'] = 'No members were selected, you must select some members to assign the award to.';

// Add/edit category
$txt['awards_add_category'] = 'Add Category';
$txt['awards_edit_category'] = 'Edit Category';
$txt['awards_category_name'] = 'Category Name';
$txt['awards_saved_category'] = 'Category Saved!';
$txt['awards_manage_categories'] = 'Manage Categories';
$txt['awards_list_categories'] = 'Categories';
$txt['awards_num_in_category'] = 'Awards in Category';
$txt['awards_viewing_category'] = 'Viewing Category';

// Add/edit profile
$txt['awards_add_profile'] = 'Add Profile';
$txt['awards_edit_profile'] = 'Edit Profile';
$txt['awards_profile_name'] = 'Profile Name';
$txt['awards_saved_profile'] = 'Profile Saved!';
$txt['awards_manage_profiles'] = 'Manage Profiles';
$txt['awards_list_profiles'] = 'Profiles';
$txt['awards_num_in_profile'] = 'Awards using profile';
$txt['awards_viewing_profile'] = 'Viewing profile';
$txt['awards_profile'] = 'Profiles';

// Settings page
$txt['awards_basic_settings'] = 'Set the Award Modification Options';
$txt['awards_enabled'] = 'Enable Awards';
$txt['awards_enabled_desc'] = 'This is the master setting that enables the awards for the site.';
$txt['awards_badges_dir'] = 'Awards Directory';
$txt['awards_badges_dir_desc'] = 'This is the directory where awards are stored - just the name without the trailing slash.';
$txt['awards_favorite'] = 'Multiple Favorite Awards';
$txt['awards_favorite_desc'] = 'Check this if you want to allow users to have more than one favorite award.';
$txt['awards_in_post'] = 'Awards in Post';
$txt['awards_in_post_desc'] = 'How many awards can be shown in each position? Set to 0 to show none.';
$txt['awards_saved_settings'] = 'Settings Saved!';
$txt['awards_aboveavatar_format'] = 'Above avatar awards framing';
$txt['awards_aboveavatar_format_desc'] = 'For awards that are shown above the avatar, define the frame formatting';
$txt['awards_aboveavatar_title'] = 'Above avatar awards title';
$txt['awards_aboveavatar_title_desc'] = 'For awards that are shown above the avatar, define the frame title or leave it blank for none';
$txt['awards_aboveavatar_style'] = 'Define the above avatar awards';
$txt['awards_belowavatar_format'] = 'Below avatar awards framing';
$txt['awards_belowavatar_format_desc'] = 'For awards that are shown below the avatar, define the frame formatting';
$txt['awards_belowavatar_title'] = 'Below avatar awards title';
$txt['awards_belowavatar_title_desc'] = 'For awards that are shown below the avatar, define the frame title or leave it blank for none';
$txt['awards_belowavatar_style'] = 'Define the below avatar awards';
$txt['awards_signature_format'] = 'Signature awards framing';
$txt['awards_signature_format_desc'] = 'For awards that are shown with the signature, define the frame formatting';
$txt['awards_signature_title'] = 'Signature awards title';
$txt['awards_signature_title_desc'] = 'For awards that are shown above the signature, define the frame title or leave it blank for none';
$txt['awards_signature_style'] = 'Define the signature awards';
$txt['awards_format_full_frame'] = 'Full frame';
$txt['awards_format_heading'] = 'Top bar only';
$txt['awards_format_no_frame'] = 'No frame';

// Requests
$txt['awards_comments'] = 'Comments';
$txt['awards_reject_selected'] = 'Reject Selected';
$txt['awards_approve_selected'] = 'Approve Selected';
$txt['awards_no_requests'] = 'There are no pending requests at this time';
$txt['awards_request_award'] = 'Request Award';
$txt['awards_requesting_award'] = 'Requesting Award:';
$txt['awards_request_comments'] = 'Enter a reason for requesting this award';

// Profile Section
$txt['awards_image'] = 'Image';
$txt['awards_mini'] = 'Mini Image';
$txt['awards_name'] = 'Award Name';
$txt['awards_favorite2'] = 'Favorite';
$txt['awards_details'] = 'Award Details';
$txt['awards_view_album'] = 'Show Awards';
$txt['showAwards'] = 'Show My Awards';
$txt['listAwards'] = 'Show Available Awards';
$txt['viewingAwards'] = 'Viewing Award:';

// Others
$txt['awards_showmembers'] = 'Showing Members with the Award';
$txt['awards_removemember_yn'] = 'Remove this award from the these members?';
$txt['awards_view_assigned'] = 'View Assigned';
$txt['awards_no_assigned_members'] = 'There are no members that have received this award.  To assign this award to a member click <a href="%1$s">here</a>';
$txt['awards_no_assigned_members2'] = 'There are no members or groups (visible to you), that have received this award.';
$txt['awards_unassign'] = 'Unassign Award';
$txt['awards_members_with'] = 'Members with Award';
$txt['awards_no_awards_member'] = 'You have not received any awards';
$txt['awards_no_awards_this_member'] = '%1$s has not received any awards';
$txt['awards_view_album'] = 'View member\'s awards album.';
$txt['awards_count_badges'] = 'This member has a total of <strong>%1$s</strong> award(s).  The award details are below:';
$txt['awards_confirm_delete_category'] = 'Are you sure you wish to delete this category?';
$txt['awards_confirm_delete_award'] = 'Are you sure you wish to delete this award?';
$txt['awards_confirm_delete_profile'] = 'Are you sure you wish to delete this profile?  Any awards using it will be moved to the default profile.';

$txt['awards_button_edit'] = 'Edit';
$txt['awards_button_delete'] = 'Delete';
$txt['awards_button_assign'] = 'Assign';
$txt['awards_button_members'] = 'Members';

// Used by: Awards ManagePermissions
$txt['permissionname_manage_awards'] = 'Manage Awards';
$txt['permissionhelp_manage_awards'] = 'This permission will allow users to add, edit and assign awards to other members';
$txt['permissionname_assign_awards'] = 'Assign Awards';
$txt['permissionhelp_assign_awards'] = 'This permission will allow users to assign awards to other users';

// Used by: Awards Multiple Areas
$txt['awards'] = 'Awards';
$txt['awards_desc'] = 'Award Description';
$txt['awards_view_album'] = 'View member\'s badge album.';
$txt['awards_modify'] = 'Modify';
$txt['awards_actions'] = 'Actions';
$txt['whoallow_awards'] = 'Managing <a href="' . $scripturl . '?action=awards">Awards</a>.';