<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'latitude',
        'longitude'
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float'
    ];

    public function edges()
    {
        return $this->hasMany(Edge::class, 'from_node_id');
    }

    public function incomingEdges()
    {
        return $this->hasMany(Edge::class, 'to_node_id');
    }

    public function neighbors()
    {
        return $this->belongsToMany(Node::class, 'edges', 'from_node_id', 'to_node_id')
                    ->withPivot(['distance', 'road_type', 'weight']);
    }

    public function calculateDistance(Node $other)
    {
        $lat1 = deg2rad($this->latitude);
        $lon1 = deg2rad($this->longitude);
        $lat2 = deg2rad($other->latitude);
        $lon2 = deg2rad($other->longitude);

        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat/2) * sin($dlat/2) + cos($lat1) * cos($lat2) * sin($dlon/2) * sin($dlon/2);
        $c = 2 * asin(sqrt($a));
        $r = 6371; // Earth radius in km

        return $c * $r;
    }
}
