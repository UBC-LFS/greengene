function validateSubmitSolutionForm()
{
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'SubmitSolutionForm' );
  
  xgene360_cu.form.addRegExTextBox( 'TraitAParent1Mean', 'Please enter number for \'TraitA Parent 1 Mean\'', '[0-9]+.?[0-9]*' );
  xgene360_cu.form.addRegExTextBox( 'TraitAParent2Mean', 'Please enter number for \'TraitA Parent 2 Mean\'', '[0-9]+.?[0-9]*' );
  xgene360_cu.form.addRegExTextBox( 'TraitAVariance', 'Please enter number for \'TraitA Variance\'', '[0-9]+.?[0-9]*' );
  xgene360_cu.form.addRegExTextBox( 'TraitAHeritability', 'Please enter number for \'TraitA Heritability\'', '[0-9]+.?[0-9]*' );
  xgene360_cu.form.addRegExTextBox( 'TraitANumberOfGenes', 'Please enter number for \'TraitA Number of Genes\'', '[0-9]+' );
  
  xgene360_cu.form.addRegExTextBox( 'TraitBParent1Mean', 'Please enter number for \'TraitB Parent 1 Mean\'', '[0-9]+.?[0-9]*' );
  xgene360_cu.form.addRegExTextBox( 'TraitBParent2Mean', 'Please enter number for \'TraitB Parent 2 Mean\'', '[0-9]+.?[0-9]*' );
  xgene360_cu.form.addRegExTextBox( 'TraitBHeritability', 'Please enter number for \'TraitB Heritability\'', '[0-9]+.?[0-9]*' );
  xgene360_cu.form.addRegExTextBox( 'TraitBVariance', 'Please enter number for \'TraitB Variance\'', '[0-9]+.?[0-9]*' );
  xgene360_cu.form.addRegExTextBox( 'TraitBNumberOfGenes', 'Please enter number for \'TraitB Number of Genes\'', '[0-9]+' );
  
  xgene360_cu.form.addCustomValidator( swapTraitMean );
  
  xgene360_cu.form.addCustomValidator( confirmationValidator );
  
  return xgene360_cu.form.validate();
}

function confirmationValidator()
{
  return confirm( 'Are you sure you want to submit your solution?' );
}

function displaySubmitSolution()
{
  xgene360_cu.setDisplay( 'SubmitSolutionDiv', true );
  
  window.scrollTo( 0, document.body.scrollHeight );

  var objElement = xgene360_cu.Element( 'TraitAParent1Mean' );
  
  if ( objElement != null )
  {
    objElement.focus();
  }
}