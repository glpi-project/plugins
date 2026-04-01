// @ts-check
const { test, expect } = require('@playwright/test');
const { waitForAppReady } = require('./helpers');

test.describe('F3 — Search', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    await waitForAppReady(page);
  });

  test('typing a query shows matching results', async ({ page }) => {
    const searchBox = page.getByLabel('Search');
    await searchBox.fill('Fields');
    await searchBox.press('Enter');

    await waitForAppReady(page);
    // Results list: each result has a plugin name link
    const results = page.locator('[data-testid="search-result-name"]');
    await expect(results.first()).toBeVisible();
    await expect(results.first()).toContainText(/fields/i);
  });

  test('each result shows a plugin name linking to the detail page', async ({ page }) => {
    const searchBox = page.getByLabel('Search');
    await searchBox.fill('Fields');
    await searchBox.press('Enter');

    await waitForAppReady(page);
    const link = page.locator('[data-testid="search-result-name"]').first();
    await link.click();
    await expect(page).toHaveURL(/#\/plugin\//);
  });

  test('a query with no matches shows the empty-state message', async ({ page }) => {
    const searchBox = page.getByLabel('Search');
    await searchBox.fill('xyznonexistentplugin');
    await searchBox.press('Enter');

    await waitForAppReady(page);
    await expect(page.getByText('No result')).toBeVisible();
  });
});
