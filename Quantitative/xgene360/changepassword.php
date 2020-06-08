<?php

/*
* include necessary files
*/

require_once('includes/global.inc.php');
require_once('includes/classes/db/usermanager.class.php');

/*
* initialize common stuff
*/

$g_obj_db = null;
$g_obj_lock = null;
$g_str_serial = null;
$g_obj_user = null;

PageHandler::initialize();
PageHandler::check_permission(array(UP_ADMINISTRATOR, UP_PROFESSOR, UP_TA, UP_STUDENT));

$g_obj_user_manager = new UserManager($g_obj_user, $g_obj_db);

process_post();

/*
* set header stuff
*/

$g_str_page_title = "Change Password";
$g_arr_scripts = array('changepassword.js');
$g_arr_nav_links = $g_arr_nav_defined_links[$g_obj_user->int_privilege];

require_once('includes/header.inc.php');

if ($g_bln_display_content) {
?>

	<!-- Start Content -->
	<form id="UpdatePasswordForm" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>">

		<table class="box">
			<tr>
				<th>Change Password</th>
			</tr>
			<tr>
				<td>

					<table class="format">

						<tr>
							<td>Old Password:&nbsp;</td>
							<td><input class="longtextinput" type="password" name="OldPassword" id="OldPassword" size="20" /></td>
						</tr>
						<tr>
							<td>New Password:&nbsp;</td>
							<td><input class="longtextinput" type="password" name="NewPassword" id="NewPassword" size="20" /></td>
						</tr>
						<tr>
							<td width="150">Confirm New Password:&nbsp;</td>
							<td><input class="longtextinput" type="password" name="ConfirmNewPassword" id="ConfirmNewPassword" size="20" /></td>
						</tr>
						<tr>
							<td colspan="2"><input class="buttoninput" type="submit" name="Command" value="Update" onclick="return validateUpdatePasswordForm();" /></td>
						</tr>

					</table>

				</td>
			</tr>

		</table>

		<input type="hidden" name="SerialId" id="SerialId" value="<?= $g_str_serial ?>" />
	</form>
	<!-- End Content -->

<?php
}

require_once('includes/footer.inc.php');


$g_obj_db->disconnect();

function process_post()
{
	global $g_obj_lock;

	if (isset($_POST['Command']) && $g_obj_lock->page_lock(PageHandler::get_post_value('SerialId'))) {
		$str_command = $_POST['Command'];

		switch ($str_command) {
			case 'Update': {
					on_update_handler();
				}
				break;

			default: {
					MessageHandler::add_message(MSG_ERROR, "Unknown Command");
				}
				break;
		}
	}
}

function on_update_handler()
{
	global $g_obj_user, $g_obj_user_manager, $g_obj_db;

	// update the password
	$str_old_password = PageHandler::get_post_value('OldPassword');
	$str_new_password = PageHandler::get_post_value('NewPassword');
	$str_confirm_new_password = PageHandler::get_post_value('ConfirmNewPassword');

	if (empty($str_old_password) || empty($str_new_password) || empty($str_confirm_new_password)) {
		MessageHandler::add_message(MSG_FAIL, 'Please enter the necessary information');
		return;
	}

	if ($str_new_password != $str_confirm_new_password) {
		MessageHandler::add_message(MSG_FAIL, 'The password does not match');
		return;
	}

	// try to authenticate user
	if (LoginManager::authenticate($g_obj_user->str_username, $str_old_password, $g_obj_db) == null) {
		MessageHandler::add_message(MSG_FAIL, 'The old password does not match');
		return;
	}

	if ($g_obj_user_manager->modify_own_password($str_confirm_new_password)) {
		MessageHandler::add_message(MSG_SUCCESS, 'Successfully updated the password');
	} else {
		MessageHandler::add_message(MSG_FAIL, 'Failed to update the password');
	}
}

?>