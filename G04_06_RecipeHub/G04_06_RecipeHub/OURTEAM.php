<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Team | Recipe Explorer</title>
    <style>
        :root {
            --primary-color: #FF6B6B;
            --secondary-color: #4ECDC4;
            --dark-color: #292F36;
            --light-color: #F7FFF7;
            --accent-color: #FFE66D;
            --text-light: #F7FFF7;
            --text-dark: #292F36;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-color);
            color: var(--dark-color);
            background-image: url('https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-1.2.1&auto=format&fit=crop&w=1600&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-blend-mode: overlay;
            background-color: rgba(247, 255, 247, 0.9);
            min-height: 100vh;
            line-height: 1.6;
        }

        header {
            background-color: rgba(255, 107, 107, 0.9);
            padding: 30px 20px;
            text-align: center;
            color: var(--text-light);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 10;
        }

        header h1 {
            font-size: 2.8em;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }

        .logo-icon {
            margin-right: 15px;
            font-size: 1.5em;
        }

        .tagline {
            font-size: 1.2em;
            opacity: 0.9;
            font-weight: 300;
        }

        nav {
            text-align: right;
            margin: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        .nav-button {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 600;
            box-shadow: 0 4px 8px rgba(255, 107, 107, 0.3);
            border: none;
            cursor: pointer;
            font-size: 1em;
            font-family: inherit;
        }

        .nav-button.secondary {
            background-color: var(--secondary-color);
            box-shadow: 0 4px 8px rgba(78, 205, 196, 0.3);
        }

        .nav-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .team-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .team-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .team-header h2 {
            font-size: 2.5em;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .team-header p {
            font-size: 1.2em;
            color: var(--text-dark);
            max-width: 700px;
            margin: 0 auto;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .team-card {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            text-align: center;
            padding-bottom: 20px;
        }

        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .team-img {
    width: 100%;               /* Stretch to card width */
    aspect-ratio: 3 / 4;       /* Maintain passport ratio (3.5cm x 4.5cm) */
    object-fit: cover;         /* Fill the space without distortion */
    border-bottom: 5px solid var(--primary-color); /* Decorative line */
    display: block;
}



        .team-info {
            padding: 20px;
        }

        .team-name {
            font-size: 1.5em;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .team-role {
            color: var(--secondary-color);
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        .team-bio {
            color: var(--text-dark);
            margin-bottom: 20px;
            font-size: 0.95em;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }

        .social-link:hover {
            background-color: var(--primary-color);
            transform: scale(1.1);
        }

        .values-section {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 40px;
            margin: 80px auto;
            max-width: 1000px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .values-section h3 {
            text-align: center;
            font-size: 2em;
            color: var(--primary-color);
            margin-bottom: 30px;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
        }

        .value-card {
            text-align: center;
            padding: 20px;
        }

        .value-icon {
            font-size: 2.5em;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }

        .value-title {
            font-size: 1.3em;
            color: var(--primary-color);
            margin-bottom: 10px;
            font-weight: 600;
        }

        footer {
            text-align: center;
            padding: 30px;
            color: var(--text-dark);
            font-size: 0.9em;
            background-color: rgba(255, 255, 255, 0.9);
            margin-top: 50px;
        }

        @media (max-width: 768px) {
            .team-grid {
                grid-template-columns: 1fr;
            }
            
            .team-header h2 {
                font-size: 2em;
            }
            
            .values-section {
                padding: 30px 20px;
            }
        }

        @media (min-width: 769px) {
    .team-grid > .team-card:nth-child(4):nth-last-child(1) {
        grid-column: 2 / span 1;
        justify-self: center;
    }
}

    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <h1><span class="logo-icon">👨‍🍳</span>Recipe Explorer</h1>
        <p class="tagline">Meet the system experts behind your favorite recipes explorer</p>
    </header>

    <nav>
        <a href="LANDING.php" class="nav-button secondary">Home</a>
        <a href="LOGIN.html" class="nav-button primary">Login</a>
    </nav>

    <div class="team-container">
        <div class="team-header">
            <h2>Our Amazing Team</h2>
            <p>We're a passionate group of developers working together to bring you the best cooking explorer experience.</p>
        </div>

        <div class="team-grid">
            <!-- Team Member 1 -->
            <div class="team-card">
    <img src="UPLOADS/Adriana.gif" alt="Adriana" class="team-img">
    <div class="team-info">
        <h3 class="team-name">ADRIANA SOFEA BINTI MOHD ZABRI</h3>
        <p class="team-role">Frontend Developer</p>
        <p class="team-bio">
            Adriana combines her eye for design with frontend expertise to craft intuitive interfaces that make uploading, previewing, and interacting with multimedia content seamless and responsive across all devices.
        </p>
        <p>
            <a href="PDF/Resume_AdrianaSofea_IT_INTERN.pdf"
               download
               style="display:inline-block; margin-top:10px; background:#FF6B6B; color:#fff; padding:8px 14px; border-radius:6px; text-decoration:none;">
                📄 Download Adriana's Resume
            </a>
        </p>
    </div>
</div>


            <!-- Team Member 2 -->
<div class="team-card">
    <img src="UPLOADS/Ainhamri.gif" alt="Ainhamri" class="team-img">
    <div class="team-info">
        <h3 class="team-name">NUR AIN BINTI HAMRI</h3>
        <p class="team-role">Multimedia Handler</p>
        <p class="team-bio">
            Ainhamri combines her technical skills with a deep understanding of multimedia systems to build secure, user-friendly platforms for uploading, managing, and displaying images, audio, and video content seamlessly.
        </p>
        <p>
            <a href="PDF/Ainhamri_Resume.pdf"
               download
               style="display:inline-block; margin-top:10px; background:#FF6B6B; color:#fff; padding:8px 14px; border-radius:6px; text-decoration:none;">
                📄 Download Ainhamri's Resume
            </a>
        </p>
    </div>
</div>



            <!-- Team Member 3 -->
            <div class="team-card">
                <img src="UPLOADS/Ain.gif" alt="Ainhamri" class="team-img">
                <div class="team-info">
                    <h3 class="team-name">NUR AIN SHAFIQAH BINTI MOHD PAUZI</h3>
                    <p class="team-role">Backend Developer</p>
                    <p class="team-bio">Ain leverages her backend development expertise to design secure, efficient systems for processing, validating, and storing multimedia files, ensuring smooth integration between user uploads and database operations.</p>                   
                <p>
    <a href="PDF/INTERNSHIP_RESUME (4).pdf"
       download
       style="display:inline-block; margin-top:10px; background:#FF6B6B; color:#fff; padding:8px 14px; border-radius:6px; text-decoration:none;">
        📄 Download Ain's Resume
    </a>
</p>
                </div>
            </div>

            <!-- Team Member 4 -->
<div class="team-card">
    <img src="UPLOADS/Hadina.gif" alt="Ainhamri" class="team-img">
    <div class="team-info">
        <h3 class="team-name">HADINA SAFWA BINTI MOHD HALIM</h3>
        <p class="team-role">Database Designer</p>
        <p class="team-bio">Hana blends creative design with user-focused thinking to create interfaces that are not only beautiful but also intuitive, ensuring every user enjoys a seamless recipe browsing and submission experience.</p>
     <p>
            <a href="PDF/RESUME(HADINA).pdf"
               download
               style="display:inline-block; margin-top:10px; background:#FF6B6B; color:#fff; padding:8px 14px; border-radius:6px; text-decoration:none;">
                📄 Download Hadina's Resume
            </a>
        </p>
    </div>
</div>

        </div>

       
        <div class="values-section">
            <h3>Our Core Values</h3>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-heart"></i></div>
                    <h4 class="value-title">Passion</h4>
                    <p>We're driven by our love for food and sharing culinary knowledge.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-lightbulb"></i></div>
                    <h4 class="value-title">Innovation</h4>
                    <p>Constantly exploring new ways to enhance your cooking experience.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-users"></i></div>
                    <h4 class="value-title">Community</h4>
                    <p>Building connections between food lovers worldwide.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-leaf"></i></div>
                    <h4 class="value-title">Sustainability</h4>
                    <p>Promoting eco-friendly cooking practices and ingredients.</p>
                </div>
            </div>
        </div>
    </div>
 <!-- Group photo -->
    <div style="margin-top: 50px; text-align: center;">
        <h3 style="margin-bottom: 20px;">Our Friendship Behind the Code</h3>
        <img src="UPLOADS/kamiGeng.jpeg" alt="Team Group Photo" style="width: 100%; max-width: 600px; border-radius: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.1);">
    </div>
</div>
    <footer>
        &copy; 2023 Recipe Explorer. All rights reserved. | <a href="#" style="color: var(--secondary-color);">Contact Us</a>
    </footer>
</body>
</html>