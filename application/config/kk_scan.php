<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Local approved OCR worker only. No public AI provider is used by default.
$config['kk_ocr_endpoint'] = getenv('KK_OCR_ENDPOINT') ?: '';
$config['kk_ocr_token'] = getenv('KK_OCR_TOKEN') ?: '';
$config['kk_tesseract'] = getenv('KK_TESSERACT') ?: '/usr/bin/tesseract';
$config['kk_pdftoppm'] = getenv('KK_PDFTOPPM') ?: '/usr/bin/pdftoppm';
$config['kk_magick'] = getenv('KK_MAGICK') ?: '/usr/bin/convert';