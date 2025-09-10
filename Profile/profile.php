<?php
session_start();
require_once '../LoginPage/database_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user profile data
$stmt = $conn->prepare('SELECT name, email, contact_no, address, profile_img FROM users WHERE id = ?');
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $contact_no, $address, $profile_img);
$stmt->fetch();
$stmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = $_POST['name'] ?? '';
    $new_contact = $_POST['contact_no'] ?? '';
    $new_address = $_POST['address'] ?? '';
    $img_path = $profile_img;
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_img']['name'], PATHINFO_EXTENSION);
        $img_path = 'uploads/profile_' . $user_id . '.' . $ext;
        move_uploaded_file($_FILES['profile_img']['tmp_name'], '../' . $img_path);
    }
    $update = $conn->prepare('UPDATE users SET name=?, contact_no=?, address=?, profile_img=? WHERE id=?');
    $update->bind_param('ssssi', $new_name, $new_contact, $new_address, $img_path, $user_id);
    $update->execute();
    $update->close();
    // Update session variable for header profile image
    $_SESSION['user_profile_img'] = '../' . $img_path;
    header('Location: profile.php?success=1');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="../LoginPage/core.css">
    <link rel="stylesheet" href="../header.css">
    <link rel="stylesheet" href="profile.css">
    <link rel="stylesheet" href="../footer.css">
</head>
<body>
<!-- CSS moved to profile.css -->
<?php include '../header.php'; ?>
<div class="main-content">
    <div class="content-section">
        <div id="profileCard" style="text-align:center;">
            <div class="profile-label">User Profile</div>
            <div class="profile-img-wrapper">
                <img id="profileImgDisplay" src="<?php echo $profile_img ? ('../' . htmlspecialchars($profile_img)) : '../Image/Logo.png'; ?>" alt="Profile" class="profile-img-main">
            </div>
            <div class="profile-name"><?php echo htmlspecialchars($name); ?></div>
            <div class="profile-email"><?php echo htmlspecialchars($email); ?></div>
            
            <button id="editProfileBtn" class="login-btn" type="button" style="margin-bottom:18px;">Edit Profile</button>
            <!-- Inilipat ang Log Out button sa loob ng profile card -->
            <form action="../LoginPage/logout.php" method="POST" style="margin-top:16px;">
                <button type="submit" class="login-btn coffee-logout-btn" style="background:#5c4033; color:#ffcc99;">Log Out</button>
            </form>
        </div>
        <div id="editProfileFormWrapper" style="display:none;">
            <h1 style="text-align:center;">Edit Profile</h1>
            <?php if (isset($_GET['success'])): ?>
                <div class="dialog-container"><div class="dialog-content">Profile updated successfully!</div></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="input-group">
                    <label for="profile_img">Profile Image</label><br>
                    <img id="editProfileImgPreview" src="<?php echo $profile_img ? ('../' . htmlspecialchars($profile_img)) : '../Image/Logo.png'; ?>" alt="Profile" style="width:110px;height:110px;border-radius:50%;object-fit:cover;margin-bottom:10px;border:2.5px solid #ffcc99;background:#f8f4ef;">
                    <input type="file" name="profile_img" id="profile_img" accept="image/*" onchange="previewProfileImg(event)">
                </div>
                <div class="input-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>
                <div class="input-group">
                    <label for="contact_no">Contact Number</label>
                    <input type="text" name="contact_no" id="contact_no" value="<?php echo htmlspecialchars($contact_no); ?>" required>
                </div>
                <div class="input-group">
                    <label for="address">Address</label>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($address); ?>" required style="flex:1;">
                        <button type="button" id="openMapBtn" style="padding:6px 14px;background:#6F4E37;color:#fff;border:none;border-radius:6px;cursor:pointer;">Map</button>
                    </div>
                </div>
                <!-- Map Modal -->
                <div id="mapModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.45);z-index:9999;align-items:center;justify-content:center;">
                  <div style="background:#fff;padding:18px 18px 8px 18px;border-radius:12px;max-width:420px;width:95vw;box-shadow:0 4px 24px rgba(0,0,0,0.18);position:relative;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                      <span style="font-weight:700;font-size:1.1rem;color:#5c4033;">Select Address</span>
                      <button type="button" id="closeMapBtn" style="background:none;border:none;font-size:1.5rem;line-height:1;color:#5c4033;cursor:pointer;">&times;</button>
                    </div>
                    <div style="margin-bottom:8px;display:flex;gap:6px;align-items:center;">
                      <input type="text" id="searchAddressInput" placeholder="Search address..." style="flex:1;padding:6px 10px;border:1px solid #ccc;border-radius:5px;">
                      <button type="button" id="searchAddressBtn" style="padding:6px 12px;background:#A0522D;color:#fff;border:none;border-radius:5px;">Search</button>
                    </div>
                    <div id="searchResults" style="max-height:90px;overflow-y:auto;margin-bottom:8px;"></div>
                    <div id="leafletMap" style="width:100%;height:320px;border-radius:8px;"></div>
                    <button type="button" id="useLocationBtn" style="margin:12px 0 0 0;width:100%;background:#A0522D;color:#fff;border:none;padding:10px 0;border-radius:6px;font-weight:700;">Use This Location</button>
                  </div>
                </div>
                <button type="submit" class="login-btn" style="background:#A0522D; color:#fff;">Save Profile</button>
                <button type="button" class="login-btn secondary" style="margin-top:8px;background:#eee;color:#5c4033;" onclick="hideEditProfile()">Cancel</button>
            </form>
        </div>
    </div>
