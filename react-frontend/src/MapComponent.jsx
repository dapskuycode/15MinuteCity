import { MapContainer, TileLayer, Polygon, Marker, Popup, useMapEvents } from 'react-leaflet';
import { useState } from 'react';
import axios from 'axios';
import api from './api';
import 'leaflet/dist/leaflet.css';

function MapComponent() {
  const [polygonCoords, setPolygonCoords] = useState([]);
  const [position, setPosition] = useState(null);
  const [publicServices, setPublicServices] = useState([]);

  function LocationHandler() {
    useMapEvents({
      click: async (e) => {
        const { lat, lng } = e.latlng;
        setPosition([lat, lng]);
        console.log(`📍 Lokasi dipilih: ${lat}, ${lng}`);

        const result = await checkPointInDatabase(lat, lng);

        if (result?.exists && result?.zone_polygon?.coordinates?.length > 0) {
          const polygon = result.zone_polygon.coordinates[0].map(([lng, lat]) => [lat, lng]);
          setPolygonCoords(polygon);
          console.log('✅ Polygon dari DB ditampilkan');

          const services = await getPublicServicesInZone(result.search_id);
          setPublicServices(services);
        } else {
          console.log('🔄 Tidak ada data di DB, mengambil dari ORS...');
          const coords = await fetchIsochrone(lat, lng);
          setPolygonCoords(coords);

          const searchId = await savePointToDatabase(lat, lng);
          if (searchId) {
            await saveZoneToDatabase(searchId, coords);
            console.log('🆕 Titik dan polygon baru disimpan');

            // Ambil fasilitas publik setelah polygon berhasil disimpan
            const services = await getPublicServicesInZone(searchId);
            setPublicServices(services);
          } else {
            // Kalau gagal simpan titik, tetap coba ambil fasilitas berdasarkan polygon langsung (tanpa ID)
            const services = await getPublicServicesFromPolygon(coords);
            setPublicServices(services);
          }
        }
      },
    });

    return null;
  }

  async function checkPointInDatabase(lat, lng) {
    try {
      const res = await api.post('/check-user-search', { lat, lng });
      console.log('🔍 Response dari DB:', res.data);
      return res.data;
    } catch (err) {
      console.error('Error checking DB:', err.response?.data || err.message);
      return null;
    }
  }

  async function fetchIsochrone(lat, lng) {
    try {
      console.log('🌐 Mengambil data dari ORS...');
      const res = await axios.post(
        'https://api.openrouteservice.org/v2/isochrones/foot-walking',
        {
          locations: [[lng, lat]],
          range: [900],
          attributes: ['area'],
        },
        {
          headers: {
            Authorization: import.meta.env.VITE_ORS_API_KEY,
            'Content-Type': 'application/json',
          },
        }
      );

      return res.data.features[0].geometry.coordinates[0].map(
        ([lng, lat]) => [lat, lng]
      );
    } catch (err) {
      console.error('Error fetching ORS:', err.response?.data || err.message);
      return [];
    }
  }

  async function savePointToDatabase(lat, lng) {
    try {
      const res = await api.post('/user-searches', {
        lat,
        lng,
        search_radius: 900,
      });
      console.log('💾 Titik berhasil disimpan:', res.data.id);
      return res.data.id;
    } catch (err) {
      console.error('Gagal simpan titik:', err.response?.data || err.message);
      return null;
    }
  }

  async function saveZoneToDatabase(searchId, coords) {
    try {
      const geojson = {
        type: 'Polygon',
        coordinates: [[...coords.map(([lat, lng]) => [lng, lat])]],
      };

      await api.post('/walkability-zones', {
        search_id: searchId,
        polygon: geojson,
        zone_type: 'walking',
        travel_time: 15,
      });
      console.log('✅ Polygon berhasil disimpan.');
    } catch (err) {
      console.error('Gagal simpan polygon:', err.response?.data || err.message);
    }
  }

  async function getPublicServicesInZone(searchId) {
    try {
      const res = await api.get(`/public-services-in-zone/${searchId}`);
      console.log('📦 Fasilitas publik:', res.data);
      return res.data;
    } catch (err) {
      console.error('❌ Gagal mengambil fasilitas:', err.response?.data || err.message);
      return [];
    }
  }
  
  async function getPublicServicesFromPolygon(coords) {
    try {
      const polygonGeoJSON = {
        type: "Polygon",
        coordinates: [[...coords.map(([lat, lng]) => [lng, lat])]],
      };

      const res = await api.post('/public-services/in-polygon', {
        polygon: polygonGeoJSON,
      });

      return res.data.data;
    } catch (err) {
      console.error('Gagal ambil fasilitas (polygon langsung):', err.response?.data || err.message);
      return [];
    }
  }


  return (
    <MapContainer
      center={[-6.9667, 110.4167]}
      zoom={13}
      scrollWheelZoom={true}
      style={{ height: '100vh', width: '100%' }}
    >
      <TileLayer
        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        attribution="&copy; OpenStreetMap contributors"
      />
      <LocationHandler />
      {position && <Marker position={position} />}
      {polygonCoords.length > 0 && (
        <Polygon
          positions={polygonCoords}
          pathOptions={{ color: 'blue', fillOpacity: 0.3 }}
        />
      )}
      {publicServices.map((item, index) => (
        <Marker key={index} position={[item.lat, item.lng]}>
          <Popup>
            <strong>{item.name}</strong><br />
            {item.description || 'Tidak ada deskripsi'}
          </Popup>
        </Marker>
      ))}
    </MapContainer>
  );
}

export default MapComponent;
