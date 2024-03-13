<?php

namespace App\Services\Traits;


trait DisplayOrderTrait
{
    /**
     * @describe resort disp order
     *
     * @param array $rows
     * @param mixed $items
     * @param string $keySort
     * @return array
     */
    public function sortDispOrder(array $rows, mixed $items, string $keySort = 'disp_order'): array
    {
        $rows = collect($rows)->map(function ($item) use ($keySort) {
            $item[$keySort] = (int)$item[$keySort];
            return $item;
        })->toArray();
        $ids = collect($rows)->pluck('id')->toArray();
        $items = collect($items)->filter(function ($item) use ($ids) {
            return !in_array($item->getKey(), $ids, true);
        });
        $items = $items->map(function ($item) {
            $item->time = 0;
            $item->id = $item->getKey();
            return $item;
        });
        $rows = array_merge($items->toArray(), $rows);
        $rows = collect($rows)->sortByDesc('time');
        $rows = $rows->sortBy($keySort)->toArray();
        $index = 0;
        foreach ($rows as $key => $row) {
            $rows[$key][$keySort] = $index + 1;
            $index++;
        }
        return $rows;
    }

}
