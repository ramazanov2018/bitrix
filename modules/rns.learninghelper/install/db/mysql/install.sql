CREATE TABLE if not exists b_learn_h_comparison_answer
(
	ID int(11) unsigned not null auto_increment,
	QUESTION_ID int(11) unsigned not null REFERENCES b_learn_question(ID),
	SORT int(11) not null default '10',
	ANSWER text null,
	QUESTION text null,
	PRIMARY KEY(ID),
	INDEX IX_B_LEARN_ANSWER1(QUESTION_ID)
);


CREATE TABLE if not exists b_learn_status
(
	ID int(11) unsigned not null auto_increment,
	LESSON_ID int(11) unsigned not null REFERENCES b_learn_lesson(ID),
	USER_ID int(11) unsigned not null REFERENCES b_user(ID),
    STATUS char(1) not null default 'Y',
    PRIMARY KEY(ID)
);