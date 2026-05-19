import { test, expect } from '@playwright/test';

test.use({ storageState: { cookies: [], origins: [] } });

test('login exitoso manda a /payments', async ({ page }) => {
  await page.goto('/login');

  await page.getByPlaceholder('martin@test.com').fill('martin@test.com');

  await page.getByPlaceholder('••••••••').fill('password123');

  await page.getByRole('button', { name: 'Entrar' }).click();

  await page.waitForURL('/payments', { timeout: 10000 });

  await expect(page).toHaveURL('/payments');
});

test('login fallido muestra mensaje de error', async ({ page }) => {
  await page.goto('/login');

  await page.getByPlaceholder('martin@test.com').fill('martin@test.com');

  await page.getByPlaceholder('••••••••').fill('wrongpassword');

  await page.getByRole('button', { name: 'Entrar' }).click();

  await expect(page.getByText('Credenciales incorrectas')).toBeVisible();
});
