<?php
require_once('../includes/global.php');

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
$user = (new Security) -> getUser();
$userId = $user->m_userId;

// DATABASE CONNECTION
$g_db = new DB();


// FORM LOGIC
// - get form variables
$problemId = null;
if (isset($_GET['problemId'])) {
	$problemId = $_GET['problemId'];
}
$studentProblemId = null;
if (isset($_GET['studentId'])) {
	$studentProblemId = $_GET['studentId'];
}

// PAGE CREATION LOGIC
$page = new Page($user, 'View Problem', 2);
$page -> addJSInclude("problem.js");
$page -> setOnLoad("loadTips();");

// only necessary on Student pages
// $student = $page->translateUser($userId);
// note: Student is now an appropriate object of either the current student
//       or a student object representing the admin viewing the student's data


// write page header, including toolbar
$page->writeHeader();

// DATA LOGIC

function setProblemList()
{	global $user, $g_db, $studentId;
	$link = URLROOT.'/admin/viewproblem.php?';
	if ($studentId!='')
		$link .= 'studentId='.$studentId.'&';
	$link .= 'problemId=';
	$rs = $user -> getProblems();
	if ($rs)
	{
		echo "<form><select name=\"proBox\" onchange=\"window.location='".$link."'+this.options[this.selectedIndex].value;\">\n<Option value=\"-1\">View another problem\n";
		while ($row=$g_db->fetch($rs))
		{
			if(strlen($row->Description) > 25)
				$problemDesc = substr($row->Description, 0, 22) . '...';
			else
				$problemDesc = $row->Description;
			echo "<Option value=\"".$row->ProblemId."\">".$row->Name." - ".$problemDesc."\n";
		}
		echo "</select></form>";
	}
}

