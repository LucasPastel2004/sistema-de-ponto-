import { test, expect } from '@playwright/test';

test('Validação Visual - Espaçamento da Box Central de Auth', async ({ page }) => {
    test.setTimeout(60000);
    await page.goto('http://localhost:8000/login', { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('.fi-simple-main section');

    const boxStyle = await page.evaluate(() => {
        const box = document.querySelector('.fi-simple-main section');
        return window.getComputedStyle(box!).padding;
    });

    // Esperamos que o padding seja de pelo menos 48px (3rem)
    // Inicialmente vai falhar pois o padrão é menor (provavelmente 24px ou 32px)
    expect(boxStyle).toBe('48px');

    await page.screenshot({ path: 'C:/Users/KATIAMESQUITACAMPOS/.gemini/antigravity/brain/f4c484b6-516f-4839-a57a-48b65b203ad5/scratch/login-padding.png', fullPage: true });
});
