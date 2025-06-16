<?php

namespace App\Services;

use App\Models\Node;
use App\Models\Edge;
use Illuminate\Support\Collection;

class AStarService
{
    public function findPath($startNodeId, $endNodeId)
    {
        $startNode = Node::find($startNodeId);
        $endNode = Node::find($endNodeId);

        if (!$startNode || !$endNode) {
            return [
                'success' => false,
                'message' => 'Node tidak ditemukan'
            ];
        }

        $openSet = collect([$startNodeId]);
        $cameFrom = [];
        $gScore = [$startNodeId => 0];
        $fScore = [$startNodeId => $startNode->calculateDistance($endNode)];

        while ($openSet->isNotEmpty()) {
            // Find node with lowest fScore
            $current = $openSet->sortBy(function($nodeId) use ($fScore) {
                return $fScore[$nodeId] ?? INF;
            })->first();

            if ($current == $endNodeId) {
                return $this->reconstructPath($cameFrom, $current, $startNode, $endNode);
            }

            $openSet = $openSet->reject(function($nodeId) use ($current) {
                return $nodeId == $current;
            });

            $currentNode = Node::find($current);
            $neighbors = Edge::where('from_node_id', $current)->get();

            foreach ($neighbors as $edge) {
                $neighborId = $edge->to_node_id;
                $tentativeGScore = ($gScore[$current] ?? INF) + $edge->getWeightedDistance();

                if ($tentativeGScore < ($gScore[$neighborId] ?? INF)) {
                    $cameFrom[$neighborId] = $current;
                    $gScore[$neighborId] = $tentativeGScore;

                    $neighborNode = Node::find($neighborId);
                    $heuristic = $neighborNode->calculateDistance($endNode);
                    $fScore[$neighborId] = $tentativeGScore + $heuristic;

                    if (!$openSet->contains($neighborId)) {
                        $openSet->push($neighborId);
                    }
                }
            }
        }

        return [
            'success' => false,
            'message' => 'Tidak ada jalur yang ditemukan'
        ];
    }

    private function reconstructPath($cameFrom, $current, $startNode, $endNode)
    {
        $path = [$current];
        $pathNodes = [Node::find($current)];

        while (isset($cameFrom[$current])) {
            $current = $cameFrom[$current];
            array_unshift($path, $current);
            array_unshift($pathNodes, Node::find($current));
        }

        // Calculate segments with road type information
        $segments = [];
        $totalDistance = 0;
        $totalWeightedDistance = 0;

        for ($i = 0; $i < count($path) - 1; $i++) {
            $edge = Edge::where('from_node_id', $path[$i])
                       ->where('to_node_id', $path[$i + 1])
                       ->first();

            if ($edge) {
                $segments[] = [
                    'from' => $pathNodes[$i]->name,
                    'to' => $pathNodes[$i + 1]->name,
                    'distance' => $edge->distance,
                    'weighted_distance' => $edge->getWeightedDistance(),
                    'road_type' => $edge->road_type,
                    'road_type_name' => $edge->getRoadTypeName(),
                    'weight' => $edge->weight
                ];
                $totalDistance += $edge->distance;
                $totalWeightedDistance += $edge->getWeightedDistance();
            }
        }

        $coordinates = collect($pathNodes)->map(function($node) {
            return [
                'name' => $node->name,
                'lat' => $node->latitude,
                'lng' => $node->longitude
            ];
        });

        return [
            'success' => true,
            'path' => collect($pathNodes)->pluck('name')->toArray(),
            'coordinates' => $coordinates,
            'segments' => $segments,
            'total_distance' => $totalDistance,
            'total_weighted_distance' => $totalWeightedDistance,
            'algorithm' => 'A* with Road Weight Optimization'
        ];
    }
}
