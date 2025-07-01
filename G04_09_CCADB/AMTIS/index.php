<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electricity Consumption Calculator</title>
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2 class="text-center">Electricity Consumption Calculator</h2>
            </div>
            <div class="card-body">
                <form action="calculate.php" method="post">
                    <div class="form-group">
                        <label for="voltage">Voltage (V):</label>
                        <input type="number" step="0.01" class="form-control" id="voltage" name="voltage" required>
                        <small class="form-text text-muted">System Voltage (e.g., 240V for Malaysia)</small>
                    </div>
                    <div class="form-group">
                        <label for="current">Current (A):</label>
                        <input type="number" step="0.01" class="form-control" id="current" name="current" required>
                        <small class="form-text text-muted">Current in Amperes</small>
                    </div>
                    <div class="form-group">
                        <label for="rate">Current Rate (sen/kWh):</label>
                        <input type="number" step="0.01" class="form-control" id="rate" name="rate" required>
                        <small class="form-text text-muted">Rate in sen (e.g., 21.8 sen/kWh)</small>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Calculate</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 4 JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>