function phenoTip($i, $j)
{
	return '<DIV id="tip'.$i.''.$j.'" name="tip'.$i.''.$j.'">';
}
function viewProblem($problemId=''){
	global 	$g_epistaticRatios, $V_trait, $V_problemname, $V_problemdesc,
			$V_progpermating, $V_totalprogeny, $V_dom, $V_ordering,
			$V_pheno, $V_epistCheck, $V_linkdist, $V_epistCode;

	//if ($p_problemId!='')//get problem info
echo '<script language="JavaScript">';

echo "function loadTips(){\n";
	for ($i = 0; $i < 3; $i++){
		if ($V_dom[$i]){
			echo "setTip($i, 4);\n";
		}else{
			echo "setTip($i, 5);\n";
		}
	}
	if ($V_epistCheck){
		echo "setTip(2, ".$V_epistCode.");\n";
	}
echo "}\n</script>";

	setProblemList();

	$t = new Table(3, false);

	$t -> writeRow('Problem Name:', $V_problemname);
	$t -> writeRow('Problem Description:', $V_problemdesc);
	$t -> writeRow('Progeny Per Mating:', $V_progpermating);
	$t -> writeRow('Maximum Progeny:', $V_totalprogeny);
	$t -> writeRow('Display Order of Genes:&nbsp;', '<select name="order" size="3"><option value="'.substr($V_ordering, 0, 1).'">Gene '.(substr($V_ordering, 0, 1)*1+1).'</option><option value="'.substr($V_ordering, 1, 1).'">Gene '.(substr($V_ordering, 1, 1)*1+1).'</option><option value="'.substr($V_ordering, 2, 1).'">Gene '.(substr($V_ordering, 2, 1)*1+1).'</option></select>');
	$t -> writeDivider();

	$p = 0;
	$temp = [];
	for ($i = 0; $i < 3; $i++)
	{
		$t -> writeHeaders('Gene '.($i+1),'');
		$t -> writeRow('Trait Name:', $V_trait[$i]);

		if ($i==2)
		{
			if ($V_epistCheck){
				// echo("epist checked ");
				$t -> writeRow('Epistasis Selected', $g_epistaticRatios[$V_epistCode]);
				$temp = explode(":", $g_epistaticRatios[$V_epistCode]);
				for ($j = 0; $j < count($temp); $j++){
					$temp[$j] .= ':';
				}
			}else{
				// echo("epist not checked ");
				// echo($V_dom[$i])
				($V_dom[$i])?$temp = array('&nbsp;', '&nbsp;'):$temp = array('&nbsp;', '&nbsp;', '&nbsp;');
			}

			$t -> writeRow(phenoTip($i, 0).'AA:</DIV>', $temp[0].$V_pheno[$p++]);
			$t -> writeRow(phenoTip($i, 1).'Aa / aA:</DIV>', $temp[1].$V_pheno[$p++]);
			(!$V_dom[$i]&&$i<2||$i == 2&&!$V_epistCheck&&!$V_dom[$i]||$i==2&&$V_epistCheck&&count(explode(":", $g_epistaticRatios[$V_epistCode]))>2)?
				$t -> writeRow(phenoTip($i, 2).'aa:</DIV>', $temp[2].$V_pheno[$p++]):$p++;
		} else {
			($V_dom[$i])?$temp = array('&nbsp;', '&nbsp;'):$temp = array('&nbsp;', '&nbsp;', '&nbsp;');
			$t -> writeRow(phenoTip($i, 0).'AA:</DIV>', $temp[0].$V_pheno[$p++]);
			$t -> writeRow(phenoTip($i, 1).'Aa / aA:</DIV>', $temp[1].$V_pheno[$p++]);
		
			(!$V_dom[$i]&&$i<2||$i == 2&&!$V_epistCheck&&!$V_dom[$i]||$i==2&&$V_epistCheck&&count(explode(":", $g_epistaticRatios[$V_epistCode]))>2)?
				$t -> writeRow(phenoTip($i, 2).'aa:</DIV>', $temp[2].$V_pheno[$p++]):$p++;
		}
		// if ($i == 2&&$V_pheno[9]!='')//have the extra phenotype selection
		// if ($V_epistCheck && $i == 2 && $V_pheno[9] != '')//have the extra phenotype selection
		if ($V_epistCheck && $i == 2 && !isset($V_pheno[0]))//have the extra phenotype selection
		{
			$t -> writeRow(phenoTip($i, 3).'&nbsp;</DIV>', $temp[3].$V_pheno[9]);
		}
		if ($i == 1){
			if ($V_linkdist[0]==''){$V_linkdist[0] = 50;}
			$t -> writeRow('Linkage:', $V_linkdist[0].'<font size="-1"> (distance from gene '.$i.' in GMUs)</font>');
		}else if ($i == 2&&!$V_epistCheck){
			if ($V_linkdist[1]==''){$V_linkdist[1] = 50;}
			$t -> writeRow('<DIV id="LK0" name="LK0">Linkage:</DIV>', '<DIV id="LK1" name="LK1">'.$V_linkdist[1].'<font size="-1"> (distance from gene 2 in GMUs)</font></DIV>');
		}
		$t -> writeDivider();
	}
	$t -> flush();
}

//$ta = new ta($userId);
$recordset;
$studentRecordset;

// retrieve the specific problem
if (!empty($problemId) )
{
	$recordset = $user->viewProblem($problemId);
}
else if (!empty($studentProblemId))
{
	$recordset = $user->getStudentProblem($studentProblemId);
}

?>

<!-- Content Start -->
<?php

// now generate the content of the page

