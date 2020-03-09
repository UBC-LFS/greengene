function openProblemDetail( strProblemId )
{
  xgene360_cu.setLocation( './viewproblem.php?ProblemId=' + strProblemId );
}

function openGenerationDetail( strProblemId, strGenerationId  )
{
  xgene360_cu.setLocation( './viewgeneration.php?ProblemId=' + strProblemId + '&GenerationId=' + strGenerationId );
}

function hightlightSelectedRow( objElement, bHightlight )
{
  if ( bHightlight )
  {
    objElement.className = 'highlight';
    xgene360_cu.useHandCursor( objElement );
  }
  
  else
  {
    objElement.className = '';
    xgene360_cu.useRegularCursor( objElement );
  }
}

function onunloadHandler()
{
  // IE only
  if ( xgene360_cu.IE )
  {
    var objProperties = [ 'data', 'onmouseover', 'onmouseout', 'onmousedown', 'onmouseup', 'ondblclick', 'onclick', 'onselectstart', 'oncontextmenu' ];
    
    var objElement = null;
    
    for ( var i = 0; i < document.all.length; ++i )
    {
      objElement = document.all[i];
      
      for ( var j = 0; j < objProperties.length; ++j )
      {
        objElement[objProperties[j]] = null;
      }
    }
  }
}

xgene360_cu.using( 'event' );

xgene360_cu.event.addDOMListener( window, "onunload", onunloadHandler );

function livePreview( strSource, strTarget, strAppend, strWhenEmpty )
{
  var objSource = xgene360_cu.Element( strSource );
  var objTarget = xgene360_cu.Element( strTarget );
  
  if ( xgene360_cu.stringLength( objSource.value ) == 0 )
  {
    objTarget.innerHTML = strWhenEmpty;
  }
  
  else
  {
    objTarget.innerHTML = strAppend + objSource.value;
  }
}

function enableGreenGeneCourseSelection( objSwitch, strElement )
{
  var objElement = xgene360_cu.Element( strElement );
  
  xgene360_cu.setDisabled( objElement, !objSwitch.checked );
}

function swapTraitMean()
{
  var objTraitAMean1 = xgene360_cu.Element( 'TraitAParent1Mean' );
  var objTraitAMean2 = xgene360_cu.Element( 'TraitAParent2Mean' );
  var objTraitBMean1 = xgene360_cu.Element( 'TraitBParent1Mean' );
  var objTraitBMean2 = xgene360_cu.Element( 'TraitBParent2Mean' );
  
  var fTraitAMean1 = parseFloat( objTraitAMean1.value );
  var fTraitAMean2 = parseFloat( objTraitAMean2.value );
  var fTraitBMean1 = parseFloat( objTraitBMean1.value );
  var fTraitBMean2 = parseFloat( objTraitBMean2.value );
  
  var bSwapped = false;
  
  if ( fTraitAMean1 > fTraitAMean2 )
  {
    // swap the value
    objTraitAMean1.value = fTraitAMean2;
    objTraitAMean2.value = fTraitAMean1;
    bSwapped = true;
  }
  
  if ( fTraitBMean1 > fTraitBMean2 )
  {
    objTraitBMean1.value = fTraitBMean2;
    objTraitBMean2.value = fTraitBMean1;
    bSwapped = true;
  }
  
  if ( bSwapped )
  {
    alert( 'The mean value has been swapped for automatic marking purposes' );
  }
  
  return true;
}
