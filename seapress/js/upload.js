var switchAndReload = function() {

    // get wp outside iframe

    var wp = parent.wp;

    // switch tabs (required for the code below)


    wp.media.frame.setState('insert');
    wp.media.frame.content.mode('browse');

    wp.media.frame.content.get().collection.props.set({ignore: (+ new Date())});
    wp.media.frame.content.get().options.selection.reset();

};

$('.seapress-upload').on('click', function(evt){

    // do upload logic...
    $url = window.location.hostname;
    $repo = this.getAttribute("data-repo");
    $file = this.getAttribute("data-file");
    $path = this.getAttribute("data-path");
    console.log($url);


    $test = "/wp-content/plugins/seapress/seawp.php?upload=true&repo=" + $repo + "&file=" + $path + $file;


    var request = $.get($test);

    request.success(function(result) {
      console.log(result);
      switchAndReload();
    });

    request.error(function(jqXHR, textStatus, errorThrown) {
      if (textStatus == 'timeout')
        alert('The server is not responding');

      if (textStatus == 'error')
        alert(errorThrown);

    });

});
