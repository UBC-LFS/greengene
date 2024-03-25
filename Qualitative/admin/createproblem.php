<?php
require_once('../includes/global.php');

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
$user = (new Security()) -> getUser();

// PAGE CREATION LOGIC
$page = new Page($user, 'Create Problem', 1);
//$page -> addJSInclude("createproblem.js"); //for old version
$page -> addJSInclude("problem.js");
$page -> setOnLoad("loadData();");

$userId = $user->m_userId;

// DATABASE CONNECTION
$g_db = new DB();

// FORM LOGIC
// - get form variables
$showProblemForm = false;
$showProblemSummary = false;

$formaction = false;
if (isset($_POST['formaction'])) {
	$formaction = $_POST['formaction'];
}

$traitRecordset = $user->getTraits();

// =============================================================================
// Logic for handling form
// =============================================================================
if ($formaction == "createproblem")
{
$page -> setOnLoad("loadTips();");
	$inputEpistasis = $_POST['epist'];

	$inputProblemName = $_POST['problemname'];
	if (empty($inputProblemName))
	{
		(new UserError()) -> addError(750);
	}

	$inputProblemDesc = $_POST['problemdesc'];

	$inputProgenyPerMating = $_POST['progpermating'];
	if ($inputProgenyPerMating < 1)
	{
		(new UserError()) -> addError(751);
	}
	$inputMaxProgeny = $_POST['totalprogeny'];
	if ($inputMaxProgeny < 1)
	{
		(new UserError()) -> addError(752);
	}

	$inputTrait0 = $_POST['trait0'];
	$inputTrait1 = $_POST['trait1'];
	$inputTrait2 = $_POST['trait2'];
	$traitNameArray = array($inputTrait0,$inputTrait1,$inputTrait2);

	if ($inputTrait0 == -1 || $inputTrait1 == -1 || $inputTrait2 == -1)
	{
		(new UserError()) -> addError(753);
	}

	$arrPhenotypes = array();
	$dom0 = isset($_POST['dom0']) ? $_POST['dom0'] : null; 
	$dom1 = isset($_POST['dom1']) ? $_POST['dom1'] : null; 
	$dom2 = isset($_POST['dom2']) ? $_POST['dom2'] : null; 
	$arrPhenotypes[0] = determineDominance($dom0,0,$arrPhenotypes);
	$arrPhenotypes[1] = determineDominance($dom1,1,$arrPhenotypes);
	// $arrPhenotypes = determineDominance($_POST['dom0'] == 'ON',0,$arrPhenotypes);
	// $arrPhenotypes = determineDominance($_POST['dom1'] == 'ON',1,$arrPhenotypes);

	// passbyreference - resolved
	// determineDominance($_POST['dom0'] == 'ON',0,&$arrPhenotypes);
	// determineDominance($_POST['dom1'] == 'ON',1,&$arrPhenotypes);
	if (isset($_POST['epistCheck']) && $_POST['epistCheck']=='ON')
	{
		// passbyreference - resolved
		$arrPhenotypes[2] = determineEpistasis($inputEpistasis,2,$arrPhenotypes);
		//determineEpistasis($inputEpistasis,2,&$arrPhenotypes);
	}
	else
	{
		$inputEpistasis = -1;
		// passbyreference - resolved
		$arrPhenotypes[2] = determineDominance($dom2,2,$arrPhenotypes);
		// determineDominance($_POST['dom2'] == 'ON',2,&$arrPhenotypes);
	}

	// get linkage distances
	// $inputLinkdist_01 = (isset($_POST['check01'])&& $_POST['check01']=='ON')?($_POST['linkdist01']=='')?50:($_POST['linkdist01']==0)?50:$_POST['linkdist01']:50;
	// $inputLinkdist_12 = (isset($_POST['check12'])&& $_POST['check12']=='ON')?($_POST['linkdist12']=='')?50:($_POST['linkdist12']==0)?50:$_POST['linkdist12']:50;
	$inputLinkdist_01 = (isset($_POST['check01']) && $_POST['check01']=='ON') ?
						(($_POST['linkdist01']=='') ? 50:
						(($_POST['linkdist01']==0) ? 50: $_POST['linkdist01'])) : 50;
	$inputLinkdist_12 = (isset($_POST['check12'])&& $_POST['check12']=='ON') ? 
						(($_POST['linkdist12']=='') ? 50: 
						(($_POST['linkdist12']==0) ? 50: $_POST['linkdist12'])) : 50;
	
	if ($inputLinkdist_01 < 0 || $inputLinkdist_12 < 0)
	{
		(new UserError()) -> addError(758);
	}

	$inputTraitOrder = $_POST['ordering'];


	// finally - create the problem into DB!
	if ((new UserError()) -> hasError() > 0 ||
		$user->createProblem($inputProblemDesc,$inputProblemName,$inputLinkdist_01,
							  $inputLinkdist_12,$inputTraitOrder,$inputEpistasis,
							  $traitNameArray,$arrPhenotypes,$inputProgenyPerMating,
							  $inputMaxProgeny)!=true)
	{
		(new UserError()) -> addError(761);
	}
	else
	{
		$page -> redirect('viewproblemlist.php');
		//$showProblemSummary = true;
	}

}
else
{
	$showProblemForm = true;
	if (!empty($traitRecordset))
	{
		$traitIdArray = array();
		$traitNameArray = array();

		// load up the recordset into memory (in the from of arrays)
		// passbyreference - resolved
		$result = loadTraitsFromRecordset($traitRecordset,$traitIdArray,$traitNameArray);
		$traitIdArray = $result['traitIdArray'];
		$traitNameArray = $result['traitNameArray'];
		// loadTraitsFromRecordset($traitRecordset,&$traitIdArray,&$traitNameArray);
	}
}

