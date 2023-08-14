<?php
/**
 * Page class
 *
 * @author Brett Taylor
 * @package PageManager
 */
class Page
{
	var $m_user;
	var $m_userActor;

	var $m_title;
	var $m_privilegeLevel;

	var $m_onLoadFunction;
	var $m_onScrollFunction;
	var $m_jsIncludes;

	/**
	 * Constructor
	 *
	 * @param User $p_user User object
	 * @param string $p_title Page title
	 * @param int $p_privilegeLevel privilege level required to access page.
	 */
	// function Page($p_user, $p_title, $p_privilegeLevel)
	function __construct($p_user, $p_title, $p_privilegeLevel)
	{
		$this->m_user = $p_user;
		$this->m_title = $p_title;
		$this->m_privilegeLevel = $p_privilegeLevel;

		if($p_privilegeLevel > 0)
		{
			if($p_privilegeLevel == 10 && $this->m_user->m_privilegeLvl != 10)
				Page::redirect(URLROOT . "/login.php");

			if($this->m_user->m_privilegeLvl > $p_privilegeLevel)
				Page::redirect(URLROOT . "/login.php");
		}

		$this->m_jsIncludes = array();
		$this->addJSInclude('default.js');

		if (isset($_GET['print'])){
			$this -> setOnLoad('printPage();');
		}
		//if ($_GET['print']!='')
		//	$this -> setOnLoad('printPage();');

		$this->m_userActor = $this->m_user;
	}

	/**
	 * Add JavaScript include
	 *
	 * @param string $p_filename JavaScript filename (no path)
	 */
	function addJSInclude($p_filename)
	{
		array_push($this->m_jsIncludes, $p_filename);
	}

	/**
	 * Set Page onLoad value
	 *
	 * @param string $p_function JavaScript function to call
	 */
	function setOnLoad($p_function)
	{
		$this->m_onLoadFunction = $p_function;
	}

	function setOnScroll($p_function)
	{
		$this->m_onScrollFunction = $p_function;
	}

	/**
	 * Redirect to URL
	 *
	 * redirects web browser to specified URL
	 *
	 * @param string $p_url URL
	 */
	function redirect($p_url)
	{
		header("Location: $p_url");
		exit;
	}

	/**
	 * Redirect user to appropriate initial page
	 *
	 * redirects web browser to initial/default page for user
	 *
	 * @param User $p_user User object representing current user
	 */
	function redirectInitial($p_user)
	{
		// var_dump($p_user);
		// $p_user->m_privilegeLvl = 1; // testing purposes
		switch($p_user->m_privilegeLvl)
		{
			case 10:
				Page::redirect('siteadmin/viewcourses.php');
				break;

			case 1:
				Page::redirect('admin/viewproblemlist.php');
				break;

			case 2:
				Page::redirect('admin/viewstudentlist.php');
				break;

			case 3:
				Page::redirect('student/viewprogeny.php');
				break;
		}
	}

	/**
	 * Translate to User object (for use on user pages)
	 *
	 * Returns appropriate Student object of either the current Student
	 * or Student object for which Admin is viewing
	 *
	 * @param int $p_errorId error number
	 * @return Student
	 */
	function translateUser($p_userId)
	{
		if($this->m_user->m_privilegeLvl != 1 && $this->m_user->m_privilegeLvl != 2)
			return $this->m_userActor;

		if($this->m_user->m_userId != $p_userId)
			$this->m_userActor = new Student($p_userId);

		return $this->m_userActor;
	}

	/**
	 * Write section header to page
	 *
	 * @param string $p_title section title
	 */
	function writeSectionHeader($p_title)
	{
		echo("<h2>$p_title</h2>");
	}

	/**
	 * Handles errors in UserError class
	 */
	function handleErrors()
	{
		// while(UserError::hasError())
		while ((new UserError()) -> hasError())
		{
			// echo('<p class="error">Error: ' . UserError::nextError() . '</p>');
			echo('<p class="error">Error: ' . (new UserError()) -> nextError() . '</p>');
		}
	}

	/**
	 * Generates help URL for the given uer-type
	 *
	 * @param string $p_userType User type as student, admin or siteadmin
	 * @return string help URL
	 */
	function getHelpURL($p_userType)
	{
		return URLROOT . "/includes/help/$p_userType.html#" . basename($_SERVER['SCRIPT_NAME'], '.php');
	}

