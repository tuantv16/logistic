<?php

namespace App\Services\Traits;

trait JobUrlTrait
{
    /**
     * @param $jobIds
     * @return array
     */
    public function getUrlDetailJob($jobIds): array
    {
        $results = [];
        $arrOrcs = $this->jobRepository->getUrlOccupation($jobIds);
        $arrAreas = $this->jobRepository->getUrlArea($jobIds);


        $statusExistsOccArea = false;
        foreach($jobIds as $jobId) {
            $arrTmps = [];
            if (!empty($arrOrcs[$jobId])) {
                $arrTmps[] = $arrOrcs[$jobId];
                $statusExistsOccArea = true;
            }

            if (!empty($arrAreas[$jobId])) {
                $arrTmps[] = $arrAreas[$jobId];
                $statusExistsOccArea = true;
            }

            $arrTmps[] = $jobId;
            $results[$jobId] = implode('/', $arrTmps);
        }
        $results['statusExistsOccArea'] = $statusExistsOccArea;

        return $results;
    }
}
