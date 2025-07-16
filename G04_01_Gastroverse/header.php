<?php
include('connect.php'); 
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gastroverse</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="snap-dialog.min.js"></script>
  <link rel="stylesheet" href="snap-dialog.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
  <style>
    .logout-btn {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      color: white;
      padding: 10px 20px;
      border-radius: 12px;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }
    .logout-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s;
    }
    .logout-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4);
      background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    }
    .logout-btn:hover::before {
      left: 100%;
    }
    .logout-btn:active {
      transform: translateY(0);
      box-shadow: 0 2px 10px rgba(239, 68, 68, 0.3);
    }
    .logout-icon {
      width: 16px;
      height: 16px;
      transition: transform 0.3s ease;
    }
    .logout-btn:hover .logout-icon {
      transform: rotate(15deg) scale(1.1);
    }
    .notification-icon {
        position: relative;
        margin: 0 16px;
        cursor: pointer;
        display: inline-block;
    }
    .notification-badge {
        position: absolute;
        top: -6px;
        right: -8px;
        background: #ef4444;
        color: white;
        font-size: 0.75rem;
        font-weight: 700;
        border-radius: 9999px;
        padding: 2px 7px;
        line-height: 1;
        z-index: 10;
        display: none;
    }
    .notification-dropdown {
        display: none;
        position: absolute;
        right: 0;
        margin-top: 18px;
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 6px 24px rgba(0,0,0,0.12);
        min-width: 340px;
        max-width: 360px;
        max-height: 350px;
        overflow-y: auto;
        z-index: 1002;
    }
    .notification-dropdown.active {
        display: block;
    }
    .notification-item {
        padding: 12px 18px;
        border-bottom: 1px solid #f3f4f6;
        color: #333;
    }
    .notification-item:last-child { border-bottom: none; }
    .notification-title { font-weight: 600; color: #ef4444; }
    .notification-date { font-size: 0.85em; color: #888; float: right;}
    .notification-content { margin-top: 3px; color: #444;}
    .notification-empty {
        padding: 16px;
        text-align: center;
        color: #bbb;
    }
    @media (max-width: 640px) {
        .notification-dropdown { min-width: 92vw; max-width: 99vw; left: 2vw; right: auto; }
    }
  </style>
</head>
<body class="bg-gray-50">

  <!-- HEADER NAVBAR -->
  <header class="bg-white shadow-md relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-4">
        
        <!-- Logo -->
        <div class="text-2xl font-bold text-orange-500 tracking-wide flex items-center gap-2">
          🍳 GASTRO VERSE 
        </div>

        <!-- Navigation Links -->
        <nav class="flex space-x-6 text-gray-700 font-medium items-center relative">
          <a href="home.php" class="hover:text-orange-500 transition duration-300">Home</a>  
          <a href="recipe.php" class="hover:text-orange-500 transition duration-300">My Recipes</a>  
          <a href="user_profile.php" class="hover:text-orange-500 transition duration-300">My Profile</a>  

          <!-- Notification Bell Icon -->
          <div class="notification-icon" id="notifBell">
            <i class="fas fa-bell fa-lg"></i>
            <span class="notification-badge" id="notifBadge"></span>
            <!-- Dropdown -->
            <div class="notification-dropdown" id="notifDropdown">
                <div id="notifList"></div>
                <div style="text-align:right; margin: 8px 8px 0 0;">
                    <a href="notifications.php" style="color:#ef4444; text-decoration:underline;">See all notifications</a>
                </div>
            </div>
          </div>
        </nav>
        <a href="logout.php" class="logout-btn" id="logout-link">
            <img src="image_property/logout.png" alt="Logout" class="logout-icon">
            Log Out
        </a>
      </div>
    </div>
  </header>

  <script>
  // Live Notification Fetching
  function renderNotifications(data) {
      // Update badge
      const badge = document.getElementById('notifBadge');
      if (data.count > 0) {
          badge.textContent = data.count;
          badge.style.display = "inline-block";
      } else {
          badge.style.display = "none";
      }
      // Populate dropdown
      const notifList = document.getElementById('notifList');
      notifList.innerHTML = '';
      if (data.count > 0) {
          data.notifications.forEach(function(n) {
              notifList.innerHTML += `
                  <div class="notification-item">
                      <span class="notification-title">${n.User_Name}</span>
                      <span class="notification-date">${(n.Comment_Date ? new Date(n.Comment_Date).toLocaleDateString() : '')}</span>
                      <div class="notification-content">
                          commented on <b>
                              <a href="fullrecipe.php?recipe_id=${n.Recipe_ID}" style="color:#ef4444;">
                                  ${n.Recipe_Title}
                              </a>
                          </b>:<br>
                          "${n.Comment_Content}"
                      </div>
                  </div>
              `;
          });
      } else {
          notifList.innerHTML = `<div class="notification-empty">No new comments yet.</div>`;
      }
  }

  function pollNotifications() {
      fetch('fetch_notifications.php')
          .then(response => response.json())
          .then(data => { renderNotifications(data); })
          .catch(err => { /* Optionally handle error */ });
  }

  setInterval(pollNotifications, 10000); // Poll every 10 seconds
  pollNotifications(); // Initial load

  // Notification dropdown logic
  document.getElementById('notifBell').addEventListener('click', function(e) {
      e.stopPropagation();
      document.getElementById('notifDropdown').classList.toggle('active');
  });
  document.addEventListener('click', function() {
      document.getElementById('notifDropdown').classList.remove('active');
  });

  // Logout confirmation
  document.getElementById('logout-link').addEventListener('click', function (event) {
      event.preventDefault();
      SnapDialog().alert('Confirm Action', 'Are you sure you want to log out?', {
        enableConfirm: true,
        onConfirm: function () {
          window.location.href = 'logout.php';
        },
        enableCancel: true,
      });
    });
  </script>
</body>
</html>