<?php
function generateThumbnail($source, $destination, $type)
{
    if ($type === 'image') {
        $img = null;
        if (exif_imagetype($source) === IMAGETYPE_JPEG) {
            $img = imagecreatefromjpeg($source);
        } elseif (exif_imagetype($source) === IMAGETYPE_PNG) {
            $img = imagecreatefrompng($source);
        }

        if ($img) {
            $thumb = imagescale($img, 200);
            imagejpeg($thumb, $destination);
            imagedestroy($img);
            imagedestroy($thumb);
        }
    } elseif ($type === 'video') {
        $cmd = "ffmpeg -i " . escapeshellarg($source) . " -ss 00:00:01 -vframes 1 " . escapeshellarg($destination);
        shell_exec($cmd);
    }
}

function extractVideoDuration($file)
{
    $cmd = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($file);
    $duration = shell_exec($cmd);
    return trim($duration);
}
