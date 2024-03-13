<?php

namespace App\Services\Traits;

trait SearchMetaTrait
{
    /**
     * @desciption  description for search page
     * @param $searchInfo
     * @return string
     */
    private function makeDescription($searchInfo): string
    {
        $textBiyoshi = '美容師求人';
        $description = '';
        if ($searchInfo['areas']->count() > 0) {
            $description .= $searchInfo['areas']->join(',') . ' の';
            $description .= !empty($searchInfo['occupations']) ? $searchInfo['occupations']->join(',') : ' の';
        } elseif ($searchInfo['rails']->count() > 0) {
            $description .= $searchInfo['rails']->join(',') . ' の';
            $description .= !empty($searchInfo['occupations']) ? $searchInfo['occupations']->join(',') : ' の';
        } elseif ($searchInfo['occupations']->count() > 0) {
            $description .= $searchInfo['occupations']->join(',') . ' の';
        }
        if (!empty($description)) {
            $description .= "一覧ページです。美容師/理容師/アイリスト/ネイリスト/エステティシャンなど美容業界の求人なら利用満足度No.1の「キレイビズ」にお任せ！全国対応！ご要望に合わせオーダーメイドでの求人紹介も可能です。";
        } else {
            $description .= "美容師/理容師/アイリスト/ネイリスト/エステティシャンなど美容業界の求人なら利用満足度No.1の「キレイビズ」にお任せ！全国対応！ご要望に合わせオーダーメイドでの求人紹介も可能です。";
        }
        return $description;
    }


    /**
     * @desciption  title for search page
     * @param $searchInfo
     * @return string
     */
    private function makeTitle($searchInfo, $params): string
    {

        //if list favorite
        if (request('fa') == 1) {
            return 'キープ一覧';
        }

        $titleOccupation = '';

        // tuantv begin - 25/10/2023
        $titleOccupation = '';
        if (count($params['occupations']) > 0) {
            $titleOccupation = $this->getTitleOccupation($params['occupations']);
        }
        // tuantv end - 25/10/2023

        //if list search
        $titleArea = '';
        if ($searchInfo['areas']->count() > 0) {
            if ($searchInfo['areas']->count() >= 4) {
                $searchInfo['areas'] = $searchInfo['areas']->slice(0, 2);
                $searchInfo['areas']->push("その他のエリア");
            }
            $titleArea = $searchInfo['areas']->join('/') . ' の';  
        }

        // $title = $searchInfo['areas']->count() > 0 ? $searchInfo['areas']->join('/') . ' の' : '';
        $title = $titleArea;
        $title .= $searchInfo['rails']->count() > 0 ? $searchInfo['rails']->join('/') . ' の' : '';

        $title .= $searchInfo['occupations']->count() > 0 ? ($titleOccupation) : ' 美容師/アイリスト/ネイリスト等';
        $title .= $searchInfo['employmentStatus']->join(' / ');
        $title .= $searchInfo['salary'];
        $title .= $searchInfo['features']->join(' / ');
        $title .= ' の求人一覧【キレイビズ】';

        $lastSlashPosition = strrpos($title, '/');
        if ($lastSlashPosition !== false) {
            // Replace the final '/' with a comma ','
            $title = substr_replace($title, ',', $lastSlashPosition, 1);
        }

        return $title;
    }


