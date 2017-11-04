<#
.SYNOPSIS
    Collects a bunch of monitoring data and sends it to a remote REST API server.
#>

# For this to work, Open Hardware Monitor needs to be running on the system.
# Download here: http://openhardwaremonitor.org/

$temp = Get-WmiObject -Namespace "root/OpenHardwareMonitor" Sensor `
    | Where-Object {$_.SensorType -eq 'Temperature' -and $_.Name -like 'CPU Core #*'} `
    | Select-Object Value `
    | ForEach-Object {$_.Value}
$proc = Get-WmiObject -Namespace "root/OpenHardwareMonitor" Sensor `
    | Where-Object {$_.SensorType -eq 'Load' -and $_.Name -like 'CPU Core #*'} `
    | Select-Object Value `
    | ForEach-Object {$_.Value / 100}
$mem  = Get-WmiObject -Namespace "root/OpenHardwareMonitor" Sensor `
    | Where-Object {$_.SensorType -eq 'Load' -and $_.Name -eq 'Memory'} `
    | Select-Object Value `
    | ForEach-Object {$_.Value / 100}
$disk = Get-PSDrive C `
    | Select-Object Free `
    | ForEach-Object {$_.Free}

$stats = @{
    "hostname" = hostname
    "cpu" = $proc
    "temperatures" = $temp
    "memory" = $mem
    "disk" = $disk
} | ConvertTo-Json

Invoke-WebRequest -Uri https://monitoring.yourcompany.com/api/ping/ -Method Post -Body $stats