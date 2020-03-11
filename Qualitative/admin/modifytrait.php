<?php
require_once('../includes/global.php');

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
$user = Security::getUser();

// DATABASE CONNECTION
$g_db = new DB();

$page = new Page($user, 'Modify Trait', 1);

// GET PARAMETERS
$traitId = isset($_GET['traitId']) ? $_GET['traitId'] : $_POST['traitId'];
$traitName = $user->getTraitName($traitId);

// delete phenotypes
if(isset($_POST['del_phenotype'])) {
	$delPhenotype = $_POST['del_phenotype'];
	if(count($delPhenotype) > 0)
	{
		for( $i = 0; $i < count($delPhenotype); $i++)
			$user->deletePhenotype($traitId, $delPhenotype[$i]);
	}
}

//check for previous page's added new phenotype
if(isset($_POST['new_phenotype']))
{
	$user->addPhenotype($traitId, $_POST['new_phenotype']);
}


$recordset = $user->getPhenotypes($traitId);

// write page header, including toolbar
$page->writeHeader();
$page->handleErrors();

$table = new Table(2, false, true);
$table->writeRow('Trait Name:', $traitName);
$table->flush();

$page->writeSectionHeader('Phenotypes');
if($g_db->getNumRows($recordset) > 0)
{
	// Start the form
	echo("<form action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=\"post\">");
	echo("<input type=\"hidden\" name=\"traitId\" value=\"$traitId\">");

	// table for phenotypes
	$phenotypeTable = new Table(2, true, true);
	$phenotypeTable->writeHeaders('Delete', 'Phenotype');

	while($row = $g_db->fetch($recordset))
	{
		$id = $row->PhenotypeId;
		$phenotypeTable->writeRow("<input type=\"checkbox\" name=\"del_phenotype[]\" value=\"$id\">", $row->Name);
	}
	$phenotypeTable->flush();
	echo('<p><input type="submit" value="Delete Selected"></p>');
	echo("</form>");
}

// Start the form
echo("<form action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=\"post\">");
echo("<input type=\"hidden\" name=\"traitId\" value=\"$traitId\">");

//Form for new phenotype.
$table = new Table(2, false, true);
$table->writeRow('New Phenotype:', "<input type=\"text\" name=\"new_phenotype\">");
$table->flush();

echo("<p><input type=\"submit\" value=\"Add Phenotype\"></p>");

// end the form
echo("</form>");

$page->writeFooter();
$g_db->disconnect();
?>
