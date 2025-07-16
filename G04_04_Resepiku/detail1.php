<?php include 'headerdetail.php'; ?>

  <main>
    <h1>Ayam Masak Merah</h1>
    <img src="images/ayam-masak-merah.jpg" alt="Ayam Masak Merah" class="recipe-img" />

    <div class="section">
      <h3>Kategori</h3>
      <p>Masakan Melayu</p>
    </div>

    <div class="section">
      <h3>Jenis Hidangan</h3>
      <p>Hidangan Utama</p>
    </div>

    <div class="section">
      <h3>Deskripsi Ringkas</h3>
      <p>Ayam Masak Merah ialah hidangan klasik Malaysia yang disukai ramai. Ia dimasak dengan ayam goreng yang disaluti sos tomato pedas manis.</p>
    </div>

    <div class="section">
      <h3>Penerangan Penuh</h3>
      <p>Resepi ini sesuai dihidang semasa kenduri atau makan malam istimewa bersama keluarga. Gabungan rempah ratus dan cili memberikan rasa yang cukup menyelerakan.</p>
    </div>

    <div class="section">
      <h3>Langkah Penyediaan</h3>
      <ol>
        <li>Goreng ayam hingga separuh masak dan ketepikan.</li>
        <li>Tumis bawang merah, bawang putih, dan halia hingga wangi.</li>
        <li>Masukkan cili kisar, tumis sehingga pecah minyak.</li>
        <li>Tambahkan sos tomato, sos cili, gula dan garam.</li>
        <li>Masukkan ayam dan renehkan hingga kuah pekat.</li>
      </ol>
    </div>

    <div class="section">
      <h3>Bahan-bahan</h3>
      <ul>
        <li>1 ekor ayam – potong kecil dan goreng</li>
        <li>5 ulas bawang merah</li>
        <li>3 ulas bawang putih</li>
        <li>1 inci halia</li>
        <li>4 sudu besar cili kisar</li>
        <li>3 sudu besar sos tomato</li>
        <li>1 sudu besar sos cili</li>
        <li>1 sudu teh gula</li>
        <li>Garam secukup rasa</li>
      </ul>
    </div>
    <!---replace from db-->
    <div class="section">
      <h3>Video Penyediaan</h3>
      <div class="video-container">
        <iframe src="https://www.youtube.com/embed/ZhZoqFD6Zl4" frameborder="0" allowfullscreen></iframe>
      </div>
    </div>

    <div class="section">
      <h3>Tahap Kesukaran</h3>
      <p>Sederhana</p>
    </div>

    <div class="section">
      <h3>Nama Pengguna Yang Kongsi Resepi</h3>
      <p><strong>Nur Aisyah Binti Ali</strong></p>
    </div>

    <!-- Feedback Section -->
    <div class="feedback-section">
      <h3>Komen / Maklum Balas</h3>
      <form class="feedback-form" id="feedbackForm">
        <textarea id="commentText" rows="4" placeholder="Tulis komen anda di sini..." required></textarea>
        <label>Tambah Gambar:</label>
        <input type="file" id="commentImage" accept="image/*" />
        <label>Tambah Video:</label>
        <input type="file" id="commentVideo" accept="video/*" />
        <br />
        <button type="submit">Hantar Komen</button>
      </form>

      <div class="comment-list" id="commentList"></div>
    </div>
  </main>

 <?php include 'footer.php'; ?>

  <script>
    document.getElementById("feedbackForm").addEventListener("submit", function (event) {
      event.preventDefault();
      const commentText = document.getElementById("commentText").value;
      const commentImageInput = document.getElementById("commentImage");
      const commentVideoInput = document.getElementById("commentVideo");
      const commentList = document.getElementById("commentList");

      const commentDiv = document.createElement("div");
      commentDiv.classList.add("comment");

      const textPara = document.createElement("p");
      textPara.textContent = commentText;
      commentDiv.appendChild(textPara);

      if (commentImageInput.files.length > 0) {
        const image = document.createElement("img");
        image.src = URL.createObjectURL(commentImageInput.files[0]);
        commentDiv.appendChild(image);
      }

      if (commentVideoInput.files.length > 0) {
        const video = document.createElement("video");
        video.src = URL.createObjectURL(commentVideoInput.files[0]);
        video.controls = true;
        commentDiv.appendChild(video);
      }

      commentList.appendChild(commentDiv);
      document.getElementById("feedbackForm").reset();
    });
  </script>
</body>

