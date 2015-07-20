CREATE TABLE plugin (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	xml_url TEXT, 
	xml_crc VARCHAR(32),
	name TEXT,
	`key` VARCHAR(255),
	homepage_url TEXT, 
	download_url TEXT, 
	issues_url TEXT,
	readme_url TEXT,
	license VARCHAR(255),
	active INT(1),
	date_added DATE,
	date_updated DATE
) ENGINE=InnoDB;

CREATE TABLE plugin_description(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	plugin_id INT,
	description TEXT,
	lang VARCHAR(10),
	FOREIGN KEY (plugin_id)
		REFERENCES plugin(id)
		ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE INDEX idx_plugin_description_plugin on plugin_description(plugin_id);


CREATE TABLE plugin_version(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	plugin_id INT,
	num VARCHAR(50),
	compatibility VARCHAR(50),
	FOREIGN KEY (plugin_id)
		REFERENCES plugin(id)
		ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE INDEX idx_plugin_version_plugin on plugin_version(plugin_id);

CREATE TABLE plugin_author(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	plugin_id INT,
	author TEXT,
	FOREIGN KEY (plugin_id)
		REFERENCES plugin(id)
) ENGINE=InnoDB;
CREATE INDEX idx_plugin_author_plugin on plugin_author(plugin_id);

CREATE TABLE tag(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	tag VARCHAR(25),
	lang VARCHAR(10)
) ENGINE=InnoDB;

CREATE TABLE plugin_tags(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
	plugin_id INT,
	tag_id INT,
	FOREIGN KEY (plugin_id)
		REFERENCES plugin(id)
) ENGINE=InnoDB;
CREATE INDEX idx_plugin_tags_plugin on plugin_tags(plugin_id);

CREATE TABLE plugin_stars(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	note INT, 
	`date` DATE, 
	plugin_id INT,
	FOREIGN KEY (plugin_id)
		REFERENCES plugin(id)
		ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE INDEX idx_plugin_stars_plugin on plugin_description(plugin_id);

-- CREATE TABLE plugin_view(
-- 	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
-- 	plugin_id INT,
-- 	`date` DATE,
-- 	FOREIGN KEY (plugin_id)
-- 		REFERENCES plugin(id)
-- 		ON DELETE CASCADE
-- ) ENGINE=InnoDB;
-- CREATE INDEX idx_plugin_view_plugin on plugin_description(plugin_id);