<?php

/**
 * Post Limit mod (SMF)
 *
 * @package SMF
 * @author Suki <suki@missallsunday.com>
 * @copyright 2025 Michel Mendiola
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 *
 * @version 1.1
 */

/*
 * Version: MPL 2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 * If a copy of the MPL was not distributed with this file,
 * You can obtain one at http://mozilla.org/MPL/2.0/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 */

 global $txt;

/* Admin panel */
$txt['PostLimit_message_default'] = 'Hi {username}, you don\'t have any more messages left for today.';
$txt['PostLimit_admin_panel'] = 'Post Limit admin panel';
$txt['PostLimit_admin_panel_desc'] = 'From here you can set some global settings and permissions for Post Limit mod';
$txt['PostLimit_admin_settings'] = 'Settings';
$txt['PostLimit_admin_permissions'] = 'Permissions';
$txt['PostLimit_enable'] = 'Enable the mod';
$txt['PostLimit_post_count_alert'] = 'Percentage of post reached for sending an alert';
$txt['PostLimit_post_count_alert_sub'] = 'When the user has reached X amount of posts before hitting the post limit,
 an alert will be sent indicating the users they have X amount of posts left.<br />
It is based on percentage due to post limit being per user. 
For example, if a user has a 10 post limit, and you set this setting to 80%,
 the user will receive an alert on his/her 8th post, indicating they only have 2 more posts left. Default is 80';
$txt['PostLimit_enable_sub'] = 'This Setting must be on for the mod to work properly.';
$txt['PostLimit_custom_message'] = 'Put your custom message here';
$txt['PostLimit_custom_message_sub'] = 'Write a custom message the user will see when they have reached theor posting limit<br /> 
you can use the following tokens to personalize the message even more:<br />
- {username} will display the display name of the user who will receive the message<br />
- {limit} will display the amount of messages this particular user can make.<br />
 If you leave this message empty, the default message will appear:<br /><i>'. $txt['PostLimit_message_default'] .'</i>';
$txt['PostLimit_alert_message_default'] = 'Hi {username}! you have reached {percentage}% of the total amount you are allowed to post for today.';
$txt['PostLimit_custom_alert_message'] = 'Put your custom alert message here';
$txt['PostLimit_custom_alert_message_sub'] = 'Write a custom alert message the user will see when they reached the percentage to show an alert, 
you can use the following tokens to personalize the message even more<br />
- {user_name} will display the display name of the user who will receive the message<br />
- {limit} will display the amount of messages this particular user can make.<br />
- {percentage} will show the percentage the user has reached.<br />
- {posts_left} The amount of posts the user can make before reaching their limit.<br />
 If you leave this message empty, the default message will appear:<br /><i>'. $txt['PostLimit_alert_message_default'] .'</i>';
$txt['PostLimit_profile_panel'] = 'Post Limit profile panel';
$txt['PostLimit_profile_panel_sub'] = 'You can set the post limit and the boards this limit will be applied for this user here';

/* Messages */
$txt['PostLimit_message_overlimit'] = 'You don\'t have any more messages left, your limit is: %d';
$txt['PostLimit_message'] = 'You have %d message left today';
$txt['PostLimit_message_title'] = 'Attention %s!';

/* Profile fields */
$txt['PostLimit_profile_userlimit'] = 'Post Limit';
$txt['PostLimit_profile_userlimit_desc'] = 'You can put any number, if empty, this user will not be limited.';
$txt['PostLimit_profile_boards'] = 'Board IDs';
$txt['PostLimit_profile_boards_desc'] = 'Write the board Id\'s where this user will be limited, comma separated, example: 1,2,3,4
<br /> If leave empty, and a post limit is set, the user will be limited on all boards';

/* Permissions strings */
$txt['cannot_can_set_post_limit'] = 'I\'m sorry, you are not allowed to set post limits.';
$txt['permissiongroup_simple_PostLimit_per_simple'] =
$txt['permissiongroup_PostLimit_per_classic']  =
$txt['PostLimit_permissions_title'] = 'Post Limit mod permissions';
$txt['PostLimit_can_set_post_limit'] = $txt['permissionname_PostLimit_can_set_post_limit'] = 'Can set post limits';
$txt['PostLimit_message_cannot'] = $txt['cannot_can_set_post_limit'];
$txt['PostLimit_message_cannot_admin'] = 'Admins cannot be limited';
$txt['PostLimit_message_cannot_own'] = 'You cannot set your own limit';
$txt['PostLimit_message_cannot_general'] = 'Post Limit warning: %s';

/* Scheduled Task */
$txt['scheduled_task_post_limit'] = 'Post Limit mod';
$txt['scheduled_task_desc_post_limit'] = 'Resets the post limit of every user down to 0.';