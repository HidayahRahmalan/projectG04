<?php
session_name("staff_session");
session_start();
require_once 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: homepage.php");
    exit();
}

// Get user data from database
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM User WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Developers | FacilityCare</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css">
    <style>
        /* Enhanced showcase section styles with text shadows */
        .showcase-section {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 4rem 0;
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }

        .showcase-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('images/G4_08_FacilityCare.jpg') center/contain no-repeat;
            opacity: 0.03;
            z-index: 0;
        }

        .showcase-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #2c3e50;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
            letter-spacing: 0.5px;
        }

        .showcase-subtitle {
            font-size: 1.2rem;
            color: #7f8c8d;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.05);
            position: relative;
            z-index: 1;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        .team-logo-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto 1.5rem;
            padding: 16px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .team-logo {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
        }

        .team-name {
            font-weight: 600;
            color: #2c3e50;
            margin-top: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .developer-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 1rem;
            border-radius: 0.375rem 0.375rem 0 0;
        }

        .developer-card {
            border: 1px solid #dee2e6;
            background-color: white;
            border-radius: 0.375rem;
            margin-bottom: 1.5rem;
            transition: transform 0.2s;
        }

        .developer-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .developer-img-container {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto;
            border: 3px solid #f0f0f0;
        }

        .developer-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .developer-detail {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .developer-detail:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .skill-badge {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 20px;
            padding: 3px 12px;
            font-size: 0.8rem;
            margin-right: 5px;
            margin-bottom: 5px;
            display: inline-block;
        }

        .social-icon {
            color: #495057;
            font-size: 1.2rem;
            margin-right: 10px;
            transition: color 0.2s;
        }

        .social-icon:hover {
            color: #0d6efd;
        }
    </style>
</head>

<body>
    <!-- Navbar (same as your profile page) -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="homepageStaff.php">
                <i class="fas fa-tools me-2 text-primary"></i>FacilityCare
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="homepageStaff.php"><i class="fas fa-home me-1"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="newReport.php"><i class="fas fa-plus-circle me-1"></i> New Report</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reportListings.php"><i class="fas fa-list me-1"></i> My Reports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="developers.php"><i class="fas fa-user-cog me-1"></i> About Us</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Staff User'; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- New Showcase Section -->
    <section class="showcase-section">
        <div class="container">
            <h1 class="showcase-title">Welcome to Our Project Showcase</h1>
            <p class="showcase-subtitle">Explore our featured projects and meet our wonderful team</p>

            <div class="team-logo-container">
                <img src="images/G4_08_ikmal_AhmadAfiq_Ainun_AinNatasya_FacilityCare.jpeg" alt="Team Logo" class="team-logo">
            </div>

            <p class="team-name">Student BITD Gempak</p>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-code me-2 text-primary"></i>Development Team</h2>
                <a href="homepage.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Home
                </a>
            </div>

            <!-- Intro paragraph starts here -->
            <p class="mb-4 text-muted">
                Meet our passionate and skilled development team behind the <strong>FacilityCare</strong> system.
                We are a group of dedicated students committed to building a system that improves maintenance reporting
                and technician coordination with ease, efficiency, and clarity.
            </p>

            <div class="row">
                <!-- Developer 1 -->
                <div class="col-md-4">
                    <div class="developer-card p-4">
                        <div class="developer-img-container mb-3">
                            <img src="images/team/developer2.jpg" class="developer-img" alt="Ikmal Daniel">
                        </div>

                        <div class="text-center">
                            <h4 class="mb-1">Ikmal Daniel</h4>
                            <p class="text-muted mb-3">Leader, Database Designer</p>

                            <!-- <div class="developer-detail">
                                <p>UI/UX expert focused on creating intuitive user interfaces and responsive designs.</p>
                            </div> -->

                            <div class="developer-detail">
                                <h6 class="mb-2"><i class="fas fa-cogs me-2 text-primary"></i>Skills</h6>
                                <div>
                                    <span class="skill-badge">HTML/CSS</span>
                                    <span class="skill-badge">JavaScript</span>
                                    <span class="skill-badge">React</span>
                                    <span class="skill-badge">Figma</span>
                                </div>
                            </div>

                            <div class="text-center mt-3">
                                <a href="pdf/RESUME IKMAL.pdf" class="btn btn-primary" download>
                                    <i class="fas fa-download me-2"></i>Download Resume
                                </a>
                            </div>

                            <div class="developer-detail">
                                <h6 class="mb-2 mt-4"><i class="fas fa-share-alt me-2 text-primary"></i>Connect</h6>
                                <div>
                                    <a href="#" class="social-icon"><i class="fab fa-github"></i></a>
                                    <a href="#" class="social-icon"><i class="fab fa-linkedin"></i></a>
                                    <a href="#" class="social-icon"><i class="fab fa-dribbble"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Developer 2 -->
                <div class="col-md-4">
                    <div class="developer-card p-4">
                        <div class="developer-img-container mb-3">
                            <img src="images/team/developer3.jpg" class="developer-img" alt="Amir Mohd">
                        </div>

                        <div class="text-center">
                            <h4 class="mb-1">Ahmad Afiq</h4>
                            <p class="text-muted mb-3">Frontend, Backend Developer</p>

                            <!-- <div class="developer-detail">
                                <p>Database specialist ensuring optimal performance and security for all system data.</p>
                            </div> -->

                            <div class="developer-detail">
                                <h6 class="mb-2"><i class="fas fa-cogs me-2 text-primary"></i>Skills</h6>
                                <div>
                                    <span class="skill-badge">MySQL</span>
                                    <span class="skill-badge">PostgreSQL</span>
                                    <span class="skill-badge">MongoDB</span>
                                    <span class="skill-badge">Database Security</span>
                                </div>
                            </div>

                            <div class="text-center mt-3">
                                <a href="pdf/RESUME AFIQ.pdf" class="btn btn-primary" download>
                                    <i class="fas fa-download me-2"></i>Download Resume
                                </a>
                            </div>

                            <div class="developer-detail">
                                <h6 class="mb-2 mt-4"><i class="fas fa-share-alt me-2 text-primary"></i>Connect</h6>
                                <div>
                                    <a href="#" class="social-icon"><i class="fab fa-github"></i></a>
                                    <a href="#" class="social-icon"><i class="fab fa-linkedin"></i></a>
                                    <a href="#" class="social-icon"><i class="fab fa-dribbble"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Developer 3 -->
                <div class="col-md-4">
                    <div class="developer-card p-4">
                        <div class="developer-img-container mb-3">
                            <img src="images/developer3.jpg" class="developer-img" alt="Ainun Nadia">
                        </div>

                        <div class="text-center">
                            <h4 class="mb-1">Ainun Nadia</h4>
                            <p class="text-muted mb-3">Multimedia Handler, Frontend and Backend Developer</p>

                            <!-- <div class="developer-detail">
                                <p>Full-stack developer with expertise in system architecture and database design.</p>
                            </div> -->

                            <div class="developer-detail">
                                <h6 class="mb-2"><i class="fas fa-cogs me-2 text-primary"></i>Skills</h6>
                                <div>
                                    <span class="skill-badge">PHP</span>
                                    <span class="skill-badge">PostgreSQL</span>
                                    <span class="skill-badge">MySQL</span>
                                    <span class="skill-badge">JavaScript</span>
                                </div>
                            </div>

                            <div class="text-center mt-3">
                                <a href="pdf/ResumeNadia.pdf" class="btn btn-primary" download>
                                    <i class="fas fa-download me-2"></i>Download Resume
                                </a>
                            </div>

                            <div class="developer-detail">
                                <h6 class="mb-2 mt-4"><i class="fas fa-share-alt me-2 text-primary"></i>Connect</h6>
                                <div>
                                    <a href="https://github.com/nndiaanuar" class="social-icon"><i class="fab fa-github"></i></a>
                                    <a href="mailto:aainunnadia@gmail.com" class="social-icon"><i class="fas fa-envelope"></i></a>
                                    <!-- <a href="#" class="social-icon"><i class="fab fa-dribbble"></i></a> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Developer 4 -->
                <div class="col-md-4">
                    <div class="developer-card p-4">
                        <div class="developer-img-container mb-3">
                            <img src="images/developer4.jpg" class="developer-img" alt="Ikmal Daniel">
                        </div>

                        <div class="text-center">
                            <h4 class="mb-1">Nurul Ain Natasya</h4>
                            <p class="text-muted mb-3">Multimedia Handler, Frontend Developer</p>

                            <!-- <div class="developer-detail">
                                <p>UI/UX expert focused on creating intuitive user interfaces and responsive designs.</p>
                            </div> -->

                            <div class="developer-detail">
                                <h6 class="mb-2"><i class="fas fa-cogs me-2 text-primary"></i>Skills</h6>
                                <div>
                                    <span class="skill-badge">HTML/CSS</span>
                                    <span class="skill-badge">Oracle</span>
                                    <span class="skill-badge">MySQL</span>
                                    <span class="skill-badge">MariaDB</span>
                                </div>
                            </div>

                            <div class="text-center mt-3">
                                <a href="pdf/RESUMETASYA.pdf" class="btn btn-primary" download>
                                    <i class="fas fa-download me-2"></i>Download Resume
                                </a>
                            </div>

                            <div class="developer-detail">
                                <h6 class="mb-2 mt-4"><i class="fas fa-share-alt me-2 text-primary"></i>Connect</h6>
                                <div>
                                    <a href="mailto:ntsya298@gmail.com" class="social-icon"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
