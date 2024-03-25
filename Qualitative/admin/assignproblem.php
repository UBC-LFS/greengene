<?php
require_once('../includes/global.php');

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
$user = (new Security) -> getUser();


// PAGE CREATION LOGIC
$page = new Page($user, 'Assign Problem Page', 1);

$userId = $user->m_userId;

// DATABASE CONNECTION
$g_db = new DB();

// FORM LOGIC
// - get form variables
$showProblemForm = false;
$showProblemSummary = false;

$formaction = $_POST['formaction'];

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
if ($formaction == "createproblem")
{
		
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
	// check for dominance/codominance on traits
	$inputRadio0 = $_POST['radio0'];
	if (empty($inputRadio0))
	{
		(new UserError) -> addError(754);		
	}
	
	determineDominance($inputRadio0,0,&$arrPhenotypes);

	$inputRadio1 = $_POST['radio1'];
	if (empty($inputRadio1))
	{
		(new UserError) -> addError(755);
	}	
	determineDominance($inputRadio1,1,&$arrPhenotypes);
	
	// for the third trait - we have to check to see if epistasis was chosen
	$inputRadio2 = $_POST['radio2'];
	$inputEpistasis = $_POST['epist'];
	if (empty($inputRadio2) && $inputEpistasis == -1)
	{
		(new UserError) -> addError(762);
	}
	else
	{		
		if ($inputEpistasis == -1)
		{						
			determineDominance($inputRadio2,2,&$arrPhenotypes);	
		}
		else
		{
			determineEpistasis($inputEpistasis,2,&$arrPhenotypes);
		}
	}
		
	/*// print out the arrPhenotypes for testing
	for ($i = 0; $i < 3; $i++)
	{
		$counter = count($arrPhenotypes[$i]);
		for ($j = 0; $j < $counter; $j++)
		{
			echo $arrPhenotypes[$i][$j] . ",";
		}
		echo "<br>\n";
	}*/
	
	// get linkage distances
	$inputLinkdist_01 = $_POST['linkdist01'];
	$inputLinkdist_12 = $_POST['linkdist12'];
	if ($inputLinkdist_01 < 0 || $inputLinkdist_12 < 0)
	{
		(new UserError) -> addError(758);
	}
	
	// get trait orders
	$inputTraitOrder0 = $_POST['traitorder0'];	 			
	$inputTraitOrder1 = $_POST['traitorder1'];	 			
	$inputTraitOrder2 = $_POST['traitorder2'];	
	
	if (	$inputTraitOrder0 == $inputTraitOrder1 || $inputTraitOrder0 == $inputTraitOrder2
	 	|| 	$inputTraitOrder1 == $inputTraitOrder2 )
	{	
		(new UserError) -> addError(759);
	}
	else
	{
		// we subtract one b/c the trait orders starts at 0 in DB
		$inputTraitOrder = ($inputTraitOrder0-1).($inputTraitOrder1-1).($inputTraitOrder2-1);
	}
	
	// finally - create the problem into DB!
	if ((new UserError()) -> hasError() > ||
		$user->createProblem($inputProblemDesc,$inputProblemName,$inputLinkdist_01,
							  $inputLinkdist_12,$inputTraitOrder,$inputEpistasis,
							  $traitNameArray,$arrPhenotypes,$inputProgenyPerMating,
							  $inputMaxProgeny)!=true)
	{
		(new UserError) -> addError(761);	
	}
	else
	{
		$showProblemSummary = true;						
	}	
}
else
{
	$showProblemForm = true;
}



// write page header, including toolbar
$page->writeHeader();

?>

<?php echo "Selected Student: " . $_POST['studentId']; ?>
<?php $page->writeSectionHeader('Problem Details:'); ?>

<?php

// DATA LOGIC

