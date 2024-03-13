<?php

namespace App\Services\Traits;

use App\Http\Resources\User\UserExport;

trait UserExportTrait
{
    protected function getHeaders(): array
    {
        return [
            'user_id' => '会員ID',
            'name_sei' => '名前[姓]',
            'name_mei' => '名前[名]',
            'kana_sei' => 'ふりがな[姓]',
            'kana_mei' => 'ふりがな[名]',
            'phone' => '電話番号',
            'email' => 'メールアドレス',
            'username' => 'ログインID',
            'wish_mypage' => 'マイページ利用',
            'wish_scout' => 'スカウトメール',
            'wish_area' => '希望エリア',
            'wish_area_2' => '希望エリア-2',
            'wish_area_3' => '希望エリア-3',
            'wish_occupation' => '希望職種',
            'wish_occupation_2' => '希望職種-2',
            'wish_occupation_3' => '希望職種-3',
            'logged_at' => '最終ログイン日時',
            'updated_at' => '最終更新日時',
            'created_at' => '登録日時',
        ];
    }

    /**
     * @param array $params
     * @return array
     */
    public function getUserListForExport(array $params): array
    {
        set_time_limit(0);
        ini_set('memory_limit', '1G');
        $users = $this->userRepository->getListForExport(['filters' => $params]);
        $headers = $this->getHeaders();
        $data = [];
        $data[] = $headers;
        foreach ($users->lazy(100) as $k => $user) {
            if ($k % 100 === 0) {
                flush();
            }
            $row = [];
            $user = new UserExport($user);
            $user = $user->toArray(request());
            foreach ($headers as $key => $header) {
                $row[] = $user[$key];
            }
            $data[] = $row;
        }
        return $data;
    }

}
