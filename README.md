# Super Simple Monitoring
Monitor your servers with this super simple monitoring solution

> Caution: This code was hacked together as fast as possible. As little time as possible was spent on this to get it working. This is not how I would normally code.

Deploy this simple PHP server somewhere and call `/api/ping` with a paylod.
View the collected data in the index file.

## Run it locally

Execute the following command in the project directory to see the graphs.

    php -S 127.0.0.1:8080

## Payload format

    {
        "temperatures":  [35, 37, 35, 35], // CPU temperatures in C
        "cpu": [ 0.02, 0.01, 0, 0.03], // CPU cores usage in %
        "memory":  0.121317138671875, // memory usage in %
        "hostname":  "monitored-server-host-name",
        "disk":  83384045568 // remaining bytes free
    }

## Sample ping script

A sample of how to ping this service with a Powershell Script can be found in `ping.ps1`.