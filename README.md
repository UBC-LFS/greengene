# Green Gene

Qualitative Tested with PHP 8.1.2 (dev notes)

- Update mysql database default value for ModificationDate & CreationDate column (old mysql versions updated the timestamp for us automatically, now we need to specify that we want it to be automatic)
- ALTER TABLE StudentProblem MODIFY COLUMN ModificationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP;
- ALTER TABLE MasterProblem MODIFY COLUMN ModificationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP;
- ALTER TABLE `Cross` MODIFY COLUMN CreationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
    - must use `` for Cross because it's a reserved SQL keyword


Things not tested yet
- Importing students (requires UBC VPN + info may not be available yet?)

Known flaws:
- Students cannot be in more than 1 course (will say user already exist when adding a student)
- TAs or Profs cannot be in more than 1 course (will say user already exist when adding a TA or Prof)

