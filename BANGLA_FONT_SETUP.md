# Bangla Font Setup with mPDF

## Problem

By default, mPDF cannot render Bangla/Bengali characters correctly — they appear as broken or disconnected glyphs. This is because Bangla is a complex script that requires **OpenType Layout (OTL)** shaping support, which must be explicitly enabled.

## Solution

Three steps are required:

### 1. Place the Font File

Copy `Nikosh.ttf` into mPDF's fonts directory:

```
vendor/mpdf/mpdf/ttfonts/Nikosh.ttf
```

> **Note:** This file lives inside `vendor/` which is not committed to git. It must be re-placed after every `composer install` on a fresh environment. Consider keeping a copy in `storage/fonts/` or a shared location.

### 2. Register the Font

Add the font registration to `vendor/mpdf/mpdf/src/Config/FontVariables.php` inside the `$fontVariables['fontdata']` array:

```php
"nikosh" => [
    'R'      => "Nikosh.ttf",
    'useOTL' => 0xFF,
],
```

> **Critical:** `useOTL => 0xFF` enables OpenType Layout support. Without it, Bangla characters will render as broken/disconnected glyphs regardless of the font used.

### 3. Configure the mPDF Instance

In `app/Services/PdfService.php`, set the default font when creating the mPDF instance:

```php
$mpdf = new Mpdf([
    'mode'         => 'utf-8',
    'default_font' => 'nikosh',
    // ...other config
]);
```

## Important Notes

- `useOTL => 0xFF` is the critical setting — this is what enables proper Bangla script shaping in mPDF.
- **SolaimanLipi does not work** with mPDF's OTL engine. It relies on Windows Uniscribe for shaping and its OpenType GSUB tables are too minimal for mPDF's internal engine.
- After placing the font, clear mPDF's font cache if characters still appear broken:
  ```
  vendor/mpdf/mpdf/tmp/mpdf/ttfontdata/
  ```
  Delete all files in that directory to force mPDF to rebuild the font cache.
- Since `vendor/` is gitignored, document this setup for any new developer onboarding to the project.
