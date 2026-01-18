<?php session_start();
if (!isset($_SESSION['user'])) {
  header('Location: ../reglogin.php');
  exit();
}
// Prevent page caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
?>
<?php require_once '../components/function.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Find Parking - SmartPark Innovations</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
theme: {
extend: {
colors: {
primary: '#20215B',
secondary: '#34D399'
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
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
<style>
:where([class^="ri-"])::before { content: "\f3c2"; }
.range-slider::-webkit-slider-thumb {
appearance: none;
width: 20px;
height: 20px;
background: #20215B;
border-radius: 50%;
cursor: pointer;
}
.range-slider::-webkit-slider-runnable-track {
height: 4px;
background: #e5e7eb;
border-radius: 2px;
}
.leaflet-control-zoom {
  top: 1rem !important;
  right: 1rem !important;
  left: auto !important;
  background: transparent;
  box-shadow: none;
}
.leaflet-control-zoom-in, .leaflet-control-zoom-out {
  background: #20215B !important;
  color: #fff !important;
  border: none !important;
  width: 40px;
  height: 40px;
  font-size: 2rem;
  border-radius: 50%;
  margin-bottom: 8px;
  box-shadow: 0 2px 8px rgba(32,33,91,0.08);
  transition: background 0.2s;
}
.leaflet-control-zoom-in:hover, .leaflet-control-zoom-out:hover {
  background: #18194a !important;
}
</style>
</head>
<body class="bg-gray-50">
<?php include 'includes/header.php'; ?>
<div class="pt-16">
<div class="max-w-7xl mx-auto px-4 py-6">
<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
<form method="GET" class="flex items-center space-x-6">
<div class="flex-1">
<label class="block text-sm text-gray-600 mb-1">Price Range</label>
<input type="range" name="max_price" min="0" max="50" value="<?php echo isset($_GET['max_price']) ? (int)$_GET['max_price'] : 50; ?>" class="range-slider w-full" oninput="this.nextElementSibling.innerText = '$' + this.value">
<div class="flex justify-between text-sm text-gray-600 mt-1">
<span>$0</span>
<span>$<?php echo isset($_GET['max_price']) ? (int)$_GET['max_price'] : 50; ?></span>
</div>
</div>
<div class="flex-1">
<label class="block text-sm text-gray-600 mb-1">Duration</label>
<select name="duration" class="w-full border border-gray-200 rounded !rounded-button text-sm">
<option value="hourly" <?php if (isset($_GET['duration']) && $_GET['duration'] == 'hourly') echo 'selected'; ?>>Hourly</option>
<option value="daily" <?php if (isset($_GET['duration']) && $_GET['duration'] == 'daily') echo 'selected'; ?>>Daily</option>
<option value="weekly" <?php if (isset($_GET['duration']) && $_GET['duration'] == 'weekly') echo 'selected'; ?>>Weekly</option>
</select>
</div>
<div class="flex-1">
<label class="block text-sm text-gray-600 mb-1">Features</label>
<select name="feature" class="w-full border border-gray-200 rounded !rounded-button text-sm">
<option value="">Any</option>
<option value="Covered" <?php if (isset($_GET['feature']) && $_GET['feature'] == 'Covered') echo 'selected'; ?>>Covered</option>
<option value="EV" <?php if (isset($_GET['feature']) && $_GET['feature'] == 'EV') echo 'selected'; ?>>EV</option>
<option value="Handicap" <?php if (isset($_GET['feature']) && $_GET['feature'] == 'Handicap') echo 'selected'; ?>>Handicap</option>
</select>
</div>
<div>
<button type="submit" class="bg-[#20215B] text-white px-6 py-2 rounded-[12px]">Filter</button>


</div>
</form>
</div>
<div class="grid grid-cols-12 gap-6">
<div class="col-span-8">
  <div class="bg-white rounded-lg shadow-sm overflow-hidden relative" style="height: 600px;">
    <div id="map" style="width: 100%; height: 100%;"></div>
  </div>
</div>
<div class="col-span-4 space-y-4">
<div class="flex justify-between items-center">
<h3 class="text-lg font-semibold">Available Spots</h3>
<form method="GET" class="inline">
<select name="sort" class="border-none text-sm text-gray-600 pr-8" onchange="this.form.submit()">
<option value="price" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'price') echo 'selected'; ?>>Sort by: Price</option>
<option value="distance" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'distance') echo 'selected'; ?>>Sort by: Distance</option>
<option value="availability" <?php if (isset($_GET['sort']) && $_GET['sort'] == 'availability') echo 'selected'; ?>>Sort by: Availability</option>
</select>
</form>
</div>
<div class="space-y-4 h-[540px] overflow-auto pr-2">
<?php
// Fetch parking spots from DB
$spots = get_parking_spots();
// Filtering (simple PHP, can be expanded)
if (isset($_GET['max_price'])) {
    $max = (float)$_GET['max_price'];
    $duration = $_GET['duration'] ?? 'hourly';
    $price_col = $duration === 'daily' ? 'price_daily' : ($duration === 'weekly' ? 'price_weekly' : 'price_hourly');
    $spots = array_filter($spots, function($s) use ($max, $price_col) {
        return $s[$price_col] <= $max;
    });
}
if (!empty($_GET['feature'])) {
    $feature = $_GET['feature'];
    $spots = array_filter($spots, function($s) use ($feature) {
        return strpos($s['features'], $feature) !== false;
    });
}
// Sorting
if (!empty($_GET['sort'])) {
    if ($_GET['sort'] == 'price') {
        usort($spots, function($a, $b) {
            return $a['price_hourly'] <=> $b['price_hourly'];
        });
    } elseif ($_GET['sort'] == 'availability') {
        usort($spots, function($a, $b) {
            return $b['available_spots'] <=> $a['available_spots'];
        });
    }
    // Distance sorting would require user location and spot lat/lng
}
foreach ($spots as $spot): ?>
<div class="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow cursor-pointer">
<div class="flex justify-between items-start mb-3">
<h4 class="font-semibold"><?php echo htmlspecialchars($spot['name']); ?></h4>
<span class="text-green-500 text-sm"><?php echo isset($spot['distance']) ? $spot['distance'].' mi' : ''; ?></span>
</div>
<div class="flex items-center space-x-4 mb-3">
<span class="text-sm text-gray-600"><?php echo (int)$spot['available_spots']; ?> spots available</span>
<span class="text-sm text-gray-600">•</span>
<span class="text-sm text-gray-600"><?php echo htmlspecialchars($spot['operating_hours']); ?></span>
</div>
<div class="flex items-center justify-between">
<div>
<span class="text-2xl font-bold text-primary">
<?php
$duration = $_GET['duration'] ?? 'hourly';
echo 'Ksh' . ($duration === 'daily' ? $spot['price_daily'] : ($duration === 'weekly' ? $spot['price_weekly'] : $spot['price_hourly']));
?>
</span>
<span class="text-gray-600">/<?php echo $duration; ?></span>
</div>
<a href="?spot_id=<?php echo $spot['id']; ?>" class="bg-[#20215B] text-green-400 px-4 py-2 rounded-md hover:bg-[#1a1b4d]">Reserve Now</a>

</div>
</div>
<?php endforeach; ?>
</div>
</div>
</div>
</div>
</div>
<?php
// Spot details modal (PHP version)
if (isset($_GET['spot_id'])) {
    $spot_id = (int)$_GET['spot_id'];
    $spots = get_parking_spots();
    $spot = null;
    foreach ($spots as $s) {
        if ($s['id'] == $spot_id) {
            $spot = $s;
            break;
        }
    }
    if ($spot): ?>
    <div id="spotDetailsModal" style="z-index:9999; position:fixed; inset:0; background:rgba(0,0,0,0.5);" class="flex items-center justify-center">
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl w-[600px]" style="z-index:10000;">
    <div class="p-6">
    <div class="flex justify-between items-start mb-4">
    <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($spot['name']); ?></h3>
    <a href="parking.php" class="text-gray-400 hover:text-gray-600"><i class="ri-close-line text-2xl"></i></a>
    </div>
    <div class="mb-6">
    <?php
      $image_url = !empty($spot['image_url']) ? $spot['image_url'] : 'https://public.readdy.ai/ai/img_res/df41e2f7c752e8d6e1bcb9df009097ae.jpg';
      // If the image_url starts with http, use as is (external)
      if (preg_match('/^https?:\/\//', $image_url)) {
        $imgSrc = $image_url;
      } else {
        $imgSrc = '/smartpark/' . ltrim($image_url, '/');
      }
    ?>
    <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Parking Facility" class="w-full h-48 object-cover rounded">
    </div>
    <div class="grid grid-cols-2 gap-4 mb-6">
    <div>
    <h4 class="font-semibold mb-2">Location</h4>
    <p class="text-gray-600"><?php echo htmlspecialchars($spot['location']); ?></p>
    </div>
    <div>
    <h4 class="font-semibold mb-2">Operating Hours</h4>
    <p class="text-gray-600"><?php echo htmlspecialchars($spot['operating_hours']); ?></p>
    </div>
    <div>
    <h4 class="font-semibold mb-2">Features</h4>
    <div class="flex space-x-2">
    <?php foreach (explode(',', $spot['features']) as $f): ?>
    <span class="px-2 py-1 bg-gray-100 rounded-full text-sm"><?php echo htmlspecialchars($f); ?></span>
    <?php endforeach; ?>
    </div>
    </div>
    <div>
    <h4 class="font-semibold mb-2">Price</h4>
    <p class="text-gray-600">
    Ksh<?php echo $spot['price_hourly']; ?>/hour • Ksh<?php echo $spot['price_daily']; ?>/day • Ksh<?php echo $spot['price_weekly']; ?>/week
    </p>
    </div>
    </div>
    <a href="receipt.php?spot_id=<?php echo $spot['id']; ?>" class="w-full inline-block text-center bg-green-500 text-white py-3 !rounded-button hover:bg-green-600 mt-4">Reserve Now</a>
    </div>
    </div>
    </div>
    <?php endif; }
?>
<?php
// Output PHP spots as JSON for JS
$spots_for_map = array_map(function($s) {
    return [
        'id' => $s['id'],
        'name' => $s['name'],
        'lat' => (float)$s['latitude'],
        'lng' => (float)$s['longitude'],
        'location' => $s['location'],
        'available_spots' => $s['available_spots'],
    ];
}, $spots);
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var map = L.map('map', {
    zoomControl: true
  }).setView([-1.286389, 36.817223], 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);
  // Move zoom control to top right
  map.zoomControl.setPosition('topright');

  var spots = <?php echo json_encode(array_values($spots_for_map)); ?>;
  spots.forEach(function(spot) {
    if (!spot.lat || !spot.lng) return;
    var marker = L.marker([spot.lat, spot.lng]).addTo(map);
    var popupContent = '<div><strong>' + spot.name + '</strong><br>' + spot.location + '<br>Available: ' + spot.available_spots +
      ' <a href="?spot_id=' + spot.id + '" class="ml-2 bg-[#20215B] text-green-400 px-2 py-1 rounded-md hover:bg-[#1a1b4d] text-sm">Reserve Now</a></div>';
    marker.bindPopup(popupContent);
  });
});
</script>
<?php include 'includes/footer.php'; ?>
</body>
</html>
