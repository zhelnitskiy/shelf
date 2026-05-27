$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

$ProjectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $ProjectRoot

function Require-Command {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Name
    )

    if (-not (Get-Command $Name -ErrorAction SilentlyContinue)) {
        Write-Error "Required dependency not found: $Name"
        exit 1
    }
}

function Invoke-External {
    param(
        [Parameter(Mandatory = $true)]
        [string[]]$Command
    )

    $executable = $Command[0]
    $arguments = @()

    if ($Command.Length -gt 1) {
        $arguments = $Command[1..($Command.Length - 1)]
    }

    & $executable @arguments

    if ($LASTEXITCODE -ne 0) {
        exit $LASTEXITCODE
    }
}

function Test-DockerComposeV2 {
    & docker compose version *> $null

    if ($LASTEXITCODE -ne 0) {
        Write-Error 'Docker Compose v2 is required.'
        exit 1
    }
}

function Wait-ForMySql {
    $elapsed = 0

    while ($elapsed -le 60) {
        & docker compose exec -T mysql sh -lc 'mysqladmin ping -h 127.0.0.1 -uroot -p"$MYSQL_ROOT_PASSWORD" --silent' *> $null

        if ($LASTEXITCODE -eq 0) {
            return
        }

        Start-Sleep -Seconds 2
        $elapsed += 2
    }

    Write-Error 'MySQL did not become available within 60 seconds.'
    exit 1
}

function Get-AppPort {
    $line = Get-Content .env | Where-Object { $_ -match '^APP_PORT=' } | Select-Object -Last 1

    if (-not $line) {
        return '8000'
    }

    $value = ($line -replace '^APP_PORT=', '').Trim().Trim('"').Trim("'")

    if ([string]::IsNullOrWhiteSpace($value)) {
        return '8000'
    }

    return $value
}

Require-Command -Name 'docker'
Test-DockerComposeV2

if (Test-Path .setup-complete) {
    Write-Host 'Project is already initialized.'
    Write-Host 'Use make up or make restart instead.'
    exit 0
}

if (-not (Test-Path .env)) {
    Copy-Item .env.example .env
}

Invoke-External -Command @('docker', 'compose', 'up', '-d', '--build')

Wait-ForMySql

Invoke-External -Command @('docker', 'compose', 'exec', '-T', 'app', 'composer', 'install', '--no-interaction', '--prefer-dist')
Invoke-External -Command @('docker', 'compose', 'exec', '-T', 'app', 'php', 'artisan', 'key:generate', '--force')
Invoke-External -Command @('docker', 'compose', 'exec', '-T', 'app', 'php', 'artisan', 'migrate:fresh', '--seed', '--force')
Invoke-External -Command @('docker', 'compose', 'exec', '-T', 'app', 'php', 'artisan', 'l5-swagger:generate')

New-Item -ItemType File -Path .setup-complete -Force | Out-Null

$appPort = Get-AppPort
Write-Host "Shelf is ready: http://localhost:$appPort/"
