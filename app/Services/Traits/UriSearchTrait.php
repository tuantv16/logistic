<?php

namespace App\Services\Traits;

use App\Repositories\Interfaces\OccupationRepository;

trait UriSearchTrait
{


    /**
     * author: tuantv
     * @param $params
     * @return string
     */
    private function buildOccupations($params)
    {
        $uri = '';

        $occupationsSeoUrl = $this->occupationRepository->getOccupationRootDisplay()->pluck('seo_url')->toArray();

        $occupations = $params['occupations'];
        
        //remove biyoshi -> sort
        $flagBiyoshi = false;
        if (in_array("biyoshi", $occupations)) {
            $key_stylist = array_search("biyoshi", $occupations);
            unset($occupations[$key_stylist]);
            $flagBiyoshi = true;
        }

        // Create an associative array to store the positions of elements in the array
        $tmpPositions = array_flip($occupationsSeoUrl);

        $intersection = array_intersect($occupations, $occupationsSeoUrl);

        if (!empty($occupations)) {
            foreach ($occupations as $key => $item) {
                if (!in_array($item, $occupationsSeoUrl)) {
                    unset($occupations[$key]);
                }
            }
        }

        if (!empty($intersection)) {
            // Reorder the occupations array based on the location of $occupationsSeoUrl retrieved in the database
            usort($occupations, function($a, $b) use ($tmpPositions) {
                return $tmpPositions[$a] <=> $tmpPositions[$b];
            });
        }  

        // reset biyoshi to params
        if ($flagBiyoshi) {
            array_unshift($occupations, "biyoshi");
        }

        //special case
        if (array_diff(["stylist", "assistant", "colorist"], $occupations) === [] && count($occupations) == 4) {
            $keyStylist = array_search("stylist", $occupations);
            $keyAssistant = array_search("assistant", $occupations);
            $keyColorist = array_search("colorist", $occupations);
            unset($occupations[$keyStylist]);
            unset($occupations[$keyAssistant]);
            unset($occupations[$keyColorist]);
        }

        if (count($occupations) > 0) {
            $uri .= '/' . implode('/', $occupations);
        }
       
        return $uri;
        
    }

    /**
     * author: tuantv
     * @param $paramEmployments
     * @return string
     */
    public function buildEmployments($paramEmployments) {

        $employmentSeoUrls = $this->employmentRepository->getEmploymentDisplay()->pluck('seo_url')->toArray();
     
        $tmpPositions = array_flip($employmentSeoUrls);
        
         // sort array occupations into $occupationsSeoUrl getted from database
         usort($paramEmployments, function($a, $b) use ($tmpPositions) {
            return $tmpPositions[$a] <=> $tmpPositions[$b];
        });

        return $paramEmployments;
    }

   
    /**
     * author: tuantv
     * description: remove Biyoshi in array
     * @param $array
     * @return string
     */
    public function removeElementBiyoshi($array) {
        if (array_search('biyoshi', $array) !== false) {
            $keyBiyoshi = array_search('biyoshi', $array);
            unset($array[$keyBiyoshi]);
        }

        return $array;
    }

    /**
     * @param $params
     * @return string
     */
    private function buildArea($params): string
    {
        $uri = '';
        if (count($params['areasLevel1']) > 0) {
            $uri .= '/' . implode('/', $params['areasLevel1']);
            if (count($params['areasLevel2']) === 1) {
                $uri .= '/' . implode('/', $params['areasLevel2']);
            }
        }

        return $uri;
    }

    /**
     * @param $params
     * @return string
     */
    private function buildRails($params): string
    {
        $uri = '';
        $railUrls = collect($params['seoUrls']);
        $railUrls = $railUrls->sortByDesc(function ($item) {
            return strlen($item);
        });

        if (count($railUrls) > 0) {
            $uri .= '/' . implode('/', [$railUrls->first()]);
        }

        return $uri;
    }

    /**
     * @param $params
     * @return string
     */
    private function buildOffer($params): string
    {
        $uri = '';
        if (isset($params['salary']['salaryMin']) && $params['salary']['salaryMin']) {
            $uri .= '/gekkyu-min-' . $params['salary']['salaryMin'];
        }
        if (isset($params['salary']['salaryMax']) && $params['salary']['salaryMax']) {
            $uri .= '/gekkyu-max-' . $params['salary']['salaryMax'];
        }

        return $uri;
    }

    
}