// some small helper functions
function generateProblemSummary($p_problemName,$p_problemDesc,$p_progenyPerMating,$p_maxProgeny,
								$p_gmuDist_12, $p_gmuDist_23,
								$p_trait1,$p_trait2,$p_trait3,$p_traitOrder,
								$p_trait1AA,$p_trait1Ab,$p_trait1bA,$p_trait1bb,
								$p_trait2AA,$p_trait2Ab,$p_trait2bA,$p_trait2bb,
								$p_trait3AA,$p_trait3Ab,$p_trait3bA,$p_trait3bb,
								$p_epistasisCode)
{

	// show the problem name
	$problemTitle = new Table(1);
	$problemTitle->writeHeaders("Problem Name");
	$problemTitle->writeRow(&$p_problemName);
	$problemTitle->flush();
	echo "<p>";
	
	// show the problem description
	$problemDesc = new Table(1);
	$problemDesc->writeHeaders("Problem Description");
	$problemDesc->writeRow(&$p_problemDesc);
	$problemDesc->flush();	
	echo "<p>";

	// show the problem's progenyPerMating and maxProgeny
	$problemLimit = new Table(2);
	$problemLimit->writeHeaders("Progeny Per Mating","Max Progeny");
	$problemLimit->writeRow(&$p_progenyPerMating,&$p_maxProgeny);
	$problemLimit->flush();	
	echo "<p>";

		
	// show the trait order
	echo "Trait Order:<br>";
	$traitOrderTable = new Table(3);
	$problemDesc->writeHeaders(&$p_trait1,&$p_trait2,&$p_trait3);
	$problemDesc->writeRow($p_traitOrder{0}+1,$p_traitOrder{1}+1,$p_traitOrder{2}+1);
	$problemDesc->flush();	
	echo "<p>";
			
	$problemDetails = new Table(6);
		
	$problemDetails->writeHeaders("Trait","Phenotype 1","Phenotype 2","Phenotype 3",
								  "Phenotype 4","Dominance");
		
	// process the first trait
	// check for dominance
	$dominantTrait = "none";
	if ($p_trait1AA == $p_trait1Ab)
	{
		$dominantTrait = $p_trait1AA;
	}
	
	$problemDetails->writeRow(&$p_trait1,&$p_trait1AA,&$p_trait1Ab,&$p_trait1bA,
							  &$p_trait1bb,&$dominantTrait);
	

	$problemDetails->writeSpanningRow("Linkage between ".$p_trait1." and ".$p_trait2.": ".$p_gmuDist_12);

	// process the second trait
	// check for dominance
	$dominantTrait = "none";
	if ($p_trait2AA == $p_trait2Ab)
	{
		$dominantTrait = $p_trait2AA;
	}
	
	$problemDetails->writeRow(&$p_trait2,&$p_trait2AA,&$p_trait2Ab,&$p_trait2bA,
							  &$p_trait2bb,&$dominantTrait);

	$problemDetails->writeSpanningRow("Linkage between ".$p_trait2." and ".$p_trait3.": ".$p_gmuDist_23);

	// process the third trait
	// check for dominance
	$dominantTrait = "none";
	if ($p_trait3AA == $p_trait3Ab)
	{
		$dominantTrait = $p_trait3AA;
	}
	
	$problemDetails->writeRow(&$p_trait3,&$p_trait3AA,&$p_trait3Ab,&$p_trait3bA,
							  &$p_trait3bb,&$dominantTrait);

	if (!empty($p_epistasisCode))
	{
		global $g_epistaticRatios;
		$problemDetails->writeSpanningRow("Epistasis Ratio: ". $g_epistaticRatios[$p_epistasisCode]);
	}
	else
	{				
		$problemDetails->writeSpanningRow("Epistasis Ratio: none");
	}
	$problemDetails->flush();

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

function generateTraitOrderSelectBox($p_name)
{
	$traitOrderSelectBox = "<select name=\"". $p_name . "\" onchange=\"ordering(this.form, this);\">\n";
	
	$traitOrderSelectBox = $traitOrderSelectBox. "<option value=\"-1\">Select an order";
	// iterate through each row, and get the information
	for ($i = 1; $i <= 3; $i++)
	{
		$option = "<option value=\"" . $i . "\">" . $i."\n";
		$traitOrderSelectBox = $traitOrderSelectBox.$option; 
	}
	
	$traitOrderSelectBox = $traitOrderSelectBox."</select>\n";	
	return $traitOrderSelectBox;
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
	global $g_epistaticRatios;
	$counter = count($g_epistaticRatios);
	$epistSelectBox = "<select name=\"". "epist" . "\" onchange=\"epi(this.form, this);\">\n";

	$epistSelectBox = $epistSelectBox . "<option value=\"-1\">Select Epistasis Ratio";
	// iterate through each row, and get the information
	for ($i = 0; $i < $counter; $i++)
	{
		$option = "<option value=\"" . $i . "\">" . $g_epistaticRatios[$i]."\n";
		$epistSelectBox = $epistSelectBox.$option; 
	}
	$epistSelectBox = $epistSelectBox."</select>\n";	
	return $epistSelectBox;
}

function generatePhenoSelectBox($p_name)//, $p_phenoIdArray, $p_phenoNameArray)
{	
	$counter = count($p_phenoIdArray);
	$currRow;
	
	$phenoSelectBox = "<select name=\"". $p_name . "\">\n";
	$phenoSelectBox .= "<option>--------------</option>";//set initial size of the option tag
	/*
	// iterate through each row, and get the information
	for ($i = 0; $i < $counter; $i++)
	{
		$option = "<option value=\"" . $p_phenoNameArray[$i] . "\">" . $p_phenoNameArray[$i]."\n";
		$phenoSelectBox = $phenoSelectBox.$option; 
	}
	*/
	$phenoSelectBox .= "</select>\n";	
	return $phenoSelectBox;
} 

function generatePhenoColumn($p_colNumber)//, $p_phenoIdArray, $p_phenoNameArray)
{
	$output = "";
	$radio0 = "<input type=\"radio\" value=\"0\" name=\"radio".$p_colNumber."\">\n";
	$radio1 = "<input type=\"radio\" value=\"1\" name=\"radio".$p_colNumber."\">\n";	
	$radio2 = "<input type=\"radio\" value=\"2\" name=\"radio".$p_colNumber."\">\n";	
	
	$pheno0SelectBox = generatePhenoSelectBox("pheno".$p_colNumber."0");//,$p_phenoIdArray,$p_phenoNameArray);
	$pheno1SelectBox = generatePhenoSelectBox("pheno".$p_colNumber."1");//,$p_phenoIdArray,$p_phenoNameArray);
	$pheno2SelectBox = generatePhenoSelectBox("pheno".$p_colNumber."2");//,$p_phenoIdArray,$p_phenoNameArray);
	
	$output = $output.$radio0.$pheno0SelectBox."<br>\n";
	$output = $output.$radio1.$pheno1SelectBox."<br>\n";	
	$output = $output.$radio2."Co-dominant"."<br>".$pheno2SelectBox."<br>\n";	
	
	return $output;
}	

function generateEpistColumn($p_epistasisValue,$p_colNumber,$p_phenoIdArray, $p_phenoNameArray)
{
	global $g_epistaticRatios;
	$output = "";
	$epistRatios = explode(":", $g_epistaticRatios[$p_epistasisValue]);	
	
	$counter = count($epistRatios);	
	for ($i = 0; $i < $counter; $i++)
	{
		$ratioValue = $epistRatios[$i];		
		$ratioTextBox = "<input type=\"text\" name=\"T" . $i . "\" size=\"2\" value=\"". $ratioValue . "\" readonly>\n";
		$phenoSelectBox = generatePhenoSelectBox("pheno".$p_colNumber.$i,$p_phenoIdArray,$p_phenoNameArray);
		$output = $output.$ratioTextBox.$phenoSelectBox."<br>\n";
	}
	return $output;
}

function determineDominance($p_radioValue,$p_traitNumber,$p_arrPhenotypes)
{
	$AATrait;
	$bbTrait;
	$mixedTrait;
	
	if ($p_radioValue == 0)
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
			$p_arrPhenotypes[$p_traitNumber] = array($AATrait,$bbTrait);
		}
	}
	else if ($p_radioValue == 1)
	{
		$AATrait = $_POST['pheno'.$p_traitNumber.'1'];
		$bbTrait = $_POST['pheno'.$p_traitNumber.'0'];
		if (empty($AATrait) || empty($bbTrait))
		{
			(new UserError) -> addError(756);
			return false;
		}
		else
		{		
			$p_arrPhenotypes[$p_traitNumber] = array($AATrait,$bbTrait);		
		}
	}
	else
	{
		$AATrait = $_POST['pheno'.$p_traitNumber.'0'];
		$bbTrait = $_POST['pheno'.$p_traitNumber.'1'];
		$mixedTrait = $_POST['pheno'.$p_traitNumber.'2'];
		if (empty($AATrait) || empty($bbTrait) || empty($mixedTrait))
		{
			(new UserError) -> addError(757);
			return false;
		}
		else
		{		
			$p_arrPhenotypes[$p_traitNumber] = array($AATrait,$bbTrait,$mixedTrait);		
		}
	}	
	return true;
}

