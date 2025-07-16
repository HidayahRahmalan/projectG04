<?php include 'headerlogin.php'; ?>

  <main>
    <div class="login-container">
      <h2>Log Masuk</h2>
      <form onsubmit="loginUser(event)">
        <label for="username">Nama Pengguna</label>
        <input type="text" id="username" required>

        <label for="password">Kata Laluan</label>
        <input type="password" id="password" required>

        <button type="submit">Log Masuk</button>
      </form>

      <div class="forgot-link">
        <a href="#" onclick="toggleReset()">Lupa kata laluan?</a>
      </div>

      <div class="reset-password" id="resetSection">
        <form onsubmit="resetPassword(event)">
          <label for="resetEmail">Emel Anda</label>
          <input type="email" id="resetEmail" required>

          <label for="newPassword">Kata Laluan Baru</label>
          <input type="password" id="newPassword" required>

          <button type="submit">Tetapkan Semula Kata Laluan</button>
        </form>
      </div>
    </div>
  </main>

  <?php include 'footer.php'; ?>

  <script>
    function toggleReset() {
      document.getElementById('resetSection').style.display = 'block';
    }

    function loginUser(event) {
      event.preventDefault();
      const username = document.getElementById('username').value;
      const password = document.getElementById('password').value;
      if (username === "user" && password === "pass") {
        alert("Log masuk berjaya!");
        window.location.href = "index.php";
      } else {
        alert("Nama pengguna atau kata laluan salah.");
      }
    }

    function resetPassword(event) {
      event.preventDefault();
      const email = document.getElementById('resetEmail').value;
      const newPassword = document.getElementById('newPassword').value;
      alert("Kata laluan berjaya ditetapkan semula untuk: " + email);
    }

    function requireLogin(action) {
      const isLoggedIn = false; // Replace with real auth check

      if (!isLoggedIn) {
        alert("Sila log masuk atau daftar terlebih dahulu untuk mengakses bahagian ini.");
        window.location.href = "login.php";
      } else {
        if (action === 'share') {
          window.location.href = "upload.php";
        } else if (action === 'notification') {
          window.location.href = "notification.php";
        }
      }
    }
  </script>
</body>

