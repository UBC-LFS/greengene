<?php
require_once('../includes/global.php');

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
$user = (new Security) -> getUser();

$userId = $user->m_userId;

// PAGE TYPE
$ASSIGN_FLAG = false; //either assign problem or modify problem

// OBTAIN PARAMETER FOR MODIFIED PROBLEM
$studentId = isset($_GET['studentId'])?$_GET['studentId']: (isset($_POST['studentId'])? $_POST['studentId']: null);
$problemId = isset($_GET['problemId'])?$_GET['problemId']: (isset($_POST['problemId'])? $_POST['problemId']: null);

// if student Id is in the argument, we are assigning problem.
if( $studentId )
	$ASSIGN_FLAG = true;

// PAGE CREATION LOGIC
$page;
if( $ASSIGN_FLAG )
{
	/*$t_user = new User($studentId);
	$page = new Page($user, 'Assign Problem to: '.
							$t_user->m_firstName." ".
							$t_user->m_lastName, 1);*/
	$page = new Page($user, 'Assign Problem',1);
}
else
	$page = new Page($user, 'Modify Problem', 1);
$page -> addJSInclude("problem.js");
$page -> setOnLoad("loadData();");

// DATABASE CONNECTION
$g_db = new DB();

$showProblemForm = false;
$showProblemSummary = false;


// FORM LOGIC
$formaction = null;
if (isset($_POST['formaction'])) {
	$formaction = $_POST['formaction'];
}


$inputProblemName;
$inputProblemDesc;
$inputProgenyPerMating;
$inputMaxProgeny;
$traitNameArray;
$inputLinkdist_01;
$inputLinkdist_12;
$inputTraitOrder;
$arrPhenotypes;
$inputEpistasis;


