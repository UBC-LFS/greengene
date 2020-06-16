function validateStudentSelection()
{
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'ManageStudentsForm' );
  
  xgene360_cu.form.addRequiredCheckBox( 'StudentId[]', 'Please select at least one student' );
  
  return xgene360_cu.form.validate();
}

function validateDeleteStudent()
{
  if ( validateStudentSelection() )
  {
    return confirm( 'Are you sure you want to delete these students?' );
  }
  
  return false;
}

function validateDropProblem()
{
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'UpdateStudentsForm' );
  
  xgene360_cu.form.addRequiredCheckBox( 'ProblemId[]', 'Please select at least one problem' );
  
  if ( xgene360_cu.form.validate() )
  {
    return confirm( 'Are you sure you want to drop the student from these problems?' );
  }
  
  return false;
}

function validateCreateStudentForm()
{
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'ManageStudentsForm' );
  
  xgene360_cu.form.addRequiredTextBox( 'FirstName', 'Please enter the \'First Name\'', 20 );
  xgene360_cu.form.addRequiredTextBox( 'LastName', 'Please enter the \'Last Name\'', 20 );
  xgene360_cu.form.addRegExTextBox( 'StudentNumber', 'Please enter number for \'Student Number\'', '[0-9]+', false );

  xgene360_cu.form.addCustomValidator( passwordValidatorCreate );
  
  return xgene360_cu.form.validate();
}

function passwordValidatorCreate()
{
  var objPassowrd = xgene360_cu.Element( 'Password' );
  var objConfirmPassword = xgene360_cu.Element( 'ConfirmPassword' );
 
  if ( objPassowrd.value != objConfirmPassword.value )
  {
    alert( 'Please make sure the password fields match.' );
    objPassowrd.focus();
    
    return false;
  }
  
  return true;
}

function validateAssignStudentsToACourseOrAProblem()
{
  if ( !validateStudentSelection() )
  {
    return false;
  }
  
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'ManageStudentsForm' );
  
  var objSelect = xgene360_cu.Element( 'SelectAssign' );
  var strSelect = objSelect.options[objSelect.selectedIndex].value;
  
  if ( strSelect == 'TdSelectCourse' )
  {
    xgene360_cu.form.addRequiredSelection( 'SelectedCourse', 'Please select a course' );
  }
  
  else if ( strSelect == 'TdSelectProblem' )
  {
    xgene360_cu.form.addRequiredSelection( 'SelectedProblem', 'Please select a problem' );
  }
  
  else
  {
    alert( 'Please select a course or a problem' );
    return false;
  }
    
  return xgene360_cu.form.validate();
}

function validateAssignStudentToACourseOrAProblem()
{
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'UpdateStudentsForm' );
  
  var objSelect = xgene360_cu.Element( 'SelectAssign' );
  var strSelect = objSelect.options[objSelect.selectedIndex].value;
  
  if ( strSelect == 'TdSelectCourse' )
  {
    xgene360_cu.form.addRequiredSelection( 'SelectedCourse', 'Please select a course' );
  }
  
  else if ( strSelect == 'TdSelectProblem' )
  {
    xgene360_cu.form.addRequiredSelection( 'SelectedProblem', 'Please select a problem' );
  }
  
  else
  {
    alert( 'Please select a course or a problem' );
    return false;
  }
    
  return xgene360_cu.form.validate();
}

function validateImportStudent()
{
  console.log("validate import student ");
  xgene360_cu.using('form');

  xgene360_cu.form.init('ManageStudentsForm');

  xgene360_cu.form.addRequiredTextBox('CourseSubjectCode', 'Please enter the \'Course Subject Code\'', 20);
  xgene360_cu.form.addRequiredTextBox('CourseNumber', 'Please enter the \'Course Subject Code\'', 20);
  xgene360_cu.form.addRequiredTextBox('CourseSection', 'Please enter the \'Course Subject Code\'', 20);
  xgene360_cu.form.addRequiredTextBox('Year', 'Please enter the \'Course Subject Code\'', 20);
  if ( !xgene360_cu.form.validate()) {
    return false;
  };
  return confirm( 'Are you sure you want to import the class list?\n\nPlease check the class details provided (\'Course Subject Code\', \'Course Number\', etc.).');
}

function validateUpdateStudentForm()
{
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'UpdateStudentsForm' );
  
  xgene360_cu.form.addRequiredTextBox( 'StudentFirstName', 'Please enter the \'First Name\'', 20 );
  xgene360_cu.form.addRequiredTextBox( 'StudentLastName', 'Please enter the \'Last Name\'', 20 );
  xgene360_cu.form.addRegExTextBox( 'StudentNumber', 'Please enter number for \'Student Number\'', '[0-9]+', false );

  xgene360_cu.form.addCustomValidator( passwordValidatorUpdate );
  
  return xgene360_cu.form.validate();
  
  return true;
}

function passwordValidatorUpdate()
{
  var objPassowrd = xgene360_cu.Element( 'StudentPassword' );
  var objConfirmPassword = xgene360_cu.Element( 'StudentPasswordConfirm' );
 
  if ( objPassowrd.value != objConfirmPassword.value )
  {
    alert( 'Please make sure the password fields match.' );
    objPassowrd.focus();
    
    return false;
  }
  
  return true;
}

function resetCreateStudentForm()
{
  return confirm( 'Clear the form?' );
}

function resetUpdateStudentForm()
{
  return confirm( 'Reset the form to original values?' );
}

function displayCreateStudent()
{
  xgene360_cu.setDisplay( 'CreateStudentDiv', true );
  
  window.scrollTo( 0, document.body.scrollHeight );

  xgene360_cu.Element( 'FirstName' ).focus();
}

function displayImportStudent()
{
  xgene360_cu.setDisplay( 'ImportStudentDiv', true );
  
  window.scrollTo( 0, document.body.scrollHeight );

}
