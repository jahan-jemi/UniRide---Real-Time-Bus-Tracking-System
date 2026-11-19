<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UniRide Live Bus Tracker</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        h1 {
            margin: 0;
            padding: 16px;
            background: #1a3c6e;
            color: white;
            font-size: 19px;
        }
        #map {
            position: absolute;
            top: 56px;
            bottom: 0;
            left: 0;
            right: 0;
        }
        #empty {
            display: none;
            position: absolute;
            top: 70px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 500;
            background: white;
            padding: 10px;
            border-radius: 7px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

    <h1>🚌 UniRide - Live Bus Tracker</h1>
    <div id="map"></div>
    <div id="empty">No buses are currently active.</div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Small JavaScript section: fetch live bus positions and move map markers.
        const map = L.map('map').setView([23.8103, 90.4125], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let markers = {};

        function updateBuses() {
            fetch('../backend/get_locations.php')
                .then(response => response.json())
                .then(buses => {
                    document.getElementById('empty').style.display = buses.length === 0 ? 'block' : 'none';
                    let active = [];
                    
                    buses.forEach(bus => {
                        let id = bus.driver_name + '-' + bus.bus_name;
                        let position = [bus.latitude, bus.longitude]; 
                        active.push(id);
                        
                        if (markers[id]) {
                            markers[id].setLatLng(position);
                        } else {
                            markers[id] = L.marker(position).addTo(map);
                        }
                        
                        markers[id].bindTooltip(bus.bus_name + ' - ' + bus.driver_name, { permanent: true });
                    });
                    
                    for (let id in markers) {
                        if (!active.includes(id)) {
                            map.removeLayer(markers[id]);
                            delete markers[id];
                        }
                    }
                })
                .catch(() => {});
        }

        updateBuses();
        setInterval(updateBuses, 3000);
    </script>

</body>
</html>