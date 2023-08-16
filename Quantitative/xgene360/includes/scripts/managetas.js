function validateTASelection()
{
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'ManageTAsForm' );
  
  xgene360_cu.form.addRequiredCheckBox( 'TAId[]', 'Please select at least one TA' );
  
  return xgene360_cu.form.validate();
}

function validateDeleteTA()
{
  if ( validateTASelection() )
  {
    return confirm( 'Are you sure you want to delete these TAs?' );
  }
  
  return false;
}

function validateCreateTAForm()
{
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'ManageTAForm' );
  xgene360_cu.form.addRequiredTextBox( 'CWL Username', 'Please enter the \'CWL Username\'', 20 );
  
  return xgene360_cu.form.validate();
}

function validateImportTA()
{
  var objFile = xgene360_cu.Element( 'ImportTAFile' );
  
  if ( xgene360_cu.stringLength( objFile.value ) == 0 )
  {
    alert( 'Please select the file to import' );
    return false;
  }
  
  return confirm( 'Are you sure you want to import the file?\n\nPlease make sure that the proper csv file is selected to continue' );
}

function validateUpdateTAForm()
{
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'UpdateProfessorForm' );
  
  xgene360_cu.form.addRequiredTextBox( 'TAFirstName', 'Please enter the \'First Name\'', 20 );
  xgene360_cu.form.addRequiredTextBox( 'TALastName', 'Please enter the \'Last Name\'', 20 );
  
    
  return xgene360_cu.form.validate();
}

function resetCreateTAForm()
{
  return confirm( 'Clear the form?' );
}

function resetUpdateTAForm()
{
  return confirm( 'Reset the form to original values?' );
}

function validateAssignTAsToACourse()
{
  if ( !validateTASelection() )
  {
    return false;
  }
  
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'ManageTAsForm' );
  
  xgene360_cu.form.addRequiredSelection( 'SelectedCourse', 'Please select a course' );
  
  return xgene360_cu.form.validate();
}

function displayCreateTA()
{
  xgene360_cu.setDisplay( 'CreateTADiv', true );
  
  window.scrollTo( 0, document.body.scrollHeight );

  xgene360_cu.Element( 'FirstName' ).focus();
}

function displayImportTA()
{
  xgene360_cu.setDisplay( 'ImportTADiv', true );
  
  window.scrollTo( 0, document.body.scrollHeight );

  xgene360_cu.Element( 'ImportTAFile' ).focus();
}