// =============================================================================
// Logic for handling form
// =============================================================================
if ($formaction == "modifyproblem" || $formaction == "assignproblem")
{
	//v2.0 variables
	// LOGIC FOR SHOWING PROBLEM FORM
	$V_problemId = $problemId;
	$V_trait = array($_POST['trait0'], $_POST['trait1'], $_POST['trait2']);
	$V_problemname = $_POST['problemname'];
	$V_problemdesc = $_POST['problemdesc'];
	$V_progpermating = $_POST['progpermating'];
	$V_totalprogeny = $_POST['totalprogeny'];
	if (!isset($_POST['dom0'])) {
		$_POST['dom0'] = null;
	}
	if (!isset($_POST['dom1'])) {
		$_POST['dom1'] = null;
	}
	if (!isset($_POST['dom2'])) {
		$_POST['dom2'] = null;
	}
	$V_dom = array($_POST['dom0']=='ON', $_POST['dom1']=='ON', $_POST['dom2']=='ON');
	$V_ordering = $_POST['ordering'];
	if ($V_ordering=='')
		$V_ordering="012";
	$V_epistCheck = isset($_POST['epistCheck'])? ($_POST['epistCheck']=='ON'):null;
	$V_pheno = (($V_epistCheck != null) ? array($_POST['pheno00'], $_POST['pheno01'], $_POST['pheno02'], $_POST['pheno10'], $_POST['pheno11'], $_POST['pheno12'], $_POST['pheno20'], $_POST['pheno21'], $_POST['pheno22'], $_POST['pheno23'])
	: array($_POST['pheno00'], $_POST['pheno01'], $_POST['pheno02'], $_POST['pheno10'], $_POST['pheno11'], $_POST['pheno12'], $_POST['pheno20'], $_POST['pheno21'], $_POST['pheno22']) );
	// $V_pheno = array($_POST['pheno00'], $_POST['pheno01'], $_POST['pheno02'], $_POST['pheno10'], $_POST['pheno11'], $_POST['pheno12'], $_POST['pheno20'], $_POST['pheno21'], $_POST['pheno22'], $_POST['pheno23']);
	// $V_epistCheck = $_POST['epistCheck']=='ON';
	$V_linkdist = array($_POST['linkdist01'], $_POST['linkdist12']);
	$V_epistCode = $_POST['epist'];

	$inputEpistasis = $V_epistCode;

	// write page header, including toolbar
	$inputProblemName = $_POST['problemname'];
	if (empty($inputProblemName))
	{
		(new UserError) -> addError(750);
	}

	$inputProblemDesc = $_POST['problemdesc'];

	$inputProgenyPerMating = $_POST['progpermating'];
	if ($inputProgenyPerMating < 1)
	{
		(new UserError) -> addError(751);
	}
	$inputMaxProgeny = $_POST['totalprogeny'];
	if ($inputMaxProgeny < 1)
	{
		(new UserError) -> addError(752);
	}
	$inputTrait0 = $_POST['trait0'];
	$inputTrait1 = $_POST['trait1'];
	$inputTrait2 = $_POST['trait2'];
	$traitNameArray = array($inputTrait0,$inputTrait1,$inputTrait2);

	if ($inputTrait0 == -1 || $inputTrait1 == -1 || $inputTrait2 == -1)
	{
		(new UserError) -> addError(753);
	}

	$arrPhenotypes = array();
	$arrPhenotypes[0] = determineDominance($_POST['dom0'] == 'ON', 0 , $arrPhenotypes);
	$arrPhenotypes[1] = determineDominance($_POST['dom1'] == 'ON', 1 , $arrPhenotypes);

	// if ($_POST['epistCheck']=='ON')
	if ($V_epistCheck)
	{
		$arrPhenotypes[2] = determineEpistasis($inputEpistasis, 2, $arrPhenotypes);
	}
	else
	{
		$inputEpistasis = -1;
		$arrPhenotypes[2] = determineDominance($_POST['dom2'] == 'ON', 2, $arrPhenotypes);
	}

	// get linkage distances
	// $inputLinkdist_01 = ($_POST['check01']=='ON')?($_POST['linkdist01']=='')?50:($_POST['linkdist01']==0)?50:$_POST['linkdist01']:50;
	// $inputLinkdist_12 = ($_POST['check12']=='ON')?($_POST['linkdist12']=='')?50:($_POST['linkdist12']==0)?50:$_POST['linkdist12']:50;
	$inputLinkdist_01 = ($_POST['check01']=='ON') ?
						(($_POST['linkdist01']=='') ? 50:
						(($_POST['linkdist01']==0) ? 50:$_POST['linkdist01'])):50;
	$inputLinkdist_12 = ($_POST['check12']=='ON') ?
						(($_POST['linkdist12']=='') ? 50:
						(($_POST['linkdist12']==0) ? 50:$_POST['linkdist12'])):50;
	
	if ($inputLinkdist_01 < 0 || $inputLinkdist_12 < 0)
	{
		(new UserError) -> addError(758);
	}
	// get trait orders
	if (isset($_POST['traitorder0']) && isset($_POST['traitorder1']) && isset($_POST['traitorder2'])) {
		$inputTraitOrder0 = $_POST['traitorder0']-1;
		$inputTraitOrder1 = $_POST['traitorder1']-1;
		$inputTraitOrder2 = $_POST['traitorder2']-1;
	}
	$inputTraitOrder = $V_ordering;
	$arrFinalPhenotypes = [];
    $arrFinalPhenotypes = $user->assignPhenotypeLogic($inputEpistasis,$arrPhenotypes,$arrFinalPhenotypes);

	$masterRecordset = $user->viewProblem($problemId);
	$problemEqual = areProblemsEqual($masterRecordset,
								 $inputProblemDesc,$inputProblemName,$inputLinkdist_01,
								 $inputLinkdist_12,$inputTraitOrder,$inputEpistasis,
								 $traitNameArray,$arrFinalPhenotypes,$inputProgenyPerMating,
								 $inputMaxProgeny );


	// finally - assign or modify the problem
	if( $formaction == "assignproblem" )
	{
		$rs = $user->getStudentProblem($studentId);
		if( !empty($rs) && $g_db->getNumRows($rs)<= 0 )
		{
			//echo "going into assignProblem";

			if ((new UserError()) -> hasError() == 0)
			{
				if ($problemEqual != true)
				{
					$user->assignModifiedProblem($studentId,$problemId,
										  $inputProblemDesc,$inputProblemName,$inputLinkdist_01,
										  $inputLinkdist_12,$inputTraitOrder,$inputEpistasis,
										  $traitNameArray,$arrPhenotypes,$inputProgenyPerMating,
										  $inputMaxProgeny);
				}
				else
				{
					$user->assignProblem($studentId,$problemId);
				}
			}

			if ((new UserError()) -> hasError() == 0)
			{
				$page->redirect("viewstudentlist.php");
			}
		}
		else
		{
			if ((new UserError()) -> hasError() == 0)
			{
				if ($problemEqual != true)
				{
					$user->reassignModifiedProblem($studentId,$problemId,
										  $inputProblemDesc,$inputProblemName,$inputLinkdist_01,
										  $inputLinkdist_12,$inputTraitOrder,$inputEpistasis,
										  $traitNameArray,$arrPhenotypes,$inputProgenyPerMating,
										  $inputMaxProgeny);
				}
				else
				{
					$user->reassignProblem($studentId,$problemId);
				}
			}

			if ((new UserError()) -> hasError() == 0)
			{
				//wrapUp("viewstudentlist.php");
				$page->redirect("viewstudentlist.php");
			}
		}

	}
	// Modify the Problem
	else
	{
		if ((new UserError()) -> hasError() == 0)
		{
			if ($problemEqual != true)
			{
				$user->modifyProblem($problemId,
									  $inputProblemDesc,$inputProblemName,$inputLinkdist_01,
									  $inputLinkdist_12,$inputTraitOrder,$inputEpistasis,
									  $traitNameArray,$arrPhenotypes,$inputProgenyPerMating,
									  $inputMaxProgeny);
			}
		}

		if ((new UserError()) -> hasError() == 0)
		{
			//problem summary

			//$page->setOnLoad("setTimeout('window.location = \\'".URLROOT."/admin/viewproblem.php?problemId=".$problemId."\\';', 2000);");
			//$page->writeHeader();
			//echo "<li>Problem Modified in progress........<li>Problem Modified.";
			//wrapUp("viewproblemlist.php");
			$page->redirect("viewproblemlist.php");
		}
		//echo "done modify";
	}
}
else
{
	$showProblemForm = true;
	//now checks for if there are problem id exist if so applies this problem's parameter.
	if ($problemId!="")
	{
		//check if problemId is valid integer:
		if (is_numeric($problemId))
		{
			$rs = $g_db -> querySelect("SELECT Description, Name, GMU1_2, GMU2_3, TraitOrder, EpistasisCode, Trait1Name, Trait1AAPhenoName, Trait1AbPhenoName, Trait1bAPhenoName, Trait1bbPhenoName, Trait2Name, Trait2AAPhenoName, Trait2AbPhenoName, Trait2bAPhenoName, Trait2bbPhenoName, Trait3Name, Trait3AAPhenoName, Trait3AbPhenoName, Trait3bAPhenoName, Trait3bbPhenoName, ProgenyPerMating, MaxProgeny FROM MasterProblem WHERE ProblemId = ".$problemId);
			if ($g_db -> getNumRows($rs) == 1)
			{
				$row = $g_db -> fetch($rs);
				$V_problemId = $problemId;
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
			}
			else
			{
				(new UserError) -> addError(653);
			}
		}
	}
}

