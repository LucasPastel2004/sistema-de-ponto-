import { test } from '@playwright/test';

test('Screenshot - Email Verification Prompt', async ({ page }) => {
    test.setTimeout(30000);
    await page.goto('http://localhost:8000/mock-email-verify', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(2000);
    await page.screenshot({ path: 'C:/Users/KATIAMESQUITACAMPOS/.gemini/antigravity/brain/f4c484b6-516f-4839-a57a-48b65b203ad5/scratch/email-verify.png', fullPage: true });
});
