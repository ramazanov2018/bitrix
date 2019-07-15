CREATE TABLE b_courses_list (
  ID int (18) not null auto_increment,
  CODE VARCHAR(100) NULL,
  NAME VARCHAR (100) NULL,
  DESCRIPTION TEXT NULL,
  PRICE int (18) NULL,
  SORT int(11) DEFAULT 100 NOT NULL,
  ACTIVE CHAR(1),
  DATE_START VARCHAR(100) NULL,
  DATE_END VARCHAR (100) NULL,
  PRIMARY KEY (ID)
);

CREATE TABLE b_courses_subscribe (
  ID int (18) not null auto_increment,
  USER_ID int (18) not null,
  COURSE_ID int (18) not null,
	DATE_CREATE datetime,
	PRIMARY KEY (ID)
);