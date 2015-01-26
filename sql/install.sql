CREATE TABLE  IF NOT EXISTS  `prefix_ulogin` (
  `ulogin_id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT ,
  `ulogin_identity` TEXT  CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
  `ulogin_userid` INTEGER UNSIGNED NOT NULL,
  `ulogin_network` varchar(50) DEFAULT NULL,
  CONSTRAINT `ulogin_constraint` FOREIGN KEY `ulogin_constraint` (`ulogin_userid`)
    REFERENCES `prefix_user` (`user_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  PRIMARY KEY(`ulogin_id`),
  UNIQUE(`ulogin_identity`(255))
)ENGINE = InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE  IF NOT EXISTS  `prefix_ulogin_settings` (
  `ulogin_id` varchar(50),
  `ulogin_value` varchar(8)
)ENGINE = InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `prefix_ulogin_settings` (`ulogin_id`, `ulogin_value`) VALUES
('uloginid1', ''),
('uloginid2', ''),
('uloginid_profile', '');