//extra helper functions:
function setProblemList()
{	global $user, $g_db, $studentId;
	$link = URLROOT.'/admin/createproblem.php?';
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
		echo "</select></form><br><br>";
	}
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
	return array("traitIdArray" => $p_traitIdArray, "traitNameArray" => $p_traitNameArray);
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

//v2.0
function determineDominance($p_isDominance,$p_traitNumber,$p_arrPhenotypes) // (bool, int, &array) : void
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
			(new UserError()) -> addError(756);
			return false;
		}
		
		
		// $p_arrPhenotypes[$p_traitNumber] = array($AATrait,$bbTrait);
		return array($AATrait, $bbTrait);
	}
	
	
	$AATrait = $_POST['pheno'.$p_traitNumber.'0'];
	$mixedTrait = $_POST['pheno'.$p_traitNumber.'1'];
	$bbTrait = $_POST['pheno'.$p_traitNumber.'2'];
	if (empty($AATrait) || empty($bbTrait) || empty($mixedTrait))
	{
		(new UserError()) -> addError(757);
		return false;
	}

	// $p_arrPhenotypes[$p_traitNumber] = array($AATrait,$bbTrait,$mixedTrait);
	return array($AATrait, $bbTrait, $mixedTrait);
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
			(new UserError()) -> addError(760);
			return false;
		}
		else
		{
			$epistTraits[$i] = $_POST['pheno2'.$i];
		}
	}
	return $epistTraits;
	// return True;
}

