import { test, expect } from '@playwright/test';

test('Validação Visual - Fundo das Telas de Auth', async ({ page }) => {
    // Definir timeout maior para garantir carregamento em dev
    test.setTimeout(60000);

    // Goto sem esperar networkidle
    await page.goto('http://localhost:8000/login', { waitUntil: 'domcontentloaded' });
    
    // Esperar pelo menos o body carregar
    await page.waitForSelector('body');

    const htmlContent = await page.content();
    
    expect(htmlContent).not.toContain('#00509E');
    expect(htmlContent).toContain('#111111');
    expect(htmlContent).toContain('#222222');

    // Capturar o screenshot
    await page.screenshot({ path: 'C:/Users/KATIAMESQUITACAMPOS/.gemini/antigravity/brain/f4c484b6-516f-4839-a57a-48b65b203ad5/scratch/login-dark.png', fullPage: true });
});
