<?php
session_start();
include("connect.php");

// Only allow admin users (uncomment if you have roles)
// if ($_SESSION['UserRole'] !== 'admin') { header("Location: landingpage.php"); exit(); }

// Only select Recipe_Title from audit_trail to avoid ambiguity!
$query = "
    SELECT 
        at.ID,
        at.User_ID,
        at.Action,
        at.Recipe_ID,
        at.Recipe_Title,
        at.Details,
        at.Timestamp,
        u.User_Name
    FROM audit_trail at
    LEFT JOIN users u ON at.User_ID = u.User_ID
    ORDER BY at.Timestamp DESC
";
$result = $conn->query($query);

$logQuery = "
    SELECT ul.Log_ID, ul.Log_Date, ul.Log_Day, ul.Log_RecipeID, ul.Log_RecipeTitle, u.User_Name
    FROM upload_log ul
    LEFT JOIN users u ON ul.User_ID = u.User_ID
    ORDER BY ul.Log_Date DESC, ul.Log_ID DESC
";
$logResult = $conn->query($logQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Audit Trail | Gastroverse</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f6f7fb;
            margin: 0;
            padding: 0;
        }
        .admin-header {
            background: linear-gradient(135deg, #ff6b6b, #ffa726);
            color: white;
            padding: 2rem 1rem;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .admin-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2.5rem;
            letter-spacing: 1px;
        }
        .admin-header p {
            margin: 0;
            opacity: 0.95;
        }
        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 12px 40px rgba(255,107,107,0.13), 0 1.5px 9px rgba(0,0,0,0.07);
        }
        .audit-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 0 0 1px #eee;
        }
        .audit-table th, .audit-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        .audit-table th {
            background: #ff6b6b;
            color: white;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .audit-table tr:last-child td {
            border-bottom: none;
        }
        .audit-table td.action-add { color: #23ad5c; font-weight: 600; }
        .audit-table td.action-edit { color: #ffa726; font-weight: 600;}
        .audit-table td.action-delete { color: #e53935; font-weight: 600;}
        .audit-table td.user {font-weight: bold; color: #667eea;}
        .audit-table td.recipe {font-style: italic;}
        .audit-table td.details {font-size: .97rem;}
        .audit-table td.time {font-size: .93rem; color: #888;}
        .no-records {
            padding: 2rem;
            text-align: center;
            color: #aaa;
            font-size: 1.2rem;
        }
        @media (max-width: 900px) {
            .admin-container { padding: 1rem;}
            .audit-table th, .audit-table td { padding: 0.8rem;}
        }
        @media (max-width: 600px) {
            .admin-header h1 { font-size: 1.6rem;}
            .admin-container { padding: 0.5rem;}
            .audit-table th, .audit-table td { padding: 0.5rem; font-size: 0.93rem;}
            .audit-table th { font-size: 1rem;}
        }
        .logout-btn {
            float: right;
            margin-top: -2.5rem;
            margin-right: 1.2rem;
            background: #fff;
            color: #ff6b6b;
            border: 1.5px solid #ff6b6b;
            border-radius: 24px;
            padding: 0.5rem 1.3rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .logout-btn:hover {
            background: #ff6b6b;
            color: #fff;
        }
        .audit-table td.action-add { color: #23ad5c; font-weight: 600; }
        .audit-table td.action-update { color: #3498db; font-weight: 600; }
        .audit-table td.action-edit { color: #3498db; font-weight: 600; }
        .audit-table td.action-delete { color: #e53935; font-weight: 600; }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>Gastroverse Admin – Audit Trail</h1>
        <p>View all user activity: recipe add/edit/delete and more.</p>
        <form style="display:inline;" method="post" action="logout.php"><button class="logout-btn" type="submit">Logout</button></form>
    </div>
    <div class="admin-container">
        <h2 style="margin-top:0;">User Actions History</h2>
        <table class="audit-table">
            <tr>
                <th>#</th>
                <th>User</th>
                <th>Action</th>
                <th>Recipe</th>
                <th>Details</th>
                <th>Time</th>
            </tr>
            <?php
            if ($result && $result->num_rows > 0):
                $n = 1;
                while($row = $result->fetch_assoc()):
                    $actionClass = "action-".strtolower($row['Action']);
                    $details = $row['Details'];
if ($json = json_decode($details, true)) {
    $pretty = [];
    if (isset($json['Recipe_Title'])) {
        $pretty[] = "<strong>Title:</strong> " . htmlspecialchars($json['Recipe_Title']);
    }
    if (isset($json['Recipe_Cuisine'])) {
        $pretty[] = "<strong>Cuisine:</strong> " . htmlspecialchars($json['Recipe_Cuisine']);
    }
    if (isset($json['Recipe_Dietary'])) {
        $pretty[] = "<strong>Dietary:</strong> " . htmlspecialchars($json['Recipe_Dietary']);
    }
    if (isset($json['Recipe_Description'])) {
        $pretty[] = "<strong>Description:</strong> " . htmlspecialchars($json['Recipe_Description']);
    }
    // Show any other fields in Details (optional)
    foreach ($json as $key => $val) {
        if (!in_array($key, ['Recipe_Title','Recipe_Cuisine','Recipe_Dietary','Recipe_Description'])) {
            $pretty[] = "<strong>" . htmlspecialchars(str_replace('_', ' ', $key)) . ":</strong> " . htmlspecialchars(is_scalar($val) ? $val : json_encode($val));
        }
    }
    $details = implode(' | ', $pretty);
} else {
    if (is_string($details)) {
        $details = htmlspecialchars($details);
    } elseif (is_array($details)) {
        $details = htmlspecialchars(json_encode($details));
    } else {
        $details = '-';
    }
}
            ?>
            <tr>
                <td><?php echo $n++; ?></td>
                <td class="user"><?php echo htmlspecialchars($row['User_Name'] ?? 'Unknown'); ?></td>
                <td class="<?php echo $actionClass; ?>"><?php echo ucfirst($row['Action']); ?></td>
                <td class="recipe"><?php echo htmlspecialchars($row['Recipe_Title'] ?? '-'); ?></td>
                <td class="details"><?php echo $details ?: '-'; ?></td>
                <td class="time"><?php echo htmlspecialchars(date('d-m-Y H:i:s', strtotime($row['Timestamp']))); ?></td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="6" class="no-records">No audit records found.</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <div class="upload-log-container admin-container">
    <h2 style="margin-top:0;">Recipe Upload Logs</h2>
    <table class="audit-table">
        <tr>
            <th>#</th>
            <th>User</th>
            <th>Recipe Title</th>
            <th>Recipe ID</th>
            <th>Date</th>
            <th>Day</th>
        </tr>
        <?php
        if ($logResult && $logResult->num_rows > 0):
            $i = 1;
            while ($log = $logResult->fetch_assoc()):
        ?>
        <tr>
            <td><?php echo $i++; ?></td>
            <td class="user"><?php echo htmlspecialchars($log['User_Name'] ?? 'Unknown'); ?></td>
            <td class="recipe"><?php echo htmlspecialchars($log['Log_RecipeTitle']); ?></td>
            <td><?php echo htmlspecialchars($log['Log_RecipeID']); ?></td>
            <td class="time"><?php echo htmlspecialchars(date('d-m-Y', strtotime($log['Log_Date']))); ?></td>
            <td><?php echo htmlspecialchars($log['Log_Day']); ?></td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="6" class="no-records">No upload logs found.</td></tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
<?php $conn->close(); ?>