// display some error message to the user if we couldn't find the problem
// in the DB
if (empty($recordset))
{
	(new UserError) -> addError(653);
}
else
{
	$row = $g_db->fetch($recordset);
	$V_trait = array( $row->Trait1Name, $row->Trait2Name, $row->Trait3Name);
	$V_problemname = $row->Name;
	$V_problemdesc = $row->Description;
	$V_progpermating = $row->ProgenyPerMating;
	$V_totalprogeny = $row->MaxProgeny;
	$V_dom = array(	($row->Trait1AAPhenoName==$row->Trait1AbPhenoName&&$row->Trait1AbPhenoName==$row->Trait1bAPhenoName),
					($row->Trait2AAPhenoName==$row->Trait2AbPhenoName&&$row->Trait2AbPhenoName==$row->Trait2bAPhenoName),
					($row->Trait3AAPhenoName==$row->Trait3AbPhenoName&&$row->Trait3AbPhenoName==$row->Trait3bAPhenoName));
	$V_ordering = $row->TraitOrder;
	$V_epistCode = $row->EpistasisCode;
	$V_epistCheck = $row->EpistasisCode!='';
	$V_pheno = array();
	($V_dom[0])?
	array_push($V_pheno, $row->Trait1AAPhenoName,$row->Trait1bbPhenoName,$row->Trait1AbPhenoName):
	array_push($V_pheno, $row->Trait1AAPhenoName,$row->Trait1AbPhenoName,$row->Trait1bbPhenoName);
	($V_dom[1])?
	array_push($V_pheno, $row->Trait2AAPhenoName,$row->Trait2bbPhenoName,$row->Trait2AbPhenoName):
	array_push($V_pheno, $row->Trait2AAPhenoName,$row->Trait2AbPhenoName,$row->Trait2bbPhenoName);
	if ($V_epistCheck){
		array_push($V_pheno, $row->Trait3AAPhenoName);
		//now check for what to put in where
		switch($V_epistCode){
			case 0:
				array_push($V_pheno, $row->Trait3AbPhenoName,$row->Trait3bAPhenoName,$row->Trait3bbPhenoName);
			break;
			case 1:
				array_push($V_pheno, $row->Trait3AbPhenoName,$row->Trait3bbPhenoName);
			break;
			case 2:
				array_push($V_pheno, $row->Trait3bbPhenoName);
			break;
			case 3:
				array_push($V_pheno, $row->Trait3bAPhenoName,$row->Trait3bbPhenoName);
			break;
			case 4:
				array_push($V_pheno, $row->Trait3bbPhenoName);
			break;
		}
	}
	else
		($V_dom[2])?
		array_push($V_pheno, $row->Trait3AAPhenoName,$row->Trait3bbPhenoName,$row->Trait3AbPhenoName):
		array_push($V_pheno, $row->Trait3AAPhenoName,$row->Trait3AbPhenoName,$row->Trait3bbPhenoName);
	$V_linkdist = array($row->GMU1_2, $row->GMU2_3);
	viewProblem();

	// if we are viewing the master problem, then we will show the list of students
	// that has been assigned to this problem
	if (!empty($problemId) && empty($studentProblemId) )
	{
		$studentRecordset = $user->getStudentProblems($problemId);

		$page->writeSectionHeader('Additional Information');
		if (!empty($studentRecordset) && $g_db->getNumRows($studentRecordset) > 0)
		{
			$numRows = $g_db->getNumRows($studentRecordset);

			echo "<p>" . $numRows . " student(s) have been assigned problems from this template.</p>";

			$table = new Table(4);
			$table->writeHeaders("User Id","Problem Name","Modified Version","");

			for ($i = 0; $i < $numRows; $i++)
			{
				$row = $g_db->fetch($studentRecordset);
				//$link = "<a href=\"viewproblem.php?studentId=" . $row->UserId . "\">View</a>";
				$button = "<input type=\"button\" value=\"View\" onClick=\"goUrl('viewproblem.php?studentId=$row->UserId');\">";
				if ($row->Modified > 0)
				{
					// TODO: pass by reference - resolved?
					$table->writeRow($row->UserId, $row->Name, "Yes", $button);
				}
				else
				{
					// TODO: pass by reference - resolved?
					$table->writeRow($row->UserId, $row->Name, "No", $button);
				}
			}
			$table->flush();
		}
		else
		{
			echo "<p>No student has been assigned this problem.</p>";
		}
	}
	// if we are viewing a student problem, then we show the link
	// to the master problem
	else if (empty($problemId) && !empty($studentProblemId) )
	{
		$studentRecordset = $user->getStudentProblem($studentProblemId);

		if (!empty($studentRecordset) && $g_db->getNumRows($studentRecordset) > 0)
		{
			$row = $g_db->fetch($studentRecordset);
			$problemRecordset = $user->viewProblem($row->MasterProblemId);

			if (!empty($problemRecordset) && $g_db->getNumRows($problemRecordset) > 0)
			{
				echo "<p>Additional Information:</p>";
				$row = $g_db->fetch($problemRecordset);
				echo "<p>This student's problem was assigned from the ".
				"<a href=\"viewproblem.php?problemId=" .$row->ProblemId."\">" .$row->Name .
				"</a> template.</p>";
			}
		}

	}

}


$page->handleErrors();
// write main footer and close database connection
$page->writeFooter();
$g_db->disconnect();
//no need to worry about this part at this point
?>
