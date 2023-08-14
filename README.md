# Green Gene

Qualitative Tested with PHP 8.1.2 (dev notes)
- getting MasterAdmin user works
    - need to change the name of the constructor to __construct
    - ^^ do this for other User subclasses (doing this caused some bugs, undid for now)
- Update mysql database default value for ModificationDate (old mysql does this automatically)
- ALTER TABLE StudentProblem MODIFY COLUMN ModificationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP;
- ALTER TABLE MasterProblem MODIFY COLUMN ModificationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP;
- ALTER TABLE `Cross` MODIFY COLUMN CreationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
- ^^^ must use `` for Cross because it's a reserved SQL keyword

Changes still need to be made:
- Other possible syntax errors?

Things we can't test OR may need to tempt hardcode stuff to test:
- Logging in with other admin privilleges without hard coding it (Admin,TA,Student)
- Importing students (requires UBC VPN)
- Student page
    - will need another CWL for it (or can hard code?)
    - current CWL is the admin, can't create a student with the same CWL and assign a problem to it
- View progress page does not display student info and header
    - due to user privilleges?


Known flaws:
- Students cannot be in more than 1 course (will say user already exist when adding a student)
- TAs or Profs cannot be in more than 1 course (will say user already exist when adding a TA or Prof)