// write page header, including toolbar
$page->writeHeader();
$page->handleErrors();

?>

<NOSCRIPT>
This page requires Javascript!!
</NOSCRIPT>

<!-- Content Start -->

<?php
// LOGIC FOR SHOWING PROBLEM FORM
if ($showProblemForm == true)
{
	// =============================================================================
	// logic for populating problem input form
	// =============================================================================

	$traitRecordset = $user->getTraits();

	$epistasisCode = 0;

	$traitIdArray;		// holds traitId info from recordset
	$traitNameArray;	// holds traitName info from recordset
	$pheno0IdArray;		// holds the phenoId info from recordset corresponding to trait0
	$pheno0NameArray;   // holds the phenoName info from recordset corresponding to trait0
	$pheno1IdArray;		// holds the phenoId info from recordset corresponding to trait1
	$pheno1NameArray;	// holds the phenoName info from recordset corresponding to trait1
	$pheno2IdArray;		// holds the phenoId info from recordset corresponding to trait2
	$pheno2NameArray;	// holds the phenoName info from recordset corresponding to trait2

	if (!empty($traitRecordset))
	{
		$traitIdArray = array();
		$traitNameArray = array();
		$pheno0IdArray = array();
		$pheno0NameArray = array();
		$pheno1IdArray = array();
		$pheno1NameArray = array();
		$pheno2IdArray = array();
		$pheno2NameArray = array();

		// load up the recordset into memory (in the from of arrays)
		$result = loadTraitsFromRecordset($traitRecordset,$traitIdArray,$traitNameArray);
		$traitIdArray = $result->TraitId;
		$traitNameArray = $result->Name;
	}
	problemForm();
}
else if ($showProblemSummary == true)
{
	$arrFinalPhenotypes = [];
    $arrFinalPhenotypes = $user->assignPhenotypeLogic($inputEpistasis,$arrPhenotypes, $arrFinalPhenotypes);

	echo "Problem Summary:<p>";
	viewProblem();
}

