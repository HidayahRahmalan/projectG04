<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$logged_in = isset($_SESSION['userID']);
$full_name = $_SESSION['full_name'] ?? '';
?>


<!DOCTYPE html>
<html>
<head>
    <title>Cooking Tutorials</title>
    <link rel="stylesheet" href="style.css">
    <style>
        header {
            background-color: #fdf6e3;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            text-align: center;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #d35400;
            margin-bottom: 10px;
        }

        .navbar {
            background-color: #fff8f0;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px 20px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            max-width: 1000px;
            position: relative;
        }

        .nav-left,
        .nav-right {
            width: 150px; /* fixed width for spacing */
        }

        .nav-center {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .navbar a, .dropdown button {
            color: #d35400;
            background: none;
            border: none;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            padding: 10px 15px;
            transition: color 0.2s ease;
        }

        .navbar a:hover,
        .dropdown-content a:hover {
            color: #a84300;
            text-decoration: underline;
        }

        .dropdown {
            position: relative;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            top: 40px;
            background-color: #fff8f0;
            border: 1px solid #ddd;
            min-width: 180px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            z-index: 10;
        }

        .dropdown-content a {
            display: block;
            padding: 10px;
            color: #d35400;
            text-decoration: none;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .btn {
            background-color: #e67e22;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #d35400;
        }

        .notification-bell {
      position: relative;
      display: inline-block;
      cursor: pointer;
    }

    .bell-icon {
      font-size: 24px;
      color: #d35400;
    }

    .notification-count {
      position: absolute;
      top: -8px;
      right: -10px;
      background: red;
      color: white;
      font-size: 12px;
      padding: 2px 6px;
      border-radius: 50%;
    }

    .notification-dropdown {
      display: none;
      position: absolute;
      right: 0;
      background: #fff;
      min-width: 280px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      padding: 10px;
      z-index: 1000;
    }

    .notification-dropdown ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .notification-dropdown li {
      padding: 10px;
      border-bottom: 1px solid #eee;
    }

    .notification-dropdown li:last-child {
      border-bottom: none;
    }

    </style>
</head>
<body>
<header>
    <div class="logo">🍳 Online Cooking Tutorials</div>

    <div class="navbar">
        <!-- Left (empty spacer or logo if needed) -->
        <div class="nav-left"></div>

        <!-- Centered navigation -->
        <div class="nav-center">
            <a href="other_people_recipe_list.php">HOME</a>

            <div class="dropdown">
                <button>RECIPES</button>
                <div class="dropdown-content">
                    <a href="create_recipe.php">Create Recipe</a>
                    <a href="recipe_list.php">My Recipes</a>
                </div>
            </div>

            <a href="about_us.php">ABOUT US</a>
        </div>

        <div class="nav-right">
<?php if ($logged_in): ?>
    <a href="logout.php" class="btn">Logout</a>
<?php else: ?>
    <a href="signup.php" class="btn">Sign Up</a>
<?php endif; ?>
<div class="notification-bell" onclick="toggleDropdown()">
  <span class="bell-icon">🔔</span>
  <span class="notification-count" id="notification-count">0</span>
  <div class="notification-dropdown" id="notification-dropdown">
    <ul id="notification-list"></ul>
  </div>
</div>

<script>
function toggleDropdown() {
  const dropdown = document.getElementById('notification-dropdown');
  dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

// Example usage: make sure this script is included after your HTML

function formatDate(str) {
  const d = new Date(str);
  const day = String(d.getDate()).padStart(2, '0');
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const year = String(d.getFullYear()).slice(-2);
  return `${day}/${month}/${year}`;
}

function loadNotifications() {
  fetch('notifications_fetch.php')
    .then(response => response.json())
    .then(data => {
      const list = document.getElementById('notification-list');
      const count = document.getElementById('notification-count');
      list.innerHTML = '';

      if (data.success && data.notifications.length > 0) {
        count.textContent = data.notifications.length;
        // Optional: group by recipeID if you want to avoid repetition
        const grouped = {};
        data.notifications.forEach(notif => {
          if (notif.recipeID) {
            if (!grouped[notif.recipeID]) grouped[notif.recipeID] = [];
            grouped[notif.recipeID].push(notif);
          } else {
            if (!grouped['other']) grouped['other'] = [];
            grouped['other'].push(notif);
          }
        });

        Object.values(grouped).forEach(group => {
          // Only show the latest notification per recipe (or type)
          const notif = group[0];
          const li = document.createElement('li');
          li.textContent =
            `${notif.message} (${notif.recipe_title ? notif.recipe_title + ', ' : ''}${formatDate(notif.created_at)})`;
          // If there are more in the group, allow "See more"
          if (group.length > 1) {
            const seeMoreBtn = document.createElement('button');
            seeMoreBtn.textContent = `See more (${group.length - 1} more)`;
            seeMoreBtn.style.marginLeft = '10px';

            const moreList = document.createElement('ul');
            moreList.style.display = 'none';
            moreList.style.marginTop = '5px';

            group.slice(1).forEach(n => {
              const moreLi = document.createElement('li');
              moreLi.textContent =
                `${n.message} (${n.recipe_title ? n.recipe_title + ', ' : ''}${formatDate(n.created_at)})`;
              moreList.appendChild(moreLi);
            });

            seeMoreBtn.onclick = function () {
              moreList.style.display = moreList.style.display === 'none' ? 'block' : 'none';
              seeMoreBtn.textContent = moreList.style.display === 'none'
                ? `See more (${group.length - 1} more)`
                : 'Hide';
            };

            li.appendChild(seeMoreBtn);
            li.appendChild(moreList);
          }
          list.appendChild(li);
        });

      } else {
        list.innerHTML = '<li>No notifications.</li>';
        count.textContent = '0';
      }
    })
    .catch(() => {
      const list = document.getElementById('notification-list');
      const count = document.getElementById('notification-count');
      list.innerHTML = '<li>Failed to load notifications.</li>';
      count.textContent = '0';
    });
}

document.addEventListener('DOMContentLoaded', loadNotifications);

</script>

</header>
