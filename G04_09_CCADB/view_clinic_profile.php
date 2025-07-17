<?php
// We can make this accessible to the public, no session start needed for now
require_once 'db_conn.php';

// 1. READ THE URL PATH QUERY
// Get the clinic ID from the URL. If it's not there, redirect.
if (!isset($_GET['id'])) {
    header("Location: location.php");
    exit();
}
$clinic_id = intval($_GET['id']);

// 2. FETCH DATA FROM DATABASE
// Get all details for this specific clinic, including the ImagePath
$stmt = $conn->prepare("SELECT ClinicName, Location, Phone, ImagePath FROM Clinic WHERE ClinicID = ?");
$stmt->bind_param("i", $clinic_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    // If no clinic with that ID exists, go back to the list
    header("Location: location.php");
    exit();
}
$clinic = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($clinic['ClinicName']); ?> - Profile</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Using the main public stylesheet -->
    <style>
        /* Some specific styles for the profile page */
        .profile-card { max-width: 800px; margin: 40px auto; }
        .profile-header { display: flex; align-items: center; gap: 30px; margin-bottom: 20px; }
        .profile-image { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; }
        .profile-info h2 { margin-bottom: 5px; }
        .profile-info p { color: #555; }
    </style>
</head>
<body>
    <!-- You can add your standard header and sidebar here -->
    
    <div class="main-content">
        <div class="content-card profile-card">
            <div class="profile-header">
                <!-- 
                    3. DYNAMIC IMAGE PATH QUERY
                    The 'src' of this image tag is not static. 
                    It's dynamically set by the 'ImagePath' value we fetched from the database.
                -->
                <img src="<?php echo htmlspecialchars($clinic['ImagePath']); ?>" alt="<?php echo htmlspecialchars($clinic['ClinicName']); ?>" class="profile-image">
                
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($clinic['ClinicName']); ?></h2>
                    <p><?php echo htmlspecialchars($clinic['Location']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($clinic['Phone']); ?></p>
                </div>
            </div>
            
            <hr>
            
            <div class="profile-body">
                <h3>About Our Clinic</h3>
                <p>Welcome to <?php echo htmlspecialchars($clinic['ClinicName']); ?>. We are dedicated to providing the best healthcare services in <?php echo htmlspecialchars($clinic['Location']); ?>. Our team of professional doctors is here to assist you with all your needs.</p>
                <a href="appointment.php?clinic_id=<?php echo $clinic_id; ?>" class="hero-button" style="margin-top:20px;">Book an Appointment Here</a>
            </div>
            <a href="location.php" class="back-button" style="margin-top: 20px;">← Back to Clinic List</a>
        </div>
    </div>
</body>
</html>