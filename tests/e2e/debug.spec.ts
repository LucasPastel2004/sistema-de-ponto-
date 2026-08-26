import { test } from '@playwright/test';
test('Capture screenshot', async ({ page }) => {
    await page.goto('http://localhost:8000/admin/login');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: 'C:/Users/KATIAMESQUITACAMPOS/.gemini/antigravity/brain/f4c484b6-516f-4839-a57a-48b65b203ad5/scratch/debug.png', fullPage: true });
});
