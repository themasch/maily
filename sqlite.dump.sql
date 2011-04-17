PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;
CREATE TABLE `user` (
    `id` INTEGER NOT NULL  PRIMARY KEY, 
    `user` VARCHAR(80), 
    `pass` VARCHAR(40)
);
INSERT INTO "user" VALUES(1,'mustera','5f4dcc3b5aa765d61d8327deb882cf99');
INSERT INTO "user" VALUES(2,'musterb','5f4dcc3b5aa765d61d8327deb882cf99');
CREATE TABLE `recipient` (
    `id` INTEGER NOT NULL  PRIMARY KEY, 
    `user` INTEGER, 
    `address` VARCHAR(80)
);
INSERT INTO "recipient" VALUES(1,NULL,'user1@example.com');
INSERT INTO "recipient" VALUES(2,NULL,'user2@example.com');
INSERT INTO "recipient" VALUES(3,1,'user3@example.com');
INSERT INTO "recipient" VALUES(4,2,'user4@example.com');
INSERT INTO "recipient" VALUES(5,1,'user5@example.com');
INSERT INTO "recipient" VALUES(6,2,'user6@example.com');
CREATE TABLE `target` ( 
    `id` INTEGER NOT NULL  PRIMARY KEY, 
    `recipient` INTEGER NOT NULL, 
    `list` INTEGER NOT NULL, 
    `allow_send` TEXT DEFAULT 'n', 
    `active` TEXT DEFAULT 'y', 
    `user_level` INT
);
INSERT INTO "target" VALUES(1,1,1,'y','y',3);
INSERT INTO "target" VALUES(2,2,2,'y','y',3);
INSERT INTO "target" VALUES(3,3,1,'n','y',2);
INSERT INTO "target" VALUES(4,4,2,'n','y',2);
INSERT INTO "target" VALUES(5,5,1,'y','n',1);
INSERT INTO "target" VALUES(6,6,2,'y','n',1);
CREATE TABLE `list` ( 
    `id` INTEGER NOT NULL  PRIMARY KEY, 
    `address` VARCHAR(80), 
    `name` VARCHAR(80)
);
INSERT INTO "list" VALUES(1,'list@example.com','Beispielliste Nr. 1');
INSERT INTO "list" VALUES(2,'list2@example.com','Beispielliste Nr. 2');
CREATE TABLE `list_config` ( 
    `id` INTEGER NOT NULL   PRIMARY KEY, 
    `list` INTEGER, 
    `name` VARCHAR(80), 
    `value` TEXT
);
COMMIT;
