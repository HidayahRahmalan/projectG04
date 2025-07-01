<?php
function calculateElectricityCharges($voltage, $current, $rate, $hours) {
    $power = $voltage * $current; // in Watts
    $energy = ($power * $hours) / 1000; // in kWh
    $total = $energy * ($rate / 100); // in RM
    
    return [
        'power' => $power,
        'energy' => $energy,
        'total' => $total
    ];
}

$voltage = isset($_POST['voltage']) ? floatval($_POST['voltage']) : 0;
$current = isset($_POST['current']) ? floatval($_POST['current']) : 0;
$rate = isset($_POST['rate']) ? floatval($_POST['rate']) : 0;

// Calculate for different time periods
$hourly = calculateElectricityCharges($voltage, $current, $rate, 1);
$daily = calculateElectricityCharges($voltage, $current, $rate, 24);
$weekly = calculateElectricityCharges($voltage, $current, $rate, 24*7);
$monthly = calculateElectricityCharges($voltage, $current, $rate, 24*30);
$yearly = calculateElectricityCharges($voltage, $current, $rate, 24*365);

// Calculate hourly breakdown for 24 hours
$hourly_breakdown = [];
for ($i = 1; $i <= 24; $i++) {
    $hourly_breakdown[$i] = calculateElectricityCharges($voltage, $current, $rate, $i);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detailed Calculation Results</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; padding-top: 20px; }
        .card { box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-header { font-weight: bold; }
        .result-table th { background-color: #f1f1f1; position: sticky; top: 0; }
        .highlight { background-color: #e9f7ef; }
        .hourly-table { max-height: 400px; overflow-y: auto; }
        .summary-card { border-left: 4px solid #28a745; }
        .hourly-card { border-left: 4px solid #17a2b8; }
        .badge-hour { font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card summary-card">
            <div class="card-header bg-success text-white">
                <h3 class="text-center">Electricity Consumption Summary</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Input Parameters</h5>
                        <ul class="list-group mb-4">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Voltage
                                <span class="badge badge-primary"><?php echo $voltage; ?> V</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Current
                                <span class="badge badge-primary"><?php echo $current; ?> A</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Rate
                                <span class="badge badge-primary"><?php echo $rate; ?> sen/kWh</span>
                            </li>
                            <li class="list-group-item list-group-item-info d-flex justify-content-between align-items-center">
                                <strong>Power</strong>
                                <span class="badge badge-success"><?php echo number_format($hourly['power'], 2); ?> Watt</span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5>Consumption Summary</h5>
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Period</th>
                                    <th>Energy (kWh)</th>
                                    <th>Cost (RM)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Daily (24h)</td>
                                    <td><?php echo number_format($daily['energy'], 3); ?></td>
                                    <td><?php echo number_format($daily['total'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td>Weekly</td>
                                    <td><?php echo number_format($weekly['energy'], 3); ?></td>
                                    <td><?php echo number_format($weekly['total'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td>Monthly</td>
                                    <td><?php echo number_format($monthly['energy'], 3); ?></td>
                                    <td><?php echo number_format($monthly['total'], 2); ?></td>
                                </tr>
                                <tr class="table-success">
                                    <td>Yearly</td>
                                    <td><?php echo number_format($yearly['energy'], 3); ?></td>
                                    <td><?php echo number_format($yearly['total'], 2); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card hourly-card">
            <div class="card-header bg-info text-white">
                <h3 class="text-center">Hourly Breakdown (24 Hours)</h3>
            </div>
            <div class="card-body">
                <div class="hourly-table">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>Hour</th>
                                <th>Cumulative Hours</th>
                                <th>Energy (kWh)</th>
                                <th>Cost (RM)</th>
                                <th>Hourly Cost (RM)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hourly_breakdown as $hour => $data): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-hour badge-primary"><?php echo $hour; ?></span>
                                </td>
                                <td><?php echo $hour; ?></td>
                                <td><?php echo number_format($data['energy'], 5); ?></td>
                                <td><?php echo number_format($data['total'], 5); ?></td>
                                <td>
                                    <?php if ($hour == 1): ?>
                                        <?php echo number_format($data['total'], 5); ?>
                                    <?php else: ?>
                                        <?php echo number_format($data['total'] - $hourly_breakdown[$hour-1]['total'], 5); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-primary btn-lg">New Calculation</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>