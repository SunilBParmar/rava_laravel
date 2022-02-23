<?php

namespace App\Helpers;

use App\Http\Controllers\FileController;
use App\Models\EntityFile;
use App\Models\File;
use App\Models\Tag;
use App\Models\User;
use App\Models\Workout;
use Aws\S3\S3Client;
use Dingo\Api\Http\Request;
use getID3;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

class BBHelper
{
    const E_NOT_FOUND = 10;
    const E_NOT_IS_FILE = 20;
    const E_NOT_IS_DIR = 30;

    const VIDEOS_DIR = '/workouts/videos/';
    const VIDEOS_DIR_UPLOAD = 'workouts/videos/';
    const WORKOUT_PREVIEWS_DIR_UPLOAD = 'workouts/previews/';
    const USER_PHOTO_DIR_UPLOAD = 'users/photos/';

    /**
     * @var S3Client|null $_client
     */
    protected $_client = null;

    /**
     * @var string|null $_bucket
     */
    protected $_bucket = null;

    /**
     * BBHelper constructor.
     * @throws InternalErrorException
     */
    public function __construct()
    {
        $version = env('AWS_VERSION', null);
        $region = env('AWS_DEFAULT_REGION', null);
        $endpoint = env('AWS_BUCKET_ENDPOINT', null);
        $key = env('AWS_ACCESS_KEY_ID', null);
        $secret = env('AWS_SECRET_ACCESS_KEY', null);
        $this->_bucket = env('AWS_BUCKET', null);

        if (!$region || !$endpoint || !$key || !$secret || !$this->_bucket) {
            throw new InternalErrorException('Internal security measures not configured', 500);
        }

        $this->_client = new S3Client([
            'version' => $version,
            'region' => $region,
            // https://
            'endpoint' => $endpoint,
            'credentials' => [
                'key' => $key,
                'secret' => $secret,
            ],
        ]);
    }

    /**
     * @param string $fileName
     * @param string $filePath
     * @param string $ext
     * @param int $size
     * @param Request $request
     * @param string $sub
     * @return File|boolean
     */
    public function storeFile($fileName, $filePath, $ext, $size, $request, $sub = self::VIDEOS_DIR_UPLOAD)
    {
        $response = $this->_client->putObject([
            'Bucket' => $this->_bucket,
            'Key' => $sub . $fileName,
            'SourceFile' => $filePath,
        ]);

        $response = $response->toArray();
        $statusCode = $response['@metadata']['statusCode'] ?? null;
        $publicUrl = $response['@metadata']['effectiveUri'] ?? null;

        if ($statusCode !== 200 || !$publicUrl) {
            return false;
        }

        $file = new File();
        $file->name = mb_substr($fileName, 0, mb_stripos($fileName, '.'));
        $file->ext = $ext;
        $file->duration = $this->getVideoDuration($filePath);
        $file->size = (int)$size;
        $title = $request->post('title', null);
        $addData = $request->post('add_data', null);

        if ($title) {
            $file->title = $title;
        }

        if ($addData) {
            $file->add_data = $addData;
        }

        $file->public_url = $publicUrl;

        if ($file->save()) {
            return $file;
        }

        return false;
    }

    /**
     * Uploads file
     * @param $fileName
     * @param $filePath
     * @param $sub
     * @return array
     */
    public function uploadEntityFile($fileName, $filePath, $sub)
    {
        $response = $this->_client->putObject([
            'Bucket' => $this->_bucket,
            'Key' => $sub . $fileName,
            'SourceFile' => $filePath,
        ]);

        $response = $response->toArray();
        $statusCode = $response['@metadata']['statusCode'] ?? null;
        $publicUrl = $response['@metadata']['effectiveUri'] ?? null;

        if ($statusCode !== 200 || !$publicUrl) {
            return [true, null, null];
        }

        return [true, $statusCode, $publicUrl];
    }

    /**
     * If path exists
     *
     * @param string $path Remote Path
     *
     * @return bool
     */
    public function exists($path)
    {
        return $this->_client->doesObjectExist($this->_bucket, $path);
    }

    /**
     * @param string $from
     * @param string $to
     * @return array
     * @throws InternalErrorException
     */
    public function moveFolderWithObjects($from, $to)
    {
        $from = ltrim($from, "/");
        $to = ltrim($to, "/");
        if (!$this->exists($from)) {
            $path = $this->_bucket . "/" . $from;
            throw new InternalErrorException("Object {$path} Not found");
        }
        if ($this->exists($to)) {
            $path = $this->_bucket . "/" . $to;
            throw new InternalErrorException("Object {$path} exists");
        }
        // Copy Object
        $res = $this->_client->copyObject(['Bucket' => $this->_bucket, 'Key' => $to, 'CopySource' => $this->_bucket . '/' . rawurlencode($from), 'ACL' => 'public-read', 'MetadataDirective' => 'COPY']);
        $pathFrom = trim($from, '/') . '/';
        $iterator = $this->_client->getIterator('ListObjects', ['Bucket' => $this->_bucket, 'Prefix' => $pathFrom]);
        foreach ($iterator as $object) {
            $pattern = preg_quote($pathFrom, "/");
            $Key = preg_replace("/^{$pattern}/", "", $object['Key']);
            if (!empty($Key)) {
                $pattern = preg_quote($pathFrom, "/");
                $Key = preg_replace("/^{$pattern}/", "", $object['Key']);
                $origem = $this->_bucket . '/' . $object['Key'];
                $destino = trim($to, "/") . "/" . $Key;
                // Copy Object
                $res = $this->_client->copyObject(['Bucket' => $this->_bucket, 'Key' => $destino, 'CopySource' => $origem, 'ACL' => 'public-read', 'MetadataDirective' => 'COPY']);
            }
        }
        return $res->toArray();
    }

