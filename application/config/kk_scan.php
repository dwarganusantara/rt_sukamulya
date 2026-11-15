<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Local approved OCR worker only. No public AI provider is used by default.
$config['kk_ocr_endpoint'] = getenv('KK_OCR_ENDPOINT') ?: '';
$config['kk_ocr_token'] = getenv('KK_OCR_TOKEN') ?: '';
$config['kk_tesseract'] = getenv('KK_TESSERACT') ?: 'C:/Program Files/Tesseract-OCR/tesseract.exe';
$config['kk_pdftoppm'] = getenv('KK_PDFTOPPM') ?: FCPATH.'tools/ocr-bin/poppler/bin/pdftoppm.exe';
$config['kk_magick'] = getenv('KK_MAGICK') ?: 'C:/Program Files/ImageMagick-7.1.2-Q16-HDRI/magick.exe';