     /**
     * @desciption  og title for search page
     * @param array $searchInfo
     * @author tuantv
     * @return string
     */
    public function getTitleOccupation($paramsOccupations) {

        $total = count($paramsOccupations);
        $difference = 0;
        $titleOccupation = '';
        $max = 5;
        $textBiyoshi = '美容師求人';
        $textJobOther = 'その他職種';
        $flagBiyoshi = false;
        if (array_search('biyoshi', $paramsOccupations) !== false) {     
            $flagBiyoshi = true;
            $paramsOccupations = array_values($paramsOccupations);
        }

        $check = $this->checkElementBiyoshi($paramsOccupations); //check isset stylist or assistant or colorist
        $flag = false;
        if ($check) {
            $posStylist = array_search("stylist", $paramsOccupations);
            $posAssistant = array_search("assistant", $paramsOccupations);
            $posColorist = array_search("colorist", $paramsOccupations);

            if ($posStylist !== false) {
                unset($paramsOccupations[$posStylist]);
                $flag = true;
            } 

            if ($posAssistant !== false) {
                unset($paramsOccupations[$posAssistant]);
                $flag = true;
            } 

            if ($posColorist !== false) {
                unset($paramsOccupations[$posColorist]);
                $flag = true;
            } 

            $difference = $total - count($paramsOccupations);
           
        }

        $arrTitleMetas = $this->occupationRepository
                    ->findWhere([
                        ['seo_url', 'IN', $paramsOccupations],
                        ['disp','=', 1]
                    ])
                    ->sortBy('level')
                    ->pluck('name');

        // case all Biyoshi (isset stylist and assistant and colorist)
        if ($flagBiyoshi) {
            $arrTitleMetas->prepend($textBiyoshi); // - 1 là trừ đi phần tử Biyoshi được add ở $arrTitleMetas->prepend($textBiyoshi);
            if (count($arrTitleMetas) - 1 + 3 > $max) {
                $arrTitleMetas = collect([$textBiyoshi, $textJobOther]);
            }
        } else if($flag) { // case Biyoshi not enough(isset stylist or assistant or colorist)
            $arrTitleMetas->prepend($textBiyoshi);
            if (count($arrTitleMetas) - 1 + $difference > $max) { // - 1 là trừ đi phần tử Biyoshi được add ở $arrTitleMetas->prepend($textBiyoshi);
                $arrTitleMetas = $arrTitleMetas->slice(0, 2)->push($textJobOther);
            }
        } else { // case not isset Biyoshi (not isset stylist, assistant, colorist)
            if (count($arrTitleMetas) > $max) {
                $arrTitleMetas = $arrTitleMetas->slice(0, 2)->push($textJobOther);
            }

        }

        $titleOccupation = $arrTitleMetas->implode(' / ');

        return $titleOccupation;
    }

    public function checkElementBiyoshi($data) {
        if (array_search('assistant', $data) !== false
            || array_search('stylist', $data) !== false || array_search('colorist', $data) !== false
        ) {
            return true;
        }

        return false;
    }

    /**
     * @desciption  og title for search page
     * @param array $searchInfo
     * @return string
     */
    private function makeOgTitle(array $searchInfo): string
    {

        $oGTitle = '';
        $title = [];
        $countConditions = 0;
        if ($searchInfo['areas']->count() > 0) {
            $countConditions++;
            $title[] = $searchInfo['areas']->join('/');
        }
        
        if ($searchInfo['occupations']->count() > 0) {
            $countConditions++;
            $title[] = $searchInfo['occupations']->join(',');
        }

        if ($searchInfo['rails']->count() > 0) {
            $countConditions++;
            $title[] = $searchInfo['rails']->join(',');
        }

        if ($searchInfo['features']->count() > 0) {
            $countConditions++;
            $title[] = $searchInfo['features']->join(',');
        }

        if ($searchInfo['employmentStatus']->count() > 0) {
            $countConditions++;
            $title[] = $searchInfo['employmentStatus']->join(',');
        }
       
        if ( $countConditions > 0) {

            //save old search text
            session(['old_search_text' => implode('/', $title)]);
            session(['old_search_url' => url()->full()]);

            

            if (count($title) > 3) {
                $title = array_merge(array_slice($title, 0, 3), ['その他条件']);
            }
            $oGTitle = implode('/', $title);
           
        } else {
            session()->forget('old_search_text');
            session()->forget('old_search_url');
        }

        if (request('page') > 1) {
            if (empty($oGTitle)) {
                $oGTitle = '求人情報';
            }
            $oGTitle = $oGTitle . ' (' . request('page') . ')';
        }

       
        return $oGTitle;
    }

}
