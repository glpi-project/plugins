-- E2E seed data
-- Applied once when the stack starts. Provides enough content for all frontend suites.

-- OAuth clients and scopes (same as functional seeds)
INSERT INTO `apps` (`id`, `name`, `secret`) VALUES
  ('webapp',      'Web App',      ''),
  ('glpidefault', 'GLPI Default', '');

INSERT INTO `scopes` (`identifier`, `description`) VALUES
  ('plugins',               'Browse plugin list'),
  ('plugins:search',        'Search plugins'),
  ('plugin:card',           'Read single plugin details'),
  ('plugin:star',           'Rate a plugin'),
  ('plugin:submit',         'Submit a new plugin'),
  ('plugin:download',       'Download a plugin'),
  ('tags',                  'Browse tag list'),
  ('tag',                   'Read single tag'),
  ('authors',               'Browse author list'),
  ('author',                'Read single author'),
  ('version',               'Filter by GLPI version'),
  ('user',                  'Read/edit own profile'),
  ('user:apps',             'Manage own API apps'),
  ('user:externalaccounts', 'Manage linked OAuth accounts'),
  ('users:search',          'Search users'),
  ('message',               'Send contact message');

-- Authors
INSERT INTO `author` (`id`, `name`) VALUES
  (1, 'Plugin Author');

-- Active plugins
INSERT INTO `plugin`
  (`id`, `name`, `key`, `xml_url`, `download_url`, `active`, `download_count`, `date_added`, `date_updated`, `xml_state`)
VALUES
  (1, 'Fields',       'fields',       'http://example.com/fields.xml',       'http://example.com/fields.zip',       1, 120, NOW(), NOW(), 'passing'),
  (2, 'Form Creator', 'formcreator',  'http://example.com/formcreator.xml',  'http://example.com/formcreator.zip',  1,  80, NOW(), NOW(), 'passing');

INSERT INTO `plugin_author` (`plugin_id`, `author_id`) VALUES (1, 1), (2, 1);

INSERT INTO `plugin_description` (`plugin_id`, `lang`, `short_description`, `long_description`) VALUES
  (1, 'en', 'Add custom fields to GLPI objects.',    '# Fields\n\nAdd custom fields to any GLPI object type.'),
  (2, 'en', 'Create custom forms in GLPI.',          '# Form Creator\n\nCreate and manage custom forms.');

INSERT INTO `plugin_version` (`plugin_id`, `num`, `compatibility`) VALUES
  (1, '1.0.0', '9.5'),
  (1, '1.1.0', '10.0'),
  (2, '2.0.0', '9.5');

-- Tags
INSERT INTO `tag` (`id`, `key`, `lang`, `tag`) VALUES
  (1, 'inventory', 'en', 'Inventory'),
  (2, 'forms',     'en', 'Forms');

INSERT INTO `plugin_tags` (`plugin_id`, `tag_id`) VALUES (1, 1), (2, 2);

-- Test user (password: Password1)
INSERT INTO `user` (`id`, `username`, `email`, `password`, `realname`, `active`, `author_id`) VALUES
  (1, 'testuser', 'testuser@example.com',
   '$2y$12$1PbuQOawaJYZsLP/39w0juPu6sFG9Vn8aE17PSgBHXHpnWD6eJ.VW',
   'Test User', 1, 1);

-- testuser has admin rights on the Fields plugin
INSERT INTO `plugin_permission` (`plugin_id`, `user_id`, `admin`) VALUES (1, 1, 1);
