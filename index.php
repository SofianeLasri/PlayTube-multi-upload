<?php
$bddHost = 'localhost'; //MySQL server address
$bddUser = 'playtube'; //User
$bddMdp = 'aStrongPassword'; //Password
$bddName = 'playtube-user'; //Database name

$baseUpFolder = "/var/www/videos/multiple-upload/videos-to-up/"; //full path of the folder videos-to-up -> Indicate the absolute path
$siteUrl = "https://videos.sl-projects.com/"; //Website address
$sitePath = "/var/www/videos/"; //full path of the site root
$ffmpeg_b = "/usr/bin/ffmpeg";  //full path to ffmpeg
$transcodeSpeed = "medium"; //Preset of ffmpeg -> veryslow, slow, medium, fast (check on the internet I forgot the rest xD)

//error_reporting(E_ALL);
//ini_set("display_errors", 1);//Disable errors

///////////////////////////////////
// Ne pas toucher à partir d'ici //
///////////////////////////////////

try {
    $bddConn = new PDO("mysql:host=$bddHost;dbname=$bddName", $bddUser, $bddMdp); //Test the connection
    $bddConn = null;
} catch (PDOException $e) { 
	$bddError = true;
	die();
}
if (!isset($bddError)){
	$bdd = new PDO("mysql:host=$bddHost;dbname=$bddName", $bddUser, $bddMdp);
	$bdd->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	$bdd->query("SET NAMES 'utf8'");
}

if (isset($_POST["submit"]) && isset($_POST["folderPath"])) {
    $files = scanFolder($_POST["folderPath"]);
    natsort($files);
}

if (isset($_POST["submit"]) && isset($_POST["videoDescription"])) {
    $message = ["success", "Traitement en cours"];
    $links = uploadVideo($_POST["folderPath"], $files, $_POST["videoDescription"], $_POST["category"], $_POST["privacy"], $_POST["age_restriction"], $_POST["tags"], $_POST["playlist"], $_POST["quality"], $_POST["userId"], $_POST["ffmpeg"]);
}

function scanFolder($dir) {
    $results = array();
    $files = scandir($dir);
    foreach ($files as $key => $value) {
        if (!is_dir($value)) {
            $results[] = $value;
        }
    }
    
    return $results;
}

