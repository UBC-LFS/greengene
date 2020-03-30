INSERT INTO `Course`(`CourseId`, `Name`, `Description`) VALUES (1, 'test','used for running test');

INSERT INTO `User`(`UserId`, `CourseId`, `PrivilegeLvl`, `Pwd`) VALUES ('testProf', 1, 1, '123');
INSERT INTO `User`(`UserId`, `CourseId`, `PrivilegeLvl`, `Pwd`) VALUES ('testTA', 1, 2, '123');
INSERT INTO `User`(`UserId`, `CourseId`, `PrivilegeLvl`, `Pwd`) VALUES ('testSA', 1, 3, '123');
INSERT INTO `User`(`UserId`, `CourseId`, `PrivilegeLvl`, `Pwd`) VALUES ('testSB', 1, 3, '123');
INSERT INTO `User`(`UserId`, `CourseId`, `PrivilegeLvl`, `Pwd`) VALUES ('testSC', 1, 3, '123');
INSERT INTO `User`(`UserId`, `CourseId`, `PrivilegeLvl`, `Pwd`) VALUES ('testSD', 1, 3, '123');
INSERT INTO `User`(`UserId`, `CourseId`, `PrivilegeLvl`, `Pwd`) VALUES ('testSE', 1, 3, '123');
INSERT INTO `User`(`UserId`, `CourseId`, `PrivilegeLvl`, `Pwd`) VALUES ('testSF', 1, 3, '123');