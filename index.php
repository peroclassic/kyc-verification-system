<?php
session_start();
$user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
if (!$user_id && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}
?>
<!DOCTYPE html>
<?php
if (empty($user_id)) {
    echo <<<HTML
    <script>
      window.onload = function () {
        window.close();
        setTimeout(function () {
          if (!window.closed) {
            window.location.href = "https://naijadirectory.org";
          }
        }, 200);
      };
    </script>
    </html>
    HTML;
    exit;
}
?>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <meta name="robots" content="noindex, nofollow">
  <title>NAIJADIRECTORY NIN & Selfie Verification</title>
  <script src="https://cdn.smileidentity.com/js/v1.4.2/smart-camera-web.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.2.1/axios.min.js"></script>
  <link rel="icon" href="img/favicon.ico" type="image/x-icon">
  <style>
    body { font-family: 'Segoe UI', sans-serif; }
    .progress-bar { transition: width 0.4s ease; }
    .sticky-attempt-info { position: sticky; top: 10px; z-index:50; }
  </style>
</head>
<body class="bg-white sm:py-8 py-4 px-4 sm:px-6">

<script>
  function tryCloseOrRedirect(event) {
    event.preventDefault(); // Prevent default link behavior
    window.close();
    // Check if the popup was closed successfully after a short delay
    setTimeout(function () {
      if (!window.closed) {
        window.location.href = 'https://naijadirectory.org'; // Fallback URL
      }
    }, 100);
  }
</script>



<!-- Slide 1 -->
<div id="slide1" class="bg-[#17794F] py-10" style="display: block;">
  <div class="max-w-xl mx-auto bg-[#17794F] border-2 border-white p-6 rounded-lg shadow text-center text-white">
    <img src="img/naijadirectory.png" alt="NaijaDirectory Logo" class="mx-auto mb-2 h-16">
    <h1 class="text-3xl font-bold">NAIJADIRECTORY</h1>
    <h2 class="text-2xl font-bold mb-3 mt-4">🔐 Welcome</h2>
    <p class="text-sm sm:text-base mb-3">
      To uphold the integrity and reliability of the platform, you are required to complete your identity verification process when submitting or managing a business listing on <strong>Naijadirectory.org</strong>.
    </p>
    <p class="text-sm sm:text-base mb-3">
      This step is part of you and our joint commitment to meeting regulatory <strong>KYC (Know Your Customer)</strong> standards and ensuring a secure and trustworthy directory for all users.
    </p>
    <p class="text-sm font-semibold underline mb-2">Important Notes:</p>
    <ul class="text-left text-sm list-disc list-inside mb-4">
      <li>The information and documents you provide are used strictly for verification purposes and are treated with strict confidentiality.</li>
      <li>By submitting your details, you confirm that all information provided is accurate.</li>
    </ul>
    <button onclick="goToSlide('slide2')" class="bg-white text-[#17794F] py-2 px-4 rounded hover:bg-gray-100">
      Next
    </button>
  </div>
</div>

<!-- Slide 2 -->
<div id="slide2" class="bg-[#17794F] py-10" style="display:none;">
  <div class="max-w-xl mx-auto bg-[#17794F] border-2 border-white p-6 rounded-lg shadow text-center text-white">
    <img src="img/naijadirectory.png" alt="NaijaDirectory Logo" class="mx-auto mb-2 h-16">
    <h1 class="text-3xl font-bold">NAIJADIRECTORY</h1>
    <h2 class="text-2xl font-bold text-[#FFD700] mb-3">⚠️ Beware of Fraud</h2>
    <p class="text-sm sm:text-base mb-3">
      <strong>NaijaDirectory</strong> or its agents will not contact you via phone, SMS, or email to request sensitive documents or login credentials.
    </p>
    <p class="text-sm sm:text-base mb-3">
      Please protect your personal and business information. If in doubt, contact our support team directly:
      <a href="mailto:support@naijadirectory.org" class="text-white underline">support@naijadirectory.org</a>.
    </p>
    <p class="text-sm font-semibold underline mb-2">Please review our policies:</p>
    <ul class="text-left text-sm list-disc list-inside mb-4">
      <li><a href="https://naijadirectory.org/acceptable-use-policy/" target="_blank" class="underline">✅ Acceptable Use Policy</a></li>
      <li><a href="https://naijadirectory.org/privacy-policy/" target="_blank" class="underline">✅ Privacy Policy</a></li>
      <li><a href="https://naijadirectory.org/disclaimer/" target="_blank" class="underline">✅ Disclaimer</a></li>
    </ul>
    <p class="text-sm sm:text-base mb-4">
      By clicking “Continue”, you confirm that all submitted information is correct and that you agree to comply with all the above policies.
    </p>
    <button onclick="goToSlide('formCard')" class="bg-white text-[#17794F] py-2 px-4 rounded hover:bg-gray-100">
      Continue
    </button>
  </div>
</div>

