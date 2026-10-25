<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryTreeService
{
    public function tree(): array
    {
        $key = 'inventory:categories:tree:v1';
        if (Cache::has($key)) return Cache::get($key);
        $tree = (function () {
            $rows = Category::query()->select(['id', 'name', 'slug', 'parent_id', 'is_active'])
                ->orderBy('name')->get();
            $all = $rows->keyBy('id');
            $active = $rows->where('is_active', true)->keyBy('id');
            $nodes = [];
            foreach ($active as $id => $row) {
                $nodes[$id] = ['id' => $row->id, 'name' => $row->name, 'slug' => $row->slug, 'is_active' => true, 'children' => []];
            }
            $roots = [];
            foreach ($active as $id => $row) {
                $parentId = $row->parent_id;
                $visited = [$id => true];
                while ($parentId !== null && !isset($active[$parentId])) {
                    // Walk through inactive ancestors until nearest active ancestor, or root.
                    if (!isset($all[$parentId]) || isset($visited[$parentId])) { $parentId = null; break; }
                    $visited[$parentId] = true;
                    $parentId = $all[$parentId]->parent_id;
                }
                if ($parentId !== null && isset($nodes[$parentId])) {
                    $nodes[$parentId]['children'][] = &$nodes[$id];
                } else {
                    $roots[] = &$nodes[$id];
                }
            }
            return $roots;
        })();
        if ($tree !== []) Cache::put($key, $tree, now()->addMinutes(30));
        return $tree;
    }
}
