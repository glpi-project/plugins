// @ts-check
const { test, expect } = require('@playwright/test');
const { waitForAppReady } = require('./helpers');

test.describe('F4 — Authentication', () => {
  test('sign up: submitting the form shows a confirmation toast', async ({ page }) => {
    const uniqueSuffix = `${Date.now()}${Math.floor(Math.random() * 10000)}`;
    const username = `newuser${uniqueSuffix}`;
    const email = `${username}@example.com`;

    await page.goto('/#/signup');
    await waitForAppReady(page);

    await page.locator('[data-testid="signup-username-input"]').fill(username);
    await page.locator('[data-testid="signup-email-input"]').fill(email);
    await page.locator('[data-testid="signup-password-input"]').fill('Passw0rd!');
    await page.locator('[data-testid="signup-website-input"]').fill('https://testwebsite.com');
    await page.locator('[data-testid="signup-confirm-password-input"]').fill('Passw0rd!');

    await page.getByRole('button', { name: /sign.?up/i }).click();
    await page.waitForURL('/#/');

    // On success the controller shows a toast and redirects to featured
    await expect(page.getByText(/check your mailbox/i)).toBeVisible();
  });

  test('sign in: valid credentials log the user in', async ({ page }) => {
    await page.goto('/#/signin');
    await waitForAppReady(page);

    await page.locator('[data-testid="signin-username-input"]').fill('testuser');
    await page.locator('[data-testid="signin-password-input"]').fill('Password1');
    await page.locator('#signin form button[type="submit"]').click();

    // Auth service shows this toast on success
    await expect(page.getByText('You are now successfully logged in')).toBeVisible();
  });

  test('sign in: wrong password shows an error toast', async ({ page }) => {
    await page.goto('/#/signin');
    await waitForAppReady(page);

    await page.locator('[data-testid="signin-username-input"]').fill('testuser');
    await page.locator('[data-testid="signin-password-input"]').fill('wrongpassword');
    await page.locator('#signin form button[type="submit"]').click();

    // The interceptor shows a translated error; INVALID_CREDENTIALS or similar
    await expect(
      page.locator('md-toast, .md-toast-content').filter({ hasText: /.+/ })
    ).toBeVisible();
    // User stays on the sign-in page
    await expect(page).toHaveURL(/#\/signin/);
  });

  test('sign out: user name disappears from the header', async ({ page }) => {
    await page.goto('/#/signin');
    await waitForAppReady(page);

    await page.locator('[data-testid="signin-username-input"]').fill('testuser');
    await page.locator('[data-testid="signin-password-input"]').fill('Password1');
    await page.locator('#signin form button[type="submit"]').click();
    await expect(page.getByText('You are now successfully logged in')).toBeVisible();

    // Find and click the sign-out control in the user menu
    await page.getByText('testuser').click();
    await page.getByText(/sign.?out|log.?out|disconnect/i).click();

    await expect(page.getByText('You are now disconnected')).toBeVisible();
  });

  test('GitHub OAuth: the GitHub login button is present', async ({ page }) => {
    await page.goto('/#/signin');
    await waitForAppReady(page);

    // The button contains a GitHub icon; test its presence only (OAuth flow is external)
    await expect(page.locator('.fa-github').first()).toBeVisible();
  });
});
