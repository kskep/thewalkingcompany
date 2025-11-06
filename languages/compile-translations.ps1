#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Compile .po translation files to .mo format using msgfmt or alternative methods.
    
.DESCRIPTION
    This script attempts to compile .po files to .mo files using:
    1. msgfmt (if available)
    2. WP-CLI (if available)
    3. Instructions for manual compilation
    
.EXAMPLE
    .\compile-translations.ps1
#>

$languagesDir = $PSScriptRoot
$poFiles = Get-ChildItem -Path $languagesDir -Filter "*.po"

Write-Host "`nFound $($poFiles.Count) .po file(s) to compile:`n" -ForegroundColor Cyan

# Check if msgfmt is available
$msgfmtAvailable = Get-Command msgfmt -ErrorAction SilentlyContinue

# Check if WP-CLI is available
$wpCliAvailable = Get-Command wp -ErrorAction SilentlyContinue

if ($msgfmtAvailable) {
    Write-Host "Using msgfmt to compile translations...`n" -ForegroundColor Green
    foreach ($poFile in $poFiles) {
        $moFile = $poFile.FullName -replace '\.po$', '.mo'
        $basename = $poFile.Name
        
        Write-Host "Compiling $basename... " -NoNewline
        
        try {
            & msgfmt -o $moFile $poFile.FullName
            Write-Host "✓ Success" -ForegroundColor Green
        } catch {
            Write-Host "✗ Failed" -ForegroundColor Red
            Write-Host "  Error: $_" -ForegroundColor Red
        }
    }
} elseif ($wpCliAvailable) {
    Write-Host "Using WP-CLI to compile translations...`n" -ForegroundColor Green
    
    try {
        Set-Location -Path $languagesDir
        & wp i18n make-mo .
        Write-Host "`n✓ Compilation completed" -ForegroundColor Green
    } catch {
        Write-Host "✗ Failed" -ForegroundColor Red
        Write-Host "  Error: $_" -ForegroundColor Red
    }
} else {
    Write-Host "Neither msgfmt nor WP-CLI found.`n" -ForegroundColor Yellow
    Write-Host "To compile translation files, you have several options:`n" -ForegroundColor Yellow
    
    Write-Host "Option 1: Install Poedit (Recommended)" -ForegroundColor Cyan
    Write-Host "  1. Download from https://poedit.net/" -ForegroundColor White
    Write-Host "  2. Open the .po file in Poedit" -ForegroundColor White
    Write-Host "  3. Click Save (automatically creates .mo file)`n" -ForegroundColor White
    
    Write-Host "Option 2: Install WP-CLI" -ForegroundColor Cyan
    Write-Host "  1. Follow instructions at https://wp-cli.org/" -ForegroundColor White
    Write-Host "  2. Run: wp i18n make-mo languages/`n" -ForegroundColor White
    
    Write-Host "Option 3: Install gettext tools" -ForegroundColor Cyan
    Write-Host "  Windows: choco install gettext.tool" -ForegroundColor White
    Write-Host "  Then run: msgfmt -o languages/el_GR.mo languages/el_GR.po`n" -ForegroundColor White
    
    Write-Host "Note: The .mo file is required for translations to work in WordPress.`n" -ForegroundColor Yellow
}

Write-Host "Done!`n" -ForegroundColor Green
