<?php
include 'backend/process_reportDetails.php';

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
  <title>Report Details | Maintenance System</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="CSS/style.css">
  <style>
    .report-header {
      background-color: #f8f9fa;
      border-bottom: 1px solid #dee2e6;
      padding: 1rem;
      border-radius: 0.375rem 0.375rem 0 0;
    }

    .status-badge {
      font-size: 0.9rem;
      padding: 0.5rem 0.8rem;
    }

    .detail-item {
      margin-bottom: 1rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid #eee;
    }

    .detail-item:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }

    .update-log {
      border-left: 3px solid #95AEF1;
      padding-left: 1rem;
      margin-bottom: 1.5rem;
    }

    .update-log:last-child {
      margin-bottom: 0;
    }

    .img-thumbnail {
      transition: transform 0.2s;
      cursor: pointer;
    }

    .img-thumbnail:hover {
      transform: scale(1.02);
    }

    .media-preview {
      width: 100%;
      height: 250px;
      object-fit: cover;
      cursor: pointer;
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

  <!-- Report Detail -->
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0"><i class="fas fa-file-alt text-primary me-2"></i>Report Details</h2>
      <a href="reportListings.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to My Reports
      </a>
    </div>

    <div class="card shadow-sm mb-4">
      <div class="report-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><?= htmlspecialchars($report['title']) ?></h4>
        <?php
        // Set $status to 'pending' if it is null, empty, or not set
        if (empty($status)) {
          $status = 'pending';
        }

        $badgeClass = match (strtolower($status)) {
          'inprogress' => 'bg-warning text-dark',
          'resolved'   => 'bg-success',
          'pending'    => 'bg-primary',
          default      => 'bg-secondary', // fallback if an unknown status appears
        };
        ?>
        <span class="status-badge badge <?= $badgeClass ?>">
          <i class="fas fa-circle-notch me-1"></i><?= ucwords(str_replace('_', ' ', $status)) ?>
        </span>

      </div>

      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <div class="detail-item">
              <h6><i class="fas fa-calendar-alt text-muted me-2"></i>Date Submitted</h6>
              <p class="mb-0"><?= date("d M Y", strtotime($report['created_at'])) ?></p>
            </div>
            <div class="detail-item">
              <h6><i class="fas fa-map-marker-alt text-muted me-2"></i>Location</h6>
              <p class="mb-0"><?= htmlspecialchars($report['facilities']) ?></p>
            </div>
            <div class="detail-item">
              <h6><i class="fas fa-tag text-muted me-2"></i>Category</h6>
              <p class="mb-0"><?= htmlspecialchars($report['category']) ?></p>
            </div>
          </div>

          <div class="col-md-6">
            <div class="detail-item">
              <h6><i class="fas fa-user-tie text-muted me-2"></i>Assigned Technician</h6>
              <p class="mb-0"><?= htmlspecialchars($technicianName) ?></p>
            </div>
            <div class="detail-item">
              <h6><i class="fas fa-phone text-muted me-2"></i>Technician Contact</h6>
              <p class="mb-0"><?= htmlspecialchars($technicianContact) ?></p>
            </div>
            <div class="detail-item">
              <h6><i class="fas fa-clock text-muted me-2"></i>Last Updated</h6>
              <p class="mb-0"> <?= date('d M Y - g:i A', strtotime($latestUpdate)) ?></p> <!-- Hardcoded -->
            </div>
          </div>
        </div>

        <div class="mt-4">
          <h6><i class="fas fa-align-left text-muted me-2"></i>Description</h6>
          <div class="alert alert-light bg-light">
            <?= nl2br(htmlspecialchars($report['description'])) ?>
          </div>
        </div>

        <?php
        $staffMedia = [];
        $technicianMedia = [];

        foreach ($mediaFiles as $file) {
          if ($file['uploaded_by_role'] === 'staff') {
            $staffMedia[] = $file;
          } elseif ($file['uploaded_by_role'] === 'technician') {
            $technicianMedia[] = $file;
          }
        }
        ?>

        <?php if (!empty($staffMedia) || !empty($technicianMedia)): ?>
          <div class="mt-4">
            <h6 class="mb-3"><i class="fas fa-image text-muted me-2"></i>Attached Media</h6>

            <!-- Staff Media Section -->
            <div class="staff-media-section mb-4"> <!-- Added mb-4 for bottom margin -->
              <h6 class="mb-2">Uploaded by Staff</h6>
              <?php if (empty($staffMedia)): ?>
                <p class="text-muted">No media uploaded by staff.</p>
              <?php else: ?>
                <div class="d-flex flex-wrap gap-3">
                  <?php foreach ($staffMedia as $media): ?>
                    <?php
                    $path = htmlspecialchars("backend/" . $media['file_path']);
                    $ext = strtolower(pathinfo($media['file_path'], PATHINFO_EXTENSION));
                    $type = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'video';
                    ?>
                    <div style="width: 140px;">
                      <div style="height: 140px; cursor: pointer;">
                        <?php if ($type === 'image'): ?>
                          <img src="<?= $path ?>" class="img-thumbnail object-fit-cover w-100 h-100"
                            onclick="previewMedia('image', '<?= $path ?>')">
                        <?php else: ?>
                          <video muted class="img-thumbnail object-fit-cover w-100 h-100"
                            onclick="previewMedia('video', '<?= $path ?>')">
                            <source src="<?= $path ?>" type="video/mp4">
                          </video>
                        <?php endif; ?>
                      </div>

                      <?php if ($type !== 'image'): ?>
                        <?php
                        $filename = pathinfo($media['file_path'], PATHINFO_FILENAME);
                        $transcriptFile = __DIR__ . "/backend/uploads/{$filename}_transcript.txt";
                        $summaryFile = __DIR__ . "/backend/uploads/{$filename}_summary.txt";

                        $transcriptText = file_exists($transcriptFile) ? file_get_contents($transcriptFile) : '';
                        $summaryText = file_exists($summaryFile) ? file_get_contents($summaryFile) : '';
                        ?>

                        <div class="d-flex flex-column mt-2" style="gap: 3px;">
                          <?php if (!empty($transcriptText)): ?>
                            <button class="btn btn-sm btn-outline-secondary open-text-modal"
                              data-title="Transcript"
                              data-content="<?= htmlspecialchars($transcriptText) ?>"
                              style="font-size: 11px;">
                              View Transcript
                            </button>
                          <?php endif; ?>

                          <?php if (!empty($summaryText)): ?>
                            <button class="btn btn-sm btn-outline-secondary open-text-modal"
                              data-title="Summary"
                              data-content="<?= htmlspecialchars($summaryText) ?>"
                              style="font-size: 11px;">
                              View Summary
                            </button>
                          <?php endif; ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <!-- Technician Media Section -->
            <div class="technician-media-section mt-4"> <!-- Added mt-4 for top margin -->
              <h6 class="mb-2">Uploaded by Technician</h6>
              <?php if (empty($technicianMedia)): ?>
                <p class="text-muted">No media uploaded by technician.</p>
              <?php else: ?>
                <div class="d-flex flex-wrap gap-3">
                  <?php foreach ($technicianMedia as $media): ?>
                    <?php
                    $path = htmlspecialchars("backend/" . $media['file_path']);
                    $ext = strtolower(pathinfo($media['file_path'], PATHINFO_EXTENSION));
                    $type = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'video';
                    ?>
                    <div style="width: 140px; height: 140px; cursor: pointer;">
                      <?php if ($type === 'image'): ?>
                        <img src="<?= $path ?>" class="img-thumbnail object-fit-cover w-100 h-100"
                          onclick="previewMedia('image', '<?= $path ?>')">
                      <?php else: ?>
                        <video muted class="img-thumbnail object-fit-cover w-100 h-100"
                          onclick="previewMedia('video', '<?= $path ?>')">
                          <source src="<?= $path ?>" type="video/mp4">
                        </video>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>

        <style>
          /* Additional styling to prevent overlap */
          .staff-media-section {
            padding-bottom: 20px;
            /* Extra space below staff section */
          }

          .technician-media-section {
            padding-top: 20px;
            /* Extra space above technician section */
            border-top: 1px solid #eee;
            /* Optional visual separator */
          }
        </style>
      </div>
    </div>

    <!-- Update Log -->
    <div class="card shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-history text-primary me-2"></i>Log</h5>
      </div>
      <div class="card-body">
        <?php foreach ($statusHistory as $log): ?>
          <div class="update-log">
            <div class="d-flex justify-content-between">
              <h6 class="text-primary">
                <?= match (strtolower($log['status'])) {
                  'submitted' => 'Report Submitted',
                  'resolved', 'in progress', 'open' => 'Status Changed',
                  default => 'Technician Update'
                } ?>
              </h6>
              <small class="text-muted">
                <?= date('d M Y - g:i A', strtotime($log['timestamp'])) ?>
              </small>
            </div>
            <p class="mb-2"><?= htmlspecialchars($log['notes'] ?: 'No additional notes') ?></p>
            <p class="text-muted small mb-0">
              <?php
              $icon = match (strtolower($log['status'])) {
                'submitted' => 'fas fa-user',
                'resolved', 'in progress', 'open' => 'fas fa-user-cog',
                default => 'fas fa-user-tie'
              };
              ?>
              <i class="<?= $icon ?> me-1"></i><?= htmlspecialchars($log['changed_by'] ?? 'System') ?>
            </p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Add Update Modal -->
  <div class="modal fade" id="addUpdateModal" tabindex="-1" aria-labelledby="addUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addUpdateModalLabel">Add Update</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form>
            <div class="mb-3">
              <label for="updateType" class="form-label">Update Type</label>
              <select class="form-select" id="updateType">
                <option selected>Status Update</option>
                <option>Technician Note</option>
                <option>Additional Information</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="updateDetails" class="form-label">Details</label>
              <textarea class="form-control" id="updateDetails" rows="3"></textarea>
            </div>
            <div class="mb-3">
              <label for="updateImages" class="form-label">Add Images (Optional)</label>
              <input type="file" class="form-control" id="updateImages" multiple accept="image/*">
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary">Submit Update</button>
        </div>
      </div>
    </div>
  </div>

  <!-- TEXT MODAL -->
  <div class="modal fade" id="textModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="textModalTitle">Modal Title</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="textModalBody" style="white-space: pre-wrap;"></div>
      </div>
    </div>
  </div>

  <footer class="bg-light py-4 mt-5 border-top">
    <div class="container text-center text-muted">
      <p class="mb-0">&copy; 2025 FacilityCare. All rights reserved.</p>
    </div>
  </footer>

  <!-- Bootstrap Preview Modal -->
  <div class="modal fade" id="mediaPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content bg-dark text-white">
        <div class="modal-header border-0">
          <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center" id="mediaPreviewContent">
          <!-- Media preview will be injected here -->
        </div>
      </div>
    </div>
  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
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
        </video>`;
      }

      const modal = new bootstrap.Modal(document.getElementById('mediaPreviewModal'));
      modal.show();
    }
  </script>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      document.querySelectorAll(".open-text-modal").forEach(btn => {
        btn.addEventListener("click", function() {
          const title = this.getAttribute("data-title");
          const content = this.getAttribute("data-content");

          document.getElementById("textModalTitle").innerText = title;
          document.getElementById("textModalBody").innerText = content;

          const modal = new bootstrap.Modal(document.getElementById("textModal"));
          modal.show();
        });
      });
    });
  </script>

</body>

</html>