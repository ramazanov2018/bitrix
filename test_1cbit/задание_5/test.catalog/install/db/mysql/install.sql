CREATE TABLE b_test_category_list (
  CATEGORY_ID INT (18) NOT NULL auto_increment PRIMARY KEY,
  CATEGORY_NAME VARCHAR (200) NOT NULL
);

INSERT INTO b_test_category_list (CATEGORY_ID,CATEGORY_NAME)
VALUES (1, 'Apple'), (2, 'Samsung');


CREATE TABLE b_test_product_list (
  PRODUCT_ID INT (18) NOT NULL auto_increment PRIMARY KEY,
  PRODUCT_NAME VARCHAR (200) NOT NULL,
  PRICE INT (18) NULL,
  CATEGORY_ID INT (18) NULL
);

INSERT INTO b_test_product_list (PRODUCT_NAME, PRICE, CATEGORY_ID)
VALUES ('Apple Watch', 45000, 1),
('iPhone XS Max', 89990,1 ),
('iPhone XS', 55000,1 ),
('iPhone XR', 40004,1 ),
('Galaxy S', 52000,2 ),
('Galaxy Note', 22000,2 ),
('Galaxy J', 40000,2 ),
('Galaxy A', 80000,2 );