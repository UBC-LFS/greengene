function validateUpdatePasswordForm()
{
  xgene360_cu.using( 'form' );
  
  xgene360_cu.form.init( 'UpdatePasswordForm' );
  
  xgene360_cu.form.addRequiredTextBox( 'OldPassword', 'Please enter the \'Old Password\'', 50 );
  xgene360_cu.form.addRequiredTextBox( 'NewPassword', 'Please enter the \'New Password\'', 50 );
  xgene360_cu.form.addRequiredTextBox( 'ConfirmNewPassword', 'Please enter the \'Confirm New Password\'', 50 );
  
  xgene360_cu.form.addCustomValidator( passwordValidatorCreate );
  
  return xgene360_cu.form.validate();
}

function passwordValidatorCreate()
{
  var objPassword = xgene360_cu.Element( 'NewPassword' );
  var objConfirmPassword = xgene360_cu.Element( 'ConfirmNewPassword' );
 
  if ( objPassword.value != objConfirmPassword.value )
  {
    alert( 'Please make sure the password fields match.' );
    objPassword.focus();
    
    return false;
  }
  
  return true;
}
