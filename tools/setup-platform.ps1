$ErrorActionPreference = 'Stop'
$taskRoot = Split-Path -Parent $PSScriptRoot
$taskPhp = 'C:\xampp\php\php.exe'
$taskUser = Read-Host 'Username Super Admin platform (akun terpisah dari Admin RT)'
$taskSecure = Read-Host 'Password baru minimal 10 karakter' -AsSecureString
$taskPointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($taskSecure)
try {
    $env:PLATFORM_OWNER_USER = $taskUser
    $env:PLATFORM_OWNER_PASSWORD = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($taskPointer)
    & $taskPhp (Join-Path $PSScriptRoot 'set-platform-owner.php')
    if ($LASTEXITCODE -ne 0) { throw 'Pembuatan akun platform tidak berhasil.' }
} finally {
    [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($taskPointer)
    Remove-Item Env:PLATFORM_OWNER_PASSWORD -ErrorAction SilentlyContinue
    Remove-Item Env:PLATFORM_OWNER_USER -ErrorAction SilentlyContinue
}
