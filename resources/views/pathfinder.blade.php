<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Semarang Pathfinder - A* Algorithm with Road Weighting</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 25%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            padding: 20px;
            color: #e2e8f0;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            color: white;
        }

        .header h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #00d4ff 0%, #5b86e5 50%, #36d1dc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 4px 8px rgba(0, 212, 255, 0.3);
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.8;
            font-weight: 300;
            color: #94a3b8;
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            align-items: start;
        }

        .controls-panel {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(148, 163, 184, 0.1);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .controls-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 28px;
            color: #f1f5f9;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .controls-title i {
            color: #00d4ff;
        }

        .form-group {
            margin-bottom: 28px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: #cbd5e1;
            font-size: 0.95rem;
        }

        .form-group label i {
            color: #00d4ff;
            margin-right: 8px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid rgba(148, 163, 184, 0.2);
            border-radius: 16px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(15, 23, 42, 0.6);
            color: #f1f5f9;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 0 4px rgba(0, 212, 255, 0.1);
            transform: translateY(-2px);
            background: rgba(15, 23, 42, 0.8);
        }

        .form-group select option {
            background: #1e293b;
            color: #f1f5f9;
        }

        .btn {
            width: 100%;
            padding: 16px 28px;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-family: inherit;
            text-transform: none;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, #00d4ff 0%, #5b86e5 100%);
            color: white;
            margin-top: 12px;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 212, 255, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            margin-top: 16px;
        }

        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(255, 107, 107, 0.4);
        }

        .message {
            margin-top: 16px;
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            border-left: 4px solid;
        }

        .message.success {
            background: rgba(34, 197, 94, 0.1);
            color: #4ade80;
            border-left-color: #22c55e;
        }

        .message.error {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border-left-color: #ef4444;
        }

        .visualization-panel {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(148, 163, 184, 0.1);
        }

        .viz-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: #f1f5f9;
            padding: 28px 32px;
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 14px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
        }

        .viz-header i {
            color: #00d4ff;
        }

        #map {
            height: 500px;
            width: 100%;
            position: relative;
        }

        .results-section {
            padding: 32px;
            display: none;
        }

        .results-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            margin-bottom: 32px;
        }

        .result-card {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8) 0%, rgba(30, 41, 59, 0.6) 100%);
            border-radius: 20px;
            padding: 28px;
            text-align: center;
            border: 1px solid rgba(148, 163, 184, 0.1);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .result-card:hover {
            transform: translateY(-5px);
        }

        .result-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #00d4ff 0%, #5b86e5 100%);
        }

        .result-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #00d4ff 0%, #5b86e5 100%);
            border-radius: 50%;
            margin: 0 auto 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            box-shadow: 0 10px 25px rgba(0, 212, 255, 0.3);
        }

        .result-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #f1f5f9;
            margin-bottom: 16px;
        }

        .solution-list {
            font-size: 16px;
            line-height: 1.8;
            color: #cbd5e1;
        }

        .distance-display {
            font-size: 2.2rem;
            background: linear-gradient(135deg, #00d4ff 0%, #5b86e5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
            margin-top: 12px;
        }

        .distance-details {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8) 0%, rgba(30, 41, 59, 0.6) 100%);
            border-radius: 20px;
            padding: 28px;
            border: 1px solid rgba(148, 163, 184, 0.1);
        }

        .details-header {
            font-size: 1.2rem;
            font-weight: 600;
            color: #f1f5f9;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .details-header i {
            color: #00d4ff;
        }

        .segment-list {
            max-height: 320px;
            overflow-y: auto;
            margin-bottom: 24px;
        }

        .segment-list::-webkit-scrollbar {
            width: 8px;
        }

        .segment-list::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.5);
            border-radius: 4px;
        }

        .segment-list::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #00d4ff 0%, #5b86e5 100%);
            border-radius: 4px;
        }

        .segment-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            margin: 10px 0;
            background: rgba(15, 23, 42, 0.6);
            border-radius: 14px;
            border-left: 4px solid;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .segment-item:hover {
            transform: translateX(8px);
            box-shadow: 0 8px 25px rgba(0, 212, 255, 0.15);
            background: rgba(15, 23, 42, 0.8);
        }

        .segment-item.highway {
            border-left-color: #10b981;
        }

        .segment-item.primary {
            border-left-color: #3b82f6;
        }

        .segment-item.secondary {
            border-left-color: #f59e0b;
        }

        .segment-item.residential {
            border-left-color: #ef4444;
        }

        .segment-from-to {
            flex: 1;
            font-size: 14px;
            color: #cbd5e1;
            font-weight: 500;
        }

        .segment-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
        }

        .segment-distance {
            font-weight: 600;
            color: #00d4ff;
            font-size: 14px;
            background: rgba(0, 212, 255, 0.1);
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid rgba(0, 212, 255, 0.2);
        }

        .road-type-badge {
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .road-type-badge.highway {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .road-type-badge.primary {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
        }

        .road-type-badge.secondary {
            background: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
        }

        .road-type-badge.residential {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .total-summary {
            background: linear-gradient(135deg, #00d4ff 0%, #5b86e5 100%);
            color: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 25px rgba(0, 212, 255, 0.3);
        }

        .summary-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
        }

        .summary-item {
            text-align: center;
            padding: 18px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease;
        }

        .summary-item:hover {
            transform: scale(1.05);
        }

        .summary-label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 6px;
        }

        .summary-value {
            font-size: 1.3rem;
            font-weight: 700;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 24px;
            color: #00d4ff;
        }

        .loading i {
            font-size: 2.5rem;
            animation: spin 1s linear infinite;
            margin-bottom: 12px;
        }

        .loading p {
            color: #cbd5e1;
            font-weight: 500;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        .algorithm-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #5b86e5 0%, #36d1dc 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-left: 12px;
            box-shadow: 0 4px 12px rgba(91, 134, 229, 0.3);
        }

        .weight-info {
            background: rgba(91, 134, 229, 0.1);
            border: 1px solid rgba(91, 134, 229, 0.3);
            border-radius: 12px;
            padding: 16px;
            margin-top: 20px;
        }

        .weight-info h4 {
            color: #5b86e5;
            margin-bottom: 12px;
            font-size: 1rem;
        }

        .weight-legend {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 8px;
            font-size: 12px;
        }

        .weight-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px;
            border-radius: 8px;
            background: rgba(15, 23, 42, 0.3);
        }

        .weight-color {
            width: 12px;
            height: 12px;
            border-radius: 2px;
        }

        .status-info {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
            text-align: center;
        }

        .status-info i {
            color: #22c55e;
            margin-right: 8px;
        }

        .status-info span {
            color: #4ade80;
            font-weight: 500;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main-content {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .controls-panel {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2.2rem;
            }

            .results-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-route"></i>
                Semarang Pathfinder
                <span class="algorithm-badge">
                    <i class="fas fa-brain"></i>
                    A* + Road Weighting
                </span>
            </h1>
            <p>Temukan rute terpendek dengan sistem pembobotan jalan menggunakan algoritma A*</p>
        </div>

        <div class="main-content">
            <div class="controls-panel">
                <div class="controls-title">
                    <i class="fas fa-cog"></i>
                    Pengaturan Rute
                </div>

                <div class="status-info">
                    <i class="fas fa-check-circle"></i>
                    <span>Data Semarang siap digunakan (30 lokasi)</span>
                </div>

                <div class="form-group">
                    <label for="startLocation">
                        <i class="fas fa-play-circle"></i> Lokasi Awal:
                    </label>
                    <select id="startLocation">
                        <option value="">Pilih lokasi awal</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="endLocation">
                        <i class="fas fa-flag-checkered"></i> Lokasi Tujuan:
                    </label>
                    <select id="endLocation">
                        <option value="">Pilih lokasi tujuan</option>
                    </select>
                </div>

                <button class="btn btn-primary" onclick="findPath()">
                    <i class="fas fa-search"></i>
                    Cari Rute Optimal
                </button>

                <div class="weight-info">
                    <h4><i class="fas fa-weight-hanging"></i> Sistem Pembobotan Jalan</h4>
                    <div class="weight-legend">
                        <div class="weight-item">
                            <div class="weight-color" style="background: #10b981;"></div>
                            <span>Tol (0.5x)</span>
                        </div>
                        <div class="weight-item">
                            <div class="weight-color" style="background: #3b82f6;"></div>
                            <span>Raya (0.7x)</span>
                        </div>
                        <div class="weight-item">
                            <div class="weight-color" style="background: #f59e0b;"></div>
                            <span>Kecil (1.0x)</span>
                        </div>
                        <div class="weight-item">
                            <div class="weight-color" style="background: #ef4444;"></div>
                            <span>Gang (1.5x)</span>
                        </div>
                    </div>
                </div>

                <div id="message"></div>

                <div class="loading" id="loading">
                    <i class="fas fa-spinner"></i>
                    <p>Mencari rute optimal...</p>
                </div>
            </div>

            <div class="visualization-panel">
                <div class="viz-header">
                    <i class="fas fa-map-marked-alt"></i>
                    Visualisasi Peta & Rute
                </div>
                <div id="map"></div>

                <div class="results-section" id="results">
                    <div class="results-grid">
                        <div class="result-card">
                            <div class="result-icon">
                                <i class="fas fa-list-ol"></i>
                            </div>
                            <div class="result-title">Rute Perjalanan</div>
                            <div class="solution-list" id="solutionList"></div>
                        </div>

                        <div class="result-card">
                            <div class="result-icon">
                                <i class="fas fa-ruler"></i>
                            </div>
                            <div class="result-title">Total Jarak</div>
                            <div class="distance-display" id="distanceResult"></div>
                        </div>
                    </div>

                    <div class="distance-details">
                        <div class="details-header">
                            <i class="fas fa-route"></i>
                            Detail Perjalanan dengan Pembobotan
                        </div>

                        <div class="segment-list" id="segmentList"></div>

                        <div class="total-summary">
                            <div class="summary-title">
                                <i class="fas fa-chart-bar"></i>
                                Ringkasan Rute Optimal
                            </div>
                            <div class="summary-grid">
                                <div class="summary-item">
                                    <div class="summary-label">Jarak Aktual</div>
                                    <div class="summary-value" id="totalDistanceDetail"></div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-label">Jarak Berbobot</div>
                                    <div class="summary-value" id="totalWeightedDistance"></div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-label">Jumlah Segmen</div>
                                    <div class="summary-value" id="totalSegments"></div>
                                </div>
                                <div class="summary-item">
                                    <div class="summary-label">Jumlah Titik</div>
                                    <div class="summary-value" id="totalNodes"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map;
        let markers = [];
        let pathLine;

        // CSRF Token setup
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Data nodes Semarang yang sudah dideklarasikan langsung
        const semarangNodes = [
            {name: 'Kota Lama Semarang', latitude: -6.968339658674677, longitude: 110.42844065022399},
            {name: 'Jl. Letjen Suprapto 35', latitude: -6.968366588116826, longitude: 110.42805767097872},
            {name: 'Jl. Kepodang No.15-16', latitude: -6.969279838846258, longitude: 110.42808001618744},
            {name: 'Jl. Letjen Suprapto 30', latitude: -6.968419, longitude: 110.427533},
            {name: 'Jl. Kepodang 105-33', latitude: -6.969305, longitude: 110.427736},
            {name: 'Jl. Kepodang', latitude: -6.969314, longitude: 110.427586},
            {name: 'Jl. Sendowo 17', latitude: -6.969685, longitude: 110.427756},
            {name: 'Jl. Sendowo 1', latitude: -6.969695, longitude: 110.427605},
            {name: 'Jl. Sendowo 2', latitude: -6.969711, longitude: 110.427680},
            {name: 'Jl. Suari 12', latitude: -6.969922, longitude: 110.427722},
            {name: 'Jl. Pekojan 1', latitude: -6.970796, longitude: 110.427884},
            {name: 'Jl. Pekojan 2', latitude: -6.971091, longitude: 110.427123},
            {name: 'Jl. Ki Nartosabdo 6', latitude: -6.971321, longitude: 110.424421},
            {name: 'Jl. Kenari 1', latitude: -6.970073, longitude: 110.426940},
            {name: 'Jl. Kenari 2', latitude: -6.970266, longitude: 110.426208},
            {name: 'Jl. MPU Tantular 1', latitude: -6.970411, longitude: 110.425727},
            {name: 'Jl. Mpu Tantular 2', latitude: -6.971386, longitude: 110.425715},
            {name: 'Jl. Kyai H. Agus Salim No.7', latitude: -6.971139, longitude: 110.426542},
            {name: 'Jl. Kyai H. Agus Salim No.121', latitude: -6.971226, longitude: 110.426547},
            {name: 'Jl. Sendowo 7-9', latitude: -6.969946, longitude: 110.425726},
            {name: 'Jl. Sendowo Purwodinatan', latitude: -6.969857, longitude: 110.426111},
            {name: 'Jl. Sendowo Barat', latitude: -6.969793, longitude: 110.426594},
            {name: 'Jl. Roda II', latitude: -6.969326, longitude: 110.426602},
            {name: 'Jl. Sendowo', latitude: -6.969543, longitude: 110.425712},
            {name: 'Jl. Branjangan', latitude: -6.968525, longitude: 110.426571},
            {name: 'Jl. Imam Bonjol Depan Rumah Pompa', latitude: -6.968762, longitude: 110.425227},
            {name: 'Jl. Kolonel Sugiyono', latitude: -6.969039, longitude: 110.424799},
            {name: 'Jl. Imam Bonjol', latitude: -6.969987, longitude: 110.423877},
            {name: 'Jl. Pemuda', latitude: -6.971111, longitude: 110.423056},
            {name: 'Alun-Alun Masjid Agung', latitude: -6.971354, longitude: 110.423858}
        ];

        // Data koneksi dengan road types
        const connections = [
            {from: 'Kota Lama Semarang', to: 'Jl. Letjen Suprapto 35', roadType: 'primary'},
            {from: 'Jl. Letjen Suprapto 35', to: 'Jl. Kepodang No.15-16', roadType: 'secondary'},
            {from: 'Jl. Letjen Suprapto 35', to: 'Jl. Letjen Suprapto 30', roadType: 'primary'},
            {from: 'Jl. Kepodang No.15-16', to: 'Jl. Kepodang 105-33', roadType: 'secondary'},
            {from: 'Jl. Letjen Suprapto 30', to: 'Jl. Kepodang', roadType: 'secondary'},
            {from: 'Jl. Letjen Suprapto 30', to: 'Jl. Branjangan', roadType: 'secondary'},
            {from: 'Jl. Kepodang 105-33', to: 'Jl. Kepodang', roadType: 'secondary'},
            {from: 'Jl. Kepodang 105-33', to: 'Jl. Sendowo 17', roadType: 'residential'},
            {from: 'Jl. Kepodang', to: 'Jl. Roda II', roadType: 'secondary'},
            {from: 'Jl. Sendowo 17', to: 'Jl. Sendowo 2', roadType: 'residential'},
            {from: 'Jl. Sendowo 1', to: 'Jl. Kepodang', roadType: 'residential'},
            {from: 'Jl. Sendowo 1', to: 'Jl. Sendowo 2', roadType: 'residential'},
            {from: 'Jl. Sendowo 2', to: 'Jl. Suari 12', roadType: 'residential'},
            {from: 'Jl. Suari 12', to: 'Jl. Pekojan 1', roadType: 'secondary'},
            {from: 'Jl. Suari 12', to: 'Jl. Kenari 1', roadType: 'secondary'},
            {from: 'Jl. Pekojan 1', to: 'Jl. Pekojan 2', roadType: 'secondary'},
            {from: 'Jl. Pekojan 2', to: 'Jl. Kyai H. Agus Salim No.7', roadType: 'secondary'},
            {from: 'Jl. Ki Nartosabdo 6', to: 'Jl. Imam Bonjol', roadType: 'primary'},
            {from: 'Jl. Ki Nartosabdo 6', to: 'Alun-Alun Masjid Agung', roadType: 'primary'},
            {from: 'Jl. Ki Nartosabdo 6', to: 'Jl. Kyai H. Agus Salim No.121', roadType: 'secondary'},
            {from: 'Jl. Kenari 1', to: 'Jl. Kenari 2', roadType: 'secondary'},
            {from: 'Jl. Kenari 2', to: 'Jl. MPU Tantular 1', roadType: 'secondary'},
            {from: 'Jl. Kenari 2', to: 'Jl. Sendowo Purwodinatan', roadType: 'residential'},
            {from: 'Jl. MPU Tantular 1', to: 'Jl. Mpu Tantular 2', roadType: 'secondary'},
            {from: 'Jl. MPU Tantular 1', to: 'Jl. Sendowo 7-9', roadType: 'residential'},
            {from: 'Jl. Mpu Tantular 2', to: 'Jl. Kyai H. Agus Salim No.7', roadType: 'secondary'},
            {from: 'Jl. Kyai H. Agus Salim No.7', to: 'Jl. Kyai H. Agus Salim No.121', roadType: 'secondary'},
            {from: 'Jl. Sendowo 7-9', to: 'Jl. Sendowo Purwodinatan', roadType: 'residential'},
            {from: 'Jl. Sendowo 7-9', to: 'Jl. Sendowo', roadType: 'residential'},
            {from: 'Jl. Sendowo Purwodinatan', to: 'Jl. Sendowo Barat', roadType: 'residential'},
            {from: 'Jl. Sendowo Barat', to: 'Jl. Sendowo 1', roadType: 'residential'},
            {from: 'Jl. Roda II', to: 'Jl. Sendowo', roadType: 'residential'},
            {from: 'Jl. Roda II', to: 'Jl. Branjangan', roadType: 'secondary'},
            {from: 'Jl. Sendowo', to: 'Jl. Imam Bonjol Depan Rumah Pompa', roadType: 'secondary'},
            {from: 'Jl. Branjangan', to: 'Jl. Imam Bonjol Depan Rumah Pompa', roadType: 'secondary'},
            {from: 'Jl. Imam Bonjol Depan Rumah Pompa', to: 'Jl. Kolonel Sugiyono', roadType: 'primary'},
            {from: 'Jl. Kolonel Sugiyono', to: 'Jl. Imam Bonjol', roadType: 'primary'},
            {from: 'Jl. Imam Bonjol', to: 'Jl. Pemuda', roadType: 'primary'},
            {from: 'Jl. Pemuda', to: 'Alun-Alun Masjid Agung', roadType: 'primary'}
        ];

        // Road type weights
        const roadWeights = {
            highway: 0.5,
            primary: 0.7,
            secondary: 1.0,
            residential: 1.5
        };

        // Initialize map
        function initMap() {
            try {
                console.log('Initializing map...');
                map = L.map('map', {
                    zoomControl: true,
                    scrollWheelZoom: true,
                    doubleClickZoom: true,
                    boxZoom: true,
                    keyboard: true,
                    dragging: true,
                    touchZoom: true
                }).setView([-6.9932, 110.4203], 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19,
                    tileSize: 256,
                    zoomOffset: 0
                }).addTo(map);

                L.control.scale({
                    position: 'bottomright',
                    metric: true,
                    imperial: false
                }).addTo(map);

                console.log('Map initialized successfully');

                // Langsung tampilkan nodes setelah map ready
                displayNodesOnMap();
                populateLocationSelects();

            } catch (error) {
                console.error('Error initializing map:', error);
                document.getElementById('map').innerHTML = '<div style="padding: 20px; text-align: center; color: #ff6b6b;">Error loading map: ' + error.message + '</div>';
            }
        }

        function showLoading() {
            document.getElementById('loading').style.display = 'block';
        }

        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
        }

        function populateLocationSelects() {
            try {
                console.log('Populating location selects...');

                const startSelect = document.getElementById('startLocation');
                const endSelect = document.getElementById('endLocation');

                startSelect.innerHTML = '<option value="">Pilih lokasi awal</option>';
                endSelect.innerHTML = '<option value="">Pilih lokasi tujuan</option>';

                semarangNodes.forEach(node => {
                    startSelect.innerHTML += `<option value="${node.name}">${node.name}</option>`;
                    endSelect.innerHTML += `<option value="${node.name}">${node.name}</option>`;
                });

                console.log('Location selects populated successfully');
            } catch (error) {
                console.error('Error populating selects:', error);
            }
        }

        function displayNodesOnMap() {
            try {
                console.log('Displaying nodes on map...');

                // Clear existing markers
                markers.forEach(marker => map.removeLayer(marker));
                markers = [];

                // Custom marker icon
                const customIcon = L.divIcon({
                    className: 'custom-marker',
                    html: '<i class="fas fa-map-marker-alt" style="color: #00d4ff; font-size: 24px; filter: drop-shadow(0 0 6px rgba(0, 212, 255, 0.6));"></i>',
                    iconSize: [24, 24],
                    iconAnchor: [12, 24]
                });

                // Add markers for each node
                semarangNodes.forEach(node => {
                    const marker = L.marker([node.latitude, node.longitude], { icon: customIcon })
                        .addTo(map)
                        .bindPopup(`
                            <div style="text-align: center; padding: 8px;">
                                <h4 style="margin: 0 0 10px 0; color: #00d4ff; font-weight: 600;">${node.name}</h4>
                                <p style="margin: 0; font-size: 12px; color: #cbd5e1;">
                                    <i class="fas fa-map-pin" style="color: #00d4ff;"></i> ${node.latitude.toFixed(6)}, ${node.longitude.toFixed(6)}
                                </p>
                            </div>
                        `);
                    markers.push(marker);
                });

                console.log('Nodes displayed successfully:', semarangNodes.length, 'markers added');
            } catch (error) {
                console.error('Error displaying nodes:', error);
            }
        }

        // Calculate distance between two points using Haversine formula
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Earth's radius in km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        // A* Algorithm implementation
        function aStarPathfinding(startName, endName) {
            const startNode = semarangNodes.find(n => n.name === startName);
            const endNode = semarangNodes.find(n => n.name === endName);

            if (!startNode || !endNode) {
                return null;
            }

            const openSet = [startName];
            const cameFrom = {};
            const gScore = {};
            const fScore = {};

            // Initialize scores
            semarangNodes.forEach(node => {
                gScore[node.name] = Infinity;
                fScore[node.name] = Infinity;
            });

            gScore[startName] = 0;
            fScore[startName] = calculateDistance(startNode.latitude, startNode.longitude, endNode.latitude, endNode.longitude);

            while (openSet.length > 0) {
                // Find node with lowest fScore
                let current = openSet.reduce((lowest, node) =>
                    fScore[node] < fScore[lowest] ? node : lowest
                );

                if (current === endName) {
                    // Reconstruct path
                    const path = [current];
                    while (cameFrom[current]) {
                        current = cameFrom[current];
                        path.unshift(current);
                    }

                    // Calculate segments
                    const segments = [];
                    let totalDistance = 0;
                    let totalWeightedDistance = 0;

                    for (let i = 0; i < path.length - 1; i++) {
                        const fromNode = semarangNodes.find(n => n.name === path[i]);
                        const toNode = semarangNodes.find(n => n.name === path[i + 1]);
                        const connection = connections.find(c =>
                            (c.from === path[i] && c.to === path[i + 1]) ||
                            (c.from === path[i + 1] && c.to === path[i])
                        );

                        const distance = calculateDistance(fromNode.latitude, fromNode.longitude, toNode.latitude, toNode.longitude);
                        const roadType = connection ? connection.roadType : 'secondary';
                        const weight = roadWeights[roadType];
                        const weightedDistance = distance * weight;

                        segments.push({
                            from: path[i],
                            to: path[i + 1],
                            distance: distance,
                            weighted_distance: weightedDistance,
                            road_type: roadType,
                            road_type_name: getRoadTypeName(roadType),
                            weight: weight
                        });

                        totalDistance += distance;
                        totalWeightedDistance += weightedDistance;
                    }

                    const coordinates = path.map(nodeName => {
                        const node = semarangNodes.find(n => n.name === nodeName);
                        return {
                            name: nodeName,
                            lat: node.latitude,
                            lng: node.longitude
                        };
                    });

                    return {
                        success: true,
                        path: path,
                        coordinates: coordinates,
                        segments: segments,
                        total_distance: totalDistance,
                        total_weighted_distance: totalWeightedDistance
                    };
                }

                // Remove current from openSet
                openSet.splice(openSet.indexOf(current), 1);

                // Check neighbors
                const neighbors = getNeighbors(current);
                neighbors.forEach(neighbor => {
                    const currentNode = semarangNodes.find(n => n.name === current);
                    const neighborNode = semarangNodes.find(n => n.name === neighbor.name);
                    const connection = connections.find(c =>
                        (c.from === current && c.to === neighbor.name) ||
                        (c.from === neighbor.name && c.to === current)
                    );

                    const distance = calculateDistance(currentNode.latitude, currentNode.longitude, neighborNode.latitude, neighborNode.longitude);
                    const roadType = connection ? connection.roadType : 'secondary';
                    const weight = roadWeights[roadType];
                    const weightedDistance = distance * weight;

                    const tentativeGScore = gScore[current] + weightedDistance;

                    if (tentativeGScore < gScore[neighbor.name]) {
                        cameFrom[neighbor.name] = current;
                        gScore[neighbor.name] = tentativeGScore;
                        fScore[neighbor.name] = tentativeGScore + calculateDistance(neighborNode.latitude, neighborNode.longitude, endNode.latitude, endNode.longitude);

                        if (!openSet.includes(neighbor.name)) {
                            openSet.push(neighbor.name);
                        }
                    }
                });
            }

            return {
                success: false,
                message: 'Tidak ada jalur yang ditemukan'
            };
        }

        function getNeighbors(nodeName) {
            const neighbors = [];
            connections.forEach(connection => {
                if (connection.from === nodeName) {
                    neighbors.push({name: connection.to, roadType: connection.roadType});
                } else if (connection.to === nodeName) {
                    neighbors.push({name: connection.from, roadType: connection.roadType});
                }
            });
            return neighbors;
        }

        function getRoadTypeName(roadType) {
            const names = {
                highway: 'Jalan Tol',
                primary: 'Jalan Raya',
                secondary: 'Jalan Kecil',
                residential: 'Gang/Perumahan'
            };
            return names[roadType] || 'Unknown';
        }

        async function findPath() {
            const start = document.getElementById('startLocation').value;
            const end = document.getElementById('endLocation').value;
            const messageDiv = document.getElementById('message');

            console.log('Finding path from', start, 'to', end);

            if (!start || !end) {
                messageDiv.innerHTML = `<div class="message error fade-in">
                    <i class="fas fa-exclamation-triangle"></i> Silakan pilih lokasi awal dan tujuan
                </div>`;
                return;
            }

            if (start === end) {
                messageDiv.innerHTML = `<div class="message error fade-in">
                    <i class="fas fa-exclamation-triangle"></i> Lokasi awal dan tujuan harus berbeda
                </div>`;
                return;
            }

            showLoading();

            try {
                // Use local A* implementation
                const result = aStarPathfinding(start, end);

                if (result && result.success) {
                    messageDiv.innerHTML = `<div class="message success fade-in">
                        <i class="fas fa-check-circle"></i>
                        Rute optimal ditemukan! Jarak aktual: <strong>${result.total_distance.toFixed(4)} km</strong>,
                        Jarak berbobot: <strong>${result.total_weighted_distance.toFixed(4)} km</strong>
                    </div>`;
                    displayPath(result);
                } else {
                    messageDiv.innerHTML = `<div class="message error fade-in">
                        <i class="fas fa-times-circle"></i> ${result ? result.message : 'Tidak dapat menemukan rute'}
                    </div>`;
                }
            } catch (error) {
                console.error('Error finding path:', error);
                messageDiv.innerHTML = `<div class="message error fade-in">
                    <i class="fas fa-exclamation-circle"></i> Error: ${error.message}
                </div>`;
            } finally {
                hideLoading();
            }
        }

        function displayPath(data) {
            console.log('Displaying path:', data);
            const { coordinates, path, total_distance, total_weighted_distance, segments } = data;

            if (pathLine) {
                map.removeLayer(pathLine);
            }

            const latLngs = coordinates.map(coord => [coord.lat, coord.lng]);
            pathLine = L.polyline(latLngs, {
                color: '#00d4ff',
                weight: 5,
                opacity: 0.9,
                dashArray: '12, 8'
            }).addTo(map);

            if (coordinates.length > 0) {
                const startIcon = L.divIcon({
                    className: 'start-marker',
                    html: '<i class="fas fa-play-circle" style="color: #4ade80; font-size: 32px; filter: drop-shadow(0 0 8px rgba(74, 222, 128, 0.6));"></i>',
                    iconSize: [32, 32],
                    iconAnchor: [16, 32]
                });

                const endIcon = L.divIcon({
                    className: 'end-marker',
                    html: '<i class="fas fa-flag-checkered" style="color: #ff6b6b; font-size: 32px; filter: drop-shadow(0 0 8px rgba(255, 107, 107, 0.6));"></i>',
                    iconSize: [32, 32],
                    iconAnchor: [16, 32]
                });

                const startMarker = L.marker([coordinates[0].lat, coordinates[0].lng], { icon: startIcon })
                    .addTo(map)
                    .bindPopup(`
                        <div style="text-align: center; padding: 12px;">
                            <h4 style="margin: 0 0 8px 0; color: #4ade80; font-weight: 600;">
                                <i class="fas fa-play-circle"></i> MULAI
                            </h4>
                            <p style="margin: 0; font-weight: 500; color: #f1f5f9;">${coordinates[0].name}</p>
                        </div>
                    `)
                    .openPopup();

                const endMarker = L.marker([coordinates[coordinates.length-1].lat, coordinates[coordinates.length-1].lng], { icon: endIcon })
                    .addTo(map)
                    .bindPopup(`
                        <div style="text-align: center; padding: 12px;">
                            <h4 style="margin: 0 0 8px 0; color: #ff6b6b; font-weight: 600;">
                                <i class="fas fa-flag-checkered"></i> TUJUAN
                            </h4>
                            <p style="margin: 0; font-weight: 500; color: #f1f5f9;">${coordinates[coordinates.length-1].name}</p>
                        </div>
                    `);

                markers.push(startMarker, endMarker);
            }

            map.fitBounds(pathLine.getBounds(), { padding: [30, 30] });

            const resultsDiv = document.getElementById('results');
            resultsDiv.style.display = 'block';
            resultsDiv.classList.add('fade-in');

            document.getElementById('solutionList').innerHTML = path.map((location, index) =>
                `<div style="margin: 8px 0; padding: 12px; background: rgba(0, 212, 255, 0.1); border-radius: 10px; border-left: 3px solid #00d4ff;">
                    <strong style="color: #00d4ff;">${index + 1}.</strong> <span style="color: #f1f5f9;">${location}</span>
                </div>`
            ).join('');

            document.getElementById('solutionList').innerHTML += `
                <div style="margin-top: 16px; padding: 12px; background: rgba(91, 134, 229, 0.1); border-radius: 10px; border-left: 3px solid #5b86e5; text-align: center;">
                    <i class="fas fa-brain" style="color: #5b86e5; margin-right: 8px;"></i>
                    <span style="color: #cbd5e1; font-size: 14px;">Algoritma: <strong style="color: #5b86e5;">A* dengan Pembobotan Jalan</strong></span>
                </div>
            `;

            document.getElementById('distanceResult').innerHTML = `${total_distance.toFixed(4)} km`;

            document.getElementById('totalDistanceDetail').innerHTML = `${total_distance.toFixed(4)} km`;
            document.getElementById('totalWeightedDistance').innerHTML = `${total_weighted_distance.toFixed(4)} km`;
            document.getElementById('totalSegments').innerHTML = segments.length;
            document.getElementById('totalNodes').innerHTML = path.length;

            let segmentHtml = '';
            segments.forEach((segment, index) => {
                segmentHtml += `
                    <div class="segment-item ${segment.road_type}">
                        <div class="segment-from-to">
                            <strong style="color: #00d4ff;">${index + 1}.</strong> ${segment.from} → ${segment.to}
                            <div style="margin-top: 4px;">
                                <span class="road-type-badge ${segment.road_type}">${segment.road_type_name}</span>
                            </div>
                        </div>
                        <div class="segment-info">
                            <div class="segment-distance">
                                ${segment.distance.toFixed(4)} km
                            </div>
                            <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">
                                Berbobot: ${segment.weighted_distance.toFixed(4)} km (${segment.weight}x)
                            </div>
                        </div>
                    </div>
                `;
            });
            document.getElementById('segmentList').innerHTML = segmentHtml;
        }

        // Initialize when page loads
        window.onload = function() {
            console.log('Page loaded, initializing...');
            initMap();

            // Test if Leaflet is loaded
            if (typeof L === 'undefined') {
                console.error('Leaflet library not loaded!');
                document.getElementById('map').innerHTML = '<div style="padding: 20px; text-align: center; color: #ff6b6b;">Leaflet library failed to load</div>';
            }
        };

        // Add error event listener
        window.addEventListener('error', function(e) {
            console.error('Global error:', e.error);
        });
    </script>
</body>
</html>
