# db-backup.ps1 - Utility script to backup and restore the database for Laravel (MySQL/XAMPP)

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
if ($scriptDir) { Set-Location $scriptDir }

# 1. Parse .env to get database credentials
$db_host = "127.0.0.1"
$db_port = "3306"
$db_name = "savve_db"
$db_user = "root"
$db_pass = ""

if (Test-Path ".env") {
    Get-Content .env | ForEach-Object {
        $line = $_.Trim()
        if ($line -match '^([^#=]+)=(.*)$') {
            $name = $Matches[1].Trim()
            $value = $Matches[2].Trim()
            if ($value -match '^"(.*)"$') { $value = $Matches[1] }
            elseif ($value -match "^'(.*)'$") { $value = $Matches[1] }
            
            if ($name -eq "DB_HOST") { $db_host = $value }
            elseif ($name -eq "DB_PORT") { $db_port = $value }
            elseif ($name -eq "DB_DATABASE") { $db_name = $value }
            elseif ($name -eq "DB_USERNAME") { $db_user = $value }
            elseif ($name -eq "DB_PASSWORD") { $db_pass = $value }
        }
    }
}

# Find mysqldump and mysql path in XAMPP or PATH
$mysqldump = "c:\xampp3\mysql\bin\mysqldump.exe"
$mysql = "c:\xampp3\mysql\bin\mysql.exe"

if (-not (Test-Path $mysqldump)) {
    # Try default xampp path if not in xampp3
    $mysqldump = "C:\xampp\mysql\bin\mysqldump.exe"
    $mysql = "C:\xampp\mysql\bin\mysql.exe"
}

if (-not (Test-Path $mysqldump)) {
    # Fallback to system PATH
    $mysqldump = "mysqldump"
    $mysql = "mysql"
}

# Create backup directory
$backupDir = Join-Path $PSScriptRoot "database/backups"
if (-not (Test-Path $backupDir)) {
    New-Item -ItemType Directory -Force -Path $backupDir | Out-Null
}

$latestBackupFile = Join-Path $backupDir "latest_backup.sql"

function Run-Backup {
    $timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
    $backupFile = Join-Path $backupDir "backup_$timestamp.sql"
    
    Write-Host "Starting database backup for '$db_name'..." -ForegroundColor Cyan
    
    # Construct arguments
    $argsList = @("-h", $db_host, "-P", $db_port, "-u", $db_user)
    if ($db_pass -ne "") {
        $argsList += "--password=$db_pass"
    }
    
    # Use --result-file to avoid PowerShell encoding redirection issues (UTF-16)
    $argsList += "--result-file=$backupFile"
    $argsList += $db_name
    
    # Execute mysqldump
    if (Test-Path $mysqldump) {
        & $mysqldump $argsList
    } else {
        & "mysqldump" $argsList
    }
    
    if ($LASTEXITCODE -eq 0 -and (Test-Path $backupFile) -and (Get-Item $backupFile).Length -gt 0) {
        # Copy to latest_backup.sql
        Copy-Item -Path $backupFile -Destination $latestBackupFile -Force
        Write-Host "Backup completed successfully!" -ForegroundColor Green
        Write-Host "Backup file saved to: $backupFile" -ForegroundColor Green
        Write-Host "Latest backup linked to: $latestBackupFile" -ForegroundColor Green
    } else {
        Write-Error "Backup failed! Please check your database connection and credentials."
    }
}

function Run-Restore {
    if (-not (Test-Path $latestBackupFile)) {
        Write-Error "No backup file found at $latestBackupFile"
        return
    }
    
    Write-Host "Restoring database '$db_name' from latest backup..." -ForegroundColor Cyan
    
    # We run import via cmd.exe redirection to avoid PowerShell pipeline encoding issues
    # Prepare password argument
    $passArg = ""
    if ($db_pass -ne "") {
        $passArg = "--password=$db_pass"
    }
    
    $mysqlCmd = "mysql"
    if (Test-Path $mysql) {
        $mysqlCmd = "`"$mysql`""
    }
    
    # Run import
    $cmdLine = "$mysqlCmd -h $db_host -P $db_port -u $db_user $passArg $db_name < `"$latestBackupFile`""
    cmd.exe /c $cmdLine
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "Database restored successfully from latest backup!" -ForegroundColor Green
    } else {
        Write-Error "Restore failed!"
    }
}

# Command line args dispatch
if ($args.Count -gt 0) {
    $action = $args[0].ToLower()
    if ($action -eq "backup") {
        Run-Backup
    } elseif ($action -eq "restore") {
        Run-Restore
    } else {
        Write-Host "Unknown action. Usage: .\db-backup.ps1 [backup|restore]" -ForegroundColor Red
    }
} else {
    # Interactive menu if run without arguments
    Write-Host ""
    Write-Host "=== Database Backup & Restore Utility ===" -ForegroundColor Yellow
    Write-Host "1. Backup database"
    Write-Host "2. Restore database (from latest backup)"
    Write-Host "3. Exit"
    Write-Host ""
    $choice = Read-Host "Select an option (1-3)"
    
    if ($choice -eq "1") {
        Run-Backup
    } elseif ($choice -eq "2") {
        Run-Restore
    } else {
        Write-Host "Exiting."
    }
}
