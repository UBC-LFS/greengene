function validateProfessorSelection()
{
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'ManageProfessorsForm' );
  
  xgene360_cu.form.addRequiredCheckBox( 'ProfessorId[]', 'Please select at least one professor' );
  
  return xgene360_cu.form.validate();
}

function validateDeleteProfessor()
{
  if ( validateProfessorSelection() )
  {
    return confirm( 'Are you sure you want to delete these professors?' );
  }
  
  return false;
}

function validateDropProfessorFromCourses()
{
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'UpdateProfessorForm' );
  
  xgene360_cu.form.addRequiredCheckBox( 'CourseId[]', 'Please select at least one course' );
  
  if ( xgene360_cu.form.validate() )
  {
    return confirm( 'Are you sure you want to drop this professor from these courses?' );
  }
  
  return false;
}

function validateCreateProfessorForm()
{
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'ManageProfessorsForm' );
  xgene360_cu.form.addRequiredTextBox( 'CWL Username', 'Please enter the \'CWL Username\'', 20 );
  
  return xgene360_cu.form.validate();
}

function validateImportProfessor()
{
  var objFile = xgene360_cu.Element( 'ImportProfessorFile' );
  
  if ( xgene360_cu.stringLength( objFile.value ) == 0 )
  {
    alert( 'Please select the file to import' );
    return false;
  }
  
  return confirm( 'Are you sure you want to import the file?\n\nPlease make sure that the proper csv file is selected to continue' );
}

function validateUpdateProfessorForm()
{
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'UpdateProfessorForm' );

  xgene360_cu.form.addRequiredTextBox( 'ProfessorFirstName', 'Please enter the \'First Name\'', 20 );
  xgene360_cu.form.addRequiredTextBox( 'ProfessorLastName', 'Please enter the \'Last Name\'', 20 );
  
  return xgene360_cu.form.validate();
}

function resetCreateProfessorForm()
{
  return confirm( 'Clear the form?' );
}

function resetUpdateProfessorForm()
{
  return confirm( 'Reset the form to original values?' );
}

function validateAssignProfessorsToACourse()
{
  if ( !validateProfessorSelection() )
  {
    return false;
  }
  
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'ManageProfessorsForm' );
  
  xgene360_cu.form.addRequiredSelection( 'SelectedCourse', 'Please select a course' );
  
  return xgene360_cu.form.validate();
}

function displayCreateProfessor()
{
  xgene360_cu.setDisplay( 'CreateProfessorDiv', true );
  
  window.scrollTo( 0, document.body.scrollHeight );

  xgene360_cu.Element( 'FirstName' ).focus();
}

function displayImportProfessor()
{
  xgene360_cu.setDisplay( 'ImportProfessorDiv', true );
  
  window.scrollTo( 0, document.body.scrollHeight );

  xgene360_cu.Element( 'ImportProfessorFile' ).focus();
}
