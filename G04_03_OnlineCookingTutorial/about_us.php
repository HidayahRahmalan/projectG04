<?php
include 'header.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>About Us - Online Cooking Tutorials</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .about-container {
            max-width: 900px;
            margin: 40px auto;
            background-color: #fffaf2;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            font-family: Arial, sans-serif;
            color: #333;
        }

        .about-container h1 {
            color: #d35400;
            margin-bottom: 20px;
            text-align: center;
        }

        .about-container p {
            line-height: 1.8;
            margin-bottom: 15px;
        }

        .developer-section {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: center;
            margin-top: 30px;
        }

        .developer-card {
            background-color: #fff8f0;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            width: 250px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        .developer-card img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid #e67e22;
        }

        .developer-card h3 {
            color: #d35400;
            margin: 10px 0 5px;
        }

        .developer-card p {
            margin: 0;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="about-container">
        <h1>About the Developer</h1>
        <p>Meet the passionate developer behind Online Cooking Tutorials. This platform was built with love for both code and cuisine, making it easy for everyone to explore, create, and share delicious recipes.</p>

        <div class="developer-section">
            <!-- Developer 1 -->
            <div class="developer-card">
                <img src="images/vanness.gif" alt="Developer Photo">
                <h3>Vannes Lam Yu Qian</h3>
                <p>Database Designer</p>
                <br></br>
                <a href="files/Vanness.pdf" target="_blank" style="background-color: #d35400; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold;">
                    More Info
                </a>
            </div>


            <!-- Add more developer cards here if needed -->

            <div class="developer-card">
                <img src="images/aqilah.jpg" alt="Developer Photo">
                <h3>Siti Nur `Aqilah Binti Rozi Halimi</h3>
                <p>Frontend Developer</p>
                <br></br>
                <a href="files/Aqilah.pdf" target="_blank" style="background-color: #d35400; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold;">
                    More Info
                </a>
                <br></br>
            </div>

            <div class="developer-card">
                <img src="images/fakhira.gif" alt="Developer Photo">
                <h3>Siti Fakhira Binti Sahidan</h3>
                <p>Backend Developer</p>
                <br></br>
                <a href="files/Fakhira.pdf" target="_blank" style="background-color: #d35400; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold;">
                    More Info
                </a>
            </div>

            <div class="developer-card">
                <img src="images/danish irwan.gif" alt="Developer Photo">
                <h3>Danish Irwan Khairudin</h3>
                <p>Multimedia Handler</p>
                <br></br>
                <a href="files/Irwan.pdf" target="_blank" style="background-color: #d35400; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold;">
                    More Info
                </a>
                <br></br>
            </div>
            
        </div>
    </div>
</body>
</html>
</div>
<?php include 'footer.php'; ?>