</div>
<?php include '../footer.php'; ?>
<!-- Leaflet.js for Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Show edit form when Edit Profile is clicked
document.getElementById('editProfileBtn').onclick = function() {
    document.getElementById('profileCard').style.display = 'none';
    document.getElementById('editProfileFormWrapper').style.display = 'block';
};
function hideEditProfile() {
    document.getElementById('editProfileFormWrapper').style.display = 'none';
    document.getElementById('profileCard').style.display = 'block';
}
// Preview profile image before upload
function previewProfileImg(event) {
    var reader = new FileReader();
    reader.onload = function(){
        document.getElementById('editProfileImgPreview').src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}
// If just updated, update header image too
<?php if (isset($_GET['success'])): ?>
    setTimeout(function() {
        var newImg = document.getElementById('editProfileImgPreview').src;
        var headerImg = document.querySelector('.nav-profile-btn img');
        if(headerImg) headerImg.src = newImg;
    }, 500);
<?php endif; ?>

// Map modal logic
let mapModal = document.getElementById('mapModal');
let openMapBtn = document.getElementById('openMapBtn');
let closeMapBtn = document.getElementById('closeMapBtn');
let useLocationBtn = document.getElementById('useLocationBtn');
let searchAddressInput = document.getElementById('searchAddressInput');
let searchAddressBtn = document.getElementById('searchAddressBtn');
let searchResults = document.getElementById('searchResults');
let leafletMap, marker, selectedAddress = '';
// Philippines bounds (approximate)
const phBounds = L.latLngBounds(
  L.latLng(4.2158, 116.8116), // Southwest
  L.latLng(21.3210, 126.6044) // Northeast
);
openMapBtn.onclick = function() {
  mapModal.style.display = 'flex';
  setTimeout(function() {
    if (!leafletMap) {
      leafletMap = L.map('leafletMap', {
        maxBounds: phBounds,
        maxBoundsViscosity: 1.0
      }).setView([12.8797, 121.7740], 6); // Center of PH
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
      }).addTo(leafletMap);
      marker = L.marker([12.8797, 121.7740], {draggable:true}).addTo(leafletMap);
      marker.on('dragend', function(e) {
        // Keep marker within PH bounds
        if (!phBounds.contains(marker.getLatLng())) {
          marker.setLatLng(phBounds.getCenter());
          leafletMap.panTo(phBounds.getCenter());
        } else {
          leafletMap.panTo(marker.getLatLng());
        }
      });
      leafletMap.on('click', function(e) {
        if (phBounds.contains(e.latlng)) {
          marker.setLatLng(e.latlng);
        }
      });
    } else {
      leafletMap.invalidateSize();
    }
  }, 200);
};
closeMapBtn.onclick = function() {
  mapModal.style.display = 'none';
  searchResults.innerHTML = '';
  searchAddressInput.value = '';
};
searchAddressBtn.onclick = function() {
  let query = searchAddressInput.value.trim();
  if (!query) return;
  searchResults.innerHTML = '<div style="color:#888;font-size:0.95rem;">Searching...</div>';
  // Restrict search to PH only
  fetch('https://nominatim.openstreetmap.org/search?format=json&countrycodes=ph&q=' + encodeURIComponent(query))
    .then(res => res.json())
    .then(data => {
      if (data.length === 0) {
        searchResults.innerHTML = '<div style="color:#a00;font-size:0.95rem;">No results found.</div>';
        return;
      }
      searchResults.innerHTML = '';
      data.slice(0,5).forEach(function(item) {
        // Only allow results within PH bounds
        if (phBounds.contains([parseFloat(item.lat), parseFloat(item.lon)])) {
          let div = document.createElement('div');
          div.textContent = item.display_name;
          div.style.cursor = 'pointer';
          div.style.padding = '4px 0';
          div.style.borderBottom = '1px solid #eee';
          div.style.fontSize = '0.98rem';
          div.onclick = function() {
            marker.setLatLng([item.lat, item.lon]);
            leafletMap.setView([item.lat, item.lon], 16);
            selectedAddress = item.display_name;
            searchAddressInput.value = item.display_name;
            searchResults.innerHTML = '';
          };
          searchResults.appendChild(div);
        }
      });
      if (!searchResults.hasChildNodes()) {
        searchResults.innerHTML = '<div style="color:#a00;font-size:0.95rem;">No PH results found.</div>';
      }
    });
};
useLocationBtn.onclick = function() {
  if (searchAddressInput.value && selectedAddress) {
    document.getElementById('address').value = searchAddressInput.value;
  } else {
    var latlng = marker.getLatLng();
    document.getElementById('address').value = latlng.lat.toFixed(6) + ', ' + latlng.lng.toFixed(6);
  }
  mapModal.style.display = 'none';
  searchResults.innerHTML = '';
  searchAddressInput.value = '';
  selectedAddress = '';
};
</script>
</body>
</html>
