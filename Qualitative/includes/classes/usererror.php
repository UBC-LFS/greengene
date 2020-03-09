<?php

// errorTypes
//	0 - 100: security errors
//	100-200: database errors
//	300-400: user errors (from User class and common amongst sub-classes)
//	400-500: student errors
//	600-700: ta errors
//	700-800: admin errors
//	900-1000: master admin errors
//	2000-2100: page errors
//

$g_errorTypes = array(
	300 => 'Passwords entered do not match or old password is incorrect',
	301 => 'Password too short.',
	302 => 'User Id cannot be empty.',
	303 => 'First Name cannot be empty.',
	304 => 'Last Name cannot be empty.',
	305 => 'User Id already exists.',
	306 => 'Login Failed. Invalid UserId or Password.',

	400 => 'Invalid cross number.',
	401 => 'No problem assigned.',
	410 => 'Maximum Number of Progeny Exceeded. Please contact your professor and/or TA!',
	420 => 'Progeny Generation failed.',
	430 => 'Cannot perform cross: missing data.',

	600 => 'Error creating student.',
	601 => 'Error modifying student.',
	602 => 'Error deleting student.',
	603 => 'Unable to find student.',
	604 => 'Error resetting user password.',
	605 => 'Error assigning problem.',
	606 => 'Error reassigning problem.',
	607 => 'Error file upload problem.',
	608 => 'Cannot update ProgenyPerMating and MaxProgeny.',
	609 => 'Error removing student problem.',
	610 => 'Error removing current student simulation.',
	// starting at 650, errors returned from the page
	650 => 'Please specify all of UserId, First Name, and Last Name.',
	651 => 'Please specify at least 2 alphabetical characters for each of First Name and Last Name.',
	652 => 'Cannot parse any student records from the file.',
	653 => 'Cannot find requested problem.',
	654 => 'Cannot find requested student.',


	701 => 'Error creating trait',
	702 => 'Error adding phynotype',
	703 => 'Error deleting phynotype',
	704 => 'Error deleting trait',
	705 => 'Error creating master problem',
	706 => 'Error deleting problem',
	707 => 'Error creating management user',
	708 => 'Error modifying course',
	709 => 'Error deleting management user',
	710 => 'Error changing TA password',
	711 => 'TA password not equal',
	712 => 'Invalid Admin User Id.',
	713 => 'Invalid Privilege Level.',
	714 => 'Error modifying management user.',
	715 => 'Modified problem does not exist',
	716 => 'Error modifying problem',
	717 => 'Error updating modified value in student table',
	718 => 'Cannot delete a problem that has been assigned to a student.',
	// starting at 750, errors returned from the page
	750 => 'Problem Name Empty.',
	751 => 'Invalid Progeny Per Mating given.',
	752 => 'Invalid Max Progeny given.',
	753 => '1 or more traits have not been specified',
	754 => 'Please specify dominance or co-dominance for Trait1',
	755 => 'Please specify dominance or co-dominance for Trait2',
	756 => 'Dominance requires 2 phenotypes to be specified',
	757 => 'Co-dominance requires 3 phenotypes to be specified',
	758 => 'Invalid linkage distance specified.',
	759 => 'Trait orders for each trait must be distinct.',
	760 => 'Missing phenotypes not specified for epistasis.',
	761 => 'Cannot create problem.',
	762 => 'Please specify one of dominance, co-dominance, or epistasis for Trait 3',
	763 => 'Cannot assign problem.',
	764 => 'Cannot modify problem.',
	765 => 'Maximum Progeny must be greater than Progeny Per Mating.',


	900 => 'Error creating new course.',
	901 => 'Error creating new course admin.',
	902 => 'Unable to delete course: invalid password.',
	903 => 'Unable to delete course.',
	904 => 'Error updating admin account.',
	905 => 'Error updating course.',
	906 => 'Invalid Admin User Id.',
	907 => 'Invalid Course Id.',
	908 => 'Course Name Empty.',
	909 => 'Error deleting course admin.',
	910 => 'Invalid Privilege Level.'
);

$g_errorList = array();

/**
 * UserError class
 *
 * @author Brett Taylor
 * @package User
 */
class UserError
{
	/**
	 * addError
	 *
	 * add an error to the current error list
	 *
	 * @param int $p_errorId error number
	 */
	function addError($p_errorId)
	{
		global $g_errorTypes, $g_errorList;
		array_push($g_errorList, $p_errorId);
	}

	/**
	 * hasError
	 *
	 * checks to see if there are errors in the list
	 *
	 * @return bool
	 */
	function hasError()
	{
		global $g_errorList;
		return (count($g_errorList) > 0);
	}

	/**
	 * nextError
	 *
	 * returns the next error (as a string) in the list
	 *
	 * @return string
	 */
	function nextError()
	{
		global $g_errorTypes, $g_errorList;
		//$Hash = array_shift($g_errorList);
		//return "(".$Hash."):" . $g_errorTypes[$Hash];
		return $g_errorTypes[array_shift($g_errorList)];
	}
}
?>
