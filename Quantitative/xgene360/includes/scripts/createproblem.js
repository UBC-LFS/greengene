function validateCreateProblemForm()
{
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'CreateProblemForm' );
  
  xgene360_cu.form.addRequiredTextBox( 'ProblemName', 'Please enter the \'Problem Name\'', 250 );
  xgene360_cu.form.addRequiredTextBox( 'TraitAName', 'Please enter the \'Trait A\'', 250 );
  xgene360_cu.form.addRequiredTextBox( 'TraitBName', 'Please enter the \'Trait B\'', 250 );
  xgene360_cu.form.addRequiredTextBox( 'ProblemDescription', 'Please enter the \'Problem Description\'', 250 );
  
  xgene360_cu.form.addCustomValidator( dateValidator );
  
  xgene360_cu.form.addRegExTextBox( 'MaxCross', 'Please enter number for \'Maximum Number of Cross\'', '[0-9]+' );
  xgene360_cu.form.addRegExTextBox( 'OffspringPerCross', 'Please enter an integer for \'Offspring per Cross\'', '[0-9]+' );
  xgene360_cu.form.addRegExTextBox( 'PlantsDisplayed', 'Please enter an integer for \'Displayed Plants per Cross\'', '[0-9]+' );
  xgene360_cu.form.addRegExTextBox( 'RangeOfAcceptance', 'Please enter number for \'Range of Acceptance\'', '[0-9]+.?[0-9]*' );
  
  xgene360_cu.form.addRegExTextBox( 'TraitANumberOfGenes', 'Please enter number for \'TraitA Number of Genes\'', '[0-9]+' );
  xgene360_cu.form.addRegExTextBox( 'TraitAHeritability', 'Please enter number for \'TraitA Heritability\'', '[0-9]+.?[0-9]*' );
  xgene360_cu.form.addRegExTextBox( 'TraitAParent1Mean', 'Please enter number for \'TraitA Parent 1 Mean\'', '[0-9]+.?[0-9]*' );
  xgene360_cu.form.addRegExTextBox( 'TraitAParent2Mean', 'Please enter number for \'TraitA Parent 2 Mean\'', '[0-9]+.?[0-9]*' );
  xgene360_cu.form.addRegExTextBox( 'TraitAVariance', 'Please enter number for \'TraitA Variance\'', '[0-9]+.?[0-9]*' );
  xgene360_cu.form.addRegExTextBox( 'TraitABaseValue', 'Please enter number for \'TraitA Base Value\'', '[0-9]+.?[0-9]*' );
  xgene360_cu.form.addRequiredTextBox( 'TraitAUnit', 'Please enter the \'Unit\'', 250 );
  xgene360_cu.form.addRegExTextBox( 'HistogramRangeA', 'Please enter number for \'Histogram Range for Trait A\'', '[0-9]+.?[0-9]*' );
  
  xgene360_cu.form.addRegExTextBox( 'TraitBNumberOfGenes', 'Please enter number for \'TraitB Number of Genes\'', '[0-9]+' );
  xgene360_cu.form.addRegExTextBox( 'TraitBHeritability', 'Please enter number for \'TraitB Heritability\'', '[0-9]+.?[0-9]*' );
  xgene360_cu.form.addRegExTextBox( 'TraitBParent1Mean', 'Please enter number for \'TraitB Parent 1 Mean\'', '[0-9]+.?[0-9]*' );
  xgene360_cu.form.addRegExTextBox( 'TraitBParent2Mean', 'Please enter number for \'TraitB Parent 2 Mean\'', '[0-9]+.?[0-9]*' );
  xgene360_cu.form.addRegExTextBox( 'TraitBVariance', 'Please enter number for \'TraitB Variance\'', '[0-9]+.?[0-9]*' );
  xgene360_cu.form.addRegExTextBox( 'TraitBBaseValue', 'Please enter number for \'TraitB Base Value\'', '[0-9]+.?[0-9]*' );
  xgene360_cu.form.addRequiredTextBox( 'TraitBUnit', 'Please enter the \'Unit\'', 250 );  
  xgene360_cu.form.addRegExTextBox( 'HistogramRangeB', 'Please enter number for \'Histogram Range for Trait B\'', '[0-9]+.?[0-9]*' );
  
  xgene360_cu.form.addCustomValidator( numberOfGenesValidator );
  
  xgene360_cu.form.addCustomValidator( swapTraitMean );
  
  return xgene360_cu.form.validate();
}

