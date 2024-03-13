<?php

namespace App\Services\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function Aws\map;

trait SearchParamTrait
{
    /**
     * @param $seoUrl
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getParams($seoUrl, Request $request): array
    {

        $keyCache = md5(request()->fullUrl());

        // return Cache::remember($keyCache, 30, function () use ($seoUrl, $request) {
            
            $favoriteIds = null;
            if (request('fa') == 1) {
                $data = Cookie::get('ji');
                $favoriteIds = $data ? json_decode($data, true) : [];
                $favoriteIds = count($favoriteIds) ? $favoriteIds : [-1];
               
            }

            $area = $this->getArea($seoUrl);
            $municipality = $this->getMunicipality(); // tuantv add
            $rails = $this->getRails($seoUrl);
            $feature = $this->getFeature($seoUrl);
           
            $featureEmployment = $this->getFeatureEmployment($seoUrl); // tuantv add
            $employment = $featureEmployment['employments']; // tuantv add

            $configEmployments = config('sites.base')['employment_setting'];
            $occupations = [];

            if(!in_array($seoUrl, $configEmployments) && !in_array($seoUrl, $feature['seoUrls'])) {
                $occupations = $this->getOccupation($seoUrl);
            }

            return [
                'favoriteIds' => $favoriteIds,
                'occupations' => $occupations,
                'rootOccupation' => $occupations[0] ?? '',
                'areas' => $area['areas'],
                'feature' => $feature['ids'],
                'employment' => $employment,
                'featureEmployment' => $featureEmployment['ids'], // tuantv add
                'featureSeoUrl' => $feature['seoUrls'],
                'areasLevel1' => $area['areasLevel1'],
                'areasLevel2' => $area['areasLevel2'],
                'areaIdsLevel1' => $area['areaIdsLevel1'],
                'areaIdsLevel2' => $area['areaIdsLevel2'],
                'railsLevel1' => $rails['railsLevel1'],
                'railsLevel2' => $rails['railsLevel2'],
                'railsLevel3' => $rails['railsLevel3'],
                'rootArea' => $area['rootArea'],
                'rootAreaId' => $area['rootAreaId'],
                'rails' => $this->getRails($seoUrl)['rails'],
                'seoUrls' => $this->getRails($seoUrl)['railUrls'],
                'salary' => $this->getSalary($seoUrl),
                'municipality' => $municipality,
                're' =>  $request->input('re') ?? ''  // param store history users
            ];
        // });
    }

    private function checkIsNotOccupation($alias): bool
    {
        $check = false;
        foreach (['area', 'station', 'gekkyu-min', 'gekkyu-max', 'city'] as $v) {
            if (preg_match('/'.$v.'/', $alias)) {
                $check = true;
                break;
            }
        }

        return $check;
    }

    /**
     * @param $seoUrl
     * @return array
     */
    private function getOccupation($seoUrl): array
    {
        $occupations = explode('/', $seoUrl);

        $listOccupations = [];
        foreach ($occupations as $occupation) {
            if ($this->checkIsNotOccupation($occupation)) {
                break;
            }

            if ($occupation != '') {
                $listOccupations[] = $occupation;
            }
        }
        
        if (count($listOccupations) > 1) {
            return $listOccupations;
        }
       
        //get occupation from config
        $config = config('sites.base.site_setting.customize.seo_occupation_list');
        $ids = collect($config)->filter(function ($item, $key) use ($listOccupations) {
            return in_array($key, $listOccupations);
        })->pluck('occupation_ids')->flatten()->toArray();

        $occupationsDb = [];
        if ($ids) {
            $occupationsDb = $this->occupationRepository
                ->findWhereIn('occupation_id', $ids)
                ->sortBy('level')
                ->pluck('seo_url')
                ->toArray();
        }

        $list = collect($occupationsDb)->filter(function ($item) use ($listOccupations) {
            return !empty($item);
        })->toArray();

        return collect(array_merge($list, $listOccupations))->unique()->toArray();
    }

