<?php
// summarize.php
session_start();

$videoUrl = $_GET['video'] ?? '';
if (!$videoUrl) {
    die("No video provided.");
}

// Convert public URL to local file path
$localPath = __DIR__ . '/../' . parse_url($videoUrl, PHP_URL_PATH);
if (!file_exists($localPath)) {
    die("Video file not found.");
}

// Send to Whisper API to transcribe
$apiKey = 'YOUR_OPENAI_API_KEY';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/audio/transcriptions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

$data = [
    'file' => new CURLFile($localPath, 'video/mp4'),
    'model' => 'whisper-1'
];

curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $apiKey"]);

$response = curl_exec($ch);
curl_close($ch);

$transcript = json_decode($response, true)['text'] ?? 'Unable to transcribe video.';
$_SESSION['transcript'] = $transcript;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Video Q&A</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
  <h3>Video Viewer + AI Q&A</h3>

  <video src="<?= htmlspecialchars($videoUrl) ?>" class="w-100 mb-3" controls></video>

  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Ask a question about this video:</h5>
      <form id="askForm">
        <div class="mb-3">
          <input type="text" class="form-control" id="questionInput" placeholder="Type your question here...">
        </div>
        <button type="submit" class="btn btn-primary">Ask</button>
      </form>
      <div class="mt-3" id="answerBox"></div>
    </div>
  </div>

  <script>
    document.getElementById('askForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const question = document.getElementById('questionInput').value;
      const res = await fetch('../backend/askVideoQuestion.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ question })
      });
      const data = await res.json();
      document.getElementById('answerBox').innerText = data.answer;
    });
  </script>
</body>
</html>
