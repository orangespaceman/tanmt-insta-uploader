<?php

class Uploader
{
  private $cachePath = __DIR__ . "/../cache/";
  private $lastJsonPath = __DIR__ . "/../last.json";
  private $nextJsonPath = __DIR__ . "/../next.json";
  private $logFile = __DIR__ . "/../log.txt";

  public function upload()
  {
    $lastJson = file_get_contents($this->lastJsonPath);
    $lastData = json_decode($lastJson);

    $nextJson = file_get_contents($this->nextJsonPath);
    $nextData = json_decode($nextJson);

    // if we have already uploaded this image, stop
    if (isset($lastData->last_modified) && isset($nextData->last_modified) && $nextData->last_modified === $lastData->last_modified) {
      // $this->log("Image hasn't changed since last upload: " . $lastData->title);
      return;
    } else {
      $this->log("Uploading image: " . $nextData->title);
    }

    try {
        // $this->connect($username, $password);
        // $photo = new \InstagramAPI\Media\Photo\InstagramPhoto($nextData->imagePath);
        $caption = "$nextData->title\n\n$nextData->tags";
        // $this->instagram->timeline->uploadPhoto($photo->getFile(), ['caption' => $caption]);

        $this->log("Insta image uploaded: \n" . $nextData->imagePath . "\n" . $caption);

        file_put_contents($this->lastJsonPath, $nextJson);
        $this->log("Last JSON updated");

    } catch (\Exception $e) {
        $this->log("Insta upload error: " . $e->getMessage());
    }
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

  private function log($log)
  {
    echo $log . "\n";

    $fp = fopen($this->logFile, 'a');
    fwrite($fp, date("Y-m-d H:i:s") . ": " .  $log . "\n");
    fclose($fp);
  }
}

$uploader = new Uploader();
$uploader->upload();
