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
   password VARCHAR(60),
   realname TEXT,
   location VARCHAR(80),
   website VARCHAR(255)
) ENGINE=InnoDB;

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