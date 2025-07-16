<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gastroverse - Master Your Kitchen</title>
    <link rel="stylesheet" href="../G04_01_Gastroverse/toastr.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="../G04_01_Gastroverse/toastr.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Header & Navigation */
        .header {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            z-index: 1000;
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            background: linear-gradient(135deg, #ff6b6b, #ffa726);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo::before {
            content: '👨‍🍳';
            font-size: 1.8rem;
            background: none;
            -webkit-text-fill-color: initial;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #ff6b6b;
        }

        .auth-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #ff6b6b;
            color: #ff6b6b;
        }

        .btn-outline:hover {
            background: #ff6b6b;
            color: white;
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff6b6b, #ffa726);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 107, 107, 0.3);
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%), 
                        url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600"><defs><pattern id="food" x="0" y="0" width="100" height="100" patternUnits="userSpaceOnUse"><text x="20" y="30" font-size="30" fill="rgba(255,255,255,0.1)">🍕</text><text x="60" y="70" font-size="25" fill="rgba(255,255,255,0.08)">🍝</text><text x="10" y="80" font-size="20" fill="rgba(255,255,255,0.06)">🥘</text></pattern></defs><rect width="100%" height="100%" fill="url(%23food)"/></svg>');
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text x="20" y="30" font-size="15" fill="rgba(255,255,255,0.1)">🍳</text><text x="70" y="20" font-size="12" fill="rgba(255,255,255,0.08)">🥕</text><text x="10" y="70" font-size="18" fill="rgba(255,255,255,0.06)">🧅</text><text x="60" y="80" font-size="14" fill="rgba(255,255,255,0.07)">🍅</text><text x="40" y="50" font-size="16" fill="rgba(255,255,255,0.05)">🌶️</text></svg>');
            animation: float 30s infinite linear;
        }

        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            100% { transform: translateY(-20vh) rotate(360deg); }
        }

        .hero-content {
            max-width: 800px;
            z-index: 2;
            position: relative;
        }

        .hero h1 {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0;
            animation: fadeInUp 1s ease forwards;
        }

        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0;
            animation: fadeInUp 1s ease 0.3s forwards;
        }

        .hero-buttons {
            opacity: 0;
            animation: fadeInUp 1s ease 0.6s forwards;
        }

        .hero-buttons .btn {
            margin: 0 0.5rem;
            padding: 1rem 2rem;
            font-size: 1.1rem;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Food Gallery Section */
        .food-gallery {
            padding: 3rem 2rem;
            background: #fff;
            overflow: hidden;
        }

        .gallery-container {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }

        .gallery-title {
            font-size: 2rem;
            margin-bottom: 2rem;
            color: #333;
        }

        .food-carousel {
            display: flex;
            gap: 1rem;
            animation: scroll 20s linear infinite;
            width: fit-content;
        }

        .food-item {
            min-width: 200px;
            height: 150px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            background: linear-gradient(135deg, #ff6b6b, #ffa726);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .food-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.1);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .food-item:hover::before {
            opacity: 1;
        }

        @keyframes scroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        /* Features Section */
        .features {
            padding: 5rem 2rem;
            background: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: #333;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100px;
            height: 100px;
            background: linear-gradient(45deg, transparent, rgba(255, 107, 107, 0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        .feature-card:hover::before {
            right: -30%;
            top: -30%;
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ff6b6b, #ffa726);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            position: relative;
            z-index: 2;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
            position: relative;
            z-index: 2;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
            position: relative;
            z-index: 2;
        }

        /* Recipe Showcase */
        .recipe-showcase {
            padding: 5rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .recipe-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .recipe-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .recipe-card:hover {
            transform: translateY(-5px);
        }

        .recipe-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        .recipe-card h3 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }

        .recipe-card p {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        /* Stats Section */
        .stats {
            padding: 5rem 2rem;
            background: #1a1a1a;
            color: white;
            position: relative;
        }

        .stats::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text x="10" y="20" font-size="8" fill="rgba(255,165,38,0.1)">⭐</text><text x="70" y="40" font-size="6" fill="rgba(255,165,38,0.08)">🏆</text><text x="30" y="70" font-size="10" fill="rgba(255,165,38,0.06)">💯</text><text x="80" y="80" font-size="7" fill="rgba(255,165,38,0.09)">📈</text></svg>');
            opacity: 0.5;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .stat-item {
            background: rgba(255, 107, 107, 0.1);
            padding: 2rem;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .stat-item h3 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
            color: #ffa726;
        }

        .stat-item p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* CTA Section */
        .cta {
            padding: 5rem 2rem;
            background: linear-gradient(135deg, #ff6b6b, #ffa726);
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text x="20" y="30" font-size="20" fill="rgba(255,255,255,0.1)">🎉</text><text x="70" y="60" font-size="15" fill="rgba(255,255,255,0.08)">🚀</text><text x="40" y="80" font-size="25" fill="rgba(255,255,255,0.06)">✨</text></svg>');
            animation: float 25s infinite linear reverse;
        }

        .cta h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            position: relative;
            z-index: 2;
        }

        .cta p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .cta .btn {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        /* Footer */
        .footer {
            background: #333;
            color: white;
            padding: 3rem 2rem 1rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .footer-section h3 {
            margin-bottom: 1rem;
            color: #ffa726;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-section h3::before {
            content: '🍽️';
            font-size: 1.2rem;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 0.5rem;
        }

        .footer-section a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: #ffa726;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid #555;
            color: #999;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero-buttons .btn {
                display: block;
                margin: 0.5rem 0;
            }
            
            .auth-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }

            .food-carousel {
                animation-duration: 15s;
            }
        }

        /* Scroll Animation */
        .fade-in {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.6s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: white;
            margin: 2rem;
            padding: 0;
            border-radius: 20px;
            width: 100%;
            max-width: 450px;
            position: relative;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.3s ease;
            overflow: hidden;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #ff6b6b, #ffa726);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .modal-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text x="20" y="30" font-size="15" fill="rgba(255,255,255,0.1)">🍳</text><text x="70" y="60" font-size="12" fill="rgba(255,255,255,0.08)">👨‍🍳</text><text x="40" y="80" font-size="18" fill="rgba(255,255,255,0.06)">🥘</text></svg>');
            opacity: 0.3;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 2rem;
            position: relative;
            z-index: 1;
        }

        .modal-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .close {
            position: absolute;
            right: 1rem;
            top: 1rem;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            z-index: 2;
        }

        .close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #ff6b6b;
            background: white;
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
        }

        .btn-modal {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #ff6b6b, #ffa726);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }

        .btn-modal:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 107, 107, 0.3);
        }

        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
            color: #999;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e9ecef;
            z-index: 1;
        }

        .divider span {
            background: white;
            padding: 0 1rem;
            position: relative;
            z-index: 2;
        }

        .social-btn {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e9ecef;
            background: white;
            border-radius: 10px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .social-btn:hover {
            border-color: #ff6b6b;
            background: #f8f9fa;
        }

        .modal-footer {
            text-align: center;
            padding: 1rem 2rem 2rem;
            color: #666;
        }

        .modal-footer a {
            color: #ff6b6b;
            text-decoration: none;
            font-weight: 600;
        }

        .modal-footer a:hover {
            text-decoration: underline;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .checkbox-group input {
            width: auto;
        }

        .checkbox-group label {
            margin: 0;
            font-weight: 400;
            font-size: 0.9rem;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .modal-content {
                margin: 1rem;
                max-width: none;
            }
            
            .modal-header {
                padding: 1.5rem;
            }
            
            .modal-body {
                padding: 1.5rem;
            }
        }

        /* Team Section */
.team-section {
    padding: 60px 20px;
    background: #f9f9f9;
    text-align: center;
}

.team-container {
    max-width: 1200px;
    margin: 0 auto;
}

.team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 40px;
    justify-items: center;
    align-items: center;
}

.team-member {
    background: white;
    padding: 20px;
    border-radius: 20px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-decoration: none;
    color: #333;
    width: 100%;
    max-width: 250px;
}

.team-member:hover {
    transform: translateY(-10px);
    box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
}

.team-photo {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 15px;
    border: 4px solid #e0e0e0;
}

.team-name {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
    color: #2c3e50;
}
.section-title {
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 50px;
    color: #2c3e50;
    text-align: center;
    position: relative;
}

.section-title::after {
    content: '';
    width: 60px;
    height: 4px;
    background-color: #3498db;
    display: block;
    margin: 10px auto 0;
    border-radius: 2px;
}

.section-description {
    font-size: 16px;
    color: #555;
    max-width: 700px;
    margin: 0 auto 40px;
    text-align: center;
    line-height: 1.6;
}
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav">
            <div class="logo">Gastroverse</div>
            <ul class="nav-links">
               
            </ul>
            <div class="auth-buttons">
                <a href="#login" class="btn btn-outline">Log In</a>
                <a href="#signup" class="btn btn-primary">Sign Up</a>
            </div>
        </nav>
    </header>
<a href="#home"></a>
    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Master Your Kitchen</h1>
            <p>Discover thousands of recipes and step-by-step cooking tutorials from world-class chefs. Transform your cooking skills with our interactive learning platform.</p>
            <div class="hero-buttons">
                <a href="#signup" class="btn btn-primary">Start Cooking Free</a>
                <a href="#recipes" class="btn btn-outline">Why Us?</a>
            </div>
        </div>
    </section>

    <!-- Food Gallery Section -->
    <section class="food-gallery">
        <div class="gallery-container">
            <h2 class="gallery-title">Delicious Recipes Await</h2>
            <div class="food-carousel">
                <div class="food-item">🍕</div>
                <div class="food-item">🍝</div>
                <div class="food-item">🥘</div>
                <div class="food-item">🍲</div>
                <div class="food-item">🥗</div>
                <div class="food-item">🍰</div>
                <div class="food-item">🥞</div>
                <div class="food-item">🌮</div>
                <div class="food-item">🍜</div>
                <div class="food-item">🥙</div>
                <div class="food-item">🍕</div>
                <div class="food-item">🍝</div>
                <div class="food-item">🥘</div>
                <div class="food-item">🍲</div>
                <div class="food-item">🥗</div>
                <div class="food-item">🍰</div>
                <div class="food-item">🥞</div>
                <div class="food-item">🌮</div>
                <div class="food-item">🍜</div>
                <div class="food-item">🥙</div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="recipes">
        <div class="container">
            <h2 class="section-title fade-in">Why Choose Gastroverse?</h2>
            <div class="features-grid">
                <div class="feature-card fade-in">
                    <div class="feature-icon">📚</div>
                    <h3>5,000+ Recipes</h3>
                    <p>From quick weeknight dinners to gourmet weekend projects, discover recipes for every skill level and occasion.</p>
                </div>
                <div class="feature-card fade-in">
                    <div class="feature-icon">🎥</div>
                    <h3>HD Video Tutorials</h3>
                    <p>Learn from professional chefs with crystal-clear video instructions that guide you through every step.</p>
                </div>  
                <div class="feature-card fade-in">
                    <div class="feature-icon">👥</div>
                    <h3>Cooking Community</h3>
                    <p>Connect with fellow food enthusiasts, share your creations, and get inspired by others' culinary journeys.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Recipe Showcase -->
    <section class="recipe-showcase">
        <div class="container">
            <h2 class="section-title fade-in">Popular Recipe Categories</h2>
            <div class="recipe-grid fade-in">
                <div class="recipe-card">
                    <span class="recipe-icon">🥩</span>
                    <h3>Main Courses</h3>
                    <p>Hearty meals that satisfy</p>
                </div>
                <div class="recipe-card">
                    <span class="recipe-icon">🥗</span>
                    <h3>Healthy Options</h3>
                    <p>Nutritious and delicious</p>
                </div>
                <div class="recipe-card">
                    <span class="recipe-icon">🍰</span>
                    <h3>Desserts</h3>
                    <p>Sweet treats for everyone</p>
                </div>
                <div class="recipe-card">
                    <span class="recipe-icon">🥤</span>
                    <h3>Beverages</h3>
                    <p>Refreshing drinks & cocktails</p>
                </div>
                <div class="recipe-card">
                    <span class="recipe-icon">🍞</span>
                    <h3>Baking</h3>
                    <p>Fresh bread and pastries</p>
                </div>
                <div class="recipe-card">
                    <span class="recipe-icon">🌍</span>
                    <h3>International</h3>
                    <p>Flavors from around the world</p>
                </div>
            </div>
        </div>
    </section>

<!-- About Us Section -->
<section id="about-us" class="team-section">
    <div class="team-container">
         <h2 class="section-title">My G's</h2>
         <p class="section-description">
            Meet the dedicated team behind Gastroverse — a group of passionate individuals driven by creativity, collaboration, and a love for food and technology. Each member brings unique strengths that make this platform truly special.
        </p>
        <div class="team-grid">
            <a href="assets/resumes/resume_member1.pdf" target="_blank" class="team-member">
                <img src="assets/images/member1.jpg" alt="ABDUL ALLIM" class="team-photo">
                <h3 class="team-name">ABDUL ALLIM</h3>
            </a>
            <a href="assets/resumes/resume_member2.pdf" target="_blank" class="team-member">
                <img src="assets/images/member2.png" alt="NUR ‘AINA" class="team-photo">
                <h3 class="team-name">NUR ‘AINA</h3>
            </a>
            <a href="assets/resumes/resume_member3.pdf" target="_blank" class="team-member">
                <img src="assets/images/member3.jpg" alt="NOR ANIS" class="team-photo">
                <h3 class="team-name">NOR ANIS</h3>
            </a>
            <a href="assets/resumes/resume_member4.pdf" target="_blank" class="team-member">
                <img src="assets/images/member4.jpg" alt="NURUL ASYIQIN" class="team-photo">
                <h3 class="team-name">NURUL ASYIQIN</h3>
            </a>
        </div>
    </div>
</section>

    <!-- Call to Action -->
    <section class="cta">
        <div class="container fade-in">
            <h2>Ready to Transform Your Cooking?</h2>
            <p>Join thousands of home cooks who have already elevated their culinary skills with Gastroverse.</p>
            <a href="#signup" class="btn btn-primary">Get Started Today</a>
        </div>
    </section>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeModal('loginModal')">&times;</span>
                <h2>Welcome Back! 👨‍🍳</h2>
                <p>Sign in to continue your culinary journey</p>
            </div>
            <div class="modal-body">
                  <form id="loginForm" method="POST" action="../G04_01_Gastroverse/sign_login/login.php">
                    <div class="form-group">
                        <label for="loginEmail">Email Address</label>
                        <input type="email" id="loginEmail" name="Email" class="form-control" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label for="loginPassword">Password</label>
                        <input type="password" id="loginPassword" name="Password" class="form-control" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" class="btn-modal">Sign In</button>
                </form>
            </div>
            <div class="modal-footer">
                <p>Don't have an account? <a href="#" onclick="switchModal('loginModal', 'signupModal')">Sign up here</a></p>
                <p><a href="#" onclick="switchModal('loginModal', 'forgotPasswordModal')">Forgot your password?</a></p>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotPasswordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeModal('forgotPasswordModal')">&times;</span>
                <h2>Forgot Password</h2>
                <p>Enter your email to receive a reset link</p>
            </div>
            <div class="modal-body">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="forgot_password.php">
                    <div class="form-group">
                        <label for="forgotEmail">Email Address</label>
                        <input type="email" id="forgotEmail" name="email" class="form-control" 
                            placeholder="Enter your email" required>
                    </div>
                    <button type="submit" class="btn-modal">Send Reset Link</button>
                </form>
            </div>
            <div class="modal-footer">
                <p>Remember your password? <a href="#" onclick="switchModal('forgotPasswordModal', 'loginModal')">Sign in</a></p>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal (shown when clicking reset link) -->
    <div id="resetPasswordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeModal('resetPasswordModal')">&times;</span>
                <h2>Reset Password</h2>
                <p>Enter your new password</p>
            </div>
            <div class="modal-body">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="reset_password" value="1">
                    <div class="form-group">
                        <label for="resetPassword">New Password</label>
                        <input type="password" id="resetPassword" name="password" class="form-control" 
                            placeholder="Enter new password" required>
                    </div>
                    <div class="form-group">
                        <label for="resetConfirmPassword">Confirm Password</label>
                        <input type="password" id="resetConfirmPassword" name="confirm_password" class="form-control" 
                            placeholder="Confirm new password" required>
                    </div>
                    <button type="submit" class="btn-modal">Reset Password</button>
                </form>
            </div>
        </div>
    </div>

        <!-- Signup Modal -->
        <div id="signupModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="close" onclick="closeModal('signupModal')">&times;</span>
                    <h2>Join Gastroverse! 🎉</h2>
                    <p>Start your cooking adventure today</p>
                </div>
                <div class="modal-body">
                   <form id="signupForm" action="../G04_01_Gastroverse/sign_login/signup.php" method="POST">
                        <div class="form-group">
                            <label for="signupName">Username</label>
                            <input type="text" id="signupName" name="Name" class="form-control" placeholder="Enter your user name" required>
                        </div>
                        <div class="form-group">
                            <label for="signupEmail">Email Address</label>
                            <input type="email" id="signupEmail" name="Email" class="form-control" placeholder="Enter your email" required>
                        </div>
                        <div class="form-group">
                            <label for="signupPassword">Password</label>
                            <input type="password" id="signupPassword" name="Password" class="form-control" placeholder="Create a password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword">Confirm Password</label>
                            <input type="password" id="confirmPassword" name="ConfirmPassword" class="form-control" placeholder="Confirm your password" required>
                        </div>

                        <div class="form-group">
                            <label>I am a:</label>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="userRole" value="chef" required>
                                    <span class="radio-text">👨‍🍳 Chef</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="userRole" value="student" required>
                                    <span class="radio-text">🎓 Student</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="userRole" value="admin" required>
                                    <span class="radio-text">🛠️ Admin</span>
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn-modal">Create Account</button>
                    </form>
                </div>
            </div>
        </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Gastroverse</h3>
                <p>Your ultimate destination for learning to cook like a pro. From beginner basics to advanced techniques.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#recipes">Why Us?</a></li>
                    <li><a href="#home">Home</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Account</h3>
                <ul>
                    <li><a href="#login">Log In</a></li>
                    <li><a href="#signup">Sign Up</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Support</h3>
                <ul>
                    <li><a href="#help">Help Center</a></li>
                    <li><a href="#contact">Contact Us</a></li>
                    <li><a href="#privacy">Privacy Policy</a></li>
                    <li><a href="#terms">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Gastroverse. All rights reserved.</p>
        </div>
    </footer>

 

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Scroll animation for fade-in elements
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });

        // Header background change on scroll
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.header');
            if (window.scrollY > 100) {
                header.style.background = 'rgba(255, 255, 255, 0.98)';
                header.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
            } else {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
                header.style.boxShadow = 'none';
            }
        });

        // Simulate login/signup functionality
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        function switchModal(currentModal, targetModal) {
            closeModal(currentModal);
            setTimeout(() => openModal(targetModal), 300);
        }

        // Modal event listeners
        document.querySelectorAll('a[href="#login"]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                openModal('loginModal');
            });
        });

        document.querySelectorAll('a[href="#signup"]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                openModal('signupModal');
            });
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                closeModal(e.target.id);
            }
        });

        $(document).ready(function() {
        
        toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-top-center",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
        }

            var message = sessionStorage.getItem('message');  
            console.log("Message from sessionStorage:", message); 
            if (message) {
                toastr.error(message); 
                sessionStorage.removeItem('message');
            } else {
                console.log("No message in sessionStorage. Displaying default message.");
                toastr.success('Welcome to our website!'); 
            }


        });
        
    </script>
</body>
</html>