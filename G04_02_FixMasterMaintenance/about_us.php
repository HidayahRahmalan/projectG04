<?php
session_start();
include 'connection.php';
include 'navbar.php';

// Protect page: only allow logged-in admin
if (!isset($_SESSION['staffID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// ===== TEAM MEMBER DATA =====
// Store all team member information in an array. This makes it easy to manage.
// **ACTION REQUIRED: Update the details below with your team's information and PDF filenames.**
$teamMembers = [
    [
        'name' => 'Azwarie Azman',
        'title' => 'Project Lead / Lead Developer',
        'image' => 'assets/images/member1.jpg',
        // CHANGED: Path to the PDF file instead of HTML content
        'resume_pdf' => 'assets/resumes/azwarie_resume.pdf' 
    ],
    [
        'name' => 'Lyana Azmi',
        'title' => 'UI/UX Designer & Frontend Dev',
        'image' => 'assets/images/member2.jpg',
        'resume_pdf' => 'assets/resumes/syazlyana_resume.pdf'
    ],
    [
        'name' => 'Fariz Rohizad',
        'title' => 'Backend Developer / Database Admin',
        'image' => 'assets/images/member3.jpg',
        'resume_pdf' => 'assets/resumes/fariz_resume.pdf'
    ],
    [
        'name' => 'Aqilah Rahmad',
        'title' => 'QA & Testing Specialist',
        'image' => 'assets/images/member4.jpg',
        'resume_pdf' => 'assets/resumes/aqilah_resume.pdf'
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>About Us - The Team</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #2c3e50, #4b6584);
            margin: 0;
            padding: 30px;
            color: #34495e;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            text-align: center;
        }
        h1 {
            color: #f39c12;
            margin-bottom: 40px;
        }
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }
        /* CHANGED: Styling the anchor tag that wraps the card */
        .member-link {
            text-decoration: none; /* Removes underline from text */
            color: inherit; /* Makes text inside inherit its parent's color */
            display: block; /* Ensures the link takes up the whole card space */
        }
        .member-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .member-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.2);
        }
        .member-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block; /* Fixes potential small gap under image */
        }
        .member-info {
            padding: 15px;
        }
        .member-info h3 {
            margin: 0 0 5px 0;
            color: #2c3e50;
        }
        .member-info p {
            margin: 0;
            color: #7f8c8d;
            font-size: 0.9em;
        }

        /* REMOVED: All modal styles are gone as they are no longer needed. */
    </style>
</head>
<body>

<div class="container">
    <h1>Meet the Development Team</h1>
    <p style="color: #ecf0f1; margin-top: -20px; margin-bottom: 40px;">Click on a team member to view their resume.</p>
    <div class="team-grid">
        <?php foreach ($teamMembers as $member): ?>
            <!--
                CHANGED: The entire card is now wrapped in an anchor (<a>) tag.
                - href points to the PDF file.
                - target="_blank" opens the PDF in a new browser tab.
                - rel="noopener noreferrer" is a security best practice for new tabs.
            -->
            <a href="<?php echo htmlspecialchars($member['resume_pdf']); ?>" target="_blank" rel="noopener noreferrer" class="member-link">
                <div class="member-card">
                    <img src="<?php echo htmlspecialchars($member['image']); ?>" alt="Photo of <?php echo htmlspecialchars($member['name']); ?>">
                    <div class="member-info">
                        <h3><?php echo htmlspecialchars($member['name']); ?></h3>
                        <p><?php echo htmlspecialchars($member['title']); ?></p>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- REMOVED: The modal HTML and all JavaScript have been deleted. -->

</body>
</html>