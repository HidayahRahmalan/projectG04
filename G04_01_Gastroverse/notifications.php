<?php
include('connect.php');
session_start();

$user_id = isset($_SESSION['UserID']) ? intval($_SESSION['UserID']) : 0;
$notifications = [];

if ($user_id) {
    $sql = "SELECT c.Comment_ID, c.Comment_Content, c.Comment_Date, c.User_ID AS commenter_id, u.User_Name, r.Recipe_Title, r.Recipe_ID 
            FROM comment c
            JOIN recipe r ON c.Recipe_ID = r.Recipe_ID
            JOIN users u ON c.User_ID = u.User_ID
            WHERE r.User_ID = ? AND c.User_ID != ? 
            ORDER BY c.Comment_Date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications - Gastro Verse</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(120deg, #fff7f0 0%, #ffe9e3 100%);
            min-height: 100vh;
        }
        .notif-glow {
            box-shadow: 0 8px 32px 0 rgba(255,107,107,.15);
        }
        .notif-badge {
            background: #ffb385;
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.2em 0.8em;
            border-radius: 9999px;
            margin-left: 0.5em;
        }
        .notif-card {
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .notif-card:hover {
            box-shadow: 0 6px 28px 0 rgba(239,68,68,.18);
            transform: translateY(-2px) scale(1.015);
        }
        .notif-author {
            color: #ff7849;
            font-weight: bold;
        }
        .notif-date {
            color: #aaa;
            font-size: 0.98rem;
            margin-left: 1em;
        }
        .notif-recipe-link {
            color: #ef4444;
            font-weight: 600;
            text-decoration: underline;
        }
        .notif-comment {
            background: #fff7f5;
            border-left: 4px solid #ffb385;
            padding: 0.7em 1em;
            margin-top: 0.6em;
            border-radius: 0 0.5em 0.5em 0;
            font-size: 1.07em;
            color: #944c2c;
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex flex-col bg-transparent">
        <!-- Header -->
        <div class="w-full px-6 py-8 bg-gradient-to-r from-orange-400 to-pink-400 shadow-lg mb-8">
            <div class="max-w-2xl mx-auto">
                <h1 class="text-3xl sm:text-4xl font-extrabold mb-1 text-white tracking-wider">All Notifications</h1>
                <p class="text-orange-100 text-lg">You’ll find all comments on your recipes here.</p>
            </div>
        </div>
        <!-- Notifications Container -->
        <div class="max-w-2xl mx-auto w-full bg-white notif-glow rounded-xl shadow-md p-6 sm:p-10 mb-12">
            <?php if ($notifications): ?>
                <?php foreach ($notifications as $notif): ?>
                <div class="notif-card mb-7 p-5 rounded-lg bg-orange-50 border border-orange-100 hover:border-orange-300">
                    <div class="flex flex-row justify-between items-center mb-1">
                        <span class="notif-author"><?php echo htmlspecialchars($notif['User_Name']); ?></span>
                        <span class="notif-date">
                            <?php 
                                $date = DateTime::createFromFormat('Y-m-d', $notif['Comment_Date']);
                                echo $date ? $date->format('d-m-Y') : htmlspecialchars($notif['Comment_Date']);
                            ?>
                        </span>
                    </div>
                    <div class="mb-1 text-gray-700">
                        commented on 
                        <a href="fullrecipe.php?recipe_id=<?php echo $notif['Recipe_ID']; ?>" class="notif-recipe-link">
                            <?php echo htmlspecialchars($notif['Recipe_Title']); ?>
                        </a>
                    </div>
                    <div class="notif-comment">
                        "<?php echo htmlspecialchars($notif['Comment_Content']); ?>"
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-gray-400 text-center py-10 text-xl font-medium">
                    <svg viewBox="0 0 24 24" fill="none" class="w-14 h-14 inline-block mb-2 opacity-60">
                        <path d="M12 5.5C9.23858 5.5 7 7.73858 7 10.5V13.5C7 13.7652 6.94732 14.0267 6.84601 14.2697L5.36652 17.8496C5.07999 18.523 5.57053 19.25 6.2931 19.25H17.7069C18.4295 19.25 18.92 18.523 18.6335 17.8496L17.154 14.2697C17.0527 14.0267 17 13.7652 17 13.5V10.5C17 7.73858 14.7614 5.5 12 5.5Z" stroke="#ffb385" stroke-width="1.3"/>
                        <ellipse cx="12" cy="21" rx="2.5" ry="1" fill="#ffb385" fill-opacity="0.3"/>
                    </svg>
                    <br>
                    No notifications yet.
                </div>
            <?php endif; ?>
            <a href="home.php" class="mt-8 inline-block px-6 py-2 rounded-lg bg-orange-100 hover:bg-orange-200 text-orange-600 font-semibold text-base transition-all duration-150 shadow-sm">&larr; Back to Home</a>
        </div>
    </div>
</body>
</html>