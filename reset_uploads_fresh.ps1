<#
  Bersihkan file unggahan warga/transaksi pada SALINAN folder aplikasi untuk hosting.
  Pasangan dari reset_data_fresh.sql. File pelindung (.htaccess, index.html) dipertahankan.

  Contoh:
    powershell -ExecutionPolicy Bypass -File reset_uploads_fresh.ps1 -Path "D:\deploy\sukamulya"
    (tambahkan -WhatIf untuk melihat daftar file tanpa menghapus)

  DIPERTAHANKAN: uploads\letters (kop/ttd/stempel), uploads\pengurus (foto Pengurus RT),
  uploads\content-dummy & gallery-dummy (konten website), gambar halaman di uploads\ (logo, banner).
#>
param(
    [Parameter(Mandatory = $true)][string]$Path,
    [switch]$WhatIf
)
$root = (Resolve-Path $Path -ErrorAction Stop).Path.TrimEnd('\')
if ($root -ieq 'C:\xampp\htdocs\sukamulya') {
    Write-Error 'Ditolak: ini folder aplikasi lokal yang sedang dipakai (DB lokal masih merujuk file-file ini). Jalankan pada salinan untuk hosting.'
    exit 1
}
if (-not (Test-Path (Join-Path $root 'application\config\config.php'))) {
    Write-Error "Ditolak: '$root' bukan folder aplikasi RT/RW Digital."
    exit 1
}
$targets = @(
    'uploads\profiles',                 # foto profil akun warga
    'uploads\proofs',                   # bukti bayar iuran & bon kas
    'application\storage\aid',          # bukti penyaluran bansos
    'application\storage\complaints',   # lampiran pengaduan
    'application\storage\documents',    # arsip dokumen
    'application\storage\purchases',    # nota belanja RT
    'application\storage\backups'       # backup lama (berisi data warga)
)
$keep = @('.htaccess', 'index.html')
$n = 0
foreach ($t in $targets) {
    $dir = Join-Path $root $t
    if (-not (Test-Path $dir)) { continue }
    Get-ChildItem $dir -File -Recurse -Force | Where-Object { $keep -notcontains $_.Name } | ForEach-Object {
        $n++
        if ($WhatIf) { Write-Output "[akan dihapus] $($_.FullName)" }
        else { Remove-Item $_.FullName -Force; Write-Output "[dihapus] $($_.FullName)" }
    }
}
Write-Output ("Selesai: {0} file {1}." -f $n, $(if ($WhatIf) { 'akan dihapus (simulasi)' } else { 'dihapus' }))
