// @ts-check
const { test, expect } = require('@playwright/test');
const { loginAs, waitForAppReady } = require('./helpers');

test.describe('F5 — User Panel', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    await loginAs(page, 'testuser', 'Password1');
    await page.goto('/#/panel');
    await waitForAppReady(page);
  });

  test('authenticated user sees the panel', async ({ page }) => {
    await expect(page.getByText('My informations')).toBeVisible();
  });

  test('plugin list shows the user\'s plugins', async ({ page }) => {
    await expect(page.getByText('My plugins')).toBeVisible();
    await expect(page.locator('h4.plugin-name').filter({ hasText: 'Fields' })).toBeVisible();
  });

  test('API keys section is reachable', async ({ page }) => {
    await page.getByText('Manage API Keys', { exact: false }).click();
    await waitForAppReady(page);
    await expect(page).toHaveURL(/#\/panel\/apikeys/);
  });
});

test.describe('F6 — Plugin Author Panel', () => {
  const updatedXmlUrl = 'http://api/tests/E2E/fixtures/fields-updated.xml';

  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    await loginAs(page, 'testuser', 'Password1');
    await page.goto('/#/panel/plugin/fields');
    await waitForAppReady(page);
  });

  test('plugin name is shown in the panel header', async ({ page }) => {
    await expect(page.locator('h2.plugin-name')).toHaveText('Fields');
  });

  test('XML URL field is editable and persists on save', async ({ page }) => {
    const xmlInput = page.locator('[data-testid="plugin-xml-url-input"]');
    await xmlInput.click();
    await xmlInput.fill(updatedXmlUrl);
    await expect(xmlInput).toHaveValue(updatedXmlUrl);

    await page.getByRole('button', { name: /save/i }).click();

    // Reload and verify the new value is still there
    await page.reload();
    await waitForAppReady(page);
    await expect(xmlInput).toHaveValue(updatedXmlUrl);
  });

  test('Refresh XML button is visible for admin', async ({ page }) => {
    await expect(page.getByRole('button', { name: /refresh xml file/i })).toBeVisible();
  });

  test('Refresh XML button triggers a response', async ({ page }) => {
    await page.getByRole('button', { name: /refresh xml file/i }).click();
    // After refresh, xml_errors list updates (may be empty or show errors)
    await expect(page.locator('.xml-errors')).toBeVisible();
  });
});
