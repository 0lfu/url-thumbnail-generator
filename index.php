<?php
    require 'vendor/autoload.php';

    use YoutubeDl\Options;
    use YoutubeDl\YoutubeDl;

    function downloadCdaVideo($url)
    {
        $yt = new YoutubeDl();
        $yt->setBinPath('yt-dlp.exe');

        $options = Options::create()
            ->downloadPath('youtube-dl')
            ->url($url)
            ->skipDownload(true);
        $collection = $yt->download($options);

        $thumbnailUrl = "";
        foreach ($collection->getVideos() as $video) {
            if ($video->getError() !== null) {
                return false;
            } else {
                foreach ($video->getThumbnails() as $thumbnail){
                    $thumbnailUrl = $thumbnail->getUrl();
                }
            }
        }
        return $thumbnailUrl;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Thumbnail Generator</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.5/jszip.min.js"></script>
    <script>
        $(document).ready(function() {
            function toggleDownloadAllButton() {
                var itemCount = $('.item').length;
                if (itemCount > 0) {
                    $('#download-all').show();
                } else {
                    $('#download-all').hide();
                }
            }

            toggleDownloadAllButton();

            $('.item').on('click', function() {
                var imageSrc = $(this).find('img').attr('src');
                var filename = $(this).find('img').data('filename');
                
                fetch(imageSrc)
                    .then(response => response.blob())
                    .then(blob => {
                        var link = document.createElement('a');
                        link.href = URL.createObjectURL(blob);
                        link.download = filename + '.jpg';
                        link.click();

                        $(this).remove();
                        toggleDownloadAllButton();
                    });
            });

            $('#form-clear').on('click', function() {
                $('#form-urls').val("");
                $('#form-title').val("");
                $('#form-startcount').val("");
                $('.item').remove();
                toggleDownloadAllButton();
            });
            
            $('#download-all').on('click', function() {
                var imageElements = $('.item img');
                var zip = new JSZip();

                imageElements.each(function(index, imgElement) {
                    var imageSrc = $(imgElement).attr('src');
                    var filename = $(imgElement).data('filename') + '.jpg';

                    fetch(imageSrc)
                        .then(response => response.blob())
                        .then(blob => {
                            zip.file(filename, blob, { binary: true });
                            
                            if (index === imageElements.length - 1) {
                                zip.generateAsync({ type: 'blob' }).then(function(content) {
                                    var link = document.createElement('a');
                                    link.href = URL.createObjectURL(content);
                                    link.download = 'thumbnails.zip';
                                    link.click();
                                });
                            }
                        });
                });
                $('.item').remove();
                toggleDownloadAllButton();
            });
        });
    </script>
</head>
<body>
    <div class="wrapper">
        <div class="main">
            <h1><span class="red">URL</span> Thumbnail Generator</h1>
            <p id="info">Support: YouTube, CDA, FB, IG and more...</p>
            <form id="formCda" method="get">
                <div class="form-row">
                    <input type="text" id="form-title" name="title" placeholder="Provide thumbnail title..." required value="<?php echo isset($_GET['title']) ? $_GET['title'] : ""; ?>">
                    <input type="number" id="form-startcount" name="startcount"placeholder="1" min="0" step="1" value="<?php echo isset($_GET['startcount']) ? $_GET['startcount'] : ""; ?>">
                </div>
                <textarea name="urls" id="form-urls" placeholder="Provide video URL..." required><?php echo isset($_GET['urls']) ? $_GET['urls'] : ""; ?></textarea>
                <input type="submit" value="Generate">
                <button type="button" id="form-clear">Clear</button>
            </form>
        </div>
        <div class="container">
            <?php
                if(isset($_GET['urls']) && !empty($_GET['urls']) && isset($_GET['title']) && !empty($_GET['title'])){
                    $urlsArray = explode("\n", $_GET['urls']);
                    $count = !empty($_GET['startcount']) ? intval($_GET['startcount']) : 1;
                    foreach($urlsArray as $url){
                        $formattedCount = str_pad($count, 2, '0', STR_PAD_LEFT);
                        $title = $_GET['title'].' - '.$formattedCount;
                        echo '<div class="item"><img src="'.downloadCdaVideo($url).'" data-filename="'.$title.'"><p>'.$title.'</p></div>';
                        $count++;
                    }
                }
            ?>
            <button type="button" id="download-all">Download All</button>
        </div>
    </div>
</body>
</html>