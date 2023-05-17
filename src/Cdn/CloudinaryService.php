<?php

declare(strict_types=1);

namespace App\Cdn;

use Cloudinary\Api\Exception\ApiError;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Tag\ImageTag;
use Cloudinary\Transformation\Argument\Color;
use Cloudinary\Transformation\Effect;
use Cloudinary\Transformation\Resize;
use Cloudinary\Transformation\RoundCorners;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class CloudinaryService
{


    private Configuration $config;

    public function __construct() {
        $this->config = Configuration::instance();
        $this->config->cloud->cloudName = 'dl4y4cfvs';
        $this->config->cloud->apiKey = '311921677578484';
        $this->config->cloud->apiSecret = 'X6oJvCzY4tIBcWstDA2YPUfukmQ';
        $this->config->url->secure = true;
    }

    public function uploadFileToCdn(string $localPath, string $cdnFolderPath, string $cdnName): void
    {

        $uploadApi = new UploadApi($this->config);

        try {
            $uploadApi->upload($localPath, [
                'folder' => $cdnFolderPath,
                'public_id' => $cdnName,
                'overwrite' => true,
                'notification_url' => '',
                'resource_type' => 'image'
            ]);
        } catch (ApiError $e) {
            dump($e);
        }
    }

    public function cartoon(string $localPath): void
    {
        $uploadApi = new ImageTag('debes/app/home_page_1.png', $this->config);
        $result = $uploadApi->effect(Effect::cartoonify())
            ->roundCorners(RoundCorners::max())
            ->backgroundColor(Color::LIGHTBLUE)
            ->resize(Resize::scale()->height(300));
        dd($result->longUrlSignature());
    }
}
