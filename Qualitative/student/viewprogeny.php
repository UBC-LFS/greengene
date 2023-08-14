<?php
require_once('../includes/global.php');

// DATABASE CONNECTION
$g_db = new DB();

// SESSION
$user = (new Security) -> getUser();

// PAGE CREATION LOGIC
$page = new Page($user, 'View Progeny', 3);

// for bad IE CSS compatibility
$ie = null;
if(isset($HTTP_USER_AGENT)) {
	echo($HTTP_USER_AGENT);
	// $ie = eregi("MSIE", $HTTP_USER_AGENT);
	$ie = preg_match("#MSIE#i", $HTTP_USER_AGENT);
}

// get the real student
if(!isset($_GET['_userId']))
	$_GET['_userId'] = null;
$student = $page->translateUser($_GET['_userId']);

// FORM LOGIC
$formaction = null;
if (isset($_GET['formaction'])) {
	$formaction = $_GET['formaction'];
}

$isStudent = ($user->m_userId == $student->m_userId);

if($formaction == 'cache')
{
	if(!empty($_GET['seedCrossNum']))
	{
		$_SESSION['seedCrossNum'] = $_GET['seedCrossNum'];
		$_SESSION['seedPlantNum'] = $_GET['seedPlantNum'];
	}
	else if(!empty($_GET['pollenCrossNum']))
	{
		$_SESSION['pollenCrossNum'] = $_GET['pollenCrossNum'];
		$_SESSION['pollenPlantNum'] = $_GET['pollenPlantNum'];
	}
}
else if($formaction == 'clear')
{
	unset($_SESSION['seedCrossNum']);
	unset($_SESSION['seedPlantNum']);
	unset($_SESSION['pollenCrossNum']);
	unset($_SESSION['pollenPlantNum']);
}
else if($formaction == 'performcross')
{
	if(($user->m_privilegeLvl == 3) &&
		$isStudent)
	{
		if(!empty($_GET['pollenCrossNum']) &&
			!empty($_GET['pollenPlantNum']) &&
			!empty($_GET['seedCrossNum']) &&
			!empty($_GET['seedPlantNum']))
		{
			$pollenCrossNum = $_GET['pollenCrossNum'];
			$pollenPlantNum = $_GET['pollenPlantNum'];
			$seedCrossNum = $_GET['seedCrossNum'];
			$seedPlantNum = $_GET['seedPlantNum'];

			$newCrossNum = $user->performCross($_GET['pollenCrossNum'],
				$_GET['pollenPlantNum'],
				$_GET['seedCrossNum'],
				$_GET['seedPlantNum']);
			$_SESSION['userSession'] = $user;

			if($newCrossNum != false)
			{
				// clear session
				unset($_SESSION['seedCrossNum']);
				unset($_SESSION['seedPlantNum']);
				unset($_SESSION['pollenCrossNum']);
				unset($_SESSION['pollenPlantNum']);

				// Redirect user to page with new cross data
				Page::redirect($_SERVER['PHP_SELF']."?cross=$newCrossNum");
				// Page::redirect("$PHP_SELF?cross=$newCrossNum");
			}
		}
		else
		{
			(new UserError) -> addError(430);
		}
	}
}