function determineEpistasis($p_epistasisValue, $p_traitNumber, $p_arrPhenotypes)
{
	global $g_epistaticRatios;
	$epistRatios = explode(":", $g_epistaticRatios[$p_epistasisValue]);	
	$counter = count($epistRatios);
	$epistTraits = array();
	
	for ($i = 0; $i < $counter; $i++)
	{
		if (empty($_POST['pheno3'.$i]))
		{
			(new UserError) -> addError(760);
			return false;
		}
		else
		{
			$epistTraits[$i] = $_POST['pheno3'.$i];	
		}
	}
	$p_arrPhenotypes[$p_traitNumber] = $epistTraits;
	return true;
}

function javascriptSetup($traitIdArray, $traitNameArray){
	global $g_db, $user;
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


?>

<NOSCRIPT>
This page requires Javascript!!
</NOSCRIPT>

<script language="JavaScript">
//phenotype dataset
<?php
// this is now loaded at the the bottom instead
//javascriptSetup(&$traitIdArray, &$traitNameArray);
?>

function onloadSettings(){
	var sel = 'document.createproblem.traitorder';
	for (var i=0;i<3;i++)
	eval(sel+i+'.selectedIndex='+(i+1)+';');
}

function clearOptions(sel)
{
	while (sel.selectedIndex>=0)sel.options[sel.selectedIndex]=null;
}

function trait(col)
{
	var phenSelect = new Array(3);
	var traitVar = eval('document.createproblem.trait'+col);
	traitVar = traitVar.options[traitVar.selectedIndex].value;
	var goOn = true;
	var i;
	for (i = 0;i<3;i++)
	{
		phenSelect[i] = eval('document.createproblem.pheno'+col+i);
		clearOptions(phenSelect[i]);
	}
	if (eval('document.createproblem.trait'+col+'.selectedIndex > 0')){
		i=0;
		while (goOn)
			(tdb[i][0]==traitVar)?goOn=false:i++;
		for (var j=0;j<tdb[i][1].length;j++)
		{
			phenSelect[0].options[j] = new Option(tdb[i][1][j][1], tdb[i][1][j][1]);
			phenSelect[1].options[j] = new Option(tdb[i][1][j][1], tdb[i][1][j][1]);
			phenSelect[2].options[j] = new Option(tdb[i][1][j][1], tdb[i][1][j][1]);
		}
		if (col==2)
			epi(document.createproblem, document.createproblem.epist);
	}
}

function swapOrder(orderBox){
	var value;
	var sel = 'document.createproblem.traitorder';
	var o = new Array(3);
	var pos;
	for (var i=0;i<3;i++)o[i]=false;
	for (var i=0;i<3;i++)o[eval(sel+i+'.selectedIndex')-1] = true;
	for (i=0;i<3;i++)if (!o[i])value = i+1;
	for (i=0;i<3;i++)if (eval(sel+(i%3)+'.selectedIndex=='+sel+((i+1)%3)+'.selectedIndex'))pos = i;
	(eval(sel+(pos%3))==orderBox)?eval(sel+((pos+1)%3)+'.selectedIndex = '+value):eval(sel+(pos%3)+'.selectedIndex = '+value);
}

function ordering(form, orderBox){
	if (orderBox.selectedIndex!=0){
		var a = new Array(3);
		var o = new Array(3);
		var i = 0;var j = 0;var k = 0;
		a[0] = form.traitorder0.selectedIndex;
		a[1] = form.traitorder1.selectedIndex;
		a[2] = form.traitorder2.selectedIndex;
		for(i = 0; i < 3; i++)o[i]=true;
		for (i=0;i<3;i++)if (a[i]!=0)(!o[a[i]-1])?swapOrder(orderBox):o[a[i]-1]=false;
		for (i=0;i<3;i++)(!o[i])?j++:k=i;
		if (j==2){
			if (a[0]==0)form.traitorder0.selectedIndex = k+1;
			if (a[1]==0)form.traitorder1.selectedIndex = k+1;
			if (a[2]==0)form.traitorder2.selectedIndex = k+1;
		}
	}
}

function epi(form, sel){
	var epistList = sel.options[sel.selectedIndex].text;
	var phenSelect = new Array(3);
	var i = 0;
	for (;i<4;i++){
		phenSelect[i] = eval('document.createproblem.pheno3'+i);
		clearOptions(phenSelect[i]);
	}
	for (i=0;i<4;i++)
		eval('document.createproblem.T'+i+'.value = \'\';');
	if (epistList.indexOf(':') > 0)
		for (var j=0,i = 0; i < epistList.length; i++)
			(epistList.charAt(i) != ':')?eval('document.createproblem.T'+j+'.value += \''+epistList.charAt(i)+'\';'):j++;
	if (form.trait2.selectedIndex==0)
		alert('Please select your trait in the third trait column before proceed Epistasis!');
	else
		for (var k = 0;k<=j;k++){
			i=0;
			while(form.pheno20.options[i]!=null){
				eval('document.createproblem.pheno3'+k+'.options['+i+'] = new Option(\''+form.pheno20.options[i].value+'\', \''+form.pheno20.options[i].text+'\');');
				i++;
			}
		}
}

function checkPhenos(){
	var sel = 'document.createproblem.pheno';
	var check;
	(document.createproblem.epist.selectedIndex==0)?check=3:check=2;
	for (var i = 0; i < check; i++){
		if (eval(sel+'00.options[0]!=null&&'+sel+'10.options[0]!=null&&'+sel+'20.options[0]!=null'))
			if (!eval('document.createproblem.radio'+i+'[2].checked')){
				if (eval(sel+i+'0.options['+sel+i+'0.selectedIndex].value == '+sel+i+'1.options['+sel+i+'1.selectedIndex].value'))
					return false;
			}else{
				if (eval(sel+i+'0.options['+sel+i+'0.selectedIndex].value == '+sel+i+'1.options['+sel+i+'1.selectedIndex].value || '+sel+i+'2.options['+sel+i+'2.selectedIndex].value == '+sel+i+'1.options['+sel+i+'1.selectedIndex].value || '+sel+i+'0.options['+sel+i+'0.selectedIndex].value == '+sel+i+'2.options['+sel+i+'2.selectedIndex].value'))
					return false;
			}
	}
	return true;
}

function checkTraits(){
	var sel = 'document.createproblem.trait';
	return !eval(sel+'0.options['+sel+'0.selectedIndex].value == '+sel+'1.options['+sel+'1.selectedIndex].value || '+sel+'2.options['+sel+'2.selectedIndex].value == '+sel+'1.options['+sel+'1.selectedIndex].value || '+sel+'0.options['+sel+'0.selectedIndex].value == '+sel+'2.options['+sel+'2.selectedIndex].value || '+sel+'0.selectedIndex == 0 || '+sel+'1.selectedIndex == 0 || '+sel+'2.selectedIndex == 0');
}

function checkEpist(){
	var form = 'document.createproblem';
	var i;
	var e = new Array(4);
	for (i=0;i<4;i++)e[i]=false;i=0;
	while (i<4&&eval(form+'.T'+i+'.value!=\'\'')){
		if (eval(form+'.pheno3'+i+'.selectedIndex')>=0)
			e[eval(form+'.pheno3'+i+'.selectedIndex')]=true;
		i++;
	}
	var k=0;
	for (var j=0;j<4;j++)
		if (e[j])k++;
	return k==i;
}

function createProblemCheck(form){
/*
	var sel = 'document.createproblem.traitorder';
	var error = '';
	var o = new Array(3);
	for (var i=0;i<3;i++)o[i]=false;
	if (form.problemname.value=='')
		error += '  *Problem Name required.\n';
	if (form.progpermating.value=='')
		error += '  *Problem Per Mating value required.\n';
	else
		if (form.progpermating.value*1!=form.progpermating.value)
			error += '  *Problem Per Mating value must be in numerical.\n';
	if (form.totalprogeny.value=='')
		error += '  *Total Progeny value required.\n';
	else
		if (form.totalprogeny.value*1!=form.totalprogeny.value)
			error += '  *Total Progeny value must be in numerical.\n';
	if (eval(sel+'0.selectedIndex==0||'+sel+'1.selectedIndex==0||'+sel+'2.selectedIndex==0'))
		error += '  *Trait Ordering required.\n';
	else
	{
		for (i=0;i<3;i++)
		o[eval(sel+i+'.selectedIndex')-1] = true;
		if (!o[0]||!o[1]||!o[2])
			error += '  *Trait Ordering cannot be the same.\n';
	}
	if (!form.radio0[0].checked&&!form.radio0[1].checked&&!form.radio0[2].checked)
		error += '  *Dominance of the first Trait is required.\n';
	if (!form.radio1[0].checked&&!form.radio1[1].checked&&!form.radio1[2].checked)
		error += '  *Dominance of the second Trait is required.\n';
	if (!form.radio2[0].checked&&!form.radio2[1].checked&&!form.radio2[2].checked&&form.epist.selectedIndex==0)
		error += '  *Dominance of the third Trait is required.\n';
	if (!checkPhenos())
		error += '  *Each Trait must have different Phenotype selected.\n';
	if (!checkTraits())
		error += '  *All 3 Traits must be different and cannot be empty.\n';
	if (form.linkdist01.value==''||form.linkdist12.value=='')
		error += '  *Linkage Distance needed to be specified.\n';
	else
		if (form.linkdist01.value*1!=form.linkdist01.value || form.linkdist12.value*1!=form.linkdist12.value)
			error += '  *Linkage Distance needed to be in numerial value.\n';
		else
			if (form.linkdist01.value<0 || form.linkdist12.value<0)
				error += '  *Linkage Distance needed to be larger than 0.\n';
	if (form.epist.selectedIndex>0&&form.check12.checked)
		error += '  *Linkage should not be exist between the 3rd and the 2nd.\n';
	if (!checkEpist())
		error += '  *Epistasis cannot be the same.\n';
	if (error!='')
		alert('Please fix the following problem before Problem can be created:\n\n'+error);
	else
*/
		form.submit();
}
</script>
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
		loadTraitsFromRecordset($traitRecordset,&$traitIdArray,&$traitNameArray);		
		
		echo "<script language=\"JavaScript\">";
		javascriptSetup(&$traitIdArray, &$traitNameArray);
		echo "</script>\n";
	}

	echo "<form name=\"createproblem\" action=\"". $PHP_SELF ."\" method=\"post\">";
	echo("<input type=\"hidden\" name=\"formaction\" value=\"createproblem\">");
	
	echo "Problem Name: <input type=\"text\" name=\"problemname\" size=\"30\"><p>\n";
	echo "Problem Descripton: <br>\n";
	echo "<textarea rows=\"8\" name=\"problemdesc\" cols=\"30\"></textarea><p>\n";
	
	echo "Progeny Per Mating: ";
	echo "<input type=\"text\" name=\"progpermating\" size=\"5\" value=\"50\"><br>\n";
	echo "Total Progeny: ";
	echo "<input type=\"text\" name=\"totalprogeny\" size=\"5\" value=\"1000\"><br>\n";
	
	// generate the trait/epistasis drop down selection boxes
	$traitSelect0 = generateTraitSelectBox("trait0",$traitNameArray,$traitNameArray);
	$traitSelect1 = generateTraitSelectBox("trait1",$traitNameArray,$traitNameArray);
	$traitSelect2 = generateTraitSelectBox("trait2",$traitNameArray,$traitNameArray);
	$epistSelect3 = generateEpistSelectBox();
	
	
	// generate the trait order drop down selection boxes
	$traitOrder0 = generateTraitOrderSelectBox("traitorder0");
	$traitOrder1 = generateTraitOrderSelectBox("traitorder1");
	$traitOrder2 = generateTraitOrderSelectBox("traitorder2");
	
	// generate the first row
	$traitCol0 = $traitSelect0 . "<br>" . $traitOrder0;
	$traitCol1 = $traitSelect1 . "<br>" . $traitOrder1;
	$traitCol2 = $traitSelect2 . "<br>" . $traitOrder2;
	
	// generate the columns under the first row
	$phenoCol0 = generatePhenoColumn(0);//,$pheno0IdArray,$pheno0NameArray);
	$phenoCol1 = generatePhenoColumn(1);//,$pheno1IdArray,$pheno1NameArray);
	$phenoCol2 = generatePhenoColumn(2);//,$pheno2IdArray,$pheno2NameArray);
	$phenoCol3 = generateEpistColumn($epistasisCode,3,$pheno2IdArray,$pheno2NameArray);
	
	$linkage01 = "<input type=\"checkbox\" name=\"check01\" value=\"ON\">" . "<br>".
				 "<input type=\"text\" name=\"linkdist01\" size=\"5\" value=\"50\">\n";
	
	$linkage12 = "<input type=\"checkbox\" name=\"check12\" value=\"ON\">" . "<br>".
				 "<input type=\"text\" name=\"linkdist12\" size=\"5\" value=\"50\">\n";
	
	$paramTable = new Table(6);
	$paramTable->writeRow($traitCol0,"Linkage",$traitCol1,"Linkage",$traitCol2,$epistSelect3);
	$paramTable->writeRow($phenoCol0,$linkage01,$phenoCol1,$linkage12,$phenoCol2,$phenoCol3);
	$paramTable->flush();
	
	echo "<p>\n";
	echo "<input type=\"button\" value=\"Create\" onclick=\"createProblemCheck(this.form);\">";
	echo "</form>";
}
else if ($showProblemSummary == true)
{
	$arrFinalPhenotypes;
	$user->assignPhenotypeLogic($inputEpistasis,$arrPhenotypes,&$arrFinalPhenotypes);
	
	echo "Problem Summary:<p>";
	
	if ($inputEpistasis == -1)
	{
		$inputEpistasis = "";
	}
	
	generateProblemSummary($inputProblemName,$inputProblemDesc,$inputProgenyPerMating,$inputMaxProgeny,
						   $inputLinkdist_01,$inputLinkdist_12,
						   $traitNameArray[0],$traitNameArray[1],$traitNameArray[2],$inputTraitOrder,
						   $arrFinalPhenotypes[0][0],$arrFinalPhenotypes[0][1],$arrFinalPhenotypes[0][2],$arrFinalPhenotypes[0][3],
						   $arrFinalPhenotypes[1][0],$arrFinalPhenotypes[1][1],$arrFinalPhenotypes[1][2],$arrFinalPhenotypes[1][3],
						   $arrFinalPhenotypes[2][0],$arrFinalPhenotypes[2][1],$arrFinalPhenotypes[2][2],$arrFinalPhenotypes[2][3],
						   $inputEpistasis);	

}

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
