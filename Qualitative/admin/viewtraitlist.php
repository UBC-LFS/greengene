<?php
require_once('../includes/global.php');

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
$user = Security::getUser();

// DATABASE CONNECTION
$g_db = new DB();

// PAGE CREATION LOGIC
$page = new Page($user, 'Manage Traits', 1);

// write page header, including toolbar
$page->writeHeader();

// ERROR FLAG
$failure = false;

// check for previous page's deleted problems
$delTrait = $_POST['del_trait'];
for( $i = 0; $i < count($delTrait); $i++)
	$user->deleteTrait($delTrait[$i]);

// retrieve the list of problems associated with the user's courseId
$recordset = $user->getTraits();

$page->handleErrors();

echo "<form action=\"$PHP_SELF\" method=\"post\">";

$traitTable = new Table(4, true, false);
$traitTable->writeHeaders('', 'Name', 'Phenotypes', '');

// iterate through each row, and get the information
while($row = $g_db->fetch($recordset))
{
	$traitId = $row->TraitId;
	$traitName = $row->Name;

	// get phenotypes for this trait
	$phenoSet = $user->getPhenotypes($traitId);

	$phenoArray = array();
	while($pheno_row = $g_db->fetch($phenoSet))
		array_push($phenoArray, $pheno_row->Name);
	$phenoList = implode(', ', $phenoArray);

	$traitTable->writeRow("<input type=\"checkbox\" name=\"del_trait[]\" value=\"$traitId\">",
		&$traitName,
		&$phenoList,
		"<input type=\"button\" value=\"Modify\" onClick=\"goUrl('modifytrait.php?traitId=$traitId');\">");
}
$traitTable->flush();

echo "<p><input type=\"submit\" value=\"Delete Selected\"> &nbsp;&nbsp;
<input type=\"button\" value=\"Create New Trait\" onClick=\"goUrl('createtrait.php');\"></p>";
echo "</form>";

$page->writeFooter();
$g_db->disconnect();
?>
