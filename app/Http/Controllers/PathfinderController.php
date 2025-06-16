<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\Edge;
use App\Services\AStarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PathfinderController extends Controller
{
    protected $astarService;

    public function __construct(AStarService $astarService)
    {
        $this->astarService = $astarService;
    }

    public function index()
    {
        return view('pathfinder');
    }

    public function loadData(Request $request)
    {
        try {
            Log::info('Loading data started');

            // Clear existing data
            DB::transaction(function() {
                Edge::truncate();
                Node::truncate();
                Log::info('Existing data cleared');
            });

            // Load sample data with road types
            $this->loadSemarangData();
            Log::info('Semarang data loaded');

            $nodes = Node::all()->map(function($node) {
                return [
                    'id' => $node->id,
                    'name' => $node->name,
                    'latitude' => $node->latitude,
                    'longitude' => $node->longitude,
                    'neighbors' => $node->neighbors->pluck('name')->toArray()
                ];
            });

            Log::info('Nodes mapped, count: ' . $nodes->count());

            return response()->json([
                'success' => true,
                'nodes' => $nodes,
                'message' => "Data berhasil dimuat. Ditemukan {$nodes->count()} node dengan sistem pembobotan jalan."
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading data: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function findPath(Request $request)
    {
        $request->validate([
            'start' => 'required|string',
            'end' => 'required|string'
        ]);

        $startNode = Node::where('name', $request->start)->first();
        $endNode = Node::where('name', $request->end)->first();

        if (!$startNode || !$endNode) {
            return response()->json([
                'success' => false,
                'message' => 'Node tidak ditemukan'
            ]);
        }

        if ($startNode->id === $endNode->id) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi awal dan tujuan harus berbeda'
            ]);
        }

        $result = $this->astarService->findPath($startNode->id, $endNode->id);
        return response()->json($result);
    }

    public function getNodes()
    {
        $nodes = Node::orderBy('name')->pluck('name');
        return response()->json($nodes);
    }

    public function getRoadTypes()
    {
        return response()->json(Edge::ROAD_TYPES);
    }

    private function loadSemarangData()
    {
        // Sample data with road types and weights
        $nodesData = [
            ['name' => 'Kota Lama Semarang', 'lat' => -6.968339658674677, 'lng' => 110.42844065022399],
            ['name' => 'Jl. Letjen Suprapto 35', 'lat' => -6.968366588116826, 'lng' => 110.42805767097872],
            ['name' => 'Jl. Kepodang No.15-16', 'lat' => -6.969279838846258, 'lng' => 110.42808001618744],
            ['name' => 'Jl. Letjen Suprapto 30', 'lat' => -6.968419, 'lng' => 110.427533],
            ['name' => 'Jl. Kepodang 105-33', 'lat' => -6.969305, 'lng' => 110.427736],
            ['name' => 'Jl. Kepodang', 'lat' => -6.969314, 'lng' => 110.427586],
            ['name' => 'Jl. Sendowo 17', 'lat' => -6.969685, 'lng' => 110.427756],
            ['name' => 'Jl. Sendowo 1', 'lat' => -6.969695, 'lng' => 110.427605],
            ['name' => 'Jl. Sendowo 2', 'lat' => -6.969711, 'lng' => 110.427680],
            ['name' => 'Jl. Suari 12', 'lat' => -6.969922, 'lng' => 110.427722],
            ['name' => 'Jl. Pekojan 1', 'lat' => -6.970796, 'lng' => 110.427884],
            ['name' => 'Jl. Pekojan 2', 'lat' => -6.971091, 'lng' => 110.427123],
            ['name' => 'Jl. Ki Nartosabdo 6', 'lat' => -6.971321, 'lng' => 110.424421],
            ['name' => 'Jl. Kenari 1', 'lat' => -6.970073, 'lng' => 110.426940],
            ['name' => 'Jl. Kenari 2', 'lat' => -6.970266, 'lng' => 110.426208],
            ['name' => 'Jl. MPU Tantular 1', 'lat' => -6.970411, 'lng' => 110.425727],
            ['name' => 'Jl. Mpu Tantular 2', 'lat' => -6.971386, 'lng' => 110.425715],
            ['name' => 'Jl. Kyai H. Agus Salim No.7', 'lat' => -6.971139, 'lng' => 110.426542],
            ['name' => 'Jl. Kyai H. Agus Salim No.121', 'lat' => -6.971226, 'lng' => 110.426547],
            ['name' => 'Jl. Sendowo 7-9', 'lat' => -6.969946, 'lng' => 110.425726],
            ['name' => 'Jl. Sendowo Purwodinatan', 'lat' => -6.969857, 'lng' => 110.426111],
            ['name' => 'Jl. Sendowo Barat', 'lat' => -6.969793, 'lng' => 110.426594],
            ['name' => 'Jl. Roda II', 'lat' => -6.969326, 'lng' => 110.426602],
            ['name' => 'Jl. Sendowo', 'lat' => -6.969543, 'lng' => 110.425712],
            ['name' => 'Jl. Branjangan', 'lat' => -6.968525, 'lng' => 110.426571],
            ['name' => 'Jl. Imam Bonjol Depan Rumah Pompa', 'lat' => -6.968762, 'lng' => 110.425227],
            ['name' => 'Jl. Kolonel Sugiyono', 'lat' => -6.969039, 'lng' => 110.424799],
            ['name' => 'Jl. Imam Bonjol', 'lat' => -6.969987, 'lng' => 110.423877],
            ['name' => 'Jl. Pemuda', 'lat' => -6.971111, 'lng' => 110.423056],
            ['name' => 'Alun-Alun Masjid Agung', 'lat' => -6.971354, 'lng' => 110.423858],
        ];

        // Create nodes
        $nodeMap = [];
        foreach ($nodesData as $nodeData) {
            $node = Node::create([
                'name' => $nodeData['name'],
                'latitude' => $nodeData['lat'],
                'longitude' => $nodeData['lng']
            ]);
            $nodeMap[$nodeData['name']] = $node;
        }

        // Define connections with road types
        $connections = [
            ['from' => 'Kota Lama Semarang', 'to' => 'Jl. Letjen Suprapto 35', 'road_type' => 'primary'],
            ['from' => 'Jl. Letjen Suprapto 35', 'to' => 'Jl. Kepodang No.15-16', 'road_type' => 'secondary'],
            ['from' => 'Jl. Letjen Suprapto 35', 'to' => 'Jl. Letjen Suprapto 30', 'road_type' => 'primary'],
            ['from' => 'Jl. Kepodang No.15-16', 'to' => 'Jl. Kepodang 105-33', 'road_type' => 'secondary'],
            ['from' => 'Jl. Letjen Suprapto 30', 'to' => 'Jl. Kepodang', 'road_type' => 'secondary'],
            ['from' => 'Jl. Letjen Suprapto 30', 'to' => 'Jl. Branjangan', 'road_type' => 'secondary'],
            ['from' => 'Jl. Kepodang 105-33', 'to' => 'Jl. Kepodang', 'road_type' => 'secondary'],
            ['from' => 'Jl. Kepodang 105-33', 'to' => 'Jl. Sendowo 17', 'road_type' => 'residential'],
            ['from' => 'Jl. Kepodang', 'to' => 'Jl. Roda II', 'road_type' => 'secondary'],
            ['from' => 'Jl. Sendowo 17', 'to' => 'Jl. Sendowo 2', 'road_type' => 'residential'],
            ['from' => 'Jl. Sendowo 1', 'to' => 'Jl. Kepodang', 'road_type' => 'residential'],
            ['from' => 'Jl. Sendowo 1', 'to' => 'Jl. Sendowo 2', 'road_type' => 'residential'],
            ['from' => 'Jl. Sendowo 2', 'to' => 'Jl. Suari 12', 'road_type' => 'residential'],
            ['from' => 'Jl. Suari 12', 'to' => 'Jl. Pekojan 1', 'road_type' => 'secondary'],
            ['from' => 'Jl. Suari 12', 'to' => 'Jl. Kenari 1', 'road_type' => 'secondary'],
            ['from' => 'Jl. Pekojan 1', 'to' => 'Jl. Pekojan 2', 'road_type' => 'secondary'],
            ['from' => 'Jl. Pekojan 2', 'to' => 'Jl. Kyai H. Agus Salim No.7', 'road_type' => 'secondary'],
            ['from' => 'Jl. Ki Nartosabdo 6', 'to' => 'Jl. Imam Bonjol', 'road_type' => 'primary'],
            ['from' => 'Jl. Ki Nartosabdo 6', 'to' => 'Alun-Alun Masjid Agung', 'road_type' => 'primary'],
            ['from' => 'Jl. Ki Nartosabdo 6', 'to' => 'Jl. Kyai H. Agus Salim No.121', 'road_type' => 'secondary'],
            ['from' => 'Jl. Kenari 1', 'to' => 'Jl. Kenari 2', 'road_type' => 'secondary'],
            ['from' => 'Jl. Kenari 2', 'to' => 'Jl. MPU Tantular 1', 'road_type' => 'secondary'],
            ['from' => 'Jl. Kenari 2', 'to' => 'Jl. Sendowo Purwodinatan', 'road_type' => 'residential'],
            ['from' => 'Jl. MPU Tantular 1', 'to' => 'Jl. Mpu Tantular 2', 'road_type' => 'secondary'],
            ['from' => 'Jl. MPU Tantular 1', 'to' => 'Jl. Sendowo 7-9', 'road_type' => 'residential'],
            ['from' => 'Jl. Mpu Tantular 2', 'to' => 'Jl. Kyai H. Agus Salim No.7', 'road_type' => 'secondary'],
            ['from' => 'Jl. Kyai H. Agus Salim No.7', 'to' => 'Jl. Kyai H. Agus Salim No.121', 'road_type' => 'secondary'],
            ['from' => 'Jl. Sendowo 7-9', 'to' => 'Jl. Sendowo Purwodinatan', 'road_type' => 'residential'],
            ['from' => 'Jl. Sendowo 7-9', 'to' => 'Jl. Sendowo', 'road_type' => 'residential'],
            ['from' => 'Jl. Sendowo Purwodinatan', 'to' => 'Jl. Sendowo Barat', 'road_type' => 'residential'],
            ['from' => 'Jl. Sendowo Barat', 'to' => 'Jl. Sendowo 1', 'road_type' => 'residential'],
            ['from' => 'Jl. Roda II', 'to' => 'Jl. Sendowo', 'road_type' => 'residential'],
            ['from' => 'Jl. Roda II', 'to' => 'Jl. Branjangan', 'road_type' => 'secondary'],
            ['from' => 'Jl. Sendowo', 'to' => 'Jl. Imam Bonjol Depan Rumah Pompa', 'road_type' => 'secondary'],
            ['from' => 'Jl. Branjangan', 'to' => 'Jl. Imam Bonjol Depan Rumah Pompa', 'road_type' => 'secondary'],
            ['from' => 'Jl. Imam Bonjol Depan Rumah Pompa', 'to' => 'Jl. Kolonel Sugiyono', 'road_type' => 'primary'],
            ['from' => 'Jl. Kolonel Sugiyono', 'to' => 'Jl. Imam Bonjol', 'road_type' => 'primary'],
            ['from' => 'Jl. Imam Bonjol', 'to' => 'Jl. Pemuda', 'road_type' => 'primary'],
            ['from' => 'Jl. Pemuda', 'to' => 'Alun-Alun Masjid Agung', 'road_type' => 'primary'],
        ];

        // Create bidirectional edges with weights
        foreach ($connections as $connection) {
            $fromNode = $nodeMap[$connection['from']];
            $toNode = $nodeMap[$connection['to']];
            $roadType = $connection['road_type'];
            $weight = Edge::ROAD_TYPES[$roadType]['weight'];
            $distance = $fromNode->calculateDistance($toNode);

            // Create edge from A to B
            Edge::create([
                'from_node_id' => $fromNode->id,
                'to_node_id' => $toNode->id,
                'distance' => $distance,
                'road_type' => $roadType,
                'weight' => $weight
            ]);

            // Create edge from B to A (bidirectional)
            Edge::create([
                'from_node_id' => $toNode->id,
                'to_node_id' => $fromNode->id,
                'distance' => $distance,
                'road_type' => $roadType,
                'weight' => $weight
            ]);
        }
    }
}
