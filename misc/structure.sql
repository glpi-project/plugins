CREATE TABLE plugin (
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   active INT(1),
   xml_url TEXT,
   xml_crc VARCHAR(32),
   name TEXT,
   logo_url TEXT,
   `key` VARCHAR(255),
   homepage_url TEXT,
   download_url TEXT,
   issues_url TEXT,
   readme_url TEXT,
   license VARCHAR(255),
   date_added DATE,
   date_updated DATE,
   download_count INT,
   UNIQUE KEY `ix_plugin` (`key`)
) ENGINE=InnoDB;

CREATE TABLE author(
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   name TEXT
) ENGINE=InnoDB;

CREATE TABLE user(
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   email VARCHAR(255),
   username VARCHAR(28),
   password VARCHAR(60),
   realname TEXT,
   location VARCHAR(80),
   website VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE user_external_account(
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   user_id INT NOT NULL,
   token VARCHAR(60),
   service VARCHAR(40),
   FOREIGN KEY (user_id)
      REFERENCES user(id)
      ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE INDEX idx_user_external_acount_user_id ON user_external_account(user_id);

CREATE TABLE plugin_download (
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   plugin_id INT NOT NULL,
   downloaded_at DATETIME,
   FOREIGN KEY (plugin_id)
      REFERENCES plugin(id)
) ENGINE=InnoDB;
CREATE INDEX idx_plugin_download_id ON plugin_download(plugin_id);

CREATE TABLE plugin_description(
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   plugin_id INT,
   short_description TEXT,
   long_description TEXT,
   lang VARCHAR(10),
   FOREIGN KEY (plugin_id)
      REFERENCES plugin(id)
      ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE INDEX idx_plugin_description_plugin ON plugin_description(plugin_id);

CREATE TABLE plugin_version(
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   plugin_id INT,
   num VARCHAR(50),
   compatibility VARCHAR(50),
   FOREIGN KEY (plugin_id)
      REFERENCES plugin(id)
      ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE INDEX idx_plugin_version_plugin ON plugin_version(plugin_id);

CREATE TABLE plugin_author(
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   plugin_id INT,
   author_id INT,
   FOREIGN KEY (plugin_id)
      REFERENCES plugin(id)
      ON DELETE CASCADE,
   FOREIGN KEY (author_id)
       REFERENCES author(id)
) ENGINE=InnoDB;
CREATE INDEX idx_plugin_author_plugin ON plugin_author(plugin_id);
CREATE INDEX idx_plugin_author_author ON plugin_author(author_id);

CREATE TABLE tag(
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   `key` VARCHAR(255),
   tag VARCHAR(25),
   lang VARCHAR(10)
) ENGINE=InnoDB;

CREATE TABLE plugin_tags(
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   plugin_id INT,
   tag_id INT,
   FOREIGN KEY (plugin_id)
      REFERENCES plugin(id)
      ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE INDEX idx_plugin_tags_plugin ON plugin_tags(plugin_id);

CREATE TABLE plugin_stars(
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   note FLOAT(2,1),
   `date` DATE,
   plugin_id INT,
   FOREIGN KEY (plugin_id)
      REFERENCES plugin(id)
      ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE INDEX idx_plugin_stars_plugin ON plugin_description(plugin_id);

CREATE TABLE plugin_screenshot(
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   plugin_id INT NOT NULL,
   url VARCHAR(500),
   FOREIGN KEY (plugin_id)
      REFERENCES plugin(id)
      ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE INDEX idx_plugin_screenshot_plugin ON plugin_screenshot(plugin_id);

CREATE TABLE message(
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   first_name VARCHAR(40),
   last_name VARCHAR(60),
   email VARCHAR(255),
   subject VARCHAR(200),
   sent DATETIME,
   message TEXT
) ENGINE=InnoDB;

CREATE TABLE apps(
   id            INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   name          VARCHAR(35),
   secret        VARCHAR(40),
   redirect_uri  VARCHAR(140)
) ENGINE=InnoDB;

INSERT INTO apps(name, secret, redirect_uri)
VALUES  ('webapp',      '9677873f8fb70251ce10616b2160be6c06fedcd9', 'http://'),
        ('glpidefault', '7ebc7ee84a9989aa839a7db2f57bcfe9117e22df', 'http://');

CREATE TABLE scopes(
   id            INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   identifier    VARCHAR(40),
   description   VARCHAR(100)
) ENGINE=InnoDB;

INSERT INTO scopes(identifier, description)
VALUES  ('plugins', 'View all known plugins'),
        ('tags', 'View all known attributed tags'),
        ('authors', 'View all known contributors'),
        ('tags', 'View all tags available');

CREATE TABLE sessions(
   id            INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   owner_type    ENUM('app', 'enduser'),
   owner_id      INT,
   app_id        INT NOT NULL,
   FOREIGN KEY (owner_id)
      REFERENCES user(id)
      ON DELETE CASCADE,
   FOREIGN KEY (app_id)
      REFERENCES apps(id)
      ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE sessions_scopes(
   id               INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   session_id       INT NOT NULL,
   scope_id         INT NOT NULL,
   FOREIGN KEY (session_id)
      REFERENCES sessions(id)
      ON DELETE CASCADE,
   FOREIGN KEY (scope_id)
      REFERENCES scopes(id)
      ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE access_tokens(
   id            INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   token         VARCHAR(40),
   session_id    INT NOT NULL,
   expire_time   DATETIME,
   FOREIGN KEY (session_id)
      REFERENCES sessions(id)
      ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE auth_codes(
   id            INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   auth_code     VARCHAR(40),
   session_id    INT NOT NULL,
   expire_time   DATETIME,
   FOREIGN KEY (session_id)
      REFERENCES sessions(id)
) ENGINE=InnoDB;