<!-- Slide 3 -->
<div id="formCard" style="display:none;" class="bg-white py-10">
  <div class="max-w-xl mx-auto bg-white border-2 border-[#17794F] p-6 rounded-lg shadow">
<img src="img/naijadirectory.png" alt="NaijaDirectory Logo" class="mx-auto mb-2 h-16">
    <h1 class="text-3xl text-[#17794F] text-center font-bold mb-2">NAIJADIRECTORY</h1>
   <div class="text-center mb-4">
  <div class="bg-[#17794F] text-center py-2 px-4 rounded-full inline-block">
    <p class="text-white text-sm font-medium">Secure Identity Verification</p>
  </div>
</div>





    <!-- Sticky Info -->
    <div id="attemptInfo" class="sticky-attempt-info mb-2 text-center text-sm hidden"></div>

    <!-- Progress Bar -->
    <div class="w-full bg-gray-200 rounded-full h-3 mb-6">
      <div id="progressBar" class="bg-[#17794F] h-3 rounded-full" style="width:25%"></div>
    </div>

    <!-- Form Section -->
    <div id="formSection">

          <p class="text-sm text-center text-red-600 mb-3 font-semibold uppercase">INSTRUCTIONS</p>
    <p class="text-sm text-[#17794F] mb-2">
      Step 1: Enter your Full name and your National Identity Number (NIN), click Next
    </p>
    <p class="text-sm text-[#17794F] mb-4">
      Step 2: Click Request Camera Access Button for the selfie and proof-of-life images
    </p>
      <h2 class="text-xl font-semibold text-[#17794F] mb-4">Step 1: Enter Your Details</h2>
      <label class="block mb-1 text-sm font-medium">NIN</label>
      <input type="text" id="id_number" placeholder="e.g., 12345678901" class="w-full border-gray-300 p-2 rounded mb-4"/>
      <label class="block mb-1 text-sm font-medium">First Name</label>
      <input type="text" id="first_name" placeholder="e.g., John" class="w-full border-gray-300 p-2 rounded mb-4"/>
      <label class="block mb-1 text-sm font-medium">Last Name</label>
      <input type="text" id="last_name" placeholder="e.g., Doe" class="w-full border-gray-300 p-2 rounded mb-4"/>
      <button id="nextBtn" class="bg-[#17794F] text-white w-full py-2 rounded hover:bg-[#18642a]">Next</button>
    </div>

    <!-- Camera Section -->
    <div id="cameraSection" style="display:none">

     <!-- Back Button -->
<button id="backToFormBtn"
  class="text-white bg-[#17794F] text-sm mb-4 px-4 py-2 rounded-full shadow-md flex items-center gap-2 hover:bg-[#145d3f] transition duration-200 ease-in-out">
  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
  </svg>
  Back to Step 1
</button>


      <h2 class="text-xl font-semibold text-[#17794F] mb-2">Step 2: Capture Selfie</h2>
      <p class="text-sm text-gray-600 mb-4">
        Please ensure you are in a well-lit environment and follow the prompts to capture your selfie clearly.
      </p>
      <smart-camera-web id="cam" class="mb-4 block"></smart-camera-web>
    </div>

    <!-- Status Spinner -->
    <div id="status" class="hidden mt-6 text-center p-4 rounded-lg bg-gray-50 border-gray-200">
      <div class="flex flex-col items-center gap-2">
        <svg class="animate-spin h-6 w-6 text-[#17794F]" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0"/>
        </svg>
        <p class="font-semibold text-[#1e7e34] text-lg">Verifying your identity…</p>
        <p class="text-gray-600 text-sm">Please hold on while we process your details.</p>
      </div>
    </div>

  </div>
</div>

<!-- Footer -->
<!-- <div class="text-center text-xs mt-4">
  <a href="https://peroclassic.com" target="_blank" class="text-[#17794F] hover:underline">Securely Powered by PEROCLASSIC HUB</a>
</div> -->

<script>
const verifiedUserId = <?php echo json_encode($user_id); ?>;
let attemptInfo = null, statusDiv = null;

document.addEventListener("DOMContentLoaded", () => {
  attemptInfo = document.getElementById("attemptInfo");
  statusDiv = document.getElementById("status");
  const userId = verifiedUserId || "";

  if (!userId) return;
  axios.post('/verification-nin/check_attempts.php', { user_id: userId })
    .then(res => {
      const check = res.data;
      if ('remaining_attempts' in check && 'max_attempts' in check) {
        const used = check.max_attempts - check.remaining_attempts;
        const color = check.remaining_attempts <= 1 ? "text-red-600" : "text-[#1e7e34]";
        attemptInfo.classList.remove("hidden");
        attemptInfo.innerHTML = `<div class="inline-flex items-center gap-2 ${color} font-medium">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20h.01M3 12a9 9 0 1118 0a9 9 0 01-18 0z"/>
          </svg>
          Attempt ${used} of ${check.max_attempts}
        </div>`;
      }

      if (check.status === "blocked") {
        document.getElementById("formSection").style.display = "none";
        statusDiv.classList.remove("hidden");
        statusDiv.innerHTML = `<div class="text-center"><p class="text-red-600 text-lg font-bold">
          Access Denied ❌</p><p class="text-gray-700 mt-1">
          Exceeded attempts. Contact support.</p></div>`;
      }
      if (check.status === "Verified") {
        document.getElementById("formSection").style.display = "none";
        statusDiv.classList.remove("hidden");
        statusDiv.innerHTML = `<div class="text-center"><p class="text-green-600 text-lg font-bold">
          Already Verified ✅</p><p class="text-gray-700 mt-1">
          You're verified. No further action needed.</p></div>`;
      }
    })
    .catch(err => console.error("Attempt check error:", err));
});

