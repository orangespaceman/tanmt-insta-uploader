<?php

class Uploader
{
  private $cachePath = "cache/";
  private $jsonPath = "last.json";
  private $logFile = "log.txt";


  public function download($url)
  {
    try {
      $this->json = file_get_contents($url);
      $data = json_decode($this->json);

      $storedJson = file_get_contents($this->jsonPath);
      $storedData = json_decode($storedJson);

      // if we have already uploaded this image, stop
      if (isset($storedData->last_modified) && $storedData->last_modified === $data->last_modified) {
        // $this->log("Image hasn't changed since last upload: " . $storedData->title);
        return;
      } else {
        $this->log("Uploading image: " . $data->title);
      }

      // if we haven't already downloaded the image, do so
      $imageName = basename($data->image);
      $imagePath = $this->cachePath . $imageName;
      if (!file_exists($imagePath)) {
        file_put_contents($imagePath, fopen($data->image, "r"));
        $this->log("Image downloaded: " . $imageName);
      } else {
        $this->log("Image exists: " . $imageName);
      }

      return [
        "imagePath" => $imagePath,
        "title" => $data->title,
        "tags" => $data->tags,
      ];

    } catch (\Exception $e) {
      $this->log("Error: " . $e->getMessage());
    }
  }

  private function updateStoredJson()
  {
    file_put_contents($this->jsonPath, $this->json);
    $this->log("Local JSON updated");
  }

  private function connect($username, $password)
  {
    $this->instagram = new \InstagramAPI\Instagram();

    try {
        $this->instagram->login($username, $password);
    } catch (Exception $e) {
        $this->log("Insta login error: " . $e->getMessage());
        exit();
    }
  }

  public function upload($imagePath, $title, $tags, $username, $password)
  {
    try {
        $this->connect($username, $password);
        $imagePath = $this->checkImageRatio($imagePath);
        $photo = new \InstagramAPI\Media\Photo\InstagramPhoto($imagePath);
        $caption = "$title\n\n$tags";
        $this->instagram->timeline->uploadPhoto($photo->getFile(), ['caption' => $caption]);

        $this->log("Insta image uploaded: " . $imagePath);

        $this->updateStoredJson();

    } catch (\Exception $e) {
        $this->log("Insta upload error: " . $e->getMessage());
    }
  }

  private function log($log)
  {
    echo $log . "\n";

    $fp = fopen($this->logFile, 'a');
    fwrite($fp, date("Y-m-d H:i:s") . ": " .  $log . "\n");
    fclose($fp);
  }

  private function checkImageRatio($imagePath)
  {
    $maxLandscapeRatio = 1.8/1;
    $maxPortraitRatio = 5/4;

    $imageDimensions = getimagesize($imagePath);
    $width = $imageDimensions[0];
    $height = $imageDimensions[1];
    $rotation = 0;

    $exif = exif_read_data($imagePath);
    if(!empty($exif['Orientation']) &&
      ($exif['Orientation'] === 6 || $exif['Orientation'] === 8)) {
      $width = $imageDimensions[1];
      $height = $imageDimensions[0];

      if ($exif['Orientation'] === 6) {
        $rotation = 270;
      }
      if ($exif['Orientation'] === 8) {
        $rotation = 90;
      }
    }

    if ($width > $height && $width/$height > $maxLandscapeRatio) {
      $newHeight = floor($width / $maxLandscapeRatio);
      $x = 0;
      $y = -floor(($newHeight - $height) / 2);
      $imagePath = $this->resizeImage($imagePath, $width, $newHeight, $x, $y, $rotation);
    } else if ($height > $width && $height/$width > $maxPortraitRatio) {
      $newWidth = floor($height / $maxPortraitRatio);
      $y = 0;
      $x = -floor(($newWidth - $width) / 2);
      $imagePath = $this->resizeImage($imagePath, $newWidth, $height, $x, $y, $rotation);
    }

    return $imagePath;
  }

  private function resizeImage($imagePath, $width, $height, $x, $y, $rotation)
  {
    $pathinfo = pathinfo($imagePath);

    if ($pathinfo['extension'] == "jpg" || $pathinfo['extension'] == "jpeg") {
      $src = imagecreatefromjpeg($imagePath);
    } else if ($pathinfo['extension'] == "png") {
      $src = imagecreatefrompng($imagePath);
    } else if ($pathinfo['extension'] == "gif") {
      $src = imagecreatefromgif($imagePath);
    } else {
      return;
    }

    $newImagePath = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '-insta.' . $pathinfo['extension'];

    $tmp = imagecreatetruecolor($width, $height);
    $backgroundColor = imagecolorallocate($tmp, 255, 255, 255);
    imagefill($tmp, 0, 0, $backgroundColor);
    $rotatedSrc = imagerotate($src, $rotation, 0);
    imagecopyresampled($tmp, $rotatedSrc, 0, 0, $x, $y, $width, $height, $width, $height);
    imagejpeg($tmp, $newImagePath, 100);
    imagedestroy($src);
    imagedestroy($tmp);

    return $newImagePath;
  }
}
