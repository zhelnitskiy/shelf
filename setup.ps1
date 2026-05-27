$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

param(
    [string]$Project,
    [string]$Port,
    [switch]$NonInteractive
)

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

function Get-SetupMarker {
    return '.setup-complete'
}

function Set-EnvValue {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Key,
        [Parameter(Mandatory = $true)]
        [string]$Value
    )

    $envFile = '.env'
    $lines = @()

    if (Test-Path $envFile) {
        $lines = Get-Content $envFile
    }

    $updated = $false

    for ($i = 0; $i -lt $lines.Count; $i++) {
        if ($lines[$i] -match "^$([regex]::Escape($Key))=") {
            $lines[$i] = "$Key=$Value"
            $updated = $true
        }
    }

    if (-not $updated) {
        $lines += "$Key=$Value"
    }

    Set-Content -Path $envFile -Value $lines
}

function Sync-AppPortEnv {
    if ([string]::IsNullOrWhiteSpace($env:APP_PORT)) {
        return
    }

    Set-EnvValue -Key 'APP_PORT' -Value $env:APP_PORT
    Set-EnvValue -Key 'APP_URL' -Value "http://localhost:$($env:APP_PORT)"
}

function Sync-ComposeProjectEnv {
    if ([string]::IsNullOrWhiteSpace($env:COMPOSE_PROJECT_NAME)) {
        return
    }

    Set-EnvValue -Key 'COMPOSE_PROJECT_NAME' -Value $env:COMPOSE_PROJECT_NAME
}

function Wait-ForMySql {
    $elapsed = 0

    while ($elapsed -le 60) {
        & docker compose exec -T mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysqladmin ping -h 127.0.0.1 -uroot --silent' *> $null

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

if (-not [string]::IsNullOrWhiteSpace($Project)) {
    $env:COMPOSE_PROJECT_NAME = $Project
}

if (-not [string]::IsNullOrWhiteSpace($Port)) {
    $env:APP_PORT = $Port
}

$setupMarker = Get-SetupMarker

if (Test-Path $setupMarker) {
    Write-Host 'Project is already initialized.'
    Write-Host 'Use make up or make restart instead.'
    exit 0
}

if (-not (Test-Path .env)) {
    Copy-Item .env.example .env
}

Sync-AppPortEnv

if (-not $NonInteractive.IsPresent) {
    if ([string]::IsNullOrWhiteSpace($env:COMPOSE_PROJECT_NAME)) {
        $answer = Read-Host 'Compose project name ("shelf")'
        if (-not [string]::IsNullOrWhiteSpace($answer)) {
            $env:COMPOSE_PROJECT_NAME = $answer
        }
    }

    if ([string]::IsNullOrWhiteSpace($env:APP_PORT)) {
        $currentPort = Get-AppPort
        $answer = Read-Host "App port ($currentPort)"
        if (-not [string]::IsNullOrWhiteSpace($answer)) {
            $env:APP_PORT = $answer
        }
    }
}

Sync-ComposeProjectEnv
Sync-AppPortEnv

Invoke-External -Command @('docker', 'compose', 'up', '-d', '--build')
Invoke-External -Command @('docker', 'compose', 'exec', '-T', 'app', 'sh', '-c', 'mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/sessions storage/framework/testing storage/framework/views storage/logs storage/api-docs && chown -R www-data:www-data bootstrap/cache storage/framework storage/logs storage/api-docs && chmod -R ug+rwX bootstrap/cache storage/framework storage/logs storage/api-docs')

Wait-ForMySql

Invoke-External -Command @('docker', 'compose', 'exec', '-T', 'app', 'composer', 'install', '--no-interaction', '--prefer-dist')
Invoke-External -Command @('docker', 'compose', 'exec', '-T', 'app', 'php', 'artisan', 'key:generate', '--force')
Invoke-External -Command @('docker', 'compose', 'exec', '-T', 'app', 'php', 'artisan', 'migrate:fresh', '--seed', '--force')
Invoke-External -Command @('docker', 'compose', 'exec', '-T', 'app', 'php', 'artisan', 'l5-swagger:generate')

New-Item -ItemType File -Path $setupMarker -Force | Out-Null

$appPort = Get-AppPort
Write-Host "Shelf is ready: http://localhost:$appPort/"
