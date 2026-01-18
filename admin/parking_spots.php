<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../reglogin.php");
    exit();
}
// Prevent page caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once '../components/function.php';
$spots = get_parking_spots();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Parking Spots - SmartPark Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#20215B',
            secondary: '#64748B'
          }, 
          borderRadius: {
            'none': '0px',
            'sm': '4px',
            DEFAULT: '8px',
            'md': '12px',
            'lg': '16px',
            'xl': '20px',
            '2xl': '24px',
            '3xl': '32px',
            'full': '9999px',
            'button': '8px'
          }
        }
      }
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <style>
    .fade-in { opacity: 0; transform: translateY(30px) scale(0.98); animation: fadeInUp 0.7s cubic-bezier(.4,2,.6,1) forwards; }
    @keyframes fadeInUp { to { opacity: 1; transform: none; } }
    
    .toast {
      position: fixed;
      top: 20px;
      right: 20px;
      background: #10B981;
      color: white;
      padding: 12px 24px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 9999;
      transform: translateX(100%);
      transition: transform 0.3s ease;
    }
    .toast.show {
      transform: translateX(0);
    }
    .toast.success {
      background: #10B981;
    }
    .toast.error {
      background: #EF4444;
    }
    
    .map-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      z-index: 10000;
      display: none;
      align-items: center;
      justify-content: center;
    }
    .map-modal.show {
      display: flex;
    }
    .map-container {
      background: white;
      border-radius: 12px;
      width: 90%;
      max-width: 800px;
      height: 80%;
      max-height: 600px;
      position: relative;
    }
    .map-header {
      padding: 20px;
      border-bottom: 1px solid #e5e7eb;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .map-content {
      height: calc(100% - 80px);
      position: relative;
    }
    #locationMap {
      width: 100%;
      height: 100%;
      border-radius: 0 0 12px 12px;
    }
    .location-info {
      position: absolute;
      top: 10px;
      left: 10px;
      right: 10px;
      background: white;
      padding: 10px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      z-index: 1000;
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen">
<?php include 'includes/header.php'; ?>
<div class="flex min-h-screen gap-x-8">
  <?php include 'includes/sidebar.php'; ?>
  <main class="flex-1 flex flex-col min-h-screen p-8">
    <h1 class="text-3xl font-bold mb-8 font-['Pacifico'] text-primary fade-in">Parking Spots</h1>
    <button onclick="showAddSpotModal()" class="mb-4 px-4 py-2 bg-primary text-white rounded-button hover:bg-secondary transition fade-in" style="animation-delay:0.05s"><i class="ri-add-line"></i> Add Parking Spot</button>
    <div class="bg-white rounded-xl shadow-lg p-6 fade-in" style="animation-delay:0.1s">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="bg-primary text-white">
              <th class="px-4 py-2 text-left">Image</th>
              <th class="px-4 py-2 text-left">Name</th>
              <th class="px-4 py-2 text-left">Location</th>
              <th class="px-4 py-2 text-left">Features</th>
              <th class="px-4 py-2 text-left">Price (Hr/Dy/Wk)</th>
              <th class="px-4 py-2 text-left">Available</th>
              <th class="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($spots as $spot): ?>
            <tr class="border-b hover:bg-primary/5 transition">
              <td class="px-4 py-2">
                <?php
  if (!empty($spot['image_url'])) {
    // If the image_url starts with http, use as is (external)
    if (preg_match('/^https?:\/\//', $spot['image_url'])) {
      $imgSrc = $spot['image_url'];
    } else {
      $imgSrc = '/smartpark/' . ltrim($spot['image_url'], '/');
    }
    echo '<img src="' . htmlspecialchars($imgSrc) . '" alt="Spot Image" class="w-16 h-12 object-cover rounded" />';
  } else {
    echo '<span class="text-gray-400">No Image</span>';
  }
?>
              </td>
              <td class="px-4 py-2 font-semibold text-primary"><?php echo htmlspecialchars($spot['name']); ?></td>
              <td class="px-4 py-2"><?php echo htmlspecialchars($spot['location']); ?></td>
              <td class="px-4 py-2"><?php echo htmlspecialchars($spot['features']); ?></td>
              <td class="px-4 py-2">
                Ksh<?php echo number_format($spot['price_hourly'],2); ?>/hr<br>
                Ksh<?php echo number_format($spot['price_daily'],2); ?>/dy<br>
                Ksh<?php echo number_format($spot['price_weekly'],2); ?>/wk
              </td>
              <td class="px-4 py-2"><?php echo (int)$spot['available_spots']; ?></td>
              <td class="px-4 py-2">
                <button class="text-primary hover:underline mr-2" onclick="editSpotModal(<?php echo $spot['id']; ?>, <?php echo htmlspecialchars(json_encode($spot), ENT_QUOTES, 'UTF-8'); ?>)"><i class="ri-edit-2-line"></i> Edit</button>
                <button class="text-red-500 hover:underline" onclick="showAdminModal('Delete Parking Spot', 'Are you sure you want to delete this spot?', function() { submitDeleteSpot(<?php echo $spot['id']; ?>); })"><i class="ri-delete-bin-6-line"></i> Delete</button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- Map Modal -->
<div id="mapModal" class="map-modal">
  <div class="map-container">
    <div class="map-header">
      <h3 class="text-lg font-semibold">Select Location</h3>
      <button onclick="closeMapModal()" class="text-gray-500 hover:text-gray-700">
        <i class="ri-close-line text-xl"></i>
      </button>
    </div>
    <div class="map-content">
      <div id="locationMap"></div>
      <div class="location-info">
        <p class="text-sm text-gray-600">Click on the map to select a location</p>
        <p id="selectedCoords" class="text-sm font-medium text-primary"></p>
        <button onclick="selectLocation()" class="mt-2 px-4 py-2 bg-primary text-white rounded hover:bg-secondary transition text-sm">
          <i class="ri-check-line"></i> Confirm Location
        </button>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/modal.php'; ?>
<script>
let map, marker;
let selectedLatitude = null;
let selectedLongitude = null;

document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.fade-in').forEach((el, i) => {
    el.style.animationDelay = (i * 0.1 + 0.1) + 's';
  });
});