//if($_GET['export'] == 'CSV')
if(isset($_GET['export']) && $_GET['export'] == 'CSV')
{
	$crossStr = $_GET['cross'];
	if($crossStr == '')
		$crossStr = 'Latest';
	$filename = "$student->m_userId-cross-$crossStr.csv";

	header('Content-type: text/x-csv');
	header("Content-Disposition: attachment; filename=\"$filename\"");
	$cross = empty($_GET['cross']) ? $student->getProgeny() : $student->getProgeny($_GET['cross']);
	$crossTable = new CrossView($cross);
	$crossTable->writeCrossesAsCSV();
}
else
{
	// if($_GET['print'] == true)
	if(isset($_GET['print']) && ($_GET['print'] == true))
		$page->setOnLoad('showAllCrosses(); printPage();');
	else if(!empty($_SESSION['pollenCrossNum']) || !empty($_SESSION['seedCrossNum']))
	{
		$page->setOnLoad('showPerformCross();');
		if($ie)
			$page->setOnScroll('doScroll()');
	}

	$page->writeHeader();

	if($student->getCropName() == '')
		(new UserError) -> addError(401);

	// DATA LOGIC
	if(!isset($_GET['cross']))
		$_GET['cross'] = null;
	$crossNum = $_GET['cross'];
	$cross = empty($_GET['cross']) ? $student->getProgeny() : $student->getProgeny($_GET['cross']);


	// handle errors
	$page->handleErrors();

	$cropName = $student->getCropName();
	$cropDesc = $student->getProblemDescription();

	if($student->getCropName() != '')
	{
		$crossesLeft = floor(($student->m_maxProgeny - $student->m_progenyGenerated) / $student->m_progenyPerMating);
		echo("<p>You are breeding: <b>$cropName</b><br>$cropDesc</p>
		<p>You have <b>$student->m_progenyGenerated</b> progeny of your <b>$student->m_maxProgeny</b> progeny
		quota.<br>Each cross will produce <b>$student->m_progenyPerMating</b> plants. You have
		<b>$crossesLeft</b> crosses remaining.</p>");
	}

	/*echo("<p><input type=\"button\" value=\"Download Data\" onClick=\"goUrl('$PHP_SELF?export=CSV&cross=" .
		$_GET['cross'] . "&_userId=" . $_GET['_userId'] . "');\">");*/
	echo("<p><input type=\"button\" value=\"Download Data\" onClick=\"goUrl('".htmlentities($_SERVER['PHP_SELF'])."?export=CSV&cross=" .
		$_GET['cross'] . "&_userId=" . $_GET['_userId'] . "');\">");

	if($crossNum == 'All')
		echo("&nbsp; &nbsp;<input type=\"button\" value=\"Show All\" onClick=\"showAllCrosses();\">&nbsp;
		<input type=\"button\" value=\"Hide All\" onClick=\"hideAllCrosses();\">");

	echo("</p>");

	$crossTable = new CrossView($cross);
	$crossTable->writeCrossTable();
?>

<br><br><br><br><br><br><br>

<script>
<?php if($ie) { ?>
function doScroll()
{
	var obj = document.getElementById('crossFloaterIE');
	obj.style.top = document.body.scrollTop + document.body.clientHeight - 60;
}
<?php } ?>

function showPerformCross()
{
<?php if($ie) { ?>
	var obj = document.getElementById('crossFloaterIE');
<?php } else { ?>
	var obj = document.getElementById('crossFloater');
<?php } ?>
	obj.style.display = 'inline';
}

function cancelCross()
{
<?php if($isStudent) { ?>
	goUrl('viewprogeny.php?cross=<?=$crossNum?>&formaction=clear');
<?php } else { ?>
	alert('Function disabled for admin');
<?php } ?>
}

function addSeedCross(crossNum, plantNum)
{
<?php if($isStudent) { ?>
	goUrl('viewprogeny.php?cross=<?=$crossNum?>&formaction=cache&seedCrossNum=' + crossNum + '&seedPlantNum=' + plantNum);
<?php } else { ?>
	alert('Function disabled for admin');
<?php } ?>
}

function addPollenCross(crossNum, plantNum)
{
<?php if($isStudent) { ?>
	goUrl('viewprogeny.php?cross=<?=$crossNum?>&formaction=cache&pollenCrossNum=' + crossNum + '&pollenPlantNum=' + plantNum);
<?php } else { ?>
	alert('Function disabled for admin');
<?php } ?>
}

function hideCross(crossNum)
{
	var obj = document.getElementById('cross' + crossNum);
	var img = document.getElementById('btn' + crossNum);
	obj.style.display = 'none';
	img.src = '<?php echo(URLROOT); ?>/includes/images/max.jpg';
}

function showCross(crossNum)
{
	var obj = document.getElementById('cross' + crossNum);
	var img = document.getElementById('btn' + crossNum);
	obj.style.display = 'inline';
	img.src = '<?php echo(URLROOT); ?>/includes/images/min.jpg';
}

function showhideCross(crossNum)
{
	var obj = document.getElementById('cross' + crossNum);
	var img = document.getElementById('btn' + crossNum);
	if(obj.style.display == 'inline')
	{
		obj.style.display = 'none';
		img.src = '<?php echo(URLROOT); ?>/includes/images/max.jpg';
	}
	else
	{
		obj.style.display = 'inline';
		img.src = '<?php echo(URLROOT); ?>/includes/images/min.jpg';
	}
}

function showAllCrosses()
{
<?php
$crossLow = $crossTable->m_firstCross;
$crossHigh = $crossTable->m_firstCross + $crossTable->m_crossCount;

for($i = $crossLow; $i < $crossHigh; $i++)
	echo("showCross($i);\n");
?>
}

function hideAllCrosses()
{
<?php
$crossLow = $crossTable->m_firstCross;
$crossHigh = $crossTable->m_firstCross + $crossTable->m_crossCount;

for($i = $crossLow; $i < $crossHigh; $i++)
	echo("hideCross($i);\n");
?>
}
</script>
<?php
		if($isStudent)
		{
?>
<form name="cross" method="get" action="<?=$_SERVER['PHP_SELF']?>">
<!--<form name="cross" method="get" action="<?=$PHP_SELF?>">-->
<input type="hidden" name="formaction" value="performcross">
<div id="crossFloater<?php if($ie) echo ('IE'); ?>">
<table>
<tr>
<?php
	if(!isset($_SESSION['pollenCrossNum'])) {
		$_SESSION['pollenCrossNum'] = null;
	}
	if(!isset($_SESSION['pollenPlantNum'])) {
		$_SESSION['pollenPlantNum'] = null;
	}
?>
	<td>Pollen</td>
	<td>Cross #: <input type="text" readonly name="pollenCrossNum" size="5" value="<?=$_SESSION['pollenCrossNum']?>"></td>
	<td>Plant #: <input type="text" readonly name="pollenPlantNum" size="5" value="<?=$_SESSION['pollenPlantNum']?>"></td>
	<td><input type="button" value="Cancel" onClick="cancelCross();"></td>
</tr>
<?php
	if(!isset($_SESSION['seedCrossNum'])) {
		$_SESSION['seedCrossNum'] = null;
	}
	if(!isset($_SESSION['seedPlantNum'])) {
		$_SESSION['seedPlantNum'] = null;
	}
?>
<tr>
	<td>Seed</td>
	<td>Cross #: <input type="text" readonly name="seedCrossNum" size="5" value="<?=$_SESSION['seedCrossNum']?>"></td>
	<td>Plant #: <input type="text" readonly name="seedPlantNum" size="5" value="<?=$_SESSION['seedPlantNum']?>"></td>
	<td><input type="submit" value="Perform Cross"></td>
</tr>
</table>
</div>
</form>
<?php
		}
	$page->writeFooter();
}

$g_db->disconnect();
?>
