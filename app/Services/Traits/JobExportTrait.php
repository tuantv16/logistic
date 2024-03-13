<?php

namespace App\Services\Traits;

use App\Http\Resources\Job\JobExportCSV;
use App\Http\Resources\Job\JobExportIndeed;
use App\Models\CustomItem;
use Illuminate\Http\Request;

trait JobExportTrait
{
    /**
     * @return string[]
     */
    private function getHeadersCSV(): array
    {
        return [
            '企業名',
            '求人ID',
            '求人名',
            '求人企業名',
            '求人企業名公開設定',
            '郵便番号',
            '住所(県)',
            '住所(市区町村)',
            '住所(その他・ビル名等)',
            '募集企業事業内容',
            '職種(大区分)',
            '職種(中区分)',
            '職種(小区分)',
            '仕事内容詳細',
            '雇用形態',
            '給与',
            '地域(大区分)',
            '地域(中区分)',
            '地域(小区分)',
            '特徴項目',
            '求人情報のPR',
            'メールアドレス',
            'メモ',
            'TEL',
            '応募期間［開始日］',
            '応募期間［終了日］',
            '公開設定',
            '最終更新日時',
            '登録日時',
            '注目設定',
            '応募上限数',
            '路線(大区分)',
            '路線(中区分)',
            '路線(小区分)',
            '契約プラン',
            '担当営業',
            '採用お祝い金',
        ];
    }

    private function getFieldsCSV(): array
    {
        return [
            'client_name',
            'job_id',
            'job_name',
            'shop',
            'disp_cl',
            'zip',
            'pref',
            'address',
            'address_2',
            'business',
            'occupation',
            'occupation_2',
            'occupation_3',
            'description',
            'employment',
            'salary',
            'area',
            'area_2',
            'area_3',
            'feature',
            'intro',
            'email',
            'memo',
            'phone',
            'limit_s_date',
            'limit_e_date',
            'disp',
            'updated_at',
            'created_at',
            'pickup',
            'max_apply',
            'rail',
            'rail_2',
            'rail_3',
            'plan',
            'sales_staff',
            'gift_money',
        ];
    }

    /**
     * @return string[]
     */
    private function getHeadersIndeed(): array
    {
        return [
            'ステータス', //1
            '職種名', //2
            '求人キャッチコピー', //3
            '会社名', //4
            '勤務地', //5
            '応募用メールアドレス (最大25件)', //6
            '電話番号（半角）', //7
            '履歴書の有無', //8
            '直接訪問先の住所', //9
            '直接訪問先の追加説明', //10
            '雇用形態', //11
            '仕事内容（仕事内容）', //12
            '仕事内容（アピールポイント）', //13
            '仕事内容（求める人材）', //14
            '仕事内容（勤務時間・曜日）', //15
            '仕事内容（休暇・休日）', //16
            '仕事内容（勤務地）', //17
            '仕事内容（アクセス）', //18
            '仕事内容（待遇・福利厚生）', //19
            '仕事内容（その他）', //20
            '給与（下限）', //21
            '給与上限', //22
            '給与種別', //23
            '職種カテゴリー', //24
            '掲載画像(URL)', //25
        ];
    }


    public function getFieldsIndeed(){
        return [
            'status', //1
            'occupation', //2
            'job_name', //3
            'client_name', //4
            'address', //5
            'email', //6
            'phone', //7
            'cv', //8
            'address_2', //9
            'access_description',  //10
            'access',  //11
            'description', //12
            'intro', //13
            'qualification', //14
            'worktime', //15
            'holiday', //16
            'address', //17
            'access', //18
            'treat', //19
            'description_other', //20
            'salary_min', //21
            'salary_max', //23
            'employment_type', //24
            'image', //25
        ];
    }

    /**
     * @return array
     */
    private function getConfigCSV(): array
    {
        $header = $this->getHeadersCSV();
        $fields = $this->getFieldsCSV();
        $customItems = $this->customItemsRepository->getCustomFieldsByCategory(CustomItem::JOB);
        foreach ($customItems as $customItem) {
            $header[] = $customItem->label;
            $fields[] = $customItem->name;
        }

        return [
            'header' => $header,
            'fields' => $fields,
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getDataIndeed(Request $request): array
    {

        $jobs = $this->jobRepository->getDataForExport($request->all());
        $dataExport[] = $this->getHeadersIndeed();
        foreach ($jobs->lazy(100) as $k => $job) {
            if ($k % 100 === 0) {
                flush();
            }
            $job = new JobExportIndeed($job);
            $job = $job->toArray($request);
            $row = [];
            foreach ($this->getFieldsIndeed() as $field) {
                $row[$field] = $job[$field] ?? '';
            }

            $dataExport[] = $row;
        }

        return $dataExport;

    }

    /**
     * @param Request $request
     * @return array
     */
    public function getDataCSV(Request $request): array
    {
        $config = $this->getConfigCSV();
        $jobs = $this->jobRepository->getDataForExport($request->all());
        $dataExport[] = $config['header'];
        foreach ($jobs->lazy(100) as $k => $job) {
            if ($k % 100 === 0) {
                flush();
            }
            $job = new JobExportCSV($job);
            $job = $job->toArray($request);
            $row = [];
            foreach ($config['fields'] as $field) {
                $row[$field] = $job[$field] ?? '';
            }
            $dataExport[] = $row;
        }
        return $dataExport;
    }
}