?>
<!-- Content End -->

<?php
// display any errors

// write main footer and close database connection
$page->writeFooter();
$g_db->disconnect();
//no need to worry about this part at this point


//----------------------  Functions ------------------------------

function setProblemList()
{	global $user, $g_db, $studentId;
	$link = URLROOT.'/admin/modifyproblem.php?';
	if ($studentId!='')
		$link .= 'studentId='.$studentId.'&';
	$link .= 'problemId=';
	$rs = $user -> getProblems();
	if ($rs)
	{
		echo "<form><select name=\"proBox\" onchange=\"window.location='".$link."'+this.options[this.selectedIndex].value;\">\n<Option value=\"-1\">Load from existing problem\n";
		while ($row=$g_db->fetch($rs))
		{
			if(strlen($row->Description) > 25)
				$problemDesc = substr($row->Description, 0, 22) . '...';
			else
				$problemDesc = $row->Description;
			echo "<Option value=\"".$row->ProblemId."\">".$row->Name." - ".$problemDesc."\n";
		}
		echo "</select></form><br>";
	}
}

function problemForm($type=''){
	global 	$g_epistaticRatios, $ASSIGN_FLAG, $studentId, $V_problemId, $V_trait, $V_problemname, $V_problemdesc,
			$V_progpermating, $V_totalprogeny, $V_dom, $V_ordering,
			$V_pheno, $V_epistCheck, $V_linkdist, $V_epistCode, $traitNameArray;
	$formType = $type;	//create or any other for modify
	//FORM START
	setProblemList();
	echo '<form name="problem" action="'.htmlentities($_SERVER['PHP_SELF']).'" method="post">';
	echo '<input type="hidden" name="formaction" value="createproblem">';
	echo '<input type="hidden" name="problemId" value="'.$V_problemId.'">';
	if( $ASSIGN_FLAG )
	{
		echo("<input type=\"hidden\" name=\"formaction\" value=\"assignproblem\">");
		echo("<input type=\"hidden\" name=\"studentId\" value=\"".$studentId."\">");
	}
	else
	{
		echo("<input type=\"hidden\" name=\"formaction\" value=\"modifyproblem\">");
	}
	$t = new Table(3, false);

	$t -> writeRow('Problem Name:', textInput('problemname', $V_problemname, 20, 30));
	$t -> writeRow('Problem Description:', textInput('problemdesc', $V_problemdesc, 40, 250));
	$t -> writeRow('Progeny Per Mating:', textInput('progpermating', $V_progpermating, 5, 10));
	$t -> writeRow('Maximum Progeny:', textInput('totalprogeny', $V_totalprogeny, 5, 10));
	$t -> writeRow('Display Order of Genes:&nbsp;', '<table border="0"><td><Input type="hidden" name="ordering" value="'.$V_ordering.'"><select name="order" size="3"><option value="'.substr($V_ordering, 0, 1).'">Gene '.(substr($V_ordering, 0, 1)*1+1).'</option><option value="'.substr($V_ordering, 1, 1).'">Gene '.(substr($V_ordering, 1, 1)*1+1).'</option><option value="'.substr($V_ordering, 2, 1).'">Gene '.(substr($V_ordering, 2, 1)*1+1).'</option></select></td><td><input name="Button" value="Up" type="button" onclick="reorder(true);"> <br> <input name="Button" value="Down" type="button" onclick="reorder(false);"></td></table>');
	$t -> writeDivider();

	$p = 0;
	$p_style = null;
	$ld = null;
	for ($i = 0; $i < 3; $i++)
	{
		$t -> writeHeaders('Gene '.($i+1),'');
		if ($formType=="create")
		{
			$t -> writeRow('Trait Name:', generateTraitSelectBox("trait".$i,$traitNameArray,$traitNameArray));
		}else{
			$t -> writeRow('Trait Name:',textInput('trait'.$i, $V_trait[$i]));
		}
		if ($i<2)
		{
			$innerTable = array('', '', '');
			$t -> writeRow('Dominance:', '<input name="dom'.$i.'" value="ON" type="checkbox" '.checked($V_dom[$i]).' onclick="DOM(this.name.charAt(3), this.checked);">');
		}
		else
		{
			$innerTable = array('<table border="0" cellspacing="0" cellpadding="0" width="100%"><td>', '</td><td>', '</td></table>');
			$t -> writeRow('<DIV id="EP0" name="EP0">Epistasis:</DIV>', '<DIV id="EP1" name="EP1"><input name="epistCheck" value="ON" type="checkbox" '  .checked($V_epistCheck).  " onclick=\"EP0(this.checked)\"></DIV>");
			$t -> writeRow('<DIV id="DO0" name="DO0">Dominance:</DIV>', '<DIV id="DO1" name="DO1"><input name="dom2" value="ON" type="checkbox" '.checked($V_dom[$i]).' onclick="DOM(this.name.charAt(3), this.checked);"></DIV>');
			$t -> writeRow('<DIV id="RA0" style="visibility: hidden;" name="RA0">Ratio:</DIV>', '<DIV id="RA1" style="visibility: hidden;" name="RA1">'.generateEpistSelectBox().'</DIV>');
		}
		if ($formType=="create")
		{
			$t -> writeRow(phenoTip($i, 0).'AA:</DIV>', $innerTable[0] . phenoData($i, 0) . $innerTable[1] . epistTip($i, 0) . $innerTable[2]);
			$t -> writeRow(phenoTip($i, 1).'Aa / aA:</DIV>', $innerTable[0] . phenoData($i, 1) . $innerTable[1] . epistTip($i, 1) . $innerTable[2]);
			$t -> writeRow(phenoTip($i, 2).'aa:</DIV>', $innerTable[0] . phenoData($i, 2) . $innerTable[1] . epistTip($i, 2) . $innerTable[2]);
		}
		else
		{
			$t -> writeRow(phenoTip($i, 0).'AA:</DIV>', $innerTable[0] . '<DIV id="pheno'.$i.'0" name="pheno'.$i.'0"'.$p_style.'>'.textInput('pheno'.$i.'0', $V_pheno[$p++]) .'<DIV>'. $innerTable[1] . epistTip($i, 0) . $innerTable[2]);
			$t -> writeRow(phenoTip($i, 1).'Aa / aA:</DIV>', $innerTable[0] . '<DIV id="pheno'.$i.'1" name="pheno'.$i.'1"'.$p_style.'>'.textInput('pheno'.$i.'1', $V_pheno[$p++]) .'<DIV>' . $innerTable[1] . epistTip($i, 1) . $innerTable[2]);
			$t -> writeRow(phenoTip($i, 2).'aa:</DIV>', $innerTable[0] . '<DIV id="pheno'.$i.'2" name="pheno'.$i.'2"'.$p_style.'>'.textInput('pheno'.$i.'2', $V_pheno[$p++]) .'<DIV>' . $innerTable[1] . epistTip($i, 2) . $innerTable[2]);
		}
		if ($i == 2)//have the extra phenotype selection
		{
			if ($formType=="create")
				$t -> writeRow(phenoTip($i, 3).'&nbsp;</DIV>', $innerTable[0] . phenoData($i, 3, 'visibility: hidden;') . $innerTable[1] . epistTip($i, 3, 'visibility: hidden;') . $innerTable[2]);
			else {
				if (count($V_pheno) === 10) 
					$t -> writeRow(phenoTip($i, 3).'&nbsp;</DIV>', $innerTable[0] . '<DIV id="pheno'.$i.'3" name="pheno'.$i.'3"'.$p_style.'>'.textInput('pheno'.$i.'3', $V_pheno[$p]) .'</DIV>'. $innerTable[1] . epistTip($i, 3, 'visibility: hidden;') . $innerTable[2]);
				// $t -> writeRow(phenoTip($i, 3).'&nbsp;</DIV>', $innerTable[0] . '<DIV id="pheno'.$i.'3" name="pheno'.$i.'3"'.$p_style.'>'.textInput('pheno'.$i.'3', $V_pheno[$p]) .'</DIV>'. $innerTable[1] . epistTip($i, 3, 'visibility: hidden;') . $innerTable[2]);
			}
		}
		if ($i == 1){
			if ($V_linkdist[0]==''){$V_linkdist[0] = 50; $ckd = "checked"; $ld = ' disabled="true"';}
			$t -> writeRow('Linkage:', '<input name="check'.($i-1).''.$i.'" value="ON" type="checkbox"'.checked(true).' onclick="this.form.linkdist'.($i-1).''.$i.'.disabled = !this.form.linkdist'.($i-1).''.$i.'.disabled"><input name="linkdist'.($i-1).''.$i.'"'.$ld.' value="'.$V_linkdist[0].'" size="5" type="text"><font size="-1"> (distance from gene '.$i.' in GMUs)</font>');
		}else if ($i == 2){
			if ($V_linkdist[1]==''){$V_linkdist[1] = 50; $ld = ' disabled="true"';}
			$t -> writeRow('<DIV id="LK0" name="LK0">Linkage:</DIV>', '<DIV id="LK1" name="LK1"><input name="check12" value="ON" type="checkbox"'.checked(true).' onclick="this.form.linkdist12.disabled = !this.form.linkdist12.disabled"><input name="linkdist12"'.$ld.' value="'.$V_linkdist[1].'" size="5" type="text"><font size="-1"> (distance from gene 2 in GMUs)</font></DIV>');
		}
		$t -> writeDivider();
	}
	//($formType=="create"||$V_problemId!='')?$t_str="Create Problem":$t_str="Modify Problem";
	$t_str;
	if ($ASSIGN_FLAG)
	{
		$t_str = "Assign Problem";
	}
	else
	{
		$t_str = "Modify Problem";
	}
	$t -> writeRow('<Input type=button value="'.$t_str.'" onclick="problemCheck(this.form);">', '&nbsp;');
	$t -> flush();
	echo "</form>";
}

