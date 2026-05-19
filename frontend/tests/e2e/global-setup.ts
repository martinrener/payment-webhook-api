import { chromium } from '@playwright/test';

async function globalSetup() {
    const browser = await chromium.launch();
    const page = await browser.newPage();
    
    await page.goto('http://localhost:3000/login');
    await page.getByPlaceholder('martin@test.com').fill('martin@test.com');
    await page.getByPlaceholder('••••••••').fill('password123');
    await page.getByRole('button', { name: 'Entrar' }).click();
    await page.waitForURL('**/payments');
    
    await page.context().storageState({ path: 'tests/e2e/auth.json' });
    await browser.close();
}

export default globalSetup;