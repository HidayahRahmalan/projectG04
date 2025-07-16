<?php
include('header.php');

$UserID = $_SESSION['UserID'];

if (!isset($_SESSION['UserID'])) {
    exit();
}

$user_stmt = $conn->prepare("SELECT *, User_ProfilePicture IS NOT NULL as has_picture FROM users WHERE User_ID = ?");
$user_stmt->bind_param("i", $UserID);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    // User ID in session doesn't match any database record
    unset($_SESSION['UserID']); // Clear invalid session
    $_SESSION['error'] = "User account not found - please login again";
    header("Location: /G04_01_Gastroverse/login.php");
    exit();
}

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    // Validate image
    $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
    $fileType = $_FILES['profile_picture']['type'];
    
    if (array_key_exists($fileType, $allowedTypes)) {
        // Check file size (max 2MB)
        if ($_FILES['profile_picture']['size'] > 2000000) {
            $_SESSION['error'] = "Image must be less than 2MB";
            header("Location: user_profile.php");
            exit();
        }
        
        $imageData = file_get_contents($_FILES['profile_picture']['tmp_name']);
        
        $updateStmt = $conn->prepare("UPDATE users SET User_ProfilePicture = ?, User_ProfilePictureType = ? WHERE User_ID = ?");
        $updateStmt->bind_param("ssi", $imageData, $fileType, $UserID);
        
        if ($updateStmt->execute()) {
            $_SESSION['message'] = "Profile picture updated successfully!";
            header("Location: user_profile.php");
            exit();
        } else {
            $_SESSION['error'] = "Error updating profile picture: " . $conn->error;
            header("Location: user_profile.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Only JPG, PNG, and GIF images are allowed";
        header("Location: user_profile.php");
        exit();
    }
}

// Handle form submission (both profile updates and image upload)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $currentPassword = $_POST['current_password'] ?? null;
    $newPassword = $_POST['new_password'] ?? null;
    
    // Validate inputs
    if (empty($name) || strlen($name) < 3) {
        $_SESSION['error'] = "Name must be at least 3 characters";
        header("Location: user_profile.php");
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
        header("Location: user_profile.php");
        exit();
    }
    
    // Handle profile picture upload if a new one was selected
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
        $fileType = $_FILES['profile_picture']['type'];
        
        if (!array_key_exists($fileType, $allowedTypes)) {
            $_SESSION['error'] = "Only JPG, PNG, and GIF images are allowed";
            header("Location: user_profile.php");
            exit();
        }
        
        if ($_FILES['profile_picture']['size'] > 2000000) {
            $_SESSION['error'] = "Image must be less than 2MB";
            header("Location: user_profile.php");
            exit();
        }
        
        $imageData = file_get_contents($_FILES['profile_picture']['tmp_name']);
        $updatePicStmt = $conn->prepare("UPDATE users SET User_ProfilePicture = ?, User_ProfilePictureType = ? WHERE User_ID = ?");
        $updatePicStmt->bind_param("ssi", $imageData, $fileType, $UserID);
        
        if (!$updatePicStmt->execute()) {
            $_SESSION['error'] = "Error updating profile picture: " . $conn->error;
            header("Location: user_profile.php");
            exit();
        }
    }
    
    // Update basic info
    $updateStmt = $conn->prepare("UPDATE users SET User_Name = ?, User_Email = ? WHERE User_ID = ?");
    $updateStmt->bind_param("ssi", $name, $email, $UserID);
    
    if (!$updateStmt->execute()) {
        $_SESSION['error'] = "Error updating profile: " . $conn->error;
        header("Location: user_profile.php");
        exit();
    }
    
    // Update password if provided
    if ($currentPassword && $newPassword) {
        // Verify current password
        $checkStmt = $conn->prepare("SELECT User_Password FROM users WHERE User_ID = ?");
        $checkStmt->bind_param("i", $UserID);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $user = $result->fetch_assoc();
        
        if (password_verify($currentPassword, $user['User_Password'])) {
            // Validate new password
            if (preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $newPassword)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $passStmt = $conn->prepare("UPDATE users SET User_Password = ? WHERE User_ID = ?");
                $passStmt->bind_param("si", $hashedPassword, $UserID);
                
                if (!$passStmt->execute()) {
                    $_SESSION['error'] = "Error updating password: " . $conn->error;
                    header("Location: user_profile.php");
                    exit();
                }
            } else {
                $_SESSION['error'] = "Password must contain at least 8 characters, one uppercase, one lowercase, one number and one special character";
                header("Location: user_profile.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Current password is incorrect";
            header("Location: user_profile.php");
            exit();
        }
    }
    
    $_SESSION['message'] = "Profile updated successfully!";
    header("Location: user_profile.php");
    exit();
}