function uploadVideo($folderPath, $files, $description, $category, $privacy, $age_restriction, $tags, $playlist, $quality, $userId, $ffmpeg) {
    global $bdd, $siteUrl, $ffmpeg_b, $transcodeSpeed, $sitePath;
    $results = array();
    foreach ($files as $video) {
        include_once('../assets/import/getID3-1.9.14/getid3/getid3.php');
        $getID3 = new getID3;
        $video_time = $getID3->analyze($folderPath."/".$video);
        $video_time = round((10 * round($video_time['playtime_seconds'],0)) / 100,0);

        $id = generateRandomString(15);
        $shortId = generateRandomString(6);
        $thumbnailPath = "upload/photos/".date("Y")."/".date("m")."/".$id.".video_thumb.jpeg";
        $videoPath = "upload/videos/".date("Y")."/".date("m")."/".$id."_video.mp4";
        $video_output_full_path_4096 = "upload/videos/".date("Y")."/".date("m")."/".$id."_video_4096p_converted.mp4";
        $video_output_full_path_2048 = "upload/videos/".date("Y")."/".date("m")."/".$id."_video_2048p_converted.mp4";
        $video_output_full_path_1080 = "upload/videos/".date("Y")."/".date("m")."/".$id."_video_1080p_converted.mp4";
        $video_output_full_path_720 = "upload/videos/".date("Y")."/".date("m")."/".$id."_video_720p_converted.mp4";
        $video_output_full_path_480 = "upload/videos/".date("Y")."/".date("m")."/".$id."_video_480p_converted.mp4";
        $video_output_full_path_360 = "upload/videos/".date("Y")."/".date("m")."/".$id."_video_360p_converted.mp4";
        $video_output_full_path_240 = "upload/videos/".date("Y")."/".date("m")."/".$id."_video_240p_converted.mp4";
        $gifPath = "upload/videos/".date("Y")."/".date("m")."/".$id."_small_video_.gif";
        $link = $siteUrl."v/".$shortId;
        array_push($results, '<a href="'.$link.'" target="_blank" class="list-group-item list-group-item-action list-group-item-success">'.$video.'</a>');

        $gif_time = 3;
        $gif_video_time = '-t '.$gif_time.'  -async 1';
        $shell = shell_exec("$ffmpeg_b $gif_video_time -y -i \"".$folderPath."/".$video."\" ".$sitePath.$gifPath);
        if ($quality=="4k") {
            $q240p=1;$q360p=1;$q480p=1;$q720p=1;$q1080p=1;$q2048p=1;$q4096p=1;
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=3840:-2 -crf 26 ".$sitePath.$video_output_full_path_4096." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=2048:-2 -crf 26 ".$sitePath.$video_output_full_path_2048." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=1920:-2 -crf 26 ".$sitePath.$video_output_full_path_1080." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=1280:-2 -crf 26 ".$sitePath.$video_output_full_path_720." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=854:-2 -crf 26 ".$sitePath.$video_output_full_path_480." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=640:-2 -crf 26 ".$sitePath.$video_output_full_path_360." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=426:-2 -crf 26 ".$sitePath.$video_output_full_path_240." 2>&1");
        } elseif ($quality=='2k') {
            $q240p=1;$q360p=1;$q480p=1;$q720p=1;$q1080p=1;$q2048p=1;$q4096p=0;
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=2048:-2 -crf 26 ".$sitePath.$video_output_full_path_2048." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=1920:-2 -crf 26 ".$sitePath.$video_output_full_path_1080." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=1280:-2 -crf 26 ".$sitePath.$video_output_full_path_720." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=854:-2 -crf 26 ".$sitePath.$video_output_full_path_480." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=640:-2 -crf 26 ".$sitePath.$video_output_full_path_360." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=426:-2 -crf 26 ".$sitePath.$video_output_full_path_240." 2>&1");
        } elseif ($quality=='1080p') {
            $q240p=1;$q360p=1;$q480p=1;$q720p=1;$q1080p=1;$q2048p=0;$q4096p=0;
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=1920:-2 -crf 26 ".$sitePath.$video_output_full_path_1080." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=1280:-2 -crf 26 ".$sitePath.$video_output_full_path_720." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=854:-2 -crf 26 ".$sitePath.$video_output_full_path_480." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=640:-2 -crf 26 ".$sitePath.$video_output_full_path_360." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=426:-2 -crf 26 ".$sitePath.$video_output_full_path_240." 2>&1");
        } elseif ($quality=='720p') {
            $q240p=1;$q360p=1;$q480p=1;$q720p=1;$q1080p=0;$q2048p=0;$q4096p=0;
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=1280:-2 -crf 26 ".$sitePath.$video_output_full_path_720." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=854:-2 -crf 26 ".$sitePath.$video_output_full_path_480." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=640:-2 -crf 26 ".$sitePath.$video_output_full_path_360." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=426:-2 -crf 26 ".$sitePath.$video_output_full_path_240." 2>&1");
        } elseif ($quality=='480p') {
            $q240p=1;$q360p=1;$q480p=1;$q720p=0;$q1080p=0;$q2048p=0;$q4096p=0;
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=854:-2 -crf 26 ".$sitePath.$video_output_full_path_480." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=640:-2 -crf 26 ".$sitePath.$video_output_full_path_360." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=426:-2 -crf 26 ".$sitePath.$video_output_full_path_240." 2>&1");
        } elseif ($quality=='360p') {
            $q240p=1;$q360p=1;$q480p=0;$q720p=0;$q1080p=0;$q2048p=0;$q4096p=0;
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=640:-2 -crf 26 ".$sitePath.$video_output_full_path_360." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=426:-2 -crf 26 ".$sitePath.$video_output_full_path_240." 2>&1");
        } elseif ($quality=='240p') {
            $q240p=1;$q360p=0;$q480p=0;$q720p=0;$q1080p=0;$q2048p=0;$q4096p=0;
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=426:-2 -crf 26 ".$sitePath.$video_output_full_path_240." 2>&1");
        } else {
            $q240p=1;$q360p=1;$q480p=1;$q720p=1;$q1080p=1;$q2048p=0;$q4096p=0;
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=1920:-2 -crf 26 ".$sitePath.$video_output_full_path_1080." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=1280:-2 -crf 26 ".$sitePath.$video_output_full_path_720." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=854:-2 -crf 26 ".$sitePath.$video_output_full_path_480." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=640:-2 -crf 26 ".$sitePath.$video_output_full_path_360." 2>&1");
            $shell = shell_exec("$ffmpeg_b -y -i \"".$folderPath."/".$video."\" -vcodec libx264 -preset $transcodeSpeed -filter:v scale=426:-2 -crf 26 ".$sitePath.$video_output_full_path_240." 2>&1");
        }
        $i = (int) ($video_time > 10) ? 11 : 1;
        $output_thumb = shell_exec("$ffmpeg_b -ss \"$i\" -i \"".$folderPath."/".$video."\" -vframes 1 -f mjpeg ".$sitePath.$thumbnailPath." 2<&1");

        $response = $bdd->prepare("INSERT INTO videos (id, video_id, user_id, short_id, title, description, thumbnail, video_location, youtube, vimeo, daily, facebook, ok, twitch, twitch_type, time, time_date, active, tags, duration, size, converted, category_id, views, featured, registered, privacy, age_restriction, type, approved, 240p, 360p, 480p, 720p, 1080p, 2048p, 4096p, sell_video, sub_category, geo_blocking, demo, gif, is_movie, stars, producer, country, movie_release, quality, rating, monetization, rent_price, stream_name, live_time, live_ended, agora_resource_id, agora_sid, license, is_stock) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $response->execute([null, $id, $userId, $shortId, $video, $description, $thumbnailPath, $videoPath, "", "", "", "", "", "", "", $video_time, date("Y")."-".date("m")."-".date("t")." ".date("H").":".date("i").":".date("s"), "0", $_POST["tags"], $video_time, filesize($folderPath."/".$video), "1", $category, "1", "1", date("Y")."/".date("m"), $privacy, $age_restriction, "", "1", $q240p, $q360p, $q480p, $q720p, $q1080p, $q2048p, $q4096p, 0, 0, "", "", $gifPath, 0, "", "", "", "", "", "", 1, 0, "", 0, 0, null, "", "", 0]);
        
        if ($playlist!="none") {
            $response = $bdd->prepare("SELECT id FROM videos WHERE short_id = ?"); // Je récupère l'id de la vidéo
            $response->execute([$shortId]);
            $videoId = $response->fetch();

            $response = $bdd->prepare("INSERT INTO play_list (id, list_id, video_id, user_id) VALUES (?,?,?,?)");
            $response->execute([null, $playlist, $videoId[0], $userId]);
        }
    }
    
    return $results;
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
	<title>Envoie multiple de vidéos</title>
</head>
<body class="bg-dark">
    <div class="container bg-white rounded mt-5 p-3">
        <form action="index.php" method="post">
            <?php
            if (isset($message)) {
                echo '<div class="alert alert-'.$message[0].'" role="alert">'.$message[1].'</div>';
                echo '<div class="list-group">';
                foreach ($links as $link) {
                    echo $link;
                }
                echo "</div>";
            }
            ?>
            <h1>Envoi automatisé de vidéos</h1>
            <div class="form-group">
                <label>Nom du dossier à scanner</label>
                <input required type="text" class="form-control" name="folderPath" value="/media/firecuda-2to/sites/videos/multiple-upload/videos-to-up">
            </div>

            <?php
            if (isset($files)) {
                echo '<div class="form-group"><label>Liste des fichiers (leur nom serviront de titre pour les vidéos)</label><select multiple class="form-control" id="filesList">';
                foreach ($files as $item) {
                    echo "<option>".$item."</option>";
                }
                echo '</select></div>'; ?>

            <div class="form-group">
                <label for="videoDescription">Description des vidéos</label>
                <textarea required class="form-control" name="videoDescription" rows="3"><?php if(isset($_POST["videoDescription"])){echo $_POST["videoDescription"];}?></textarea>
            </div>
            <?php
                echo '<div class="form-group"><label>Catégorie</label><select required class="form-control" name="category" id="category">';
                $res = $bdd->query("SELECT * FROM langs WHERE type='category'");
                while($category = $res->fetch()){
                    echo "<option value=\"".$category['lang_key']."\">".$category['french']."</option>";
                }
                echo '</select></div>'; ?>

            <div class="form-group">
                <label>Confidentialité</label>
                <select required name="privacy" required id="privacy" class="form-control">
                  <option <?php if(isset($_POST["privacy"])&&($_POST["privacy"]==0)){echo 'selected';}?> value="0">Public</option>
                  <option <?php if(isset($_POST["privacy"])&&($_POST["privacy"]==1)){echo 'selected';}?> value="1">Privé</option>
                  <option <?php if(isset($_POST["privacy"])&&($_POST["privacy"]==2)){echo 'selected';}?> value="2">Non listé</option>
               </select>
            </div>
            <div class="form-group">
                <label>Restriction d'âge</label>
                <select required name="age_restriction" id="age_restriction" class="form-control">
                    <option <?php if(isset($_POST["age_restriction"])&&($_POST["age_restriction"]==1)){echo 'selected';}?> value="1">Tous les âges peuvent voir cette vidéo</option>
                    <option <?php if(isset($_POST["age_restriction"])&&($_POST["age_restriction"]==2)){echo 'selected';}?> value="2">Seulement +18</option>
                </select>
            </div>
            <div class="form-group">
                <label>Tags (1 seul stp car flemme de faire du js)</label>
                <input required type="text" class="form-control" name="tags" <?php if(isset($_POST["tags"])){echo 'value="'.$_POST["tags"].'"';}?>>
            </div>

            <?php
                echo '<div class="form-group"><label>Playlist (non obligatoire)</label><select class="form-control" name="playlist" id="playlist">';
                echo "<option value=\"none\">Aucune</option>";
                $res = $bdd->query("SELECT * FROM lists");
                while($playlist = $res->fetch()){
                    if(isset($_POST["playlist"])&&($playlist['list_id'] == $_POST["playlist"])){
                        $selected='selected';
                    }else{
                        $selected="";
                    }
                    echo "<option ".$selected." value=\"".$playlist['list_id']."\">".$playlist['name']."</option>";
                }
                echo '</select></div>'; ?>

            <div class="form-group">
                <label for="quality">Qualité</label>
                <select class="form-control" id="quality" name="quality">
                    <option value="4k">4k</option>
                    <option value="2k">2k</option>
                    <option value="1080p">1080p</option>
                    <option value="720p">720p</option>
                    <option value="480p">480p</option>
                    <option value="360p">360p</option>
                    <option value="240p">240p</option>
                </select>
            </div>

            <div class="custom-control custom-switch form-group">
                <input <?php if(isset($_POST["ffmpeg"])&&($_POST["ffmpeg"]=="on")){echo 'checked';}?> type="checkbox" class="custom-control-input" id="ffmpeg" name="ffmpeg">
                <label class="custom-control-label" for="ffmpeg">Utiliser FFmpeg ?</label>
            </div>

        <?php 
                echo '<div class="form-group"><label>Envoyer via le compte</label><select class="form-control" name="userId" id="userId">';
                $res = $bdd->query("SELECT * FROM users");
                while($user = $res->fetch()){
                    if(isset($_POST["userId"])&&($user['id'] == $_POST["userId"])){
                        $selected='selected';
                    }else{
                        $selected="";
                    }
                    echo "<option ".$selected." value=\"".$user['id']."\">".$user['username']."</option>";
                }
                echo '</select></div>';

        } ?>
            <div class="alert alert-danger" role="alert">
              Attention! Cela peut prendre <strong>énormément</strong> de temps. Pensez à modifier  <a href="https://stackoverflow.com/questions/15776400/make-script-execution-to-unlimited" target="_blank" class="alert-link">max_execution_time</a> avant d'envoyer!
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Envoyer</button>
        </form>
    </div>
</body>
</html>