    /**
     * If path is file
     *
     * @param string $path Remote Path
     *
     * @return bool
     */
    public function isFile($path)
    {
        $path = trim($path, '/');
        return $this->_client->doesObjectExist($this->_bucket, $path);
    }

    /**
     * @param string $filePath
     * @return int|mixed
     */
    public function getVideoDuration($filePath)
    {
        $getID3 = new getID3;
        $file = $getID3->analyze($filePath);
        $durationSeconds = $file['playtime_seconds'] ?? 0;
        if ($durationSeconds > 0) {
            return round($durationSeconds / 60, 1, PHP_ROUND_HALF_DOWN);
        }

        return 0;
    }

    /**
     * @param File $file
     * @param Request $request
     */
    public function attachUser(File $file, Request $request)
    {
        $user = User::find((int)$request->post('user_id', null)) ?? Auth::user();

        if (!$user) {
            return false;
        }

        $file->user_id = $user->id;
        return $file->save();
    }

    /**
     * @param File $file
     * @param Request $request
     * @return void
     */
    public function attachTags(File $file, Request $request)
    {
        $tags = [];
        $tagsIds = $request->post('tags_id', null);

        if (!$tagsIds || empty($tagsIds)) {
            return;
        }

        foreach (array_unique($tagsIds) as $tagId) {
            $tag = Tag::find((int)$tagId);

            if (!$tag) {
                continue;
            }

            $tags[] = $tag;
        }

        if (!$tags || empty($tags)) {
            return;
        }

        foreach ($tags as $tag) {
            $file->tags()->attach($tag);
        }
    }

    /**
     * Gets file extension not by mime type
     */
    public function getExt($s)
    {
        return mb_substr($s, mb_strripos($s, '.'));
    }

    /**
     * @param File|EntityFile $file
     * @param null $sub
     * @param bool $authExt
     * @return bool
     */
    public function deleteFile($file, $sub = null, $authExt = false)
    {
        $response = $this->_client->deleteObject([
            'Bucket' => $this->_bucket,
            'Key' => $sub . $file->name . ($authExt ? '' : $file->ext),
        ]);

        $response = $response->toArray();
        $statusCode = $response['@metadata']['statusCode'] ?? null;
        return $statusCode === 204;
    }


    /**
     * @param $request
     * @param Workout $workout
     * @return bool
     */
    public function savePreviewImage($request, Workout $workout)
    {
        if ($request->hasFile('preview_image') && $workout) {
            $file = $request->file('preview_image');
            $path = Storage::disk('local')->putFileAs(
                'storage',
                $request->file('preview_image'),
                $file->hashName()
            );

            if ($path) {
                $storePath = Storage::disk('local')->path($path);
                list($status, $statusCode, $publicUrl) = $this->uploadEntityFile($file->hashName(), $storePath, self::WORKOUT_PREVIEWS_DIR_UPLOAD);
                \Illuminate\Support\Facades\File::delete($storePath);

                if ($status === true && $statusCode === 200) {
                    $workout->deletePreviewImage(true);
                    $fileEntity = new EntityFile([
                        'entity_type' => EntityFile::ENTITY_TYPE_WORKOUT,
                        'entity_id' => $workout->id,
                        'name' => basename($path),
                        'public_url' => $publicUrl
                    ]);

                    if ($fileEntity->save()) {
                        $workout->preview_url = $publicUrl;
                        return $workout->save();
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param $request
     * @param User $user
     * @return bool
     */
    public function saveUserPhoto($request, User $user)
    {
        if ($request->hasFile('photo_image') && $user) {
            $file = $request->file('photo_image');
            $path = Storage::disk('local')->putFileAs(
                'storage',
                $request->file('photo_image'),
                $file->hashName()
            );

            if ($path) {
                $storePath = Storage::disk('local')->path($path);
                list($status, $statusCode, $publicUrl) = $this->uploadEntityFile($file->hashName(), $storePath, self::USER_PHOTO_DIR_UPLOAD);
                \Illuminate\Support\Facades\File::delete($storePath);

                if ($status === true && $statusCode === 200) {
                    $user->deletePhotoImage(true);
                    $fileEntity = new EntityFile([
                        'entity_type' => EntityFile::ENTITY_TYPE_USER,
                        'entity_id' => $user->id,
                        'name' => basename($path),
                        'public_url' => $publicUrl
                    ]);

                    if ($fileEntity->save()) {
                        $user->photo_url = $publicUrl;
                        return $user->save();
                    }
                }
            }
        }
        return false;
    }
}
