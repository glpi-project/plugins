/**
 * Shared helpers for E2E tests.
 *
 * loginAs() bypasses the UI login form by obtaining a token directly from the
 * API and injecting it into localStorage — the same keys the Auth service uses.
 * Use the UI sign-in flow in auth.spec.js; use loginAs() everywhere else.
 */


/**
 * Obtain an access token via the password grant and inject it into the page's
 * localStorage so Angular's Auth service treats the session as authenticated.
 *
 * @param {import('@playwright/test').Page} page
 * @param {string} username
 * @param {string} password
 */
async function loginAs(page, username, password) {
  const baseURL = process.env.E2E_BASE_URL || 'http://localhost:4200';

  const resp = await page.request.post(`${baseURL}/api/oauth/authorize`, {
    form: {
      grant_type: 'password',
      client_id:  'webapp',
      username,
      password,
      scope: [
        'plugins', 'plugins:search', 'plugin:card', 'plugin:star',
        'plugin:download', 'tags', 'tag', 'authors', 'author', 'version',
        'message', 'user', 'user:externalaccounts', 'user:apps',
        'plugin:submit', 'users:search',
      ].join(' '),
    },
  });

  if (!resp.ok()) {
    const body = await resp.text();
    throw new Error(
      `Auth request failed (${resp.status()}): ${body}`
    );
  }

  const { access_token, refresh_token, expires_in } = await resp.json();

  await page.evaluate(
    ([token, refresh, expiresAt]) => {
      localStorage.setItem('access_token', token);
      localStorage.setItem('refresh_token', refresh);
      localStorage.setItem('access_token_expires_at', expiresAt);
      localStorage.setItem('authed', 'true');
    },
    [access_token, refresh_token, String(Math.floor(Date.now() / 1000) + expires_in)],
  );

  // Angular's $httpProvider.defaults.headers is set in .config() which runs
  // only once at bootstrap. Reload so Angular re-bootstraps and picks up the
  // token from localStorage before any test navigation occurs.
  await page.reload();
  await waitForAppReady(page);
}

/**
 * Wait until the app has finished loading its initial data (no pending network
 * requests for 500 ms). Works regardless of frontend framework.
 *
 * @param {import('@playwright/test').Page} page
 */
async function waitForAppReady(page) {
  await page.waitForLoadState('networkidle');
}

module.exports = { loginAs, waitForAppReady };
