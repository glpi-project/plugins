// @ts-check
const { test, expect } = require('@playwright/test');
const { loginAs, waitForAppReady } = require('./helpers');

test.describe('F2 — Plugin detail page', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/#/plugin/fields');
    await waitForAppReady(page);
  });

  test('plugin name is visible in the header', async ({ page }) => {
    await expect(page.locator('[data-testid="plugin-name"]')).toHaveText('Fields');
  });

  test('author name is present', async ({ page }) => {
    await expect(page.locator('[data-testid="plugin-authors"]').first()).toContainText('Plugin Author');
  });

  test('at least one version compatibility badge is shown', async ({ page }) => {
    await expect(page.locator('[data-testid="version-badge"]').first()).toBeVisible();
  });

  test('description tab content is rendered', async ({ page }) => {
    await expect(page.locator('[data-testid="plugin-description"]').first()).toBeVisible();
  });

  test('watch button is hidden when not logged in', async ({ page }) => {
    await expect(page.locator('[data-testid="watch-button"]')).toBeHidden();
  });

  test('watch button is visible and toggles state when logged in', async ({ page }) => {
    await loginAs(page, 'testuser', 'Password1');
    await page.reload();
    await waitForAppReady(page);

    const watchBtn = page.locator('[data-testid="watch-button"]');
    await expect(watchBtn).toBeVisible();

    // Click to watch — icon switches from eye to eye-slash
    await watchBtn.click();
    await expect(watchBtn.locator('.fa-eye-slash')).toBeVisible();

    // Click again to unwatch
    await watchBtn.click();
    await expect(watchBtn.locator('.fa-eye')).toBeVisible();
  });
});
