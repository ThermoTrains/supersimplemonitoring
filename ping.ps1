while (1) {
    $hostname = hostname
    $proc = (Get-WmiObject -class win32_processor -EA SilentlyContinue | Measure-Object -property LoadPercentage -Average | Select Average | % {$_.Average / 100})
    $mem = Get-WmiObject win32_OperatingSystem -EA SilentlyContinue 
    $mem = (($mem.TotalVisibleMemorySize - $mem.FreePhysicalMemory) / $mem.TotalVisibleMemorySize)
    $disk = Get-PSDrive C | Select-Object Free | ForEach-Object {$_.Free}

    $stats = @{
        "hostname" = $hostname
        "cpu"      = $proc
        "memory"   = $mem
        "disk"     = $disk
    } | ConvertTo-Json

    Invoke-WebRequest -Uri https://monitoring.sebastianhaeni.ch/api/ping/ -Method Post -Body $stats
    
    Start-Sleep -Seconds 300 # 5 Minutes
}
