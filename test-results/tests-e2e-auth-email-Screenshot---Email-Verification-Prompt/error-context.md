# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: tests\e2e\auth-email.spec.ts >> Screenshot - Email Verification Prompt
- Location: tests\e2e\auth-email.spec.ts:3:5

# Error details

```
Test timeout of 30000ms exceeded.
```

```
Error: page.goto: net::ERR_ABORTED; maybe frame was detached?
Call log:
  - navigating to "http://localhost:8000/mock-email-verify", waiting until "domcontentloaded"

```

# Test source

```ts
  1 | ﻿import { test } from '@playwright/test';
  2 | 
  3 | test('Screenshot - Email Verification Prompt', async ({ page }) => {
  4 |     test.setTimeout(30000);
> 5 |     await page.goto('http://localhost:8000/mock-email-verify', { waitUntil: 'domcontentloaded' });
    |                ^ Error: page.goto: net::ERR_ABORTED; maybe frame was detached?
  6 |     await page.waitForTimeout(2000);
  7 |     await page.screenshot({ path: 'C:/Users/KATIAMESQUITACAMPOS/.gemini/antigravity/brain/f4c484b6-516f-4839-a57a-48b65b203ad5/scratch/email-verify.png', fullPage: true });
  8 | });
  9 | 
```