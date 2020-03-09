function validateDeleteCourse()
{
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'ManageCourseForm' );
  
  xgene360_cu.form.addRequiredCheckBox( 'CourseId[]', 'Please select at least one course' );
  
  if ( xgene360_cu.form.validate() )
  {
    return confirm( 'Are you sure you want to delete these courses?' );
  }
  
  return false;
}

function validateDropProfessor()
{
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'ManageCourseForm' );
  
  xgene360_cu.form.addRequiredCheckBox( 'ProfessorId[]', 'Please select at least one professor' );
  
  if ( xgene360_cu.form.validate() )
  {
    return confirm( 'Are you sure you want to drop these professors?' );
  }
  
  return false;
}

function validateDropTA()
{
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'ManageCourseForm' );
  
  xgene360_cu.form.addRequiredCheckBox( 'TAId[]', 'Please select at least one professor' );
  
  if ( xgene360_cu.form.validate() )
  {
    return confirm( 'Are you sure you want to drop these TAs?' );
  }
  
  return false;
}


function validateCreateCourse()
{
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'ManageCourseForm' );
  
  xgene360_cu.form.addRequiredTextBox( 'CourseName', 'Please enter the \'Course Name\'', 30 );
  xgene360_cu.form.addRequiredTextBox( 'CourseDescription', 'Please enter the \'Course Description\'', 250 );
  
  return xgene360_cu.form.validate();
}

function confirmResetCreateCourse()
{
  return confirm( 'Clear the form?' );
}

function confimResetUpdateCourse()
{
  return confirm( 'Reset the form to its original values?' );
}

function displayCreateCourse()
{
  xgene360_cu.setDisplay( 'CreateCourseDiv', true );
  
  window.scrollTo( 0, document.body.scrollHeight );

  xgene360_cu.Element( 'CourseName' ).focus();
}
