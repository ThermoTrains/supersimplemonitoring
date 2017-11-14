<?php
date_default_timezone_set('Europe/Zurich');

$file = file_get_contents("data/stats.txt");
$lines = explode("\n", $file);
$records = [];
$dataStart = new DateTime();
$dataStart->sub(new DateInterval('P2D')); // today minus 2 days
foreach($lines as $line){
    // ignore empty lines
    if(strlen($line) === 0){
        continue;
    }

    // parse line
    $record = json_decode($line);

    // do not show data older than 2 days
    if(new DateTime($record->timestamp) < $dataStart){
        break;
    }

    // appends to records array
    $records[] = $record;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>Super Simple Monitoring</title>

    <meta name="author" content="https://sebastianhaeni.ch">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#0e0e0e">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <style>
    body {
        margin: 0;
        padding: 0;
        background: #0e0e0e;
        font-family: arial;
        color: #f0f0f0;
    }
    a { 
        color: #f0f0f0;
    }
    .container {
        margin: 20px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-template-rows: 1fr 1fr;
    }
    .text-center {
        text-align: center;
    }
    button {
        background-color: #0e0e0e;
        color: #f0f0f0;
        border: none;
        cursor: pointer;
    }
    .show-more {
        display: none;
    }
    .footer {
        margin-top: 4em;
        font-size: .7em;
        width: 100%;
    }
    @media (max-width: 1000px) {
        .container {
            grid-template-columns: 1fr;
            grid-template-rows: 1fr;
        }
    }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.19.1/moment.min.js" integrity="sha256-zG8v+NWiZxmjNi+CvUYnZwKtHzFtdO8cAKUIdB8+U9I=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.bundle.min.js" integrity="sha256-N4u5BjTLNwmGul6RgLoESPNqDFVUibVuOYhP4gJgrew=" crossorigin="anonymous"></script>
</head>
<body>

<div class="container">
    <div>
        <canvas id="chart-uptime" width="400" height="200"></canvas>
    </div>
    <div>
        <canvas id="chart-cpu" width="400" height="200"></canvas>
    </div>
    <div>
        <canvas id="chart-memory" width="400" height="200"></canvas>
    </div>
    <div>
        <canvas id="chart-disk" width="400" height="200"></canvas>
    </div>
</div>

<div class="text-center">
    <button type="button" onclick="toggleShowMore()">Show more</button>
</div>
<script>
function toggleShowMore(){
    var el = document.getElementsByClassName('show-more')[0];
    el.style.display = !el.style.display || el.style.display === 'none' ? 'block' : 'none';
}

var chartOptions = {
    legend: {
        labels: {
            fontColor: "#f0f0f0"
        }
    },
    tooltips: {
        mode: 'index',
        intersect: false
    },
    scales: {
        xAxes: [{
            type: 'time',
            time: {unit: 'hour'},
            ticks: {
                fontColor: '#f0f0f0'
            }
        }],
        yAxes: [{
            ticks: {
                suggestedMin: 0,
                suggestedMax: 100,
                fontColor: '#f0f0f0'
            },
            gridLines: {
                color: "#222",
            }
        }]
    },
    elements: {
        point: {radius: 0}
    }
};

var chartColors = {
	red: 'rgb(255, 99, 132)',
	orange: 'rgb(255, 159, 64)',
	yellow: 'rgb(255, 205, 86)',
	green: 'rgb(75, 192, 192)',
	blue: 'rgb(54, 162, 235)',
	purple: 'rgb(153, 102, 255)',
	grey: 'rgb(201, 203, 207)'
};

// Uptime
var ctx = document.getElementById("chart-uptime").getContext('2d');
var myChart = new Chart(ctx, {
    type: 'line',
    data: {
        datasets: [{
            label: 'Up Time',
            backgroundColor: chartColors.red,
            borderColor: chartColors.red,
            data: [<?php
$last = $records[count($records) - 1];

foreach(array_reverse($records) as $record){
    $timestamp = new DateTime($record->timestamp);
    $lasttime = new DateTime($last->timestamp);
    $comparetime = clone $lasttime;

    $comparetime->add(new DateInterval('PT6M')); // add 6 minutes

    $value = $timestamp < $comparetime ? 100 : 0;
    echo "{x: moment('" . $lasttime->format('Y-m-d H:i:s') . "'), y: " . $value . "},\n";

    $last = $record;
}

$lasttime = (new DateTime($last->timestamp));
echo "{x: moment('" . $lasttime->format('Y-m-d H:i:s') . "'), y: 100},\n";

$value = 100;
$lasttime->add(new DateInterval('PT6M'));
if($lasttime < new DateTime()){
    $value = 0;
}

echo "{x: moment('" . (new DateTime())->format('Y-m-d H:i:s') . "'), y: " . $value . "},\n";

            ?>],
        }]
    },
    options: chartOptions
});

// CPU
ctx = document.getElementById("chart-cpu").getContext('2d');
myChart = new Chart(ctx, {
    type: 'line',
    data: {
        datasets: [<?php
$colors = ["orange","blue","purple","grey"];

// Usage
for($i = 0; $i < count($records[0]->cpu); $i++){
    echo "{label: 'Core ".($i+1)." Load %',fill:false,backgroundColor: chartColors.". $colors[$i] .",borderColor: chartColors.". $colors[$i] .",data: [";
    foreach($records as $record){
        if(!is_array($record->cpu)){
            continue;
        }
        echo "{x: moment('" . $record->timestamp . "'), y: " . ($record->cpu[$i] * 100) . "},";
    }
    echo "]},";
}

// Temperatures
for($i = 0; $i < count($records[0]->cpu); $i++){
    echo "{label: 'Core ".($i+1)." °C',fill:false,borderDash:[3,3],backgroundColor: chartColors.". $colors[$i] .",borderColor: chartColors.". $colors[$i] .",data: [";
    foreach($records as $record){
        if(!is_array($record->temperatures)){
            continue;
        }
        echo "{x: moment('" . $record->timestamp . "'), y: " . $record->temperatures[$i] . "},";
    }
    echo "]},";
}
?>]
    },
    options: chartOptions
});

// Memory
ctx = document.getElementById("chart-memory").getContext('2d');
myChart = new Chart(ctx, {
    type: 'line',
    data: {
        datasets: [{
            label: 'Memory Usage %',
            backgroundColor: chartColors.yellow,
            borderColor: chartColors.yellow,
            data: [<?php
foreach($records as $record){
    if(!is_numeric($record->memory)){
        continue;
    }
    echo "{x: moment('" . $record->timestamp . "'), y: " . ($record->memory * 100) . "},";
}
?>],
        }]
    },
    options: chartOptions
});

// Disk
ctx = document.getElementById("chart-disk").getContext('2d');
myChart = new Chart(ctx, {
    type: 'line',
    data: {
        datasets: [{
            label: 'Free Disk Space [GB]',
            backgroundColor: chartColors.green,
            borderColor: chartColors.green,
            data: [<?php
foreach($records as $record){
    echo "{x: moment('" . $record->timestamp . "'), y: " . ($record->disk / 1024 / 1024 / 1024) . "},";
}
?>],
        }]
    },
    options: chartOptions
});
</script>

<div class="show-more">
<pre><code><?php echo $file ?></code></pre>
</div>

<p class="footer text-center">
Hacked together by <a href="https://sebastianhaeni.ch" target="_blank" noopener>Sebastian Häni</a>
</p>

</body>
</html>