function initMap(existingLat = null, existingLng = null) {
  if (map) return;
  
  let centerLat = -1.286389; // Nairobi coordinates
  let centerLng = 36.817223;
  
  // If we have existing coordinates, use them as center
  if (existingLat && existingLng) {
    centerLat = parseFloat(existingLat);
    centerLng = parseFloat(existingLng);
    selectedLatitude = centerLat;
    selectedLongitude = centerLng;
  }
  
  map = L.map('locationMap').setView([centerLat, centerLng], 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);
  
  // Add existing marker if coordinates exist
  if (existingLat && existingLng) {
    marker = L.marker([centerLat, centerLng]).addTo(map);
    document.getElementById('selectedCoords').textContent = `Lat: ${centerLat.toFixed(6)}, Lng: ${centerLng.toFixed(6)}`;
  }
  
  map.on('click', function(e) {
    const lat = e.latlng.lat;
    const lng = e.latlng.lng;
    
    // Remove existing marker
    if (marker) {
      map.removeLayer(marker);
    }
    
    // Add new marker
    marker = L.marker([lat, lng]).addTo(map);
    
    // Update coordinates
    selectedLatitude = lat;
    selectedLongitude = lng;
    document.getElementById('selectedCoords').textContent = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
  });
}

function showMapModal(existingLat = null, existingLng = null) {
  document.getElementById('mapModal').classList.add('show');
  setTimeout(() => {
    initMap(existingLat, existingLng);
  }, 100);
}

function closeMapModal() {
  document.getElementById('mapModal').classList.remove('show');
  if (map) {
    map.remove();
    map = null;
    marker = null;
  }
}

function selectLocation() {
  if (selectedLatitude && selectedLongitude) {
    // Update hidden inputs
    document.getElementById('latitude').value = selectedLatitude;
    document.getElementById('longitude').value = selectedLongitude;
    document.getElementById('locationDisplay').value = `Lat: ${selectedLatitude.toFixed(6)}, Lng: ${selectedLongitude.toFixed(6)}`;
    closeMapModal();
  } else {
    alert('Please click on the map to select a location first.');
  }
}

function showAddSpotModal() {
  var form = `<form id='addSpotForm' class='space-y-4' enctype='multipart/form-data'>
    <div class='grid grid-cols-1 md:grid-cols-2 gap-4'>
      <div><label class='block text-gray-700 font-medium mb-1'>Name</label><input name='name' class='w-full border rounded p-2' required></div>
      <div><label class='block text-gray-700 font-medium mb-1'>Location</label><input name='location' class='w-full border rounded p-2' required></div>
      <div><label class='block text-gray-700 font-medium mb-1'>Features</label><input name='features' class='w-full border rounded p-2'></div>
      <div><label class='block text-gray-700 font-medium mb-1'>Available Spots</label><input name='available_spots' type='number' class='w-full border rounded p-2'></div>
      <div><label class='block text-gray-700 font-medium mb-1'>Hourly Price</label><input name='price_hourly' type='number' step='0.01' class='w-full border rounded p-2'></div>
      <div><label class='block text-gray-700 font-medium mb-1'>Daily Price</label><input name='price_daily' type='number' step='0.01' class='w-full border rounded p-2'></div>
      <div><label class='block text-gray-700 font-medium mb-1'>Weekly Price</label><input name='price_weekly' type='number' step='0.01' class='w-full border rounded p-2'></div>
      <div><label class='block text-gray-700 font-medium mb-1'>Operating Hours</label><input name='operating_hours' class='w-full border rounded p-2'></div>
      <div class='md:col-span-2'>
        <label class='block text-gray-700 font-medium mb-1'>Pin Location</label>
        <div class='flex gap-2'>
          <input type='text' id='locationDisplay' placeholder='Click to select location on map' class='flex-1 border rounded p-2' readonly>
          <button type='button' onclick='showMapModal()' class='px-4 py-2 bg-primary text-white rounded hover:bg-secondary transition'>
            <i class='ri-map-pin-line'></i> Select
          </button>
        </div>
        <input type='hidden' name='latitude' id='latitude'>
        <input type='hidden' name='longitude' id='longitude'>
      </div>
      <div class='md:col-span-2'><label class='block text-gray-700 font-medium mb-1'>Image</label><input name='image' type='file' accept='image/*' class='w-full border rounded p-2'></div>
    </div>
  </form>`;
  showAdminModal('Add Parking Spot', `<div class='max-h-96 overflow-y-auto pr-2'>${form}</div>`, function() {
    const formEl = document.getElementById('addSpotForm');
    const fd = new FormData(formEl);
    fetch('parking_spots_add.php', {method:'POST', body:fd})
      .then(r=>r.json()).then(res=>{
        if(res.success){ 
          showToast('Successfully added!', 'success');
          setTimeout(() => location.reload(), 1500);
        }
        else{ showToast('Add failed: ' + (res.error || 'Unknown error'), 'error'); }
      });
  });
}

