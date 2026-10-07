param(
    [Parameter(Mandatory = $true)]
    [ValidateSet('local', 'server')]
    [string] $Environment
)

$source = ".env.$Environment"

if (-not (Test-Path $source)) {
    Write-Error "Missing $source"
    exit 1
}

Copy-Item $source ".env" -Force

if (Test-Path "artisan") {
    $phpCommand = Get-Command php -ErrorAction SilentlyContinue
    $phpPath = if ($phpCommand) { $phpCommand.Source } elseif (Test-Path "C:\xampp\php\php.exe") { "C:\xampp\php\php.exe" } else { $null }

    if ($phpPath) {
        & $phpPath artisan config:clear
        & $phpPath artisan cache:clear
    } else {
        Write-Warning "Could not find PHP. .env was switched, but Laravel cache was not cleared."
    }
}

Write-Host "Switched Laravel environment to $Environment"
