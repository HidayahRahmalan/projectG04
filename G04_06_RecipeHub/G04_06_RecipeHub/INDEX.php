<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Recipe Explorer</title>
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
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
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

        .nav-button.primary:hover {
            background-color: #ff5252;
        }

        .nav-button.secondary:hover {
            background-color: #3dbeb6;
        }

        .hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 70vh;
            padding: 0 20px;
            text-align: center;
        }

        .home-section {
            max-width: 800px;
            padding: 50px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
            backdrop-filter: blur(5px);
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .home-section h1 {
            font-size: 2.8em;
            margin-bottom: 20px;
            color: var(--primary-color);
            line-height: 1.2;
        }

        .home-section p {
            font-size: 1.3em;
            color: var(--text-dark);
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-container {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .cta-button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 16px 35px;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 4px 8px rgba(255, 107, 107, 0.3);
        }

        .cta-button.secondary {
            background-color: var(--secondary-color);
            box-shadow: 0 4px 8px rgba(78, 205, 196, 0.3);
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .cta-button.primary:hover {
            background-color: #ff5252;
        }

        .cta-button.secondary:hover {
            background-color: #3dbeb6;
        }

        footer {
            text-align: center;
            padding: 30px;
            color: var(--text-dark);
            font-size: 0.9em;
            margin-top: auto;
        }

        @media (max-width: 768px) {
            header h1 {
                font-size: 2em;
            }
            
            .home-section {
                padding: 30px;
            }
            
            .home-section h1 {
                font-size: 2em;
            }
            
            .home-section p {
                font-size: 1.1em;
            }
            
            .cta-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .cta-button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1><span class="logo-icon">👨‍🍳</span>Recipe Explorer</h1>
        <p class="tagline">Discover culinary delights from around the world</p>
    </header>

    <nav>
        <a href="OURTEAM.php" class="nav-button secondary">Our Team</a>
        <a href="LOGIN.html" class="nav-button primary">Login</a>
    </nav>

    <div class="hero">
        <section class="home-section">
            <h1>Discover, Create, and Share Amazing Recipes</h1>
            <p>Join our community of food enthusiasts to explore thousands of chef-approved recipes, save your favorites, and share your own culinary creations with the world.</p>
            <div class="cta-container">
                <a href="REGISTER.html" class="cta-button primary">Get Started</a>
 
            </div>
        </section>
    </div>

    <footer>
        &copy; 2023 Recipe Explorer. All rights reserved.
    </footer>
</body>
</html>