function viewProblem(){
	global 	$g_epistaticRatios, $V_trait, $V_problemname, $V_problemdesc,
			$V_progpermating, $V_totalprogeny, $V_dom, $V_ordering,
			$V_pheno, $V_epistCheck, $V_linkdist, $V_epistCode;

	$t = new Table(3);

	$t -> writeRow('Problem Name:', $V_problemname);
	$t -> writeRow('Problem Description:', $V_problemdesc);
	$t -> writeRow('Progeny Per Mating:', $V_progpermating);
	$t -> writeRow('Maximum Progeny:', $V_totalprogeny);
	$t -> writeRow('Display Order of Genes:&nbsp;', '<select name="order" size="3"><option value="'.substr($V_ordering, 0, 1).'">Gene '.(substr($V_ordering, 0, 1)*1+1).'</option><option value="'.substr($V_ordering, 1, 1).'">Gene '.(substr($V_ordering, 1, 1)*1+1).'</option><option value="'.substr($V_ordering, 2, 1).'">Gene '.(substr($V_ordering, 2, 1)*1+1).'</option></select>');
	$t -> writeDivider();

	$p = 0;
	for ($i = 0; $i < 3; $i++)
	{
		$t -> writeHeaders('Gene '.($i+1),'');
		$t -> writeRow('Trait Name:', $V_trait[$i]);

		if ($i==2)
		{
			if ($V_epistCheck){
				$t -> writeRow('Epistasis Selected', $g_epistaticRatios[$V_epistCode]);
				$temp = explode(":", $g_epistaticRatios[$V_epistCode]);
			}
		}

		$t -> writeRow(phenoTip($i, 0).'AA:</DIV>', $temp[0].': '.$V_pheno[$p++]);
		$t -> writeRow(phenoTip($i, 1).'Aa / aA:</DIV>', $temp[1].': '.$V_pheno[$p++]);
		(!$V_dom[$i])?
			$t -> writeRow(phenoTip($i, 2).'aa:</DIV>', $temp[2].': '.$V_pheno[$p++]):$p++;

		if ($i == 2&&$V_epistCheck&&count(explode(":", $g_epistaticRatios[$V_epistCode]))==4)//have the extra phenotype selection
		{
			$t -> writeRow(phenoTip($i, 3).'&nbsp;</DIV>', $temp[3].': '.$V_pheno[$p++]);
		}
		if ($i == 1){
			if ($V_linkdist[0]==''){$V_linkdist[0] = 0;}
			$t -> writeRow('Linkage:', $V_linkdist[0].'<font size="-1"> (distance from gene '.$i.' in GMUs)</font>');
		}else if ($i == 2&&!$V_epistCheck){
			if ($V_linkdist[1]==''){$V_linkdist[1] = 0;}
			$t -> writeRow('<DIV id="LK0" name="LK0">Linkage:</DIV>', '<DIV id="LK1" name="LK1">'.$V_linkdist[1].'<font size="-1"> (distance from gene 2 in GMUs)</font></DIV>');
		}
		$t -> writeDivider();
	}
	$t -> flush();
}

