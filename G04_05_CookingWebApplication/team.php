<?php 
include('connect.php');
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Team - CookingApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .team-member-card {
            transition: transform 0.3s;
            cursor: pointer;
        }
        .team-member-card:hover {
            transform: translateY(-10px);
        }
        .member-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            margin: 0 auto;
            display: block;
            border: 5px solid #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .pdf-container {
            width: 100%;
            height: 80vh;
            border: none;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">CookingApp</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="recipes.php">Recipes</a></li>
                    <li class="nav-item"><a class="nav-link" href="team.php">Our Team</a></li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="add_recipe.php">Add Recipe</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="signup.php">Sign Up</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h1 class="text-center mb-5">Our Team</h1>
        
        <div class="row">
            <!-- Team Member 1 -->
            <div class="col-md-3 mb-4">
                <div class="card team-member-card text-center p-3 h-100" onclick="showResume(1)">
                    <img src="images/team1.png" alt="Team Member 1" class="member-image mb-3">
                    <h4>Muhammad Sufi Haikal Bin Saifuzbahari</h4>
                    <p class="text-muted">Developer 1</p>
                </div>
            </div>
            
            <!-- Team Member 2 -->
            <div class="col-md-3 mb-4">
                <div class="card team-member-card text-center p-3 h-100" onclick="showResume(2)">
                    <img src="images/team2.png" alt="Team Member 2" class="member-image mb-3">
                    <h4>Danish Imran Bin Khairudin</h4>
                    <p class="text-muted">Developer 2</p>
                </div>
            </div>
            
            <!-- Team Member 3 -->
            <div class="col-md-3 mb-4">
                <div class="card team-member-card text-center p-3 h-100" onclick="showResume(3)">
                    <img src="images/team3.png" alt="Team Member 3" class="member-image mb-3">
                    <h4>Aimi Najwa Binti Abdul Yazid</h4>
                    <p class="text-muted">Developer 3</p>
                </div>
            </div>
            
            <!-- Team Member 4 -->
            <div class="col-md-3 mb-4">
                <div class="card team-member-card text-center p-3 h-100" onclick="showResume(4)">
                    <img src="images/team4.png" alt="Team Member 4" class="member-image mb-3">
                    <h4>Nurul Nuraini Binti Ahmad Hisham</h4>
                    <p class="text-muted">Developer 4</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Resume Modals -->
    <!-- Member 1 -->
    <div class="modal fade" id="resumeModal1" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Muhammad Sufi Haikal Bin Saifuzbahari - Resume</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe src="resumes/member1.pdf" class="pdf-container" frameborder="0"></iframe>
                </div>
                <div class="modal-footer">
                    <a href="resumes/member1.pdf" class="btn btn-primary" download>Download Resume</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Member 2 -->
    <div class="modal fade" id="resumeModal2" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Danish Imran Bin Khairudin - Resume</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe src="resumes/member2.pdf" class="pdf-container" frameborder="0"></iframe>
                </div>
                <div class="modal-footer">
                    <a href="resumes/member2.pdf" class="btn btn-primary" download>Download Resume</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Member 3 -->
    <div class="modal fade" id="resumeModal3" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Aimi Najwa Binti Abdul Yazid - Resume</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe src="resumes/member3.pdf" class="pdf-container" frameborder="0"></iframe>
                </div>
                <div class="modal-footer">
                    <a href="resumes/member3.pdf" class="btn btn-primary" download>Download Resume</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Member 4 -->
    <div class="modal fade" id="resumeModal4" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nurul Nuraini Binti Ahmad Hisham - Resume</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe src="resumes/member4.pdf" class="pdf-container" frameborder="0"></iframe>
                </div>
                <div class="modal-footer">
                    <a href="resumes/member4.pdf" class="btn btn-primary" download>Download Resume</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; 2025 CookingApp. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showResume(memberId) {
            const modal = new bootstrap.Modal(document.getElementById('resumeModal' + memberId));
            modal.show();
        }
    </script>
</body>
</html>