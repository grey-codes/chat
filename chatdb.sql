CREATE TABLE IF NOT EXISTS users (
  user_id int NOT NULL AUTO_INCREMENT,
  user_name varchar(20) NOT NULL,
  pass_hash char(128) NOT NULL,/*sha512*/
  PRIMARY KEY (user_id)
) ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS channels (
  channel_id int NOT NULL AUTO_INCREMENT,
  name varchar(20) NOT NULL,
  owner_id int NOT NULL,
  unixperm smallint NOT NULL,
  PRIMARY KEY (channel_id),
  FOREIGN KEY (owner_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS messages (
  msg_id int NOT NULL AUTO_INCREMENT,
  channel_id int NOT NULL,
  owner_id int NOT NULL,
  value TEXT,
  PRIMARY KEY (msg_id),
  FOREIGN KEY (channel_id) REFERENCES channels(channel_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (owner_id) REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS roles (
  role_id int NOT NULL AUTO_INCREMENT,
  channel_id int NOT NULL,
  role_name VARCHAR(50),
  permission_json TEXT,
  privilege int NOT NULL,
  PRIMARY KEY (role_id, channel_id),
  FOREIGN KEY (channel_id) REFERENCES channels(channel_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=INNODB;

CREATE TABLE IF NOT EXISTS user_roles (
  user_id int NOT NULL,
  channel_id int NOT NULL,
  role_id int NOT NULL,
  PRIMARY KEY (user_id, channel_id),
  FOREIGN KEY (channel_id) REFERENCES channels(channel_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=INNODB;