// DATA LOGIC
// some small helper functions
function areProblemsEqual(	$masterRecordset,
							$inputProblemDesc,$inputProblemName,$inputLinkdist_01,
							$inputLinkdist_12,$inputTraitOrder,$inputEpistasis,
							$traitNameArray,$arrFinalPhenotypes,$inputProgenyPerMating,
							$inputMaxProgeny )
{
	global $g_db;

	//$masterRecordset = $user->viewProblem($problemId);
	if (empty($masterRecordset))
	{
		// UserError::addError(653);
		(new UserError) -> addError(653);
		return false;
	}
	else
	{
		$row = $g_db->fetch($masterRecordset);

		// ugly brute force way of comparing two problems
		if (strcmp($row->Description,$inputProblemDesc))
		{
			//echo "<p>original problem desc..." . $row->Description;
			//echo "<p>new problem desc..." . $inputProblemDesc;
			return false;
		}
		if (strcmp($row->Name,$inputProblemName))
		{
			return false;
		}
		if (strcmp($row->GMU1_2,$inputLinkdist_01))
		{
			return false;
		}
		if (strcmp($row->GMU2_3,$inputLinkdist_12))
		{
			return false;
		}
		if (strcmp($row->TraitOrder,$inputTraitOrder))
		{
			return false;
		}
		if (isset($row->EpistasisCode) && $inputEpistasis == -1)
		{
			//echo "epistasis code is not null in original";
			return false;
		}
		if (!isset($row->EpistasisCode) && $inputEpistasis != -1)
		{
			//echo "epistasis code is null in original";
			return false;
		}
		if (strcmp($row->Trait1Name,$traitNameArray[0]))
		{
			return false;
		}
		if (strcmp($row->Trait1AAPhenoName,$arrFinalPhenotypes[0][0]))
		{
			return false;
		}
		if (strcmp($row->Trait1AbPhenoName,$arrFinalPhenotypes[0][1]))
		{
			return false;
		}
		if (strcmp($row->Trait1bAPhenoName,$arrFinalPhenotypes[0][2]))
		{
			return false;
		}
		if (strcmp($row->Trait1bbPhenoName,$arrFinalPhenotypes[0][3]))
		{
			return false;
		}
		if (strcmp($row->Trait2Name,$traitNameArray[1]))
		{
			return false;
		}
		if (strcmp($row->Trait2AAPhenoName,$arrFinalPhenotypes[1][0]))
		{
			return false;
		}
		if (strcmp($row->Trait2AbPhenoName,$arrFinalPhenotypes[1][1]))
		{
			return false;
		}
		if (strcmp($row->Trait2bAPhenoName,$arrFinalPhenotypes[1][2]))
		{
			return false;
		}
		if (strcmp($row->Trait2bbPhenoName,$arrFinalPhenotypes[1][3]))
		{
			return false;
		}
		if (strcmp($row->Trait3Name,$traitNameArray[2]))
		{
			return false;
		}
		if (strcmp($row->Trait3AAPhenoName,$arrFinalPhenotypes[2][0]))
		{
			return false;
		}
		if (strcmp($row->Trait3AbPhenoName,$arrFinalPhenotypes[2][1]))
		{
			return false;
		}
		if (strcmp($row->Trait3bAPhenoName,$arrFinalPhenotypes[2][2]))
		{
			return false;
		}
		if (strcmp($row->Trait3bbPhenoName,$arrFinalPhenotypes[2][3]))
		{
			return false;
		}
		if ($row->ProgenyPerMating != $inputProgenyPerMating)
		{
			return false;
		}
		if ($row->MaxProgeny != $inputMaxProgeny)
		{
			return false;
		}

		// still here at this point? the problems must be the same
		return true;
	}
}

