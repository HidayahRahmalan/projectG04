<?php include('backend/process_newReport.php');
if (!isset($_SESSION['user_id'])) {
    header("Location: homepage.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard | Maintenance System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css">
    <style>
        .voice-btn {
            cursor: pointer;
            transition: all 0.2s;
        }

        .voice-btn:hover {
            color: #0d6efd !important;
            transform: scale(1.1);
        }

        .voice-btn:active {
            transform: scale(0.95);
        }

        .object-fit-cover {
            object-fit: cover;
        }

        .listening {
            animation: pulse 1.5s infinite;
            color: #dc3545 !important;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }

        .language-selector {
            position: relative;
            display: inline-block;
        }

        .language-dropdown {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 120px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1);
            z-index: 1;
            border-radius: 4px;
            padding: 5px 0;
            right: 0;
        }

        .language-selector:hover .language-dropdown {
            display: block;
        }

        .language-option {
            padding: 5px 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        .language-option:hover {
            background-color: #f8f9fa;
        }

        .language-flag {
            width: 20px;
            margin-right: 8px;
        }

        .active-language {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
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
                        <a class="nav-link active" href="reportListings.php"><i class="fas fa-list me-1"></i> My Reports</a>
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

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h4 class="mb-0"><i class="fas fa-plus-circle me-2 text-warning"></i>New Maintenance Report</h4>
                    </div>
                    <div class="card-body">
                        <form action="backend/process_newReport.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="issueTitle" class="form-label">Issue Title*</label>
                                <input type="text" class="form-control" id="issueTitle" name="title" placeholder="E.g., Leaking pipe in restroom" required>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="location" class="form-label">Location*</label>
                                    <input type="text" class="form-control" id="location" name="location" placeholder="Building, Floor, Unit No, Room" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="category" class="form-label">Category*</label>
                                    <select class="form-select" name="category" id="category" required>
                                        <option value="" selected disabled>Select category</option>
                                        <?php foreach ($enumValues as $category): ?>
                                            <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="priority" class="form-label">Priority Level*</label>
                                <div class="d-flex gap-3">
                                    <?php foreach ($priorityValues as $value): ?>
                                        <?php
                                        $badgeClass = match (strtolower($value)) {
                                            'low' => 'bg-success',
                                            'medium' => 'bg-warning',
                                            'high' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                        ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="priority" id="<?= htmlspecialchars($value) ?>" value="<?= htmlspecialchars($value) ?>" <?= strtolower($value) === 'medium' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="<?= htmlspecialchars($value) ?>">
                                                <span class="badge <?= $badgeClass ?>"><?= ucfirst($value) ?></span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Detailed Description*
                                    <span class="text-muted small">
                                        <span class="language-selector">
                                            <span id="currentLanguage" class="active-language">English (US)</span>
                                            <i class="fas fa-microphone ms-1 voice-btn" id="voiceDescBtn" title="Voice Description"></i>
                                            <div class="language-dropdown">
                                                <div class="language-option active" onclick="changeLanguage('en-US', 'English (US)', this)">
                                                    <img src="https://flagcdn.com/w20/us.png" class="language-flag"> English
                                                </div>
                                                <div class="language-option" onclick="changeLanguage('ms-MY', 'Malay', this)">
                                                    <img src="https://flagcdn.com/w20/my.png" class="language-flag"> Malay
                                                </div>
                                            </div>
                                        </span>
                                    </span>
                                </label>
                                <textarea class="form-control" id="description" rows="4" name="description" placeholder="Describe the issue in detail..." required></textarea>
                                <div id="voiceStatus" class="small text-muted mt-1"></div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Upload Evidence (Photos/Videos)</label>
                                <div class="border rounded p-3 text-center">
                                    <input type="text" id="fileCaption" class="form-control mb-3" placeholder="Add description for your evidence..." name="file_description">
                                    <div id="previewArea" class="d-flex flex-wrap gap-2 mb-3"></div>
                                    <input type="file" id="fileUpload" name="media[]" class="d-none" accept="image/*,video/*" multiple>
                                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('fileUpload').click()">
                                        <i class="fas fa-camera me-2"></i>Add Photos/Videos
                                    </button>
                                    <p class="small text-muted mt-2 mb-0">Max 5 files (JPEG, PNG, MP4 up to 10MB each)</p>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Report
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="mediaPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center" id="mediaPreviewContent"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Language configuration
        let currentLanguage = 'en-US';
        let currentLanguageName = 'English (US)';

        function changeLanguage(langCode, langName, element) {
            currentLanguage = langCode;
            currentLanguageName = langName;
            document.getElementById('currentLanguage').textContent = langName;
            if (descVoice) {
                descVoice.setLanguage(langCode);
            }
            // Update active state in dropdown
            const dropdown = element.closest('.language-dropdown');
            dropdown.querySelectorAll('.language-option').forEach(opt => {
                opt.classList.remove('active');
            });
            element.classList.add('active');
        }

        // Enhanced Voice Recognition System with English and Malay support
        class VoiceRecognition {
            constructor(targetId, buttonId, statusId, initialLang = 'en-US') {
                this.targetElement = document.getElementById(targetId);
                this.buttonElement = document.getElementById(buttonId);
                this.statusElement = statusId ? document.getElementById(statusId) : null;
                this.recognition = null;
                this.isListening = false;
                this.originalPlaceholder = this.targetElement.placeholder;
                this.currentLang = initialLang;

                this.init();
            }

            init() {
                if (!('webkitSpeechRecognition' in window)) {
                    this.disableVoiceFeature();
                    return;
                }

                this.recognition = new webkitSpeechRecognition();
                this.recognition.continuous = false;
                this.recognition.interimResults = false;
                this.recognition.lang = this.currentLang;

                this.recognition.onstart = () => this.onListeningStart();
                this.recognition.onresult = (event) => this.onResult(event);
                this.recognition.onerror = (event) => this.onError(event);
                this.recognition.onend = () => this.onListeningEnd();

                this.buttonElement.addEventListener('click', () => this.toggleListening());
            }

            setLanguage(langCode) {
                this.currentLang = langCode;
                if (this.recognition) {
                    this.recognition.lang = langCode;
                }
            }

            toggleListening() {
                if (this.isListening) {
                    this.stopListening();
                } else {
                    this.startListening();
                }
            }

            startListening() {
                try {
                    this.targetElement.focus();
                    this.recognition.lang = this.currentLang;
                    this.recognition.start();
                    this.isListening = true;
                } catch (error) {
                    this.showStatus("Error: " + error.message, true);
                    console.error("Recognition error:", error);
                    this.isListening = false;
                }
            }

            stopListening() {
                this.recognition.stop();
                this.isListening = false;
            }

            onListeningStart() {
                this.buttonElement.classList.add('listening');
                this.showStatus("Listening... Speak now (" + this.getLanguageName() + ")");
                this.targetElement.placeholder = "Listening... Speak now";
            }

            onResult(event) {
                const transcript = event.results[0][0].transcript;
                this.targetElement.value = transcript;
                this.showStatus("Voice input complete (" + this.getLanguageName() + ")");
            }

            onError(event) {
                console.error('Voice recognition error:', event.error);
                let errorMsg = "Error occurred";

                switch (event.error) {
                    case 'no-speech':
                        errorMsg = "No speech detected";
                        break;
                    case 'audio-capture':
                        errorMsg = "No microphone found";
                        break;
                    case 'not-allowed':
                        errorMsg = "Microphone access denied";
                        break;
                    default:
                        errorMsg = "Error: " + event.error;
                }

                this.showStatus(errorMsg + " (" + this.getLanguageName() + ")", true);
            }

            getLanguageName() {
                switch (this.currentLang) {
                    case 'en-US':
                        return 'English';
                    case 'ms-MY':
                        return 'Malay';
                    default:
                        return this.currentLang;
                }
            }

            onListeningEnd() {
                this.buttonElement.classList.remove('listening');
                this.targetElement.placeholder = this.originalPlaceholder;
                this.isListening = false;

                if (!this.statusElement || !this.statusElement.textContent.includes("complete")) {
                    this.showStatus("Ready for voice input (" + this.getLanguageName() + ")");
                }
            }

            showStatus(message, isError = false) {
                if (this.statusElement) {
                    this.statusElement.textContent = message;
                    this.statusElement.style.color = isError ? '#dc3545' : '#6c757d';

                    if (isError) {
                        setTimeout(() => {
                            this.statusElement.textContent = "Ready for voice input (" + this.getLanguageName() + ")";
                            this.statusElement.style.color = '#6c757d';
                        }, 3000);
                    }
                }
            }

            disableVoiceFeature() {
                this.buttonElement.style.display = 'none';
            }
        }

        // Initialize voice recognition
        let descVoice;
        document.addEventListener('DOMContentLoaded', function() {
            // Description field voice recognition
            descVoice = new VoiceRecognition(
                'description',
                'voiceDescBtn',
                'voiceStatus',
                currentLanguage
            );

            // File upload preview functionality
            document.getElementById('fileUpload').addEventListener('change', function(e) {
                const previewArea = document.getElementById('previewArea');
                previewArea.innerHTML = '';

                if (this.files && this.files.length > 0) {
                    if (this.files.length > 5) {
                        alert('Maximum 5 files allowed');
                        this.value = '';
                        return;
                    }

                    Array.from(this.files).forEach(file => {
                        if (file.size > 10 * 1024 * 1024) {
                            alert(`File "${file.name}" exceeds 10MB limit`);
                            this.value = '';
                            previewArea.innerHTML = '';
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const previewDiv = document.createElement('div');
                            previewDiv.className = 'position-relative';
                            previewDiv.style.width = '100px';
                            previewDiv.style.height = '100px';
                            previewDiv.style.cursor = 'pointer';

                            if (file.type.startsWith('image/')) {
                                previewDiv.innerHTML = `
                                    <img src="${e.target.result}" class="img-thumbnail h-100 w-100 object-fit-cover" onclick="previewMedia('image', '${e.target.result}')">
                                    <button class="btn btn-danger btn-sm position-absolute top-0 end-0 p-1" onclick="removeFilePreview(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                `;
                            } else if (file.type.startsWith('video/')) {
                                previewDiv.innerHTML = `
                                    <video class="img-thumbnail h-100 w-100 object-fit-cover" muted onclick="previewMedia('video', '${e.target.result}')">
                                        <source src="${e.target.result}" type="${file.type}">
                                    </video>
                                    <button class="btn btn-danger btn-sm position-absolute top-0 end-0 p-1" onclick="removeFilePreview(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                `;
                            }

                            previewArea.appendChild(previewDiv);
                        };
                        reader.readAsDataURL(file);
                    });
                }
            });
        });

        function removeFilePreview(button) {
            button.parentElement.remove();
        }

        function previewMedia(type, src) {
            const container = document.getElementById("mediaPreviewContent");
            container.innerHTML = '';

            if (type === 'image') {
                container.innerHTML = `<img src="${src}" class="img-fluid rounded">`;
            } else if (type === 'video') {
                container.innerHTML = `
                    <video class="w-100" controls autoplay>
                        <source src="${src}">
                        Your browser does not support the video tag.
                    </video>
                `;
            }

            const modal = new bootstrap.Modal(document.getElementById('mediaPreviewModal'));
            modal.show();
        }
    </script>

    <footer class="bg-light py-4 mt-5 border-top">
        <div class="container text-center text-muted">
            <p class="mb-0">&copy; 2025 FacilityCare. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>
