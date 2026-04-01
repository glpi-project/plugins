-- Built-in OAuth clients (webapp / glpidefault use empty secret)
INSERT INTO `apps` (`id`, `name`, `secret`) VALUES
  ('webapp',      'Web App',      ''),
  ('glpidefault', 'GLPI Default', '');

-- All API scopes
INSERT INTO `scopes` (`identifier`, `description`) VALUES
  ('plugins',              'Browse plugin list'),
  ('plugins:search',       'Search plugins'),
  ('plugin:card',          'Read single plugin details'),
  ('plugin:star',          'Rate a plugin'),
  ('plugin:submit',        'Submit a new plugin'),
  ('plugin:download',      'Download a plugin'),
  ('tags',                 'Browse tag list'),
  ('tag',                  'Read single tag'),
  ('authors',              'Browse author list'),
  ('author',               'Read single author'),
  ('version',              'Filter by GLPI version'),
  ('user',                 'Read/edit own profile'),
  ('user:apps',            'Manage own API apps'),
  ('user:externalaccounts','Manage linked OAuth accounts'),
  ('users:search',         'Search users'),
  ('message',              'Send contact message');