// Handle back button to return to form section from camera section
document.getElementById("backToFormBtn").addEventListener("click", () => {
  document.getElementById("cameraSection").style.display = "none";
  document.getElementById("formSection").style.display = "block";
  progressBar.style.width = "25%";
});


const cam = document.getElementById("cam"),
      progressBar = document.getElementById("progressBar");
document.getElementById("nextBtn").addEventListener("click", () => {
  const nin = document.getElementById("id_number").value.trim(),
        fn = document.getElementById("first_name").value.trim(),
        ln = document.getElementById("last_name").value.trim();
  if (nin.length !== 11 || !fn || !ln) {
    return alert("Please input valid NIN and both names.");
  }
  document.getElementById("formSection").style.display = "none";
  document.getElementById("cameraSection").style.display = "block";
  progressBar.style.width = "75%";
});

cam.addEventListener("imagesComputed", e => {
  
  document.getElementById("backToFormBtn").style.display = "none";

  const photo = e.detail;
  const nin = document.getElementById("id_number").value.trim(),
        fn = document.getElementById("first_name").value.trim(),
        ln = document.getElementById("last_name").value.trim(),
        userId = verifiedUserId || "";

  document.getElementById("cameraSection").style.display = "none";
  statusDiv.classList.remove("hidden");
  progressBar.style.width = "100%";

  axios.post('/verification-nin/check_attempts.php', { user_id: userId })
    .then(res => {
      const check = res.data;
      if ('remaining_attempts' in check && 'max_attempts' in check) {
        const used = check.max_attempts - check.remaining_attempts;
        const color = check.remaining_attempts <= 1 ? "text-red-600" : "text-[#17794F]";
        attemptInfo.innerHTML = `<div class="inline-flex items-center gap-2 ${color} font-medium">
          Attempt ${used} of ${check.max_attempts}</div>`;
      }
      if (check.status === "blocked") throw new Error("blocked");
      if (check.status === "Verified") throw new Error("Verified");

      return axios.post('/verification-nin/process_verification.php', {
        id_number: nin, first_name: fn, last_name: ln,
        user_id: userId, selfieLiveness: photo.images
      });
    })
    .then(() => {
      statusDiv.innerHTML = `<div class="text-center flex flex-col items-center gap-2">
    <svg class="animate-spin h-6 w-6 text-[#17794F]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <p class="text-green-600 text-lg font-bold">Verification Submitted</p>
    <p class="text-gray-700 text-sm">Fetching actual verification result...</p>
  </div>`;
      return pollForVerificationResult(userId);
    })
    .then(result => {
      const msg = result.status === "Verified" ? "Passed ✅" :
                  result.status === "timeout"? "Still Processing..." : "Failed ❌";
      statusDiv.innerHTML = `<p class="text-${result.status==="Verified"?"green":"red"}-600 text-lg font-bold">
        Verification ${msg}</p>`;
      window.opener?.postMessage({ type: "verification_result", data: result }, "*");
      setTimeout(() => window.close(), 1500);
    })
    .catch(err => {
      if (err.message === "blocked" || err.message === "Verified") return;
      statusDiv.innerHTML = `<p class="text-red-600 text-lg font-bold">Error</p>`;
    });
});

async function pollForVerificationResult(userId, retries = 10, delay = 3000) {
  for (let i=0; i<retries; i++) {
    try {
      const resp = await axios.post('/verification-nin/fetch_verification_result.php', { user_id: userId });
      if (resp.data.status && resp.data.status !== "pending") return resp.data;
    } catch {}
    await new Promise(r => setTimeout(r, delay));
  }
  return { status: "timeout", message: "Still processing. Please check back shortly." };
}

function goToSlide(slideId) {
  const allSlides = ['slide1', 'slide2', 'formCard'];
  allSlides.forEach(id => {
    const el = document.getElementById(id);
    if (el) el.style.display = (id === slideId) ? 'block' : 'none';
  });

  const progress = {
    'slide1': '10%',
    'slide2': '30%',
    'formCard': '50%',
  };
  const bar = document.getElementById('progressBar');
  if (bar && progress[slideId]) bar.style.width = progress[slide];
  }
</script>

</body>
</html>

