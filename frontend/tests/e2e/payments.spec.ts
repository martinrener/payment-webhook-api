import { test, expect } from '@playwright/test';

test.beforeEach(async ({ page }) => {
    await page.goto('/payments');
    await page.waitForSelector('table tbody tr');
});

test.describe.configure({ mode: 'serial' });

test('boton metrics te lleva a la pagina de metrics', async ({ page }) => {

  await page.getByRole('link', { name: 'Metrics' }).click();

  await page.waitForURL('/metrics', { timeout: 10000 });

  await expect(page).toHaveURL('/metrics');
});

test('pagina de metrics carga correctamente', async ({ page }) => {
    await page.goto('/metrics');
    await expect(page.getByText('Metrics')).toBeVisible();
    await expect(page.getByText('Unique Users')).toBeVisible();
    await expect(page.getByText('Payments by Event')).toBeVisible();
    await expect(page.getByText('Payments by Currency')).toBeVisible();
});

test('tabla de payments muestra datos', async ({ page }) => {
    await expect(page.locator('table tbody tr').first()).toBeVisible();
});

test('click en fila payments navega a la pagina de events', async ({ page }) => {
    const firstRow = page.locator('table tbody tr').first();
    const paymentId = await firstRow.locator('td').nth(0).textContent();
    await firstRow.click();
    await page.waitForURL(`/payments/${paymentId?.trim()}`, { timeout: 10000 });
    await expect(page).toHaveURL(`/payments/${paymentId?.trim()}`);
});

test('paginacion funciona correctamente', async ({ page }) => {
    const totalText = await page.getByText(/Total:/).textContent();
    const total = parseInt(totalText?.match(/\d+/)?.[0] ?? '0');
    const totalPages = Math.floor(total / 10);

    for (let i = 0; i < totalPages; i++) {
        await page.getByRole('button', { name: 'Siguiente' }).click();
        await page.waitForTimeout(500);
    }

    await expect(page.getByRole('button', { name: 'Siguiente' })).toBeDisabled();
});

test('refund crea evento payment.refunded', async ({ page }) => {
    const firstRow = page.locator('table tbody tr').first();
    const paymentId = await firstRow.locator('td').nth(0).textContent();
    
    await firstRow.getByRole('button', { name: 'Refund' }).click();
    await page.getByRole('button', { name: 'Confirmar reembolso' }).click();
    await page.waitForTimeout(2000);
    
    await page.goto(`/payments/${paymentId?.trim()}`);
    const eventRows = page.locator('table tbody tr');
    await expect(eventRows.filter({ hasText: 'payment.refunded' }).first()).toBeVisible();
});

test('filtro por event filtra bien', async ({ page }) => {
    await page.getByPlaceholder('Event Type').fill('payment.refunded');
    await page.getByRole('button', { name: 'Filtros' }).click();
    await page.waitForTimeout(1000);
    const rows = page.locator('table tbody tr');
    const count = await rows.count();
    for (let i = 0; i < count; i++) {
        await expect(rows.nth(i).locator('td').nth(1)).toContainText('payment.refunded');
    }
});

test('filtro por currency filtra bien', async ({ page }) => {
    await page.getByPlaceholder('Currency').fill('USD');
    await page.getByRole('button', { name: 'Filtros' }).click();
    await page.waitForTimeout(1000);
    const rows = page.locator('table tbody tr');
    const count = await rows.count();
    for (let i = 0; i < count; i++) {
        await expect(rows.nth(i).locator('td').nth(3)).toContainText('USD');
    }
});

test('filtro por user_id filtra bien', async ({ page }) => {
    await page.getByPlaceholder('User ID').fill('user_05');
    await page.getByRole('button', { name: 'Filtros' }).click();
    await page.waitForTimeout(1000);
    const rows = page.locator('table tbody tr');
    const count = await rows.count();
    for (let i = 0; i < count; i++) {
        await expect(rows.nth(i).locator('td').nth(4)).toContainText('user_05');
    }
});

test('filtro por currency,event y user_id filtra bien', async ({ page }) => {
    await page.getByPlaceholder('Currency').fill('USD');
    await page.getByPlaceholder('Event Type').fill('payment.refunded');
    await page.getByPlaceholder('User ID').fill('user_01');
    await page.getByRole('button', { name: 'Filtros' }).click();
    await page.waitForTimeout(1000);
    const rows = page.locator('table tbody tr');
    const count = await rows.count();
    for (let i = 0; i < count; i++) {
        await expect(rows.nth(i).locator('td').nth(3)).toContainText('USD');
        await expect(rows.nth(i).locator('td').nth(1)).toContainText('payment.refunded');
        await expect(rows.nth(i).locator('td').nth(4)).toContainText('user_01');
    }
});

test('Boton para exportar CSV anda bien', async ({ page }) => {
    const downloadPromise = page.waitForEvent('download');
    await page.getByRole('button', { name: 'Export CSV' }).click();
    const download = await downloadPromise;
    
    expect(download.suggestedFilename()).toBe('payments.csv');
});

test('boton logout hace logout', async ({ page }) => {

  await page.getByRole('button', { name: 'Logout' }).click();

  await page.waitForURL('/login', { timeout: 10000 });

  await expect(page).toHaveURL('/login');
});