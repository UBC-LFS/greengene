# Green Gene

Qualitative Tested with PHP 8.1.2
- login page working
    - needed to make UserError functions static (there might be better alternatives)
- getting MasterAdmin user works
    - need to change the name of the constructor to __construct
    - ^^ do this for other User subclasses
- Update mysql database default value for ModificationDate (old mysql does this automatically)
- ALTER TABLE StudentProblem MODIFY COLUMN ModificationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
- ALTER TABLE MasterProblem MODIFY COLUMN ModificationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP;