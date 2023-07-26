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

    public function getImageFromCdn(string $cdnPath, int $height, int $width): string
    {
        $uploadApi = new ImageTag($cdnPath, $this->config);

        $result = $uploadApi->resize(Resize::scale()->height($height));
        return (string) $result;
    }
}