function editSpotModal(id, spotData) {
  var spot = spotData;
  var locationDisplay = '';
  if (spot.latitude && spot.longitude) {
    locationDisplay = `Lat: ${parseFloat(spot.latitude).toFixed(6)}, Lng: ${parseFloat(spot.longitude).toFixed(6)}`;
  }
  
  var form = `<form id='editSpotForm' class='space-y-4' enctype='multipart/form-data'>
    <div class='grid grid-cols-1 md:grid-cols-2 gap-4'>
      <div><label class='block text-gray-700 font-medium mb-1'>Name</label><input name='name' class='w-full border rounded p-2' value='${spot.name || ''}' required></div>
      <div><label class='block text-gray-700 font-medium mb-1'>Location</label><input name='location' class='w-full border rounded p-2' value='${spot.location || ''}' required></div>
      <div><label class='block text-gray-700 font-medium mb-1'>Features</label><input name='features' class='w-full border rounded p-2' value='${spot.features || ''}'></div>
      <div><label class='block text-gray-700 font-medium mb-1'>Available Spots</label><input name='available_spots' type='number' class='w-full border rounded p-2' value='${spot.available_spots || ''}'></div>
      <div><label class='block text-gray-700 font-medium mb-1'>Hourly Price</label><input name='price_hourly' type='number' step='0.01' class='w-full border rounded p-2' value='${spot.price_hourly || ''}'></div>
      <div><label class='block text-gray-700 font-medium mb-1'>Daily Price</label><input name='price_daily' type='number' step='0.01' class='w-full border rounded p-2' value='${spot.price_daily || ''}'></div>
      <div><label class='block text-gray-700 font-medium mb-1'>Weekly Price</label><input name='price_weekly' type='number' step='0.01' class='w-full border rounded p-2' value='${spot.price_weekly || ''}'></div>
      <div><label class='block text-gray-700 font-medium mb-1'>Operating Hours</label><input name='operating_hours' class='w-full border rounded p-2' value='${spot.operating_hours || ''}'></div>
      <div class='md:col-span-2'>
        <label class='block text-gray-700 font-medium mb-1'>Pin Location</label>
        <div class='flex gap-2'>
          <input type='text' id='locationDisplay' placeholder='Click to select location on map' class='flex-1 border rounded p-2' value='${locationDisplay}' readonly>
          <button type='button' onclick='showMapModal(${spot.latitude || null}, ${spot.longitude || null})' class='px-4 py-2 bg-primary text-white rounded hover:bg-secondary transition'>
            <i class='ri-map-pin-line'></i> Select
          </button>
        </div>
        <input type='hidden' name='latitude' id='latitude' value='${spot.latitude || ''}'>
        <input type='hidden' name='longitude' id='longitude' value='${spot.longitude || ''}'>
      </div>
      <div class='md:col-span-2'><label class='block text-gray-700 font-medium mb-1'>Image (leave blank to keep current)</label><input name='image' type='file' accept='image/*' class='w-full border rounded p-2'></div>
    </div>
  </form>`;
  showAdminModal('Edit Parking Spot', `<div class='max-h-96 overflow-y-auto pr-2'>${form}</div>`, function() {
    const formEl = document.getElementById('editSpotForm');
    const fd = new FormData(formEl);
    fd.append('id', id);
    fetch('parking_spots_edit.php', {method:'POST', body:fd})
      .then(r=>r.json()).then(res=>{
        if(res.success){ location.reload(); }
        else{ alert('Update failed'); }
      });
  });
}

function submitDeleteSpot(id) {
  fetch('parking_spots_delete.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'id='+id})
    .then(r=>r.json()).then(res=>{
      if(res.success){ 
        showToast('Successfully deleted!', 'success');
        setTimeout(() => location.reload(), 1500);
      }
      else{ showToast('Delete failed', 'error'); }
    });
}

function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.textContent = message;
  document.body.appendChild(toast);
  
  setTimeout(() => toast.classList.add('show'), 100);
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => document.body.removeChild(toast), 300);
  }, 3000);
}
</script>
</body>
</html>
