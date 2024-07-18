SET NAMES utf8;
SET foreign_key_checks = 0;

CREATE TABLE `access_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `token` varchar(40) DEFAULT NULL,
  `session_id` int NOT NULL,
  `expire_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `access_tokens_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `access_tokens_scopes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `access_token_id` int NOT NULL,
  `scope_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `access_token_id` (`access_token_id`),
  KEY `scope_id` (`scope_id`),
  CONSTRAINT `access_tokens_scopes_ibfk_1` FOREIGN KEY (`access_token_id`) REFERENCES `access_tokens` (`id`) ON DELETE CASCADE,
  CONSTRAINT `access_tokens_scopes_ibfk_2` FOREIGN KEY (`scope_id`) REFERENCES `scopes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `apps` (
  `id` varchar(40) NOT NULL,
  `user_id` int DEFAULT NULL,
  `name` varchar(35) DEFAULT NULL,
  `secret` varchar(40) DEFAULT NULL,
  `homepage_url` varchar(300) DEFAULT NULL,
  `description` text,
  `redirect_uri` varchar(140) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_apps_user` (`user_id`),
  CONSTRAINT `apps_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `auth_codes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `auth_code` varchar(40) DEFAULT NULL,
  `session_id` int NOT NULL,
  `expire_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `auth_codes_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `author` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `message` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(40) DEFAULT NULL,
  `last_name` varchar(60) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `sent` datetime DEFAULT NULL,
  `message` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `plugin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `active` int DEFAULT NULL,
  `featured` int DEFAULT NULL,
  `xml_state` enum('bad_xml_url','xml_error','passing') DEFAULT NULL,
  `xml_url` text,
  `xml_crc` varchar(32) DEFAULT NULL,
  `name` text,
  `logo_url` text,
  `key` varchar(255) DEFAULT NULL,
  `homepage_url` text,
  `download_url` text,
  `issues_url` text,
  `readme_url` text,
  `changelog_url` text,
  `license` varchar(255) DEFAULT NULL,
  `date_added` date DEFAULT NULL,
  `date_updated` date DEFAULT NULL,
  `download_count` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ix_plugin` (`key`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `plugin_author` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plugin_id` int DEFAULT NULL,
  `author_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_plugin_author_plugin` (`plugin_id`),
  KEY `idx_plugin_author_author` (`author_id`),
  CONSTRAINT `plugin_author_ibfk_1` FOREIGN KEY (`plugin_id`) REFERENCES `plugin` (`id`) ON DELETE CASCADE,
  CONSTRAINT `plugin_author_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `author` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `plugin_description` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plugin_id` int DEFAULT NULL,
  `short_description` text,
  `long_description` text,
  `lang` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_plugin_description_plugin` (`plugin_id`),
  CONSTRAINT `plugin_description_ibfk_1` FOREIGN KEY (`plugin_id`) REFERENCES `plugin` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `plugin_download` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plugin_id` int NOT NULL,
  `downloaded_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_plugin_download_id` (`plugin_id`),
  KEY `downloaded_at` (`downloaded_at`),
  CONSTRAINT `plugin_download_ibfk_1` FOREIGN KEY (`plugin_id`) REFERENCES `plugin` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `plugin_lang` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lang` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lang` (`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `plugin_permission` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plugin_id` int NOT NULL,
  `user_id` int NOT NULL,
  `admin` int DEFAULT NULL,
  `allowed_refresh_xml` int DEFAULT NULL,
  `allowed_change_xml_url` int DEFAULT NULL,
  `allowed_notifications` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plugin_id` (`plugin_id`,`user_id`),
  KEY `idx_plugin_permission_plugin` (`plugin_id`),
  KEY `idx_plugin_permission_user` (`user_id`),
  CONSTRAINT `plugin_permission_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `plugin_permission_ibfk_2` FOREIGN KEY (`plugin_id`) REFERENCES `plugin` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `plugin_plugin_lang` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plugin_id` int NOT NULL,
  `plugin_lang_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `plugin_id` (`plugin_id`),
  KEY `plugin_lang_id` (`plugin_lang_id`),
  CONSTRAINT `plugin_plugin_lang_ibfk_1` FOREIGN KEY (`plugin_id`) REFERENCES `plugin` (`id`) ON DELETE CASCADE,
  CONSTRAINT `plugin_plugin_lang_ibfk_2` FOREIGN KEY (`plugin_lang_id`) REFERENCES `plugin_lang` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `plugin_screenshot` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plugin_id` int NOT NULL,
  `url` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_plugin_screenshot_plugin` (`plugin_id`),
  CONSTRAINT `plugin_screenshot_ibfk_1` FOREIGN KEY (`plugin_id`) REFERENCES `plugin` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `plugin_stars` (
  `id` int NOT NULL AUTO_INCREMENT,
  `note` float(2,1) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `plugin_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_plugin_stars_plugin` (`plugin_id`),
  CONSTRAINT `plugin_stars_ibfk_1` FOREIGN KEY (`plugin_id`) REFERENCES `plugin` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `plugin_tags` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plugin_id` int DEFAULT NULL,
  `tag_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_plugin_tags_plugin` (`plugin_id`),
  CONSTRAINT `plugin_tags_ibfk_1` FOREIGN KEY (`plugin_id`) REFERENCES `plugin` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `plugin_version` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plugin_id` int DEFAULT NULL,
  `num` varchar(50) DEFAULT NULL,
  `compatibility` varchar(50) DEFAULT NULL,
  `download_url` text,
  PRIMARY KEY (`id`),
  KEY `idx_plugin_version_plugin` (`plugin_id`),
  CONSTRAINT `plugin_version_ibfk_1` FOREIGN KEY (`plugin_id`) REFERENCES `plugin` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `plugin_xml_fetch_fails` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plugin_id` int NOT NULL,
  `n` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_plugin_xml_fetch_fails_plugin_id` (`plugin_id`),
  CONSTRAINT `plugin_xml_fetch_fails_ibfk_1` FOREIGN KEY (`plugin_id`) REFERENCES `plugin` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `refresh_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `access_token_id` int NOT NULL,
  `token` varchar(40) DEFAULT NULL,
  `expire_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_refresh_tokens_access_token` (`access_token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `scopes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identifier` varchar(40) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner_type` enum('client','user') DEFAULT NULL,
  `owner_id` int DEFAULT NULL,
  `app_id` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  KEY `app_id` (`app_id`),
  CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sessions_ibfk_2` FOREIGN KEY (`app_id`) REFERENCES `apps` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sessions_scopes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `scope_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `scope_id` (`scope_id`),
  CONSTRAINT `sessions_scopes_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sessions_scopes_ibfk_2` FOREIGN KEY (`scope_id`) REFERENCES `scopes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tag` (
  `id` int NOT NULL AUTO_INCREMENT,
  `key` varchar(255) DEFAULT NULL,
  `tag` varchar(25) DEFAULT NULL,
  `lang` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `active` int DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `username` varchar(28) DEFAULT NULL,
  `password` varchar(60) DEFAULT NULL,
  `realname` text,
  `location` varchar(80) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `author_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `user_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `author` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_external_account` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `external_user_id` int NOT NULL,
  `token` varchar(60) DEFAULT NULL,
  `service` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_external_acount_user_id` (`user_id`),
  CONSTRAINT `user_external_account_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_plugin_watch` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `plugin_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `plugin_id` (`plugin_id`),
  CONSTRAINT `user_plugin_watch_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_plugin_watch_ibfk_2` FOREIGN KEY (`plugin_id`) REFERENCES `plugin` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_resetpassword_token` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_resetpassword_token` (`user_id`),
  CONSTRAINT `user_resetpassword_token_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user_validation_token` (
  `id` int NOT NULL AUTO_INCREMENT,
  `token` varchar(40) NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_validation_token_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `apps` (`id`, `name`, `secret`, `redirect_uri`)
VALUES  ('webapp', 'Main HTTP Site', '', NULL),
        ('glpidefault', 'GLPI Update Manager', '', NULL);

INSERT INTO `scopes` (`identifier`, `description`)
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
