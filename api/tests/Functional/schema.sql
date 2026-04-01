-- Functional test schema
-- All tables use CREATE TABLE IF NOT EXISTS so re-runs are safe.

CREATE TABLE IF NOT EXISTS `apps` (
  `id`           varchar(20)  NOT NULL,
  `name`         varchar(255) NOT NULL,
  `secret`       varchar(255) NOT NULL DEFAULT '',
  `redirect_uri` varchar(255) DEFAULT NULL,
  `description`  text         DEFAULT NULL,
  `user_id`      int(11)      DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `scopes` (
  `id`          int(11)      NOT NULL AUTO_INCREMENT,
  `identifier`  varchar(100) NOT NULL,
  `description` text         DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identifier` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user` (
  `id`        int(11)      NOT NULL AUTO_INCREMENT,
  `username`  varchar(255) NOT NULL,
  `email`     varchar(255) NOT NULL,
  `password`  varchar(255) DEFAULT NULL,
  `realname`  varchar(255) DEFAULT NULL,
  `location`  varchar(255) DEFAULT NULL,
  `website`   varchar(255) DEFAULT NULL,
  `active`    tinyint(1)   NOT NULL DEFAULT 0,
  `author_id` int(11)      DEFAULT NULL,
  `gravatar`  varchar(32)  DEFAULT NULL,
  `created_at` datetime    DEFAULT NULL,
  `updated_at` datetime    DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sessions` (
  `id`         int(11)     NOT NULL AUTO_INCREMENT,
  `owner_type` varchar(50) DEFAULT NULL,
  `owner_id`   int(11)     DEFAULT NULL,
  `app_id`     varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `access_tokens` (
  `id`          int(11)      NOT NULL AUTO_INCREMENT,
  `token`       varchar(255) NOT NULL,
  `session_id`  int(11)      NOT NULL,
  `expire_time` int(11)      NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `access_tokens_scopes` (
  `access_token_id` int(11) NOT NULL,
  `scope_id`        int(11) NOT NULL,
  PRIMARY KEY (`access_token_id`, `scope_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `refresh_tokens` (
  `id`              int(11)      NOT NULL AUTO_INCREMENT,
  `token`           varchar(255) NOT NULL,
  `access_token_id` int(11)      NOT NULL,
  `expire_time`     int(11)      NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sessions_scopes` (
  `session_id` int(11) NOT NULL,
  `scope_id`   int(11) NOT NULL,
  PRIMARY KEY (`session_id`, `scope_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user_validation_token` (
  `id`      int(11)      NOT NULL AUTO_INCREMENT,
  `token`   varchar(255) NOT NULL,
  `user_id` int(11)      NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user_resetpassword_token` (
  `id`      int(11)      NOT NULL AUTO_INCREMENT,
  `token`   varchar(255) NOT NULL,
  `user_id` int(11)      NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user_external_account` (
  `id`               int(11)      NOT NULL AUTO_INCREMENT,
  `user_id`          int(11)      NOT NULL,
  `service`          varchar(50)  NOT NULL,
  `external_user_id` varchar(255) NOT NULL,
  `token`            varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `author` (
  `id`       int(11)      NOT NULL AUTO_INCREMENT,
  `name`     varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `gravatar` varchar(32)  DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `plugin` (
  `id`             int(11)      NOT NULL AUTO_INCREMENT,
  `name`           varchar(255) NOT NULL,
  `key`            varchar(50)  NOT NULL,
  `logo_url`       varchar(255) DEFAULT NULL,
  `xml_url`        varchar(255) DEFAULT NULL,
  `homepage_url`   varchar(255) DEFAULT NULL,
  `download_url`   varchar(255) DEFAULT NULL,
  `issues_url`     varchar(255) DEFAULT NULL,
  `readme_url`     varchar(255) DEFAULT NULL,
  `changelog_url`  varchar(255) DEFAULT NULL,
  `license`        varchar(50)  DEFAULT NULL,
  `date_added`     datetime     DEFAULT NULL,
  `date_updated`   datetime     DEFAULT NULL,
  `download_count` int(11)      NOT NULL DEFAULT 0,
  `xml_state`      varchar(50)  DEFAULT NULL,
  `active`         tinyint(1)   NOT NULL DEFAULT 0,
  `note`           float        DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `plugin_author` (
  `plugin_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  PRIMARY KEY (`plugin_id`, `author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `plugin_permission` (
  `plugin_id`               int(11)    NOT NULL,
  `user_id`                 int(11)    NOT NULL,
  `admin`                   tinyint(1) NOT NULL DEFAULT 0,
  `allowed_refresh_xml`     tinyint(1) NOT NULL DEFAULT 0,
  `allowed_change_xml_url`  tinyint(1) NOT NULL DEFAULT 0,
  `allowed_notifications`   tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`plugin_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `plugin_description` (
  `id`                int(11) NOT NULL AUTO_INCREMENT,
  `plugin_id`         int(11) NOT NULL,
  `lang`              varchar(10) NOT NULL,
  `short_description` text DEFAULT NULL,
  `long_description`  text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `plugin_version` (
  `id`           int(11)      NOT NULL AUTO_INCREMENT,
  `plugin_id`    int(11)      NOT NULL,
  `num`          varchar(50)  NOT NULL,
  `compatibility` varchar(50) NOT NULL,
  `download_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `plugin_download` (
  `id`            int(11)  NOT NULL AUTO_INCREMENT,
  `plugin_id`     int(11)  NOT NULL,
  `downloaded_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `plugin_screenshot` (
  `id`        int(11)      NOT NULL AUTO_INCREMENT,
  `plugin_id` int(11)      NOT NULL,
  `url`       varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `plugin_stars` (
  `id`        int(11)  NOT NULL AUTO_INCREMENT,
  `plugin_id` int(11)  NOT NULL,
  `note`      int(11)  NOT NULL,
  `date`      datetime DEFAULT NULL,
  `user_id`   int(11)  DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `plugin_xml_fetch_fails` (
  `plugin_id` int(11) NOT NULL,
  `n`         int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`plugin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `plugin_lang` (
  `id`   int(11)     NOT NULL AUTO_INCREMENT,
  `lang` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `plugin_plugin_lang` (
  `plugin_id`      int(11) NOT NULL,
  `plugin_lang_id` int(11) NOT NULL,
  PRIMARY KEY (`plugin_id`, `plugin_lang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tag` (
  `id`   int(11)      NOT NULL AUTO_INCREMENT,
  `key`  varchar(100) NOT NULL,
  `lang` varchar(10)  NOT NULL,
  `tag`  varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `plugin_tags` (
  `plugin_id` int(11) NOT NULL,
  `tag_id`    int(11) NOT NULL,
  PRIMARY KEY (`plugin_id`, `tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user_plugin_watch` (
  `id`        int(11) NOT NULL AUTO_INCREMENT,
  `user_id`   int(11) NOT NULL,
  `plugin_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `auth_codes` (
  `id`           int(11)      NOT NULL AUTO_INCREMENT,
  `code`         varchar(255) NOT NULL,
  `session_id`   int(11)      NOT NULL,
  `expire_time`  int(11)      NOT NULL,
  `redirect_uri` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `message` (
  `id`      int(11)      NOT NULL AUTO_INCREMENT,
  `name`    varchar(255) DEFAULT NULL,
  `email`   varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text         DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
