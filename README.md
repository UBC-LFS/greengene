# Green Gene
Last updated: Aug 16th, 2023

Qualitative - Tested with PHP 8.1.2<br>
Quantitative - Incomplete

For a full description of what this project does and for instructions on using it, check out the [user manual](/docs/greengene_user_manual.pdf)!

## Installation
```
git clone https://github.com/UBC-LFS/greengene.git
```

Follow the [Qualitative Set Up Guide](./Qualitative/INSTALL.md)

Note: This step can be skipped if a database already exist.
All you need to do is create a `config.php` file manually inside `Qualitative/includes` using the [config template](./Qualitative/includes/config.php-example) and add the secret variables.

## Updating the database (if a database previously existed) - Qualitative
If a database already exists from a previous version of greengene, the columns will need to be updated. Why?
Previous versions of mysql automatically created a timestamp for us when creating a new data for certain columns + mysql updated the timestamp for us automatically when the data is modified. Newer versions of mysql require us to specify that we want it to be automatic
```
CONNECT <database name>;
ALTER TABLE StudentProblem MODIFY COLUMN ModificationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP;
ALTER TABLE MasterProblem MODIFY COLUMN ModificationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP;
ALTER TABLE `Cross` MODIFY COLUMN CreationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
```

Note: we must use `` for Cross because it's a reserved SQL keyword

## Dev notes - Qualitative
Things not tested yet
- Importing students (requires UBC VPN + info may not be available yet?)

Possible flaws:
- Students cannot be in more than 1 course (will say user already exist when adding the student)
- TAs or Profs cannot be in more than 1 course (will say user already exist when adding the TA or Prof)