function problemForm($type='', $V_pheno, $V_epistCheck){
	global 	$g_epistaticRatios, $V_problemId, $V_trait, $V_problemname, $V_problemdesc,
			$V_progpermating, $V_totalprogeny, $V_dom, $V_ordering,
			$V_epistCheck, $V_linkdist, $V_epistCode, $traitNameArray;
	$formType = $type;	//create or any other for modify
	if ($V_problemId!='')
		$formType = "createFromExisting";
	//FORM START
	$t = new Table(3, false);
	setProblemList();
	echo '<form name="problem" action="'.htmlentities($_SERVER['PHP_SELF']).'" method="post">';
	echo '<input type="hidden" name="formaction" value="createproblem">';
	echo '<input type="hidden" name="problemId" value="'.$V_problemId.'">';

	// Added to remove $p_style and $id undefined notice
	$p_style = "";
	$ld = "";
	$p = 0;

	if(empty($V_progpermating))
		$V_progpermating = 50;
	if(empty($V_totalprogeny))
		$V_totalprogeny = 1000;

	$t -> writeRow('Problem Name:', textInput('problemname', $V_problemname, 20, 30));
	$t -> writeRow('Problem Description:', textInput('problemdesc', $V_problemdesc, 40, 250));
	$t -> writeRow('Progeny Per Mating:', textInput('progpermating', $V_progpermating, 5, 10));
	$t -> writeRow('Maximum Progeny:', textInput('totalprogeny', $V_totalprogeny, 5, 10));
	$t -> writeRow('Display Order of Genes:&nbsp;', '<table border="0"><td><Input type="hidden" name="ordering" value="'.$V_ordering.'"><select name="order" size="3"><option value="'.substr($V_ordering, 0, 1).'">Gene '.(substr($V_ordering, 0, 1)*1+1).'</option><option value="'.substr($V_ordering, 1, 1).'">Gene '.(substr($V_ordering, 1, 1)*1+1).'</option><option value="'.substr($V_ordering, 2, 1).'">Gene '.(substr($V_ordering, 2, 1)*1+1).'</option></select></td><td><input name="Button" value="Up" type="button" onclick="reorder(true);"> <br> <input name="Button" value="Down" type="button" onclick="reorder(false);"></td></table>');
	$t -> writeDivider();

	if (!isset($_POST['linkdist01']) || !isset($_POST['linkdist12'])) {
		$_POST['linkdist01'] = false;
		$_POST['linkdist12'] = false;
	}

	$p = 0;
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
			$innerTable = array('<table border="0" cellspacing="0" cellpadding="0"><td>', '</td><td>', '</td></table>');
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
			$t -> writeRow(phenoTip($i, 0).'AA:</DIV>', $innerTable[0] . '<DIV id="pheno'.$i.'0" name="pheno'.$i.'0"'.$p_style.'>'.textInput('pheno'.$i.'0', $V_pheno[$p++]). '</DIV>' . $innerTable[1] . epistTip($i, 0) . $innerTable[2]);
			$t -> writeRow(phenoTip($i, 1).'Aa / aA:</DIV>', $innerTable[0] . '<DIV id="pheno'.$i.'1" name="pheno'.$i.'1"'.$p_style.'>'.textInput('pheno'.$i.'1', $V_pheno[$p++]). '</DIV>' . $innerTable[1] . epistTip($i, 1) . $innerTable[2]);
			$t -> writeRow(phenoTip($i, 2).'aa:</DIV>', $innerTable[0] . '<DIV id="pheno'.$i.'2" name="pheno'.$i.'2"'.$p_style.'>'.textInput('pheno'.$i.'2', $V_pheno[$p++]). '</DIV>' . $innerTable[1] . epistTip($i, 2) . $innerTable[2]);
		}
		if ($i == 2)//have the extra phenotype selection
		{
			if ($formType=="create")
				$t -> writeRow(phenoTip($i, 3).'&nbsp;</DIV>', $innerTable[0] . phenoData($i, 3, 'visibility: hidden;') . $innerTable[1] . epistTip($i, 3, 'visibility: hidden;') . $innerTable[2]);
			else {
				if ($V_epistCheck) {
					$t -> writeRow(phenoTip($i, 3).'&nbsp;</DIV>', $innerTable[0] . '<DIV id="pheno'.$i.'3" name="pheno'.$i.'3"'.$p_style.'>'.textInput('pheno'.$i.'3', $V_pheno[$p]). '</DIV>' . $innerTable[1] . epistTip($i, 3, 'visibility: hidden;') . $innerTable[2]);
				}
			}
		}
		if ($i == 1){
			if ($V_linkdist[0]==''){$V_linkdist[0] = 50; $ld = ' disabled="true"';}
			$t -> writeRow('Linkage:', '<input name="check'.($i-1).''.$i.'" value="ON" type="checkbox"'.checked($_POST['linkdist01']!='').' onclick="this.form.linkdist'.($i-1).''.$i.'.disabled = !this.form.linkdist'.($i-1).''.$i.'.disabled"><input name="linkdist'.($i-1).''.$i.'"'.$ld.' value="'.$V_linkdist[0].'" size="5" type="text"><font size="-1"> (distance from gene '.$i.' in GMUs)</font>');
		}else if ($i == 2){
			if ($V_linkdist[1]==''){$V_linkdist[1] = 50; $ld = ' disabled="true"';}
			$t -> writeRow('<DIV id="LK0" name="LK0">Linkage:</DIV>', '<DIV id="LK1" name="LK1"><input name="check12" value="ON" type="checkbox"'.checked($_POST['linkdist12']!='').' onclick="this.form.linkdist12.disabled = !this.form.linkdist12.disabled"><input name="linkdist12"'.$ld.' value="'.$V_linkdist[1].'" size="5" type="text"><font size="-1"> (distance from gene 2 in GMUs)</font></DIV>');
		}
		$t -> writeDivider();
	}
	($formType=="create"||$V_problemId!='')?$t_str="Create Problem":$t_str="Modify Problem";
	$t -> writeRow('<Input type=button value="'.$t_str.'" onclick="problemCheck(this.form);">', '&nbsp;');
	$t -> flush();
	echo "</form>";
}