	/**
	 * Writes MasterAdmin toolbar
	 */
	function writeMasterAdminMenus()
	{
		$root = URLROOT;
		echo <<<END
	<input type="button" value="Manage Courses" onClick="goUrl('$root/siteadmin/viewcourses.php');">
	<input type="button" value="Create Course" onClick="goUrl('$root/siteadmin/managecourse.php');">
	<input type="button" value="Modify My Account" onClick="goUrl('$root/siteadmin/myaccount.php');">
END;
	}

	/**
	 * Writes Admin toolbar
	 */
	function writeAdminMenus()
	{
		$root = URLROOT;

		echo <<<END
	<select name="problem" onChange="goUrl(form.problem.value);">
	<option value="">Problem Menu</option>
	<option value="$root/admin/viewproblemlist.php" class="item">Manage Problems</option>
	<option value="$root/admin/createproblem.php" class="item">Create Problem</option>
	<option value="$root/admin/viewtraitlist.php" class="item">Manage Traits</option>
	<option value="$root/admin/createtrait.php" class="item">Create Trait</option>
	</select>
	<select name="student" onChange="goUrl(form.student.value);">
	<option value="">Student Menu</option>
	<option value="$root/admin/viewstudentlist.php" class="item">Manage Students</option>
	<option value="$root/admin/createstudent.php" class="item">Create Student</option>
	<option value="$root/admin/importstudents.php" class="item">Import Students</option>
	<option value="$root/admin/viewprogress.php" class="item">View Progress</option>
	</select>
	<select name="course" onChange="goUrl(form.course.value);">
	<option value="">Course Menu</option>
	<option value="$root/admin/viewadminlist.php" class="item">Manage Admins</option>
	<option value="$root/admin/modifyadmin.php" class="item">Create Admin</option>
	<option value="$root/admin/modifycourse.php" class="item">Modify Course</option>
	</select>
END;
	}

	/**
	 * Writes TA toolbar
	 */
	function writeTAMenus()
	{
		$root = URLROOT;

		echo <<<END
	<select name="student" onChange="goUrl(form.student.value);">
	<option value="">Student Menu</option>
	<option value="$root/admin/viewstudentlist.php" class="item">Manage Students</option>
	<option value="$root/admin/createstudent.php" class="item">Create Student</option>
	<option value="$root/admin/importstudents.php" class="item">Import Students</option>
	<option value="$root/admin/viewprogress.php" class="item">View Progress</option>
	</select>
END;
	}

	/**
	 * Writes Student toolbar
	 */
	function writeStudentMenus()
	{
		$root = URLROOT;

		$crossCount = $this->m_userActor->getCrossCount();

		echo <<<END
Display Cross: <select name="cross" onChange="goUrl(form.cross.value);">
END;
		// parse GET string to determine cross number
		echo("<option value=\"$root/student/viewprogeny.php?_userId=" . $_GET['_userId'] . "&cross=Latest\"");
		if(!isset($_GET['cross']) || $_GET['cross'] == 'Latest' )
			echo(" selected");
		else
			echo(" class=\"item\"");
		echo(">Latest</option>");

		echo("<option value=\"$root/student/viewprogeny.php?_userId=" . $_GET['_userId'] . "&cross=All\"");
		if(isset($_GET['cross']) && $_GET['cross'] == 'All')
			echo(" selected");
		else
			echo(" class=\"item\"");
		echo(">All</option>");

		// generate all crosses
		for($i = 1; $i <= $crossCount; $i++)
		{
			echo("<option value=\"$root/student/viewprogeny.php?_userId=" . $_GET['_userId'] . "&cross=$i\"");
			if(isset($_GET['cross']) && $_GET['cross'] == $i)
				echo('  selected');
			else
				echo(' class="item"');
			echo(">$i</option>");
		}

		echo("</select>");

		if($this->m_user->m_userId != $this->m_userActor->m_userId)
		{
			echo("&nbsp;&nbsp;<input type=\"button\" value=\"Back to Admin View\" onClick=\"goUrl('$root/admin/viewprogress.php');\">");
		}
	}