    /**
     * @desciption get area from seo url
     * @param $seoUrl
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getArea($seoUrl): array
    {
        $areas = [];
        $rootArea = '';
        $rootAreaId = 0;

      

        if (preg_match('/area\/([a-zA-Z\-]+)/i', $seoUrl, $matches)) {
            $areas = [$matches[0]];
            $rootArea = $matches[0];
            $rootAreaId = $this->areaRepository->findWhere(['seo_url' => $rootArea])->pluck('area_id')->first();
        }
      
        
        //get city - tuantv comment 26/01/2024
        // if (preg_match('/city\/(\d+)/i', $seoUrl, $matches)) {
        //     $city = [$matches[0]];
        //     request()->merge(['a2' => $city]);
        // }

        // tuantv add 26/01/2024
        if (preg_match_all('/city\/(\d+)/i', $seoUrl, $matches)) {
            $cities = array_map(function ($match) {
                return "city/$match";
            }, $matches[1]);
            request()->merge(['a2' => $cities]);
        }

        $area2 = collect(\request()->get('a2', []))->unique()->toArray();
   
        return [
            'areas' => collect(array_merge($areas, $area2))->unique()->values()->toArray(),
            'areasLevel1' => $areas,
            'areaIdsLevel1' => count($areas) > 0 ? $this->areaRepository->findWhereIn('seo_url', $areas)->pluck(
                'area_id'
            )->toArray() : [],
            'areasLevel2' => $area2,
            'areaIdsLevel2' => count($area2) > 0 ? $this->areaRepository->findWhereIn('seo_url', $area2)->pluck(
                'area_id'
            )->toArray() : [],
            'rootArea' => $rootArea,
            'rootAreaId' => $rootAreaId
        ];
    }

    /**
     * @desciption get area from seo url
     * @param $seoUrl
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getMunicipality(): array
    {
        return \request()->get('m2', []);
    }

    /**
     * @desciption get rails from seo url
     * @param $seoUrl
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getRails($seoUrl): array
    {
        //parse rails from seo url
        $rails = [];
        if (preg_match_all('/station\/(\d+)/i', $seoUrl, $matches)) {
            $rails = $matches[1];
        }
        $rails1 = \request()->get('r', []);
        foreach ($rails1 as $item) {
            if (preg_match('/station\/(\d+)/i', $item, $matches)) {
                $rails = array_merge($rails, [$matches[1]]);
            }
        }

        $rails2 = \request()->get('r2', []);
        foreach ($rails2 as $item) {
            if (preg_match_all('/station\/(\d+)/i', $item, $matches)) {
                $rails = array_merge($rails, $matches[1]);
            }
        }

        $rails3 = \request()->get('r3', []);
        foreach ($rails3 as $item) {
            if (preg_match_all('/station\/(\d+)/i', $item, $matches)) {
                $rails = array_merge($rails, $matches[1]);
            }
        }

        $rails = array_unique($rails);

        //rebuild seo url for checking url correctly
        $railItems = $this->railRepository->findWhereIn('rail_id', $rails);

        $railLevel1 = $railItems->filter(function ($item) {
            return $item->level == 1;
        })->pluck('rail_id')->toArray();

        $railLevel2 = $railItems->filter(function ($item) {
            return $item->level == 2;
        })->pluck('rail_id')->toArray();

        $railLevel3 = $railItems->filter(function ($item) {
            return $item->level == 3;
        })->pluck('rail_id')->toArray();

        $railSeoUrlList = [];
        $railSeoUrlList = $this->getRailSeoUrl($railItems, 3, $railSeoUrlList);
        $railSeoUrlList = $this->getRailSeoUrl($railItems, 2, $railSeoUrlList);
        $railSeoUrlList = $this->getRailSeoUrl($railItems, 1, $railSeoUrlList);
        //end rebuild seo url
    
        return [
            'railUrls' => $railSeoUrlList,
            'railsLevel1' => $railLevel1,
            'railsLevel2' => $railLevel2,
            'railsLevel3' => $railLevel3,
            'rails' => $rails,
            
        ];

    }

     /**
     * @param $railItems
     * @param $level
     * @param array $railList
     * @return array
     */
    private function getRailSeoUrl($railItems, $level, array $railList): array
    {
        $railLvlList = $railItems->filter(function ($item) use ($level) {
            return $item->level == $level;
        });
        $railLvlList = $railLvlList->map(function ($item) {
            $seoUrl = 'station/' . $item->rail_id;
            if ($seoUrl != '') {
                $item->seo_url = $seoUrl;
            }
            return $item;
        })->pluck('seo_url')->toArray();
        return array_merge($railList, $railLvlList);
    }


    /**
     * @desciption get salary from seo url
     * @param $seoUrl
     * @return array[]|int[]
     */
    private function getSalary($seoUrl): array
    {
        $salaryMin = 0;
        $salaryMax = 0;
        if (preg_match('/gekkyu-min-(\d+)/i', $seoUrl, $matches)) {
            $salaryMin = $matches[1];
        }
        if (preg_match('/gekkyu-max-(\d+)/i', $seoUrl, $matches)) {
            $salaryMax = $matches[1];
        }

        return [
            'salaryMin' => $salaryMin,
            'salaryMax' => $salaryMax
        ];
    }

}
