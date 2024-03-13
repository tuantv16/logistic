<?php

namespace App\Services\Traits;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Utility;

trait SearchTrait
{
    /**
     * @param $params
     * @param int $max
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getSearchInfo($params, int $max = 5): array
    {

        $keyCache = 'search_info_' . md5(json_encode($params));
        return cache()->remember($keyCache, 2, function () use ($params, $max) {
            $areas = collect([]);
            if (count($params['areas']) > 0) {
                $areas = $this->areaRepository
                    ->findWhere([
                        ['seo_url', 'IN', $params['areas']]
                    ])
                    ->sortBy('level')
                    ->pluck('name');
            }

            $areas = $areas->unique();

            // if (!empty($params['municipality'])) {
            //     $municipalities = $this->getMunicipalities($params['municipality']);
            //     $municipalities = $municipalities->pluck('name');
            //     $areas = $areas->merge($municipalities);
            // }

            if ($areas->count() >= 4) {
                $areas = $areas->slice(0, 2);
                $areas->push("その他のエリア");
            }

            //$areaBreadcrumbs = $areas;

            $rails = collect([]);
            if (count($params['rails'])) {
                $rails = $this->railRepository
                    ->findWhere([
                        [
                            'rail_id',
                            'IN',
                            $params['rails']
                        ]
                    ])
                    ->sortBy('level')
                    ->pluck('name');

                    //dd($rails);
                if (count($rails) > $max) {
                    $rails = $rails->slice(0, 2)->push('その他路線');
                }
            }  
 
 
            $params['occupations'] = $this->parseParamBiyoshi($params['occupations']); // tuantv - convert element biyoshi to stylist, assistant, colorist
            $params['key_element_biyoshi'] = $this->getKeyBiyoshi($params['occupations']);

            $occupations = collect([]);
             // tuantv add
            if (count($params['occupations']) > 0) {
                $occupations = $this->getOccupations($params['occupations']);
            }
           
            $employmentStatus = collect([]);
            $employmentSeoUrl = collect([]);
            if (request()->get('e')) {
                $employmentStatus = $this->employmentRepository
                    ->findWhere([
                        ['seo_url', 'IN', request()->get('e')]
                    ])
                    ->sortBy('level')
                    ->pluck('name');
            }

            //tuantv add
            if (!empty($params['featureEmployment'])) {
                $employmentStatus = $this->employmentRepository->findWhereIn('employment_id', $params['featureEmployment'])->pluck('name');
                $employmentSeoUrl = $this->employmentRepository->findWhereIn('employment_id', $params['featureEmployment'])->pluck('seo_url');
            }

            $salary = '';
            if ($params['salary']['salaryMin']) {
                $salary = Utility::numberFormat($params['salary']['salaryMin']) . '円';
                $salary .= '〜';
            }

            if ($params['salary']['salaryMax']) {
                if (!$params['salary']['salaryMin']){
                    $salary .= '〜'.Utility::numberFormat($params['salary']['salaryMax']) . '円';
                } else {
                    $salary .= Utility::numberFormat($params['salary']['salaryMax']) . '円';
                }
            }
           
            $features = collect([]);
            if ($params['featureSeoUrl']) {
                $features = $this->featureRepository->getFeatureForSearchInfo($params['featureSeoUrl'])->pluck('name');
                if (count($features) > $max) {
                    //$features = $features->slice(0, 2)->push('その他こだわり');
                    $features = $features->slice(0, 2);
                }
            }
            

            return [
                'occupations' => $occupations,
                'areas' => $areas,
                'areas_rails' => collect($areas)->merge($rails),
                'rails' => $rails,
                'employmentStatus' => $employmentStatus,
                'employmentSeoUrl' => $employmentSeoUrl,
                'salary' => $salary,
                'features' => $features,
                'keyElementBiyoshi' =>$params['key_element_biyoshi'],
                //'areaBreadcrumbs' => $areaBreadcrumbs
            ];
        });
    }

    public function getMunicipalities($ids) {
        $data = $this->municipalityRepository
        ->whereIn('municipality_id', $ids);
        return $data;
    }

      /**
      * author tuantv - 06/11/2023
      * @desciption title occupations
      * @return array
     */
    public function getOccupations($occupations, $max = 5) {
        $occupations = $this->occupationRepository
            ->findWhere([
                ['seo_url', 'IN', $occupations],
                ['disp','=', 1]
            ])
            ->sortBy('level')
            ->pluck('name');

        if (count($occupations) > $max) {
            //$occupations = $occupations->slice(0, 2)->push('その他職種');
            $occupations = $occupations->slice(0, 2);
        }

        return $occupations;
    }
    
     /**
      * author tuantv - 27/10/2023
      * @desciption parse param biyoshi
      * @return array
     */
    public function parseParamBiyoshi($occupations) {
        $keyBiyoshi = array_search('biyoshi', $occupations);
        if ($keyBiyoshi !== false) {
            unset($occupations[$keyBiyoshi]);
            array_unshift($occupations, 'stylist');
            array_unshift($occupations, 'assistant');
            array_unshift($occupations, 'colorist');
        }


        return $occupations;
    }

    public function getKeyBiyoshi($occupations) {
        $keyBiyoshi = array_search('stylist', $occupations);
        $keyAssistant= array_search('assistant', $occupations);
        $keyColorist = array_search('colorist', $occupations);
        $results = [];
        if ($keyBiyoshi !== false) {
            array_push($results, $keyBiyoshi);
        }
           
        if ($keyAssistant !== false) {
            array_push($results, $keyAssistant);
        }

        if ($keyColorist !== false) {
            array_push($results, $keyColorist);
        }

        return $results;
    }
    

    /**
     * @desciption create meta json for search page
     * @return array
     */
    private function makeMetaJson(): array
    {
        $url = url('/recruits/jobs');
        return [
            '@context' => 'http://schema.org',
            '@type' => 'WebSite',
            'url' => $url,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => "{$url}?p={search_term}",
                'query-input' => 'required name=search_term'
            ]
        ];
    }
}
