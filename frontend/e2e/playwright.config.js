// @ts-check
const { defineConfig, devices } = require('@playwright/test');

const path = require('path');
const COMPOSE_FILE = path.resolve(__dirname, '../../docker-compose.e2e.yml');

module.exports = defineConfig({
  testDir: '.',
  testMatch: '**/*.spec.js',

  // Start the Docker stack automatically if E2E_BASE_URL is not already set
  // (i.e. not pointing at an externally managed server).
  webServer: process.env.E2E_BASE_URL ? undefined : {
    command: `docker compose -f ${COMPOSE_FILE} up --build`,
    url: 'http://localhost:4200',
    timeout: 300_000,   // frontend image build can take a few minutes
    reuseExistingServer: true,
    stdout: 'pipe',
    stderr: 'pipe',
  },
  timeout: 30_000,
  expect: { timeout: 8_000 },
  fullyParallel: false,   // AngularJS SPA shares a single backend; run serially
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  reporter: process.env.CI ? [['github'], ['html', { open: 'never' }]] : 'list',

  use: {
    baseURL: process.env.E2E_BASE_URL || 'http://localhost:4200',
    trace: 'on-first-retry',
    // The app uses hash-based routing (#/plugin/fields); no need for JS-aware navigation.
    // Increase navigation timeout for the first load (Angular bootstrap + anon token fetch).
    navigationTimeout: 15_000,
  },

  projects: [
    { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
  ],
});