function loadTraitsFromRecordset($p_recordset,$p_traitIdArray, $p_traitNameArray)
{
	global $g_db;
	$counter = $g_db->getNumRows($p_recordset);

	$currRow;

	// iterate through each row, and get the information
	for ($i = 0; $i < $counter; $i++)
	{
		$currRow = $g_db->fetch($p_recordset);
		$p_traitIdArray[$i]= $currRow->TraitId;
		$p_traitNameArray[$i] = $currRow->Name;
	}
	return $currRow;
}

function loadPhenotypesFromRecordset($p_recordset,$p_phenoIdArray, $p_phenoNameArray)
{
	global $g_db;
	$counter = $g_db->getNumRows($p_recordset);

	$currRow;

	// iterate through each row, and get the information
	for ($i = 0; $i < $counter; $i++)
	{
		$currRow = $g_db->fetch($p_recordset);
		$p_phenoIdArray[$i]= $currRow->PhenotypeId;
		$p_phenoNameArray[$i] = $currRow->Name;
	}
}

function generateTraitSelectBox($p_name, $p_traitIdArray, $p_traitNameArray)
{
	$counter = count($p_traitIdArray);

	$traitSelectBox = "<select name=\"". $p_name . "\" onchange=\"trait('".substr($p_name, strlen($p_name)-1)."');\">\n";

	$traitSelectBox = $traitSelectBox. "<option value=\"-1\">Select a trait";
	// iterate through each row, and get the information
	for ($i = 0; $i < $counter; $i++)
	{
		$option = "<option value=\"" . $p_traitIdArray[$i] . "\">" . $p_traitNameArray[$i]."\n";
		$traitSelectBox = $traitSelectBox.$option;
	}

	$traitSelectBox = $traitSelectBox."</select>\n";
	return $traitSelectBox;
}

