CREATE TABLE plugin (
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   active INT(1),
   xml_state ENUM('bad_xml_url', 'xml_error', 'passing'),
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
   created_at DATETIME,
   updated_at DATETIME,
   active INT(1),
   email VARCHAR(255),
   username VARCHAR(28),
   password VARCHAR(60),
   realname TEXT,
   location VARCHAR(80),
   website VARCHAR(255),
   author_id INT,
   FOREIGN KEY(author_id)
      REFERENCES author(id)
) ENGINE=InnoDB;

CREATE TABLE user_plugin_watch(
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   user_id INT NOT NULL,
   plugin_id INT NOT NULL,
   FOREIGN KEY(user_id)
      REFERENCES user(id)
      ON DELETE CASCADE,
   FOREIGN KEY(plugin_id)
      REFERENCES plugin(id)
      ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE user_validation_token (
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   token varchar(40) NOT NULL,
   user_id INT NOT NULL,
   FOREIGN KEY(user_id)
      REFERENCES user(id)
      ON DELETE CASCADE
);

CREATE TABLE user_external_account(
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   user_id INT NOT NULL,
   external_user_id INT NOT NULL,
   token VARCHAR(60),
   service VARCHAR(40),
   FOREIGN KEY (user_id)
      REFERENCES user(id)
      ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE INDEX idx_user_external_acount_user_id ON user_external_account(user_id);

CREATE TABLE user_resetpassword_token(
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   user_id INT NOT NULL,
   token VARCHAR(40),
   FOREIGN KEY(user_id)
      REFERENCES user(id)
      ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE INDEX idx_user_resetpassword_token ON user_resetpassword_token(user_id);

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

CREATE TABLE plugin_contributor(
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   plugin_id INT,
   user_id INT,
   FOREIGN KEY (plugin_id)
      REFERENCES plugin(id)
      ON DELETE CASCADE,
   FOREIGN KEY (user_id)
      REFERENCES user(id)
      ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE INDEX idx_plugin_contributor_plugin ON plugin_contributor(plugin_id);
CREATE INDEX idx_plugin_contributor_user ON plugin_contributor(userpuser_id);

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

CREATE TABLE plugin_lang(
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   lang VARCHAR(5) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE plugin_plugin_lang(
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   plugin_id INT NOT NULL,
   plugin_lang_id INT NOT NULL,
   FOREIGN KEY (plugin_id)
      REFERENCES plugin(id)
      ON DELETE CASCADE,
   FOREIGN KEY (plugin_lang_id)
      REFERENCES plugin_lang(id)
      ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE plugin_permission (
   id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   plugin_id INT NOT NULL,
   user_id INT NOT NULL,
   admin INT(1),
   allowed_refresh_xml INT(1),
   allowed_change_xml_url INT(1),
   allowed_notifications INT(1),
   FOREIGN KEY (user_id)
      REFERENCES user(id)
      ON DELETE CASCADE,
   FOREIGN KEY (plugin_id)
      REFERENCES plugin(id)
      ON DELETE CASCADE,
   UNIQUE (plugin_id,user_id)
) ENGINE=InnoDB;
CREATE INDEX idx_plugin_permission_plugin ON plugin_permission(plugin_id);
CREATE INDEX idx_plugin_permission_user ON plugin_permission(user_id);

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
   id            VARCHAR(40) NOT NULL PRIMARY KEY,
   user_id       INT,
   name          VARCHAR(35),
   secret        VARCHAR(40),
   homepage_url  VARCHAR(300),
   description   TEXT,
   redirect_uri  VARCHAR(140),
   FOREIGN KEY(user_id)
      REFERENCES user(id)
) ENGINE=InnoDB;
CREATE INDEX idx_apps_user ON apps(user_id);

INSERT INTO apps(id, name, secret, redirect_uri)
VALUES  ('webapp', 'Main HTTP Site', '', NULL),
        ('glpidefault', 'GLPI Update Manager', '', NULL);

CREATE TABLE scopes(
   id            INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   identifier    VARCHAR(40),
   description   VARCHAR(100)
) ENGINE=InnoDB;

INSERT INTO scopes(identifier, description)
VALUES  ('plugins', 'Read lists of plugins (all, popular, trending, new, updated, ...)'),
        ('plugins:search', 'Search into of plugins'),
        ('plugin:card', 'Get card of specific plugin'),
        ('plugin:star', 'View card of a single tag'),
        ('plugin:submit', 'Grants right to note a plugin'),
        ('plugin:download', 'Grants right to download a plugin'),
        ('tags', 'View all known attributed tags'),
        ('tag', 'View card of a single tags'),
        ('authors', 'Read lists of authors'),
        ('author', 'Get card of specific author'),
        ('version', 'Get card of a specific GLPI Version'),
        ('message', 'Send a message to our wonderful team'),
        ('user', 'Allow logged user to modify his profile'),
        ('user:externalaccounts', 'Allow logged user to view/edit/delete his external social account connections'),
        ('user:apps', 'Allow logged user to view/edit/delete his API Keys'),
        ('users:search', 'Allow to Search trough the users of GLPi Plugins');

CREATE TABLE sessions(
   id            INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   owner_type    ENUM('client', 'user'),
   owner_id      INT,
   app_id        VARCHAR(40) NOT NULL,
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
   expire_time   DATETIME
) ENGINE=InnoDB;

CREATE TABLE access_tokens_scopes(
   id               INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   access_token_id  INT NOT NULL,
   scope_id         INT NOT NULL,
   FOREIGN KEY (access_token_id)
      REFERENCES access_tokens(id)
      ON DELETE CASCADE,
   FOREIGN KEY (scope_id)
      REFERENCES scopes(id)
      ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE refresh_tokens(
   id              INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   access_token_id INT NOT NULL,
   token           VARCHAR(40),
   expire_time     DATETIME
) ENGINE=InnoDB;
CREATE INDEX idx_refresh_tokens_access_token ON refresh_tokens(access_token_id);

CREATE TABLE auth_codes(
   id            INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   auth_code     VARCHAR(40),
   session_id    INT NOT NULL,
   expire_time   DATETIME,
   FOREIGN KEY (session_id)
      REFERENCES sessions(id)
) ENGINE=InnoDB;

CREATE TABLE plugin_xml_fetch_fails(
   id            INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   plugin_id     INT NOT NULL,
   n             INT UNSIGNED NOT NULL,
   FOREIGN KEY (plugin_id)
      REFERENCES plugin(id)
      ON DELETE CASCADE
);
CREATE INDEX idx_plugin_xml_fetch_fails_plugin_id ON plugin_xml_fetch_fails(plugin_id);