function validateCopyProblem()
{
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'CreateProblemForm' );

  xgene360_cu.form.addRequiredSelection( 'CopyProblem', 'Please select a problem' );
  
  return xgene360_cu.form.validate();
}

function numberOfGenesValidator()
{
  var objItem = xgene360_cu.Element( 'TraitANumberOfGenes' );
  var iNumber = parseInt( objItem.value );
  
  if ( iNumber <= 0 || iNumber > 200 )
  {
    alert( 'Please enter a number between 1 and 200 for \'TraitA Number of Genes\'' );
    objItem.focus();
    
    return false;
  }
  
  objItem = xgene360_cu.Element( 'TraitBNumberOfGenes' );
  iNumber = parseInt( objItem.value );
  
  if ( iNumber <= 0 || iNumber > 200 )
  {
    alert( 'Please enter a number between 0 and 200 for \'TraitB Number of Genes\'' );
    objItem.focus();
    
    return false;
  }
  
  return true;
}

function dateValidator()
{
  xgene360_cu.using( 'dateExtension' );
  
  var iStartDateYear = parseInt( xgene360_cu.Element( 'StartDateYear' ).value );
  var iStartDateMonth = parseInt( xgene360_cu.Element( 'StartDateMonth' ).value ) - 1;
  var iStartDateDay = parseInt( xgene360_cu.Element( 'StartDateDay' ).value );
  var iStartDateHour = parseInt( xgene360_cu.Element( 'StartDateHour' ).value );
  var iStartDateMinute = parseInt( xgene360_cu.Element( 'StartDateMinute' ).value );
  
  var objStartDate = new Date(  iStartDateYear,
                                iStartDateMonth,
                                iStartDateDay,
                                iStartDateHour,
                                iStartDateMinute,
                                0 );

  if ( !objStartDate.validate( iStartDateYear, iStartDateMonth, iStartDateDay, iStartDateHour, iStartDateMinute, 0 ) )
  {
    alert( 'Please enter a validate \'Start Date\'' );
    
    return false;
  }
  
  var iDueDateYear = parseInt( xgene360_cu.Element( 'DueDateYear' ).value );
  var iDueDateMonth = parseInt( xgene360_cu.Element( 'DueDateMonth' ).value ) - 1;
  var iDueDateDay = parseInt( xgene360_cu.Element( 'DueDateDay' ).value );
  var iDueDateHour = parseInt( xgene360_cu.Element( 'DueDateHour' ).value );
  var iDueDateMinute = parseInt( xgene360_cu.Element( 'DueDateMinute' ).value );
  
  var objDueDate =  new Date( iDueDateYear,
                                iDueDateMonth,
                                iDueDateDay,
                                iDueDateHour,
                                iDueDateMinute, 
                                0 );
                                
  if ( !objDueDate.validate( iDueDateYear, iDueDateMonth, iDueDateDay, iDueDateHour, iDueDateMinute, 0 ) )
  {
    alert( 'Please enter a validate \'Due Date\'' );
    
    return false;
  }
  
  if ( objStartDate >= objDueDate )
  {
    alert( '\'Due Date\' must be later than \'Start Date\'' );
    
    return false;
  }
  
  xgene360_cu.Element( 'StartDate' ).value = objStartDate.toSQLFormat();
  xgene360_cu.Element( 'DueDate' ).value = objDueDate.toSQLFormat();
  
  return true;
}