// Fetch user data
$user_stmt = $conn->prepare("SELECT *, User_ProfilePicture IS NOT NULL as has_picture FROM users WHERE User_ID = ?");
$user_stmt->bind_param("i", $UserID);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    $_SESSION['error'] = "User not found";
    header("Location: /G04_01_Gastroverse/landingpage.php"); // or wherever you want to redirect
    exit();
}

// Get profile picture data
$profilePicture = '';
if ($user['has_picture']) {
    $profilePicture = 'data:' . $user['User_ProfilePictureType'] . ';base64,' . base64_encode($user['User_ProfilePicture']);
} else {
    $profilePicture = 'https://ui-avatars.com/api/?name=' . urlencode($user['User_Name']) . '&background=random&size=200';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo htmlspecialchars($user['User_Name']); ?></title>
    <link rel="stylesheet" href="../G04_01_Gastroverse/toastr.min.css">
    <script src="../G04_01_Gastroverse/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #1f2937;
        }

        .profile-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 1.5rem;
            margin-top: 10px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        
        .profile-header h1 {
            margin: 0;
            color: #333;
        }
        
        .profile-content {
            display: flex;
            gap: 30px;
        }
        
        .profile-picture-container {
            width: 200px;
            position: relative;
            padding: 10px;
        }
        
        .profile-picture {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .edit-picture-btn {
            position: absolute;
            bottom: 15px;
            right: 15px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #4e73df;
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .profile-details {
            flex: 1;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 15px;
        }
        
        .form-control:disabled {
            background-color: #f9f9f9;
            color: #666;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-primary {
            background-color: #4e73df;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #3a56b5;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid #4e73df;
            color: #4e73df;
        }
        
        .btn-outline:hover {
            background: #f0f4ff;
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 35px;
            color: #666;
        }
        
        .password-section {
            margin-top: 20px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 5px;
            border: 1px solid #eee;
        }
        
        .password-requirements {
            margin-top: 5px;
            font-size: 13px;
            color: #666;
        }
        
        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 3px;
        }
        
        .requirement i {
            margin-right: 5px;
            font-size: 12px;
        }
        
        .valid {
            color: #2ecc71;
        }
        
        .invalid {
            color: #e74c3c;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .hidden {
            display: none;
        }
    </style>
</head>
<body>

 <div class="profile-container">
    <div class="profile-header">
        <h2 class="section-title">My Profile</h2>
        <button type="button" class="btn btn-outline" id="editProfileBtn" aria-label="Edit profile">
            <i class="fas fa-edit"></i> Edit Profile
        </button>
    </div>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>
    <form id="profileForm" method="POST" enctype="multipart/form-data">
        <div class="profile-content">
            <div class="profile-picture-container" style="position: relative; display: inline-block;">
                <img src="<?php echo $profilePicture; ?>" class="profile-picture" id="profilePicture" alt="Profile picture" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid white; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
                <button type="button" class="edit-picture-btn" id="editPictureBtn" aria-label="Change profile picture" style="position: relative; bottom: 10px; right: 10px; width: 36px; height: 36px; border-radius: 50%; background: #4e73df; color: white; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-camera"></i>
                </button>
                <input type="file" id="profilePicInput" name="profile_picture" accept="image/*" style="display: none;" aria-hidden="true">
            </div>

            <div class="profile-details">
                <div class="form-group">
                    <label for="nameInput" class="form-label">Username</label>
                    <input type="text" class="form-control" name="name" id="nameInput" autocomplete="username"
                           value="<?php echo htmlspecialchars($user['User_Name']); ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label for="emailInput" class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" id="emailInput" autocomplete="email"
                           value="<?php echo htmlspecialchars($user['User_Email']); ?>" disabled>
                    <div id="emailError" style="color: #e74c3c; font-size: 13px; display: none;"></div>
                </div>
                
                <div class="form-group">
                    <label for="userType" class="form-label">User Type</label>
                    <input type="text" class="form-control" id="userType" name="user_type"
                           value="<?php echo htmlspecialchars(ucfirst($user['User_Role'])); ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label for="memberSince" class="form-label">Member Since</label>
                    <input type="text" class="form-control" id="memberSince" name="member_since"
                           value="<?php echo date('F Y', strtotime($user['created_at'] ?? 'now')); ?>" disabled>
                </div>
                
                <div class="password-section hidden" id="passwordSection">
                    <div class="form-group">
                        <label for="currentPassword" class="form-label">Current Password</label>
                        <div style="position: relative;">
                            <input type="password" class="form-control" name="current_password" id="currentPassword" 
                                   autocomplete="current-password" style="padding-right: 35px;">
                            <i class="fas fa-eye password-toggle" id="toggleCurrentPassword" 
                               style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"
                               aria-label="Toggle password visibility" role="button"></i>
                        </div>
                        <div id="currentPasswordError" style="color: #e74c3c; font-size: 13px; display: none;"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="newPassword" class="form-label">New Password</label>
                        <div style="position: relative;">
                            <input type="password" class="form-control" name="new_password" id="newPassword" 
                                   autocomplete="new-password" style="padding-right: 35px;">
                            <i class="fas fa-eye password-toggle" id="toggleNewPassword" 
                               style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"
                               aria-label="Toggle password visibility" role="button"></i>
                        </div>
                        <div id="newPasswordError" style="color: #e74c3c; font-size: 13px; display: none;"></div>
                        
                        <div class="password-requirements">
                            <div class="requirement">
                                <i class="fas fa-check-circle valid hidden" id="lengthValid" aria-hidden="true"></i>
                                <i class="fas fa-times-circle invalid" id="lengthInvalid" aria-hidden="true"></i>
                                <span>At least 8 characters</span>
                            </div>
                            <div class="requirement">
                                <i class="fas fa-check-circle valid hidden" id="upperValid" aria-hidden="true"></i>
                                <i class="fas fa-times-circle invalid" id="upperInvalid" aria-hidden="true"></i>
                                <span>At least 1 uppercase letter</span>
                            </div>
                            <div class="requirement">
                                <i class="fas fa-check-circle valid hidden" id="lowerValid" aria-hidden="true"></i>
                                <i class="fas fa-times-circle invalid" id="lowerInvalid" aria-hidden="true"></i>
                                <span>At least 1 lowercase letter</span>
                            </div>
                            <div class="requirement">
                                <i class="fas fa-check-circle valid hidden" id="numberValid" aria-hidden="true"></i>
                                <i class="fas fa-times-circle invalid" id="numberInvalid" aria-hidden="true"></i>
                                <span>At least 1 number</span>
                            </div>
                            <div class="requirement">
                                <i class="fas fa-check-circle valid hidden" id="specialValid" aria-hidden="true"></i>
                                <i class="fas fa-times-circle invalid" id="specialInvalid" aria-hidden="true"></i>
                                <span>At least 1 special character (@$!%*?&)</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions hidden" id="formActions">
                    <button type="submit" class="btn btn-primary" id="saveChangesBtn" aria-label="Save changes">
                        <i class="fas fa-save"></i> Save 
                    </button>
                    <button type="button" class="btn btn-danger" id="cancelEditBtn" aria-label="Cancel editing">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // DOM Elements
            const editProfileBtn = document.getElementById('editProfileBtn');
            const cancelEditBtn = document.getElementById('cancelEditBtn');
            const saveChangesBtn = document.getElementById('saveChangesBtn');
            const editPictureBtn = document.getElementById('editPictureBtn');
            const profilePicInput = document.getElementById('profilePicInput');
            const profilePicture = document.getElementById('profilePicture');
            const passwordSection = document.getElementById('passwordSection');
            const formActions = document.getElementById('formActions');
            const nameInput = document.getElementById('nameInput');
            const emailInput = document.getElementById('emailInput');
            const currentPassword = document.getElementById('currentPassword');
            const newPassword = document.getElementById('newPassword');
            const toggleCurrentPassword = document.getElementById('toggleCurrentPassword');
            const toggleNewPassword = document.getElementById('toggleNewPassword');
            
            // Password requirement icons
            const lengthValid = document.getElementById('lengthValid');
            const lengthInvalid = document.getElementById('lengthInvalid');
            const upperValid = document.getElementById('upperValid');
            const upperInvalid = document.getElementById('upperInvalid');
            const lowerValid = document.getElementById('lowerValid');
            const lowerInvalid = document.getElementById('lowerInvalid');
            const numberValid = document.getElementById('numberValid');
            const numberInvalid = document.getElementById('numberInvalid');
            const specialValid = document.getElementById('specialValid');
            const specialInvalid = document.getElementById('specialInvalid');
            
            // Error message elements
            const emailError = document.getElementById('emailError');
            const currentPasswordError = document.getElementById('currentPasswordError');
            const newPasswordError = document.getElementById('newPasswordError');
            
            // Track edit state
            let isEditing = false;
            let originalName = nameInput.value;
            let originalEmail = emailInput.value;
            
            // Toggle password visibility
            toggleCurrentPassword.addEventListener('click', function() {
                if (currentPassword.type === 'password') {
                    currentPassword.type = 'text';
                    this.classList.remove('fa-eye');
                    this.classList.add('fa-eye-slash');
                } else {
                    currentPassword.type = 'password';
                    this.classList.remove('fa-eye-slash');
                    this.classList.add('fa-eye');
                }
            });
            
            toggleNewPassword.addEventListener('click', function() {
                if (newPassword.type === 'password') {
                    newPassword.type = 'text';
                    this.classList.remove('fa-eye');
                    this.classList.add('fa-eye-slash');
                } else {
                    newPassword.type = 'password';
                    this.classList.remove('fa-eye-slash');
                    this.classList.add('fa-eye');
                }
            });
            
            // New password validation
            newPassword.addEventListener('input', function() {
                const password = this.value;
                
                // Validate length
                if (password.length >= 8) {
                    lengthValid.classList.remove('hidden');
                    lengthInvalid.classList.add('hidden');
                } else {
                    lengthValid.classList.add('hidden');
                    lengthInvalid.classList.remove('hidden');
                }
                
                // Validate uppercase
                if (/[A-Z]/.test(password)) {
                    upperValid.classList.remove('hidden');
                    upperInvalid.classList.add('hidden');
                } else {
                    upperValid.classList.add('hidden');
                    upperInvalid.classList.remove('hidden');
                }
                
                // Validate lowercase
                if (/[a-z]/.test(password)) {
                    lowerValid.classList.remove('hidden');
                    lowerInvalid.classList.add('hidden');
                } else {
                    lowerValid.classList.add('hidden');
                    lowerInvalid.classList.remove('hidden');
                }
                
                // Validate number
                if (/\d/.test(password)) {
                    numberValid.classList.remove('hidden');
                    numberInvalid.classList.add('hidden');
                } else {
                    numberValid.classList.add('hidden');
                    numberInvalid.classList.remove('hidden');
                }
                
                // Validate special character
                if (/[@$!%*?&]/.test(password)) {
                    specialValid.classList.remove('hidden');
                    specialInvalid.classList.add('hidden');
                } else {
                    specialValid.classList.add('hidden');
                    specialInvalid.classList.remove('hidden');
                }
            });
            
            // Email validation
            emailInput.addEventListener('input', function() {
                const email = this.value;
                const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                
                if (!emailRegex.test(email)) {
                    emailError.textContent = 'Please enter a valid email address (e.g., abu.aaa@gmail.com)';
                    emailError.style.display = 'block';
                } else {
                    emailError.style.display = 'none';
                }
            });
            
           // Profile picture upload - REMOVE the fetch code
            editPictureBtn.addEventListener('click', () => profilePicInput.click());

            profilePicInput.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        profilePicture.src = event.target.result;
                        // REMOVED the auto-submit code
                    };
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
            
            // Toggle edit mode
            function toggleEditMode() {
                isEditing = !isEditing;
                
                // Toggle form controls
                nameInput.disabled = !isEditing;
                emailInput.disabled = !isEditing;
                editPictureBtn.classList.toggle('hidden', !isEditing);
                passwordSection.classList.toggle('hidden', !isEditing);
                formActions.classList.toggle('hidden', !isEditing);
                
                // Change edit button text
                editProfileBtn.innerHTML = isEditing ? 
                    '<i class="fas fa-eye"></i> View Mode' : 
                    '<i class="fas fa-edit"></i> Edit Profile';
                
                // Reset form if canceling
                if (!isEditing) {
                    nameInput.value = originalName;
                    emailInput.value = originalEmail;
                    currentPassword.value = '';
                    newPassword.value = '';
                    emailError.style.display = 'none';
                    currentPasswordError.style.display = 'none';
                    newPasswordError.style.display = 'none';
                }
            }
            
            // Edit profile button
            editProfileBtn.addEventListener('click', toggleEditMode);
            
            // Cancel edit button
            cancelEditBtn.addEventListener('click', async function() {
                const { isConfirmed } = await Swal.fire({
                    title: 'Discard changes?',
                    text: 'Are you sure you want to cancel? All unsaved changes will be lost.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#4e73df',
                    cancelButtonColor: '#e74c3c',
                    confirmButtonText: 'Yes, discard',
                    cancelButtonText: 'No, keep editing'
                });
                
                if (isConfirmed) {
                    toggleEditMode();
                }
            });
            
            // Form submission
            document.getElementById('profileForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                let isValid = true;
                
                // Validate email
                const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                if (!emailRegex.test(emailInput.value)) {
                    emailError.textContent = 'Please enter a valid email address';
                    emailError.style.display = 'block';
                    isValid = false;
                }
                
                // Validate password if changed
                if (newPassword.value) {
                    if (!currentPassword.value) {
                        currentPasswordError.textContent = 'Please enter your current password';
                        currentPasswordError.style.display = 'block';
                        isValid = false;
                    }
                    
                    if (newPassword.value.length < 8) {
                        newPasswordError.textContent = 'Password must be at least 8 characters';
                        newPasswordError.style.display = 'block';
                        isValid = false;
                    } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}/.test(newPassword.value)) {
                        newPasswordError.textContent = 'Password must contain uppercase, lowercase, number, and special character';
                        newPasswordError.style.display = 'block';
                        isValid = false;
                    }
                }
                
                if (!isValid) {
                    return;
                }
                
                const { isConfirmed } = await Swal.fire({
                    title: 'Save changes?',
                    text: 'Are you sure you want to save these changes?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#4e73df',
                    cancelButtonColor: '#e74c3c',
                    confirmButtonText: 'Yes, save changes',
                    cancelButtonText: 'No, cancel'
                });
                
                if (isConfirmed) {
                    // Show loading state
                    saveChangesBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                    saveChangesBtn.disabled = true;
                    
                    // Submit the form
                    this.submit();
                }
            });
        });
    </script>
</body>
</html>