	/**
	 * Writes standard page header and toolbar
	 */
	function writeHeader()
	{
		$root = URLROOT;
		$imgroot = "$root/includes/images";
		// var_dump($this->m_user);
		$userName = $this->m_userActor->m_firstName . ' ' . $this->m_userActor->m_lastName;
		$courseText = $this->m_user->m_courseName . ' ' . $this->m_user->m_courseDescription;

		switch($this->m_userActor->m_privilegeLvl)
		{
			case 1:
				$userType = 'Professor';
				$helpURL = Page::getHelpURL('admin');
				break;
			case 2:
				$userType = 'TA';
				$helpURL = Page::getHelpURL('admin');
				break;
			case 3:
				$userType = 'Student';
				$helpURL = Page::getHelpURL('student');
				break;
			case 10:
				$userType = 'Site Administrator';
				$helpURL = Page::getHelpURL('siteadmin');
				break;
		}

		$load = (!empty($this->m_onLoadFunction) ? "onLoad=\"$this->m_onLoadFunction\"" : "");
		$scroll = (!empty($this->m_onScrollFunction) ? "onScroll=\"$this->m_onScrollFunction\"" : "");

		if(!isset($_GET['print']) || $_GET['print'] =! 'true')
			$bodyTag = "<body $load $scroll style=\"background:url('$imgroot/background.gif');\">";
		else
			$bodyTag = "<body $load $scroll>";

		$printURL = $_SERVER['PHP_SELF']."?print=true";
		// $printURL = "$PHP_SELF?print=true";
		foreach($_GET as $key => $arg)
			$printURL .= "&$key=$arg";

        // TODO:
		$jsTag = "";
		foreach($this->m_jsIncludes as $js)
			$jsTag .= "<script language=\"JavaScript\" src=\"$root/includes/js/$js\"></script>\n";

		if(!isset($_GET['print']) || $_GET['print'] =! 'true')
			$cssURL = "$root/includes/css/default.css";
		else
			$cssURL = "$root/includes/css/printer.css";


		echo <<<END
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>GreenGene: $this->m_title</title>
<link rel="stylesheet" href="$cssURL" />
</head>
$jsTag
$bodyTag
<!-- header -->
END;

		if(!isset($_GET['print']) || $_GET['print'] =! 'true')
		{

			echo <<<END
<div id="headerContent">
<table>
<form>
<tr>
	<td colspan="2"><img src="$imgroot/header.jpg" width="964" height="122" alt="GreenGene title"></td>
</tr>
<tr class="topRow" style="background:url('$imgroot/darkborder.gif');">
	<td class="courseInfo">$courseText&nbsp;&nbsp; <span class="userTypeText">$userType</span></td>
	<td class="textLinks">
		$userName |
		<a href="$root/logout.php">Logout</a>
	</td>
</tr>
<tr>
	<td colspan="2"><img src="$imgroot/bordertrim.gif" height="3" width="100%" alt="border trim"></td>
</tr>
<tr class="toolbarRow" style="background:url('$imgroot/lightborder.gif');">
	<td class="toolbar">
END;

			// now write menus
			switch($this->m_userActor->m_privilegeLvl)
			{
				case 10:
					$this->writeMasterAdminMenus();
					break;
				case 1:
					$this->writeAdminMenus();
					break;
				case 2:
					$this->writeTAMenus();
					break;
				case 3:
					$this->writeStudentMenus();
					break;
			}

			echo <<<END
	</td>
	<td class="iconLinks">
		<a href="$helpURL" target="_blank">Help<img src="$imgroot/help.gif" width="13" height="13" alt="Help"></a>&nbsp;
		<a href="$printURL" target="_blank">Printer Friendly<img src="$imgroot/printer.gif" width="13" height="13" alt="Printer Friendly"></a>
	</td>
</tr>
</form>
</table>
<!-- end header -->
</div>
END;
		}

		if(!isset($_GET['print']) || $_GET['print'] =! 'true')
			echo("<div id=\"bodyContentOut\" style=\"background:url('$imgroot/innerbackground.gif');\">");
		else
			echo("<div id=\"bodyContentOut\">");

	echo <<<END
<div id="bodyContent">
<br><h1>$this->m_title</h1>
<!-- body -->
END;
	}

	/**
	 * Writes standard page footer
	 */
	function writeFooter()
	{
		// insert real footer
		echo <<<END
<!-- end body -->
</div>
</div>
<!-- footer -->
<div id="footerContent">GreenGene 1.0. &copy; 2005, <a href="http://sourceforge.net/projects/yellowleaf">YellowLeaf Project</a>. Released under the <a href="http://www.gnu.org/copyleft/gpl.html">GNU General Public License</a>.</div>
<!-- end footer -->
</body>
</html>
END;
	}
}
?>
