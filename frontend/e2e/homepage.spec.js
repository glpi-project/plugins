// @ts-check
const { test, expect } = require('@playwright/test');
const { waitForAppReady } = require('./helpers');

test.describe('F1 — Homepage', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    await waitForAppReady(page);
  });

  test('page title identifies the site', async ({ page }) => {
    await expect(page).toHaveTitle(/GLPi Plugins/i);
  });

  test('featured sections are visible', async ({ page }) => {
    // The featured page shows four lists: Trending, New, Popular, Updated
    for (const heading of ['Trending', 'New', 'Popular', 'Updated']) {
      await expect(page.getByText(heading, { exact: true }).first()).toBeVisible();
    }
  });

  test('each section contains at least one plugin name', async ({ page }) => {
    // Seed contains active plugins — they should appear in each list
    for (const testid of ['trending-plugin-item', 'new-plugin-item', 'popular-plugin-item', 'updated-plugin-item']) {
      await expect(page.locator(`[data-testid="${testid}"]`).first()).toBeVisible();
    }
  });

  test('clicking a plugin name navigates to the plugin detail page', async ({ page }) => {
    const firstPlugin = page.locator('[data-testid="trending-plugin-item"]').first();
    const name = (await firstPlugin.textContent()) ?? '';
    await firstPlugin.click();
    // URL changes to #/plugin/<key>
    await expect(page).toHaveURL(/#\/plugin\//);
    // The plugin name appears in the detail header
    await expect(page.locator('[data-testid="plugin-name"]').filter({ hasText: name.trim() })).toBeVisible();
  });
});
