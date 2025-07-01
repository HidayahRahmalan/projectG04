<?php
// This function takes two numbers and returns their sum
function addNumbers($a, $b) {
    return $a + $b;
}

// Example usage
$result = addNumbers(5, 10);
echo "The sum is: " . $result;
?>

//crete a simple HTML form
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Simple Form</title>
    <style>
        body { font-family: Arial, sans-serif; }
        form { max-width: 300px; margin: auto; }
        label, input { display: block; margin-bottom: 10px; }
        input[type="submit"] { width: 100%; }
    </style>
</head>
<body>
    <form action="test2.php" method="post">
        <label for="number1">Number 1:</label>
        <input type="number" id="number1" name="number1" required>

        <label for="number2">Number 2:</label>
        <input type="number" id="number2" name="number2" required>

        <input type="submit" value="Add Numbers">
    </form>
    <?php
    // Check if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get the numbers from the form
        $number1 = isset($_POST['number1']) ? floatval($_POST['number1']) : 0;
        $number2 = isset($_POST['number2']) ? floatval($_POST['number2']) : 0;

        // Call the function to add numbers
        $sum = addNumbers($number1, $number2);

        // Display the result
        echo "<h3>The sum of $number1 and $number2 is: $sum</h3>";
    }
    ?>
</body>