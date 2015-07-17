CREATE TABLE plugin (
	id INT, 
	xml_url TEXT, 
	xml_crc VARCHAR(32),
	name TEXT, # displayed name
	key VARCHAR(255), # sysname
	homepage_url TEXT, 
	download_url TEXT, 
	issues_url TEXT,
	readme_url TEXT, # for displaying content on plugin page
	license VARCHAR(255)
	categories TEXT,
	active INT(1),  # for moderation
	date_added DATE,
	date_updated DATE
) ENGINE=InnoDB;

CREATE TABLE plugin_description(
	id INT,
	plugin_id INT,
	description TEXT,
	lang VARCHAR(10)
) ENGINE=InnoDB;

CREATE TABLE plugin_version(
	id INT,
	plugin_id INT,
	num VARCHAR(50),
	compatibility VARCHAR(50)
) ENGINE=InnoDB;

CREATE TABLE plugin_author(
	id INT, 
	author TEXT
) ENGINE=InnoDB;

CREATE TABLE tag(
	id INT,
	tag VARCHAR(25),
	lang VARCHAR(10)
) ENGINE=InnoDB;

CREATE TABLE plugin_tags(
	id INT, 
	plugin_id INT,
	tag_id
) ENGINE=InnoDB;

CREATE TABLE plugin_stars(
	id INT, 
	note INT, 
	date DATE, 
	plugin_id INT,
) ENGINE=InnoDB;

CREATE TABLE plugin_view(
	id INT, 
	plugin_id INT,
	date DATE
) ENGINE=InnoDB;