function viewProblem(){
	global 	$g_epistaticRatios, $V_trait, $V_problemname, $V_problemdesc,
			$V_progpermating, $V_totalprogeny, $V_dom, $V_ordering,
			$V_pheno, $V_epistCheck, $V_linkdist, $V_epistCode;

	$t = new Table(3, false);

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

function javascriptSetup($traitIdArray, $traitNameArray){
	global $page, $g_db, $user;
	$traitSize = count($traitIdArray);
	echo "var tdb = new Array(".$traitSize.");\n";
	for ($i = 0; $i < $traitSize; $i++)
	{
		$rs = $user -> getPhenotypes($traitIdArray[$i]);
		echo "tdb[".$i."] = new Array(2);\n";//trait ID with corresponding list of phenotype
		echo "tdb[".$i."][0] = '".str_replace("'", "\\'", $traitNameArray[$i])."';\n";//trait ID
		echo "tdb[".$i."][1] = new Array(".$g_db -> getNumRows($rs).");\n";//size of list of phenotype
		$j = 0;
		while ($phenoType = $g_db -> fetch($rs))
		{
			echo "tdb[".$i."][1][".$j."] = new Array(2);\n";//store phenotype id value pair
			echo "tdb[".$i."][1][".$j."][0] = ".$phenoType -> PhenotypeId.";\n";
			echo "tdb[".$i."][1][".$j."][1] = '".str_replace("'", "\\'", $phenoType -> Name)."';\n";
			$j++;
		}
	}
}


// write page header, including toolbar
$page->writeHeader();


// LOGIC FOR SHOWING PROBLEM FORM
// values will be assigned false if non of the values are set
$V_problemId = isset($_GET['problemId'])? $_GET['problemId'] : (isset($_POST['problemId'])?$_POST['problemId']:false);
//$V_problemId = $_GET['problemId']?$_GET['problemId']:$_POST['problemId'];
$V_trait = array(
	isset($_POST['trait0'])?$_POST['trait0']: false,
	isset($_POST['trait1'])?$_POST['trait1']: false,
	isset($_POST['trait2'])?$_POST['trait2']: false);
//$V_trait = array($_POST['trait0'], $_POST['trait1'], $_POST['trait2']);
$V_problemname = isset($_POST['problemname'])?$_POST['problemname']:false;
//$V_problemname = $_POST['problemname'];
$V_problemdesc = isset($_POST['problemdesc'])?$_POST['problemdesc']:false;
//$V_problemdesc = $_POST['problemdesc'];
$V_progpermating = isset($_POST['progpermating'])?$_POST['progpermating']:false;
//$V_progpermating = $_POST['progpermating'];
$V_totalprogeny = isset($_POST['totalprogeny'])?$_POST['totalprogeny']:false;
//$V_totalprogeny = $_POST['totalprogeny'];
$V_dom = array(
	isset($_POST['dom0'])?$_POST['dom0']=='ON': false,
	isset($_POST['dom1'])?$_POST['dom1']=='ON': false,
	isset($_POST['dom2'])?$_POST['dom2']=='ON': false);
//$V_dom = array($_POST['dom0']=='ON', $_POST['dom1']=='ON', $_POST['dom2']=='ON');
$V_ordering = isset($_POST['ordering'])?$_POST['ordering']: false;
//$V_ordering = $_POST['ordering'];
if ($V_ordering=='')
	$V_ordering="012";
$V_pheno = array(
	isset($_POST['pheno00'])?$_POST['pheno00']:false,
	isset($_POST['pheno01'])?$_POST['pheno01']:false,
	isset($_POST['pheno02'])?$_POST['pheno02']:false,
	isset($_POST['pheno10'])?$_POST['pheno10']:false,
	isset($_POST['pheno11'])?$_POST['pheno11']:false,
	isset($_POST['pheno12'])?$_POST['pheno12']:false,
	isset($_POST['pheno20'])?$_POST['pheno20']:false,
	isset($_POST['pheno21'])?$_POST['pheno21']:false,
	isset($_POST['pheno22'])?$_POST['pheno22']:false,
	isset($_POST['pheno23'])?$_POST['pheno23']:false);
//$V_pheno = array($_POST['pheno00'], 
//			$_POST['pheno01'], $_POST['pheno02'],
//			 $_POST['pheno10'], $_POST['pheno11'], 
// 				$_POST['pheno12'], $_POST['pheno20'],
//				 $_POST['pheno21'], $_POST['pheno22'], 
// 					$_POST['pheno23']); - only when Epistasis clicked
$V_epistCheck = isset($_POST['epistCheck'])?($_POST['epistCheck']=='ON'):false;
//$V_epistCheck = $_POST['epistCheck']=='ON';
$V_linkdist = array(
	isset($_POST['linkdist01'])?$_POST['linkdist01']:false,
	isset($_POST['linkdist12'])?$_POST['linkdist12']:false);
//$V_linkdist = array($_POST['linkdist01'], $_POST['linkdist12']);
$V_epistCode = isset($_POST['epist'])?$_POST['epist']:false;
//$V_epistCode = $_POST['epist'];


echo '<script language="JavaScript">';

// passbyreference - resolved? value of parameter was not reassigned inside javascriptSetup
//javascriptSetup(&$traitIdArray, &$traitNameArray);
javascriptSetup($traitIdArray, $traitNameArray);

echo "function loadTips(){\n";
	for ($i = 0; $i < 3; $i++){
		if ($V_dom[$i]){
			echo "setTip($i, dom);";
		}
	}
	if ($V_epistCheck){
		echo "setTip(2, epis);\n";
	}
echo "}\n</script>";
?>


<NOSCRIPT>
This page requires Javascript!!
</NOSCRIPT>
<!-- Content Start -->

<?php

//-------------------------------------------------------------------------------
if ($showProblemForm == true){
	if ($V_problemId!=''){
		$masterRecordset = $user->viewProblem($V_problemId);
		$row = $g_db->fetch($masterRecordset);
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
		}else {
			($V_dom[2])?
			array_push($V_pheno, $row->Trait3AAPhenoName,$row->Trait3bbPhenoName,$row->Trait3AbPhenoName):
			array_push($V_pheno, $row->Trait3AAPhenoName,$row->Trait3AbPhenoName,$row->Trait3bbPhenoName);
			$V_linkdist = array($row->GMU1_2, $row->GMU2_3);
		}
	}
	problemForm("create", $V_pheno, $V_epistCheck);
}
//-------------------------------------------------------------------------------

//-------------------------------------------------------------------------------
if ($showProblemSummary == true){
	viewProblem();
}
//-------------------------------------------------------------------------------
?>
<!-- Content End -->

<?php
// display any errors
$page->handleErrors();

// write main footer and close database connection
$page->writeFooter();
$g_db->disconnect();
//no need to worry about this part at this point
?>
