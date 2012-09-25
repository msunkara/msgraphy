new function($) {
  /* FLICKR SEARCH SCRIPT */
    $(document).ready(function(){
      //Submit Ajax Search on pressing Enter key in search box
      $('.flickrHeader #search').keypress(function(event) {
        if (event.which === 13) {
          event.preventDefault();
          ajax_search();
        }
      });
      //Or when clicking search button
      $('.flickrHeader #searchBtn').click(ajax_search);
    });
    $('.result_box').live('click', function() {
      $('.result_box').removeClass('selected');
      $('.result_box input').attr('disabled', 'disabled');
      $(this).addClass('selected');
      $(this).find('input').removeAttr('disabled');
    });
    function ajax_search() {
      var searchVal = $('.flickrHeader #search').val();
      $.ajax({
        type: 'GET',
        url: '/?flickrHeader_ajaxRequest=1',
        data: 'search=' + searchVal,
        dataType: 'json',
        beforeSend: function() {
          $('.flickrHeader #results').html('Loading...');
          if(!searchVal[0]) {
            $('.flickrHeader #results').html('<p>Please enter a keyword as search value.</p>');    
            return false;
          }   
        },
        success: function(response) {
          content = '';
          if (response.total == '0') {
            if (response.status == 'fail')
              content = '<p>'+response.message+'</p>';
            else
              content = '<p>There were no photos matching that search</p>';
            $('.flickrHeader #results').empty().append(content);
          } else {
            $('.flickrHeader #results').empty();
            for(index in response.photos) {
              photo = response.photos[index];
              content = $('<div class="result_box"></div>');
              $(content).append('<img src="'+photo.thumb_url+'" />');
              $(content).append('<input type="hidden" disabled="true" name="flickrHeader[image][id]" value="'+photo.id+'" />');
              $(content).append('<input type="hidden" disabled="true" name="flickrHeader[image][width]" value="'+photo.width+'" />');
              $(content).append('<input type="hidden" disabled="true" name="flickrHeader[image][height]" value="'+photo.height+'" />');
              $(content).append('<input type="hidden" disabled="true" name="flickrHeader[image][url]" value="'+photo.url+'" />');
              $(content).append('<input type="hidden" disabled="true" name="flickrHeader[image][thumb_url]" value="'+photo.thumb_url+'" />');
              $('.flickrHeader #results').append(content);
            }
          }
        }
      });
    }

  /* CROPPING SCRIPT */
  function onEndCrop( coords ) {
    $( '#x1' ).val(coords.x);
    $( '#y1' ).val(coords.y);
    $( '#width' ).val(coords.w);
    $( '#height' ).val(coords.h);
  }
  $(document).ready(function() {
    var init_width = Number($('#width').val());
    var init_height = Number($('#height').val());
    var init_x1 = Number($('#x1').val());
    var init_y1 = Number($('#y1').val());
    crop_args = {
      handles: true,
      keys: true,
      show: true,
      x1: init_x1,
      y1: init_y1,
      x2: init_width + init_x1,
      y2: init_height + init_y1,
      onSelectChange: function(img, c) {
        $('#x1').val(c.x1);
        $('#y1').val(c.y1);
        $('#width').val(c.width);
        $('#height').val(c.height);
      }
    }
    if (!fh_flex_width && !fh_flex_height)
      crop_args.aspectRatio = init_width + ':' + init_height;
    if (!fh_flex_height) crop_args.maxHeight = fh_height;
    if (!fh_flex_width) crop_args.maxWidth = fh_width;

    $('img#upload').imgAreaSelect(crop_args);
  });

}(jQuery);
