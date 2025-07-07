<?php
session_start();
if (!isset($_SESSION['UserID'])) {
    header("Location: LOGIN.html");
    exit();
}

include('dbConnection.php');

// Fetch user details
$userID = $_SESSION['UserID'];
$sql = "SELECT * FROM user WHERE UserID = :userID";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':userID', $userID);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user's recipes count
$recipeCountSql = "SELECT COUNT(*) as count FROM recipes WHERE UserID = :userID";
$recipeCountStmt = $conn->prepare($recipeCountSql);
$recipeCountStmt->bindParam(':userID', $userID);
$recipeCountStmt->execute();
$recipeCount = $recipeCountStmt->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Recipe Explorer</title>
    <style>
        :root {
            --primary-color: #FF6B6B;
            --secondary-color: #4ECDC4;
            --dark-color: #292F36;
            --light-color: #F7FFF7;
            --accent-color: #FFE66D;
            --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f7;
            color: var(--dark-color);
            line-height: 1.6;
            padding: 0;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            color: var(--primary-color);
            font-size: 24px;
            font-weight: 700;
            text-decoration: none;
        }
        
        .logo-icon {
            margin-right: 12px;
            font-size: 32px;
        }
        
        .profile-container {
            display: flex;
            gap: 30px;
            margin-top: 20px;
        }
        
        .profile-sidebar {
            width: 300px;
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            height: fit-content;
        }
        
        .profile-main {
            flex: 1;
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--card-shadow);
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 40px;
            font-weight: bold;
            margin-right: 20px;
        }
        
        .profile-info h2 {
            font-size: 24px;
            margin-bottom: 5px;
            color: var(--dark-color);
        }
        
        .profile-info p {
            color: #666;
            margin-bottom: 3px;
        }
        
        .profile-role {
            display: inline-block;
            padding: 4px 12px;
            background-color: var(--accent-color);
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 5px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        
        .stat-card {
            background: #f8f8f8;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 28px;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .stat-card p {
            font-size: 14px;
            color: #666;
        }
        
        .section-title {
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            color: var(--dark-color);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .info-item {
            margin-bottom: 15px;
        }
        
        .info-item label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #555;
        }
        
        .info-item p {
            background: #f8f8f8;
            padding: 10px 15px;
            border-radius: 8px;
            word-break: break-all;
        }
        
        .edit-btn {
            display: inline-block;
            padding: 8px 20px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            margin-top: 20px;
        }
        
        .edit-btn:hover {
            background-color: #ff5252;
        }
        
        @media (max-width: 768px) {
            .profile-container {
                flex-direction: column;
            }
            
            .profile-sidebar {
                width: 100%;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <a href="INDEXX.php" class="logo">
                <span class="logo-icon">👨‍🍳</span>
                <span>Recipe Explorer</span>
            </a>
            <nav>
    <a href="INDEXX.php" style="margin-right: 15px; color: var(--dark-color); text-decoration: none;">Browse</a>
    <a href="MYRECIPE.php" style="margin-right: 15px; color: var(--dark-color); text-decoration: none;">My Recipes</a>
    <a href="PROFILE.php" style="color: var(--dark-color); text-decoration: none;">Profile</a>
</nav>

            <?php if (isset($_SESSION['UserName'])): ?>
                <div style="display: flex; align-items: center; gap: 15px; margin-left: 15px;">
                    <span style="font-weight: bold; color: var(--dark-color);">
                        👋 Hello, <?= htmlspecialchars($_SESSION['UserName']) ?>
                    </span>
                    <a href="logout.php" style="color: white; background-color: var(--primary-color); padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 14px;">
                        Logout
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>
    
    <div class="container">
        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?= strtoupper(substr($user['UserName'], 0, 1)) ?>
                    </div>
                    <div class="profile-info">
                        <h2><?= htmlspecialchars($user['UserName']) ?></h2>
                        <p><?= htmlspecialchars($user['UserEmail']) ?></p>
                        <span class="profile-role"><?= htmlspecialchars($user['UserRole']) ?></span>
                    </div>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?= $recipeCount ?></h3>
                        <p>Recipes</p>
                    </div>
                    <div class="stat-card">
                        <h3><?= $user['UserRole'] === 'Chef' ? 'Chef' : 'Member' ?></h3>
                        <p>Status</p>
                    </div>
                </div>
                
                <button class="edit-btn" onclick="editProfile()">Edit Profile</button>
            </div>
            
            <div class="profile-main">
                <h2 class="section-title">Account Information</h2>
                
                <div class="info-grid">
                    <div class="info-item">
                        <label>User ID</label>
                        <p><?= htmlspecialchars($user['UserID']) ?></p>
                    </div>
                    
                    <div class="info-item">
                        <label>Username</label>
                        <p><?= htmlspecialchars($user['UserName']) ?></p>
                    </div>
                    
                    <div class="info-item">
                        <label>Email Address</label>
                        <p><?= htmlspecialchars($user['UserEmail']) ?></p>
                    </div>
                    
                    <div class="info-item">
                        <label>Account Type</label>
                        <p><?= htmlspecialchars($user['UserRole']) ?></p>
                    </div>
                    
                </div>
                
                <h2 class="section-title" style="margin-top: 30px;">Account Security</h2>
                <button class="edit-btn" onclick="changePassword()">Change Password</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function editProfile() {
            Swal.fire({
                title: 'Edit Profile',
                html: `
                    <form id="editForm">
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Username</label>
                            <input type="text" id="username" value="<?= htmlspecialchars($user['UserName']) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ddd;">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Email</label>
                            <input type="email" id="email" value="<?= htmlspecialchars($user['UserEmail']) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ddd;">
                        </div>
                    </form>
                `,
                showCancelButton: true,
                confirmButtonText: 'Save Changes',
                preConfirm: () => {
                    const username = document.getElementById('username').value.trim();
                    const email = document.getElementById('email').value.trim();
                    
                    if (!username || !email) {
                        Swal.showValidationMessage('All fields are required');
                        return false;
                    }
                    
                    if (!/^\S+@\S+\.\S+$/.test(email)) {
                        Swal.showValidationMessage('Please enter a valid email');
                        return false;
                    }
                    
                    // In a real app, you would send this to your backend
                    return fetch('update_profile.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            return true;
                        } else {
                            throw new Error(data.message || 'Update failed');
                        }
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Request failed: ${error}`);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Success!', 'Your profile has been updated.', 'success').then(() => {
                        location.reload();
                    });
                }
            });
        }
        
        function changePassword() {
            Swal.fire({
                title: 'Change Password',
                html: `
                    <form id="passwordForm">
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">New Password</label>
                            <input type="password" id="newPass" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ddd;" required>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Confirm New Password</label>
                            <input type="password" id="confirmPass" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ddd;" required>
                        </div>
                    </form>
                `,
                showCancelButton: true,
                confirmButtonText: 'Update Password',
                preConfirm: () => {
                    const currentPass = document.getElementById('currentPass').value;
                    const newPass = document.getElementById('newPass').value;
                    const confirmPass = document.getElementById('confirmPass').value;
                    
                    if (!currentPass || !newPass || !confirmPass) {
                        Swal.showValidationMessage('All fields are required');
                        return false;
                    }
                    
                    if (newPass !== confirmPass) {
                        Swal.showValidationMessage('Passwords do not match');
                        return false;
                    }
                    
                    if (newPass.length < 6) {
                        Swal.showValidationMessage('Password must be at least 6 characters');
                        return false;
                    }
                    
                    // In a real app, you would send this to your backend
                    return fetch('change_password.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `currentPass=${encodeURIComponent(currentPass)}&newPass=${encodeURIComponent(newPass)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            return true;
                        } else {
                            throw new Error(data.message || 'Password change failed');
                        }
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Request failed: ${error}`);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Success!', 'Your password has been changed.', 'success');
                }
            });
        }
    </script>
</body>
</html>