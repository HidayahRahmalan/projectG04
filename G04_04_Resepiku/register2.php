<?php include 'headerregister.php'; ?>

  <main>
    <div class="register-container">
      <h2>Daftar Akaun</h2>
      <form onsubmit="registerUser(event)">
        <label for="fullname">Nama Penuh</label>
        <input type="text" id="fullname" required>

        <label for="role">Peranan</label>
        <select id="role" required>
          <option value="">-- Pilih Peranan --</option>
          <option value="pengguna">Chef</option>
          <option value="admin">Student</option>
        </select>

        <label for="username">Nama Pengguna</label>
        <input type="text" id="username" required>

        <label for="email">Emel</label>
        <input type="email" id="email" required>

        <label for="password">Kata Laluan</label>
        <input type="password" id="password" required>

        <button type="submit">Daftar</button>
      </form>
    </div>
  </main>

  <?php include 'footer.php'; ?>

  <script>
    function registerUser(event) {
      event.preventDefault();
      const fullname = document.getElementById('fullname').value;
      const role = document.getElementById('role').value;
      const username = document.getElementById('username').value;
      const email = document.getElementById('email').value;
      const password = document.getElementById('password').value;

      // Simulate a successful registration
      alert(`Pendaftaran berjaya untuk ${fullname} sebagai ${role}.`);
      window.location.href = "login.php";
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

