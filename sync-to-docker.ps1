# PowerShell script to sync local files to Docker volume
# Run this after making changes to keep Docker container updated

Write-Host "🔄 Syncing local files to Docker volume..." -ForegroundColor Yellow

# Get the container name
$containerName = "storeall-app-1"

# Check if container is running
$containerStatus = docker ps --filter "name=$containerName" --format "{{.Status}}"
if (-not $containerStatus) {
    Write-Host "❌ Container $containerName is not running. Starting containers..." -ForegroundColor Red
    docker-compose up -d
    Start-Sleep -Seconds 10
}

# Sync files to container
Write-Host "📁 Copying files to container..." -ForegroundColor Cyan
docker cp . $containerName:/var/www/html/

Write-Host "✅ Sync completed!" -ForegroundColor Green
Write-Host "🌐 Your application is available at: http://localhost:8080" -ForegroundColor Blue

