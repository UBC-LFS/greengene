function openCourseDetail( strCourseId )
{
  xgene360_cu.setLocation( './viewcourse.php?CourseId=' + strCourseId );
}

function openProfessorDetail( strProfessorId )
{
  xgene360_cu.setLocation( './viewprofessor.php?ProfessorId=' + strProfessorId );
}

function openTADetail( strTAId )
{
  xgene360_cu.setLocation( './viewta.php?TAId=' + strTAId );
}

function openStudentDetail( strStudentId )
{
  xgene360_cu.setLocation( './viewstudent.php?StudentId=' + strStudentId );
}

function openSolutions( e, strProblemId )
{
  xgene360_cu.setLocation( './viewsolutions.php?ProblemId=' + strProblemId );
  xgene360_cu.stopPropagation( e );
}

function openProgress( e, strProblemId, strStudentId )
{
  xgene360_cu.setLocation( './viewprogress.php?ProblemId=' + strProblemId + '&StudentId=' + strStudentId );
  xgene360_cu.stopPropagation( e );
}