function generateEpistSelectBox()
{
	global $g_epistaticRatios, $V_epistCode;
	$counter = count($g_epistaticRatios);
	$epistSelectBox = "<select name=\"". "epist" . "\" onchange=\"epi(this.form, this);\">\n";

	$epistSelectBox = $epistSelectBox . "<option value=\"-1\">Select Epistasis Ratio";
	// iterate through each row, and get the information
	for ($i = 0; $i < $counter; $i++)
	{
		$option = "<option value=\"" . $i . "\"";
		if ($V_epistCode==$i)
			$option .= " selected";
		$option .= ">" . $g_epistaticRatios[$i]."\n";
		$epistSelectBox = $epistSelectBox.$option;
	}
	$epistSelectBox = $epistSelectBox."</select>\n";
	return $epistSelectBox;
}

function determineDominance($p_isDominance,$p_traitNumber,$p_arrPhenotypes)
{
	$AATrait;
	$bbTrait;
	$mixedTrait;

	if ($p_isDominance)
	{
		$AATrait = $_POST['pheno'.$p_traitNumber.'0'];
		$bbTrait = $_POST['pheno'.$p_traitNumber.'1'];

		if (empty($AATrait) || empty($bbTrait))
		{
			(new UserError) -> addError(756);
			return false;
		}
		else
		{
			return array($AATrait,$bbTrait);
		}
	}
	$AATrait = $_POST['pheno'.$p_traitNumber.'0'];
	$mixedTrait = $_POST['pheno'.$p_traitNumber.'1'];
	$bbTrait = $_POST['pheno'.$p_traitNumber.'2'];
	if (empty($AATrait) || empty($bbTrait) || empty($mixedTrait) )
	{
		(new UserError) -> addError(757);
		return false;
	}
	
	return array($AATrait,$bbTrait,$mixedTrait);
}

function determineEpistasis($p_epistasisValue, $p_traitNumber, $p_arrPhenotypes)
{
	global $g_epistaticRatios;
	$epistRatios = explode(":", $g_epistaticRatios[$p_epistasisValue]);
	$counter = count($epistRatios);
	$epistTraits = array();

	for ($i = 0; $i < $counter; $i++)
	{
		if (empty($_POST['pheno2'.$i]))
		{
			(new UserError) -> addError(760);
			return false;
		}
		else
		{
			$epistTraits[$i] = $_POST['pheno2'.$i];
		}
	}
	return $epistTraits;
}


function textInput($p_name, $p_value='', $p_size='', $p_maxlength='')
{
	$p_value = str_replace("<", "&lt;", $p_value);
	$p_value = str_replace("\"", "&quot;", $p_value);

	$size = ($p_size == '' ? '' : " size=\"$p_size\"");
	$length = ($p_maxlength == ''? '' : " maxlength=\"$p_maxlength\"");

	return "<input name=\"$p_name\" type=\"text\" $size $length value=\"$p_value\">";
}

function checked($k){return ($k)?' checked':'';}

function phenoTip($i, $j)
{
	return '<DIV id="tip'.$i.''.$j.'" name="tip'.$i.''.$j.'">';
}

function phenoData($i, $j, $p_style='')
{
	if ($p_style!='') $p_style= ' style="'. $p_style .'"';
	return '<DIV id="pheno'.$i.''.$j.'" name="pheno'.$i.''.$j.'"'.$p_style.'><select name="pheno'.$i.''.$j.'"><option>--------------</option><option><option><option><option><option><option></select></DIV>';
}

function epistTip($i, $j)
{
	if ($i == 2)
		return '<DIV id="C'.$j.'" style="visibility: hidden;" name="C'.$j.'"><input type="text" name="T'.$j.'" value="" size="2" readonly disabled="true"></DIV>';
	else
		return '';
}

function wrapUp($linkForward){
//	$page->redirect($linkForward);
}
//--------------------------------------------------------------
?>
